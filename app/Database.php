<?php
/**
 * Smart Khata - Secure PDO Database Handler
 * Exclusively uses prepared statements and supports MySQL 8+ and SQLite.
 */

namespace SmartKhata;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private string $driver = 'sqlite';

    private function __construct(array $config) {
        $this->driver = strtolower($config['db_driver'] ?? 'sqlite');

        try {
            if ($this->driver === 'mysql') {
                $host = $config['db_host'] ?? '127.0.0.1';
                $port = $config['db_port'] ?? '3306';
                $dbname = $config['db_name'] ?? 'smart_khata';
                $charset = $config['db_charset'] ?? 'utf8mb4';
                $user = $config['db_user'] ?? 'root';
                $pass = $config['db_pass'] ?? '';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
                $this->pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci"
                ]);
            } else {
                // SQLite driver
                $path = $config['db_path'] ?? (dirname(__DIR__) . '/storage/database/smart_khata.sqlite');
                $dir = dirname($path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0750, true);
                }

                $this->pdo = new PDO("sqlite:" . $path, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // Enable foreign keys and WAL mode in SQLite for concurrency
                $this->pdo->exec("PRAGMA foreign_keys = ON;");
                $this->pdo->exec("PRAGMA journal_mode = WAL;");
            }
        } catch (PDOException $e) {
            throw new Exception("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function init(array $config): self {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            throw new Exception("Database has not been initialized. Please run installer or configure settings.");
        }
        return self::$instance;
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }

    public function getDriver(): string {
        return $this->driver;
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollBack(): bool {
        if ($this->pdo->inTransaction()) {
            return $this->pdo->rollBack();
        }
        return false;
    }

    public function inTransaction(): bool {
        return $this->pdo->inTransaction();
    }

    public function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchOne(string $sql, array $params = []): ?array {
        $stmt = $this->query($sql, $params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
}
