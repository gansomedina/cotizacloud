<?php
// ============================================================
// PRUEBA REAL de la bitácora de planes (planes_log / plan_log)
// contra MariaDB de verdad.
//
// Lo que garantiza, y por qué importa cada una:
//   1. Un cambio de plan queda registrado CON el plan anterior — que es
//      justo el dato que hoy no existe en ningún lado.
//   2. Un no-cambio NO ensucia la bitácora.
//   3. 'forzar' sí registra los hechos que no mueven el plan (gracia, cobro
//      que solo extiende la fecha).
//   4. Una empresa inexistente no revienta.
//   5. CON LA TABLA AUSENTE, planes_log NO LANZA. Ésta es la importante:
//      el helper corre en api/mp_return.php y en el cron. Si lanzara, un
//      cliente podría pagar y quedarse sin plan.
//
// REQUISITOS (entorno de desarrollo, NUNCA producción):
//   - MariaDB/MySQL local con BD 'simtest' y usuario sim/sim
//   - DESTRUYE y recrea sus propias tablas en simtest
// Correr: php tools/test_plan_log.php   → debe terminar en OK
// Obligatorio tras CUALQUIER cambio a planes_log() o a sus llamadores.
// ============================================================
define('COTIZAAPP', 1);
$_SERVER['REMOTE_ADDR'] = '203.0.113.9';

class DB {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (!self::$pdo) {
            self::$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4',
                'sim', 'sim', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        }
        return self::$pdo;
    }
    public static function query($s, $p = []): array { $t = self::pdo()->prepare($s); $t->execute($p); return $t->fetchAll(PDO::FETCH_ASSOC); }
    public static function row($s, $p = []) { $t = self::pdo()->prepare($s); $t->execute($p); $r = $t->fetch(PDO::FETCH_ASSOC); return $r === false ? null : $r; }
    public static function val($s, $p = []) { $t = self::pdo()->prepare($s); $t->execute($p); return $t->fetchColumn(); }
    public static function execute($s, $p = []): int { $t = self::pdo()->prepare($s); $t->execute($p); return $t->rowCount(); }
}

require __DIR__ . '/../core/Helpers.php';

// ── Fixtures ──────────────────────────────────────────────
DB::execute("DROP TABLE IF EXISTS plan_log");
DB::execute("DROP TABLE IF EXISTS empresas_pl");
DB::execute("CREATE TABLE IF NOT EXISTS empresas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(60), nombre VARCHAR(120),
    plan VARCHAR(20) DEFAULT 'free', plan_vence DATE NULL, created_at DATETIME)");
DB::execute("DELETE FROM empresas");
DB::execute("INSERT INTO empresas (id, slug, nombre, plan, plan_vence, created_at)
             VALUES (1,'acme','Acme SA','pro','2026-12-01',NOW())");
$ddl = file_get_contents(__DIR__ . '/../migrations/add_plan_log.sql');
// Quitar comentarios de línea para poder ejecutar el CREATE de un jalón
$ddl = preg_replace('/^\s*--.*$/m', '', $ddl);
DB::execute(trim(rtrim(trim($ddl), ';')));

$ok = 0; $fail = 0;
function chk(string $t, bool $c): void {
    global $ok, $fail;
    if ($c) { $ok++; echo "  ✓ $t\n"; } else { $fail++; echo "  ✗ $t\n"; }
}

$eid = 1;

echo "\n1) UN CAMBIO DE PLAN QUEDA REGISTRADO CON SU ORIGEN\n";
$antes = planes_snapshot($eid);
DB::execute("UPDATE empresas SET plan='business', plan_vence='2027-01-01' WHERE id=?", [$eid]);
planes_log($eid, 'cambio_plan', $antes, ['motivo' => 'manual']);
$r = DB::row("SELECT * FROM plan_log WHERE empresa_id=? ORDER BY id DESC LIMIT 1", [$eid]);
chk('quedó la fila',                         (bool)$r);
chk('guarda DE QUÉ plan venía (pro)',        ($r['plan_anterior'] ?? '') === 'pro');
chk('guarda A QUÉ plan fue (business)',      ($r['plan_nuevo'] ?? '') === 'business');
chk('guarda el vencimiento nuevo',           ($r['vence_nuevo'] ?? '') === '2027-01-01');
chk('deriva el origen del evento',           ($r['origen'] ?? '') === 'superadmin');
chk('guarda la IP',                          !empty($r['ip']));
chk('usuario_id NULL cuando no hay sesión',  $r['usuario_id'] === null);
chk('confianza por defecto = probado',       ($r['confianza'] ?? '') === 'probado');

echo "\n2) UN NO-CAMBIO NO ENSUCIA LA BITÁCORA\n";
$n1 = (int)DB::val("SELECT COUNT(*) FROM plan_log WHERE empresa_id=?", [$eid]);
planes_log($eid, 'cambio_plan', planes_snapshot($eid), []);
$n2 = (int)DB::val("SELECT COUNT(*) FROM plan_log WHERE empresa_id=?", [$eid]);
chk('mismo plan y misma fecha → no inserta', $n1 === $n2);

echo "\n3) 'forzar' REGISTRA LO QUE NO MUEVE EL PLAN\n";
planes_log($eid, 'grace_inicio', planes_snapshot($eid), ['forzar' => true, 'motivo' => 'pago_rechazado']);
$n3 = (int)DB::val("SELECT COUNT(*) FROM plan_log WHERE empresa_id=?", [$eid]);
chk('gracia queda registrada aunque el plan no cambie', $n3 === $n2 + 1);

echo "\n4) DATOS RAROS NO REVIENTAN\n";
$vivo = true;
try { planes_log(999999, 'cambio_plan', null, []); } catch (Throwable $e) { $vivo = false; }
chk('empresa inexistente', $vivo);
$vivo = true;
try { planes_log($eid, str_repeat('X', 200), planes_snapshot($eid), ['forzar' => true, 'motivo' => str_repeat('Y', 200)]); }
catch (Throwable $e) { $vivo = false; }
chk('evento y motivo larguísimos (se truncan, no fallan)', $vivo);

echo "\n5) SIN TABLA, LA RUTA DEL DINERO SIGUE VIVA\n";
DB::execute("RENAME TABLE plan_log TO plan_log_off");
$vivo = true;
try { planes_log($eid, 'pago_mp', planes_snapshot($eid), ['forzar' => true]); } catch (Throwable $e) { $vivo = false; }
chk('planes_log NO lanza con la tabla ausente', $vivo);
$vivo = true;
try { planes_snapshot($eid); } catch (Throwable $e) { $vivo = false; }
chk('planes_snapshot tampoco lanza', $vivo);
DB::execute("RENAME TABLE plan_log_off TO plan_log");

echo "\n" . ($fail === 0
    ? "✓ PLAN LOG OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
