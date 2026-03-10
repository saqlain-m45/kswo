<?php include 'includes/header.php'; ?>

<section class="py-5 mt-5 bg-light-soft" style="min-height: 80vh;">
    <div class="container mt-5">
        <div class="row g-4 justify-content-center reveal">
            <?php
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$events = $stmt->fetchAll();

if (empty($events)): ?>
            <div class="col-md-4">
                <div class="card glass-card border-0 h-100 shadow-premium-sm text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-calendar-day text-primary opacity-50 display-4"></i>
                        </div>
                        <h5 class="fw-bold">No Events Scheduled</h5>
                        <p class="text-muted small">Check back soon for upcoming events and activities.</p>
                    </div>
                </div>
            </div>
            <?php
else: ?>
            <?php foreach ($events as $e): ?>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="event-card-premium reveal h-100">
                    <div class="card border-0 glass-card h-100 position-relative shadow-premium-sm overflow-hidden">
                        <div class="event-image-container">
                            <?php if ($e['image_path'] && file_exists($e['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($e['image_path']); ?>" class="event-banner"
                                alt="<?php echo htmlspecialchars($e['title']); ?>">
                            <?php
        else: ?>
                            <div class="event-placeholder d-flex align-items-center justify-content-center">
                                <i class="fas fa-calendar-alt text-muted display-4 opacity-50"></i>
                            </div>
                            <?php
        endif; ?>
                            <div class="date-chip">
                                <div class="day">
                                    <?php echo date('d', strtotime($e['event_date'])); ?>
                                </div>
                                <div class="month">
                                    <?php echo date('M', strtotime($e['event_date'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3 event-title">
                                <?php echo htmlspecialchars($e['title']); ?>
                            </h5>
                            <p class="description-text mb-0">
                                <?php echo htmlspecialchars($e['description']); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-0 px-4 pb-4">
                            <div class="d-flex align-items-center text-muted small">
                                <i class="far fa-clock me-2 text-primary"></i>
                                <?php echo date('Y', strtotime($e['event_date'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
    endforeach; ?>
            <?php
endif; ?>
        </div>
    </div>
</section>

<style>
    .bg-light-soft {
        background-color: #f8fbff;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 24px;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .event-card-premium:hover .glass-card {
        transform: translateY(-10px);
        box-shadow: 0 30px 60px rgba(0, 129, 255, 0.15) !important;
    }

    .event-image-container {
        height: 220px;
        position: relative;
        overflow: hidden;
        background: #f0f4f8;
    }

    .event-banner {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .event-card-premium:hover .event-banner {
        transform: scale(1.1);
    }

    .event-placeholder {
        height: 100%;
        width: 100%;
    }

    .date-chip {
        position: absolute;
        top: 20px;
        left: 20px;
        background: white;
        padding: 8px 15px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        z-index: 5;
    }

    .date-chip .day {
        font-size: 1.2rem;
        font-weight: 800;
        color: #2d3436;
        line-height: 1;
    }

    .date-chip .month {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #0081ff;
        letter-spacing: 1px;
    }

    .event-title {
        color: #2d3436;
        letter-spacing: -0.5px;
    }

    .description-text {
        font-size: 0.95rem;
        color: #636e72;
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .gradient-text {
        background: linear-gradient(135deg, #0081ff, #00d2ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reveals = document.querySelectorAll('.reveal');
        const revealOnScroll = () => {
            reveals.forEach(el => {
                const windowHeight = window.innerHeight;
                const elementTop = el.getBoundingClientRect().top;
                if (elementTop < windowHeight - 50) {
                    el.classList.add('active');
                }
            });
        };
        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll();
    });
</script>

<?php include 'includes/footer.php'; ?>