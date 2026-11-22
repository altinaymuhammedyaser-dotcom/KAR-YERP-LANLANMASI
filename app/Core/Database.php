<?php
/**
 * Akilli Kariyer - Veritabani katmani (PDO tabanli merkezi yonetim)
 */

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    /**
     * Yapilandirmayi yukler.
     */
    public static function config(): array
    {
        if (self::$config === []) {
            $file = APP_ROOT . '/config/database.php';
            self::$config = is_file($file) ? require $file : [];
        }
        return self::$config;
    }

    /**
     * PDO baglantisini dondurur (tekil ornek).
     */
    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = self::config();
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host'] ?? '127.0.0.1',
            $cfg['port'] ?? '3306',
            $cfg['database'] ?? 'akilli_kariyer',
            $cfg['charset'] ?? 'utf8mb4'
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $cfg['username'] ?? 'root',
                $cfg['password'] ?? '',
                $cfg['options'] ?? []
            );
            self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            self::$pdo->exec("SET time_zone = '+03:00'");
        } catch (PDOException $e) {
            self::fail($e);
        }

        return self::$pdo;
    }

    /**
     * Hazirlanmis sorgu calistirir.
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Tek satır getirir.
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Tüm satırları getirir.
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Tek bir skaler deger getirir.
     */
    public static function fetchColumn(string $sql, array $params = [], mixed $default = null): mixed
    {
        $value = self::query($sql, $params)->fetchColumn();
        return $value === false ? $default : $value;
    }

    /**
     * Satır ekler ve eklenen kaydin id'sini dondurur.
     */
    public static function insert(string $sql, array $params = []): int
    {
        self::query($sql, $params);
        return (int) self::connection()->lastInsertId();
    }

    /**
     * Satir gunceller, etkilenen satir sayisini dondurur.
     */
    public static function update(string $sql, array $params = []): int
    {
        return self::query($sql, $params)->rowCount();
    }

    /**
     * Tek bir deger icin yardimci (filtrelenmis deger).
     */
    public static function value(string $sql, array $params = [], mixed $default = null): mixed
    {
        return self::fetchColumn($sql, $params, $default);
    }

    /**
     * Islem (transaction) icinde calistirir. Hata olursa geri alir.
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Baglanti hatasi durumunda anlasilir mesaj gosterir.
     */
    private static function fail(PDOException $e): void
    {
        error_log('[DB] ' . $e->getMessage());
        http_response_code(500);
        $detail = APP_DEBUG ? htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : '';
        echo '<!DOCTYPE html><html lang="tr"><head><meta charset="utf-8">'
           . '<title>Veritabanı Bağlantı Hatası</title>'
           . '<style>body{font-family:"Segoe UI",system-ui,sans-serif;background:#F6F8FC;color:#172B42;display:grid;place-items:center;min-height:100vh;margin:0}'
           . '.box{background:#fff;border:1px solid #E3E9F2;border-radius:16px;padding:40px;max-width:620px;box-shadow:0 18px 40px rgba(20,43,74,.08)}'
           . 'h1{color:#142B4A;font-size:22px;margin:0 0 12px}code{background:#EAF4FA;padding:2px 7px;border-radius:5px;font-size:13px}'
           . 'p{color:#748399;line-height:1.7}</style></head><body><div class="box">'
           . '<h1>Veritabanına bağlanılamadı</h1>'
           . '<p>Uygulama, <code>config/database.php</code> dosyasındaki bilgilerle MySQL sunucusuna bağlanmayı dener. '
           . 'Lütfen aşağıdaki adımları kontrol edin:</p>'
           . '<p><strong>1.</strong> XAMPP / MAMP üzerinden <strong>MySQL servisinin çalıştığından</strong> emin olun.<br>'
           . '<strong>2.</strong> <code>database/database.sql</code> dosyasını phpMyAdmin ile içe aktardığınızdan emin olun.<br>'
           . '<strong>3.</strong> <code>config/database.php</code> içindeki kullanıcı adı / şifre bilgilerini kontrol edin.</p>'
           . ($detail !== '' ? '<p><strong>Hata:</strong><br><code>' . $detail . '</code></p>' : '')
           . '</div></body></html>';
        exit;
    }
}
