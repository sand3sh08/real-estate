<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$error = '';
$success = '';

$stmt = $pdo->prepare("
    SELECT p.*, u.name AS seller_name, u.email AS seller_email, u.phone AS seller_phone
    FROM properties p
    JOIN users u ON p.seller_id = u.id
    WHERE p.id = ? AND p.status = 'Approved'
");
$stmt->execute([$id]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$imgStmt = $pdo->prepare('SELECT image FROM property_images WHERE property_id = ?');
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

$isFavourite = false;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer') {
    $favStmt = $pdo->prepare('SELECT id FROM favourites WHERE buyer_id = ? AND property_id = ?');
    $favStmt->execute([$_SESSION['user_id'], $id]);
    $isFavourite = (bool) $favStmt->fetch();
}

// Handle favourite toggle (Enquiry removed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['role']) && $_SESSION['role'] === 'buyer') {
    $action = $_POST['action'] ?? '';

    if ($action === 'favourite') {
        if ($isFavourite) {
            $pdo->prepare('DELETE FROM favourites WHERE buyer_id = ? AND property_id = ?')
                ->execute([$_SESSION['user_id'], $id]);
            $isFavourite = false;
            $success = 'Removed from favourites.';
        } else {
            $pdo->prepare('INSERT IGNORE INTO favourites (buyer_id, property_id) VALUES (?, ?)')
                ->execute([$_SESSION['user_id'], $id]);
            $isFavourite = true;
            $success = 'Added to favourites.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title']) ?> - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .fade-in { animation: fadeUp 0.8s ease forwards; opacity: 0; }
        .property-gallery {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        }
        .property-gallery .carousel-inner img {
            height: 400px;
            object-fit: cover;
        }
        .property-gallery .carousel-control-prev,
        .property-gallery .carousel-control-next {
            background: rgba(0,0,0,0.3);
            border-radius: 50%;
            width: 44px;
            height: 44px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .property-gallery:hover .carousel-control-prev,
        .property-gallery:hover .carousel-control-next { opacity: 1; }
        .badge-light {
            background: #f1f5f9 !important;
            color: #334155 !important;
            padding: 6px 14px;
            font-weight: 600;
            border-radius: 30px;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        .card:hover { box-shadow: 0 20px 40px rgba(37,99,235,0.1); }
        .btn {
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 700;
            transition: all 0.3s ease;
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
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border: none;
            box-shadow: 0 10px 25px rgba(239,68,68,0.3);
        }
        .btn-danger:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(239,68,68,0.4);
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            border: none;
            box-shadow: 0 10px 25px rgba(16,185,129,0.3);
        }
        .btn-success:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(16,185,129,0.4);
        }
        .btn-warning-custom {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border: none;
            color: white;
            box-shadow: 0 10px 25px rgba(245,158,11,0.3);
        }
        .btn-warning-custom:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(245,158,11,0.4);
            color: white;
        }
        .alert { border-radius: 15px; border: none; }
        .seller-card h5 {
            font-weight: 800;
            color: var(--dark, #0f172a);
        }
        .seller-card p {
            margin-bottom: 4px;
            color: #64748b;
        }
        @media (max-width: 768px) {
            .property-gallery .carousel-inner img { height: 250px; }
            .property-gallery .carousel-control-prev,
            .property-gallery .carousel-control-next { width: 36px; height: 36px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
<main class="container py-4">
    <?php if ($error): ?><div class="alert alert-danger fade-in"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success fade-in"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <?php if ($images): ?>
                <div id="gallery" class="carousel slide mb-3 property-gallery fade-in" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $i => $img): ?>
                            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                <img src="<?= BASE_URL ?>/uploads/property_images/<?= htmlspecialchars($img['image']) ?>" class="d-block w-100" alt="">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#gallery" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                        <button class="carousel-control-next" type="button" data-bs-target="#gallery" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded mb-3 fade-in" style="height:300px">No Images</div>
            <?php endif; ?>

            <h2 class="fade-in"><?= htmlspecialchars($property['title']) ?></h2>
            <p class="text-primary fs-4 fw-bold fade-in"><?= formatPrice($property['price']) ?></p>
            <p class="text-muted fade-in"><?= htmlspecialchars($property['location'] . ', ' . $property['city'] . ', ' . $property['state']) ?></p>
            <p class="fade-in"><?= nl2br(htmlspecialchars($property['description'] ?? '')) ?></p>

            <div class="row g-2 mb-3 fade-in">
                <div class="col-auto"><span class="badge bg-light text-dark border"><?= (int) $property['bedrooms'] ?> Beds</span></div>
                <div class="col-auto"><span class="badge bg-light text-dark border"><?= (int) $property['bathrooms'] ?> Baths</span></div>
                <div class="col-auto"><span class="badge bg-light text-dark border"><?= (int) $property['parking'] ?> Parking</span></div>
                <div class="col-auto"><span class="badge bg-light text-dark border"><?= htmlspecialchars($property['property_type']) ?></span></div>
                <?php if ($property['area']): ?>
                    <div class="col-auto"><span class="badge bg-light text-dark border"><?= $property['area'] ?> sq.ft</span></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Seller Info Card -->
            <div class="card mb-3 fade-in seller-card">
                <div class="card-body">
                    <h5>Seller Info</h5>
                    <p class="mb-1"><strong><?= htmlspecialchars($property['seller_name']) ?></strong></p>
                    <p class="mb-1 small"><?= htmlspecialchars($property['seller_email']) ?></p>
                    <p class="mb-0 small"><?= htmlspecialchars($property['seller_phone']) ?></p>
                </div>
            </div>

            <!-- BROKER → SELLER MESSAGE BUTTON -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'broker'): ?>
                <div class="card mb-3 fade-in">
                    <div class="card-body">
                        <h5>💼 Offer Your Services</h5>
                        <p class="text-muted small">Contact the seller and offer your brokerage services</p>
                        <a href="<?= BASE_URL ?>/messaging/inbox.php?seller_id=<?= (int) $property['seller_id'] ?>&property_id=<?= (int) $property['id'] ?>&role=broker" 
                           class="btn btn-warning-custom w-100">
                            <i class="fas fa-handshake"></i> Contact Seller
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- BUYER → SELLER MESSAGE BUTTON -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
                <div class="card mb-3 fade-in">
                    <div class="card-body">
                        <h5>📩 Contact Seller</h5>
                        <p class="text-muted small">Message the seller directly about this property</p>
                        <a href="<?= BASE_URL ?>/messaging/inbox.php?seller_id=<?= (int) $property['seller_id'] ?>&property_id=<?= (int) $property['id'] ?>" 
                           class="btn btn-success w-100">
                            <i class="fas fa-comment"></i> Message Seller
                        </a>
                    </div>
                </div>

                <!-- Favourite Button -->
                <form method="post" class="mb-3 fade-in">
                    <input type="hidden" name="action" value="favourite">
                    <button type="submit" class="btn <?= $isFavourite ? 'btn-danger' : 'btn-outline-danger' ?> w-100">
                        <?= $isFavourite ? '♥ Remove Favourite' : '♡ Add to Favourites' ?>
                    </button>
                </form>
            <?php endif; ?>

            <!-- GUEST / NOT LOGGED IN -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="alert alert-info fade-in">
                    Please <a href="<?= BASE_URL ?>/login.php">login</a> as a buyer or broker to contact the seller.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>