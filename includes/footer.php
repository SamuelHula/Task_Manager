   </div>
   
   <footer class="mt-5 py-3 bg-light text-center">
      <div class="container">
         <p class="mb-0">Task Manager &copy; <?php echo date('Y'); ?> - Simple PHP CRUD Application</p>
         <small class="text-muted">Connected to MySQL Database</small>
      </div>
   </footer>
   
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
   <script>
      // Confirm delete action
      function confirmDelete(taskId, taskTitle) {
         if (confirm(`Are you sure you want to delete "${taskTitle}"?`)) {
               window.location.href = `delete.php?id=${taskId}`;
         }
      }
      
      // Format dates
      document.addEventListener('DOMContentLoaded', function() {
         const dueDates = document.querySelectorAll('.due-date');
         dueDates.forEach(element => {
               const date = new Date(element.textContent);
               element.textContent = date.toLocaleDateString('en-US', {
                  year: 'numeric',
                  month: 'short',
                  day: 'numeric'
               });
               
               // Add warning for overdue tasks
               if (date < new Date() && !element.closest('.task-card').classList.contains('completed')) {
                  element.closest('.task-card').classList.add('border-danger');
                  element.innerHTML += ' <span class="badge bg-danger">Overdue</span>';
               }
         });
      });
   </script>
</body>
</html>