<?php

$host = $_SERVER['DB_HOST'] ?? 'localhost';
$port = $_SERVER['DB_PORT'] ?? '3306';
$dbname = $_SERVER['DB_DATABASE'] ?? 'database';
$username = $_SERVER['DB_USERNAME'] ?? 'username';
$password = $_SERVER['DB_PASSWORD'] ?? 'password';

$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log("Conexão com banco de dados falhou: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados.");
}

return $pdo;
