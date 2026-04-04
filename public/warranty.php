<?php
// ============================================================
//  CotizaApp — public/warranty.php
//  GET /w/:codigo   (sin login — landing de compensación)
// ============================================================

defined('COTIZAAPP') or die;

$codigo = $codigo ?? '';
if (!$codigo) { http_response_code(404); die('No encontrado'); }

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
body{font-family:'Plus Jakarta Sans',sans-serif;background:linear-gradient(160deg,#f0fdf4 0%,#f8f8f6 40%,#fefce8 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}

.card{max-width:460px;width:100%;background:#fff;border-radius:28px;box-shadow:0 4px 60px rgba(0,0,0,.06),0 1px 3px rgba(0,0,0,.04);overflow:hidden;text-align:center}

/* Header suave */
.card-header{padding:36px 30px 24px;background:#fff;border-bottom:1px solid #f0f0ec}
.logo{margin:0 auto 12px}
.logo img{max-width:180px;max-height:80px;object-fit:contain}
.logo-text{width:56px;height:56px;border-radius:14px;background:#1a5c38;color:#fff;font:700 18px 'Plus Jakarta Sans',sans-serif;display:inline-flex;align-items:center;justify-content:center}
.emp-name{color:#4a4a46;font:600 14px 'Plus Jakarta Sans',sans-serif;letter-spacing:.02em}

/* Regalo */
.gift-area{padding:32px 30px 24px;background:linear-gradient(180deg,#fafaf8 0%,#fff 100%)}
.gift-icon{font-size:64px;margin-bottom:16px;display:block;animation:float 3s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}

.title{font:800 24px/1.2 'Plus Jakarta Sans',sans-serif;color:#1a1a18;letter-spacing:-.02em}
.title span{color:#16a34a}
.subtitle{font:400 14px/1.7 'Plus Jakarta Sans',sans-serif;color:#6a6a64;margin-top:10px;max-width:340px;margin-left:auto;margin-right:auto}

/* Monto */
.amount{margin:28px 30px 0;padding:28px 24px;background:linear-gradient(135deg,#f0fdf4 0%,#ecfdf5 50%,#d1fae5 100%);border-radius:20px;position:relative;overflow:hidden}
.amount::before{content:'';position:absolute;top:-30px;right:-30px;width:100px;height:100px;background:rgba(22,163,74,.08);border-radius:50%}
.amount::after{content:'';position:absolute;bottom:-20px;left:-20px;width:70px;height:70px;background:rgba(22,163,74,.05);border-radius:50%}
.amount-label{font:600 11px 'Plus Jakarta Sans',sans-serif;text-transform:uppercase;letter-spacing:.12em;color:#16a34a;margin-bottom:8px;position:relative;z-index:1}
.amount-value{font:900 48px 'Plus Jakarta Sans',sans-serif;color:#1a5c38;letter-spacing:-.03em;position:relative;z-index:1}
.amount-sub{font:400 13px 'Plus Jakarta Sans',sans-serif;color:#4a4a46;margin-top:6px;position:relative;z-index:1}

/* Código */
.code{margin:20px 30px 0;padding:18px 20px;background:#fafaf8;border:2px dashed #d4d4cd;border-radius:14px}
.code-label{font:500 10px 'Plus Jakarta Sans',sans-serif;text-transform:uppercase;letter-spacing:.12em;color:#94a3b8;margin-bottom:6px}
.code-value{font:800 28px 'Plus Jakarta Sans',sans-serif;color:#1a5c38;letter-spacing:.12em}

/* Instrucciones */
.instructions{margin:24px 30px 0;text-align:left}
.inst-title{font:700 13px 'Plus Jakarta Sans',sans-serif;color:#1a1a18;margin-bottom:10px}
.inst-step{display:flex;align-items:flex-start;gap:10px;margin-bottom:10px}
.inst-num{width:24px;height:24px;border-radius:50%;background:#f0fdf4;color:#16a34a;font:700 12px 'Plus Jakarta Sans',sans-serif;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.inst-text{font:400 13px/1.5 'Plus Jakarta Sans',sans-serif;color:#6a6a64}

/* Vigencia */
.validity{margin:20px 30px 0;padding:10px 16px;background:#fffbeb;border-radius:10px;font:500 12px 'Plus Jakarta Sans',sans-serif;color:#92400e}

/* Footer */
.card-footer{padding:20px 30px;margin-top:24px;border-top:1px solid #f0f0ec;font:400 12px 'Plus Jakarta Sans',sans-serif;color:#94a3b8}
.card-footer a{color:#1a5c38;text-decoration:none}
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

    <div class="gift-area">
        <span class="gift-icon">🎁</span>
        <div class="title">La calidad es <span>nuestra prioridad</span></div>
        <div class="subtitle">Lamentamos cualquier inconveniente. Como muestra de nuestro compromiso, le ofrecemos un descuento especial.</div>
    </div>

    <div class="amount">
        <div class="amount-label">Descuento exclusivo para usted</div>
        <div class="amount-value"><?= $descuento_txt ?></div>
        <div class="amount-sub">de descuento directo en su próxima cotización</div>
    </div>

    <div class="code">
        <div class="code-label">Su código personal</div>
        <div class="code-value"><?= e($cupon['codigo']) ?></div>
    </div>

    <div class="instructions">
        <div class="inst-title">¿Cómo utilizarlo?</div>
        <div class="inst-step">
            <div class="inst-num">1</div>
            <div class="inst-text">Solicite su próxima cotización con su asesor</div>
        </div>
        <div class="inst-step">
            <div class="inst-num">2</div>
            <div class="inst-text">Comparta este código y el descuento se aplicará automáticamente</div>
        </div>
        <div class="inst-step">
            <div class="inst-num">3</div>
            <div class="inst-text">Disfrute de su proyecto con el mejor servicio</div>
        </div>
        <div class="inst-step">
            <div class="inst-num">4</div>
            <div class="inst-text">Puede usarlo usted o regalárselo a alguien especial</div>
        </div>
        <div style="margin-top:18px;padding:14px 18px;background:linear-gradient(135deg,#fefce8,#fef9c3);border-radius:12px;text-align:center">
            <div style="font:800 15px 'Plus Jakarta Sans',sans-serif;color:#92400e">¡Aproveche esta oportunidad!</div>
            <div style="font:400 12px 'Plus Jakarta Sans',sans-serif;color:#a16207;margin-top:4px">Descuento exclusivo por tiempo limitado</div>
        </div>
    </div>

    <?php if ($cupon['vencimiento_fecha']): ?>
    <div class="validity">
        Válido hasta el <?= date('d/m/Y', strtotime($cupon['vencimiento_fecha'])) ?> · Un solo uso
    </div>
    <?php endif; ?>

    <div class="card-footer">
        <?= e($empresa) ?>
        <?php if ($cupon['emp_tel']): ?> · <a href="tel:<?= e($cupon['emp_tel']) ?>"><?= e($cupon['emp_tel']) ?></a><?php endif; ?>
        <?php if ($cupon['emp_email']): ?> · <a href="mailto:<?= e($cupon['emp_email']) ?>"><?= e($cupon['emp_email']) ?></a><?php endif; ?>
    </div>
</div>

</body>
</html>
