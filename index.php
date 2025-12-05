<?php
require_once 'includes/functions.php';

$tasks = getAllTasks();
$stats = getTaskStats();

require_once 'includes/header.php';
?>

<h1 class="mb-4">Task Manager</h1>

<!-- Statistics Cards -->
<div class="row mb-4">
   <div class="col-md-3">
      <div class="card text-white bg-primary">
         <div class="card-body">
               <h5 class="card-title">Total Tasks</h5>
               <h2 class="display-4"><?php echo $stats['total']; ?></h2>
         </div>
      </div>
   </div>
   <div class="col-md-3">
      <div class="card text-white bg-warning">
         <div class="card-body">
               <h5 class="card-title">Pending</h5>
               <h2 class="display-4"><?php echo $stats['pending']; ?></h2>
         </div>
      </div>
   </div>
   <div class="col-md-3">
      <div class="card text-white bg-info">
         <div class="card-body">
               <h5 class="card-title">In Progress</h5>
               <h2 class="display-4"><?php echo $stats['in_progress']; ?></h2>
         </div>
      </div>
   </div>
   <div class="col-md-3">
      <div class="card text-white bg-success">
         <div class="card-body">
               <h5 class="card-title">Completed</h5>
               <h2 class="display-4"><?php echo $stats['completed']; ?></h2>
         </div>
      </div>
   </div>
</div>

<!-- Add Task Button -->
<div class="mb-4">
   <a href="create.php" class="btn btn-success">
      <i class="bi bi-plus-circle"></i> Add New Task
   </a>
</div>

<!-- Tasks List -->
<?php if (empty($tasks)): ?>
   <div class="alert alert-info">
      <h4 class="alert-heading">No tasks found!</h4>
      <p>You don't have any tasks yet. Click the "Add New Task" button to create your first task.</p>
   </div>
<?php else: ?>
   <div class="row">
      <?php foreach ($tasks as $task): ?>
         <?php 
         $statusClass = '';
         switch ($task['status']) {
               case 'pending':
                  $statusClass = 'bg-warning';
                  break;
               case 'in_progress':
                  $statusClass = 'bg-info';
                  break;
               case 'completed':
                  $statusClass = 'bg-success';
                  break;
         }
         ?>
         <div class="col-md-4 mb-4">
               <div class="card task-card <?php echo $task['status'] == 'completed' ? 'completed' : ''; ?>">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-start mb-2">
                           <h5 class="card-title"><?php echo htmlspecialchars($task['title']); ?></h5>
                           <span class="badge <?php echo $statusClass; ?> status-badge">
                              <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                           </span>
                     </div>
                     
                     <p class="card-text"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                     
                     <div class="d-flex justify-content-between align-items-center mt-3">
                           <small class="text-muted due-date"><?php echo $task['due_date']; ?></small>
                           <div>
                              <a href="edit.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-outline-primary">
                                 <i class="bi bi-pencil"></i>
                              </a>
                              <button onclick="confirmDelete(<?php echo $task['id']; ?>, '<?php echo addslashes($task['title']); ?>')" 
                                       class="btn btn-sm btn-outline-danger">
                                 <i class="bi bi-trash"></i>
                              </button>
                           </div>
                     </div>
                  </div>
               </div>
         </div>
      <?php endforeach; ?>
   </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>