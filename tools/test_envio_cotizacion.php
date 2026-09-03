<?php
// ============================================================
// PRUEBA — LOS BOTONES DE ENVÍO AL CLIENTE EXISTEN Y ESTÁN CABLEADOS.
//
// Por qué existe: la hoja de envío del editor (openUrlOverlay) llevaba meses
// escrita —con sus accesos a WhatsApp y correo dentro— pero NINGÚN botón la
// abría. Código vivo, inalcanzable, y nadie se dio cuenta. El asesor solo tenía
// "Copiar link". Esta prueba impide que vuelva a pasar.
//
// Lo que garantiza:
//   1. Los dos lugares donde se comparte una cotización (el popup de recién
//      generada y la hoja del editor) tienen WhatsApp y correo.
//   2. La hoja del editor tiene quién la abra.
//   3. El número se normaliza en UN solo lugar (PHP), no con una copia en JS.
//   4. El correo del cliente se captura en los DOS formularios de alta.
//
// Correr: php tools/test_envio_cotizacion.php   → debe terminar en OK
// No necesita base de datos.
// ============================================================

$ok = 0; $fail = 0;
function chk(string $t, $got, $want = true): void {
    global $ok, $fail;
    if ($got === $want) { $ok++; echo "  ✓ $t\n"; }
    else { $fail++; echo "  ✗ $t  got=" . json_encode($got) . " want=" . json_encode($want) . "\n"; }
}
function src(string $rel): string {
    return (string)file_get_contents(__DIR__ . '/../' . $rel);
}

$nueva  = src('modules/cotizaciones/nueva.php');
$ver    = src('modules/cotizaciones/ver.php');
$helper = src('core/Helpers.php');
$crear  = src('modules/clientes/crear.php');
$lista  = src('modules/clientes/lista.php');
$cliver = src('modules/clientes/ver.php');

echo "\n1) LA HOJA DEL EDITOR TIENE QUIÉN LA ABRA\n";
// El bug original: la función existía y nadie la llamaba.
chk('openUrlOverlay() está definida',   str_contains($ver, 'function openUrlOverlay('));
chk('y ALGO la llama',                  (bool)preg_match('/onclick="openUrlOverlay\(\)"/', $ver));

echo "\n2) LOS DOS LUGARES OFRECEN WHATSAPP Y CORREO\n";
chk('editor: enlace de WhatsApp',       str_contains($ver, 'https://wa.me/'));
chk('editor: enlace de correo',         str_contains($ver, 'mailto:'));
chk('popup: botón de WhatsApp',         str_contains($nueva, "id=\"popup-wa\""));
chk('popup: botón de correo',           str_contains($nueva, "id=\"popup-mail\""));
chk('popup: les pone href al abrirse',
    str_contains($nueva, "btnWa.href") && str_contains($nueva, "getElementById('popup-mail').href"));

echo "\n3) EL NÚMERO SE NORMALIZA EN UN SOLO LUGAR\n";
// Si alguien reescribe la normalización en JavaScript, las dos versiones se
// separan el día que cambie una regla (y el mensaje se va a otro número).
chk('existe tel_whatsapp() en PHP',     str_contains($helper, 'function tel_whatsapp('));
chk('el editor la usa',                 str_contains($ver, 'tel_whatsapp('));
chk('el popup recibe el número ya hecho', str_contains($nueva, "'wa'       => tel_whatsapp("));
chk('el JS solo lo lee, no lo calcula', str_contains($nueva, 'cli.wa'));
// Señal de que alguien volvió a normalizar a mano en JS.
chk('sin regex de dígitos en el JS del popup',
    (bool)preg_match("/replace\(\/\\\\D/", $nueva), false);

echo "\n4) EL TEXTO DEL MENSAJE ES UNO SOLO\n";
chk('existe wa_texto_cotizacion()',     str_contains($helper, 'function wa_texto_cotizacion('));
chk('el editor lo usa',                 str_contains($ver, 'wa_texto_cotizacion('));
chk('el popup recibe la plantilla de PHP', str_contains($nueva, 'const WA_TPL'));
chk('y la variante sin nombre',         str_contains($nueva, 'WA_TPL_SIN'));

echo "\n5) NO REVIENTA SIN CLIENTE\n";
// Se puede generar una cotización sin cliente asignado; el popup igual se abre.
chk('el popup tolera clienteSeleccionado null',
    str_contains($nueva, "typeof clienteSeleccionado !== 'undefined' && clienteSeleccionado"));
chk('y el editor tolera cliente sin correo',
    str_contains($ver, "\$cli_mail = trim((string)(\$cot['cliente_email'] ?? ''))"));

echo "\n6) EL CORREO SE CAPTURA EN LOS DOS FORMULARIOS DE ALTA\n";
// Antes solo se guardaba al EDITAR: un cliente recién creado no tenía a dónde
// recibir su cotización, y el botón de correo nacía inútil.
chk('alta rápida (cotización): campo',  str_contains($nueva, 'id="nc-email"'));
chk('alta rápida: lo manda',            (bool)preg_match('/JSON\.stringify\(\{ nombre, telefono, email, direccion \}\)/', $nueva));
chk('módulo Clientes: campo',           str_contains($lista, 'id="cli-email"'));
chk('módulo Clientes: lo manda',        str_contains($lista, 'telefono_empresa, email, direccion, nota'));
chk('el endpoint lo guarda al CREAR',   str_contains($crear, 'telefono_empresa, email, direccion, nota'));
chk('edición de cliente: teléfono de oficina', str_contains($cliver, 'id="edit-telefono-empresa"'));

echo "\n7) LA BASE SIN MIGRAR NO TUMBA EL ALTA\n";
// Las columnas nuevas son opcionales: si la migración no se corrió, el cliente
// se guarda igual en vez de dar error 500.
chk('crear.php tiene respaldo sin telefono_empresa',
    substr_count($crear, 'INSERT INTO clientes') === 2);
chk('lada_pais() tolera que falte la columna',
    (bool)preg_match('/function lada_pais[\s\S]{0,400}catch \(\\\\Throwable/', $helper));

// ════════════════════════════════════════════════════════════
//  ENVÍO REAL DE CORREO (el sistema manda, no la app del asesor)
// ════════════════════════════════════════════════════════════
$mailer = src('core/Mailer.php');
$endp   = src('modules/cotizaciones/enviar_correo.php');
$router = src('core/Router.php');
$cfg    = src('modules/config/index.php');
$gemp   = src('modules/config/guardar_empresa.php');

echo "\n8) NACE APAGADO Y SE PRENDE POR EMPRESA\n";
// Prenderlo para todos de golpe pondría a nueve empresas a mandar correo sin
// haberlo pedido, con los rebotes cayendo sobre la reputación del dominio que
// también manda las recuperaciones de contraseña.
chk('el default es false',              (bool)preg_match("/'envio_correo_cliente' => false/", $helper));
chk('el endpoint lo exige',             str_contains($endp, "empty(\$ncfg['envio_correo_cliente'])"));
chk('hay interruptor en Configuración', str_contains($cfg, 'id="e_envio_correo"'));
chk('y se guarda',                      str_contains($cfg, 'envio_correo_cliente:'));
chk('el editor respeta el interruptor', str_contains($ver, '$envio_correo_on'));

echo "\n9) EL ENDPOINT ESTÁ PROTEGIDO\n";
chk('ruta registrada',                  str_contains($router, "/cotizaciones/:id/enviar-correo"));
chk('exige CSRF',                       str_contains($endp, 'csrf_check()'));
chk('valida que la cotización sea de la empresa', str_contains($endp, 'AND c.empresa_id = ?'));
// Un doble clic mandándole cinco veces el mismo correo al cliente es la vía
// rápida a que lo marque como spam.
chk('limita los envíos',                str_contains($endp, "rate_check('cot_correo_"));
chk('y registra el intento',            str_contains($endp, "rate_hit('cot_correo_"));
chk('no manda en cualquier estado',     str_contains($endp, "['borrador','enviada','vista']"));

echo "\n10) A NOMBRE DE LA EMPRESA, RESPONDIENDO AL ASESOR\n";
// El cliente de "Comercializadora Reyes" no tiene por qué ver nuestra marca
// donde va la de su proveedor.
chk('template propio para el cliente',  str_contains($mailer, 'function wrap_template_empresa('));
chk('usa el nombre de la empresa',      str_contains($mailer, '$emp   = htmlspecialchars($empresa_nombre'));
// Una respuesta del cliente es señal de compra: si cae en un buzón general no
// tiene dueño y se muere.
chk('pone Responder-A',                 str_contains($mailer, '$mail->addReplyTo('));
chk('es el asesor de la cotización',    str_contains($endp, "\$cot['vendedor_id'] ?: \$cot['usuario_id']"));
// Sin versión de texto, un correo que es casi solo un enlace huele a spam.
chk('manda alternativa de texto',       str_contains($mailer, '$mail->AltBody'));
// Aislar la reputación evita que un rebote de cotización mande a spam la
// recuperación de contraseña de otro cliente.
chk('admite subdominio de envíos',      str_contains($mailer, 'SMTP_FROM_ENVIOS'));
// Dominio propio = se respeta la marca del cliente, igual que en los slugs.
chk('el pie de marca es condicional',   str_contains($mailer, '$mostrar_marca'));
chk('y lo apaga el dominio propio',     str_contains($endp, "empty(\$empresa['dominio_custom'])"));

echo "\n11) EL CORREO NO SPOILEA EL TOTAL\n";
// El correo existe para que el cliente ABRA la cotización: ahí es donde el Radar
// mide interés y donde puede aceptar. Con el monto en el correo, muchos no
// entran y perdemos la señal.
// Acotado al método: otros correos (abonos) sí llevan totales, y con razón.
$m_cot = '';
if (preg_match('/public static function enviar_cotizacion\(array \$a\): bool[\s\S]*?\n    \}\n/', $mailer, $mm)) {
    $m_cot = $mm[0];
}
chk('se pudo aislar el método',         $m_cot !== '');
chk('no arma el total en el mensaje',   str_contains($m_cot, '$total'), false);
chk('ni el subtotal',                   str_contains($m_cot, 'subtotal'), false);
chk('sí lleva botón al enlace',         str_contains($m_cot, 'Ver mi cotización'));

echo "\n12) SI NO HAY CORREO, SE PIDE Y SE GUARDA\n";
// Bloquear el envío por un dato opcional dejaría el botón inútil justo cuando
// más se necesita.
chk('el editor tiene el campo',         str_contains($ver, 'id="correo-input"'));
chk('el endpoint lo acepta',            str_contains($endp, "\$email_nuevo = trim((string)(\$body['email'] ?? ''))"));
chk('lo valida',                        str_contains($endp, 'FILTER_VALIDATE_EMAIL'));
chk('y lo guarda en el cliente',        str_contains($endp, 'UPDATE clientes SET email=?'));

echo "\n13) MANDARLO POR CORREO CUENTA COMO ENVIARLO\n";
// Si no se sella, el Radar y el termómetro empiezan a contar desde otra fecha.
chk('sella enviada_at',                 str_contains($endp, 'enviada_at = COALESCE(enviada_at, NOW())'));
// Y si el registro falla, el correo YA salió: no se puede reportar como error.
chk('el log no tumba el envío',
    (bool)preg_match('/INSERT INTO cotizacion_log[\s\S]{0,300}catch \(\\\\Throwable/', $endp));

echo "\n14) UNA BASE SIN MIGRAR NO ROMPE LA CONFIGURACIÓN\n";
// lada_pais va en un UPDATE aparte: metida en el grande, una base sin migrar
// dejaría de guardar TODA la configuración de la empresa.
chk('lada_pais se guarda aparte',       str_contains($gemp, 'UPDATE empresas SET lada_pais=?'));
chk('y tolerando el fallo',             (bool)preg_match('/lada_pais=\?[\s\S]{0,200}catch \(\\\\Throwable/', $gemp));

echo "\n15) WHATSAPP TAMBIÉN SE PUEDE APAGAR\n";
// Nace PRENDIDO, al revés que el correo: no manda nada por su cuenta —abre la
// app del asesor— y es por donde se manda el 90% en México.
chk('el default es true',               (bool)preg_match("/'envio_whatsapp_cliente' => true/", $helper));
chk('hay interruptor en Configuración', str_contains($cfg, 'id="e_envio_whatsapp"'));
chk('y se guarda',                      str_contains($cfg, 'envio_whatsapp_cliente:'));
chk('el editor lo respeta',             str_contains($ver, '$envio_wa_on'));
chk('el popup también',                 str_contains($nueva, '$envio_wa_on'));
// Apagado, el botón no existe en el DOM: sin guarda, el JS del popup reventaría
// y el asesor se quedaría sin ver la liga de lo que acaba de generar.
chk('el JS tolera que no exista',       str_contains($nueva, "const btnWa = document.getElementById('popup-wa')"));
chk('y no lo toca si falta',            str_contains($nueva, 'if (btnWa) {'));
// Con uno solo, la rejilla de dos columnas dejaría un hueco.
chk('la rejilla se ajusta en el editor', str_contains($ver, "\$envio_wa_on ? '1fr 1fr' : '1fr'"));
chk('y en el popup',                     str_contains($nueva, "\$envio_wa_on ? '1fr 1fr' : '1fr'"));

echo "\n16) A QUIÉN LE RESPONDE EL CLIENTE ES ELECCIÓN DE LA EMPRESA\n";
chk('el default es el asesor',          (bool)preg_match("/'correo_responde_a'    => 'asesor'/", $helper));
chk('hay selector en Configuración',    str_contains($cfg, 'id="e_responde_a"'));
chk('con las dos opciones',             str_contains($cfg, 'value="asesor"') && str_contains($cfg, 'value="empresa"'));
chk('y se guarda',                      str_contains($cfg, 'correo_responde_a:'));
chk('el endpoint lo lee',               str_contains($endp, "\$ncfg['correo_responde_a'] ?? 'asesor'"));
// Un correo sin Responder-A deja al cliente sin forma de contestar, así que la
// opción elegida cae a la otra cuando no tiene correo.
chk('empresa cae al asesor si no tiene', str_contains($endp, '$reply_to     = $mail_empresa ?: $mail_asesor;'));
chk('asesor cae a la empresa si no tiene', str_contains($endp, '$reply_to     = $mail_asesor ?: $mail_empresa;'));

echo "\n" . ($fail === 0
    ? "✓ ENVÍO AL CLIENTE OK — $ok comprobaciones\n"
    : "✗ FALLARON $fail de " . ($ok + $fail) . "\n");
exit($fail === 0 ? 0 : 1);
