<?php
// Smoke-test de render del _mesa.php (mesa incrustada en el ranking).
// REQUIERE: correr ANTES php tools/sim_mesa_armar.php (siembra empresa 5 en
// la BD simtest — sim_mesa_reporte la pisa con otros datos). Correr:
//   php tools/sim_mesa_armar.php && php tools/sim_mesa_render.php -> OK
define('COTIZAAPP', 1);
define('MODULES_PATH', '/dev/null');
class DB {
    private static ?PDO $pdo = null;
    public static function pdo(): PDO {
        if (!self::$pdo) self::$pdo = new PDO('mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=simtest;charset=utf8mb4','sim','sim',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        return self::$pdo;
    }
    public static function query($sql,$p=[]):array{$st=self::pdo()->prepare($sql);$st->execute($p);return $st->fetchAll(PDO::FETCH_ASSOC);}
    public static function row($sql,$p=[]){$st=self::pdo()->prepare($sql);$st->execute($p);$r=$st->fetch(PDO::FETCH_ASSOC);return $r===false?null:$r;}
    public static function val($sql,$p=[]){$st=self::pdo()->prepare($sql);$st->execute($p);return $st->fetchColumn();}
    public static function execute($sql,$p=[]):void{$st=self::pdo()->prepare($sql);$st->execute($p);}
}
class Radar { public static function ciclo_venta($e){ return ['auto'=>true,'p75'=>20,'mediana'=>10]; } }
class ActividadScore { public static function periodo_efectivo($e){ return 15; } }
class Auth { public static function id(){ return 500; } public static function es_admin(){ return false; } }
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function csrf_token(){ return 'test-token'; }
require '/home/user/cotizacloud/core/DiagnosticoTips.php';
require '/home/user/cotizacloud/core/MesaSugerencias.php';
require '/home/user/cotizacloud/core/Mesa.php';

$es_admin_dash = 1; $empresa_id = 5;
ob_start();
include '/home/user/cotizacloud/modules/dashboard/_mesa.php';
$emitido = ob_get_clean();

$fail = 0;
function chk($n,$g,$w){ global $fail; $ok=($g==$w); if(!$ok)$fail++; echo ($ok?"  ✓ ":"  ✗ ").$n.($ok?'':'  got='.json_encode(is_string($g)?substr($g,0,120):$g))."\n"; }

chk('el include NO emite nada directo', trim($emitido), '');
chk('MESA_SHARED existe y trae el bloque compartido', isset($MESA_SHARED) && str_contains($MESA_SHARED, 'id="mesa-shared"'), true);
chk('shared trae playbook; assets traen CSS y JS', str_contains($MESA_SHARED,'mesa-pb') && str_contains($MESA_ASSETS ?? '','.mesa-emb .mrow') && str_contains($MESA_ASSETS ?? '','function mesaTap'), true);
chk('SIN ?mesa_dias el reporte NO se renderiza (M2: bajo demanda) y el link navega', !str_contains($MESA_SHARED,'id="mesa-rp"') && str_contains($MESA_SHARED,'mesa_dias=30'), true);
chk('shared SIN chips de selector de vendedores', str_contains($MESA_SHARED, 'href="?mesa_uid='), false);
chk('bloques por asesor: 500 y 501', (function($k){ sort($k); return $k; })(array_keys($MESA_BLOQUES ?? [])), [500, 501]);
chk('bloque 500: details con id propio y franja', str_contains($MESA_BLOQUES[500], 'id="mesa-emb-500"') && str_contains($MESA_BLOQUES[500], 'class="mstrip"'), true);
chk('bloque 500: trae filas (mrow) y cajones (mdrawer)', substr_count($MESA_BLOQUES[500], 'class="mrow') >= 8 && str_contains($MESA_BLOQUES[500], 'mdrawer'), true);
chk('bloque 500: resumen con por trabajar / en juego', (bool)preg_match('/por trabajar|en seguimiento|en juego/', $MESA_BLOQUES[500]), true);
chk('bloque 501: lista completa, solo 2 milagros ocultos por CAP_MILAGROS (top 26 de 28)', str_contains($MESA_BLOQUES[501], 'top 26 de 28'), true);
chk('sin referencias huérfanas a #mesa-card', str_contains($MESA_SHARED . implode('', $MESA_BLOQUES), '#mesa-card') || str_contains($MESA_SHARED . implode('', $MESA_BLOQUES), 'mesa-card'), false);
chk('aviso de limpieza vive en el bloque del asesor', str_contains($MESA_BLOQUES[500], 'jamás ha cerrado') || !str_contains($MESA_SHARED, 'jamás ha cerrado'), true);
chk('binding de filas diferido a DOMContentLoaded', str_contains($MESA_ASSETS ?? '', "addEventListener('DOMContentLoaded'"), true);

// ── Segundo render CON ?mesa_dias: el reporte debe aparecer ──
$_GET['mesa_dias'] = '30';
unset($MESA_SHARED, $MESA_BLOQUES, $MESA_EMITIDO);
ob_start();
include '/home/user/cotizacloud/modules/dashboard/_mesa.php';
ob_end_clean();
chk('CON ?mesa_dias el reporte SÍ se renderiza abierto', str_contains($MESA_SHARED ?? '', 'id="mesa-rp"') && str_contains($MESA_SHARED ?? '', 'Cartera hoy'), true);
chk('pills de período preservan el estado (mesa_dias en el link)', str_contains($MESA_SHARED ?? '', 'mesa_dias=60'), true);

// ── Tercer render: MODO ASESOR (uid 500, mesa_activa=1) ──
unset($_GET['mesa_dias']);
unset($MESA_SHARED, $MESA_BLOQUES, $MESA_ASESOR, $MESA_ASSETS, $MESA_EMITIDO);
$es_admin_dash = 0;
$empresa = ['mesa_activa' => 1];
ob_start();
include '/home/user/cotizacloud/modules/dashboard/_mesa.php';
ob_end_clean();
chk('ASESOR: tarjeta propia con su mesa', str_contains($MESA_ASESOR ?? '', 'Tu mesa de trabajo') && str_contains($MESA_ASESOR ?? '', 'mesa-emb-500'), true);
chk('ASESOR: su mesa abierta por default', str_contains($MESA_ASESOR ?? '', '<details open class="mesa-emb mesa-strip"'), true);
chk('ASESOR: widget de cobertura de señales presente', str_contains($MESA_ASESOR ?? '', 'Señales de tu mesa'), true);
chk('ASESOR: SIN reporte del equipo ni recuperado empresa-wide', !str_contains($MESA_ASESOR ?? '', 'Reporte del equipo') && !str_contains($MESA_ASESOR ?? '', 'toda la empresa, no solo'), true);
chk('ASESOR: sin bloques de otros asesores', !str_contains($MESA_ASESOR ?? '', 'mesa-emb-501'), true);
chk('ASESOR: assets aparte (JS de taps disponible)', str_contains($MESA_ASSETS ?? '', 'function mesaTap') && str_contains($MESA_ASSETS ?? '', 'mesa-toast'), true);
chk('ASESOR: playbook con disuasión reutilizado', str_contains($MESA_ASESOR ?? '', 'mesa-pb'), true);
chk('ASESOR: shared de admin NO se construyó', empty($MESA_SHARED), true);

// ── Cuarto render: asesor con mesa_activa=0 → NADA ──
unset($MESA_SHARED, $MESA_BLOQUES, $MESA_ASESOR, $MESA_ASSETS);
$empresa = ['mesa_activa' => 0];
ob_start();
include '/home/user/cotizacloud/modules/dashboard/_mesa.php';
ob_end_clean();
chk('ASESOR con flag=0: la mesa no existe para él', empty($MESA_ASESOR) && empty($MESA_SHARED), true);

echo "\n".($fail ? "✗ $fail FALLAS" : "✓ RENDER OK")."\n";
exit($fail ? 1 : 0);
