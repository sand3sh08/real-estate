<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('broker');

$userId = (int) $_SESSION['user_id'];
$success = '';
$error = '';

// Check if profile already exists
$stmt = $pdo->prepare('SELECT * FROM brokers WHERE user_id = ?');
$stmt->execute([$userId]);
$broker = $stmt->fetch();

$isNewProfile = !$broker;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company = trim($_POST['company'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $commission = trim($_POST['commission'] ?? '');
    $contact = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $office = trim($_POST['office_address'] ?? '');
    $about = trim($_POST['about'] ?? '');

    if ($isNewProfile) {
        // INSERT new profile
        $stmt = $pdo->prepare("
            INSERT INTO brokers (user_id, company, experience, commission, contact_number, email, office_address, about)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $company, $experience, $commission, $contact, $email, $office, $about]);
        $success = "Profile created successfully!";
        // Refresh broker data
        $stmt = $pdo->prepare('SELECT * FROM brokers WHERE user_id = ?');
        $stmt->execute([$userId]);
        $broker = $stmt->fetch();
        $isNewProfile = false;
    } else {
        // UPDATE existing profile
        $stmt = $pdo->prepare("
            UPDATE brokers
            SET
                company = ?,
                experience = ?,
                commission = ?,
                contact_number = ?,
                email = ?,
                office_address = ?,
                about = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $company,
            $experience,
            $commission,
            $contact,
            $email,
            $office,
            $about,
            $userId
        ]);
        $success = "Profile updated successfully!";
        // Refresh broker data
        $stmt = $pdo->prepare('SELECT * FROM brokers WHERE user_id = ?');
        $stmt->execute([$userId]);
        $broker = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broker Profile - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Page Header -->
<section class="page-header seller-header" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fade-in">👨‍💼 <?= $isNewProfile ? 'Create' : 'Edit' ?> Broker Profile</h1>
                <p class="fade-in">
                    <?php if ($isNewProfile): ?>
                        Fill in your details to get started as a broker
                    <?php else: ?>
                        Update your profile information
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="dashboard.php" class="btn btn-light btn-header fade-in">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">
    <?php if ($success): ?>
        <div class="alert alert-success fade-in">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger fade-in"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card-modern fade-in">
                <div class="card-modern-header">
                    <i class="fas fa-user-edit text-primary"></i> 
                    <?= $isNewProfile ? 'Create Your Profile' : 'Edit Your Profile' ?>
                </div>
                <div class="card-modern-body">
                    <?php if ($isNewProfile): ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle"></i> 
                            This is your first time setting up your broker profile. Fill in the details below and click "Create Profile". You can always edit it later.
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="company" class="form-control" 
                                       value="<?= htmlspecialchars($broker['company'] ?? '') ?>"
                                       placeholder="e.g. XYZ Realty">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Experience</label>
                                <input type="text" name="experience" class="form-control" 
                                       value="<?= htmlspecialchars($broker['experience'] ?? '') ?>"
                                       placeholder="e.g. 5 years">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Rate</label>
                                <input type="text" name="commission" class="form-control" 
                                       value="<?= htmlspecialchars($broker['commission'] ?? '') ?>"
                                       placeholder="e.g. 2%">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" 
                                       value="<?= htmlspecialchars($broker['contact_number'] ?? '') ?>"
                                       placeholder="+91 9876543210">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($broker['email'] ?? '') ?>"
                                       placeholder="broker@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Office Address</label>
                                <input type="text" name="office_address" class="form-control" 
                                       value="<?= htmlspecialchars($broker['office_address'] ?? '') ?>"
                                       placeholder="123, Main Street, City">
                            </div>
                            <div class="col-12">
                                <label class="form-label">About / Bio</label>
                                <textarea name="about" class="form-control" rows="4" 
                                          placeholder="Tell clients about yourself, your experience, and your services..."><?= htmlspecialchars($broker['about'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <?php if ($isNewProfile): ?>
                                    <button type="submit" class="btn btn-success btn-submit">
                                        <i class="fas fa-plus-circle"></i> Create Profile
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                <?php endif; ?>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>

                    <?php if (!$isNewProfile): ?>
                        <div class="mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Your profile is complete. You can update any field above and save changes.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>