<?php
header("Content-Type: application/json");

// Konfigurasi database PostgreSQL (untuk Docker)
$host = getenv('POSTGRES_HOST') ?: 'postgres';
$port = getenv('POSTGRES_PORT') ?: '5432';
$dbname = getenv('POSTGRES_DB') ?: 'webservice';
$username = getenv('POSTGRES_USER') ?: 'root';
$password = getenv('POSTGRES_PASSWORD') ?: 'root123';

try {
    // Membuat koneksi PDO ke PostgreSQL
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password);
    
    // Set error mode ke exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test query
    $query = $conn->prepare("SELECT version()");
    $query->execute();
    $version = $query->fetchColumn();
    
    echo json_encode([
        'message' => 'Connected to PostgreSQL successfully',
        'version' => $version,
        'code' => 200,
        'status' => 'success'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'message' => 'PostgreSQL Connection failed: ' . $e->getMessage(),
        'code' => 500,
        'status' => 'failed'
    ]);
}
?>
