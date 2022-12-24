<?php
namespace App\api;
use \PDO;
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

$objDb = new DbConnect;
$conn = $objDb->connect();
$sql = "SELECT * FROM users";
$path = explode('/', $_SERVER['REQUEST_URI']);
if (isset($path[5]) && is_numeric($path[5])) {
    $sql .= " WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $path[5]);
    $stmt->execute();
    $users = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo json_encode($users);
?>