<?php
require_once 'includes/functions.php';

if (isset($_GET['id'])) {
   $id = $_GET['id'];
   
   // Optional: Get task info before deleting for logging
   $task = getTaskById($id);
   
   if ($task && deleteTask($id)) {
      header('Location: index.php?message=Task deleted successfully&type=success');
   } else {
      header('Location: index.php?message=Error deleting task&type=danger');
   }
} else {
   header('Location: index.php');
}
exit;
?>