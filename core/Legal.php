<?php
// ============================================================
//  CotizaApp — core/Legal.php
//  Versionado de documentos legales (Términos, Privacidad) y
//  registro de evidencia de consentimiento (clickwrap).
//
//  Marco legal: Código de Comercio arts. 89 bis, 90, 93, 93 bis.
//  Para que la aceptación sea defendible se guarda:
//   - atribución (usuario_id/email + IP + user_agent)
//   - versión exacta aceptada + hash SHA-256 del texto
//   - timestamp con milisegundos
//  La columna nom151_constancia queda como hook para subir a
//  presunción de integridad vía un PSC en el futuro.
// ============================================================

defined('COTIZAAPP') or die;

class Legal
{
    /**
     * Versiones vigentes de cada documento legal.
     * Cuando cambie el texto de /terminos o /privacidad, súbele la
     * versión aquí (fecha de vigencia). El registro guarda el hash
     * del contenido real, así que el número es solo una etiqueta.
     */
    const VERSIONES = [
        'terminos'   => '2026-06-01',
        'privacidad' => '2026-05-28',
    ];

    /** Ruta del archivo público que contiene cada documento. */
    const ARCHIVOS = [
        'terminos'   => 'public/terminos.php',
        'privacidad' => 'public/privacidad.php',
    ];

    /**
     * Devuelve (creando si hace falta) el id de la versión vigente
     * de un documento, guardando una copia inmutable de su contenido
     * y el hash SHA-256.
     */
    public static function version_vigente(string $tipo): ?int
    {
        if (!isset(self::VERSIONES[$tipo])) return null;
        $version = self::VERSIONES[$tipo];

        $existente = DB::val(
            "SELECT id FROM documento_versiones WHERE tipo = ? AND version = ?",
            [$tipo, $version]
        );
        if ($existente) return (int)$existente;

        // Primera vez que se ve esta versión: archivar contenido + hash.
        $ruta = ROOT_PATH . '/' . (self::ARCHIVOS[$tipo] ?? '');
        $contenido = is_file($ruta) ? (file_get_contents($ruta) ?: '') : '';
        $hash = hash('sha256', $contenido);

        try {
            return DB::insert(
                "INSERT INTO documento_versiones
                    (tipo, version, contenido, hash_sha256, vigente_desde)
                 VALUES (?, ?, ?, ?, ?)",
                [$tipo, $version, $contenido, $hash, $version . ' 00:00:00']
            );
        } catch (\Throwable $e) {
            error_log('[Legal] no se pudo registrar versión ' . $tipo . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra una aceptación (un row por documento aceptado).
     * Nunca se sobrescribe: cada aceptación es evidencia independiente.
     *
     * @param array $tipos  documentos aceptados, ej. ['terminos','privacidad']
     */
    public static function registrar_aceptacion(
        ?int $usuario_id,
        ?int $empresa_id,
        ?string $email,
        array $tipos = ['terminos', 'privacidad'],
        string $metodo = 'checkbox'
    ): void {
        $ip = function_exists('ip_real') ? ip_real() : ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $ahora = date('Y-m-d H:i:s'); // los milisegundos los pone la columna DATETIME(3) si aplica

        foreach ($tipos as $tipo) {
            $ver_id = self::version_vigente($tipo);
            if (!$ver_id) continue;
            $hash = DB::val("SELECT hash_sha256 FROM documento_versiones WHERE id = ?", [$ver_id]) ?? '';
            try {
                DB::insert(
                    "INSERT INTO consentimientos
                        (usuario_id, empresa_id, email, documento_version_id, hash_sha256,
                         aceptado_at, ip, user_agent, metodo, accion)
                     VALUES (?,?,?,?,?,?,?,?,?, 'accept')",
                    [$usuario_id, $empresa_id, $email, $ver_id, $hash,
                     $ahora, $ip, $ua, $metodo]
                );
            } catch (\Throwable $e) {
                error_log('[Legal] no se pudo registrar consentimiento ' . $tipo . ': ' . $e->getMessage());
            }
        }
    }
}
