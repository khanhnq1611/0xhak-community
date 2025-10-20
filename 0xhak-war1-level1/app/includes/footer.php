    </div>

    <footer class="footer mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> 0xHAK Community. All rights reserved.</p>
            <div class="mt-2">
                <a href="#" class="text-white me-3"><i class="fab fa-github fa-lg"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                <a href="#" class="text-white me-3"><i class="fab fa-discord fa-lg"></i></a>
                <a href="#" class="text-white"><i class="fas fa-envelope fa-lg"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Handle file input change
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('.custom-file-input');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function(e) {
                    const fileName = e.target.files[0]?.name || 'Choose file';
                    const label = this.nextElementSibling;
                    label.textContent = fileName;
                });
            });
        });
    </script>
</body>
</html>
