<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('buyer');

$buyerId = (int) $_SESSION['user_id'];

if (isset($_GET['remove'])) {
    $propId = (int) $_GET['remove'];
    $pdo->prepare('DELETE FROM favourites WHERE buyer_id = ? AND property_id = ?')
        ->execute([$buyerId, $propId]);
    header('Location: favourites.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, f.created_at AS fav_date,
           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) AS thumb
    FROM favourites f
    JOIN properties p ON f.property_id = p.id
    WHERE f.buyer_id = ? AND p.status = 'Approved'
    ORDER BY f.created_at DESC
");
$stmt->execute([$buyerId]);
$favourites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favourites - Buyer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <style>
        /* =====================================================
           FAVOURITES PAGE – CUSTOM STYLES
        ===================================================== */

        .fade-in {
            animation: fadeUp 0.8s ease forwards;
            opacity: 0;
        }

        .property-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background: white;
        }

        .property-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(37,99,235,0.12);
        }

        .property-card .card-img-top {
            height: 220px;
            object-fit: cover;
        }

        .property-card .card-body {
            padding: 20px;
        }

        .property-card .card-title {
            font-weight: 800;
            color: var(--dark, #0f172a);
            font-size: 18px;
        }

        .property-card .text-primary {
            font-weight: 700;
            font-size: 18px;
        }

        .btn {
            border-radius: 50px;
            padding: 6px 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            box-shadow: 0 10px 25px rgba(37,99,235,0.3);
        }
        .btn-primary:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(37,99,235,0.4);
        }

        .btn-outline-danger {
            border: 2px solid #ef4444;
            color: #ef4444;
        }
        .btn-outline-danger:hover {
            background: #ef4444;
            color: white;
            transform: translateY(-4px);
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        @media (max-width: 768px) {
            .property-card .card-img-top {
                height: 180px;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">My Favourites</h2>

    <?php if (empty($favourites)): ?>
        <div class="alert alert-info fade-in">No favourites yet. <a href="<?= BASE_URL ?>/search.php">Browse properties</a></div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($favourites as $p): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card property-card h-100 fade-in">
                        <?php if ($p['thumb']): ?>
                            <img src="<?= BASE_URL ?>/uploads/property_images/<?= htmlspecialchars($p['thumb']) ?>" class="card-img-top" alt="">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:220px">No Image</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($p['title']) ?></h5>
                            <p class="text-primary fw-bold"><?= formatPrice($p['price']) ?></p>
                            <p class="text-muted small"><?= htmlspecialchars($p['city']) ?></p>
                            <div class="d-flex gap-2">
                                <a href="<?= BASE_URL ?>/property.php?id=<?= (int) $p['id'] ?>" class="btn btn-primary btn-sm">View</a>
                                <a href="?remove=<?= (int) $p['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove from favourites?')">Remove</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>