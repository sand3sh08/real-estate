<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('seller');

$sellerId = (int) $_SESSION['user_id'];
$id = (int) ($_GET['id'] ?? 0);
$error = '';

$stmt = $pdo->prepare('SELECT * FROM properties WHERE id = ? AND seller_id = ?');
$stmt->execute([$id, $sellerId]);
$property = $stmt->fetch();

if (!$property) {
    header('Location: my_properties.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $area = !empty($_POST['area']) ? (float) $_POST['area'] : null;
    $location = trim($_POST['location'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $type = trim($_POST['property_type'] ?? '');
    $bedrooms = (int) ($_POST['bedrooms'] ?? 0);
    $bathrooms = (int) ($_POST['bathrooms'] ?? 0);
    $parking = (int) ($_POST['parking'] ?? 0);

    if ($title === '' || $price <= 0) {
        $error = 'Title and price are required.';
    } else {
        $newStatus = ($property['status'] === 'Approved') ? 'Approved' : 'Pending';
        $pdo->prepare("
            UPDATE properties SET title=?, description=?, price=?, area=?, location=?,
            city=?, state=?, property_type=?, bedrooms=?, bathrooms=?, parking=?, status=?
            WHERE id=? AND seller_id=?
        ")->execute([
            $title, $desc, $price, $area, $location, $city, $state, $type,
            $bedrooms, $bathrooms, $parking, $newStatus, $id, $sellerId
        ]);
        if (!empty($_FILES['images']['name'][0])) {
            uploadPropertyImages($pdo, $id, $_FILES['images']);
        }
        header('Location: my_properties.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Decorative floating shapes -->
<div class="page-shape page-shape-1"></div>
<div class="page-shape page-shape-2"></div>
<div class="page-shape page-shape-3"></div>

<main class="container py-4 position-relative" style="z-index: 1;">
    <h2 class="mb-4 fade-in">Edit Property</h2>
    <?php if ($error): ?><div class="alert alert-danger fade-in"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-card-modern fade-in">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($property['title']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Price (₹)</label>
                <input type="number" name="price" class="form-control" step="0.01" required value="<?= $property['price'] ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Property Type</label>
                <select name="property_type" class="form-select">
                    <?php foreach (['Apartment', 'House', 'Villa', 'Plot', 'Commercial'] as $t): ?>
                        <option value="<?= $t ?>" <?= $property['property_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Area (sq.ft)</label>
                <input type="number" name="area" class="form-control" step="0.01" value="<?= $property['area'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" required value="<?= htmlspecialchars($property['city']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($property['state']) ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($property['location']) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bedrooms</label>
                <input type="number" name="bedrooms" class="form-control" value="<?= (int) $property['bedrooms'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bathrooms</label>
                <input type="number" name="bathrooms" class="form-control" value="<?= (int) $property['bathrooms'] ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Parking</label>
                <input type="number" name="parking" class="form-control" value="<?= (int) $property['parking'] ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Add More Images</label>
                <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
            </div>
            <div class="col-12">
                <p>Status: <span class="badge-status <?= strtolower($property['status']) === 'approved' ? 'bg-success' : 'bg-warning' ?>"><?= $property['status'] ?></span></p>
                <button type="submit" class="btn btn-primary btn-submit">Save Changes</button>
                <a href="my_properties.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>