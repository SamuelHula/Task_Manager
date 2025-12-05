<?php
require_once 'includes/functions.php';
require_once 'includes/validation.php';

if (!isset($_GET['id'])) {
   header('Location: index.php');
   exit;
}

$id = $_GET['id'];
$task = getTaskById($id);

if (!$task) {
   header('Location: index.php');
   exit;
}

$message = '';
$messageType = '';
$validationErrors = [];

// Initialize form data with task data
$formData = [
   'title' => htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'),
   'description' => htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'),
   'status' => htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8'),
   'due_date' => $task['due_date']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   // Validate the form
   $validation = validateTaskForm($_POST);
   
   if ($validation['isValid']) {
      // Prepare data for database
      $data = [
         'title' => $validation['results']['title']['value'],
         'description' => $validation['results']['description']['value'],
         'status' => $validation['results']['status']['value'],
         'due_date' => $validation['results']['due_date']['value']
      ];
      
      if (updateTask($id, $data)) {
         $message = 'Task updated successfully!';
         $messageType = 'success';
         
         // Update form data with new values
         $formData = sanitizeFormInput($_POST);
         
         // Refresh task data from database
         $task = getTaskById($id);
      } else {
         $message = 'Error updating task. Please try again.';
         $messageType = 'danger';
         $formData = sanitizeFormInput($_POST);
      }
   } else {
      $message = 'Please correct the errors below.';
      $messageType = 'danger';
      $validationErrors = $validation['errors'];
      $formData = sanitizeFormInput($_POST);
   }
}

require_once 'includes/header.php';
?>

<h1 class="mb-4">Edit Task</h1>

<?php if ($message): ?>
   <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
      <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (!empty($validationErrors)): ?>
   <?php echo displayValidationErrors($validationErrors); ?>
<?php endif; ?>

<form method="POST" action="edit.php?id=<?php echo $id; ?>" id="taskForm" class="needs-validation" novalidate>
   <div class="mb-3">
      <label for="title" class="form-label">
         Task Title <span class="text-danger">*</span>
         <small class="text-muted">(3-100 characters)</small>
      </label>
      <input type="text" 
            class="form-control <?php echo getFieldClass('title', $validationErrors); ?>" 
            id="title" 
            name="title" 
            value="<?php echo $formData['title']; ?>"
            required
            minlength="3"
            maxlength="100"
            pattern="^[a-zA-Z0-9\s\-\.,!?']+$"
            oninput="updateCharacterCount('title', 100)">
      <div class="invalid-feedback">
         Please enter a valid title (3-100 characters, only letters, numbers, spaces, and basic punctuation).
      </div>
      <?php echo showCharacterCount($formData['title'], 100, 'title'); ?>
   </div>
   
   <div class="mb-3">
      <label for="description" class="form-label">
         Description
         <small class="text-muted">(Max 500 characters)</small>
      </label>
      <textarea class="form-control <?php echo getFieldClass('description', $validationErrors); ?>" 
               id="description" 
               name="description" 
               rows="3"
               maxlength="500"
               oninput="updateCharacterCount('description', 500)"><?php 
         echo str_replace('<br />', '', $formData['description']); 
      ?></textarea>
      <div class="invalid-feedback">
         Description cannot exceed 500 characters and should not contain harmful content.
      </div>
      <?php 
      $plainDescription = str_replace('<br />', '', $formData['description']);
      echo showCharacterCount($plainDescription, 500, 'description'); 
      ?>
   </div>
   
   <div class="row mb-3">
      <div class="col-md-6">
         <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
         <select class="form-select <?php echo getFieldClass('status', $validationErrors); ?>" 
                  id="status" 
                  name="status" 
                  required>
               <option value="pending" <?php echo $formData['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
               <option value="in_progress" <?php echo $formData['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
               <option value="completed" <?php echo $formData['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
         </select>
         <div class="invalid-feedback">
               Please select a valid status.
         </div>
      </div>
      
      <div class="col-md-6">
         <label for="due_date" class="form-label">Due Date</label>
         <input type="date" 
                  class="form-control <?php echo getFieldClass('due_date', $validationErrors); ?>" 
                  id="due_date" 
                  name="due_date"
                  value="<?php echo $formData['due_date']; ?>">
         <div class="invalid-feedback">
               Please enter a valid date (YYYY-MM-DD format).
         </div>
         <small class="text-muted">Leave empty if no due date</small>
      </div>
   </div>
   
   <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
         <i class="bi bi-save"></i> Update Task
      </button>
      <button type="reset" class="btn btn-outline-secondary" onclick="resetForm()">
         <i class="bi bi-arrow-clockwise"></i> Reset to Original
      </button>
      <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
   </div>
</form>

<hr class="my-4">

<div class="text-muted">
   <small>
      <strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($task['created_at'])); ?><br>
      <strong>Last Updated:</strong> <?php echo date('M d, Y H:i', strtotime($task['updated_at'])); ?>
   </small>
</div>

<script>
// Character count update
function updateCharacterCount(fieldId, maxLength) {
   const field = document.getElementById(fieldId);
   const countElement = document.getElementById(fieldId + '-count');
   const progressBar = countElement?.nextElementSibling?.querySelector('.progress-bar');
   
   if (field && countElement) {
      const currentLength = field.value.length;
      const percentage = (currentLength / maxLength) * 100;
      
      countElement.textContent = `${currentLength} / ${maxLength} characters`;
      
      // Update color based on percentage
      if (percentage > 95) {
         countElement.className = 'text-danger';
         if (progressBar) progressBar.className = 'progress-bar bg-danger';
      } else if (percentage > 80) {
         countElement.className = 'text-warning';
         if (progressBar) progressBar.className = 'progress-bar bg-warning';
      } else {
         countElement.className = 'text-success';
         if (progressBar) progressBar.className = 'progress-bar bg-success';
      }
      
      if (progressBar) {
         progressBar.style.width = `${Math.min(percentage, 100)}%`;
      }
   }
}

// Form validation
(function() {
   'use strict';
   
   const forms = document.querySelectorAll('.needs-validation');
   
   Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
         if (!form.checkValidity()) {
               event.preventDefault();
               event.stopPropagation();
         }
         
         validateTitlePattern(form);
         validateDatePattern(form);
         
         form.classList.add('was-validated');
      }, false);
   });
   
   const titleField = document.getElementById('title');
   if (titleField) {
      titleField.addEventListener('input', function() {
         const pattern = /^[a-zA-Z0-9\s\-\.,!?']+$/;
         if (!pattern.test(this.value) && this.value.length > 0) {
               this.setCustomValidity('Only letters, numbers, spaces, and basic punctuation are allowed.');
         } else {
               this.setCustomValidity('');
         }
      });
   }
   
   const dateField = document.getElementById('due_date');
   if (dateField) {
      dateField.addEventListener('change', function() {
         if (this.value) {
               const datePattern = /^\d{4}-\d{2}-\d{2}$/;
               if (!datePattern.test(this.value)) {
                  this.setCustomValidity('Please use YYYY-MM-DD format.');
               } else {
                  this.setCustomValidity('');
               }
         }
      });
   }
   
   // Initialize character counts
   updateCharacterCount('title', 100);
   updateCharacterCount('description', 500);
})();

function validateTitlePattern(form) {
   const titleField = form.querySelector('#title');
   if (titleField) {
      const pattern = /^[a-zA-Z0-9\s\-\.,!?']+$/;
      if (!pattern.test(titleField.value) && titleField.value.length > 0) {
         titleField.setCustomValidity('Only letters, numbers, spaces, and basic punctuation are allowed.');
      }
   }
}

function validateDatePattern(form) {
   const dateField = form.querySelector('#due_date');
   if (dateField && dateField.value) {
      const datePattern = /^\d{4}-\d{2}-\d{2}$/;
      if (!datePattern.test(dateField.value)) {
         dateField.setCustomValidity('Please use YYYY-MM-DD format.');
      }
   }
}

// Reset form to original values
function resetForm() {
   const form = document.getElementById('taskForm');
   if (form) {
      form.classList.remove('was-validated');
      
      // Reset to original task values
      form.querySelector('#title').value = '<?php echo addslashes(htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8')); ?>';
      form.querySelector('#description').value = '<?php echo addslashes(str_replace('<br />', '', htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'))); ?>';
      form.querySelector('#status').value = '<?php echo $task['status']; ?>';
      form.querySelector('#due_date').value = '<?php echo $task['due_date']; ?>';
      
      // Update character counts
      updateCharacterCount('title', 100);
      updateCharacterCount('description', 500);
   }
}

// Prevent harmful content in description
document.getElementById('description')?.addEventListener('input', function(e) {
   const harmfulPatterns = [
      /<script.*?>.*?<\/script>/gi,
      /onload\s*=/gi,
      /onerror\s*=/gi,
      /javascript:/gi
   ];
   
   harmfulPatterns.forEach(pattern => {
      this.value = this.value.replace(pattern, '');
   });
});
</script>

<?php require_once 'includes/footer.php'; ?>