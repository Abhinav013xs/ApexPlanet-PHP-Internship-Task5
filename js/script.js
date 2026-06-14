// Project: PHP & MySQL Blog Management System (Task 2)
// Author: Web Development Intern
// Description: Client-side interactive confirmations and validations.

document.addEventListener('DOMContentLoaded', function() {
    
    // Log execution to console
    console.log("Task 2 Blog Management JS loaded.");

    // Delete Confirmation dialog
    // Find all delete buttons in the dashboard table or pages
    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');

    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Display a pop-up confirmation box
            const confirmation = confirm("Warning: Are you sure you want to permanently delete this blog post?");
            
            // If the user clicks "Cancel", prevent the browser from proceeding to the delete page
            if (!confirmation) {
                event.preventDefault();
            }
        });
    });

});
