<?php
// ============================================================
//  CotizaApp — core/DB.php
//  PDO wrapper singleton
// ============================================================

defined('COTIZAAPP') or die;

class DB
{
    private static $pdo = null;

    // ─── Conexión singleton ──────────────────────────────────
    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // charset=utf8mb4 ya va en el DSN — no necesita INIT_COMMAND
        ];

        try {
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Sincronizar timezone de MySQL con la de PHP para que NOW()
            // y strtotime() operen en la misma zona horaria.
            $tz = date('P'); // e.g. "-07:00"
            self::$pdo->exec("SET time_zone = '{$tz}'");
        } catch (PDOException $e) {
            if (DEBUG) {
                throw $e;
            }
            http_response_code(500);
            die('Error de conexión a base de datos.');
        }

        return self::$pdo;
    }

    // ─── Prepare + execute + fetch all ──────────────────────
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ─── Fetch una sola fila ─────────────────────────────────
    public static function row(string $sql, array $params = []): ?array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ─── Fetch un solo valor ─────────────────────────────────
    public static function val(string $sql, array $params = []): mixed
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // ─── Execute (INSERT, UPDATE, DELETE) ───────────────────
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // ─── Insert y retorna último ID ──────────────────────────
    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return (int) self::connect()->lastInsertId();
    }

    // ─── Transacciones ───────────────────────────────────────
    public static function beginTransaction(): void
    {
        self::connect()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connect()->commit();
    }

    public static function rollback(): void
    {
        if (self::connect()->inTransaction()) {
            self::connect()->rollBack();
        }
    }

    // ─── Folio siguiente (thread-safe) ───────────────────────
    // Genera el siguiente número consecutivo para COT / VTA / REC
    // Retorna: "COT-2025-0001"
    public static function siguiente_folio(int $empresa_id, string $tipo, string $prefijo): string
    {
        $anio = (int) date('Y');
        $pdo  = self::connect();

        // INSERT OR UPDATE atómico
        $pdo->prepare("
            INSERT INTO folios (empresa_id, tipo, anio, ultimo)
            VALUES (?, ?, ?, 1)
            ON DUPLICATE KEY UPDATE ultimo = ultimo + 1
        ")->execute([$empresa_id, $tipo, $anio]);

        $ultimo = (int) $pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn();

        // Si fue UPDATE, LAST_INSERT_ID devuelve 0 en algunos drivers; lo leemos directo
        if ($ultimo === 0) {
            $ultimo = (int) DB::val(
                "SELECT ultimo FROM folios WHERE empresa_id=? AND tipo=? AND anio=?",
                [$empresa_id, $tipo, $anio]
            );
        }

        return sprintf('%s-%d-%04d', $prefijo, $anio, $ultimo);
    }

    // ─── UPSERT helper ───────────────────────────────────────
    // Construye INSERT ... ON DUPLICATE KEY UPDATE dinámicamente
    public static function upsert(string $table, array $data): int
    {
        $cols    = array_keys($data);
        $vals    = array_values($data);
        $holders = implode(', ', array_fill(0, count($cols), '?'));
        $colStr  = implode(', ', array_map(fn($c) => "`$c`", $cols));
        $updates = implode(', ', array_map(fn($c) => "`$c` = VALUES(`$c`)", $cols));

        $sql = "INSERT INTO `$table` ($colStr) VALUES ($holders)
                ON DUPLICATE KEY UPDATE $updates";

        $stmt = self::connect()->prepare($sql);
        $stmt->execute($vals);
        return (int) self::connect()->lastInsertId() ?: 0;
    }
}
