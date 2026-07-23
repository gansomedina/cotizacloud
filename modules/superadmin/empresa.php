<?php
// ============================================================
//  SuperAdmin — Detalle de empresa
// ============================================================
defined('COTIZAAPP') or die;
Auth::requerir_superadmin();

$empresa_id = (int)($id ?? 0);
$emp = DB::row("SELECT * FROM empresas WHERE id = ?", [$empresa_id]);
if (!$emp) { http_response_code(404); die('Empresa no encontrada'); }

// ── Usuarios ──────────────────────────────────────────────
$usuarios = DB::query(
    "SELECT id, nombre, email, usuario, rol, activo, ultimo_login, created_at
     FROM usuarios WHERE empresa_id = ? ORDER BY rol ASC, nombre ASC",
    [$empresa_id]
);

// ── Origen del registro: IP y dispositivo del primer login ──
// user_sessions guarda ip + user_agent en cada login (Auth.php). La sesión
// más antigua (id menor) es el primer acceso ≈ momento del alta.
$primer_login = DB::row(
    "SELECT s.ip, s.user_agent, s.created_at, u.nombre AS usuario_nombre
     FROM user_sessions s
     JOIN usuarios u ON u.id = s.usuario_id
     WHERE s.empresa_id = ?
     ORDER BY s.id ASC LIMIT 1",
    [$empresa_id]
);
function sa_dispositivo(string $ua): string {
    if ($ua === '') return '—';
    $os  = stripos($ua,'iPhone')!==false ? 'iPhone'
         : (stripos($ua,'iPad')!==false ? 'iPad'
         : (stripos($ua,'Android')!==false ? 'Android'
         : (stripos($ua,'Macintosh')!==false ? 'Mac'
         : (stripos($ua,'Windows')!==false ? 'Windows'
         : (stripos($ua,'Linux')!==false ? 'Linux' : 'Dispositivo')))));
    $nav = (stripos($ua,'Edg')!==false || stripos($ua,'EdgiOS')!==false) ? 'Edge'
         : ((stripos($ua,'Firefox')!==false || stripos($ua,'FxiOS')!==false) ? 'Firefox'
         : ((stripos($ua,'CriOS')!==false) ? 'Chrome'
         : ((stripos($ua,'SamsungBrowser')!==false) ? 'Samsung'
         : ((stripos($ua,'OPR')!==false || stripos($ua,'OPiOS')!==false) ? 'Opera'
         : ((stripos($ua,'CotizaCloud')!==false) ? 'App'
         : ((stripos($ua,'Chrome')!==false) ? 'Chrome'
         : (stripos($ua,'Safari')!==false ? 'Safari' : '')))))));
    return $nav ? "$os · $nav" : $os;
}

// ── Consentimientos legales (aceptación de TyC + Privacidad) ──
// Evidencia de clickwrap registrada al alta. Tabla puede no existir
// en instalaciones viejas; se protege con try.
$consentimientos = [];
try {
    $consentimientos = DB::query(
        "SELECT c.aceptado_at, c.ip, c.metodo, c.hash_sha256,
                dv.tipo, dv.version, u.nombre AS usuario_nombre
         FROM consentimientos c
         JOIN documento_versiones dv ON dv.id = c.documento_version_id
         LEFT JOIN usuarios u ON u.id = c.usuario_id
         WHERE c.empresa_id = ?
         ORDER BY c.aceptado_at ASC, dv.tipo ASC",
        [$empresa_id]
    );
} catch (\Throwable $e) { $consentimientos = []; }

// ── Métricas ──────────────────────────────────────────────
$num_cots = (int)DB::val("SELECT COUNT(*) FROM cotizaciones WHERE empresa_id = ?", [$empresa_id]);
$num_ventas = (int)DB::val("SELECT COUNT(*) FROM ventas WHERE empresa_id = ?", [$empresa_id]);
$num_clientes = (int)DB::val("SELECT COUNT(*) FROM clientes WHERE empresa_id = ?", [$empresa_id]);
$num_articulos = (int)DB::val("SELECT COUNT(*) FROM articulos WHERE empresa_id = ?", [$empresa_id]);

// Cotizaciones por estado
$cots_estado = DB::query(
    "SELECT estado, COUNT(*) AS n FROM cotizaciones WHERE empresa_id = ? GROUP BY estado",
    [$empresa_id]
);
$estados = [];
foreach ($cots_estado as $ce) $estados[$ce['estado']] = (int)$ce['n'];

// Últimas 10 cotizaciones
$ultimas_cots = DB::query(
    "SELECT c.id, c.slug, c.numero, c.estado, c.total, c.created_at,
            cl.nombre AS cliente_nombre
     FROM cotizaciones c
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     WHERE c.empresa_id = ?
     ORDER BY c.created_at DESC LIMIT 10",
    [$empresa_id]
);

// Trial info
$trial = trial_info($empresa_id);

// Radar config
$radar_config = json_decode($emp['radar_config'] ?? '{}', true) ?: [];
$radar_modo = $radar_config['modo'] ?? 'medio';

function sa_hace(int $ts): string {
    $d = time() - $ts;
    if ($d <= 0) return 'ahora';
    if ($d < 60) return $d . 's';
    if ($d < 3600) return floor($d / 60) . 'm';
    if ($d < 86400) return floor($d / 3600) . 'h';
    if ($d < 86400 * 30) return floor($d / 86400) . 'd';
    return date('d/m/Y', $ts);
}

function sa_money(float $n): string {
    if ($n >= 1000000) return '$' . number_format($n / 1000000, 1) . 'M';
    if ($n >= 1000) return '$' . number_format($n / 1000, 0) . 'K';
    return '$' . number_format($n, 0);
}

$badge_estado = [
    'borrador' => 'badge-slate',
    'enviada'  => 'badge-blue',
    'vista'    => 'badge-amber',
    'aceptada' => 'badge-green',
    'rechazada'=> 'badge-red',
    'expirada' => 'badge-red',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($emp['nombre']) ?> — Super Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<script src="/assets/js/feather.min.js"></script>
<style>
:root {
    --bg:#f4f4f0; --white:#fff; --border:#e2e2dc; --border2:#c8c8c0;
    --text:#1a1a18; --t2:#4a4a46; --t3:#6a6a64;
    --g:#1a5c38; --g-bg:#eef7f2; --g-border:#b8ddc8;
    --amb:#92400e; --amb-bg:#fef3c7;
    --blue:#1d4ed8; --blue-bg:#dbeafe;
    --danger:#c53030; --danger-bg:#fff5f5;
    --purple:#6d28d9; --purple-bg:#ede9fe;
    --slate:#475569; --slate-bg:#f1f5f9;
    --r:12px; --r-sm:9px;
    --sh:0 1px 3px rgba(0,0,0,.06);
    --body:'Plus Jakarta Sans',sans-serif;
    --num:'DM Sans',sans-serif;
}
*,*::before,*::after{box-sizing:border-box}
body{font-family:var(--body);background:var(--bg);color:var(--text);margin:0;font-size:14px;line-height:1.5}

.sa-wrap{max-width:1100px;margin:0 auto;padding:20px 24px 60px}

.back{display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:var(--t3);text-decoration:none;margin-bottom:16px;transition:color .12s}
.back:hover{color:var(--g)}

.emp-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.emp-header h1{font-size:22px;font-weight:800;margin:0}
.emp-header .slug{font-size:13px;color:var(--t3);font-family:var(--num)}

.badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600}
.badge-green{background:var(--g-bg);color:var(--g)}
.badge-red{background:var(--danger-bg);color:var(--danger)}
.badge-amber{background:var(--amb-bg);color:var(--amb)}
.badge-blue{background:var(--blue-bg);color:var(--blue)}
.badge-slate{background:var(--slate-bg);color:var(--slate)}

.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 18px;box-shadow:var(--sh)}
.card-label{font-size:11.5px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.3px}
.card-val{font:800 24px var(--num);color:var(--text);margin-top:4px}

.section{margin-bottom:28px}
.section h2{font-size:15px;font-weight:700;margin:0 0 12px;color:var(--text)}

.tbl-wrap{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{text-align:left;font-size:11px;font-weight:700;color:var(--t3);text-transform:uppercase;letter-spacing:.4px;padding:10px 14px;border-bottom:2px solid var(--border);white-space:nowrap}
td{padding:10px 14px;font-size:13px;color:var(--text);border-bottom:1px solid var(--border)}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafaf8}
.num{font-family:var(--num)}
.ago{color:var(--t3);font-size:12px}

.info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:12px;margin-bottom:24px}
.info-item{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:14px 16px;box-shadow:var(--sh)}
.info-item label{font-size:11px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.3px;display:block;margin-bottom:4px}
.info-item span{font-size:13.5px;font-weight:500;color:var(--text)}

.btn-enter{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--r-sm);font:600 13px var(--body);cursor:pointer;border:1.5px solid var(--g-border);background:var(--g-bg);color:var(--g);text-decoration:none;transition:all .12s}
.btn-enter:hover{background:var(--g);color:#fff}

@media(max-width:768px){
    .sa-wrap{padding:14px}
    .cards{grid-template-columns:repeat(2,1fr)}
}
</style>
</head>
<body>

<div class="sa-wrap">

<a href="/superadmin" class="back"><i data-feather="arrow-left" style="width:14px;height:14px"></i> Todas las empresas</a>

<div class="emp-header">
    <div>
        <h1><?= e($emp['nombre']) ?></h1>
        <div class="slug"><?= !empty($emp['dominio_custom']) ? e($emp['dominio_custom']) : e($emp['slug']) . '.cotiza.cloud' ?></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <span class="badge <?= $emp['activa'] ? 'badge-green' : 'badge-red' ?>">
            <?= $emp['activa'] ? 'Activa' : 'Suspendida' ?>
        </span>
        <form method="post" action="/superadmin/impersonar" style="margin:0">
            <input type="hidden" name="empresa_id" value="<?= $emp['id'] ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn-enter"><i data-feather="log-in" style="width:14px;height:14px"></i> Entrar como admin</button>
        </form>
        <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/toggle" style="margin:0" onsubmit="return confirm('<?= $emp['activa'] ? '¿Suspender esta empresa? Los usuarios no podrán acceder.' : '¿Reactivar esta empresa?' ?>')">
            <?= csrf_field() ?>
            <?php if ($emp['activa']): ?>
                <button type="submit" class="btn-enter" style="border-color:#fca5a5;background:#fff5f5;color:#c53030"><i data-feather="pause-circle" style="width:14px;height:14px"></i> Suspender</button>
            <?php else: ?>
                <button type="submit" class="btn-enter"><i data-feather="play-circle" style="width:14px;height:14px"></i> Activar</button>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Plan -->
<?php
    $plan_bg = $trial['es_free'] ? 'var(--amb-bg)' : ($trial['vencido'] ? 'var(--danger-bg)' : ($trial['por_vencer'] ? 'var(--amb-bg)' : 'var(--g-bg)'));
    $plan_border = $trial['es_free'] ? '#fcd34d' : ($trial['vencido'] ? '#fca5a5' : ($trial['por_vencer'] ? '#fcd34d' : 'var(--g-border)'));
    $badge_class = $trial['es_free'] ? 'badge-amber' : ($trial['vencido'] ? 'badge-red' : ($trial['es_business'] ? 'badge-blue' : ($trial['es_lite'] ? 'badge-slate' : 'badge-green')));
    $badge_text = $trial['es_free'] ? 'FREE' : ($trial['vencido'] ? strtoupper($trial['plan_label']) . ' VENCIDO' : strtoupper($trial['plan_label']) . (!empty($trial['trial_activo']) ? ' · PRUEBA' : ''));
?>
<div style="background:<?= $plan_bg ?>;border:1px solid <?= $plan_border ?>;border-radius:var(--r);padding:16px 20px;margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <span class="badge <?= $badge_class ?>" style="font-size:12px;padding:5px 14px;margin-right:8px">
                <?= $badge_text ?>
            </span>
            <?php if ($trial['es_free']): ?>
                <span style="font-size:13px;color:var(--amb);font-weight:600">
                    <?php if (!empty($trial['trial_usado'])): ?>
                    Prueba de 30 días FINALIZADA sin pago — bloqueada para cotizaciones nuevas
                    <?php else: ?>
                    <?= $trial['usadas'] ?> / <?= TRIAL_LIMIT ?> cotizaciones usadas
                    <?php if ($trial['agotado']): ?> — <strong>AGOTADO</strong><?php endif; ?>
                    <?php endif; ?>
                </span>
                <div style="background:#fde68a;border-radius:6px;height:6px;margin-top:8px;max-width:300px;overflow:hidden">
                    <div style="background:<?= $trial['agotado'] ? 'var(--danger)' : 'var(--amb)' ?>;height:100%;width:<?= $trial['pct'] ?>%;border-radius:6px"></div>
                </div>
            <?php elseif ($trial['vencido']): ?>
                <span style="font-size:13px;color:var(--danger);font-weight:600">
                    Licencia vencida el <?= date('d/m/Y', strtotime($trial['plan_vence'])) ?> — Empresa suspendida
                </span>
            <?php elseif ($trial['por_vencer']): ?>
                <span style="font-size:13px;color:var(--amb);font-weight:600">
                    Vence el <?= date('d/m/Y', strtotime($trial['plan_vence'])) ?> — <?= $trial['dias_restantes'] ?> días restantes
                </span>
            <?php else: ?>
                <span style="font-size:13px;color:var(--g);font-weight:600">
                    Licencia activa hasta <?= $trial['plan_vence'] ? date('d/m/Y', strtotime($trial['plan_vence'])) : 'indefinido' ?>
                    <?php if ($trial['dias_restantes'] !== null): ?> — <?= $trial['dias_restantes'] ?> días<?php endif; ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($trial['es_free']): ?>
            <!-- Activar plan pagado -->
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                <select id="sa-duracion" style="padding:6px 10px;border:1.5px solid var(--g-border);border-radius:var(--r-sm);font:500 12px var(--body);background:var(--white)">
                    <option value="1_mes">1 mes</option>
                    <option value="3_meses">3 meses</option>
                    <option value="6_meses">6 meses</option>
                    <option value="1_anio">1 año</option>
                </select>
                <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="margin:0" onsubmit="this.querySelector('[name=duracion]').value=document.getElementById('sa-duracion').value;return confirm('¿Activar plan Lite?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="activar_lite">
                    <input type="hidden" name="duracion" value="">
                    <button type="submit" class="btn-enter" style="font-size:12px;border-color:#fde68a;background:#fffbeb;color:#92400e"><i data-feather="feather" style="width:14px;height:14px"></i> Lite</button>
                </form>
                <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="margin:0" onsubmit="this.querySelector('[name=duracion]').value=document.getElementById('sa-duracion').value;return confirm('¿Activar plan Pro?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="activar_pro">
                    <input type="hidden" name="duracion" value="">
                    <button type="submit" class="btn-enter" style="font-size:12px"><i data-feather="zap" style="width:14px;height:14px"></i> Pro</button>
                </form>
                <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="margin:0" onsubmit="this.querySelector('[name=duracion]').value=document.getElementById('sa-duracion').value;return confirm('¿Activar plan Business?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="activar_business">
                    <input type="hidden" name="duracion" value="">
                    <button type="submit" class="btn-enter" style="font-size:12px;border-color:var(--blue-bg);background:var(--blue-bg);color:var(--blue)"><i data-feather="briefcase" style="width:14px;height:14px"></i> Business</button>
                </form>
            </div>
        <?php else: ?>
            <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
                <!-- Renovar -->
                <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="margin:0;display:flex;gap:6px;align-items:center" onsubmit="return confirm('¿Renovar licencia <?= $trial['plan_label'] ?>?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="renovar">
                    <select name="duracion" style="padding:6px 10px;border:1.5px solid var(--g-border);border-radius:var(--r-sm);font:500 12px var(--body);background:var(--white)">
                        <option value="1_mes">+1 mes</option>
                        <option value="3_meses">+3 meses</option>
                        <option value="6_meses">+6 meses</option>
                        <option value="1_anio">+1 año</option>
                    </select>
                    <button type="submit" class="btn-enter" style="font-size:12px"><i data-feather="refresh-cw" style="width:14px;height:14px"></i> Renovar</button>
                </form>
                <!-- Cambiar plan — muestra los planes distintos al actual -->
                <?php
                $planes_cambio = ['lite' => 'Lite', 'pro' => 'Pro', 'business' => 'Business'];
                foreach ($planes_cambio as $pk => $pn):
                    if ($trial['plan'] === $pk) continue;
                ?>
                <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="margin:0" onsubmit="return confirm('¿Cambiar a <?= $pn ?>?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="cambiar_plan">
                    <input type="hidden" name="nuevo_plan" value="<?= $pk ?>">
                    <button type="submit" class="btn-enter" style="font-size:12px<?= $pk==='business' ? ';border-color:var(--blue-bg);background:var(--blue-bg);color:var(--blue)' : ($pk==='lite' ? ';border-color:#fde68a;background:#fffbeb;color:#92400e' : '') ?>"><i data-feather="repeat" style="width:14px;height:14px"></i> <?= $pn ?></button>
                </form>
                <?php endforeach; ?>
                <!-- Regresar a Free -->
                <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="margin:0" onsubmit="return confirm('¿Regresar a FREE? Se activará el límite de <?= TRIAL_LIMIT ?> cotizaciones.')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="accion" value="regresar_free">
                    <button type="submit" class="btn-enter" style="border-color:#fcd34d;background:var(--amb-bg);color:var(--amb);font-size:12px"><i data-feather="rotate-ccw" style="width:14px;height:14px"></i> Free</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <div style="margin-top:8px;font-size:12px;color:var(--t3)">
        <?= $trial['usadas'] ?> cotizaciones creadas en total
    </div>
    <?php
    $sub_info = DB::row("SELECT * FROM suscripciones WHERE empresa_id=?", [$emp['id']]);
    if ($sub_info): ?>
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid <?= $plan_border ?>">
        <div style="font:700 11px var(--body);text-transform:uppercase;letter-spacing:.06em;color:var(--t3);margin-bottom:6px">Suscripción MercadoPago</div>
        <div style="font:400 13px var(--body);color:var(--text);display:flex;gap:16px;flex-wrap:wrap">
            <span>Estado: <strong style="color:<?= $sub_info['estado']==='active' ? 'var(--g)' : 'var(--amb)' ?>"><?= ucfirst($sub_info['estado']) ?></strong></span>
            <span>Ciclo: <strong><?= ucfirst($sub_info['ciclo']) ?></strong></span>
            <span>Monto: <strong>$<?= number_format($sub_info['monto_mxn'],2) ?> MXN</strong></span>
            <?php if ($sub_info['cancel_al_vencer']): ?><span style="color:var(--danger);font-weight:600">Cancelará al vencer</span><?php endif; ?>
        </div>
        <?php if ($sub_info['mp_preapproval_id']): ?>
        <div style="font:400 11px var(--num);color:var(--t3);margin-top:4px">MP ID: <?= e($sub_info['mp_preapproval_id']) ?></div>
        <?php endif; ?>
        <?php
        $ultimo_pago = DB::row("SELECT * FROM pagos_suscripcion WHERE empresa_id=? ORDER BY fecha_pago DESC LIMIT 1", [$emp['id']]);
        if ($ultimo_pago): ?>
        <div style="font:400 12px var(--body);color:var(--t3);margin-top:4px">
            Último pago: <?= date('d/m/Y', strtotime($ultimo_pago['fecha_pago'])) ?> — $<?= number_format($ultimo_pago['monto_mxn'],2) ?> (<?= $ultimo_pago['estado'] ?>)
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ($trial['en_grace'] ?? false): ?>
    <div style="margin-top:8px;padding:8px 12px;background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--r-sm);font:500 12px var(--body);color:var(--danger)">
        En período de gracia hasta <?= date('d/m/Y', strtotime($trial['grace_hasta'])) ?>
    </div>
    <?php endif; ?>
</div>

<!-- Dominio custom -->
<div style="background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:16px 20px;margin-bottom:16px;box-shadow:var(--sh)">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
        <i data-feather="globe" style="width:16px;height:16px;color:var(--purple)"></i>
        <span style="font-size:13px;font-weight:700;color:var(--text)">Dominio custom</span>
    </div>
    <!-- Mesa de Trabajo: rollout por empresa -->
    <div style="margin-bottom:14px;padding:10px 12px;background:#f8f8f5;border:1px solid #e2e2dc;border-radius:8px">
      <div style="font-weight:700;font-size:13px;margin-bottom:6px">📋 Mesa de Trabajo (rollout)</div>
      <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap"
            onsubmit="return confirm('¿Cambiar el estado de la Mesa para esta empresa?')">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="mesa_activa">
        <?php $mesa_flag_emp = (int)($emp['mesa_activa'] ?? 0); ?>
        <select name="valor" style="padding:6px 10px;border:1px solid #d4d4ce;border-radius:6px;font-size:13px">
          <option value="0" <?= $mesa_flag_emp === 0 ? 'selected' : '' ?>>0 — Apagada (solo admin la ve)</option>
          <option value="1" <?= $mesa_flag_emp === 1 ? 'selected' : '' ?>>1 — UI asesores (SIN score — quincena de aprendizaje)</option>
          <option value="2" <?= $mesa_flag_emp === 2 ? 'selected' : '' ?>>2 — UI + score (Seguimiento = la mesa)</option>
        </select>
        <button type="submit" class="btn btn-sm">Guardar</button>
        <span style="font-size:11.5px;color:#8a8a84">Requiere migración add_mesa_score.sql. Avisar la regla ANTES de pasar a 2: cobertura de la mesa ≥80% = completo · 50–80% = medio · menos = no cuenta.</span>
      </form>
    </div>

    <!-- Asientos: tope de usuarios activos (perilla por empresa — paquetes 23-jul) -->
    <div style="margin-bottom:14px;padding:10px 12px;background:#f8f8f5;border:1px solid #e2e2dc;border-radius:8px">
      <div style="font-weight:700;font-size:13px;margin-bottom:6px">🪑 Asientos (tope de usuarios activos)</div>
      <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/plan" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="asientos">
        <input type="number" name="asientos" min="1" max="250" value="<?= ($emp['asientos'] ?? null) !== null ? (int)$emp['asientos'] : '' ?>"
               placeholder="vacío = default del plan"
               style="width:180px;padding:6px 10px;border:1px solid #d4d4ce;border-radius:6px;font-size:13px">
        <button type="submit" class="btn btn-sm">Guardar</button>
        <span style="font-size:11.5px;color:#8a8a84">Vacío = default del plan (Free/Lite 1 · Pro/Business ilimitado). Se usa para Business por asiento pactado en demo o para capar un caso puntual.</span>
      </form>
    </div>

    <form method="post" action="/superadmin/empresa/<?= $emp['id'] ?>/dominio" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <?= csrf_field() ?>
        <input type="text" name="dominio_custom" value="<?= e($emp['dominio_custom'] ?? '') ?>"
               placeholder="ej: hmo.ontimecocinas.com"
               style="flex:1;min-width:220px;padding:8px 12px;border:1.5px solid var(--border2);border-radius:var(--r-sm);font:500 13px var(--num);background:var(--white)">
        <button type="submit" class="btn-enter" style="font-size:12px"><i data-feather="save" style="width:14px;height:14px"></i> Guardar</button>
    </form>
    <?php if (!empty($emp['dominio_custom'])): ?>
        <div style="margin-top:8px;font-size:12px;color:var(--t3)">
            URLs publicas: <strong style="color:var(--purple)"><?= e($emp['dominio_custom']) ?>/c/slug</strong>
            <span style="margin-left:8px">DNS: apuntar CNAME a <code style="background:var(--slate-bg);padding:2px 6px;border-radius:4px">cotiza.cloud</code></span>
        </div>
    <?php else: ?>
        <div style="margin-top:8px;font-size:12px;color:var(--t3)">
            Sin dominio custom — usando <strong><?= e($emp['slug']) ?>.cotiza.cloud</strong>
        </div>
    <?php endif; ?>
</div>

<!-- Info de empresa -->
<div class="info-grid">
    <div class="info-item">
        <label>Moneda</label>
        <span><?= e($emp['moneda'] ?? 'MXN') ?></span>
    </div>
    <div class="info-item">
        <label>Impuesto</label>
        <span><?= e($emp['impuesto_modo'] ?? 'ninguno') ?> <?= $emp['impuesto_pct'] ? '(' . $emp['impuesto_pct'] . '% ' . e($emp['impuesto_nombre'] ?? 'IVA') . ')' : '' ?></span>
    </div>
    <div class="info-item">
        <label>Registrada</label>
        <span><?= $emp['created_at'] ? date('d/m/Y H:i', strtotime($emp['created_at'])) : '—' ?></span>
    </div>
    <div class="info-item">
        <label>Radar modo</label>
        <span class="badge badge-<?= match($radar_modo) { 'agresivo'=>'amber', 'ligero'=>'blue', default=>'green' } ?>"><?= e($radar_modo) ?></span>
    </div>
    <?php if (!empty($emp['email'])): ?>
    <div class="info-item">
        <label>Email</label>
        <span><?= e($emp['email']) ?></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($emp['telefono'])): ?>
    <div class="info-item">
        <label>Teléfono</label>
        <span><?= e($emp['telefono']) ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="cards">
    <div class="card">
        <div class="card-label">Usuarios</div>
        <div class="card-val"><?= count($usuarios) ?></div>
    </div>
    <div class="card">
        <div class="card-label">Cotizaciones</div>
        <div class="card-val num"><?= $num_cots ?></div>
    </div>
    <div class="card">
        <div class="card-label">Ventas</div>
        <div class="card-val num"><?= $num_ventas ?></div>
    </div>
    <div class="card">
        <div class="card-label">Clientes</div>
        <div class="card-val num"><?= $num_clientes ?></div>
    </div>
    <div class="card">
        <div class="card-label">Artículos</div>
        <div class="card-val num"><?= $num_articulos ?></div>
    </div>
</div>

<!-- Cotizaciones por estado -->
<?php if ($estados): ?>
<div class="section">
    <h2>Cotizaciones por estado</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <?php foreach ($estados as $est => $n): ?>
            <span class="badge <?= $badge_estado[$est] ?? 'badge-slate' ?>" style="font-size:12px;padding:5px 12px">
                <?= e(ucfirst($est)) ?>: <?= $n ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Origen del registro -->
<div class="section">
    <h2>Origen del primer acceso</h2>
    <?php if ($primer_login): ?>
    <div class="tbl-wrap">
    <table>
    <tbody>
    <tr>
        <td style="font-weight:600;width:140px">IP</td>
        <td>
            <a href="https://ipinfo.io/<?= e($primer_login['ip']) ?>" target="_blank" rel="noopener"
               class="num" style="color:var(--blue);text-decoration:none;font-weight:600"><?= e($primer_login['ip']) ?></a>
            <span style="color:var(--t3);font-size:12px"> — clic para ver ciudad y proveedor</span>
        </td>
    </tr>
    <tr>
        <td style="font-weight:600">Dispositivo</td>
        <td><?= e(sa_dispositivo($primer_login['user_agent'] ?? '')) ?></td>
    </tr>
    <tr>
        <td style="font-weight:600">Fecha</td>
        <td><span class="ago"><?= date('d/m/Y H:i', strtotime($primer_login['created_at'])) ?></span></td>
    </tr>
    <tr>
        <td style="font-weight:600">Usuario</td>
        <td><?= e($primer_login['usuario_nombre']) ?></td>
    </tr>
    </tbody>
    </table>
    </div>
    <div style="font-size:12px;color:var(--t3);margin-top:8px;line-height:1.5">
        Aproximado: con datos móviles o VPN la ciudad puede no ser exacta. El registro no guarda IP; este dato viene del primer inicio de sesión.
    </div>
    <?php else: ?>
    <div style="color:var(--t3);font-size:13px">Sin sesiones registradas todavía (aún no inicia sesión).</div>
    <?php endif; ?>
</div>

<!-- Consentimiento legal -->
<div class="section">
    <h2>Aceptación de Términos y Privacidad</h2>
    <?php if ($consentimientos): ?>
    <div class="tbl-wrap">
    <table>
    <thead>
    <tr>
        <th>Documento</th>
        <th>Versión</th>
        <th>Fecha y hora</th>
        <th>IP</th>
        <th>Método</th>
        <th>Hash (SHA-256)</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($consentimientos as $cs):
        $doc_lbl = $cs['tipo'] === 'terminos' ? 'Términos y Condiciones' : 'Aviso de Privacidad';
    ?>
    <tr>
        <td style="font-weight:600"><?= e($doc_lbl) ?></td>
        <td class="num"><?= e($cs['version']) ?></td>
        <td><span class="ago"><?= e(date('d/m/Y H:i:s', strtotime($cs['aceptado_at']))) ?></span></td>
        <td>
            <a href="https://ipinfo.io/<?= e($cs['ip']) ?>" target="_blank" rel="noopener"
               class="num" style="color:var(--blue);text-decoration:none"><?= e($cs['ip']) ?></a>
        </td>
        <td><span class="badge badge-green"><?= e($cs['metodo']) ?></span></td>
        <td class="num" style="font-size:11px;color:var(--t3)" title="<?= e($cs['hash_sha256']) ?>"><?= e(substr($cs['hash_sha256'], 0, 16)) ?>…</td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <div style="font-size:12px;color:var(--t3);margin-top:8px;line-height:1.5">
        Evidencia de consentimiento (clickwrap) registrada al momento del alta: documento, versión exacta aceptada, fecha con milisegundos, IP, método y hash del texto. Conforme al Código de Comercio (arts. 89 bis, 90, 93).
    </div>
    <?php else: ?>
    <div style="color:var(--t3);font-size:13px">Sin registro de consentimiento. Las empresas dadas de alta antes de junio 2026 (o por el panel superadmin) no tienen este registro.</div>
    <?php endif; ?>
</div>

<!-- Usuarios -->
<div class="section">
    <h2>Usuarios</h2>
    <div class="tbl-wrap">
    <table>
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Email</th>
        <th>Rol</th>
        <th>Estado</th>
        <th>Último login</th>
        <th>Creado</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
    <tr>
        <td style="font-weight:600"><?= e($u['nombre']) ?></td>
        <td class="num" style="font-size:12.5px"><?= e($u['email']) ?></td>
        <td><span class="badge <?= $u['rol'] === 'admin' ? 'badge-amber' : 'badge-slate' ?>"><?= e($u['rol']) ?></span></td>
        <td><span class="badge <?= $u['activo'] ? 'badge-green' : 'badge-red' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
        <td><span class="ago"><?= $u['ultimo_login'] ? sa_hace(strtotime($u['ultimo_login'])) : 'nunca' ?></span></td>
        <td><span class="ago"><?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>

<!-- Últimas cotizaciones -->
<?php if ($ultimas_cots): ?>
<div class="section">
    <h2>Últimas 10 cotizaciones</h2>
    <div class="tbl-wrap">
    <table>
    <thead>
    <tr>
        <th>Folio</th>
        <th>Cliente</th>
        <th>Estado</th>
        <th>Total</th>
        <th>Creada</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($ultimas_cots as $c): ?>
    <tr>
        <td class="num" style="font-weight:600;font-size:12.5px"><?= e($c['numero'] ?? $c['slug']) ?></td>
        <td><?= e($c['cliente_nombre'] ?? '—') ?></td>
        <td><span class="badge <?= $badge_estado[$c['estado']] ?? 'badge-slate' ?>"><?= e(ucfirst($c['estado'])) ?></span></td>
        <td class="num"><?= sa_money((float)($c['total'] ?? 0)) ?></td>
        <td><span class="ago"><?= $c['created_at'] ? sa_hace(strtotime($c['created_at'])) : '—' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

</div>

<script>if(typeof feather!=='undefined'){feather.replace();}</script>

<!-- Push Notifications (solo carga en app nativa Capacitor) -->
<script src="/assets/js/push.js?v=2"></script>

</body>
</html>
