<?php
// ============================================================
//  CotizaApp — public/cotizacion.php
//  GET /c/:slug   (sin login)
// ============================================================

defined('COTIZAAPP') or die;

$slug = $slug ?? '';
if (!$slug) { http_response_code(404); die('No encontrado'); }

// ─── Cargar cotización ───────────────────────────────────
$cot = DB::row(
    "SELECT c.*, e.nombre AS emp_nombre, e.ciudad AS emp_ciudad,
            e.telefono AS emp_tel, e.email AS emp_email,
            e.website AS emp_web, e.direccion AS emp_direccion, e.rfc AS emp_rfc,
            e.moneda, e.logo_url AS emp_logo,
            e.impuesto_modo, e.impuesto_pct, e.impuesto_nombre AS impuesto_label,
            e.cot_terminos AS terminos, e.cot_footer, e.cot_encabezado, e.cot_theme,
            e.texto_aceptar, e.texto_rechazar,
            e.slug AS emp_slug, e.ocultar_cant_pu,
            cl.nombre AS cliente_nombre, cl.telefono AS cli_tel, cl.email AS cli_email,
            u.nombre  AS asesor_nombre
     FROM cotizaciones c
     JOIN empresas  e  ON e.id = c.empresa_id
     LEFT JOIN clientes cl ON cl.id = c.cliente_id
     LEFT JOIN usuarios u  ON u.id  = COALESCE(c.vendedor_id, c.usuario_id)
     WHERE c.slug = ? AND c.empresa_id = ?",
    [$slug, EMPRESA_ID]
);

if (!$cot) { http_response_code(404); die('Cotización no encontrada'); }

// ─── Cotización suspendida — bloquear acceso público ───────
if (!empty($cot['suspendida'])) {
    http_response_code(200);
    $emp_nombre = htmlspecialchars($cot['emp_nombre'], ENT_QUOTES, 'UTF-8');
    $emp_tel    = htmlspecialchars($cot['emp_tel'] ?? '', ENT_QUOTES, 'UTF-8');
    $emp_email  = htmlspecialchars($cot['emp_email'] ?? '', ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cotización no disponible</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
      *{margin:0;padding:0;box-sizing:border-box}
      body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8f8f6;font-family:"Plus Jakarta Sans",sans-serif;padding:24px}
      .card{background:#fff;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,.08);max-width:420px;width:100%;padding:40px 32px;text-align:center}
      .ico{width:56px;height:56px;border-radius:50%;background:#fff7ed;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:28px}
      h1{font-size:18px;font-weight:700;color:#1a1a1a;margin-bottom:8px}
      p{font-size:14px;color:#6a6a64;line-height:1.6;margin-bottom:20px}
      .contact{display:flex;flex-direction:column;gap:8px}
      .contact a{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:10px;background:#f0fdf4;color:#1a5c38;font-weight:600;font-size:13px;text-decoration:none;transition:background .15s}
      .contact a:hover{background:#dcfce7}
    </style></head><body>
    <div class="card">
      <div class="ico">⏸</div>
      <h1>Cotización no disponible</h1>
      <p>Para activar esta cotización, favor de contactar a su asesor.</p>
      <div class="contact">';
    if ($emp_tel) echo "<a href=\"tel:{$emp_tel}\">📞 {$emp_tel}</a>";
    if ($emp_email) echo "<a href=\"mailto:{$emp_email}\">✉ {$emp_email}</a>";
    echo '</div></div></body></html>';
    exit;
}

// ─── Líneas ──────────────────────────────────────────────
$todas_lineas = DB::query(
    "SELECT * FROM cotizacion_lineas WHERE cotizacion_id = ? ORDER BY orden ASC",
    [$cot['id']]
);
$lineas       = array_filter($todas_lineas, fn($l) => empty($l['es_extra']));
$lineas       = array_values($lineas);
$lineas_extra = array_filter($todas_lineas, fn($l) => !empty($l['es_extra']));
$lineas_extra = array_values($lineas_extra);

// ─── Archivos adjuntos ─────────────────────────────────────
$adjuntos = DB::query(
    "SELECT nombre_original, nombre_archivo, mime_type, tamano_bytes
     FROM cotizacion_archivos WHERE cotizacion_id = ? ORDER BY id ASC LIMIT 3",
    [$cot['id']]
);

// ─── Descuento automático activo ─────────────────────────
$adc_on  = (bool)$cot['descuento_auto_activo'];
$adc_pct = (float)$cot['descuento_auto_pct'];
$adc_exp = $cot['descuento_auto_expira'] ? strtotime($cot['descuento_auto_expira']) : 0;
if ($adc_exp && $adc_exp < time()) { $adc_on = false; }

// ─── Calcular totales ────────────────────────────────────
$subtotal = array_sum(array_column($lineas, 'subtotal'));

// Si ya fue aceptada, usar los valores guardados al momento de la aceptación
if ($cot['estado'] === 'aceptada' || $cot['estado'] === 'convertida') {
    $desc_auto_amt = (float)($cot['descuento_auto_amt'] ?? 0);
    $cupon_monto_guardado = (float)($cot['cupon_monto'] ?? 0);
    $base = $subtotal - $desc_auto_amt - $cupon_monto_guardado;
} else {
    $desc_auto_amt = $adc_on ? round($subtotal * $adc_pct / 100, 2) : 0;
    $cupon_monto_guardado = 0;
    $base = $subtotal - $desc_auto_amt;
}
$impuesto_amt = 0;
if ($cot['impuesto_modo'] === 'suma') {
    $impuesto_amt = round($base * ((float)$cot['impuesto_pct'] / 100), 2);
} elseif ($cot['impuesto_modo'] === 'incluido') {
    $impuesto_amt = round($base - $base / (1 + (float)$cot['impuesto_pct'] / 100), 2);
}
$subtotal_extras = array_sum(array_column($lineas_extra, 'subtotal'));
$total_base = $base + ($cot['impuesto_modo'] === 'suma' ? $impuesto_amt : 0) + $subtotal_extras;

// ─── Cupones disponibles (para JS) ───────────────────────
$cupones = DB::query(
    "SELECT codigo, porcentaje AS pct_descuento, descripcion, vencimiento_fecha AS fecha_vencimiento
     FROM cupones WHERE empresa_id = ? AND activo = 1",
    [EMPRESA_ID]
);

// ═══════════════════════════════════════════════════════════
//  REGISTRO DE VISITA SERVER-SIDE
//  Regla principal: si el usuario está logueado en esta empresa
//  → aprender su IP + visitor_id + UA como internos → NO contar visita.
//  Solo si pasa todos los filtros → es un cliente real → contar.
// ═══════════════════════════════════════════════════════════
$ip  = ip_real();
$ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ref = $_SERVER['HTTP_REFERER'] ?? '';

// Leer visitor_id desde cookie (key 'cz_vid' — mismo que usa el JS)
// Disponible desde la primera carga, antes de que JS envíe cualquier evento
$visitor_id_cookie = substr(
    preg_replace('/[^a-zA-Z0-9\-_]/', '', (string)($_COOKIE['cz_vid'] ?? '')),
    0, 64
);

require_once MODULES_PATH . '/radar/Radar.php';

// ── CAPA 0: Usuario logueado de esta empresa ──────────────────────────
// Es la verificación más importante y debe ser la primera.
// Certeza absoluta: conocemos usuario_id, IP, UA y visitor_id.
// Aprendemos todo y no registramos nada — es ruido interno.
$es_usuario_interno = (Auth::id() !== null && (int)(Auth::empresa()['id'] ?? 0) === (int)$cot['empresa_id']);

if ($es_usuario_interno) {
    // Aprender IP de este acceso como interna
    Radar::aprender_ip_radar((int)$cot['empresa_id'], $ip);
    // Aprender visitor_id del navegador con alta confianza
    if ($visitor_id_cookie !== '') {
        Radar::marcar_visitor_interno(
            (int)$cot['empresa_id'],
            $visitor_id_cookie,
            'internal_user',
            (int)Auth::id(),
            $ip,
            $ua
        );
    }
    // No registrar visita ni eventos — salir
    goto skip_tracking;
}

// ── A partir de aquí: visitante no logueado ───────────────────────────
if (!es_bot($ua) && in_array($cot['estado'], ['enviada','vista','aceptada','rechazada'])) {
    try {
        // ── CAPA 1: visitor_id ya conocido como interno ───────────────
        // El asesor abrió esto antes logueado — su UUID ya está en la lista negra
        if ($visitor_id_cookie !== '' && Radar::es_visitor_interno((int)$cot['empresa_id'], $visitor_id_cookie)) {
            goto skip_tracking;
        }

        // ── CAPA 2: IP interna conocida ───────────────────────────────
        // Asesor sin login desde la oficina, o home office con IP aprendida antes
        $es_ip_interna = (bool)DB::val(
            "SELECT 1 FROM radar_ips_internas WHERE empresa_id=? AND ip=? LIMIT 1",
            [(int)$cot['empresa_id'], $ip]
        );
        if ($es_ip_interna) {
            // Aprovechar para aprender el visitor_id de este navegador
            if ($visitor_id_cookie !== '') {
                Radar::marcar_visitor_interno((int)$cot['empresa_id'], $visitor_id_cookie, 'internal_ip', null, $ip, $ua);
            }
            goto skip_tracking;
        }

        // ── CAPA 3: Bot por IP prefix ─────────────────────────────────
        foreach (Radar::BOT_IP as $prefix) {
            if (str_starts_with($ip, $prefix)) goto skip_tracking;
        }

        // ── Pasa todos los filtros → cliente real ─────────────────────
        // Deduplicación por IP: una sesión cada 30 minutos (igual que el original)
        $session_existe = DB::row(
            "SELECT id FROM quote_sessions
             WHERE cotizacion_id=? AND ip=? AND activa=1
               AND updated_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
             LIMIT 1",
            [$cot['id'], $ip]
        );

        if (!$session_existe) {
            // Nueva sesión — registrar y contar visita
            DB::insert(
                "INSERT INTO quote_sessions (cotizacion_id, ip, user_agent, visitor_id, activa)
                 VALUES (?,?,?,?,1)",
                [$cot['id'], $ip, substr($ua,0,300), $visitor_id_cookie ?: null]
            );

            // Actualizar estado a "vista" si era "enviada"
            if ($cot['estado'] === 'enviada') {
                DB::execute(
                    "UPDATE cotizaciones SET estado='vista', vista_at=NOW() WHERE id=? AND estado='enviada'",
                    [$cot['id']]
                );
                $cot['estado'] = 'vista';
            }

            // Incrementar visitas SOLO en sesión nueva — no en recargas
            DB::execute(
                "UPDATE cotizaciones SET ultima_vista_at=NOW(), visitas=visitas+1 WHERE id=?",
                [$cot['id']]
            );

            // Recalcular Radar con cada visita nueva
            if (in_array($cot['estado'], ['enviada','vista'])) {
                try { Radar::recalcular((int)$cot['id'], (int)$cot['empresa_id']); } catch (\Throwable $re) {}
            }

        } else {
            // Sesión ya existe — solo actualizar timestamp (heartbeat)
            // NO incrementar visitas — evita el bug de vistas que suben al recargar
            DB::execute("UPDATE quote_sessions SET updated_at=NOW() WHERE id=?", [$session_existe['id']]);
        }

    } catch (Exception $e) {
        // Silencioso — nunca romper la vista por error de tracking
    }
}
skip_tracking:

// ─── Helpers locales ─────────────────────────────────────
function fmt_pub(float $n, string $moneda = 'MXN'): string {
    return '$' . number_format($n, 2, '.', ',');
}

function iniciales_emp(string $nombre): string {
    $palabras = array_filter(explode(' ', $nombre));
    $ini = '';
    foreach (array_slice($palabras, 0, 2) as $p) { $ini .= strtoupper($p[0]); }
    return $ini ?: 'CO';
}

$ini_emp = iniciales_emp($cot['emp_nombre']);
$estado  = $cot['estado'];
$es_activa = in_array($estado, ['enviada','vista','borrador']);

// Badge de estado para el header
$badge_map = [
    'borrador'   => ['#f1f5f9','#475569','Borrador'],
    'enviada'    => ['#dbeafe','#1d4ed8','Enviada'],
    'vista'      => ['#ede9fe','#6d28d9','Vista'],
    'aceptada'   => ['#eef7f2','#1a5c38','Aceptada'],
    'rechazada'  => ['#fff5f5','#c53030','Rechazada'],
    'vencida'    => ['#fef3c7','#92400e','Vencida'],
    'convertida' => ['#eef7f2','#1a5c38','Convertida'],
];
[$badge_bg, $badge_color, $badge_lbl] = $badge_map[$estado] ?? ['#f1f5f9','#475569',ucfirst($estado)];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
<title>Cotización · <?= e($cot['emp_nombre']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
<?php
$themes = [
    'verde'   => ['g'=>'#1a6b3c','glt'=>'#edf7f2','gbd'=>'#b6ddc7'],
    'azul'    => ['g'=>'#1d4ed8','glt'=>'#eff6ff','gbd'=>'#bfdbfe'],
    'rojo'    => ['g'=>'#b91c1c','glt'=>'#fef2f2','gbd'=>'#fecaca'],
    'naranja' => ['g'=>'#e8a317','glt'=>'#fffdf5','gbd'=>'#fde68a'],
    'dorado'  => ['g'=>'#92400e','glt'=>'#fffbeb','gbd'=>'#fde68a'],
    'morado'  => ['g'=>'#6d28d9','glt'=>'#f5f3ff','gbd'=>'#c4b5fd'],
    'oscuro'  => ['g'=>'#1e293b','glt'=>'#f1f5f9','gbd'=>'#cbd5e1'],
];
$th = $themes[$cot['cot_theme'] ?? 'verde'] ?? $themes['verde'];
$ocultar_cp = !empty($cot['ocultar_cant_pu']);
?>
:root{--g:<?=$th['g']?>;--glt:<?=$th['glt']?>;--gbd:<?=$th['gbd']?>;--text:#111;--t2:#444;--t3:#888;--bd:#d8d8d8;--bg:#f7f7f5;--white:#fff;--amb:#92400e;--red:#b91c1c;--r:6px}
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:17px;-webkit-text-size-adjust:100%}
body{font-family:'Plus Jakarta Sans',-apple-system,sans-serif;background:var(--bg);color:var(--text);-webkit-font-smoothing:antialiased;overflow-x:hidden}

.hdr{background:var(--white);border-bottom:2px solid var(--text);text-align:center;padding:12px 20px 0}
.hdr-inner{max-width:960px;margin:0 auto}
.hdr-logo{width:160px;height:70px;background:var(--g);color:#fff;font:700 28px 'Plus Jakarta Sans',sans-serif;display:inline-flex;align-items:center;justify-content:center;margin-bottom:6px}
.hdr-co{font:800 22px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em;margin-bottom:2px}
.hdr-tag{font-size:13px;color:var(--t3);line-height:1.3}
.hdr-rfc{font:500 11px 'DM Sans',sans-serif;color:var(--t3);letter-spacing:.04em;margin-top:1px;margin-bottom:4px}
.vbadge{display:none} /* oculto al cliente */
.print-fac,.print-info{display:none} /* solo para impresión — se activan en @media print */
.vdot{width:6px;height:6px;border-radius:50%;animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.hdr-cnt{display:flex;flex-wrap:wrap;justify-content:center;gap:3px 16px;font-size:13px;color:var(--t2);padding-bottom:10px}
.hdr-cnt a{color:var(--t2);text-decoration:none}
.tabs{display:flex;justify-content:center;border-top:1px solid var(--bd)}
.tab{padding:12px 28px;font:600 11px 'Plus Jakarta Sans',sans-serif;letter-spacing:.08em;text-transform:uppercase;color:var(--t3);background:none;border:none;border-bottom:2.5px solid transparent;cursor:pointer;transition:all .15s}
.tab.on{color:var(--g);border-bottom-color:var(--g)}

.body{padding:0 0 60px}
.wrap{max-width:960px;margin:0 auto;padding:0 20px}
.slbl{font:700 14px 'Plus Jakarta Sans',sans-serif;letter-spacing:.04em;text-transform:uppercase;color:var(--text);margin:28px 0 12px;display:flex;align-items:center;gap:12px}
.slbl::after{content:'';flex:1;height:1px;background:var(--bd)}


.qh{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden}
.qh-top{padding:20px 22px 16px;border-bottom:1px solid var(--bd)}
.qh-title{font:800 24px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.025em;line-height:1.2;margin-bottom:4px}
.qh-client{font-size:15px;color:var(--t2)}
.qh-client span{color:var(--t3);font-size:13px}
.qh-pills{padding:14px 22px;display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:8px}
.pill{display:flex;flex-direction:column;padding:10px 16px;background:var(--bg);border:1px solid var(--bd);border-radius:12px}
.pill-label{font-size:10.5px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
.pill-value{font:600 14px 'DM Sans',sans-serif;color:var(--text)}
.chip{padding:4px 12px;background:var(--bg);border:1px solid var(--bd);border-radius:99px;font-size:13px;color:var(--t2)}
.chip-danger{background:#fff5f5;border-color:#fca5a5;color:#c53030}
.chip-warn{background:#fffbeb;border-color:#fcd34d;color:#92400e}

.tbl{width:100%;border-collapse:collapse;background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;font-size:15px}
.tbl thead tr{background:var(--bg)}
.tbl th{padding:10px 16px;font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.1em;text-transform:uppercase;color:var(--t3);text-align:left;border-bottom:1px solid var(--bd)}
.tbl th.r{text-align:right}
.tbl td{padding:14px 16px;border-bottom:1px solid var(--bd);vertical-align:top}
.tbl tr:last-child td{border-bottom:none}
.iname{font:600 17px 'Plus Jakarta Sans',sans-serif}
.isku{font-size:13px;color:var(--t3);margin-bottom:6px}
.idesc{font-size:15px;color:var(--t2);line-height:1.55}
.idesc a{color:var(--g);text-decoration:none}
.tqty{font-size:15px;color:var(--t2);white-space:nowrap}
.tamt{font:600 17px 'Plus Jakarta Sans',sans-serif;text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}

@media(max-width:600px){
  .tbl{display:none}
  .items-mob{display:flex;flex-direction:column;background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden}
  .im{border-bottom:1px solid var(--bd);padding:18px 16px}
  .im:last-child{border-bottom:none}
  .im-name{font:600 20px 'Plus Jakarta Sans',sans-serif;margin-bottom:3px}
  .im-sku{font-size:14px;color:var(--t3);margin-bottom:7px}
  .im-desc{font-size:17px;color:var(--t2);line-height:1.55;margin-bottom:0}
  .im-desc a{color:var(--g);text-decoration:none}
  .im-meta{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-top:14px;padding-top:14px;border-top:1px dashed var(--bd)}
  .im-meta-left{display:flex;gap:16px}
  .im-meta-chip{font-size:14px;color:var(--t2)}
  .im-meta-chip span{font-weight:600;color:var(--text)}
  .im-meta-total{font:700 19px 'Plus Jakarta Sans',sans-serif;font-variant-numeric:tabular-nums;white-space:nowrap}
}
@media(min-width:601px){.items-mob{display:none}}

.tots{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden}
.tr{display:flex;justify-content:space-between;align-items:center;padding:13px 20px;border-bottom:1px solid var(--bd);font-size:16px}
.tr:last-child{border-bottom:none}
.tl{color:var(--t2)}
.tv{font:600 16px 'Plus Jakarta Sans',sans-serif;font-variant-numeric:tabular-nums}
.td .tl,.td .tv{color:var(--amb)}
.tf{background:var(--bg);border-top:2px solid var(--text)!important}
.tf .tl{font:700 17px 'Plus Jakarta Sans',sans-serif}
.tf .tv{font:800 24px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em}

/* Cronómetro */
.adc{background:var(--glt);border:1px solid var(--gbd);border-radius:var(--r);overflow:hidden}
.adc-t{padding:16px 20px;border-bottom:1px solid var(--gbd);display:flex;align-items:center;justify-content:space-between;gap:12px}
.adc-ey{font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.1em;text-transform:uppercase;color:var(--g);margin-bottom:8px}
.adc-or{font-size:15px;color:var(--t3);text-decoration:line-through;margin-bottom:2px}
.adc-nw{font:800 28px 'Plus Jakarta Sans',sans-serif;color:var(--text);letter-spacing:-.03em}
.adc-pc{background:var(--g);color:#fff;font:700 16px 'Plus Jakarta Sans',sans-serif;padding:10px 16px;border-radius:var(--r);flex-shrink:0}
.adc-f{padding:12px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
.adc-el{font-size:14px;color:var(--t2)}
.tmr{display:flex;align-items:flex-start;gap:3px}
.tblk{text-align:center}
.tn{display:block;font:700 17px 'Plus Jakarta Sans',sans-serif;color:var(--text);background:var(--white);border:1px solid var(--gbd);padding:5px 9px;min-width:40px;line-height:1;border-radius:4px;font-variant-numeric:tabular-nums}
.tu{font-size:9px;color:var(--t3);letter-spacing:.08em;text-transform:uppercase;margin-top:4px;text-align:center}
.ts{font:700 17px 'Plus Jakarta Sans',sans-serif;color:var(--t3);padding:5px 1px 0}
.urg .tn{color:var(--red);border-color:#fca5a5;background:#fef2f2}

/* Cupón */
.coup{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden}
.coup-b{padding:16px 20px}
.coup-l{font-size:14px;color:var(--t2);margin-bottom:10px}
.coup-r{display:flex;gap:8px}
.cinp{flex:1;min-width:0;padding:12px 14px;border:1.5px solid var(--bd);border-radius:var(--r);font:600 16px 'Plus Jakarta Sans',sans-serif;color:var(--text);background:var(--bg);outline:none;text-transform:uppercase;letter-spacing:.04em;transition:border-color .15s}
.cinp:focus{border-color:var(--g)}
.cinp.err{border-color:var(--red)}
.cbtn{padding:12px 20px;background:var(--g);border:none;border-radius:var(--r);font:600 14px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;flex-shrink:0}
.cerr{font-size:13px;color:var(--red);margin-top:8px;display:none}
.cerr.on{display:block}
.capp{padding:12px 20px;background:var(--glt);border-top:1px solid var(--gbd);display:none;align-items:center;justify-content:space-between;gap:10px}
.capp.on{display:flex}
.ccode{font:700 14px 'Plus Jakarta Sans',sans-serif;color:var(--g)}
.cdesc{font-size:12px;color:var(--g);opacity:.7;margin-top:1px}
.cpct{font:700 16px 'Plus Jakarta Sans',sans-serif;color:var(--g)}
.crm{background:none;border:none;font-size:12px;color:var(--t3);cursor:pointer;text-decoration:underline}

/* CTAs */
.cta{display:flex;flex-direction:column;gap:10px}
.bacc{width:100%;padding:16px;background:var(--g);border:none;border-radius:var(--r);font:700 16px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;letter-spacing:-.01em}
.brej{width:100%;padding:13px;background:var(--white);border:1.5px solid var(--bd);border-radius:var(--r);font:500 15px 'Plus Jakarta Sans',sans-serif;color:var(--t2);cursor:pointer}
.bprt{width:100%;padding:12px;background:var(--bg);border:1px solid var(--bd);border-radius:var(--r);font:500 14px 'Plus Jakarta Sans',sans-serif;color:var(--t2);cursor:pointer}

/* Notas */
.notes{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);padding:16px 20px;font-size:16px;color:var(--t2);line-height:1.7}
/* Términos */
.terms{background:var(--white);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden}
.term{padding:13px 18px;border-bottom:1px solid var(--bd)}
.term:last-child{border-bottom:none}
.terml{font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.09em;text-transform:uppercase;color:var(--t3);margin-bottom:5px}
.termv{font-size:15px;color:var(--t2);line-height:1.6}
.termv a{color:var(--g)}

/* Footer */
.footer{background:var(--white);border-top:2px solid var(--text);padding:20px 20px 40px;text-align:center}
.footer-inner{max-width:960px;margin:0 auto}
.flogo{width:80px;height:80px;border-radius:14px;background:var(--g);color:#fff;font:700 22px 'Plus Jakarta Sans',sans-serif;display:inline-flex;align-items:center;justify-content:center;margin-bottom:10px}
.fname2{font:700 15px 'Plus Jakarta Sans',sans-serif;margin-bottom:2px}
.fsub{font-size:13px;color:var(--t3);margin-bottom:14px}
.fdisc{font-size:12px;color:var(--t3);line-height:1.65;max-width:480px;margin:0 auto}

/* Pantalla de éxito */
.succ{display:none;padding:60px 20px 40px;text-align:center;max-width:480px;margin:0 auto}
.succ.on{display:block}
.sico{font-size:48px;margin-bottom:14px}
.stit{font:800 22px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em;margin-bottom:10px}
.smsg{font-size:16px;color:var(--t2);line-height:1.6;margin-bottom:18px}
.sbox{background:var(--glt);border:1px solid var(--gbd);border-radius:var(--r);padding:14px 18px;font-size:14px;color:var(--g)}

/* Modales */
.ov{position:fixed;inset:0;z-index:500;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);display:flex;align-items:flex-end;justify-content:center;opacity:0;pointer-events:none;transition:opacity .22s}
.ov.on{opacity:1;pointer-events:all}
.modal{background:var(--white);border-radius:16px 16px 0 0;border-top:2px solid var(--text);padding:20px 20px 48px;width:100%;max-width:520px;transform:translateY(100%);transition:transform .28s cubic-bezier(.32,0,.15,1);max-height:92vh;overflow-y:auto}
.ov.on .modal{transform:translateY(0)}
.mpull{width:32px;height:3px;border-radius:2px;background:var(--bd);margin:0 auto 18px}
.mtit{font:800 20px 'Plus Jakarta Sans',sans-serif;letter-spacing:-.02em;margin-bottom:6px}
.msub{font-size:14px;color:var(--t2);margin-bottom:16px;line-height:1.5}
.sbox2{background:var(--bg);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-bottom:16px}
.sr{display:flex;justify-content:space-between;padding:10px 14px;font-size:14px;color:var(--t2);border-bottom:1px solid var(--bd)}
.sr:last-child{border-bottom:none}
.sr.tot{font:700 17px 'Plus Jakarta Sans',sans-serif;color:var(--text);background:var(--white)}
.acc-msg{margin-top:12px;padding:12px 14px;background:var(--bg);border-radius:10px;font:400 13px 'Plus Jakarta Sans',sans-serif;color:var(--t2);line-height:1.6;white-space:pre-wrap}
.flbl{font:700 10px 'Plus Jakarta Sans',sans-serif;letter-spacing:.09em;text-transform:uppercase;color:var(--t3);margin-bottom:7px}
.finp{width:100%;padding:13px 14px;border:1.5px solid var(--bd);border-radius:var(--r);font:400 16px 'Plus Jakarta Sans',sans-serif;color:var(--text);background:var(--bg);outline:none;margin-bottom:14px;transition:border-color .15s}
.finp:focus{border-color:var(--g)}
.mbok{width:100%;padding:15px;background:var(--g);border:none;border-radius:var(--r);font:700 15px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;margin-bottom:8px}
.mbno{width:100%;padding:13px;border:1.5px solid var(--bd);background:transparent;border-radius:var(--r);font:500 14px 'Plus Jakarta Sans',sans-serif;color:var(--t2);cursor:pointer}
.ropt{width:100%;padding:13px 16px;border:1.5px solid var(--bd);border-radius:var(--r);background:var(--bg);font:400 15px 'Plus Jakarta Sans',sans-serif;color:var(--text);cursor:pointer;text-align:left;margin-bottom:8px;display:block;transition:all .12s}
.ropt.on{border-color:var(--g);background:var(--glt);color:var(--g);font-weight:600}
.rtxt{width:100%;padding:12px 14px;border:1.5px solid var(--bd);border-radius:var(--r);background:var(--bg);font:400 15px 'Plus Jakarta Sans',sans-serif;color:var(--text);resize:none;outline:none;margin-bottom:14px;display:none}
.rtxt.on{display:block}
.mbrej{width:100%;padding:15px;background:var(--red);border:none;border-radius:var(--r);font:700 15px 'Plus Jakarta Sans',sans-serif;color:#fff;cursor:pointer;margin-bottom:8px}

/* ── PRINT: Factura B&N láser ── */
.print-fac { display:none }
@media print{
  @page { margin:12mm 14mm; size:letter }
  *{ -webkit-print-color-adjust:exact; print-color-adjust:exact }
  body { background:#fff; font-family:'Plus Jakarta Sans',sans-serif; font-size:9.5pt; color:#000 }

  /* Ocultar todo lo interactivo */
  .hdr,.tabs,.vbadge,.cta,.ov,.succ,#coupLbl,#coupSec,#adcSec,
  .items-mob,.bprt,.footer { display:none!important }

  /* Mostrar solo sección cotización */
  #tab-d,#tab-t { display:block!important }
  .body { padding:0 }
  .wrap { padding:0; max-width:100% }

  /* ── Encabezado factura ── */
  .print-fac { display:flex!important; justify-content:space-between;
    align-items:flex-start; padding-bottom:9pt; border-bottom:1.5pt solid #000;
    margin-bottom:9pt }
  .pf-left {}
  .pf-emp  { font:800 15pt 'Plus Jakarta Sans',sans-serif; letter-spacing:-.02em; margin-bottom:3pt }
  .pf-sub  { font:400 8pt 'Plus Jakarta Sans',sans-serif; color:#444; line-height:1.5 }
  .pf-right{ text-align:right }
  .pf-tipo { font:300 24pt 'DM Sans',sans-serif; letter-spacing:-.03em; color:#000; line-height:1 }
  .pf-folio{ font:400 8pt 'DM Sans',sans-serif; color:#555; margin-top:2pt }

  /* ── Fila info (cliente / fecha / validez) ── */
  .print-info { display:flex!important; gap:0; border:1pt solid #ccc;
    border-radius:2pt; overflow:hidden; margin-bottom:7pt }
  .pi-cell { flex:1; padding:5pt 8pt; border-right:1pt solid #ccc }
  .pi-cell:last-child { border-right:none }
  .pi-lbl  { font:700 6.5pt 'Plus Jakarta Sans',sans-serif; letter-spacing:.08em;
    text-transform:uppercase; color:#555; margin-bottom:2pt }
  .pi-val  { font:600 9pt 'Plus Jakarta Sans',sans-serif; color:#000 }

  /* ── Ocultar header web / slbls ── */
  .slbl { display:none!important }
  .slbl.slbl-print { display:flex!important; font:700 11pt var(--body);letter-spacing:.1em;text-transform:uppercase;margin:14pt 0 6pt;padding-top:8pt;border-top:2pt solid #000;align-items:center;gap:8pt }
  .slbl.slbl-print::after { content:'';flex:1;height:1pt;border-bottom:1pt dashed #999 }
  .qh   { display:none!important }

  /* ── Encabezado/saludo compacto en print ── */
  .encabezado-saludo { font-size:8.5pt!important; line-height:1.4!important; padding:6pt 8pt!important; margin:4pt 0 6pt!important; border:.4pt solid #ddd!important; border-radius:0!important }

  /* ── Tabla artículos ── */
  .tbl { display:table!important; width:100%; border-collapse:collapse; margin-bottom:0; border:none!important; border-radius:0!important }
  .tbl thead th { font:700 7pt 'Plus Jakarta Sans',sans-serif; letter-spacing:.07em;
    text-transform:uppercase; color:#000; padding:4pt 7pt;
    border-bottom:1.5pt solid #000; text-align:left }
  .tbl thead th.r { text-align:right }
  .tbl td { padding:4pt 7pt; border-bottom:.4pt solid #ddd; vertical-align:top;
    font:400 9pt 'Plus Jakarta Sans',sans-serif; color:#000 }
  .tbl tr:last-child td { border-bottom:none }
  .iname { font:600 9pt 'Plus Jakarta Sans',sans-serif }
  .isku  { font:400 7pt 'DM Sans',sans-serif; color:#666; margin-top:1pt }
  .idesc { font:400 7.5pt 'Plus Jakarta Sans',sans-serif; color:#555;
    margin-top:1pt; line-height:1.4 }
  .tqty  { font:400 8pt 'DM Sans',sans-serif; color:#444; white-space:nowrap }
  .tamt  { font:500 9pt 'DM Sans',sans-serif; color:#000; text-align:right }

  /* ── Totales ── */
  .tots  { border:none!important; border-radius:0!important; margin-top:0 }
  .tr    { padding:3pt 7pt; font:400 9pt 'Plus Jakarta Sans',sans-serif;
    border-bottom:.3pt solid #eee; display:flex; justify-content:space-between }
  .tl    { color:#444 }
  .tv    { font:400 9pt 'DM Sans',sans-serif; text-align:right }
  .tf    { border-top:1.5pt solid #000!important; border-bottom:none!important }
  .tf .tl{ font:700 10pt 'Plus Jakarta Sans',sans-serif; color:#000 }
  .tf .tv{ font:500 13pt 'DM Sans',sans-serif; color:#000 }

  /* ── Notas / Términos ── */
  .notes { padding:6pt 8pt; font-size:8pt; color:#333; line-height:1.5;
    border:1pt solid #ddd; border-radius:0; margin-top:8pt }
  #tab-t { margin-top:12pt; padding-top:10pt; border-top:1pt solid #ccc }
  .term  { padding:5pt 8pt; border-bottom:.3pt solid #eee }
  .terml { font:700 6.5pt 'Plus Jakarta Sans',sans-serif; letter-spacing:.08em;
    text-transform:uppercase; color:#666; margin-bottom:2pt }
  .termv { font:400 8pt 'Plus Jakarta Sans',sans-serif; color:#333; line-height:1.5 }

  /* ── Pie de página ── */
  .print-footer { display:block!important; margin-top:10pt; padding-top:7pt;
    border-top:1pt solid #ccc; font:400 7.5pt 'Plus Jakarta Sans',sans-serif;
    color:#666; text-align:center; line-height:1.6 }
}
</style>
<?= MarketingPixels::scripts_base(EMPRESA_ID) ?>
</head>
<body>

<!-- HEADER -->
<div class="hdr">
  <div class="hdr-inner">
    <?php if (!empty($cot['emp_logo'])): ?>
    <div class="hdr-logo" style="background:none;width:auto;height:auto;max-width:200px;max-height:80px"><img src="<?= e($cot['emp_logo']) ?>" alt="Logo" style="max-width:200px;max-height:80px;object-fit:contain"></div>
    <?php else: ?>
    <div class="hdr-logo"><?= e($ini_emp) ?></div>
    <?php endif; ?>
    <div class="hdr-co"><?= e($cot['emp_nombre']) ?></div>
    <?php if (!empty($cot['emp_direccion']) || !empty($cot['emp_ciudad'])): ?>
    <div class="hdr-tag"><?= e(implode(', ', array_filter([$cot['emp_direccion'] ?? '', $cot['emp_ciudad'] ?? '']))) ?></div>
    <?php endif; ?>
    <?php if (!empty($cot['emp_rfc'])): ?>
    <div class="hdr-rfc">RFC: <?= e($cot['emp_rfc']) ?></div>
    <?php endif; ?>
    <div class="hdr-cnt">
        <?php if ($cot['emp_tel']): ?><a href="tel:<?= e($cot['emp_tel']) ?>"><?= e($cot['emp_tel']) ?></a><?php endif; ?>
        <?php if ($cot['emp_email']): ?><a href="mailto:<?= e($cot['emp_email']) ?>"><?= e($cot['emp_email']) ?></a><?php endif; ?>
        <?php if (!empty($cot['emp_web'])): ?><a href="<?= e($cot['emp_web']) ?>" target="_blank"><?= e(preg_replace('#^https?://#','',$cot['emp_web'])) ?></a><?php endif; ?>
    </div>
    <div class="tabs">
        <button class="tab on" onclick="go('d',this)">Cotización</button>
    </div>
  </div>
</div>

<div class="body" id="mainBody">
<div class="wrap">

<!-- ══ TAB COTIZACIÓN ══ -->
<div id="tab-d">

<!-- PRINT: Encabezado tipo factura (solo visible al imprimir) -->
<div class="print-fac">
  <div class="pf-left">
    <div class="pf-emp"><?= e($cot['emp_nombre']) ?></div>
    <div class="pf-sub">
      <?php if (!empty($cot['emp_tel'])): ?><?= e($cot['emp_tel']) ?> &nbsp;·&nbsp; <?php endif; ?>
      <?php if (!empty($cot['emp_email'])): ?><?= e($cot['emp_email']) ?><br><?php endif; ?>
      <?php if (!empty($cot['emp_web'])): ?><?= e(preg_replace('#^https?://#','',$cot['emp_web'])) ?><br><?php endif; ?>
      <?php if (!empty($cot['emp_ciudad'])): ?><?= e($cot['emp_ciudad']) ?><?php endif; ?>
    </div>
  </div>
  <div class="pf-right">
    <div class="pf-tipo">Cotización</div>
    <div class="pf-folio"><?= e($cot['numero']) ?></div>
  </div>
</div>

<!-- PRINT: Info cliente / fechas -->
<div class="print-info">
  <div class="pi-cell">
    <div class="pi-lbl">Cliente</div>
    <div class="pi-val"><?= e($cot['cliente_nombre'] ?? '—') ?></div>
    <?php if ($cot['cli_tel'] ?? null): ?>
    <div style="font:400 8pt 'DM Sans',sans-serif;color:#555;margin-top:1pt"><?= e($cot['cli_tel']) ?></div>
    <?php endif; ?>
  </div>
  <div class="pi-cell">
    <div class="pi-lbl">Concepto</div>
    <div class="pi-val"><?= e($cot['titulo']) ?></div>
  </div>
  <div class="pi-cell">
    <div class="pi-lbl">Fecha</div>
    <div class="pi-val"><?= date('d/m/Y', strtotime($cot['created_at'])) ?></div>
  </div>
  <?php if ($cot['valida_hasta']): ?>
  <div class="pi-cell">
    <div class="pi-lbl">Válido hasta</div>
    <div class="pi-val"><?= date('d/m/Y', strtotime($cot['valida_hasta'])) ?></div>
  </div>
  <?php endif; ?>
  <?php if ($cot['asesor_nombre']): ?>
  <div class="pi-cell">
    <div class="pi-lbl">Asesor</div>
    <div class="pi-val"><?= e($cot['asesor_nombre']) ?></div>
  </div>
  <?php endif; ?>
</div>

  <!-- BLOQUE COTIZACIÓN — visible en pantalla -->
  <div class="qh">
    <div class="qh-top">
      <div class="qh-title"><?= e($cot['titulo']) ?></div>
      <?php if ($cot['cliente_nombre']): ?>
      <div class="qh-client"><?= e($cot['cliente_nombre']) ?><?php if ($cot['cli_tel']): ?> <span>· <?= e($cot['cli_tel']) ?></span><?php endif ?></div>
      <?php endif ?>
    </div>
    <div class="qh-pills">
      <div class="pill">
        <div class="pill-label">Cotización</div>
        <div class="pill-value"><?= e($cot['numero']) ?></div>
      </div>
      <div class="pill">
        <div class="pill-label">Elaboración</div>
        <div class="pill-value"><?= date('d/m/Y', strtotime($cot['created_at'])) ?></div>
      </div>
      <?php if ($cot['valida_hasta']): ?>
      <?php
        $vts = strtotime($cot['valida_hasta']);
        $vd  = ($vts - strtotime('today')) / 86400;
      ?>
      <div class="pill" <?php if ($vd < 0): ?>style="background:#fff5f5;border-color:#fca5a5"<?php elseif ($vd <= 3): ?>style="background:#fffbeb;border-color:#fcd34d"<?php endif; ?>>
        <div class="pill-label"><?= $vd < 0 ? 'Venció' : 'Vencimiento' ?></div>
        <div class="pill-value" <?php if ($vd < 0): ?>style="color:#c53030"<?php elseif ($vd <= 3): ?>style="color:#92400e"<?php endif; ?>><?= date('d/m/Y', $vts) ?></div>
      </div>
      <?php endif; ?>
      <?php if ($cot['asesor_nombre']): ?>
      <div class="pill">
        <div class="pill-label">Asesor</div>
        <div class="pill-value"><?= e($cot['asesor_nombre']) ?></div>
      </div>
      <?php endif; ?>
      <div class="pill">
        <div class="pill-label">Total</div>
        <div class="pill-value"><?= fmt_pub((float)$cot['total']) ?></div>
      </div>
    </div>
  </div>

  <!-- ENCABEZADO / SALUDO -->
  <?php
  $encabezado_raw = trim($cot['cot_encabezado'] ?? '');
  if ($encabezado_raw !== ''):
      $encabezado = str_replace(
          ['{{cliente}}', '{{empresa}}', '{{asesor}}'],
          [e($cot['cliente_nombre'] ?? ''), e($cot['emp_nombre']), e($cot['asesor_nombre'] ?? '')],
          e_html($encabezado_raw)
      );
  ?>
  <div class="encabezado-saludo" style="margin:24px 0 8px;padding:20px 24px;background:var(--white);border:1px solid var(--bd);border-radius:var(--r);font:400 15px/1.7 'Plus Jakarta Sans',sans-serif;color:var(--text)">
    <?= nl2br($encabezado) ?>
  </div>
  <?php endif; ?>

  <!-- ARTÍCULOS DESKTOP -->
  <div id="itemsBlock">
  <div class="slbl">Artículos incluidos</div>
  <table class="tbl">
    <thead><tr><th>Descripción</th><?php if (!$ocultar_cp): ?><th>Cantidad</th><?php endif; ?><th class="r">Total</th></tr></thead>
    <tbody>
    <?php foreach ($lineas as $l): ?>
    <tr>
      <td>
        <div class="iname"><?= e($l['titulo']) ?></div>
        <?php if ($l['sku']): ?><div class="isku"><?= e($l['sku']) ?></div><?php endif; ?>
        <?php if ($l['descripcion']): ?><div class="idesc"><?= nl2br(e($l['descripcion'])) ?></div><?php endif; ?>
      </td>
      <?php if (!$ocultar_cp): ?>
      <td class="tqty"><?= $l['precio_unit'] > 0 ? number_format($l['cantidad'],2).' × '.fmt_pub($l['precio_unit']) : '—' ?></td>
      <?php endif; ?>
      <td class="tamt"><?= $l['precio_unit'] > 0 ? fmt_pub($l['subtotal']) : fmt_pub(0) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- ARTÍCULOS MOBILE -->
  <div class="items-mob">
    <?php foreach ($lineas as $l): ?>
    <div class="im">
      <div class="im-name"><?= e($l['titulo']) ?></div>
      <?php if ($l['sku']): ?><div class="im-sku"><?= e($l['sku']) ?></div><?php endif; ?>
      <?php if ($l['descripcion']): ?><div class="im-desc"><?= nl2br(e($l['descripcion'])) ?></div><?php endif; ?>
      <?php if ($l['precio_unit'] > 0): ?>
      <div class="im-meta">
        <?php if (!$ocultar_cp): ?>
        <div class="im-meta-left">
          <div class="im-meta-chip">Cant. <span><?= number_format($l['cantidad'],2) ?></span></div>
          <div class="im-meta-chip">P.U. <span><?= fmt_pub($l['precio_unit']) ?></span></div>
        </div>
        <?php else: ?>
        <div class="im-meta-left"></div>
        <?php endif; ?>
        <div class="im-meta-total"><?= fmt_pub($l['subtotal']) ?></div>
      </div>
      <?php else: ?>
      <div class="im-meta"><div class="im-meta-left"></div><div class="im-meta-total"><?= fmt_pub(0) ?></div></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($lineas_extra)): ?>
  <!-- Subtotal artículos (solo si hay extras) -->
  <div style="display:flex;justify-content:flex-end;padding:8px 16px;background:var(--bg);border:1px solid var(--bd);border-top:none;border-radius:0 0 var(--r) var(--r);margin-bottom:16px">
    <span style="font:400 12px var(--body);color:var(--t3);margin-right:8px">Subtotal artículos</span>
    <span style="font:600 14px var(--num)"><?= fmt_pub($subtotal) ?></span>
  </div>

  <!-- EXTRAS — desktop -->
  <div class="slbl slbl-print" style="margin-top:20px;padding-top:10px;border-top:2px solid var(--bd)">EXTRAS</div>
  <table class="tbl">
    <thead><tr><th>Descripción</th><th class="r">Total</th></tr></thead>
    <tbody>
    <?php foreach ($lineas_extra as $le): ?>
    <tr>
      <td>
        <div class="iname"><?= e($le['titulo']) ?></div>
        <?php if ($le['descripcion']): ?><div class="idesc"><?= nl2br(e($le['descripcion'])) ?></div><?php endif; ?>
      </td>
      <td class="tamt"><?= fmt_pub((float)$le['subtotal']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr style="background:var(--bg)">
      <td style="text-align:right;font:400 12px var(--body);color:var(--t3)">Subtotal extras</td>
      <td class="tamt" style="font:600 13px var(--num)"><?= fmt_pub($subtotal_extras) ?></td>
    </tr>
    </tbody>
  </table>

  <!-- EXTRAS — mobile -->
  <div class="items-mob">
    <?php foreach ($lineas_extra as $le): ?>
    <div class="im">
      <div class="im-name"><?= e($le['titulo']) ?></div>
      <?php if ($le['descripcion']): ?><div class="im-desc"><?= nl2br(e($le['descripcion'])) ?></div><?php endif; ?>
      <div class="im-meta"><div class="im-meta-left"></div><div class="im-meta-total"><?= fmt_pub((float)$le['subtotal']) ?></div></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- TOTALES -->
  <div class="slbl" id="resumenlbl-screen">Resumen</div>
  <div class="tots" id="totalsScreen">
    <div class="tr"><span class="tl"><?= $subtotal_extras > 0 ? 'Subtotal artículos' : 'Subtotal' ?></span><span class="tv" id="tSub"><?= fmt_pub($subtotal) ?></span></div>
    <?php if ($desc_auto_amt > 0): ?>
    <div class="tr td" id="tAR">
      <span class="tl" id="tAL">Descuento especial<?= $adc_pct > 0 ? ' (' . number_format($adc_pct,0) . '%)' : '' ?></span>
      <span class="tv" id="tAV">-<?= fmt_pub($desc_auto_amt) ?></span>
    </div>
    <?php else: ?>
    <div class="tr td" id="tAR" style="display:none"><span class="tl" id="tAL">Descuento</span><span class="tv" id="tAV">—</span></div>
    <?php endif; ?>
    <?php if ($cupon_monto_guardado > 0): ?>
    <div class="tr td" id="tCR">
      <span class="tl" id="tCL">Cupón <?= e($cot['cupon_codigo'] ?? '') ?><?= ($cot['cupon_pct'] ?? 0) > 0 ? ' (' . (float)$cot['cupon_pct'] . '%)' : '' ?></span>
      <span class="tv" id="tCV">-<?= fmt_pub($cupon_monto_guardado) ?></span>
    </div>
    <?php else: ?>
    <div class="tr td" id="tCR" style="display:none"><span class="tl" id="tCL">Cupón</span><span class="tv" id="tCV">—</span></div>
    <?php endif; ?>
    <?php if ($subtotal_extras > 0): ?>
    <div class="tr"><span class="tl">Subtotal extras</span><span class="tv"><?= fmt_pub($subtotal_extras) ?></span></div>
    <?php endif; ?>
    <?php if ($cot['impuesto_modo'] !== 'ninguno'): ?>
    <div class="tr"><span class="tl"><?= e($cot['impuesto_label'] ?: ($cot['emp_impuesto_label'] ?? 'IVA')) ?> (<?= (float)$cot['impuesto_pct'] ?>%)</span><span class="tv"><?= fmt_pub($impuesto_amt) ?></span></div>
    <?php endif; ?>
    <div class="tr tf"><span class="tl">Total</span><span class="tv" id="tTot"><?= fmt_pub($total_base) ?></span></div>
  </div>

  <!-- CRONÓMETRO descuento automático -->
  <?php if ($adc_on && $adc_exp > time()): ?>
  <div id="adcSec">
    <div class="slbl">Oferta especial</div>
    <div class="adc">
      <div class="adc-t">
        <div>
          <div class="adc-ey">Oferta por tiempo limitado</div>
          <div class="adc-or" id="adcOr"><?= fmt_pub($subtotal) ?></div>
          <div class="adc-nw" id="adcNw"><?= fmt_pub($total_base) ?></div>
        </div>
        <div class="adc-pc" id="adcPc">-<?= number_format($adc_pct,0) ?>%</div>
      </div>
      <div class="adc-f">
        <span class="adc-el">Expira en</span>
        <div class="tmr" id="tmrWrap">
          <div class="tblk"><span class="tn" id="tD">—</span><div class="tu">días</div></div>
          <div class="ts">:</div>
          <div class="tblk"><span class="tn" id="tH">—</span><div class="tu">hrs</div></div>
          <div class="ts">:</div>
          <div class="tblk"><span class="tn" id="tM">—</span><div class="tu">min</div></div>
          <div class="ts">:</div>
          <div class="tblk"><span class="tn" id="tS">—</span><div class="tu">seg</div></div>
        </div>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div data-adc="off" style="display:none"></div>
  <?php endif; ?>

  <!-- CUPÓN -->
  <?php if (!empty($cupones) && $es_activa): ?>
  <div id="coupLbl"><div class="slbl">¿Tienes un código de descuento?</div></div>
  <div class="coup" id="coupSec">
    <div class="coup-b" id="coupFld">
      <div class="coup-l">Ingresa tu código de descuento</div>
      <div class="coup-r">
        <input class="cinp" id="cInp" type="text" placeholder="TU CÓDIGO" maxlength="30"
               oninput="this.value=this.value.toUpperCase()" onkeydown="if(event.key==='Enter')applyC()">
        <button class="cbtn" onclick="applyC()">Aplicar</button>
      </div>
      <div class="cerr" id="cErr">Cupón no válido o vencido</div>
    </div>
    <div class="capp" id="cApp">
      <div><div class="ccode" id="cCode">—</div><div class="cdesc" id="cDesc">—</div></div>
      <div style="display:flex;align-items:center;gap:10px">
        <span class="cpct" id="cPct">—</span>
        <button class="crm" onclick="rmC()">Quitar</button>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ADJUNTOS -->
  <?php if (!empty($adjuntos)): ?>
  <div class="slbl">Archivos adjuntos</div>
  <div style="display:flex;flex-direction:column;gap:8px">
    <?php foreach ($adjuntos as $adj):
        $ext = strtolower(pathinfo($adj['nombre_original'], PATHINFO_EXTENSION));
        $is_img = in_array($ext, ['jpg','jpeg','png','gif']);
        $ico_map = ['pdf'=>'📄','doc'=>'📝','docx'=>'📝','xls'=>'📊','xlsx'=>'📊'];
        $ico = $is_img ? '🖼' : ($ico_map[$ext] ?? '📎');
        $size_kb = round($adj['tamano_bytes'] / 1024);
        $size_txt = $size_kb >= 1024 ? number_format($size_kb/1024, 1).' MB' : $size_kb.' KB';
        $file_url = UPLOADS_URL . '/' . $adj['nombre_archivo'];
    ?>
    <a href="<?= e($file_url) ?>" target="_blank" rel="noopener"
       style="display:flex;align-items:center;gap:12px;padding:14px 18px;background:var(--white);border:1.5px solid var(--bd);border-radius:var(--r);text-decoration:none;transition:border-color .15s"
       onmouseover="this.style.borderColor='var(--g)'" onmouseout="this.style.borderColor='var(--bd)'">
      <span style="font-size:24px;flex-shrink:0"><?= $ico ?></span>
      <div style="flex:1;min-width:0">
        <div style="font:600 14px 'Plus Jakarta Sans',sans-serif;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($adj['nombre_original']) ?></div>
        <div style="font:400 12px 'Plus Jakarta Sans',sans-serif;color:var(--t3);margin-top:2px"><?= $size_txt ?> · <?= strtoupper($ext) ?></div>
      </div>
      <span style="font:600 12px 'Plus Jakarta Sans',sans-serif;color:var(--g);white-space:nowrap;padding:6px 12px;background:var(--glt);border-radius:var(--r)">Ver archivo</span>
    </a>
    <?php endforeach ?>
  </div>
  <?php endif ?>

  <!-- CTAs -->
  <?php if ($es_activa): ?>
  <div style="margin-top:20px" class="cta">
    <button class="bacc" onclick="openM('acceptOv')">✓ Aceptar cotización</button>
    <button class="brej" onclick="openM('rejectOv')">No es lo que busco</button>
    <button class="bprt" onclick="window.print()">Imprimir / Guardar PDF</button>
  </div>
  <?php elseif ($estado === 'aceptada'): ?>
  <div style="margin-top:20px;padding:16px 20px;background:var(--glt);border:1px solid var(--gbd);border-radius:var(--r);text-align:center;">
    <div style="font:700 16px 'Plus Jakarta Sans',sans-serif;color:var(--g);">✓ Cotización aceptada</div>
    <div style="font-size:14px;color:var(--g);opacity:.8;margin-top:4px;">Gracias por tu confirmación.</div>
  </div>
  <div style="margin-top:12px">
    <button class="bprt" onclick="window.print()">Imprimir / Guardar PDF</button>
  </div>
  <?php elseif ($estado === 'rechazada'): ?>
  <div style="margin-top:20px;padding:16px 20px;background:#fff5f5;border:1px solid #fca5a5;border-radius:var(--r);text-align:center;font-size:14px;color:#c53030;">
    Esta cotización fue rechazada.
  </div>
  <?php endif; ?>

  <!-- TÉRMINOS Y CONDICIONES -->
  <?php if ($cot['terminos']): ?>
  <div class="slbl">Términos y condiciones</div>
  <div class="terms">
    <?php if (str_contains($cot['terminos'], '<')): ?>
      <div class="term"><div class="termv"><?= nl2br(e_html($cot['terminos'])) ?></div></div>
    <?php else:
    $terminos_lines = array_filter(explode("\n", trim($cot['terminos'])));
    foreach ($terminos_lines as $linea):
        $linea = trim($linea);
        if (!$linea) continue;
        if (str_starts_with($linea, '##')) {
            echo '<div class="term"><div class="terml">' . e(ltrim($linea,'# ')) . '</div></div>';
        } else {
            echo '<div class="term"><div class="termv">' . nl2br(e($linea)) . '</div></div>';
        }
    endforeach;
    endif; ?>
  </div>
  <?php endif; ?>

  <div style="height:10px"></div>
</div><!-- /tab-d -->

</div><!-- /wrap -->
</div><!-- /body -->

<!-- PANTALLA ÉXITO -->
<div class="succ" id="succWrap">
  <div class="sico" id="sIco"><?= ico('check', 48, '#16a34a') ?></div>
  <div class="stit" id="sTit"></div>
  <div class="smsg" id="sMsg"></div>
  <div class="sbox" id="sBox"></div>
</div>

<!-- FOOTER -->
<div class="footer">
  <div class="footer-inner">
    <?php if (!empty($cot['emp_logo'])): ?>
    <div class="flogo" style="background:none;width:auto;height:auto;max-width:140px;max-height:60px"><img src="<?= e($cot['emp_logo']) ?>" alt="Logo" style="max-width:140px;max-height:60px;object-fit:contain"></div>
    <?php else: ?>
    <div class="flogo"><?= e($ini_emp) ?></div>
    <?php endif; ?>
    <div class="fname2"><?= e($cot['emp_nombre']) ?></div>
    <?php if ($cot['emp_ciudad']): ?><div class="fsub"><?= e($cot['emp_ciudad']) ?></div><?php endif; ?>
    <?php if (!empty($cot['cot_footer'])): ?>
    <div class="fdisc"><?= nl2br(str_contains($cot['cot_footer'], '<') ? e_html($cot['cot_footer']) : e($cot['cot_footer'])) ?></div>
    <?php else: ?>
    <div class="fdisc">Cotización generada en cotiza.cloud</div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL ACEPTAR -->
<div class="ov" id="acceptOv" onclick="if(event.target===this)closeM('acceptOv')">
  <div class="modal">
    <div class="mpull"></div>
    <div class="mtit">Confirmar aceptación</div>
    <div class="msub">El equipo de <?= e($cot['emp_nombre']) ?> se pondrá en contacto para coordinar el inicio.</div>
    <div class="sbox2" id="accSum"></div>
    <div class="flbl">Tu nombre completo</div>
    <input type="text" class="finp" id="accName" placeholder="Confirma tu nombre" value="<?= e($cot['cliente_nombre'] ?? '') ?>">
    <button class="mbok" onclick="doAcc()">Confirmar — Acepto esta cotización</button>
    <button class="mbno" onclick="closeM('acceptOv')">Cancelar</button>
  </div>
</div>

<!-- MODAL RECHAZAR -->
<div class="ov" id="rejectOv" onclick="if(event.target===this)closeM('rejectOv')">
  <div class="modal">
    <div class="mpull"></div>
    <div class="mtit">¿Por qué rechazas?</div>
    <div class="msub">Opcional — tu respuesta nos ayuda a mejorar.</div>
    <button class="ropt" onclick="selR(this,'precio')">El precio está fuera de mi presupuesto</button>
    <button class="ropt" onclick="selR(this,'esperar')">Voy a esperar por ahora</button>
    <button class="ropt" onclick="selR(this,'prov')">Elegí a otro proveedor</button>
    <button class="ropt" onclick="selR(this,'dis')">El diseño no es lo que busco</button>
    <button class="ropt" onclick="selR(this,'otro')">Otro motivo...</button>
    <textarea class="rtxt" id="rOther" placeholder="Cuéntanos más..." rows="3"></textarea>
    <button class="mbrej" onclick="doRej()">Enviar y rechazar</button>
    <button class="mbno" onclick="closeM('rejectOv')">Cancelar</button>
  </div>
</div>

<script>
const SUB   = <?= (float)$subtotal ?>;
const TAX   = {modo:'<?= $cot['impuesto_modo'] ?>',pct:<?= (float)$cot['impuesto_pct'] ?>};
const AUTO  = {on:<?= $adc_on?'true':'false' ?>,pct:<?= (float)$adc_pct ?>,exp:new Date(<?= $adc_exp ? ($adc_exp * 1000) : 0 ?>)};
const COUPONS = <?= json_encode(array_map(fn($c) => [
    'code' => $c['codigo'],
    'pct'  => (float)$c['pct_descuento'],
    'desc' => $c['descripcion'] ?? '',
    'exp'  => $c['fecha_vencimiento'] ?? null,
], $cupones)) ?>;
const COT_ID  = <?= (int)$cot['id'] ?>;
const EMPRESA = <?= json_encode([
    'nombre'        => $cot['emp_nombre'],
    'tel'           => $cot['emp_tel'],
    'email'         => $cot['emp_email'],
    'texto_aceptar' => $cot['texto_aceptar'] ?? '',
    'texto_rechazar'=> $cot['texto_rechazar'] ?? '',
]) ?>;

let applied = null, tmrInterval = null;

// ─── Tabs ───────────────────────────────────────────────
function go(tab, el) {
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('on'));
    el.classList.add('on');
    document.getElementById('tab-d').style.display = tab==='d' ? 'block' : 'none';
    const tt = document.getElementById('tab-t');
    if (tt) tt.style.display = tab==='t' ? 'block' : 'none';
    if(window.czTrack) window.czTrack('tab_' + tab);
}

// ─── Cronómetro ─────────────────────────────────────────
function pad(n){return String(n).padStart(2,'0')}
function initAuto(){
    if (!AUTO.on || new Date() >= AUTO.exp) {
        document.getElementById('adcSec').style.display='none';
        return;
    }
    function tick(){
        const d = Math.max(0, Math.floor((AUTO.exp - new Date()) / 1000));
        if (!d) { clearInterval(tmrInterval); document.getElementById('adcSec').style.display='none'; return; }
        document.getElementById('tD').textContent = pad(Math.floor(d/86400));
        document.getElementById('tH').textContent = pad(Math.floor((d%86400)/3600));
        document.getElementById('tM').textContent = pad(Math.floor((d%3600)/60));
        document.getElementById('tS').textContent = pad(d%60);
        document.getElementById('tmrWrap').className = 'tmr' + (d<3600?' urg':'');
    }
    tick(); tmrInterval = setInterval(tick, 1000);
}

// ─── Cupón ──────────────────────────────────────────────
function applyC(){
    const v   = document.getElementById('cInp').value.trim().toUpperCase();
    const inp = document.getElementById('cInp'), err = document.getElementById('cErr');

    // Disparar SIEMPRE al intentar validar — válido o no.
    // Buscar un descuento ya es señal fuerte de intención de compra.
    // El radar lee "coupon_validate_click" como booster de price_signal_score (+0.75)
    // y como señal débil en el bucket inminente.
    if(window.sendCouponValidateClick) window.sendCouponValidateClick();

    const c   = COUPONS.find(x => x.code===v);
    const now = new Date();
    const ok  = c && (!c.exp || now < new Date(c.exp));
    if (!ok) {
        inp.classList.add('err'); err.classList.add('on');
        setTimeout(()=>{inp.classList.remove('err');err.classList.remove('on')}, 3000);
        return;
    }
    applied = c;
    document.getElementById('cApp').classList.add('on');
    document.getElementById('coupFld').style.display = 'none';
    document.getElementById('cCode').textContent = c.code;
    document.getElementById('cDesc').textContent = c.desc;
    document.getElementById('cPct').textContent  = '-' + c.pct + '%';
    calc();
}
function rmC(){
    applied = null;
    document.getElementById('cApp').classList.remove('on');
    document.getElementById('coupFld').style.display = 'block';
    document.getElementById('cInp').value = '';
    calc();
}

// ─── Calcular totales ────────────────────────────────────
function fmt(n){return'$'+n.toLocaleString('es-MX',{minimumFractionDigits:2,maximumFractionDigits:2})}
function calc(){
    let tot = SUB, aa = 0, ca = 0;
    const adcOn = AUTO.on && document.getElementById('adcSec').style.display !== 'none';
    const ar = document.getElementById('tAR');
    if (adcOn) {
        aa = SUB * AUTO.pct / 100; tot -= aa;
        ar.style.display = '';
        document.getElementById('tAL').textContent = 'Descuento especial ('+AUTO.pct+'%)';
        document.getElementById('tAV').textContent = '-'+fmt(aa);
    } else ar.style.display = 'none';

    const cr = document.getElementById('tCR');
    if (applied) {
        ca = tot * applied.pct / 100; tot -= ca;
        cr.style.display = '';
        document.getElementById('tCL').textContent = 'Cupón '+applied.code+' ('+applied.pct+'%)';
        document.getElementById('tCV').textContent = '-'+fmt(ca);
    } else cr.style.display = 'none';

    // Sumar IVA si el modo es "suma"
    if (TAX.modo === 'suma') {
        tot += tot * TAX.pct / 100;
    }

    document.getElementById('tTot').textContent = fmt(tot);
    return {tot, aa, ca};
}

// ─── Modales ─────────────────────────────────────────────
function openM(id){
    if (id === 'acceptOv') {
        const {tot,aa,ca} = calc();
        let base = SUB - aa - ca;
        let h = '<div class="sr"><span>Subtotal</span><span>'+fmt(SUB)+'</span></div>';
        if (aa) h += '<div class="sr" style="color:var(--amb)"><span>Descuento especial</span><span>-'+fmt(aa)+'</span></div>';
        if (ca && applied) h += '<div class="sr" style="color:var(--amb)"><span>Cupón '+applied.code+'</span><span>-'+fmt(ca)+'</span></div>';
        if (TAX.modo === 'suma') {
            let taxAmt = base * TAX.pct / 100;
            h += '<div class="sr"><span><?= e($cot['impuesto_label'] ?: 'IVA') ?> ('+TAX.pct+'%)</span><span>'+fmt(taxAmt)+'</span></div>';
        }
        h += '<div class="sr tot"><span>Total</span><span>'+fmt(tot)+'</span></div>';
        // Mostrar texto_aceptar de config si existe, nada hardcodeado
        if (EMPRESA.texto_aceptar) {
            h += '<div class="acc-msg">'+EMPRESA.texto_aceptar.replace(/\n/g,'<br>')+'</div>';
        }
        document.getElementById('accSum').innerHTML = h;
    }
    document.getElementById(id).classList.add('on');
    if(window.czTrack) window.czTrack(id === 'acceptOv' ? 'accept_open' : 'reject_open');
}
function closeM(id){ document.getElementById(id).classList.remove('on'); }

// ─── Aceptar ─────────────────────────────────────────────
async function doAcc(){
    const nombre = document.getElementById('accName').value.trim();
    if (!nombre) { document.getElementById('accName').focus(); return; }
    closeM('acceptOv');
    clearInterval(tmrInterval);

    const {tot, aa, ca} = calc();
    const cupon = applied ? applied.code : null;

    let respOk = false;
    try {
        const r = await fetch('/api/quote-action', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                cotizacion_id: COT_ID, accion: 'aceptar',
                nombre, total_final: tot,
                descuento_auto_amt: aa, cupon_codigo: cupon, cupon_pct: applied?.pct ?? 0
            })
        });
        const data = await r.json();
        respOk = data.ok === true;
    } catch(e){}

    if(window.czTrack) window.czTrack('accept_confirm');

    const msgExito = EMPRESA.texto_aceptar
        ? EMPRESA.texto_aceptar.replace(/\n/g,'<br>')
        : 'Gracias, '+nombre+'. El equipo se pondrá en contacto contigo pronto.';
    mostrarExito('<?= addslashes(ico('check',48,'#16a34a')) ?>', '¡Cotización aceptada!',
        msgExito,
        'WhatsApp: '+(EMPRESA.tel||'')+(EMPRESA.email?' · '+EMPRESA.email:'')
    );

    // Marketing pixels — evento de conversión
    var totalFinal = total_base;
    var MONEDA = '<?= e($cot['moneda'] ?? 'MXN') ?>';
    <?= MarketingPixels::evento_aceptar_js(EMPRESA_ID) ?>

    // Recargar después de 3 segundos para mostrar estado actualizado
    if(respOk) setTimeout(() => location.reload(), 3000);
}

// ─── Rechazar ────────────────────────────────────────────
let razonSel = null;
function selR(btn, r){
    document.querySelectorAll('.ropt').forEach(b=>b.classList.remove('on'));
    btn.classList.add('on'); razonSel = r;
    const tx = document.getElementById('rOther');
    if (r==='otro'){tx.classList.add('on');tx.focus()}else tx.classList.remove('on');
}
async function doRej(){
    closeM('rejectOv');
    clearInterval(tmrInterval);
    const otro = document.getElementById('rOther').value.trim();
    const motivo = razonSel === 'otro' ? otro : razonSel;

    try {
        await fetch('/api/quote-action', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({cotizacion_id: COT_ID, accion: 'rechazar', motivo})
        });
    } catch(e){}

    mostrarExito('👋', 'Cotización rechazada',
        'Hemos registrado tu decisión. Si deseas retomar el proyecto, con gusto te atendemos.',
        EMPRESA.nombre+(EMPRESA.tel?' · '+EMPRESA.tel:'')
    );
    if(window.czTrack) window.czTrack('reject_confirm');
    <?= MarketingPixels::evento_rechazar_js(EMPRESA_ID) ?>
}

// ─── Éxito ───────────────────────────────────────────────
function mostrarExito(ico, tit, msg, box){
    document.getElementById('mainBody').style.display = 'none';
    document.querySelector('.footer').style.display   = 'none';
    const s = document.getElementById('succWrap');
    s.classList.add('on');
    document.getElementById('sIco').textContent = ico;
    document.getElementById('sTit').textContent = tit;
    document.getElementById('sMsg').textContent = msg;
    document.getElementById('sBox').textContent = box;
}

// ================================================================
//  TRACKER DE ENGAGEMENT — portado fielmente de sliced-quote-display_4_.php
//  Captura: quote_open, quote_close, quote_scroll (50%/90%),
//           section_view_totals, section_revisit_totals,
//           quote_price_review_loop, promo_timer_present,
//           coupon_validate_click
//  Identidad: visitor_id (localStorage+cookie 730d), session_id (sessionStorage),
//             page_id (UUID por carga)
//  Filtros server-side: bot_ip, bot_ua, visitor_interno, usuario_logueado, ip_interna
// ================================================================
const TRACK_URL = '/api/track';
(function() {
    var quoteId = typeof COT_ID !== 'undefined' ? COT_ID : 0;
    if (!quoteId) return;

    // ── Identidad persistente ─────────────────────────────────────
    // visitor_id: localStorage PRIMARY → cookie FALLBACK → nuevo UUID
    // Mismo key que en el login ('cz_visitor_id' / 'cz_vid') para que
    // al loguearse en el mismo navegador se crucen automáticamente.
    function uuidv4() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0;
            return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
        });
    }
    function getCookie(n) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + n.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    }
    function setCookie(n, v, sec) {
        document.cookie = n + '=' + encodeURIComponent(v) + '; path=/; max-age=' + sec + '; SameSite=Lax';
    }
    function lsGet(k)  { try { return localStorage.getItem(k)  || ''; } catch(e) { return ''; } }
    function lsSet(k,v){ try { localStorage.setItem(k, v);             } catch(e) {} }
    function ssGet(k)  { try { return sessionStorage.getItem(k)|| ''; } catch(e) { return ''; } }
    function ssSet(k,v){ try { sessionStorage.setItem(k, v);           } catch(e) {} }

    function getVisitorId() {
        var lk = 'cz_visitor_id', ck = 'cz_vid';
        var id = lsGet(lk) || getCookie(ck) || uuidv4();
        lsSet(lk, id);
        setCookie(ck, id, 60*60*24*730); // 2 años
        return id;
    }
    function getSessionId() {
        var k = 'cz_session_id', id = ssGet(k);
        if (!id) { id = uuidv4(); ssSet(k, id); }
        return id;
    }

    var visitorId = getVisitorId();
    var sessionId = getSessionId();
    var pageId    = uuidv4(); // Nuevo por cada carga de página

    // ── Métricas de tiempo ────────────────────────────────────────
    var openStartedAt = Date.now();
    var visibleStart  = document.visibilityState === 'visible' ? Date.now() : 0;
    var visibleAccum  = 0;
    var maxScroll     = 0;
    var closeSent     = false;

    // Acumular tiempo visible cuando el tab se oculta
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            if (!visibleStart) visibleStart = Date.now();
        } else {
            if (visibleStart) { visibleAccum += Date.now() - visibleStart; visibleStart = 0; }
        }
    });

    function currentVisibleMs() {
        return Math.max(0, Math.round(visibleAccum + (visibleStart ? Date.now() - visibleStart : 0)));
    }
    function currentOpenMs() {
        return Math.max(0, Math.round(Date.now() - openStartedAt));
    }

    // ── Scroll ────────────────────────────────────────────────────
    function getScrollPct() {
        var doc = document.documentElement, body = document.body;
        var st  = window.pageYOffset || doc.scrollTop || body.scrollTop || 0;
        var sh  = Math.max(body.scrollHeight, doc.scrollHeight,
                           body.offsetHeight, doc.offsetHeight,
                           body.clientHeight, doc.clientHeight);
        var wh  = window.innerHeight || doc.clientHeight || 0;
        var den = sh - wh;
        if (den <= 0) return 100;
        return Math.min(100, Math.max(0, Math.round((st / den) * 100)));
    }
    function updateMaxScroll() {
        var p = getScrollPct(); if (p > maxScroll) maxScroll = p; return p;
    }

    // ── Envío de eventos ─────────────────────────────────────────
    // useBeacon=true solo para quote_close — garantiza entrega al cerrar tab
    // Portado exactamente de sliced-quote-display_4_.php: sendEvent()
    function sendEvent(tipo, useBeacon) {
        var payload = JSON.stringify({
            cotizacion_id: quoteId,
            tipo:          tipo,
            visitor_id:    visitorId,
            session_id:    sessionId,
            page_id:       pageId,
            max_scroll:    maxScroll,
            open_ms:       currentOpenMs(),
            visible_ms:    currentVisibleMs(),
            path:          location.pathname + location.search
        });
        if (useBeacon && navigator.sendBeacon) {
            try {
                navigator.sendBeacon(TRACK_URL, new Blob([payload], {type:'application/json'}));
                return;
            } catch(e) {}
        }
        try {
            fetch(TRACK_URL, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: payload,
                keepalive: true
            });
        } catch(e) {}
    }

    // ── Viewport helper ──────────────────────────────────────────
    // Portado exactamente de isElementInViewport() del original.
    // threshold: totals=0.45 (45%), items=0.25 (25%) — igual que el original
    function inView(el, thr) {
        if (!el) return false;
        thr = typeof thr === 'number' ? thr : 0.5;
        var r  = el.getBoundingClientRect();
        var vh = window.innerHeight || document.documentElement.clientHeight;
        var h  = Math.max(r.height, 1);
        var px = Math.max(0, Math.min(r.bottom, vh) - Math.max(r.top, 0));
        return (px / h) >= thr;
    }

    // ── Elementos del DOM ─────────────────────────────────────────
    // IDs que deben existir en el HTML del portal:
    //   #totalsScreen  → bloque de totales (subtotal, descuento, IVA, total)
    //   #itemsBlock    → tabla de líneas de cotización
    //   #adcSec        → sección de descuento automático (timer de promo)
    var totalsEl = document.getElementById('totalsScreen');
    var itemsEl  = document.getElementById('itemsBlock');
    var promoEl  = document.getElementById('adcSec');

    // ── section_view_totals / section_revisit_totals ──────────────
    // Primera vez que totals entra al 45% del viewport: section_view_totals
    // Después de alejarse y volver (cooldown 6000ms): section_revisit_totals
    // Igual que el original: puede repetirse múltiples veces
    var totalsOnce  = false, totalsCanRev = false, lastRevAt = 0;
    var COOLDOWN_MS = 6000;

    function checkTotals() {
        if (!totalsEl) return;
        var iv = inView(totalsEl, 0.45);
        if (iv && !totalsOnce) {
            totalsOnce = true; totalsCanRev = false;
            sendEvent('section_view_totals', false);
            return;
        }
        if (!iv && totalsOnce) { totalsCanRev = true; }
        if (iv && totalsOnce && totalsCanRev) {
            var now = Date.now();
            if ((now - lastRevAt) >= COOLDOWN_MS) {
                lastRevAt = now; totalsCanRev = false;
                sendEvent('section_revisit_totals', false);
            }
        }
    }

    // ── quote_price_review_loop ───────────────────────────────────
    // Patrón: ve totales → sube a ver los items → regresa a totales.
    // Se dispara máx. 1 vez (priceLoopSent), luego resetea estado a
    // 'saw_totals' por si el usuario sigue ciclando — igual que el original.
    var loopState = 'idle', loopSent = false;

    function checkPriceLoop() {
        if (!totalsEl || !itemsEl) return;
        var tiv = inView(totalsEl, 0.45);
        var iiv = inView(itemsEl,  0.25); // 0.25 exacto del original

        if      (tiv && loopState === 'idle')               { loopState = 'saw_totals'; }
        else if (iiv && loopState === 'saw_totals')          { loopState = 'saw_items_after_totals'; }
        else if (tiv && loopState === 'saw_items_after_totals') {
            if (!loopSent) { loopSent = true; sendEvent('quote_price_review_loop', false); }
            loopState = 'saw_totals'; // resetear para detectar ciclos adicionales
        }
    }

    // ── promo_timer_present ───────────────────────────────────────
    // Portado de checkPromoTimerPresent() del original.
    // #adcSec = sección de descuento automático con countdown.
    // Se dispara si el elemento existe, está visible y no está oculto con display:none.
    // Una sola vez por página (promoSent).
    var promoSent = false;
    function checkPromo() {
        if (promoSent || !promoEl) return;
        if (promoEl.style.display !== 'none' && promoEl.offsetParent !== null) {
            promoSent = true;
            sendEvent('promo_timer_present', false);
        }
    }

    // ── quote_scroll ─────────────────────────────────────────────
    // Milestones exactos del original: 50% y 90%.
    // Cada milestone se dispara exactamente una vez por carga.
    var sentMilestones = {};
    function checkScrollMilestones() {
        [50, 90].forEach(function(m) {
            if (!sentMilestones[m] && maxScroll >= m) {
                sentMilestones[m] = true;
                sendEvent('quote_scroll', false);
            }
        });
    }

    // ── Listeners ────────────────────────────────────────────────
    // Orden idéntico al original: scroll → resize → visibilitychange
    window.addEventListener('scroll', function() {
        updateMaxScroll();
        checkScrollMilestones();
        checkTotals();
        checkPriceLoop();
        checkPromo();
    }, { passive: true });

    window.addEventListener('resize', function() {
        checkTotals();
        checkPriceLoop();
    });

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            checkTotals();
            checkPriceLoop();
            checkPromo();
        }
    });

    // ── quote_close ───────────────────────────────────────────────
    // sendBeacon = true para garantizar entrega al cerrar tab/ventana.
    // Igual que el original: beforeunload + pagehide.
    function sendClose() {
        if (closeSent) return; closeSent = true;
        updateMaxScroll();
        if (visibleStart) { visibleAccum += Date.now() - visibleStart; visibleStart = 0; }
        sendEvent('quote_close', true);
    }
    window.addEventListener('beforeunload', sendClose);
    window.addEventListener('pagehide',     sendClose);

    // ── API pública ───────────────────────────────────────────────
    // coupon_validate_click: llamado desde applyC() — el intento ya es señal
    window.sendCouponValidateClick = function() { sendEvent('coupon_validate_click', false); };
    // czTrack: para eventos adicionales (accept_open, reject_open, tab_d, tab_t, etc.)
    window.czTrack = function(tipo) { sendEvent(tipo, false); };

    // ── Inicialización ────────────────────────────────────────────
    // Orden exacto del original: updateMaxScroll → quote_open → checkPromo → checkTotals → checkPriceLoop
    updateMaxScroll();
    sendEvent('quote_open', false);
    checkPromo();
    checkTotals();
    checkPriceLoop();

})();

// ─── Init ────────────────────────────────────────────────
initAuto();
calc();
</script>

<!-- PRINT: Pie de página -->
<div class="print-footer" style="display:none">
  <?php if (!empty($cot['cot_footer'])): ?>
  <div style="margin-bottom:5pt"><?= nl2br(str_contains($cot['cot_footer'], '<') ? e_html($cot['cot_footer']) : e($cot['cot_footer'])) ?></div>
  <?php endif; ?>
  <?= e($cot['emp_nombre']) ?>
  <?php if (!empty($cot['emp_tel'])): ?> · <?= e($cot['emp_tel']) ?><?php endif; ?>
  <?php if (!empty($cot['emp_email'])): ?> · <?= e($cot['emp_email']) ?><?php endif; ?>
  <?php if (!empty($cot['emp_web'])): ?> · <?= e(preg_replace('#^https?://#','',$cot['emp_web'])) ?><?php endif; ?><br>
  Cotización <?= e($cot['numero']) ?> generada en cotiza.cloud
</div>
<?= MarketingPixels::evento_view(EMPRESA_ID, $cot['numero'], (float)$total_base, $cot['moneda'] ?? 'MXN') ?>
</body>
</html>
