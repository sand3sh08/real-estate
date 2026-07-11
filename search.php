<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$sql = "
    SELECT p.*, u.name AS seller_name,
           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) AS thumb
    FROM properties p
    JOIN users u ON p.seller_id = u.id
    WHERE p.status = 'Approved'
";
$params = [];

if (!empty($_GET['city'])) {
    $sql .= ' AND p.city LIKE ?';
    $params[] = '%' . $_GET['city'] . '%';
}
if (!empty($_GET['type'])) {
    $sql .= ' AND p.property_type = ?';
    $params[] = $_GET['type'];
}
if (!empty($_GET['min_price'])) {
    $sql .= ' AND p.price >= ?';
    $params[] = (float) $_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $sql .= ' AND p.price <= ?';
    $params[] = (float) $_GET['max_price'];
}

$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Properties - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <style>
        /* =====================================================
           SEARCH PAGE – CUSTOM STYLES
        ===================================================== */

        .fade-in {
            animation: fadeUp 0.8s ease forwards;
            opacity: 0;
        }

        .search-card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.06);
            padding: 30px;
            background: white;
            transition: all 0.3s ease;
        }

        .search-card:hover {
            box-shadow: 0 20px 50px rgba(37,99,235,0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark, #0f172a);
            font-size: 14px;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary, #2563eb);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        }

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

        .property-card .btn-sm {
            border-radius: 50px;
            padding: 6px 18px;
            font-weight: 600;
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        @media (max-width: 768px) {
            .search-card {
                padding: 20px;
            }
            .property-card .card-img-top {
                height: 180px;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">Search Properties</h2>

    <form method="get" class="search-card mb-4 fade-in">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Property Type</label>
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach (['Apartment', 'House', 'Villa', 'Plot', 'Commercial'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($_GET['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Min Price</label>
                <input type="number" name="min_price" class="form-control" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Price</label>
                <input type="number" name="max_price" class="form-control" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

    <p class="text-muted fade-in"><?= count($properties) ?> result(s) found</p>

    <?php if (empty($properties)): ?>
        <div class="alert alert-info fade-in">No properties match your search criteria.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($properties as $p): ?>
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
                            <p class="text-muted small"><?= htmlspecialchars($p['city'] . ', ' . $p['state']) ?> · <?= htmlspecialchars($p['property_type']) ?></p>
                            <a href="<?= BASE_URL ?>/property.php?id=<?= (int) $p['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>