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
?>