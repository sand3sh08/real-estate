<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', '/real-estate');

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole($roles): void
{
    requireLogin();
    if (!in_array($_SESSION['role'], (array) $roles, true)) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function redirectByRole(string $role): void
{
    $map = [
        'admin'  => BASE_URL . '/admin/dashboard.php',
        'seller' => BASE_URL . '/seller/dashboard.php',
        'buyer'  => BASE_URL . '/buyer/dashboard.php',
        'broker' => BASE_URL . '/broker/dashboard.php',
    ];

    header('Location: ' . ($map[$role] ?? BASE_URL . '/index.php'));
    exit;
}

function statusBadge(string $status): string
{
    $map = [
        'Pending'  => 'warning',
        'Approved' => 'success',
        'Rejected' => 'danger',
        'Sold'     => 'secondary',
        'active'   => 'success',
        'pending'  => 'warning',
        'rejected' => 'danger',
        'blocked'  => 'dark',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . htmlspecialchars($status) . '</span>';
}

function formatPrice($price): string
{
    return '₹' . number_format((float) $price, 0);
}

function allowedImage(string $filename): bool
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
}

function uploadPropertyImages(PDO $pdo, int $propertyId, array $files): void
{
    $uploadDir = __DIR__ . '/../uploads/property_images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK || empty($files['tmp_name'][$i])) {
            continue;
        }
        if (!allowedImage($files['name'][$i])) {
            continue;
        }
        if ($files['size'][$i] > 5 * 1024 * 1024) {
            continue;
        }

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        $filename = uniqid('prop_', true) . '.' . $ext;

        if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $filename)) {
            $stmt = $pdo->prepare('INSERT INTO property_images (property_id, image) VALUES (?, ?)');
            $stmt->execute([$propertyId, $filename]);
        }
    }
}
