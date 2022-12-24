<?php
namespace Api;
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");  
header("Access-Control-Allow-Methods: *");

$objDb = new DbConnect;
$conn = $objDb->connect();
$sql = "DELETE FROM users WHERE id = :id";
$path = explode('/', $_SERVER['REQUEST_URI']);

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $path[6]);

if($stmt->execute()) {
    $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
} else {
    $response = ['status' => 0, 'message' => 'Failed to delete record.'];
}
echo json_encode($response);
?>