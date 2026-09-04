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

echo "\n10) NADA REVIENTA SIN DATOS\n";
$s99 = RitmoCot::semana(EMP, 99);
chk('asesor sin cotizaciones → gris', $s99['estado'], 'gris');
chk('sin frases que inventar',        RitmoCot::frases($s99), []);
chk('histórico vacío es lista vacía', RitmoCot::historico(EMP, 99, 12), []);

echo "\n" . ($fail === 0
    ? "✓ SIMULACIÓN RITMO OK — $ok comprobaciones contra MariaDB real\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
