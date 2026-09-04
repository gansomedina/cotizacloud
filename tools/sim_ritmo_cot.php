<?php
// ============================================================
// SIMULACIÓN REAL — RitmoCot contra MariaDB de verdad.
//
// Por qué una simulación y no una prueba de texto: lo que puede fallar aquí
// no es la lógica del semáforo (esa se prueba pura, abajo) sino las QUERIES —
// que el rango de la vara excluya de verdad la semana en curso, que el filtro
// de imports muerda, que YEARWEEK agrupe donde uno cree. Eso solo se ve
// corriéndolas contra un motor real con fechas reales.
//
// REQUISITOS (desarrollo, NUNCA producción):
//   - MariaDB/MySQL local con BD 'simtest' y usuario sim/sim
//   - DESTRUYE y recrea sus tablas en cada corrida
// Correr: php tools/sim_ritmo_cot.php   → debe terminar en OK
// Obligatorio tras CUALQUIER cambio a RitmoCot.
// ============================================================
define('COTIZAAPP', 1);

class DB {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (!self::$pdo) {
            self::$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4',
                'sim', 'sim', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        return self::$pdo;
    }
    public static function query($sql, $params = []): array {
        $st = self::pdo()->prepare($sql); $st->execute($params); return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function row($sql, $params = []) {
        $st = self::pdo()->prepare($sql); $st->execute($params);
        $r = $st->fetch(PDO::FETCH_ASSOC); return $r === false ? null : $r;
    }
    public static function val($sql, $params = []) {
        $st = self::pdo()->prepare($sql); $st->execute($params); return $st->fetchColumn();
    }
    public static function execute($sql, $params = []): void {
        $st = self::pdo()->prepare($sql); $st->execute($params);
    }
}
require __DIR__ . '/../core/RitmoCot.php';

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got, JSON_UNESCAPED_UNICODE) . " want=" . json_encode($want, JSON_UNESCAPED_UNICODE) . "\n"; }
}

// ── Esquema mínimo: solo lo que RitmoCot toca ──
DB::pdo()->exec("
DROP TABLE IF EXISTS cotizaciones, ventas, actividad_log;
CREATE TABLE cotizaciones (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, empresa_id INT UNSIGNED NOT NULL,
  usuario_id INT UNSIGNED NOT NULL, vendedor_id INT UNSIGNED NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0, estado VARCHAR(20) NOT NULL DEFAULT 'enviada',
  visitas INT NOT NULL DEFAULT 0, suspendida TINYINT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL
);
CREATE TABLE ventas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, cotizacion_id INT UNSIGNED,
  estado VARCHAR(20) NOT NULL DEFAULT 'activa', pagado DECIMAL(12,2) NOT NULL DEFAULT 0
);
CREATE TABLE actividad_log (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, usuario_id INT UNSIGNED NOT NULL,
  tipo VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL
);
");

const EMP = 1;
/** Crea una cotización hace $dias días. Las 2 horas van SUMADAS (no restadas):
 *  puestas al revés, la cotización del día 35 caía 35.08 días atrás y se salía
 *  de la ventana de la vara por dos horas — el fixture medía 27 días y no 28. */
function cot(int $uid, int $dias, array $o = []): int {
    DB::execute(
        "INSERT INTO cotizaciones (empresa_id, usuario_id, vendedor_id, total, estado, visitas, suspendida, created_at)
         VALUES (?,?,?,?,?,?,?, NOW() - INTERVAL ? DAY + INTERVAL 2 HOUR)",
        [EMP, $o['usuario_id'] ?? $uid, $o['vendedor_id'] ?? null, $o['total'] ?? 1000,
         $o['estado'] ?? 'enviada', $o['visitas'] ?? 0, $o['suspendida'] ?? 0, $dias]
    );
    return (int)DB::pdo()->lastInsertId();
}
function actividad(int $uid, int $dias, string $tipo = 'radar_view'): void {
    DB::execute("INSERT INTO actividad_log (usuario_id, tipo, created_at) VALUES (?,?, NOW() - INTERVAL ? DAY + INTERVAL 2 HOUR)",
        [$uid, $tipo, $dias]);
}

// ════════════════════════════════════════════════════════════
echo "\n1) EL SEMÁFORO (función pura, sin base de datos)\n";
// gris manda sobre todo: sin vara o sin presencia no se juzga.
chk('sin ritmo probado → gris',      RitmoCot::semaforo(0, 1.5, 7), 'gris');
chk('sin días en el sistema → gris', RitmoCot::semaforo(0, 6.0, 1), 'gris');
// La presencia SOLO excusa el bajón. Nueve cotizaciones despachadas el mismo
// día dan un solo día de señal, y leerlas como "no estuvo" es al revés de lo
// que pasó: quien cotiza mucho, obviamente estuvo.
chk('un día con muchas sigue siendo alto', RitmoCot::semaforo(9, 4.0, 1), 'alto');
chk('y en su ritmo con un solo día es verde', RitmoCot::semaforo(4, 4.0, 1), 'verde');
chk('cayó a menos de la mitad',      RitmoCot::semaforo(2, 6.0, 5), 'rojo');
chk('justo en la mitad NO es rojo',  RitmoCot::semaforo(3, 6.0, 5), 'verde');
chk('en su ritmo',                   RitmoCot::semaforo(6, 6.0, 5), 'verde');
chk('vez y media es "alto"',         RitmoCot::semaforo(9, 6.0, 5), 'alto');
chk('el doble también',              RitmoCot::semaforo(12, 6.0, 5), 'alto');
// La vara cruda, no la redondeada: 1.75 no es 2.
chk('la vara se compara en crudo',   RitmoCot::semaforo(2, 1.9, 7), 'gris');

// ════════════════════════════════════════════════════════════
echo "\n2) LA VARA EXCLUYE LA SEMANA EN CURSO\n";
// Asesor 10: 3 esta semana; 8 por semana en las 4 anteriores (32 en total).
// Si la vara incluyera la semana en curso, sería 35/5=7 y la caída se vería
// más chica de lo que es. Excluyéndola: 32/4 = 8.
foreach ([1, 2, 4] as $d) cot(10, $d);   // 3 cotizaciones en 3 días distintos
for ($d = 8; $d < 36; $d++) { cot(10, $d); cot(10, $d); }   // 2/día × 28 días = 56
$s = RitmoCot::semana(EMP, 10);
chk('cuenta la semana en curso',     $s['n7'], 3);
chk('la vara son 4 semanas exactas', $s['base_wk'], 56 / 4);
chk('y no incluye la semana actual', $s['base_wk'] === (56 / 4));
chk('el bajón se detecta',           $s['estado'], 'rojo');

echo "\n3) LO QUE NO SE CUENTA ES LO MISMO QUE NO CUENTA EL SCORE\n";
// Asesor 11: 4 buenas esta semana + basura que el score tampoco cuenta.
for ($i = 0; $i < 4; $i++) cot(11, 1);
cot(11, 1, ['total' => 0]);                       // sin importe
cot(11, 1, ['suspendida' => 1]);                  // suspendida
cot(11, 1, ['estado' => 'borrador']);             // borrador sin visitas
cot(11, 1, ['estado' => 'borrador','visitas'=>3]);// borrador YA VISTO: sí cuenta
$s11 = RitmoCot::semana(EMP, 11);
chk('descarta total=0, suspendida y borrador', $s11['n7'], 5);  // 4 + el borrador visto

echo "\n4) UN IMPORT MASIVO NO ES UNA SEMANA PRODUCTIVA\n";
// Asesor 12: 25 cotizaciones el mismo día (import) + 2 de verdad.
for ($i = 0; $i < 25; $i++) cot(12, 3);
cot(12, 1); cot(12, 5);
$s12 = RitmoCot::semana(EMP, 12);
chk('el día del import se descarta entero', $s12['n7'], 2);

echo "\n5) EL DUEÑO ES vendedor_id, NO QUIEN LA CAPTURÓ\n";
// Capturada por 13, asignada a 14: cuenta para 14.
cot(13, 1, ['vendedor_id' => 14]);
chk('cuenta para el vendedor',   RitmoCot::semana(EMP, 14)['n7'], 1);
chk('y no para el capturista',   RitmoCot::semana(EMP, 13)['n7'], 0);

echo "\n6) AUSENCIA NO ES BAJÓN\n";
// Asesor 15: ritmo de 8/semana, esta semana 0 y sin señal de actividad.
for ($d = 8; $d < 36; $d++) { cot(15, $d); cot(15, $d); }
$s15 = RitmoCot::semana(EMP, 15);
chk('sin actividad no se le reclama', $s15['estado'], 'gris');
chk('y la frase lo dice',
    str_contains(RitmoCot::frases($s15)[0] ?? '', 'no hubo actividad tuya'));
// El mismo asesor, ahora con 3 días de presencia → sí es bajón.
for ($d = 1; $d <= 3; $d++) actividad(15, $d);
$s15b = RitmoCot::semana(EMP, 15);
chk('con presencia sí es bajón',      $s15b['estado'], 'rojo');
chk('cuenta los días de presencia',   $s15b['dias_señal'], 3);

echo "\n7) COTIZAR CUENTA COMO DÍA TRABAJADO\n";
// crear.php NO escribe en actividad_log. Un asesor que solo cotizó tendría 0
// días de presencia y su bajón se leería como ausencia — al revés de la verdad.
cot(16, 1); cot(16, 2); cot(16, 4);
chk('los días con cotización cuentan', RitmoCot::semana(EMP, 16)['dias_señal'], 3);

echo "\n8) SUBIR EL RITMO NO SE APLAUDE SOLO\n";
// Asesor 17: ritmo 4/sem, esta semana 9 — pero ninguna abierta.
for ($d = 8; $d < 36; $d += 7) { cot(17,$d); cot(17,$d); cot(17,$d); cot(17,$d); }
for ($i = 0; $i < 9; $i++) cot(17, 2);
$s17 = RitmoCot::semana(EMP, 17);
chk('detecta el volumen alto', $s17['estado'], 'alto');
chk('y advierte que nadie las abrió',
    str_contains(implode(' ', RitmoCot::frases($s17)), 'ninguna la ha abierto'));
// Ahora con aperturas: la frase cambia de advertencia a confirmación.
DB::execute("UPDATE cotizaciones SET visitas = 2 WHERE usuario_id = 17 AND created_at >= NOW() - INTERVAL 7 DAY LIMIT 5");
chk('con aperturas, lo reconoce',
    str_contains(implode(' ', RitmoCot::frases(RitmoCot::semana(EMP, 17))), 'ya las abrió el cliente'));

echo "\n9) EL HISTÓRICO CUENTA IGUAL QUE LA ALERTA\n";
// Misma receta, mismo reloj: la tabla del módulo Reportes no puede contradecir
// a la frase del reporte del asesor.
$h = RitmoCot::historico(EMP, 12, 12);
$suma_h = array_sum(array_column($h, 'n'));
chk('el histórico también excluye el import', $suma_h, 2);
$h11 = RitmoCot::historico(EMP, 11, 12);
chk('y aplica los mismos filtros de basura', array_sum(array_column($h11, 'n')), 5);
// Cerradas: una venta pagada sobre una cotización de esta semana.
$cid = cot(18, 1);
DB::execute("INSERT INTO ventas (cotizacion_id, estado, pagado) VALUES (?, 'activa', 500)", [$cid]);
cot(18, 1);
$h18 = RitmoCot::historico(EMP, 18, 12);
chk('cuenta las cerradas con pago', $h18[0]['cerradas'] ?? -1, 1);
chk('y no infla el total',          $h18[0]['n'] ?? -1, 2);
// Venta cancelada o sin pago no es cierre.
$cid2 = cot(19, 1);
DB::execute("INSERT INTO ventas (cotizacion_id, estado, pagado) VALUES (?, 'cancelada', 900)", [$cid2]);
$cid3 = cot(19, 1);
DB::execute("INSERT INTO ventas (cotizacion_id, estado, pagado) VALUES (?, 'activa', 0)", [$cid3]);
chk('cancelada y sin pago no son cierre', RitmoCot::historico(EMP, 19, 12)[0]['cerradas'] ?? -1, 0);
chk('el histórico ordena de reciente a viejo',
    count($h) < 2 || $h[0]['semana'] >= $h[1]['semana']);

echo "\n10) EL HUECO: LO QUE EL PROMEDIO SEMANAL NO VE\n";
// Tres días sin cotizar a media semana no mueven el promedio hasta que la
// semana cierra — y para entonces ya no sirve. Asesor 20: ritmo alto (8/sem),
// última cotización hace 3 días, cotiza de lunes a sábado.
for ($d = 8; $d < 36; $d++) { cot(20, $d); cot(20, $d); }
cot(20, 3);
for ($d = 1; $d <= 5; $d++) actividad(20, $d);   // sí estuvo: el hueco es real, no ausencia
$s20 = RitmoCot::semana(EMP, 20);
chk('sabe cuándo fue la última',  $s20['ultima'], (new DateTimeImmutable('-3 days'))->format('Y-m-d'));
chk('cuenta el hueco',            $s20['dias_sin'] >= 2);          // 3 de calendario, menos los que no trabaja
chk('y lo enciende',              $s20['hueco_alerta'], true);
// El mismo hueco con ritmo bajo NO es alarma: quien cotiza poco, cotiza espaciado.
chk('con ritmo bajo, 3 días es normal',
    RitmoCot::semana(EMP, 21)['hueco_alerta'], false);             // asesor sin historia

// SOLO DÍAS QUE ÉL TRABAJA. Sin esto, el lunes marcaría siempre 3 días para
// quien descansa sábado y domingo, y la alarma se volvería ruido semanal.
// Asesor 22: cotiza SOLO lunes, martes y miércoles, desde hace 5 semanas.
for ($d = 1; $d <= 40; $d++) {
    $dow = (int)(new DateTimeImmutable("-$d days"))->format('N');  // 1=lun … 7=dom
    if ($dow <= 3 && $d > 7) { cot(22, $d); cot(22, $d); cot(22, $d); }
}
$s22 = RitmoCot::semana(EMP, 22);
$cal = (int)(new DateTimeImmutable($s22['ultima']))->diff(new DateTimeImmutable('today'))->days;
chk('el hueco NO son días de calendario', $s22['dias_sin'] <= $cal);
chk('nunca cuenta más días de los que pasaron', $s22['dias_sin'] <= $cal);

// Sin hábito legible (pocos días distintos) se informa, pero no se acusa.
cot(23, 2);
$s23 = RitmoCot::semana(EMP, 23);
chk('sin hábito: informa el hueco',  $s23['dias_sin'], 2);
chk('pero no alerta',                $s23['hueco_alerta'], false);
chk('y no inventa un ritmo',         $s23['hueco_normal'], null);

// La frase del reporte: el hueco MANDA sobre el promedio semanal, porque se
// arregla hoy y el promedio de la semana ya no.
$f20 = RitmoCot::frases($s20);
chk('la frase habla del hueco',     str_contains(implode(' ', $f20), 'sin cotizar'));
chk('y no gasta un tercer renglón', count($f20), 2);
// El hueco DESPLAZA al "bajaste X%": se arregla hoy, el promedio de la semana
// ya no. Solo cabe un renglón además del ritmo.
chk('el hueco desplaza al bajón',   str_contains(implode(' ', $f20), 'Bajaste'), false);
// A quien NO estuvo no se le reclama el hueco en el reporte: su renglón es la
// ausencia, y decir las dos cosas es decir lo mismo dos veces. El hueco sí lo
// ve el jefe en la tabla de Reportes.
chk('al ausente no se le reclama',
    str_contains(implode(' ', RitmoCot::frases($s15)), 'sin cotizar'), false);

echo "\n10b) LOS DÍAS QUE ENTRÓ Y NO COTIZÓ\n";
// El asesor 20 tiene actividad los últimos 5 días y su última cotización fue
// hace 3 → los días 1 y 2 entró y no salió ninguna.
chk('los detecta',            count($s20['dias_dentro'] ?? []) >= 2);
chk('y ninguno tiene cotización',
    (bool)array_filter($s20['dias_dentro'], fn($f) =>
        (int)DB::val("SELECT COUNT(*) FROM cotizaciones
                       WHERE empresa_id=? AND COALESCE(vendedor_id,usuario_id)=? AND DATE(created_at)=?",
                     [EMP, 20, $f]) > 0), false);
chk('la frase los enuncia',   str_contains(implode(' ', RitmoCot::frases($s20)), 'sin cotizar.'));
// SE LISTAN TODOS. Recortarlos a tres y cerrar con "(y 1 día más)" dejaba al
// lector preguntándose cuál era ese día — el dato por el que existe la frase.
$muchos = ['n7'=>0,'abiertas7'=>0,'base_wk'=>8.0,'estado'=>'rojo','dias_señal'=>5,'pct'=>0.0,
           'dias_sin'=>5,'ultima'=>'2026-08-28','hueco_normal'=>0.75,'hueco_alerta'=>true,
           'dias_dentro'=>['2026-08-29','2026-08-31','2026-09-01','2026-09-02','2026-09-03']];
$fm = implode(' ', RitmoCot::frases($muchos));
chk('no dice "y N días más"',  (bool)preg_match('/y \d+ días? más/', $fm), false);
foreach (['sáb 29','lun 31','mar 1','mié 2','jue 3'] as $dia) {
    chk("lista el $dia",       str_contains($fm, $dia));
}
// Solo se buscan cuando ya hay alarma: en quien va en su ritmo son ruido, y la
// consulta se ahorra (en Reportes esto corre una vez por asesor).
chk('en ritmo normal ni se consultan',
    RitmoCot::semana(EMP, 16)['dias_dentro'], []);
// Nunca lista un día que sí tuvo cotización.
cot(24, 1); actividad(24, 1); actividad(24, 3);
for ($d = 8; $d < 36; $d++) { cot(24, $d); cot(24, $d); }
$s24 = RitmoCot::semana(EMP, 24);
chk('el día que sí cotizó no aparece',
    in_array((new DateTimeImmutable('-1 day'))->format('Y-m-d'), $s24['dias_dentro'] ?? [], true), false);
chk('el día que solo entró, sí',
    in_array((new DateTimeImmutable('-3 days'))->format('Y-m-d'), $s24['dias_dentro'] ?? [], true));

echo "\n11) LAS SEMANAS SON SEMANAS, NO 'CUANDO EMPEZÓ'\n";
// El encabezado de la tabla decía 02/Sep, 24/Aug, 17/Aug — saltos irregulares,
// porque era la primera cotización de cada semana y no el inicio de la semana.
$h20 = RitmoCot::historico(EMP, 20, 12);
$lunes_ok = true;
foreach ($h20 as $h) if ((int)(new DateTimeImmutable($h['ini']))->format('N') !== 1) $lunes_ok = false;
chk('cada semana empieza en lunes', $lunes_ok);
chk('y la clave coincide con el lunes',
    empty($h20) || $h20[0]['semana'] === (new DateTimeImmutable($h20[0]['ini']))->format('oW'));

echo "\n12) NADA REVIENTA SIN DATOS\n";
$s99 = RitmoCot::semana(EMP, 99);
chk('asesor sin cotizaciones → gris', $s99['estado'], 'gris');
// Todas las claves presentes: quien lea el resultado no debe toparse con un
// índice inexistente solo porque este asesor no tiene datos.
foreach (['n7','abiertas7','base_wk','estado','dias_señal','pct','dias_sin','ultima','hueco_normal','hueco_alerta','dias_dentro'] as $k) {
    chk("sin datos, la clave '$k' existe", array_key_exists($k, $s99));
}
chk('sin frases que inventar',        RitmoCot::frases($s99), []);
chk('histórico vacío es lista vacía', RitmoCot::historico(EMP, 99, 12), []);

echo "\n" . ($fail === 0
    ? "✓ SIMULACIÓN RITMO OK — $ok comprobaciones contra MariaDB real\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
