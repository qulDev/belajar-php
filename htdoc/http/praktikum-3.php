<?php
header("Content-Type: application/json"); // Set the response content type to JSON

// Konfigurasi database MySQL (untuk Docker)
$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_DATABASE') ?: 'webservice';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root_password';

try {
    // Membuat koneksi MySQLi
    $conn = new mysqli($host, $username, $password, $dbname, $port);

    // Cek koneksi
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Handle API request
    $method = $_SERVER['REQUEST_METHOD'];
    switch ($method) {
        case 'GET':
        if (isset($_GET['id'])) {
            getDetailProvince($conn, $_GET['id']);
        } else {
        getAllProvinces($conn);  
        }
        break;
        default:
            echo json_encode([
                'message' => 'Method Not Allowed',
                'code' => 405,
                'status' => 'failed'
            ]);
            break;
    }

} catch (Exception $e) {
    // Response error
    $response = [
        'message' => 'Connection failed: ' . $e->getMessage(),
        'code' => 500,
        'status' => 'failed'
    ];

    echo json_encode($response);
}

function getAllProvinces($conn)
{
    try {
        $query = "SELECT * FROM provinces";
        $result = $conn->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            "status" => "success",
            "code" => 200,
            "message" => "Data retrieved successfully",
            "data" => $data
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "failed",
            "code" => 500,
            "message" => "Query failed: " . $e->getMessage(),
            "data" => []
        ]);
    }
}


function getDetailProvince($conn,$id)  {

    try {
        $query = "SELECT * FROM provinces WHERE id_province = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }

        $data = $result->fetch_assoc();

        if (!$data) {
            echo json_encode([
                "status" => "failed",
                "code" => 404,
                "message" => "Province not found",
                "data" => []
            ]);
            return;
        }

        echo json_encode([
            "status" => "success",
            "code" => 200,
            "message" => "Data retrieved successfully",
            "data" => $data
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "failed",
            "code" => 500,
            "message" => "Query failed: " . $e->getMessage(),
            "data" => []
        ]);
    }
}


?>