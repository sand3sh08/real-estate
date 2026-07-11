<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('seller');

$sellerId = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id FROM properties WHERE id = ? AND seller_id = ?');
$stmt->execute([$id, $sellerId]);

if ($stmt->fetch()) {
    $imgs = $pdo->prepare('SELECT image FROM property_images WHERE property_id = ?');
    $imgs->execute([$id]);
    foreach ($imgs->fetchAll() as $img) {
        $path = __DIR__ . '/../uploads/property_images/' . $img['image'];
        if (is_file($path)) {
            unlink($path);
        }
    }
    $pdo->prepare('DELETE FROM properties WHERE id = ? AND seller_id = ?')->execute([$id, $sellerId]);
}

header('Location: my_properties.php');
exit;