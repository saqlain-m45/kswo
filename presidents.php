<?php include 'includes/header.php'; ?>

<section class="py-5 mt-5 bg-light-soft" style="min-height: 80vh;">
    <div class="container mt-5">
        <div class="text-center mb-5 reveal">


            <div class="row g-4 justify-content-center reveal">
                <?php
$stmt = $pdo->query("SELECT * FROM presidents ORDER BY is_current DESC, id DESC");
$presidents = $stmt->fetchAll();

if (empty($presidents)): ?>
                <!-- Placeholder if empty -->
                <div class="col-md-4">
                    <div class="card glass-card border-0 h-100">
                        <div class="card-body p-4 text-center">
                            <div class="placeholder-circle mb-4">
                                <i class="fas fa-user-tie text-white opacity-50 display-6"></i>
                            </div>
                            <h5 class="fw-bold">No Records Found</h5>
                            <p class="text-muted small">Leaders will appear here once added from the admin panel.</p>
                        </div>
                    </div>
                </div>
                <?php
else: ?>
                <?php foreach ($presidents as $p): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="president-card-premium reveal h-100">
                        <div class="card border-0 glass-card h-100 position-relative shadow-premium-sm">
                            <?php if ($p['is_current']): ?>
                            <div class="current-badge-modern">
                                <i class="fas fa-crown me-1"></i> Current
                            </div>
                            <?php
        endif; ?>

                            <div class="portrait-container">
                                <?php
        // Use absolute web path for <img> and local path for file_exists
        $has_photo = false;
        if (!empty($p['photo_path'])) {
            if (file_exists($p['photo_path'])) {
                $has_photo = true;
            }
        }
?>

                                <?php if ($has_photo): ?>
                                <img src="<?php echo htmlspecialchars($p['photo_path']); ?>" class="president-portrait"
                                    alt="<?php echo htmlspecialchars($p['name']); ?>">
                                <?php
        else: ?>
                                <div class="portrait-placeholder">
                                    <i class="fas fa-user-circle text-muted display-4"></i>
                                </div>
                                <?php
        endif; ?>
                            </div>

                            <div class="card-body text-center pt-0 px-3 pb-4">
                                <h6 class="fw-bold mb-1 mt-3 president-name">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </h6>
                                <div class="duration-tag mb-3">
                                    <?php echo htmlspecialchars($p['duration']); ?>
                                </div>
                                <p class="description-small mb-0">
                                    <?php echo htmlspecialchars($p['description']); ?>
                                </p>
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
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        transition: all 0.3s ease;
    }

    .president-card-premium {
        perspective: 1000px;
    }

    .president-card-premium:hover .glass-card {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 129, 255, 0.12) !important;
    }

    .portrait-container {
        height: 180px;
        margin: 20px;
        border-radius: 15px;
        overflow: hidden;
        background: #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .president-portrait {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .president-card-premium:hover .president-portrait {
        transform: scale(1.1);
    }

    .portrait-placeholder {
        opacity: 0.5;
    }

    .president-name {
        color: #2d3436;
        letter-spacing: -0.5px;
        font-size: 1rem;
    }

    .duration-tag {
        font-size: 0.7rem;
        font-weight: 700;
        color: #0081ff;
        background: rgba(0, 129, 255, 0.08);
        display: inline-block;
        padding: 2px 12px;
        border-radius: 50px;
    }

    .description-small {
        font-size: 0.8rem;
        color: #636e72;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .current-badge-modern {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #0081ff;
        color: white;
        font-size: 0.6rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 50px;
        z-index: 10;
        text-transform: uppercase;
        box-shadow: 0 4px 10px rgba(0, 129, 255, 0.3);
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