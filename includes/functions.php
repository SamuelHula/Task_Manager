<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Get all tasks from database
 */
function getAllTasks() {
    $conn = getConnection();
    $tasks = [];
    
    $sql = "SELECT * FROM tasks ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
    }
    
    $conn->close();
    return $tasks;
}

/**
 * Get a single task by ID
 */
function getTaskById($id) {
   $conn = getConnection();
   
   // Prevent SQL injection
   $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
   $stmt->bind_param("i", $id);
   $stmt->execute();
   $result = $stmt->get_result();
   
   $task = $result->fetch_assoc();
   
   $stmt->close();
   $conn->close();
   
   return $task;
}

/**
 * Create a new task
 */
function createTask($data) {
   $conn = getConnection();
   
   $stmt = $conn->prepare("INSERT INTO tasks (title, description, status, due_date) VALUES (?, ?, ?, ?)");
   $stmt->bind_param("ssss", $data['title'], $data['description'], $data['status'], $data['due_date']);
   
   $success = $stmt->execute();
   
   $stmt->close();
   $conn->close();
   
   return $success;
}

/**
 * Update an existing task
 */
function updateTask($id, $data) {
   $conn = getConnection();
   
   $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, due_date = ? WHERE id = ?");
   $stmt->bind_param("ssssi", $data['title'], $data['description'], $data['status'], $data['due_date'], $id);
   
   $success = $stmt->execute();
   
   $stmt->close();
   $conn->close();
   
   return $success;
}

/**
 * Delete a task
 */
function deleteTask($id) {
   $conn = getConnection();
   
   $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
   $stmt->bind_param("i", $id);
   
   $success = $stmt->execute();
   
   $stmt->close();
   $conn->close();
   
   return $success;
}

/**
 * Get task statistics
 */
function getTaskStats() {
   $conn = getConnection();
   $stats = [];
   
   $sql = "SELECT 
               COUNT(*) as total,
               SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
               SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
               SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
         FROM tasks";
   
   $result = $conn->query($sql);
   $stats = $result->fetch_assoc();
   
   $conn->close();
   return $stats;
}
?>