<?php
namespace Api;
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

$objDb = new DbConnect;
$conn = $objDb->connect();
$user = json_decode( file_get_contents('php://input') );
$sql = "INSERT INTO users(id, name, email, mobile, created_at) VALUES(null, :name, :email, :mobile, :created_at)";
$stmt = $conn->prepare($sql);
$created_at = date('Y-m-d H:i:s');
$stmt->bindParam(':name', $user->name);
$stmt->bindParam(':email', $user->email);
$stmt->bindParam(':mobile', $user->mobile);
$stmt->bindParam(':created_at', $created_at);

if($stmt->execute()) {
    $response = ['status' => 1, 'message' => 'Record created successfully.'];
} else {
    $response = ['status' => 0, 'message' => 'Failed to create record.'];
}
echo json_encode($response);
?>