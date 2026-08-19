<?php
// ============================================================
//  Supervisor — Cambiar de sucursal
//
//  Reescribe la empresa de SU sesión, igual que hace
//  modules/superadmin/impersonar.php. A partir de ahí Auth::init define
//  EMPRESA_ID con la sucursal elegida y TODO el sistema —dashboard, mesa,
//  ritmo, ranking, tips, radar, reportes— se comporta como si fuera de esa
//  empresa. Sin una sola línea de render propia.
//
//  POR QUÉ ASÍ Y NO RENDERIZANDO YO:
//  intentar replicar la Mesa (1,436 líneas) y el Ranking (177) siempre dejaba
//  algo fuera, y obligaba a tocar archivos del dashboard que usan TODOS los
//  clientes. Aquí no se toca nada del sistema: el supervisor ve el original.
//
//  El candado es supervisor_ve_empresa(): solo puede pararse en las sucursales
//  de su archivo. Un empresa_id que no esté en su lista se rechaza.
// ============================================================
defined('COTIZAAPP') or die;
supervisor_requerir();
csrf_check();

$destino = (int)($_POST['empresa_id'] ?? 0);
if (!supervisor_ve_empresa($destino)) {
    flash('error', 'Esa sucursal no está en tu lista.');
    redirect('/supervisor');
}

$emp = DB::row("SELECT id FROM empresas WHERE id = ?", [$destino]);
if (!$emp) { flash('error', 'Sucursal no encontrada.'); redirect('/supervisor'); }

$u = Auth::usuario();
if (!$u) redirect('/login');

// Conservar la duración que ya traía su sesión: cambiar de sucursal no debe
// alargarle ni acortarle la sesión.
$viejo = $_COOKIE[SESSION_NAME] ?? null;
$expira = null;
if ($viejo) {
    try { $expira = DB::val("SELECT expires_at FROM user_sessions WHERE token = ?", [$viejo]); }
    catch (Throwable $e) {}
    try { DB::execute("DELETE FROM user_sessions WHERE token = ?", [$viejo]); } catch (Throwable $e) {}
}
if (!$expira) $expira = date('Y-m-d H:i:s', time() + SESSION_BROWSER_SECONDS);

$token = generar_token(32);
DB::insert(
    "INSERT INTO user_sessions (usuario_id, empresa_id, token, ip, user_agent, expires_at)
     VALUES (?, ?, ?, ?, ?, ?)",
    [(int)$u['id'], $destino, $token, ip_real(), $_SERVER['HTTP_USER_AGENT'] ?? '', $expira]
);

setcookie(SESSION_NAME, $token, [
    'expires'  => strtotime($expira),
    'path'     => '/',
    'domain'   => '.' . BASE_DOMAIN,
    'secure'   => !DEBUG,
    'httponly' => true,
    'samesite' => 'Lax',
]);

redirect($_POST['ir'] === 'supervisor' ? '/supervisor' : '/dashboard');
