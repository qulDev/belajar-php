<?php
header("Content-Type: application/json");

// Konfigurasi Redis (untuk Docker)
$host = getenv('REDIS_HOST') ?: 'redis';
$port = getenv('REDIS_PORT') ?: 6379;

try {
    // Membuat koneksi Redis
    $redis = new Redis();
    $redis->connect($host, $port);
    
    // Test Redis
    $redis->set('test_key', 'Hello Redis from PHP!');
    $value = $redis->get('test_key');
    
    echo json_encode([
        'message' => 'Connected to Redis successfully',
        'test_value' => $value,
        'code' => 200,
        'status' => 'success'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'message' => 'Redis Connection failed: ' . $e->getMessage(),
        'code' => 500,
        'status' => 'failed'
    ]);
}
?>
