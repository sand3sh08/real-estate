<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$id = (int) ($_GET['id'] ?? 0);
$pdo->prepare("UPDATE properties SET status = 'Approved' WHERE id = ?")->execute([$id]);

header('Location: properties.php?tab=pending');
exit;
