<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include 'DBConnect.php'; // Pastikan file DBConnect.php ada dan sudah benar
$objDB = new DBConnect;
$conn = $objDB->connect();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        $sql = "SELECT * FROM gis";
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path[3]) && is_numeric($path[3])) {
            $sql = "SELECT * FROM gis WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $path[3], PDO::PARAM_INT); // Tambahkan tipe data PDO::PARAM_INT
            $stmt->execute();
            $gis = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($gis) {
                echo json_encode($gis);
            } else {
                echo json_encode(['error' => 'Data not found']);
            }
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $gis = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($gis);
        }
        break;
    case 'POST':
        $gis = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO gis(lat, lng, locationName, created_at) VALUES(:lat, :lng, :locationName, :created_at)"; // Hapus id, karena biasanya AUTO_INCREMENT
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':lat', $gis->lat);
        $stmt->bindParam(':lng', $gis->lng);
        $stmt->bindParam(':locationName', $gis->locationName);
        $stmt->bindParam(':created_at', $created_at);
        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record created successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to create record.'];
        }
        echo json_encode($response);
        break;
    case 'PUT':
        $path = explode('/', $_SERVER['REQUEST_URI']);
        $gis = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE gis SET lat = :lat, lng = :lng, locationName = :locationName, updated_at = :updated_at WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d');
        $stmt->bindParam(':id', $path[3], PDO::PARAM_INT);
        $stmt->bindParam(':lat', $gis->lat);
        $stmt->bindParam(':lng', $gis->lng);
        $stmt->bindParam(':locationName', $gis->locationName);
        $stmt->bindParam(':updated_at', $updated_at);
        
        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record updated successfully.'];
    
            // Jika perubahan berhasil, Anda dapat mengambil data terbaru dari database
            $sql = "SELECT * FROM gis WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $path[3], PDO::PARAM_INT);
            if ($stmt->execute()) {
                $updatedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
                // Sertakan data terbaru dalam respons
                $response['data'] = $updatedRecord;
            } else {
                $response = ['status' => 0, 'message' => 'Failed to fetch updated record.'];
            }
        } else {
            $response = ['status' => 0, 'message' => 'Failed to update record.'];
        }
        echo json_encode($response);
        break;
    case 'DELETE':
        // Pastikan ID lokasi yang akan dihapus ada dalam URL
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path[3]) && is_numeric($path[3])) {
            $idToDelete = $path[3];
            $sql = "DELETE FROM gis WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $idToDelete, PDO::PARAM_INT);
    
            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to delete record.'];
            }
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'Invalid ID for delete operation.']);
        }
        break;
}
?>
