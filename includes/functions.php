<?php
require_once __DIR__ . '/db.php';

function fetch_all(string $sql, string $types = '', array $params = []): array
{
    $db = get_db_connection();
    if (!$db) {
        return [];
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return [];
    }

    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_one(string $sql, string $types = '', array $params = []): ?array
{
    $rows = fetch_all($sql, $types, $params);
    return $rows[0] ?? null;
}

function cnic_valid(string $cnic): bool
{
    return (bool)preg_match('/^\d{5}-\d{7}-\d$/', $cnic);
}

function generate_transaction_id(): string
{
    return 'KSWO-' . date('Ymd-His') . '-' . random_int(100, 999);
}

function upload_receipt_file(string $inputName = 'payment_receipt'): array
{
    if (!isset($_FILES[$inputName]) || !is_array($_FILES[$inputName])) {
        return ['path' => null, 'error' => 'Please attach payment receipt file.'];
    }

    $file = $_FILES[$inputName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => 'Please attach payment receipt file.'];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Failed to upload receipt file.'];
    }

    $maxSize = 5 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) {
        return ['path' => null, 'error' => 'Receipt file must be 5MB or less.'];
    }

    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['path' => null, 'error' => 'Allowed receipt file types: jpg, jpeg, png, pdf.'];
    }

    $uploadDir = __DIR__ . '/../uploads/receipts/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return ['path' => null, 'error' => 'Unable to prepare upload directory.'];
    }

    $fileName = 'receipt_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
        return ['path' => null, 'error' => 'Unable to save receipt file.'];
    }

    return ['path' => 'uploads/receipts/' . $fileName, 'error' => null];
}

function upload_payment_icon(string $inputName = 'icon_file'): array
{
    if (!isset($_FILES[$inputName]) || !is_array($_FILES[$inputName])) {
        return ['path' => null, 'error' => null];
    }

    $file = $_FILES[$inputName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => 'Failed to upload icon file.'];
    }

    $maxSize = 2 * 1024 * 1024;
    if (($file['size'] ?? 0) > $maxSize) {
        return ['path' => null, 'error' => 'Icon file must be 2MB or less.'];
    }

    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
    if (!in_array($extension, $allowedExtensions, true)) {
        return ['path' => null, 'error' => 'Allowed icon types: jpg, jpeg, png, webp, svg.'];
    }

    $uploadDir = __DIR__ . '/../uploads/payment-icons/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        return ['path' => null, 'error' => 'Unable to prepare icon upload directory.'];
    }

    $fileName = 'pay_icon_' . date('YmdHis') . '_' . random_int(1000, 9999) . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
        return ['path' => null, 'error' => 'Unable to save icon file.'];
    }

    return ['path' => 'uploads/payment-icons/' . $fileName, 'error' => null];
}
