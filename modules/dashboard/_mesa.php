<?php
// ============================================================
//  Mesa de Trabajo — v1.2 BETA (solo admin/superadmin)
//  Una tabla limpia: solo faltas y contradicciones del asesor.
//  Motivo = etiqueta corta (por qué está en la mesa). Sin párrafos.
// ============================================================
defined('COTIZAAPP') or die;
if (empty($es_admin_dash)) return;

$mesa_vendedores = DB::query(
    "SELECT DISTINCT u.id, u.nombre
     FROM usuarios u
     JOIN cotizaciones c ON COALESCE(c.vendedor_id, c.usuario_id) = u.id
     WHERE u.empresa_id = ? AND u.activo = 1
       AND c.empresa_id = ? AND c.estado IN ('enviada','vista') AND c.suspendida = 0
     ORDER BY u.nombre",
    [$empresa_id, $empresa_id]
);
if (!$mesa_vendedores) return;

$mesa_uid = (int)($_GET['mesa_uid'] ?? 0);
$mesa_ids = array_map(fn($v) => (int)$v['id'], $mesa_vendedores);
if (!in_array($mesa_uid, $mesa_ids, true)) $mesa_uid = $mesa_ids[0];

$mesa = Mesa::armar($empresa_id, $mesa_uid);
$mr   = $mesa['resumen'];
$mmoney = fn(float $n) => '$' . number_format($n, 0);

// Categoría → [etiqueta corta, color]
$MESA_MOTIVO = [
    'revivida'         => ['Revivió tras tu descarte', '#d97706'],
    'milagro'          => ['La ve ahora, fuera de ciclo', '#d97706'],
    'interes_muriendo' => ['Dijiste "va en serio" y se apaga', '#dc2626'],
    'sin_postura'      => ['Se movió y falta tu juicio', '#dc2626'],
    'ultimo_tramo'     => ['Último tramo de tu ventana', '#64748b'],
];
?>
<details class="card" id="mesa-card" style="margin-bottom:16px" <?= isset($_GET['mesa_uid']) ? 'open' : '' ?>>
  <summary style="cursor:pointer;padding:14px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;list-style:none">
    <span style="font-weight:800">📋 Mesa de trabajo</span>
    <span style="font-size:11px;background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:10px;font-weight:700">BETA · solo admin</span>
    <span style="color:#4a4a46;font-size:13.5px">
      <?php if ($mr['n'] > 0): ?><b><?= (int)$mr['n'] ?></b> pendientes · <b><?= $mmoney($mr['monto']) ?></b>
      <?php else: ?><span style="color:#16a34a;font-weight:700">✓ al corriente</span><?php endif; ?>
    </span>
    <span style="margin-left:auto;color:#6a6a64;font-size:12px">▾</span>
  </summary>
  <div style="padding:0 16px 16px">

    <?php if (count($mesa_vendedores) > 1): ?>
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
      <?php foreach ($mesa_vendedores as $mv): $act = (int)$mv['id'] === $mesa_uid; ?>
        <a href="?mesa_uid=<?= (int)$mv['id'] ?>#mesa-card"
           style="padding:5px 12px;border-radius:16px;font-size:12.5px;font-weight:600;text-decoration:none;
                  <?= $act ? 'background:#1a5c38;color:#fff' : 'background:#f4f4f0;color:#4a4a46;border:1px solid #e2e2dc' ?>">
          <?= e($mv['nombre']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!$mesa['rows']): ?>
      <div style="color:#16a34a;padding:10px 0;font-weight:600">✓ Todo lo activo está juzgado y en ventana.</div>
    <?php else: ?>
    <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse;font-size:13.5px">
      <tbody>
      <?php foreach ($mesa['rows'] as $r):
          $m = $MESA_MOTIVO[$r['cat']] ?? ['', '#64748b'];
      ?>
      <tr style="border-top:1px solid #eeeee9">
        <td style="padding:9px 10px 9px 0">
          <a href="/cotizaciones/<?= (int)$r['id'] ?>" style="font-weight:700;color:#1a1a18;text-decoration:none"><?= e($r['cliente']) ?></a>
        </td>
        <td style="padding:9px 10px;font-weight:800;white-space:nowrap;text-align:right"><?= $mmoney($r['total']) ?></td>
        <td style="padding:9px 10px;color:#8a8a84;white-space:nowrap;font-size:12px">día <?= (int)$r['edad'] ?></td>
        <td style="padding:9px 0 9px 10px">
          <span style="font-size:12px;font-weight:600;color:<?= $m[1] ?>"><?= e($m[0]) ?></span>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    <?php endif; ?>

    <?php if ($mesa['limpieza']['n'] >= 10): ?>
    <div style="margin-top:10px;font-size:12px;color:#9a3412">
      🗑 <b><?= (int)$mesa['limpieza']['n'] ?></b> cotizaciones (<?= $mmoney($mesa['limpieza']['monto']) ?>) rebasan los
      <?= (int)$mesa['limpieza']['linea_dias'] ?> días — tu empresa nunca ha cerrado una tan vieja. <em>(Limpieza: próxima versión.)</em>
    </div>
    <?php endif; ?>

  </div>
</details>
