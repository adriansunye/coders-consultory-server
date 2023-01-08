<?php
namespace Api;
use \PDO;

include './Statement.php';
include './GetInterface.php';
include './PostInterface.php';

use \Api\Statement;
use \Api\GetInterface;
use \Api\PostInterface;

	class ConsultStatement extends Statement implements GetInterface{

		public function __construct($db, $sql, $path){
			Parent::__construct($db, $sql, $path);
		}

        public function fetch(){
            $conn = $this->db->connect();
            $stmt = $conn->prepare($this->sql);
            $stmt->bindParam(':id', $this->path[4]);
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