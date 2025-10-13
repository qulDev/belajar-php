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
        case "POST":
            $data = json_decode(file_get_contents('php://input'), true);
            createProvince($conn, $data);
            break;
case "PUT":
    if (!isset($_GET['id'])) {
        echo json_encode([
            "status" => "failed",
            "code" => 400,
            "message" => "Invalid input: ID is required for update",
            "data" => []
        ]);
        return;
    }
        $data = json_decode(file_get_contents('php://input'), true);
        updateProvince($conn, $_GET['id'], $data);
    break;
    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode([
                "status" => "failed",
                "code" => 400,
                "message" => "Invalid input: ID is required for deletion",
                "data" => []
            ]);
            return;
        }
        deleteProvince($conn, $_GET['id']);
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

function createProvince($conn,$data)  {

if (!isset(($data['name'])) || empty($data['name'])) {
    echo json_encode([
        "status" => "failed",
        "code" => 400,
        "message" => "Invalid input: Name is required",
        "data" => []
    ]);
    return;
}

    $name = $data['name'] ?? null;
    if (!$name) {
        echo json_encode([
            "status" => "failed",
            "code" => 400,
            "message" => "Name is required",
            "data" => []
        ]);
        return;
    }

    try {
        $conn -> query("INSERT INTO provinces (name_province) VALUES ('$name')");
        echo json_encode([
            "status" => "success",
            "code" => 201,
            "message" => "Province created successfully",
        ]);
    } catch (Throwable $th) {
        echo json_encode([
            "status" => "failed",
            "code" => 500,
            "message" => "Failed to create province: " . $th->getMessage(),
            "data" => []
        ]);
    }
}

function updateProvince($conn,$id,$data)  {
    if (!isset(($data['name'])) || empty($data['name'])) {
        echo json_encode([
            "status" => "failed",
            "code" => 400,
            "message" => "Invalid input: Name is required",
            "data" => []
        ]);
        return;

    }

    try {
        $stmt = $conn->prepare("UPDATE provinces SET name_province = ? WHERE id_province = ?");
        $stmt->bind_param("si", $data['name'], $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
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
            "message" => "Province updated successfully",
            "data" => []
        ]);
    } catch (Throwable $th) {
        echo json_encode([
            "status" => "failed",
            "code" => 500,
            "message" => "Failed to update province: " . $th->getMessage(),
            "data" => []
        ]);
    }
}

function deleteProvince($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM provinces WHERE id_province = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
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
            "message" => "Province deleted successfully",
            "data" => []
        ]);
    } catch (Throwable $th) {
        echo json_encode([
            "status" => "failed",
            "code" => 500,
            "message" => "Failed to delete province: " . $th->getMessage(),
            "data" => []
        ]);
    }
}

?>