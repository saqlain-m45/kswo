<footer class="site-footer">
    <div class="container pb-5">
        <div class="row g-5">
            <div class="col-lg-4">
                <div class="mb-4">
                    <img src="assets/images/logo.png" alt="KSWO Logo" style="height: 50px; width: auto;">
                </div>
                <p class="text-muted" style="color: rgba(255,255,255,0.5) !important;">Khattak Student Welfare
                    Organization is dedicated to empowering students and
                    serving the community through education and welfare initiatives. Join us in making a difference.</p>
            </div>
            <div class="col-md-4 col-lg-2 ms-auto">
                <h5 class="footer-title">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="transparency.php">Transparency</a></li>
                    <li><a href="register.php">Join Us</a></li>
                </ul>
            </div>
            <div class="col-md-6 col-lg-4">
                <h5 class="footer-title">Contact Us</h5>
                <p class="text-muted" style="color: rgba(255,255,255,0.5) !important;">
                    <span class="d-block mb-2">Email: info@kswo.org</span>
                    <span class="d-block mb-2">Phone: +92 300 1234567</span>
                    <span class="d-block">Location: Kohat, KPK, Pakistan</span>
                </p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0">&copy;
                        <?php echo date('Y'); ?> KSWO. All Rights Reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Developed by <a href="https://nexsoft.site" target="_blank"
                            class="dev-link">Nexsoft</a></p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="assets/js/script.js"></script>

<!-- Global Scroll Reveal Logic -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reveals = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                const elementVisible = 100;
                if (elementTop < windowHeight - elementVisible) {
                    el.classList.add('active');
                }
            });
        };
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll(); // Trigger once on load
    });
</script>

</body>

</html>