<?php
/*
================================================================================
TUGAS 1 - REST API SERVICE
Resource: CITY dan DISTRICT
Operasi: GET, POST, PUT, DELETE
NIM: 2301010164
================================================================================

QUERY PEMBUATAN TABEL:
------------------------

-- Tabel City
CREATE TABLE IF NOT EXISTS city (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    population INT,
    area DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel District
CREATE TABLE IF NOT EXISTS district (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city_id INT NOT NULL,
    postal_code VARCHAR(10),
    population INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE CASCADE
);

================================================================================
ENDPOINT API:
------------------------
1. City Resource:
   - GET    /city          -> Ambil semua data city
   - GET    /city/{id}     -> Ambil data city berdasarkan ID
   - POST   /city          -> Tambah data city baru
   - PUT    /city/{id}     -> Update data city
   - DELETE /city/{id}     -> Hapus data city

2. District Resource:
   - GET    /district      -> Ambil semua data district
   - GET    /district/{id} -> Ambil data district berdasarkan ID
   - POST   /district      -> Tambah data district baru
   - PUT    /district/{id} -> Update data district
   - DELETE /district/{id} -> Hapus data district


================================================================================
*/

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// ============================================================================
// KONFIGURASI DATABASE
// ============================================================================
$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_DATABASE') ?: 'tugas_1_pws';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root_password';

// ============================================================================
// KONEKSI DATABASE
// ============================================================================
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Koneksi database gagal: " . $e->getMessage()
    ]);
    exit();
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function sendResponse($statusCode, $success, $message, $data = null) {
    http_response_code($statusCode);
    $response = [
        "success" => $success,
        "message" => $message
    ];
    if ($data !== null) {
        $response["data"] = $data;
    }
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit();
}

function getRequestBody() {
    return json_decode(file_get_contents("php://input"), true);
}

// ============================================================================
// CITY RESOURCE - OPERASI GET
// ============================================================================
function getCities($conn, $id = null) {
    try {
        if ($id) {
            // GET city berdasarkan ID
            $stmt = $conn->prepare("SELECT * FROM city WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $city = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($city) {
                sendResponse(200, true, "Data city berhasil diambil", $city);
            } else {
                sendResponse(404, false, "City dengan ID $id tidak ditemukan");
            }
        } else {
            // GET semua city
            $stmt = $conn->query("SELECT * FROM city ORDER BY id ASC");
            $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, true, "Data city berhasil diambil", $cities);
        }
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// CITY RESOURCE - OPERASI POST
// ============================================================================
function createCity($conn) {
    try {
        $data = getRequestBody();
        
        // Validasi input
        if (empty($data['name']) || empty($data['province'])) {
            sendResponse(400, false, "Field 'name' dan 'province' wajib diisi");
        }
        
        $stmt = $conn->prepare("INSERT INTO city (name, province, population, area) VALUES (:name, :province, :population, :area)");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':province', $data['province']);
        $stmt->bindParam(':population', $data['population']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->execute();
        
        $newId = $conn->lastInsertId();
        
        // Ambil data yang baru dibuat
        $stmt = $conn->prepare("SELECT * FROM city WHERE id = :id");
        $stmt->bindParam(':id', $newId);
        $stmt->execute();
        $newCity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(201, true, "City berhasil ditambahkan", $newCity);
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// CITY RESOURCE - OPERASI PUT
// ============================================================================
function updateCity($conn, $id) {
    try {
        // Cek apakah city ada
        $stmt = $conn->prepare("SELECT * FROM city WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            sendResponse(404, false, "City dengan ID $id tidak ditemukan");
        }
        
        $data = getRequestBody();
        
        // Validasi input
        if (empty($data['name']) || empty($data['province'])) {
            sendResponse(400, false, "Field 'name' dan 'province' wajib diisi");
        }
        
        $stmt = $conn->prepare("UPDATE city SET name = :name, province = :province, population = :population, area = :area WHERE id = :id");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':province', $data['province']);
        $stmt->bindParam(':population', $data['population']);
        $stmt->bindParam(':area', $data['area']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Ambil data yang sudah diupdate
        $stmt = $conn->prepare("SELECT * FROM city WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $updatedCity = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(200, true, "City berhasil diupdate", $updatedCity);
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// CITY RESOURCE - OPERASI DELETE
// ============================================================================
function deleteCity($conn, $id) {
    try {
        // Cek apakah city ada
        $stmt = $conn->prepare("SELECT * FROM city WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $city = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$city) {
            sendResponse(404, false, "City dengan ID $id tidak ditemukan");
        }
        
        // Hapus city
        $stmt = $conn->prepare("DELETE FROM city WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        sendResponse(200, true, "City berhasil dihapus", $city);
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// DISTRICT RESOURCE - OPERASI GET
// ============================================================================
function getDistricts($conn, $id = null) {
    try {
        if ($id) {
            // GET district berdasarkan ID dengan JOIN ke city
            $stmt = $conn->prepare("SELECT d.*, c.name as city_name, c.province 
                                   FROM district d 
                                   LEFT JOIN city c ON d.city_id = c.id 
                                   WHERE d.id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $district = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($district) {
                sendResponse(200, true, "Data district berhasil diambil", $district);
            } else {
                sendResponse(404, false, "District dengan ID $id tidak ditemukan");
            }
        } else {
            // GET semua district dengan JOIN ke city
            $stmt = $conn->query("SELECT d.*, c.name as city_name, c.province 
                                 FROM district d 
                                 LEFT JOIN city c ON d.city_id = c.id 
                                 ORDER BY d.id ASC");
            $districts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            sendResponse(200, true, "Data district berhasil diambil", $districts);
        }
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// DISTRICT RESOURCE - OPERASI POST
// ============================================================================
function createDistrict($conn) {
    try {
        $data = getRequestBody();
        
        // Validasi input
        if (empty($data['name']) || empty($data['city_id'])) {
            sendResponse(400, false, "Field 'name' dan 'city_id' wajib diisi");
        }
        
        // Cek apakah city_id valid
        $stmt = $conn->prepare("SELECT id FROM city WHERE id = :city_id");
        $stmt->bindParam(':city_id', $data['city_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            sendResponse(400, false, "City dengan ID {$data['city_id']} tidak ditemukan");
        }
        
        $stmt = $conn->prepare("INSERT INTO district (name, city_id, postal_code, population) VALUES (:name, :city_id, :postal_code, :population)");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':city_id', $data['city_id']);
        $stmt->bindParam(':postal_code', $data['postal_code']);
        $stmt->bindParam(':population', $data['population']);
        $stmt->execute();
        
        $newId = $conn->lastInsertId();
        
        // Ambil data yang baru dibuat dengan JOIN
        $stmt = $conn->prepare("SELECT d.*, c.name as city_name, c.province 
                               FROM district d 
                               LEFT JOIN city c ON d.city_id = c.id 
                               WHERE d.id = :id");
        $stmt->bindParam(':id', $newId);
        $stmt->execute();
        $newDistrict = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(201, true, "District berhasil ditambahkan", $newDistrict);
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// DISTRICT RESOURCE - OPERASI PUT
// ============================================================================
function updateDistrict($conn, $id) {
    try {
        // Cek apakah district ada
        $stmt = $conn->prepare("SELECT * FROM district WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            sendResponse(404, false, "District dengan ID $id tidak ditemukan");
        }
        
        $data = getRequestBody();
        
        // Validasi input
        if (empty($data['name']) || empty($data['city_id'])) {
            sendResponse(400, false, "Field 'name' dan 'city_id' wajib diisi");
        }
        
        // Cek apakah city_id valid
        $stmt = $conn->prepare("SELECT id FROM city WHERE id = :city_id");
        $stmt->bindParam(':city_id', $data['city_id']);
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            sendResponse(400, false, "City dengan ID {$data['city_id']} tidak ditemukan");
        }
        
        $stmt = $conn->prepare("UPDATE district SET name = :name, city_id = :city_id, postal_code = :postal_code, population = :population WHERE id = :id");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':city_id', $data['city_id']);
        $stmt->bindParam(':postal_code', $data['postal_code']);
        $stmt->bindParam(':population', $data['population']);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Ambil data yang sudah diupdate dengan JOIN
        $stmt = $conn->prepare("SELECT d.*, c.name as city_name, c.province 
                               FROM district d 
                               LEFT JOIN city c ON d.city_id = c.id 
                               WHERE d.id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $updatedDistrict = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse(200, true, "District berhasil diupdate", $updatedDistrict);
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// DISTRICT RESOURCE - OPERASI DELETE
// ============================================================================
function deleteDistrict($conn, $id) {
    try {
        // Cek apakah district ada
        $stmt = $conn->prepare("SELECT d.*, c.name as city_name 
                               FROM district d 
                               LEFT JOIN city c ON d.city_id = c.id 
                               WHERE d.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $district = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$district) {
            sendResponse(404, false, "District dengan ID $id tidak ditemukan");
        }
        
        // Hapus district
        $stmt = $conn->prepare("DELETE FROM district WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        sendResponse(200, true, "District berhasil dihapus", $district);
    } catch(PDOException $e) {
        sendResponse(500, false, "Error: " . $e->getMessage());
    }
}

// ============================================================================
// ROUTING - HANDLE REQUEST
// ============================================================================

// Ambil HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Ambil resource dari parameter
$resource = isset($_GET['resource']) ? $_GET['resource'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Routing berdasarkan resource dan method
switch($resource) {
    case 'city':
        switch($method) {
            case 'GET':
                getCities($conn, $id);
                break;
            case 'POST':
                createCity($conn);
                break;
            case 'PUT':
                if ($id) {
                    updateCity($conn, $id);
                } else {
                    sendResponse(400, false, "ID city diperlukan untuk operasi PUT");
                }
                break;
            case 'DELETE':
                if ($id) {
                    deleteCity($conn, $id);
                } else {
                    sendResponse(400, false, "ID city diperlukan untuk operasi DELETE");
                }
                break;
            default:
                sendResponse(405, false, "Method $method tidak diizinkan");
        }
        break;
        
    case 'district':
        switch($method) {
            case 'GET':
                getDistricts($conn, $id);
                break;
            case 'POST':
                createDistrict($conn);
                break;
            case 'PUT':
                if ($id) {
                    updateDistrict($conn, $id);
                } else {
                    sendResponse(400, false, "ID district diperlukan untuk operasi PUT");
                }
                break;
            case 'DELETE':
                if ($id) {
                    deleteDistrict($conn, $id);
                } else {
                    sendResponse(400, false, "ID district diperlukan untuk operasi DELETE");
                }
                break;
            default:
                sendResponse(405, false, "Method $method tidak diizinkan");
        }
        break;
        
    default:
        // Tampilkan dokumentasi API
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "REST API Service - City & District",
            "nim" => "2301010164",
            "endpoints" => [
                "city" => [
                    "GET /index.php?resource=city" => "Ambil semua data city",
                    "GET /index.php?resource=city&id={id}" => "Ambil data city berdasarkan ID",
                    "POST /index.php?resource=city" => "Tambah city baru (Body: name, province, population, area)",
                    "PUT /index.php?resource=city&id={id}" => "Update city (Body: name, province, population, area)",
                    "DELETE /index.php?resource=city&id={id}" => "Hapus city"
                ],
                "district" => [
                    "GET /index.php?resource=district" => "Ambil semua data district",
                    "GET /index.php?resource=district&id={id}" => "Ambil data district berdasarkan ID",
                    "POST /index.php?resource=district" => "Tambah district baru (Body: name, city_id, postal_code, population)",
                    "PUT /index.php?resource=district&id={id}" => "Update district (Body: name, city_id, postal_code, population)",
                    "DELETE /index.php?resource=district&id={id}" => "Hapus district"
                ]
            ],
            "query_database" => [
                "city" => "CREATE TABLE city (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, province VARCHAR(100) NOT NULL, population INT, area DECIMAL(10,2), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)",
                "district" => "CREATE TABLE district (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, city_id INT NOT NULL, postal_code VARCHAR(10), population INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (city_id) REFERENCES city(id) ON DELETE CASCADE)"
            ]
        ], JSON_PRETTY_PRINT);
}

$conn = null;
?>