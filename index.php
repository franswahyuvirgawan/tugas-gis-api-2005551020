<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Headers:*");
header("Access-Control-Allow-Methods:*");

$db_conn= mysqli_connect("localhost","root", "root", "react-crud");
if($db_conn===false)
{
  die("ERROR: Could Not Connect".mysqli_connect_error());
}

$method = $_SERVER['REQUEST_METHOD'];
//echo "test----".$method; die;
switch($method)
{
  case "GET": 
    $path= explode('/', $_SERVER['REQUEST_URI']);

    if(isset($path[4]) && is_numeric($path[4]))
    {
      $json_array= array();
      $id= $path[4];
      
      $getgisrow= mysqli_query($db_conn, "SELECT * FROM gis WHERE id='$id' ");
      while($gisrow= mysqli_fetch_array($getgisrow))
      {
       $json_array['rowgisdata']= array('id'=>$gisrow['id'],'lat'=>$gisrow['lat'], 'lng'=>$gisrow['lng'], 'locationName'=>$gisrow['locationName'],);
      }
      echo json_encode($json_array['rowgisdata']);
      return;

    } else { 

    $allgis= mysqli_query($db_conn, "SELECT * FROM gis"); 
    if(mysqli_num_rows($allgis) > 0)
    {
      while($row= mysqli_fetch_array($allgis))
      {
       $json_array["gisdata"][]= array("id"=>$row['id'], "lat"=>$row["lat"], "lng"=>$row["lng"], "locationName"=>$row["locationName"]);
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
      $gispostdata= json_decode(file_get_contents("php://input"));
      //echo "sucess data";
      //print_r($gispostdata); die;
      $lat= $gispostdata->lat;
      $lng= $gispostdata->lng;
      $locationName= $gispostdata->locationName;
      $result= mysqli_query($db_conn, "INSERT INTO gis (lat, lng, locationName) 
      VALUES('$lat', '$lng', '$locationName')");

      if($result)
      {
        echo json_encode(["success"=>"gis Added Successfully"]);
        return;
      } else {
          echo json_encode(["success"=>"Please Check the gis Data!"]);
          return; 
      }
      break;
      case "PUT":
        $gisUpdate= json_decode(file_get_contents("php://input"));

         $id= $gisUpdate->id;
         $lat= $gisUpdate->lat;
         $lng= $gisUpdate->lng;
         $locationName= $gisUpdate->locationName;

         $updateData= mysqli_query($db_conn, "UPDATE gis SET lat='$lat', lng='$lng', locationName='$locationName' WHERE id='$id'  ");
         if($updateData)
         {
           echo json_encode(["success"=>"gis Record Update Successfully"]);
           return;
         } else {
             echo json_encode(["success"=>"Please Check the gis Data!"]);
             return; 
         }
       // print_r($gisUpdate); die;
        break;
        case "DELETE":
          // Baca data JSON dari body permintaan
          $data = json_decode(file_get_contents("php://input"));
          $locationId = $data->locationId;
        
          // Periksa apakah locationId ada dan apakah Anda ingin menjalankan DELETE
          if (!empty($locationId)) {
            $result = mysqli_query($db_conn, "DELETE FROM gis WHERE id = '$locationId'");
            if ($result) {
              echo json_encode(["success" => "gis Record Deleted Successfully"]);
              return;
            } else {
              echo json_encode(["error" => "Failed to delete gis record"]);
              return;
            }
          } else {
            echo json_encode(["error" => "Invalid locationId"]);
            return;
          }
          break;
};

?>