<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); 
define('DB_PASS', '');
define('DB_NAME', 'task_manager');

function getConnection() {
   try {
      $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
      
      // Check connection
      if ($conn->connect_error) {
         throw new Exception("Connection failed: " . $conn->connect_error);
      }
      
      return $conn;
   } catch (Exception $e) {
      die("Database connection error: " . $e->getMessage());
   }
}
?>