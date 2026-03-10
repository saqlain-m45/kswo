<div class="card border-0 p-4 text-end bg-white reveal dashboard-sidebar" style="border-radius: 24px;">
    <div class="profile-avatar-wrapper mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center ms-auto shadow-sm position-relative shadow-premium"
            style="width: 120px; height: 120px; background: var(--primary-gradient); border: 5px solid #fff; overflow: hidden;">
            <?php if ($user['profile_pic']): ?>
            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile" class="w-100 h-100"
                style="object-fit: cover;">
            <?php
else: ?>
            <span style="font-size: 3rem; color: #fff; font-weight: 700;">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </span>
            <?php
endif; ?>
        </div>
    </div>
    <h3 class="mb-1 fw-bold text-main">
        <?php echo htmlspecialchars($user['name']); ?>
    </h3>
    <p class="text-secondary small mb-4">Member ID: #KSWO-
        <?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>
    </p>

    <div class="status-badge mb-4">
        <div class="mb-3">
            <label class="small text-muted text-uppercase fw-bold letter-spacing-1 d-block mb-1">Email</label>
            <span class="fw-bold text-dark">
                <?php echo htmlspecialchars($user['email']); ?>
            </span>
        </div>
        <div class="mb-3">
            <label class="small text-muted text-uppercase fw-bold letter-spacing-1 d-block mb-1">Phone</label>
            <span class="fw-bold text-dark">
                <?php echo htmlspecialchars($user['phone']); ?>
            </span>
        </div>
        <div>
            <label class="small text-muted text-uppercase fw-bold letter-spacing-1 d-block mb-1">CNIC</label>
            <span class="fw-bold text-dark">
                <?php echo htmlspecialchars($user['cnic']); ?>
            </span>
        </div>
    </div>

    <hr class="my-4 opacity-10">

    <a href="logout.php" class="btn btn-outline-danger w-100 mt-2 py-3 border-2 fw-bold"
        style="border-radius: 12px;">Logout</a>
</div>

<style>
    .nav-link {
        transition: all 0.3s;
        border-radius: 8px;
    }

    .nav-link:hover {
        background: #f8f9fa;
        color: var(--accent-color) !important;
    }

    .nav-link.active {
        background: rgba(0, 86, 179, 0.05);
    }
</style>