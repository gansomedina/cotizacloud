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
        $vacio = ['n7'=>0,'abiertas7'=>0,'base_wk'=>0.0,'estado'=>'gris','dias_señal'=>0,'pct'=>null];
        try {
            $no_imp = self::_sin_import($empresa_id, $usuario_id);
            $w      = self::_where();
            // La vara EXCLUYE la semana en curso (días 8 al 35). Si la incluyera,
            // la semana mala se metería en su propio promedio y amortiguaría la
            // señal — el mismo principio por el que close_rate_hist mira solo lo
            // anterior a la ventana.
            $r = DB::row(
                "SELECT
                    SUM(c.created_at >= NOW() - INTERVAL 7 DAY) AS n7,
                    SUM(c.created_at >= NOW() - INTERVAL 7 DAY AND c.visitas > 0) AS ab7,
                    SUM(c.created_at <  NOW() - INTERVAL 7 DAY) AS nbase
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

        return [
            'n7'         => $n7,
            'abiertas7'  => $ab7,
            'base_wk'    => $base_wk,
            'estado'     => self::semaforo($n7, $base_wk, $dias),
            'dias_señal' => $dias,
            'pct'        => $base_wk > 0 ? $n7 / $base_wk : null,
        ];
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
                "SELECT YEARWEEK(c.created_at, 1)                       AS semana,
                        MIN(DATE(c.created_at))                          AS ini,
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

        if ($s['estado'] === 'rojo') {
            $caida = $base > 0 ? (int)round((1 - $n / $base) * 100) : 0;
            $out[] = "Bajaste {$caida}%. Estuviste {$s['dias_señal']} de 7 días en el sistema, así que no fue ausencia: lo que bajó es la prospección.";
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
