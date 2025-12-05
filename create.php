<?php
require_once 'includes/functions.php';
require_once 'includes/validation.php';

$message = '';
$messageType = '';
$formData = [
   'title' => '',
   'description' => '',
   'status' => 'pending',
   'due_date' => date('Y-m-d')
];
$validationErrors = [];

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
      
      if (createTask($data)) {
         $message = 'Task created successfully!';
         $messageType = 'success';
         
         // Clear form data
         $formData = [
               'title' => '',
               'description' => '',
               'status' => 'pending',
               'due_date' => date('Y-m-d')
         ];
      } else {
         $message = 'Error creating task. Please try again.';
         $messageType = 'danger';
         
         // Keep form data
         $formData = sanitizeFormInput($_POST);
      }
   } else {
      $message = 'Please correct the errors below.';
      $messageType = 'danger';
      $validationErrors = $validation['errors'];
      
      // Keep form data with original values
      $formData = sanitizeFormInput($_POST);
   }
}

require_once 'includes/header.php';
?>

<h1 class="mb-4">Create New Task</h1>

<?php if ($message): ?>
   <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
      <?php echo $message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
   </div>
<?php endif; ?>

<?php if (!empty($validationErrors)): ?>
   <?php echo displayValidationErrors($validationErrors); ?>
<?php endif; ?>

<form method="POST" action="create.php" id="taskForm" class="needs-validation" novalidate>
   <div class="mb-3">
      <label for="title" class="form-label">
         Task Title <span class="text-danger">*</span>
         <small class="text-muted">(3-100 characters)</small>
      </label>
      <input type="text" 
            class="form-control <?php echo getFieldClass('title', $validationErrors); ?>" 
            id="title" 
            name="title" 
            value="<?php echo htmlspecialchars($formData['title']); ?>"
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
               oninput="updateCharacterCount('description', 500)"
               placeholder="Enter task description"><?php echo htmlspecialchars($formData['description']); ?></textarea>
      <div class="invalid-feedback">
         Description cannot exceed 500 characters and should not contain harmful content.
      </div>
      <?php echo showCharacterCount($formData['description'], 500, 'description'); ?>
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
                  value="<?php echo $formData['due_date']; ?>"
                  min="<?php echo date('Y-m-d'); ?>">
         <div class="invalid-feedback">
               Please enter a valid future date (YYYY-MM-DD format).
         </div>
         <small class="text-muted">Leave empty if no due date</small>
      </div>
   </div>
   
   <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary">
         <i class="bi bi-save"></i> Create Task
      </button>
      <button type="reset" class="btn btn-outline-secondary" onclick="resetForm()">
         <i class="bi bi-arrow-clockwise"></i> Reset
      </button>
      <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
   </div>
</form>

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
   
   // Fetch all forms we want to apply custom Bootstrap validation styles to
   const forms = document.querySelectorAll('.needs-validation');
   
   // Loop over them and prevent submission
   Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
         if (!form.checkValidity()) {
               event.preventDefault();
               event.stopPropagation();
         }
         
         // Additional custom validation
         validateTitlePattern(form);
         validateFutureDate(form);
         
         form.classList.add('was-validated');
      }, false);
   });
   
   // Real-time validation
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
               const selectedDate = new Date(this.value);
               const today = new Date();
               today.setHours(0, 0, 0, 0);
               
               if (selectedDate < today) {
                  this.setCustomValidity('Due date cannot be in the past.');
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

// Additional validation functions
function validateTitlePattern(form) {
   const titleField = form.querySelector('#title');
   if (titleField) {
      const pattern = /^[a-zA-Z0-9\s\-\.,!?']+$/;
      if (!pattern.test(titleField.value) && titleField.value.length > 0) {
         titleField.setCustomValidity('Only letters, numbers, spaces, and basic punctuation are allowed.');
      }
   }
}

function validateFutureDate(form) {
   const dateField = form.querySelector('#due_date');
   if (dateField && dateField.value) {
      const selectedDate = new Date(dateField.value);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      
      if (selectedDate < today) {
         dateField.setCustomValidity('Due date cannot be in the past.');
      }
   }
}

// Form reset function
function resetForm() {
   const form = document.getElementById('taskForm');
   if (form) {
      form.classList.remove('was-validated');
      form.reset();
      
      // Reset character counts
      updateCharacterCount('title', 100);
      updateCharacterCount('description', 500);
      
      // Set default date to today
      const dateField = document.getElementById('due_date');
      if (dateField) {
         dateField.value = '<?php echo date("Y-m-d"); ?>';
      }
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