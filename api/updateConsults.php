<?php
namespace App\api;
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
$objDb = new DbConnect;
$conn = $objDb->connect();
$user = json_decode( file_get_contents('php://input') );
$sql = "UPDATE users SET name= :name, email =:email, mobile =:mobile, updated_at =:updated_at WHERE id = :id";
$stmt = $conn->prepare($sql);
$updated_at = date('Y-m-d H:i:s');
$stmt->bindParam(':id', $user->id);
$stmt->bindParam(':name', $user->name);
$stmt->bindParam(':email', $user->email);
$stmt->bindParam(':mobile', $user->mobile);
$stmt->bindParam(':updated_at', $updated_at);

if($stmt->execute()) {
    $response = ['status' => 1, 'message' => 'Record updated successfully.'];
} else {
    $response = ['status' => 0, 'message' => 'Failed to update record.'];
}
echo json_encode($response);
?>