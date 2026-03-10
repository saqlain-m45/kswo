<?php
ob_start();
include 'includes/header.php';

// Remove strict login required - allow guest donations
$is_guest = !isLoggedIn();
$user_id = $is_guest ? null : $_SESSION['user_id'];

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step1'])) {
        $_SESSION['temp_amount'] = (float)$_POST['amount'];
        $_SESSION['temp_sub'] = isset($_POST['subscription']) ? 1 : 0;

        if ($is_guest) {
            $_SESSION['guest_name'] = sanitize($_POST['guest_name']);
            $_SESSION['guest_email'] = sanitize($_POST['guest_email']);
            $_SESSION['guest_phone'] = sanitize($_POST['guest_phone']);
        }
        redirect('donate.php?step=2');
    }
    elseif (isset($_POST['step2'])) {
        $_SESSION['temp_method'] = sanitize($_POST['method']);
        redirect('donate.php?step=3');
    }
    elseif (isset($_POST['step3'])) {
        $ref = sanitize($_POST['ref_number'] ?? '');
        $amount = $_SESSION['temp_amount'];
        $method = $_SESSION['temp_method'];
        $sub = $_SESSION['temp_sub'];

        $screenshot_path = null;
        if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] == 0) {
            $ext = pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION);
            $filename = 'receipt_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $target_dir = 'uploads/donations/';
            if (!is_dir($target_dir))
                mkdir($target_dir, 0777, true);

            $target = $target_dir . $filename;
            if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $target)) {
                $screenshot_path = $target;
            }
        }

        if (empty($ref) && !$screenshot_path) {
            $error = "Reference number or screenshot is required.";
        }
        else {
            if ($is_guest) {
                $stmt = $pdo->prepare("INSERT INTO donations (guest_name, guest_email, guest_phone, amount, method, reference_number, screenshot, is_subscription) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $success = $stmt->execute([$_SESSION['guest_name'], $_SESSION['guest_email'], $_SESSION['guest_phone'], $amount, $method, $ref, $screenshot_path, $sub]);
            }
            else {
                $stmt = $pdo->prepare("INSERT INTO donations (user_id, amount, method, reference_number, screenshot, is_subscription) VALUES (?, ?, ?, ?, ?, ?)");
                $success = $stmt->execute([$user_id, $amount, $method, $ref, $screenshot_path, $sub]);
            }

            if ($success) {
                unset($_SESSION['temp_amount'], $_SESSION['temp_method'], $_SESSION['temp_sub'], $_SESSION['guest_name'], $_SESSION['guest_email'], $_SESSION['guest_phone']);
                if ($is_guest) {
                    redirect('transparency.php?status=thank_you');
                }
                else {
                    redirect('dashboard.php?status=success');
                }
            }
            else {
                $error = "Failed to record donation. Please contact support.";
            }
        }
    }
}
?>

<section class="py-5 mt-5">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <!-- Progress Stepper -->
                <div class="d-flex justify-content-between mb-5 px-md-5 reveal">
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto shadow-premium"
                            style="width: 50px; height: 50px; background: <?php echo $step >= 1 ? 'var(--primary-gradient)' : '#eee'; ?>; color: <?php echo $step >= 1 ? '#fff' : '#888'; ?>; font-weight: 700;">
                            1</div>
                        <small class="fw-bold <?php echo $step >= 1 ? 'text-primary' : 'text-muted'; ?>">Details</small>
                    </div>
                    <div class="flex-grow-1 align-self-center mx-2 mb-4" style="height: 2px; background: #eee;">
                        <div
                            style="height: 100%; width: <?php echo $step > 1 ? '100%' : '0%'; ?>; background: var(--primary-gradient); transition: width 0.5s ease;">
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto shadow-premium"
                            style="width: 50px; height: 50px; background: <?php echo $step >= 2 ? 'var(--primary-gradient)' : '#eee'; ?>; color: <?php echo $step >= 2 ? '#fff' : '#888'; ?>; font-weight: 700;">
                            2</div>
                        <small class="fw-bold <?php echo $step >= 2 ? 'text-primary' : 'text-muted'; ?>">Method</small>
                    </div>
                    <div class="flex-grow-1 align-self-center mx-2 mb-4" style="height: 2px; background: #eee;">
                        <div
                            style="height: 100%; width: <?php echo $step > 2 ? '100%' : '0%'; ?>; background: var(--primary-gradient); transition: width 0.5s ease;">
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2 mx-auto shadow-premium"
                            style="width: 50px; height: 50px; background: <?php echo $step >= 3 ? 'var(--primary-gradient)' : '#eee'; ?>; color: <?php echo $step >= 3 ? '#fff' : '#888'; ?>; font-weight: 700;">
                            3</div>
                        <small class="fw-bold <?php echo $step >= 3 ? 'text-primary' : 'text-muted'; ?>">Confirm</small>
                    </div>
                </div>

                <div class="card border-0 shadow-premium p-4 p-md-5 bg-white reveal" style="border-radius: 28px;">
                    <?php if ($error): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                        <?php echo $error; ?>
                    </div>
                    <?php
endif; ?>

                    <?php if ($step == 1): ?>
                    <h3 class="mb-4 fw-bold">Donation <span class="gradient-text">Details</span></h3>
                    <form action="donate.php?step=1" method="POST">
                        <?php if ($is_guest): ?>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label text-muted fw-bold small text-uppercase">Full Name (Publicly
                                    Visible)</label>
                                <input type="text" name="guest_name" class="form-control bg-light border-0 py-3 px-4"
                                    placeholder="Your Name" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold small text-uppercase">Email</label>
                                <input type="email" name="guest_email" class="form-control bg-light border-0 py-3 px-4"
                                    placeholder="email@example.com" required style="border-radius: 12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted fw-bold small text-uppercase">Phone</label>
                                <input type="text" name="guest_phone" class="form-control bg-light border-0 py-3 px-4"
                                    placeholder="03xx-xxxxxxx" required style="border-radius: 12px;">
                            </div>
                        </div>
                        <?php
    endif; ?>

                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase mb-2">Donation Amount
                                (PKR)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-0 px-4">Rs.</span>
                                <input type="number" name="amount" class="form-control bg-light border-0 px-4 py-3"
                                    placeholder="1000" min="10" required style="border-radius: 0 12px 12px 0;">
                            </div>
                        </div>
                        <div class="form-check mb-5 custom-checkbox">
                            <input class="form-check-input" type="checkbox" name="subscription" id="subCheck">
                            <label class="form-check-label text-muted" for="subCheck">
                                Set as monthly subscription
                            </label>
                        </div>
                        <button type="submit" name="step1"
                            class="btn btn-primary-custom w-100 py-3 btn-custom shadow-premium">Continue</button>
                    </form>

                    <?php
elseif ($step == 2): ?>
                    <h3 class="mb-4 fw-bold">Payment <span class="gradient-text">Method</span></h3>
                    <div class="mb-4">
                        <div
                            class="alert alert-info border-0 rounded-4 px-4 py-3 bg-primary-subtle text-primary reveal">
                            <i class="fas fa-info-circle me-2"></i> Please transfer the amount and then provide the
                            Transaction ID or Receipt in the next step.
                        </div>
                    </div>
                    <form action="donate.php?step=2" method="POST">
                        <div class="mb-4">
                            <div class="form-check card p-4 mb-3 cursor-pointer border-0 shadow-sm payment-card">
                                <input class="form-check-input" type="radio" name="method" value="Easypaisa" id="epa"
                                    checked>
                                <label class="form-check-label d-flex justify-content-between w-100" for="epa">
                                    <span class="fw-bold">Easypaisa</span>
                                    <span class="text-primary fw-bold">0300-1234567</span>
                                </label>
                            </div>
                            <div class="form-check card p-4 cursor-pointer border-0 shadow-sm payment-card">
                                <input class="form-check-input" type="radio" name="method" value="JazzCash" id="jca">
                                <label class="form-check-label d-flex justify-content-between w-100" for="jca">
                                    <span class="fw-bold">JazzCash</span>
                                    <span class="text-danger fw-bold">0311-7654321</span>
                                </label>
                            </div>
                        </div>
                        <button type="submit" name="step2"
                            class="btn btn-primary-custom w-100 py-3 btn-custom shadow-premium">I have
                            sent the money</button>
                    </form>

                    <?php
elseif ($step == 3): ?>
                    <h3 class="mb-4 fw-bold">Final <span class="gradient-text">Verification</span></h3>
                    <p class="text-muted mb-4 lead">You are donating <span class="text-primary fw-bold">Rs.
                            <?php echo number_format($_SESSION['temp_amount']); ?>
                        </span> via <span class="text-dark fw-bold">
                            <?php echo $_SESSION['temp_method']; ?>
                        </span>.</p>
                    <form action="donate.php?step=3" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase mb-2">Transaction ID /
                                Reference Number</label>
                            <input type="text" name="ref_number"
                                class="form-control form-control-lg bg-light border-0 py-3" placeholder="Enter ID here"
                                style="border-radius: 12px;">
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted fw-bold small text-uppercase mb-2">Payment Receipt
                                Screenshot</label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="screenshot" class="form-control bg-light border-0 py-3"
                                    accept="image/*" style="border-radius: 12px;">
                                <div class="mt-2 small text-muted">Upload a screenshot of your payment confirmation for
                                    faster verification.</div>
                            </div>
                        </div>
                        <button type="submit" name="step3"
                            class="btn btn-primary-custom w-100 py-3 btn-custom shadow-premium">Complete
                            Donation</button>
                        <a href="donate.php?step=2" class="btn btn-link w-100 mt-2 text-muted text-decoration-none">Back
                            to Payment Method</a>
                    </form>
                    <?php
endif; ?>
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

    // Auto-select card on radio click
    document.querySelectorAll('.payment-card').forEach(card => {
        card.addEventListener('click', function () {
            this.querySelector('input').checked = true;
        });
    });
</script>

<style>
    .payment-card {
        transition: all 0.3s ease;
        border: 2px solid transparent !important;
    }

    .payment-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }

    .payment-card:has(input:checked) {
        border: 2px solid var(--accent-color) !important;
        background: rgba(0, 161, 255, 0.05);
    }

    .custom-checkbox .form-check-input:checked {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
    }

    .bg-primary-subtle {
        background-color: rgba(0, 161, 255, 0.1) !important;
    }
</style>

<?php
include 'includes/footer.php';
ob_end_flush();
?>