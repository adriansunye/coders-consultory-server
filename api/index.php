<?php

namespace Api;

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

use \PDO;

include './DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

$pathServer = explode('/', $_SERVER['REQUEST_URI']);

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case "GET":
        if ($pathServer[3] === "consults") {
        $sql = "SELECT * FROM consults";
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($path[4]) && is_numeric($path[4])) {
            $sql .= " WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $path[4]);
            $stmt->execute();
            $consults = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $consults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode($consults);
    }else {

        $sql = "SELECT * FROM users";
        $path = explode('/', $_SERVER['REQUEST_URI']);
        if ($path[4] === "admin") {
            $sql .= " WHERE admin = 1";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
            $sql .= " WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $path[4]);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        

        echo json_encode($user);

    }
        break;
    case "POST":
        if ($pathServer[3] === "consults") {

            if (isset($_FILES['image'])) {
                $base64URL = saveImage("uploads/");
            }
            else{
                $base64URL = "no image";
            }
            
            $consult = json_decode($_POST["_jsonData"]);
            $sql = "INSERT INTO consults(id, title, description, username, coder, image_path, created_at, updated_at) 
                    VALUES(null, :title, :description, :username, :coder, :image_path, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $created_at = date('Y-m-d H:i:s');
            $stmt->bindParam(':title', $consult->title);
            $stmt->bindParam(':description', $consult->description);
            $stmt->bindParam(':username', $consult->username);
            $stmt->bindParam(':coder', $consult->coder);
            $stmt->bindParam(':image_path', $base64URL);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':updated_at', $created_at);

            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Record created successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to create record.'];
            }
            echo json_encode($response);
        } else {
            if (isset($_FILES['image'])) {
                $base64URL = saveImage("uploads/users/profilePictures/");
            }
            else{
                $base64URL = "no image";
            }
            $user = json_decode($_POST["_jsonData"]);
            $sql = "INSERT INTO users (username, email, password, profile_picture_path, created_at, updated_at) 
                    VALUES(:username, :email, :password, :profile_picture_path, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $created_at = date('Y-m-d H:i:s');
             // The plain text password to be hashed
            $plaintext_password =  $user->password;
            
            // The hash of the password that
            // can be stored in the database
            $hash = password_hash($plaintext_password, PASSWORD_DEFAULT);

            $stmt->bindParam(':username', $user->user);
            $stmt->bindParam(':email', $user->email);
            $stmt->bindParam(':password', $hash);
            $stmt->bindParam(':profile_picture_path', $base64URL);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':updated_at', $created_at);

            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Record created successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to create record.'];
            }
            echo json_encode($response);
        }

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
        $path = explode('/', $_SERVER['REQUEST_URI']);

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $path[4]);

        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
        } else {
            $response = ['status' => 0, 'message' => 'Failed to delete record.'];
        }
        echo json_encode($response);
        break;
}

function saveImage ($upload_dir){
     

        $file_name = $_FILES["image"]["name"];
        $file_tmp_name = $_FILES["image"]["tmp_name"];
        $error = $_FILES["image"]["error"];
    
        if($error > 0){
            return $response = [
                "status" => "error",
                "error" => true,
                "message" => "Error uploading the file!"
            ];
        }else{
            $random_name = rand(1000,1000000)."-".$file_name;
            $upload_name = $upload_dir.strtolower($random_name);
            $upload_name = preg_replace('/\s+/', '-', $upload_name);
            if(move_uploaded_file($file_tmp_name , $upload_name)) {

                // Convert uploaded file into Base64
                $path = './'.$upload_name;
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $base64URL = 'data:image/' . $type . ';base64,' . base64_encode($data);

                return $base64URL
                ;
            }else
            {
                return $response = [
                    "status" => "danger",
                    "error" => true,
                    "url" =>  $file_name,
                    "message" => "Error uploading the file!"
                ];
            }
        }
}
