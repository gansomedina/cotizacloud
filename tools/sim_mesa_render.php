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
$trial = ['es_business' => true]; // gate de plan (paquetes 23-jul)
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
// Marcadores 📅/📵. La única fila con historial es M21: 2 citas (ninguna en
// pie) y racha de 3 "no contestó". Se comprueban en el HTML porque el renglón
// pinta el estado VIGENTE y ahí ese historial ya no se ve — los marcadores son
// justo lo que lo rescata.
chk('bloque 500: el 📅 se pinta con su cuenta y apagado (no hay cita en pie)',
    str_contains($MESA_BLOQUES[500], 'class="mtag mcitas"') && str_contains($MESA_BLOQUES[500], '📅<i>2</i>'), true);
chk('bloque 500: el 📅 no se enciende sin cita en pie', str_contains($MESA_BLOQUES[500], 'mcitas on'), false);
chk('bloque 500: el tooltip del 📅 dice cuántas y cuándo fue la última',
    (bool)preg_match('/Se citaron 2 veces — la última el \d\d\/\d\d/u', $MESA_BLOQUES[500]), true);
chk('bloque 500: el 📵 se pinta con la racha y encendido (3 seguidos)',
    str_contains($MESA_BLOQUES[500], '📵<i>3</i>') && str_contains($MESA_BLOQUES[500], 'class="mtag mnc on"'), true);
chk('bloque 500: el tooltip del 📵 explica la escalera',
    str_contains($MESA_BLOQUES[500], 'al 4.º intento se habilita suspenderla'), true);
chk('M9 (hablaron tras 2 intentos) NO trae 📵 — la racha se reinicia',
    substr_count($MESA_BLOQUES[500], 'class="mtag mnc'), 1);
// LA COLUMNA NO SE MUEVE: los dos huecos se pintan en TODAS las filas, tengan
// o no marcador. Si un día se vuelven condicionales, el ▶ empieza a bailar de
// renglón en renglón y la columna deja de ser escaneable.
$n_rows = substr_count($MESA_BLOQUES[500], 'class="mrow');
$n_cols = substr_count($MESA_BLOQUES[500], 'class="mtags"');
chk('la columna se reserva en todas las filas (mtags) — el ▶ no baila',
    [$n_cols, $n_rows > 0], [$n_rows, true]);
// OJO con el espacio final: sin él, 'class="mtag' también matchea 'class="mtags"'
chk('dos huecos por fila, llenos o vacíos',
    substr_count($MESA_BLOQUES[500], 'class="mtag '), $n_cols * 2);
// Posición: JUNTO al texto del límite, no en la orilla. La caja del límite va
// centrada y ancha; arrimarlos al ▶ los dejaba solos al final con un hueco
// enorme en medio y no se veían.
chk('los marcadores van justo después del texto del límite',
    (bool)preg_match('/class="mfresh[^"]*"[^>]*>.*?<\/span>\s*<span class="mtags">/su', $MESA_BLOQUES[500]), true);
chk('y el ▶ se queda en la orilla (margin-right:auto se come el sobrante)',
    str_contains($MESA_ASSETS ?? '', 'margin:0 auto 0 6px'), true);
// TELÉFONO — COLUMNA PROPIA. Batallaban para encontrarlo: había que abrir la
// cotización para verlo.
chk('se pinta tal como lo capturaron (con sus espacios y guiones)',
    str_contains($MESA_BLOQUES[500], '<span class="mtel">662 123-4567</span>'), true);
// Metido en la línea del folio se truncaba: los títulos de esta empresa son
// direcciones y se comían el número — que era justo lo que se quería ver.
chk('NO va en la línea que se trunca',
    str_contains($MESA_BLOQUES[500], '662 123-4567</span>')
    && !str_contains($MESA_BLOQUES[500], 'Cliente Uno · 662'), true);
chk('vive fuera de .mcli',
    (bool)preg_match('/<\/span>\s*<span class="mtel">/u', $MESA_BLOQUES[500]), true);
// Se pidió VER el número, no marcarlo. Que la prueba afirme la ausencia: si
// alguien lo vuelve a "mejorar" con un botón de llamada, se cae la suite.
chk('sin acción de llamar',                  str_contains($MESA_BLOQUES[500], 'href="tel:'), false);
chk('sin 📞',                                str_contains($MESA_BLOQUES[500], '📞'),         false);
// Columna de verdad: el hueco existe en TODAS las filas, tengan teléfono o no.
chk('la columna se reserva en todas las filas',
    substr_count($MESA_BLOQUES[500], 'class="mtel"'), $n_rows);
chk('las filas sin teléfono lo dejan vacío, no inventan',
    str_contains($MESA_BLOQUES[500], '<span class="mtel"></span>'), true);
chk('el móvil NO lo esconde',
    (bool)preg_match('/@media[^{]*max-width:\s*640px[\s\S]{0,600}?\.mtel\s*\{[^}]*display\s*:\s*none/u', $MESA_ASSETS ?? ''), false);
chk('trae su estilo',                        str_contains($MESA_ASSETS ?? '', '.mesa-emb .mtel'), true);

// EL MOTIVO ESCRITO DE "OTRO" SE VE EN EL HISTORIAL DEL CAJÓN.
// Si se guardara y nadie lo pintara seria un dato muerto — justo lo que paso
// con el Descuento Inteligente, que se aplicaba bien y no se mostraba.
chk('el historial pinta el motivo que escribio el asesor',
    str_contains($MESA_BLOQUES[500], 'se mudó a Obregón'), true);
chk('con su propio estilo, no pegado al estado',
    str_contains($MESA_BLOQUES[500], 'class="mhr"')
    && str_contains($MESA_ASSETS ?? '', '.mesa-emb .mhr'), true);
// Los toques sin texto no inventan un guion colgando.
chk('los toques sin motivo escrito no dejan un "—" suelto',
    (bool)preg_match('/<span class="mhr">—\s*<\/span>/u', $MESA_BLOQUES[500]), false);

// DESCARTAR PIDE MOTIVO. El 👎 del renglón descarta igual que la pastilla del
// cajón, así que abre el mismo selector. Si el partial dejara de viajar en los
// assets, el 👎 quedaría MUERTO (mesaFb sale temprano si no existe czPedirRazon)
// — un botón que no hace nada y nadie se entera. Por eso se comprueba aquí.
chk('el selector de motivo viaja con los assets',
    str_contains($MESA_ASSETS ?? '', 'id="czrz-back"')
    && str_contains($MESA_ASSETS ?? '', 'window.czPedirRazon'), true);
chk('trae los 6 motivos, de la fuente única',
    substr_count($MESA_ASSETS ?? '', 'czRzElegir('), count(Mesa::RAZONES));
chk('el 👎 del renglón pide motivo antes de mandar nada',
    (bool)preg_match("/tipo === 'sin_interes' && !razon[\s\S]{0,220}czPedirRazon/u", $MESA_ASSETS ?? ''), true);
chk('el motivo viaja en el POST del 👎',
    (bool)preg_match("/area:'feedback'[^}]*razon:/u", $MESA_ASSETS ?? ''), true);
chk('cancelar NO descarta (el callback se suelta sin llamarse)',
    (bool)preg_match('/czRzCerrar[\s\S]{0,220}cb = null/u', $MESA_ASSETS ?? ''), true);
chk('la pastilla del cajón usa la MISMA lista',
    substr_count($MESA_BLOQUES[500], 'data-rz="'), $n_rows * count(Mesa::RAZONES));

chk('assets: 📅 y 📵 traen su estilo (si no, salen como texto suelto)',
    str_contains($MESA_ASSETS ?? '', '.mesa-emb .mtags')
    && str_contains($MESA_ASSETS ?? '', '.mesa-emb .mtag')
    && str_contains($MESA_ASSETS ?? '', '.mesa-emb .mnc.on'), true);
chk('bloque 501: lista completa, solo 2 milagros ocultos por CAP_MILAGROS (top 26 de 28)', str_contains($MESA_BLOQUES[501], 'top 26 de 28'), true);
chk('sin referencias huérfanas a #mesa-card', str_contains($MESA_SHARED . implode('', $MESA_BLOQUES), '#mesa-card') || str_contains($MESA_SHARED . implode('', $MESA_BLOQUES), 'mesa-card'), false);
chk('aviso de limpieza vive en el bloque del asesor', str_contains($MESA_BLOQUES[500], 'jamás ha cerrado') || !str_contains($MESA_SHARED, 'jamás ha cerrado'), true);
chk('binding de filas diferido a DOMContentLoaded', str_contains($MESA_ASSETS ?? '', "addEventListener('DOMContentLoaded'"), true);
// Las queries que dependen de ventas.pagado/vendedor_id: si la tabla del fixture
// se queda corta, _mesa.php las falla en silencio (fail-open) y el render pasaba
// con ceros sin que nadie se enterara. M99 es una venta cobrada de ESTE mes.
chk('la query de cierres del mes CORRIÓ (no fail-open silencioso)', isset($mesa_mes), true);
chk('bloque 500: pinta el marcador del mes con el cierre real', str_contains($MESA_BLOQUES[500], '📅 Mes:') && (int)($mesa_mes[500]['cierres'] ?? 0) === 1, true);

// ── Segundo render CON ?mesa_dias: el reporte debe aparecer ──
$_GET['mesa_dias'] = '30';
unset($MESA_SHARED, $MESA_BLOQUES, $MESA_EMITIDO);
ob_start();
$trial = ['es_business' => true]; // gate de plan (paquetes 23-jul)
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
$trial = ['es_business' => true]; // gate de plan (paquetes 23-jul)
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
$trial = ['es_business' => true]; // gate de plan (paquetes 23-jul)
include '/home/user/cotizacloud/modules/dashboard/_mesa.php';
ob_end_clean();
chk('ASESOR con flag=0: la mesa no existe para él', empty($MESA_ASESOR) && empty($MESA_SHARED), true);

echo "\n".($fail ? "✗ $fail FALLAS" : "✓ RENDER OK")."\n";
exit($fail ? 1 : 0);
