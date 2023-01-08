<?php
namespace Api;
      abstract class Statement {
            protected $db;
            protected $sql;
            protected $path;

            public function __construct($db, $sql, $path){
                  $this->db = $db;
                  $this->sql = $sql;
                  $this->path = $path;
            }
      }
?>