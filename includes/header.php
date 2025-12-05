<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Task Manager - PHP CRUD Application</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
   <style>
      .task-card {
         transition: transform 0.2s;
      }
      .task-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }
      .status-badge {
         font-size: 0.75em;
      }
      .completed {
         opacity: 0.8;
         background-color: #f8f9fa;
      }
      .character-count {
         font-size: 0.8rem;
      }
      .progress {
         background-color: #e9ecef;
      }
      .was-validated .form-control:valid {
         border-color: #198754;
         padding-right: calc(1.5em + 0.75rem);
         background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
         background-repeat: no-repeat;
         background-position: right calc(0.375em + 0.1875rem) center;
         background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
      }
      .was-validated .form-control:invalid {
         border-color: #dc3545;
         padding-right: calc(1.5em + 0.75rem);
         background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
         background-repeat: no-repeat;
         background-position: right calc(0.375em + 0.1875rem) center;
         background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
      }
   </style>
</head>
<body>
   <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
      <div class="container">
         <a class="navbar-brand" href="index.php">
               <i class="bi bi-check2-circle"></i> Task Manager
         </a>
         <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
               <span class="navbar-toggler-icon"></span>
         </button>
         <div class="collapse navbar-collapse" id="navbarNav">
               <ul class="navbar-nav ms-auto">
                  <li class="nav-item">
                     <a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a>
                  </li>
                  <li class="nav-item">
                     <a class="nav-link" href="create.php"><i class="bi bi-plus-circle"></i> Add Task</a>
                  </li>
               </ul>
         </div>
      </div>
   </nav>
   <div class="container">