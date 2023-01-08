<?php
namespace Api;
use \PDO;

include './Statement.php';
include './GetInterface.php';
include './PostInterface.php';

use \Api\Statement;
use \Api\GetInterface;
use \Api\PostInterface;

	class PostConsultStatement extends Statement implements PostInterface{
        private $data;
        private $image;

		public function __construct($db, $sql, $path, $data, $image){
			Parent::__construct($db, $sql, $path);
            $this->data = $data;
            $this->image = $image;
		}

        public function post(){

        }

        public function saveImage()
        {
            
        }
        
	}
?>