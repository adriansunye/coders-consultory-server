<?php
namespace Api;

define('ROUTE', __DIR__);

include './CORS.php';
include './ConsultStatement.php';

include './DbConnect.php';

use \PDO;

$objDb = new DbConnect;
$conn = $objDb->connect();

$pathServer = explode('/', $_SERVER['REQUEST_URI']);

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case "GET":
        if ($pathServer[3] === "consults") {
            $sql = "SELECT * FROM consults";
            if (isset($pathServer[4]) && is_numeric($pathServer[4])) {
                $sql .= " WHERE id = :id";
                $consultStatement = new ConsultStatement($objDb, $sql, $pathServer);
                $response = $consultStatement->fetch();
            } else {
                $consultStatement = new ConsultStatement($objDb, $sql, $pathServer);
                $response = $consultStatement->fetchAll();
            }
        }
        
        if ($pathServer[3] === "users") {
            $sql = "SELECT * FROM users";
            if ($pathServer[4] === "admin") {
                $sql .= " WHERE admin = 1";
                $conn = $objDb->connect();
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            else {
                $sql .= " WHERE username = :username";
                $conn = $objDb->connect();
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':username', $pathServer[4]);
                $stmt->execute();
                $response = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        echo json_encode($response);
        break;

    case "POST":
        $base64URL = "no image";

        if (isset($_FILES['image'])) {
            $base64URL = saveImage($pathServer);
        }
        $data = json_decode($_POST["_jsonData"]);

        if ($pathServer[3] === "consults") {
            $sql = "INSERT INTO consults(id, title, description, username, coder, image_path, created_at, updated_at) 
                    VALUES(null, :title, :description, :username, :coder, :image_path, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $created_at = date('Y-m-d H:i:s');
            $stmt->bindParam(':title', $data->title);
            $stmt->bindParam(':description', $data->description);
            $stmt->bindParam(':username', $data->username);
            $stmt->bindParam(':coder', $data->coder);
            $stmt->bindParam(':image_path', $base64URL);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':updated_at', $created_at);

            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Record created successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to create record.'];
            }
            
        } else {
            $sql = "INSERT INTO users (username, email, password, profile_picture_path, created_at, updated_at) 
                    VALUES(:username, :email, :password, :profile_picture_path, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $created_at = date('Y-m-d H:i:s');
             // The plain text password to be hashed
            $plaintext_password =  $data->password;
            
            // The hash of the password that
            // can be stored in the database
            $hash = password_hash($plaintext_password, PASSWORD_DEFAULT);

            $stmt->bindParam(':username', $data->user);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':password', $hash);
            $stmt->bindParam(':profile_picture_path', $base64URL);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':updated_at', $created_at);

            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Record created successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to create record.'];
            }
        }
        echo json_encode($response);
        break;

    case "PUT":
        $consult = json_decode(file_get_contents('php://input'));
        $sql = "UPDATE consults SET title= :title, description =:description, image_path =:image_path, updated_at =:updated_at WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $updated_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':id', $consult->id);
        $stmt->bindParam(':title', $consult->title);
        $stmt->bindParam(':description', $consult->description);
        $stmt->bindParam(':image_path', $consult->image_path);
        $stmt->bindParam(':updated_at', $updated_at);

        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record updated successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to update record.'];
        }
        echo json_encode($response);
        break;

    case "DELETE":
        $sql = "DELETE FROM consults WHERE id = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $pathServer[4]);

        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to delete record.'];
        }
        echo json_encode($response);
        break;
}

function saveImage ($pathServer){
    if ($pathServer[3] === "consults") {
        $upload_dir = "uploads/consults/";
    }
    else{
        $upload_dir = "uploads/users/profilePictures/";
    }

    $file_name = $_FILES["image"]["name"];
    $file_tmp_name = $_FILES["image"]["tmp_name"];
    $random_name = rand(1000,1000000)."-".$file_name;
    $upload_name = $upload_dir.strtolower($random_name);
    $upload_name = preg_replace('/\s+/', '-', $upload_name);
    if(move_uploaded_file($file_tmp_name , $upload_name)) {
        // Convert uploaded file into Base64
        $path = './'.$upload_name;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64URL = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64URL;
    }
}
