// Project: PHP & MySQL Blog Management System (Task 5)
// File: assets/js/script.js
// Description: Client-side interactive confirmations and validations.

document.addEventListener('DOMContentLoaded', function() {
    
    // Log setup confirmation
    console.log("Task 5 Blog Management JS system initialized.");

    // 1. Delete Post Confirmation
    const deletePostButtons = document.querySelectorAll('.btn-delete-confirm');
    deletePostButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            const confirmation = confirm("Warning: Are you sure you want to permanently delete this blog post?");
            if (!confirmation) {
                event.preventDefault();
            }
        });
    });

    // 2. Delete User Account Confirmation (Admin only)
    const deleteUserButtons = document.querySelectorAll('.btn-delete-user-confirm');
    deleteUserButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            const username = this.getAttribute('data-username') || 'this user';
            const confirmation = confirm("CRITICAL WARNING: Are you sure you want to permanently delete the user '" + username + "'? All of their published posts will be deleted as well. This action CANNOT be undone!");
            if (!confirmation) {
                event.preventDefault();
            }
        });
    });

});
