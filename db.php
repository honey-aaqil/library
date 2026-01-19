<?php
$host = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port = '4000';
$dbname = 'test';
$username = '3V1CZE179Eww3UQ.root';
$password = '8nrno5QOnwstXq9a'; // REPLACE THIS with your actual TiDB password
$ssl_ca = __DIR__ . '/isrgrootx1.pem';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_CA => $ssl_ca,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => true,
    ];

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password, $options);
} catch (PDOException $e) {
    die("ERROR: Could not connect to TiDB. " . $e->getMessage());
}

class PdoSessionHandler implements SessionHandlerInterface
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($id): string|false
    {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['data'];
        }
        return '';
    }

    public function write($id, $data): bool
    {
        $access = time();
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, access, data) VALUES (:id, :access, :data)");
        return $stmt->execute([':id' => $id, ':access' => $access, ':data' => $data]);
    }

    public function destroy($id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function gc($max_lifetime): int|false
    {
        $old = time() - $max_lifetime;
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE access < :old");
        $stmt->execute([':old' => $old]);
        return $stmt->rowCount();
    }
}

$handler = new PdoSessionHandler($pdo);
session_set_save_handler($handler, true);
session_start();
?>