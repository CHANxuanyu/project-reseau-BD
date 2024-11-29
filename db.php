<?php
// db.php
$host = 'postgresql-chanxuanyu.alwaysdata.net';
$db   = 'chanxuanyu_restaurant';
$user = 'chanxuanyu';
$pass = 'Chan030903';
$dsn = "pgsql:host=$host;dbname=$db";

try {
    // Create a PDO instance
    $pdo = new PDO($dsn, $user, $pass);
    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection errors
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>
