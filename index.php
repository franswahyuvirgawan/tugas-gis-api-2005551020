<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include 'DBConnect.php';
$objDB = new DBConnect;
$conn = $objDB->connect();


$user = file_get_contents('php://input');
$method = $_SERVER['REQUEST_METHOD'];
switch($method){
    case 'POST':
        $gis = json_decode(file_get_contents('php://input'));
        $sql = "INSERT INTO gis(id, lat, lng, locationName, created_at) VALUES(null, :lat, :lng, :locationName, :created_at)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':lat', $gis->lat);
        $stmt->bindParam(':lng', $gis->lng);
        $stmt->bindParam(':locationName', $gis->locationName);
        $stmt->bindParam(':created_at', $created_at);
        if($stmt->execute()){
            $response = ['status'=>1, 'message' => 'Record created successfully.'];
        }else{
            $response = ['status'=>0, 'message' => 'Failed to created record.'];
        }
        echo json_decode($response);
        break;
}