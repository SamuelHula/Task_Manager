<?php
/**
 * Validation functions for form inputs
 */

/**
 * Validate task title
 */
function validateTitle($title) {
   $errors = [];
   $title = trim($title);
   
   // Check if empty
   if (empty($title)) {
      $errors[] = "Title is required";
      return ['valid' => false, 'errors' => $errors, 'value' => $title];
   }
   
   // Check length
   $minLength = 3;
   $maxLength = 100;
   $length = mb_strlen($title);
   
   if ($length < $minLength) {
      $errors[] = "Title must be at least $minLength characters";
   }
   
   if ($length > $maxLength) {
      $errors[] = "Title must not exceed $maxLength characters";
   }
   
   // Check for invalid characters (only allow letters, numbers, spaces, and basic punctuation)
   if (!preg_match('/^[a-zA-Z0-9\s\-\.,!?\']+$/u', $title)) {
      $errors[] = "Title contains invalid characters. Only letters, numbers, spaces, and basic punctuation are allowed";
   }
   
   // Sanitize: Convert special characters to HTML entities and remove extra spaces
   $sanitized = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
   $sanitized = preg_replace('/\s+/', ' ', $sanitized); // Replace multiple spaces with single space
   
   return [
      'valid' => empty($errors),
      'errors' => $errors,
      'value' => $sanitized,
      'original' => $title
   ];
}

/**
 * Validate task description
 */
function validateDescription($description) {
   $errors = [];
   $description = trim($description);
   
   // Check length
   $maxLength = 500;
   $length = mb_strlen($description);
   
   if ($length > $maxLength) {
      $errors[] = "Description must not exceed $maxLength characters";
   }
   
   // Check for potentially harmful content (basic XSS prevention)
   $dangerousPatterns = [
      '/<script.*?>.*?<\/script>/is',
      '/onload\s*=/i',
      '/onerror\s*=/i',
      '/javascript:/i'
   ];
   
   foreach ($dangerousPatterns as $pattern) {
      if (preg_match($pattern, $description)) {
         $errors[] = "Description contains potentially harmful content";
         break;
      }
   }
   
   // Sanitize: Convert special characters to HTML entities
   $sanitized = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
   
   // Preserve line breaks
   $sanitized = nl2br($sanitized);
   
   return [
      'valid' => empty($errors),
      'errors' => $errors,
      'value' => $sanitized,
      'original' => $description
   ];
}

/**
 * Validate task status
 */
function validateStatus($status) {
   $errors = [];
   
   $allowedStatuses = ['pending', 'in_progress', 'completed'];
   
   if (!in_array($status, $allowedStatuses)) {
      $errors[] = "Invalid status selected";
   }
   
   return [
      'valid' => empty($errors),
      'errors' => $errors,
      'value' => $status
   ];
}

/**
 * Validate due date
 */
function validateDueDate($dueDate) {
   $errors = [];
   
   if (empty($dueDate)) {
      return [
         'valid' => true,
         'errors' => [],
         'value' => null
      ];
   }
   
   // Check date format
   if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
      $errors[] = "Invalid date format. Use YYYY-MM-DD";
   } else {
      // Check if date is valid
      $dateParts = explode('-', $dueDate);
      if (count($dateParts) !== 3 || !checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
         $errors[] = "Invalid date";
      } else {
         // Check if date is in the past (optional business rule)
         $currentDate = new DateTime();
         $inputDate = new DateTime($dueDate);
         
         if ($inputDate < $currentDate) {
               // Warning but not error - sometimes tasks can be overdue
               return [
                  'valid' => true,
                  'errors' => [],
                  'value' => $dueDate,
                  'warning' => 'Due date is in the past'
               ];
         }
      }
   }
   
   return [
      'valid' => empty($errors),
      'errors' => $errors,
      'value' => $dueDate
   ];
}

/**
 * Validate entire task form
 */
function validateTaskForm($data) {
   $validationResults = [];
   $allErrors = [];
   $isValid = true;
   
   // Validate each field
   $validationResults['title'] = validateTitle($data['title'] ?? '');
   $validationResults['description'] = validateDescription($data['description'] ?? '');
   $validationResults['status'] = validateStatus($data['status'] ?? '');
   $validationResults['due_date'] = validateDueDate($data['due_date'] ?? '');
   
   // Collect all errors
   foreach ($validationResults as $field => $result) {
      if (!$result['valid']) {
         $isValid = false;
         $allErrors[$field] = $result['errors'];
      }
   }
   
   return [
      'isValid' => $isValid,
      'results' => $validationResults,
      'errors' => $allErrors
   ];
}

/**
 * Sanitize all form inputs
 */
function sanitizeFormInput($input) {
   $sanitized = [];
   
   foreach ($input as $key => $value) {
      if (is_array($value)) {
         $sanitized[$key] = sanitizeFormInput($value);
      } else {
         // Remove whitespace
         $value = trim($value);
         
         // Convert special characters to HTML entities
         $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      }
   }
   
   return $sanitized;
}

/**
 * Display validation errors in a formatted way
 */
function displayValidationErrors($errors) {
   if (empty($errors)) {
      return '';
   }
   
   $html = '<div class="alert alert-danger">';
   $html .= '<h5><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h5>';
   $html .= '<ul class="mb-0">';
   
   foreach ($errors as $field => $fieldErrors) {
      $fieldName = ucfirst(str_replace('_', ' ', $field));
      foreach ($fieldErrors as $error) {
         $html .= "<li><strong>{$fieldName}:</strong> {$error}</li>";
      }
   }
   
   $html .= '</ul>';
   $html .= '</div>';
   
   return $html;
}

/**
 * Get field CSS class based on validation
 */
function getFieldClass($fieldName, $errors) {
   if (isset($errors[$fieldName])) {
      return 'is-invalid';
   }
   return '';
}

/**
 * Show character count for text inputs
 */
function showCharacterCount($text, $maxLength, $fieldId) {
   $currentLength = mb_strlen($text);
   $percentage = ($currentLength / $maxLength) * 100;
   
   $colorClass = 'text-success';
   if ($percentage > 80) {
      $colorClass = 'text-warning';
   }
   if ($percentage > 95) {
      $colorClass = 'text-danger';
   }
   
   return "
   <div class='character-count mt-1'>
      <small class='{$colorClass}' id='{$fieldId}-count'>
         {$currentLength} / {$maxLength} characters
      </small>
      <div class='progress' style='height: 3px;'>
         <div class='progress-bar {$colorClass}' 
               role='progressbar' 
               style='width: {$percentage}%'>
         </div>
      </div>
   </div>";
}
?>