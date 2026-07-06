<?php
// Project: PHP & MySQL Blog Management System (Task 5)
// File: includes/footer.php
// Description: Unified HTML footer component containing scripting files.

$base_path = $base_path ?? "./";
?>
    <!-- Footer Section -->
    <footer class="bg-dark text-muted py-4 mt-auto border-top border-primary border-4">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start mb-2 mb-md-0">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <span class="text-white fw-bold">BlogSystem</span>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Final Internship Project | Intern: <span class="text-white fw-bold">Abhinav</span></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript Confirms & Utilities -->
    <script src="<?php echo $base_path; ?>assets/js/script.js"></script>
</body>
</html>
