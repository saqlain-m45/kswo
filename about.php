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
                    <div class="row justify-content-center g-4">
                        <div class="col-md-5">
                            <h4 class="display-5 text-white fw-bold mb-2">200+</h4>
                            <p class="text-white opacity-75 mb-0">Scholarships Awarded</p>
                        </div>
                        <div class="col-md-5">
                            <h4 class="display-5 text-white fw-bold mb-2">24/7</h4>
                            <p class="text-white opacity-75 mb-0">Support System</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- About Founder Section -->
        <div class="row mt-5 pt-4 reveal justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-premium overflow-hidden" style="border-radius: 30px;">
                    <div class="row g-0">
                        <div class="col-md-5 bg-light d-flex align-items-center justify-content-center p-4"
                            style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <!-- Placeholder for founder image - using a clean CSS circle for now, can be replaced with actual image -->
                            <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                style="width: 220px; height: 220px; background: var(--primary-gradient); border: 8px solid white;">
                                <i class="fas fa-user-tie fa-5x text-white opacity-75"></i>
                            </div>
                        </div>
                        <div class="col-md-7 p-5">
                            <h6 class="text-primary fw-bold text-uppercase small letter-spacing-1 mb-2">The Visionary
                            </h6>
                            <h3 class="mb-4 fw-bold">About Our <span class="gradient-text">Founder</span></h3>
                            <p class="text-muted mb-4" style="line-height: 1.8;">
                                Our founder established KSWO with a profound commitment to the educational advancement
                                of the Khattak community. Recognizing the untapped potential within our youth and the
                                hurdles they face, the foundation was laid to create a sustainable support network.
                            </p>
                            <p class="text-muted mb-0" style="line-height: 1.8;">
                                Through visionary leadership and relentless dedication, what started as a noble thought
                                has blossomed into a reality that empowers hundreds of students today, ensuring that
                                financial constraints never extinguish the light of knowledge.
                            </p>
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