<?php
// CONEXIÓN A POSTGRESQL - workflowP
$host = 'localhost';
$port = '5432';
$dbname = 'workflowP';
$user = 'postgres';
$password = '123456';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>