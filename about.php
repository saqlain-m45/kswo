<?php include 'includes/header.php'; ?>

<section class="py-5 mt-5">
    <div class="container mt-5">
        <div class="row mb-5 reveal">
            <div class="col-lg-8">
                <h1 class="display-4"><span class="gradient-text">About</span> KSWO</h1>
                <p class="lead text-muted">A legacy of service, a future of empowerment.</p>
            </div>
        </div>

        <div class="row g-5 reveal">
            <div class="col-md-6">
                <h3 class="mb-4">Our <span class="gradient-text">History</span></h3>
                <p>The Khattak Student Welfare Organization (KSWO) was founded with a singular vision: to ensure that no
                    student in the Khattak community is left behind due to a lack of resources. Over the years, we have
                    grown from a small group of volunteers to a structured organization supporting hundreds of members.
                </p>
                <p>Our journey has been defined by the resilience of our students and the generosity of our donors. We
                    have organized countless workshops, provided financial aid, and created a networking platform for
                    Khattak students across the country.</p>
            </div>
            <div class="col-md-6">
                <div class="card p-5 border-0 shadow-premium h-100" style="background: #fff; border-radius: 24px;">
                    <h3 class="mb-4">Mission & <span class="gradient-text">Vision</span></h3>
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold text-uppercase small letter-spacing-1">Mission</h6>
                        <p>To provide academic, financial, and moral support to Khattak students,
                            fostering a community of excellence and mutual aid.</p>
                    </div>
                    <div>
                        <h6 class="text-primary fw-bold text-uppercase small letter-spacing-1">Vision</h6>
                        <p>To become a leading educational welfare organization that empowers the
                            youth of the Khattak tribe to lead in every field of life.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5 reveal">
            <div class="col-12">
                <div class="p-5 rounded-4 shadow-premium text-center"
                    style="background: var(--primary-gradient); border-radius: 30px !important;">
                    <h2 class="mb-5 text-white">Our Community Impact</h2>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h4 class="display-5 text-white fw-bold mb-2">200+</h4>
                            <p class="text-white opacity-75 mb-0">Scholarships Awarded</p>
                        </div>
                        <div class="col-md-4">
                            <h4 class="display-5 text-white fw-bold mb-2">15+</h4>
                            <p class="text-white opacity-75 mb-0">Cities Covered</p>
                        </div>
                        <div class="col-md-4">
                            <h4 class="display-5 text-white fw-bold mb-2">24/7</h4>
                            <p class="text-white opacity-75 mb-0">Support System</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reveals = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 100) {
                    el.classList.add('active');
                }
            });
        };
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();
    });
</script>

<?php include 'includes/footer.php'; ?>