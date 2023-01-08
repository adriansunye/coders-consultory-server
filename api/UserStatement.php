<?php
namespace Api;
use \PDO;


include './GetInterface.php';



	class UserStatement extends Statement implements GetInterface{

		public function __construct($db, $sql, $path){
			Parent::__construct($db, $sql, $path);
		}

        public function fetch(){
            $conn = $this->db->connect();
            $stmt = $conn->prepare($this->sql);
            $stmt->bindParam(':username', $this->path[4]);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
		}

        public function fetchAll(){
            $conn = $this->db->connect();
            $stmt = $conn->prepare($this->sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
	}
?>