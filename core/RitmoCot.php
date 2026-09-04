<?php
// ============================================================
//  RitmoCot — cuántas cotizaciones hace el asesor por semana
//
//  Responde una sola pregunta: ¿está cotizando a su ritmo, o se cayó?
//  La vara es ÉL MISMO —sus 4 semanas anteriores—, no el equipo: una
//  empresa de un solo vendedor no tiene equipo contra quién comparar, y dos
//  asesores con territorios distintos no cotizan al mismo ritmo aunque
//  trabajen igual de bien.
//
//  NO PESA EN EL SCORE, a propósito. La Conversión ya divide entre
//  cotizaciones abiertas, así que cotizar de más YA baja el score. Si además
//  premiáramos el volumen tendríamos dos fuerzas opuestas empujando el mismo
//  número, y —peor— le daríamos al asesor un motivo para cotizar cualquier
//  cosa. Esto informa; no califica.
//
//  Fuente: cotizaciones.created_at. Es la única fecha que SIEMPRE existe
//  (enviada_at puede ser NULL, updated_at se pisa sola con ON UPDATE), y es
//  inmutable: reconstruye la historia hacia atrás sin depender de que alguien
//  haya abierto el dashboard ese día, que es el defecto de score_diario.
//
//  Solo lectura. Sin migración: no hay tabla nueva ni columna nueva.
// ============================================================
defined('COTIZAAPP') or die;

class RitmoCot
{
    /** Semanas que forman la vara. 4 = un mes; menos es ruido, más es historia vieja. */
    private const SEMANAS_BASE = 4;
    /** Ritmo mínimo que prueba que HAY ritmo. Debajo de esto, 0 esta semana no
     *  significa nada: comparar 1 contra 0.75 es leer ruido.
     *  (RitmoAsesor::_citas usa el mismo gate con citas; aquí SÍ aplica también
     *  al cero, porque una semana sin cotizar no es lo mismo que una sin citas.) */
    private const BASE_MIN    = 2.0;
    private const CAIDA       = 0.50;  // menos de la mitad de su ritmo
    private const SUBIDA      = 1.50;  // más de vez y media
    /** Días dentro de la semana con señal de trabajo para creerle al bajón.
     *  Con 1 o menos, lo honesto es decir "no estuvo", no "bajó el ritmo". */
    private const DIAS_MIN    = 2;
    /** Cuántas veces su hueco normal entre cotizaciones tiene que pasar para
     *  llamarlo hueco. Quien cotiza 8 por semana debe cotizar casi a diario:
     *  3 días sin hacerlo es raro. Quien cotiza 2, no. El umbral sale de SU
     *  ritmo, no de un número fijo. */
    private const HUECO_VECES = 3.0;
    /** Días distintos de la semana en que se le ha visto cotizar, para saber
     *  qué días trabaja. Con menos, no hay hábito que leer. */
    private const DOW_MIN     = 3;

    /**
     * Los MISMOS filtros que usa el score para contar cotizaciones
     * (ActividadScore::calcular, "cot_asignadas"). Si aquí contáramos distinto,
     * el reporte diría "hiciste 8" y el score habría contado 5 — dos verdades
     * para el mismo asesor en la misma hoja.
     */
    private static function _where(): string
    {
        return "c.empresa_id = ? AND COALESCE(c.vendedor_id, c.usuario_id) = ?
                AND c.total > 0 AND c.suspendida = 0
                AND (c.estado != 'borrador' OR c.visitas > 0)";
    }

    /**
     * Días de importación masiva: >20 cotizaciones en un día no las hizo una
     * persona. Sin cota de fecha, igual que en ActividadScore — un import viejo
     * sigue siendo un import. Devuelve el fragmento SQL ya listo (o '').
     */
    private static function _sin_import(int $eid, int $uid): string
    {
        try {
            $d = DB::query(
                "SELECT DATE(created_at) AS d, COUNT(*) AS n FROM cotizaciones
                  WHERE empresa_id = ? AND COALESCE(vendedor_id, usuario_id) = ?
                  GROUP BY DATE(created_at) HAVING n > 20",
                [$eid, $uid]
            );
        } catch (Throwable $e) { return ''; }
        if (!$d) return '';
        // Fechas de la propia base, formato fijo YYYY-MM-DD: no hay entrada de
        // usuario aquí. Van interpoladas porque el número de placeholders
        // cambiaría en cada llamada.
        $f = [];
        foreach ($d as $r) if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$r['d'])) $f[] = "'" . $r['d'] . "'";
        return $f ? " AND DATE(c.created_at) NOT IN (" . implode(',', $f) . ")" : '';
    }

    /**
     * Días de la última semana con señal de que el asesor trabajó.
     *
     * Dos fuentes unidas a propósito: actividad_log NO registra "creó una
     * cotización" (crear.php nunca llama a ActividadScore::registrar), así que
     * un asesor que se pasó la semana cotizando podría salir con 0 días activos.
     * Sumar los días en que creó cotizaciones tapa ese hueco.
     *
     * Sirve para distinguir "bajó el ritmo" de "estuvo fuera". NO es un control
     * de asistencia: el sistema no tiene bandera de vacaciones ni calendario
     * laboral, y esto es lo más cerca que se puede estar con lo que hay.
     */
    private static function _dias_con_señal(int $eid, int $uid): int
    {
        try {
            return (int)DB::val(
                "SELECT COUNT(*) FROM (
                    SELECT DATE(created_at) AS d FROM actividad_log
                     WHERE usuario_id = ? AND tipo IN ('radar_view','quote_view','client_view')
                       AND created_at >= NOW() - INTERVAL 7 DAY
                    UNION
                    SELECT DATE(created_at) AS d FROM cotizaciones
                     WHERE empresa_id = ? AND COALESCE(vendedor_id, usuario_id) = ?
                       AND created_at >= NOW() - INTERVAL 7 DAY
                 ) x",
                [$uid, $eid, $uid]
            );
        } catch (Throwable $e) { return 0; }   // sin actividad_log: no se juzga la ausencia
    }

    /**
     * Semáforo, en función pura y aparte para poder probarlo sin base de datos
     * (mismo patrón que RitmoAsesor::_citas_semaforo).
     *
     *   gris    — no hay con qué juzgar, o no estuvo
     *   rojo    — cayó a menos de la mitad de su ritmo
     *   alto    — cotizó vez y media o más. NO es verde: cotizar de más puede
     *             ser prospección o puede ser regar. Lo dice, no lo aplaude.
     *   verde   — en su ritmo
     */
    public static function semaforo(int $n7, float $base_wk, int $dias_señal): string
    {
        if ($base_wk < self::BASE_MIN) return 'gris';
        // La presencia solo EXCUSA un bajón; no condiciona lo demás. Puesta
        // antes de todo, un asesor que despachó nueve cotizaciones en un mismo
        // día salía con un solo día de señal y el sistema lo leía como ausente
        // — justo al revés de lo que pasó. Quien cotiza mucho, obviamente
        // estuvo.
        if ($n7 >= $base_wk * self::SUBIDA) return 'alto';
        if ($n7 <  $base_wk * self::CAIDA)  {
            return $dias_señal < self::DIAS_MIN ? 'gris' : 'rojo';
        }
        return 'verde';
    }

    /**
     * Cuánto lleva sin cotizar — la alarma que el promedio semanal NO da.
     *
     * Un hueco de tres días a media semana es invisible para un promedio: la
     * semana todavía no cierra y el número no se ha movido. Y es justo cuando
     * sirve verlo, no el lunes siguiente.
     *
     * SE CUENTA EN LOS DÍAS QUE ÉL TRABAJA, no en días de calendario. Sin esto
     * el lunes siempre marcaría "3 días" para quien descansa sábado y domingo,
     * y la alarma se volvería ruido semanal que nadie mira. Qué días trabaja
     * sale de su propia historia —los días de la semana en que se le ha visto
     * cotizar—, no de suponerle un horario: hay mueblerías que abren sábado y
     * hay quien libra el lunes.
     *
     * El umbral también es suyo: su hueco normal entre cotizaciones, por
     * HUECO_VECES. Quien cotiza ocho por semana debe hacerlo casi a diario y
     * tres días sin cotizar es raro; quien cotiza dos, no.
     *
     * @return array{dias_sin:?int,ultima:?string,hueco_normal:?float,hueco_alerta:bool}
     */
    private static function _hueco(string $ultima, string $dows, float $base_wk): array
    {
        $nulo = ['dias_sin'=>null,'ultima'=>null,'hueco_normal'=>null,'hueco_alerta'=>false,
                  'dias_dentro'=>[]];
        if ($ultima === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ultima)) return $nulo;

        // DAYOFWEEK de MySQL: 1=domingo … 7=sábado.
        $labora = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $dows)), fn($d) => $d >= 1 && $d <= 7)));

        try {
            $hoy = new DateTimeImmutable('today');
            $ult = new DateTimeImmutable($ultima);
        } catch (Throwable $e) { return $nulo; }
        if ($ult > $hoy) return $nulo;

        // Sin hábito legible, se informa el hueco en días de calendario pero no
        // se alerta: no hay con qué saber si tres días son muchos para él.
        if (count($labora) < self::DOW_MIN) {
            return ['dias_sin' => (int)$ult->diff($hoy)->days, 'ultima' => $ultima,
                    'hueco_normal' => null, 'hueco_alerta' => false];
        }

        // Se cuentan los días POSTERIORES a la última cotización, hoy incluido,
        // y solo los que él trabaja.
        $dias = 0;
        for ($d = $ult->modify('+1 day'); $d <= $hoy; $d = $d->modify('+1 day')) {
            if (in_array((int)$d->format('w') + 1, $labora, true)) $dias++;
        }

        $normal = $base_wk > 0 ? count($labora) / $base_wk : null;   // días entre cotización
        $alerta = $base_wk >= self::BASE_MIN && $normal !== null
               && $dias >= max(2, (int)ceil($normal * self::HUECO_VECES));

        return ['dias_sin'=>$dias, 'ultima'=>$ultima, 'hueco_normal'=>$normal, 'hueco_alerta'=>$alerta];
    }

    /**
     * Días de la última semana en que ENTRÓ al sistema y no cotizó ninguna.
     *
     * Es el dato más concreto que puede dar esta sección: no "bajaste 40%" sino
     * "el martes y el miércoles estuviste y no salió ninguna".
     *
     * OJO CON LO QUE ESTO **NO** PRUEBA. Un día en el sistema sin cotizar no es
     * un día perdido: el asesor pudo pasarlo dando seguimiento, cerrando una
     * venta o atendiendo en piso — nada de eso crea una cotización. Por eso la
     * frase enuncia el hecho y no lo califica, y por eso solo aparece cuando ya
     * hay una alarma encendida: como evidencia de algo que ya se detectó, no
     * como acusación por sí sola.
     *
     * @return list<string> fechas 'Y-m-d', de la más vieja a la más reciente
     */
    private static function _dias_dentro_sin_cotizar(int $eid, int $uid): array
    {
        try {
            $f = DB::query(
                "SELECT DISTINCT DATE(a.created_at) AS d
                   FROM actividad_log a
                  WHERE a.usuario_id = ?
                    AND a.tipo IN ('radar_view','quote_view','client_view')
                    AND a.created_at >= NOW() - INTERVAL 7 DAY
                    AND NOT EXISTS (
                        SELECT 1 FROM cotizaciones c
                         WHERE c.empresa_id = ? AND COALESCE(c.vendedor_id, c.usuario_id) = ?
                           AND DATE(c.created_at) = DATE(a.created_at))
                  ORDER BY d",
                [$uid, $eid, $uid]
            );
        } catch (Throwable $e) { return []; }
        return array_column($f, 'd');
    }

    /** "mar 2", "mié 3" — date('D') viene en inglés. */
    private static function _dia_corto(string $ymd): string
    {
        $t = strtotime($ymd);
        $d = ['Sun'=>'dom','Mon'=>'lun','Tue'=>'mar','Wed'=>'mié','Thu'=>'jue','Fri'=>'vie','Sat'=>'sáb'];
        return ($d[date('D', $t)] ?? '') . ' ' . (int)date('j', $t);
    }

    /**
     * "el mar 2 y mié 3" · "el sáb 29, lun 31, mar 1 y mié 2".
     *
     * SE LISTAN TODOS. Recortarlos a tres y cerrar con "(y 1 día más)" dejaba
     * al lector preguntándose cuál era ese día — que es justo el dato por el
     * que existe la frase. Son siete como mucho: caben.
     */
    private static function _lista_dias(array $fechas): string
    {
        $m = array_map([self::class, '_dia_corto'], $fechas);
        if (count($m) === 1) return "el {$m[0]}";
        return 'el ' . implode(', ', array_slice($m, 0, -1)) . ' y ' . end($m);
    }

    /**
     * La semana del asesor contra su propio ritmo.
     *
     * @return array{n7:int,abiertas7:int,base_wk:float,estado:string,dias_señal:int,pct:?float}
     *   n7        cotizaciones de los últimos 7 días
     *   abiertas7 cuántas de esas ya abrió el cliente (para no leer el volumen solo)
     *   base_wk   su ritmo semanal, CRUDO (el semáforo compara contra el float:
     *             1.75 no es 2; el texto redondea aparte — misma lección que
     *             RitmoAsesor::_citas)
     *   pct       n7 / base_wk, o null si no hay vara
     */
    public static function semana(int $empresa_id, int $usuario_id): array
    {
        // El retorno vacío trae TODAS las claves: quien lo lea no puede toparse
        // con un índice que no existe solo porque la consulta falló.
        $vacio = ['n7'=>0,'abiertas7'=>0,'base_wk'=>0.0,'estado'=>'gris','dias_señal'=>0,'pct'=>null,
                  'dias_sin'=>null,'ultima'=>null,'hueco_normal'=>null,'hueco_alerta'=>false,
                  'dias_dentro'=>[]];
        try {
            $no_imp = self::_sin_import($empresa_id, $usuario_id);
            $w      = self::_where();
            // La vara EXCLUYE la semana en curso (días 8 al 35). Si la incluyera,
            // la semana mala se metería en su propio promedio y amortiguaría la
            // señal — el mismo principio por el que close_rate_hist mira solo lo
            // anterior a la ventana.
            // `ultima` y `dows` viajan en la MISMA consulta: el hueco desde su
            // última cotización y los días de la semana en que trabaja salen
            // gratis de las filas que ya se están leyendo.
            $r = DB::row(
                "SELECT
                    SUM(c.created_at >= NOW() - INTERVAL 7 DAY) AS n7,
                    SUM(c.created_at >= NOW() - INTERVAL 7 DAY AND c.visitas > 0) AS ab7,
                    SUM(c.created_at <  NOW() - INTERVAL 7 DAY) AS nbase,
                    MAX(DATE(c.created_at)) AS ultima,
                    GROUP_CONCAT(DISTINCT DAYOFWEEK(c.created_at)) AS dows
                 FROM cotizaciones c
                 WHERE $w $no_imp
                   AND c.created_at >= NOW() - INTERVAL " . (7 + 7 * self::SEMANAS_BASE) . " DAY",
                [$empresa_id, $usuario_id]
            );
        } catch (Throwable $e) { return $vacio; }

        $n7      = (int)($r['n7']    ?? 0);
        $ab7     = (int)($r['ab7']   ?? 0);
        $base_wk = ((int)($r['nbase'] ?? 0)) / self::SEMANAS_BASE;
        $dias    = self::_dias_con_señal($empresa_id, $usuario_id);
        $hueco   = self::_hueco((string)($r['ultima'] ?? ''), (string)($r['dows'] ?? ''), $base_wk);
        $estado  = self::semaforo($n7, $base_wk, $dias);

        // Solo se buscan los días "entró y no cotizó" cuando ya hay algo que
        // explicar. En un asesor que va en su ritmo son ruido, y la consulta
        // se ahorra: en la tabla de Reportes esto corre una vez por asesor.
        $dentro = ($estado === 'rojo' || !empty($hueco['hueco_alerta']))
            ? self::_dias_dentro_sin_cotizar($empresa_id, $usuario_id) : [];

        return [
            'n7'         => $n7,
            'abiertas7'  => $ab7,
            'base_wk'    => $base_wk,
            'estado'     => $estado,
            'dias_señal' => $dias,
            'pct'        => $base_wk > 0 ? $n7 / $base_wk : null,
            'dias_dentro'=> $dentro,
        ] + $hueco;
    }

    /**
     * Serie semanal para el módulo Reportes: una fila por semana, de la más
     * reciente hacia atrás. Cuenta lo mismo que semana() — misma receta, mismo
     * reloj— para que la tabla y la alerta no puedan contradecirse.
     *
     * @return list<array{semana:string,ini:string,n:int,abiertas:int,cerradas:int}>
     */
    public static function historico(int $empresa_id, int $usuario_id, int $semanas = 12): array
    {
        $semanas = max(1, min(52, $semanas));
        try {
            $no_imp = self::_sin_import($empresa_id, $usuario_id);
            $w      = self::_where();
            $filas = DB::query(
                // `ini` es el LUNES de la semana, no la primera cotización de
                // esa semana. Con MIN(created_at) los encabezados de la tabla
                // saltaban irregular —02/Sep, 24/Aug, 17/Aug— porque cada uno
                // era el día en que ese asesor arrancó, no el inicio de semana.
                "SELECT YEARWEEK(c.created_at, 1)                       AS semana,
                        MIN(DATE_SUB(DATE(c.created_at), INTERVAL WEEKDAY(c.created_at) DAY)) AS ini,
                        COUNT(*)                                         AS n,
                        SUM(c.visitas > 0)                               AS abiertas,
                        SUM(EXISTS (SELECT 1 FROM ventas v
                                     WHERE v.cotizacion_id = c.id
                                       AND v.estado <> 'cancelada' AND v.pagado > 0)) AS cerradas
                   FROM cotizaciones c
                  WHERE $w $no_imp
                    AND c.created_at >= NOW() - INTERVAL " . (7 * $semanas) . " DAY
                  GROUP BY YEARWEEK(c.created_at, 1)
                  ORDER BY semana DESC",
                [$empresa_id, $usuario_id]
            );
        } catch (Throwable $e) { return []; }

        return array_map(fn($f) => [
            'semana'   => (string)$f['semana'],
            'ini'      => (string)$f['ini'],
            'n'        => (int)$f['n'],
            'abiertas' => (int)$f['abiertas'],
            'cerradas' => (int)$f['cerradas'],
        ], $filas);
    }

    /**
     * Las dos frases del reporte del asesor. Máximo dos renglones: el reporte
     * está calibrado para UNA hoja y cada sección gasta ese presupuesto.
     * Devuelve [] cuando no hay nada honesto que decir — el render omite la
     * sección vacía en vez de rellenar.
     */
    public static function frases(array $s): array
    {
        $n = $s['n7']; $base = $s['base_wk'];
        $b = $base >= 10 ? round($base) : round($base, 1);   // 2.5 dice más que 3; 12.4 no
        $cot = fn(int $k) => $k === 1 ? '1 cotización' : "$k cotizaciones";

        if ($s['estado'] === 'gris') {
            // Sin vara suficiente: se informa el dato pelón, sin juicio.
            if ($base < self::BASE_MIN) {
                return $n > 0 || $base > 0
                    ? [$cot($n) . " esta semana. Todavía no hay historia suficiente para saber si es tu ritmo normal."]
                    : [];
            }
            return [$cot($n) . " esta semana, contra un ritmo de $b. No se juzga: no hubo actividad tuya en el sistema estos días."];
        }

        $out = [$cot($n) . " esta semana — tu ritmo normal son $b por semana."];

        // EL HUECO MANDA sobre el promedio. Llevar tres días sin cotizar es
        // información de HOY y se arregla hoy; que la semana vaya 40% abajo ya
        // no se puede cambiar. Y solo cabe un renglón más: el reporte está
        // calibrado para una hoja.
        // Los días en que entró y no salió ninguna: el dato más concreto de la
        // sección. Se ENUNCIA, no se califica — un día sin cotizar pudo irse en
        // seguimiento, en cerrar una venta o atendiendo en piso.
        $dd = $s['dias_dentro'] ?? [];
        $dentro = $dd ? ' Entraste ' . self::_lista_dias($dd) . ' sin cotizar.' : '';

        if (!empty($s['hueco_alerta'])) {
            $d = (int)$s['dias_sin'];
            $out[] = "Llevas $d día" . ($d === 1 ? '' : 's') . " de trabajo sin cotizar — la última fue el "
                   . date('d/M', strtotime((string)$s['ultima'])) . "."
                   . ($dentro !== '' ? $dentro : " A tu ritmo cotizas cada "
                       . (($s['hueco_normal'] ?? 0) < 1.5 ? 'día' : round((float)$s['hueco_normal']) . ' días') . ".");
            return $out;
        }

        if ($s['estado'] === 'rojo') {
            $caida = $base > 0 ? (int)round((1 - $n / $base) * 100) : 0;
            $out[] = "Bajaste {$caida}%. Estuviste {$s['dias_señal']} de 7 días en el sistema, así que no fue ausencia: lo que bajó es la prospección."
                   . $dentro;
        } elseif ($s['estado'] === 'alto') {
            // Volumen sin apertura es regar, no prospectar. Nunca se felicita
            // el número solo.
            $out[] = $s['abiertas7'] > 0
                ? "Subiste el ritmo y {$s['abiertas7']} de esas ya las abrió el cliente — la prospección está entrando."
                : "Subiste el ritmo, pero ninguna la ha abierto el cliente todavía. Revisa que estés cotizando a quien de verdad pidió.";
        }
        return $out;
    }
}
