# Belajar PHP with MySQL, PostgreSQL, and Redis

Project PHP dengan Docker yang sudah dikonfigurasi untuk connect ke MySQL, PostgreSQL, dan Redis.

## Requirements
- Docker
- Docker Compose

## Struktur Container
- **php-app**: PHP 8.2 dengan Apache (Port 8080)
- **mysql-db**: MySQL 8.0 (Port 3306)
- **postgres-db**: PostgreSQL 15 (Port 5432)
- **redis-cache**: Redis 7 Alpine (Port 6379)

## Cara Menjalankan

### 1. Build dan Jalankan Container
```bash
docker-compose up -d --build
```

### 2. Cek Status Container
```bash
docker-compose ps
```

### 3. Testing Koneksi
Buka browser dan akses:
```
http://localhost:8080/test-connections.php
```

### 4. Stop Container
```bash
docker-compose down
```

### 5. Stop dan Hapus Data
```bash
docker-compose down -v
```

## Konfigurasi Koneksi

### MySQL
```php
$mysql = new PDO(
    "mysql:host=mysql;dbname=my_database",
    "my_user",
    "my_password"
);
```

### PostgreSQL
```php
$pgsql = new PDO(
    "pgsql:host=postgres;dbname=my_database",
    "my_user",
    "my_password"
);
```

### Redis
```php
$redis = new Redis();
$redis->connect('redis', 6379);
```

## PHP Extensions Terinstall
- PDO
- PDO MySQL
- PDO PostgreSQL
- PostgreSQL
- MySQLi
- Redis
- Zip

## Environment Variables
Anda bisa mengubah konfigurasi database di file `docker-compose.yml` pada bagian `environment` masing-masing service.

## Volume
Data akan disimpan di Docker volumes:
- `mysql-data`: Data MySQL
- `postgres-data`: Data PostgreSQL
- `redis-data`: Data Redis

## Network
Semua container terhubung dalam network `app-network` sehingga bisa saling berkomunikasi.
<?php
echo "<h1>Testing Database Connections</h1>";

// Test MySQL Connection
echo "<h2>MySQL Connection Test</h2>";
try {
    $mysql = new PDO(
        "mysql:host=mysql;dbname=my_database",
        "my_user",
        "my_password"
    );
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ MySQL connection successful!<br>";
    echo "MySQL version: " . $mysql->query('SELECT VERSION()')->fetchColumn() . "<br>";
} catch(PDOException $e) {
    echo "❌ MySQL connection failed: " . $e->getMessage() . "<br>";
}

// Test PostgreSQL Connection
echo "<h2>PostgreSQL Connection Test</h2>";
try {
    $pgsql = new PDO(
        "pgsql:host=postgres;dbname=my_database",
        "my_user",
        "my_password"
    );
    $pgsql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ PostgreSQL connection successful!<br>";
    echo "PostgreSQL version: " . $pgsql->query('SELECT version()')->fetchColumn() . "<br>";
} catch(PDOException $e) {
    echo "❌ PostgreSQL connection failed: " . $e->getMessage() . "<br>";
}

// Test Redis Connection
echo "<h2>Redis Connection Test</h2>";
try {
    $redis = new Redis();
    $redis->connect('redis', 6379);
    echo "✅ Redis connection successful!<br>";
    
    // Test set/get
    $redis->set('test_key', 'Hello from Redis!');
    $value = $redis->get('test_key');
    echo "Redis test value: " . $value . "<br>";
    
    echo "Redis info: " . $redis->info()['redis_version'] . "<br>";
} catch(Exception $e) {
    echo "❌ Redis connection failed: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h2>PHP Extensions Loaded</h2>";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅' : '❌') . "<br>";
echo "PDO PostgreSQL: " . (extension_loaded('pdo_pgsql') ? '✅' : '❌') . "<br>";
echo "PostgreSQL: " . (extension_loaded('pgsql') ? '✅' : '❌') . "<br>";
echo "MySQLi: " . (extension_loaded('mysqli') ? '✅' : '❌') . "<br>";
echo "Redis: " . (extension_loaded('redis') ? '✅' : '❌') . "<br>";
?>

