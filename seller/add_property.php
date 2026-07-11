<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('seller');

$error = '';
$success = '';

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

    if ($title === '' || $price <= 0 || $city === '') {
        $error = 'Title, Price and City are required.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO properties
            (seller_id, title, description, price, area, location, city, state,
             property_type, bedrooms, bathrooms, parking, status)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $desc,
            $price,
            $area,
            $location,
            $city,
            $state,
            $type,
            $bedrooms,
            $bathrooms,
            $parking,
            'Pending'
        ]);
        $propertyId = (int)$pdo->lastInsertId();
        if (!empty($_FILES['images']['name'][0])) {
            uploadPropertyImages($pdo, $propertyId, $_FILES['images']);
        }
        $success = 'Property submitted for admin approval.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - Seller</title>
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
    <h2 class="mb-4 fade-in">Add Property</h2>
    <?php if ($error): ?><div class="alert alert-danger fade-in"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success fade-in"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="form-card-modern fade-in">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Price (₹) *</label>
                <input type="number" name="price" class="form-control" step="0.01" required>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Property Type</label>
                <select name="property_type" class="form-select">
                    <?php foreach (['Apartment', 'House', 'Villa', 'Plot', 'Commercial'] as $t): ?>
                        <option value="<?= $t ?>"><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Area (sq.ft)</label>
                <input type="number" name="area" class="form-control" step="0.01">
            </div>
            <div class="col-md-4">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control">
            </div>
            <div class="col-md-8">
                <label class="form-label">Location / Address</label>
                <input type="text" name="location" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bedrooms</label>
                <input type="number" name="bedrooms" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bathrooms</label>
                <input type="number" name="bathrooms" class="form-control" value="0" min="0">
            </div>
            <div class="col-md-4">
                <label class="form-label">Parking</label>
                <input type="number" name="parking" class="form-control" value="0" min="0">
            </div>
            <div class="col-12">
                <label class="form-label">Images (jpg, png, webp — max 5MB each)</label>
                <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.webp" multiple>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-submit">Submit for Approval</button>
                <a href="my_properties.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </form>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>