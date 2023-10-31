<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

$db_conn = mysqli_connect("localhost", "root", "root", "react-crud");
if ($db_conn === false) {
  die("ERROR: Could Not Connect" . mysqli_connect_error());
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case "GET":
    $path = explode('/', $_SERVER['REQUEST_URI']);

    if (isset($path[4]) && is_numeric($path[4])) {
      // Ambil data GIS berdasarkan ID
      $json_array = array();
      $id = $path[4];

      $getgisrow = mysqli_query($db_conn, "SELECT * FROM gis_encode WHERE id='$id'");
      while ($gisrow = mysqli_fetch_array($getgisrow)) {
        $json_array['rowgisdata'] = array('id' => $gisrow['id'], 'encoded_polyline' => $gisrow['encoded_polyline']);
      }
      echo json_encode($json_array['rowgisdata']);
      return;
    } else {
      // Ambil semua data GIS
      $allgis = mysqli_query($db_conn, "SELECT * FROM gis_encode");
      if (mysqli_num_rows($allgis) > 0) {
        while ($row = mysqli_fetch_array($allgis)) {
          $json_array["gisdata"][] = array("id" => $row['id'], "encoded_polyline" => $row["encoded_polyline"]);
        }
        echo json_encode($json_array["gisdata"]);
        return;
      } else {
        echo json_encode([]);
        return;
      }
    }
    break;
  case "POST":
    // Tambahkan data GIS
    $gispostdata = json_decode(file_get_contents("php://input"));
    $encoded_polyline = $gispostdata->encoded_polyline;
    $id = $gispostdata->id;
    $result = mysqli_query($db_conn, "INSERT INTO gis_encode (id, encoded_polyline) VALUES('$id', '$encoded_polyline')");

    if ($result) {
      echo json_encode(["success" => "GIS Added Successfully"]);
      return;
    } else {
      echo json_encode(["success" => "Please Check the GIS Data!"]);
      return;
    }
    break;
  case "PUT":
    // Perbarui data GIS
    $gisUpdate = json_decode(file_get_contents("php://input"));

    $id = $gisUpdate->id;
    $encoded_polyline = $gisUpdate->encoded_polyline;

    $updateData = mysqli_query($db_conn, "UPDATE gis_encode SET encoded_polyline='$encoded_polyline' WHERE id='$id'");
    if ($updateData) {
      echo json_encode(["success" => "GIS Record Update Successfully"]);
      return;
    } else {
      echo json_encode(["success" => "Please Check the GIS Data!"]);
      return;
    }
    break;
  case "DELETE":
    // Hapus semua data GIS
    $deleteAllData = mysqli_query($db_conn, "DELETE FROM gis_encode");

    if ($deleteAllData) {
      echo json_encode(["success" => "Semua data GIS telah dihapus"]);
    } else {
      echo json_encode(["success" => "Gagal menghapus data GIS"]);
    }
    return;
}
?>
