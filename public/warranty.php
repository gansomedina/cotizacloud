<?php
// ============================================================
//  CotizaApp — public/warranty.php
//  GET /w/:codigo   (sin login — landing de compensación)
// ============================================================

defined('COTIZAAPP') or die;

$codigo = $codigo ?? '';
if (!$codigo) { http_response_code(404); die('No encontrado'); }

// Buscar cupón de compensación
$cupon = DB::row(
    "SELECT c.*, e.nombre AS emp_nombre, e.logo_url AS emp_logo,
            e.telefono AS emp_tel, e.email AS emp_email, e.slug AS emp_slug
     FROM cupones c
     JOIN empresas e ON e.id = c.empresa_id
     WHERE c.codigo = ? AND c.empresa_id = ? AND c.activo = 1",
    [$codigo, EMPRESA_ID]
);

if (!$cupon) { http_response_code(404); die('Cupón no encontrado'); }

$monto = (float)($cupon['monto_fijo'] ?? 0);
$pct   = (float)($cupon['porcentaje'] ?? 0);
$descuento_txt = $monto > 0 ? '$' . number_format($monto, 0) : number_format($pct, 0) . '%';
$empresa = $cupon['emp_nombre'];
$logo    = $cupon['emp_logo'] ?? '';
$ini     = strtoupper(substr($empresa, 0, 2));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Compensación — <?= e($empresa) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f8f8f6;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}

.card{max-width:480px;width:100%;background:#fff;border-radius:24px;box-shadow:0 8px 40px rgba(0,0,0,.08);overflow:hidden;text-align:center}

.card-header{background:linear-gradient(135deg,#1a5c38 0%,#16a34a 100%);padding:40px 30px 50px;position:relative}
.card-header::after{content:'';position:absolute;bottom:-20px;left:50%;transform:translateX(-50%);width:60px;height:60px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center}

.logo{width:160px;height:100px;margin:0 auto 16px;display:flex;align-items:center;justify-content:center}
.logo img{max-width:160px;max-height:80px;object-fit:contain}
.logo-text{width:60px;height:60px;border-radius:14px;background:rgba(255,255,255,.2);color:#fff;font:700 20px 'Plus Jakarta Sans',sans-serif;display:flex;align-items:center;justify-content:center}
.emp-name{color:#fff;font:700 18px 'Plus Jakarta Sans',sans-serif;opacity:.9}

.gift{width:70px;height:70px;background:#fff;border-radius:50%;box-shadow:0 4px 20px rgba(0,0,0,.1);display:flex;align-items:center;justify-content:center;margin:-35px auto 0;position:relative;z-index:2;font-size:36px}

.card-body{padding:30px 30px 36px}

.title{font:800 22px 'Plus Jakarta Sans',sans-serif;color:#1a1a18;margin:20px 0 8px;line-height:1.3}
.subtitle{font-size:15px;color:#6a6a64;line-height:1.6;margin-bottom:28px}

.amount-card{background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border:2px solid #bbf7d0;border-radius:16px;padding:24px;margin-bottom:24px}
.amount-label{font:600 11px 'Plus Jakarta Sans',sans-serif;text-transform:uppercase;letter-spacing:.1em;color:#16a34a;margin-bottom:8px}
.amount-value{font:900 42px 'Plus Jakarta Sans',sans-serif;color:#1a5c38;letter-spacing:-.03em}
.amount-sub{font-size:13px;color:#4a4a46;margin-top:8px}

.code-box{background:#f4f4f0;border:2px dashed #c8c8c0;border-radius:12px;padding:16px;margin-bottom:24px}
.code-label{font:600 11px 'Plus Jakarta Sans',sans-serif;text-transform:uppercase;letter-spacing:.1em;color:#6a6a64;margin-bottom:6px}
.code-value{font:800 24px 'Plus Jakarta Sans',sans-serif;color:#1a5c38;letter-spacing:.08em}

.note{font-size:12px;color:#94a3b8;line-height:1.5}
.note strong{color:#6a6a64}

.footer{padding:16px 30px;border-top:1px solid #e2e2dc;font-size:12px;color:#94a3b8}
.footer a{color:#1a5c38;text-decoration:none}
</style>
</head>
<body>

<div class="card">
    <div class="card-header">
        <div class="logo">
            <?php if ($logo): ?>
            <img src="<?= e($logo) ?>" alt="Logo">
            <?php else: ?>
            <div class="logo-text"><?= e($ini) ?></div>
            <?php endif; ?>
        </div>
        <div class="emp-name"><?= e($empresa) ?></div>
    </div>

    <div class="gift">🎁</div>

    <div class="card-body">
        <div class="title">La calidad es nuestra prioridad</div>
        <div class="subtitle">
            Lamentamos el inconveniente. Como muestra de nuestro compromiso con usted,
            le ofrecemos un descuento directo en su próxima cotización.
        </div>

        <div class="amount-card">
            <div class="amount-label">Descuento especial para usted</div>
            <div class="amount-value"><?= $descuento_txt ?></div>
            <div class="amount-sub">de descuento en su próxima cotización</div>
        </div>

        <div class="code-box">
            <div class="code-label">Su código de descuento</div>
            <div class="code-value"><?= e($cupon['codigo']) ?></div>
        </div>

        <div class="note">
            <strong>¿Cómo usarlo?</strong> Comparta este código con su asesor al solicitar
            su próxima cotización. El descuento se aplicará automáticamente.<br><br>
            Este cupón es válido por un solo uso.
            <?php if ($cupon['vencimiento_fecha']): ?>
            Válido hasta el <?= date('d/m/Y', strtotime($cupon['vencimiento_fecha'])) ?>.
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <?= e($empresa) ?>
        <?php if ($cupon['emp_tel']): ?> · <a href="tel:<?= e($cupon['emp_tel']) ?>"><?= e($cupon['emp_tel']) ?></a><?php endif; ?>
        <?php if ($cupon['emp_email']): ?> · <a href="mailto:<?= e($cupon['emp_email']) ?>"><?= e($cupon['emp_email']) ?></a><?php endif; ?>
    </div>
</div>

</body>
</html>
