<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);

// Delete property images
$imgs = $pdo->prepare('SELECT image FROM property_images WHERE property_id = ?');
$imgs->execute([$id]);
foreach ($imgs->fetchAll() as $img) {
    $path = __DIR__ . '/../uploads/property_images/' . $img['image'];
    if (is_file($path)) {
        unlink($path);
    }
}

$pdo->prepare("DELETE FROM properties WHERE id = ?")->execute([$id]);
header('Location: properties.php?tab=all');
exit;