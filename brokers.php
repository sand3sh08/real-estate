<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch all brokers with user details AND all broker fields
$sql = "
    SELECT b.*, u.name, u.email, u.phone, u.created_at
    FROM brokers b
    JOIN users u ON b.user_id = u.id
    WHERE u.role = 'broker' AND u.status = 'active'
";

$params = [];

// Search filter (by user's name, email, or phone)
if (!empty($_GET['search'])) {
    $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $search = '%' . $_GET['search'] . '%';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$sql .= ' ORDER BY u.name ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$brokers = $stmt->fetchAll();

// For buyers/sellers, check if they have existing conversations with each broker
$conversationCounts = [];
if (isset($_SESSION['user_id']) && ($_SESSION['role'] === 'buyer' || $_SESSION['role'] === 'seller')) {
    $userId = (int) $_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    foreach ($brokers as $broker) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM conversations 
            WHERE user_id = ? AND broker_id = ? AND user_role = ?
        ");
        $stmt->execute([$userId, $broker['user_id'], $userRole]);
        $conversationCounts[$broker['user_id']] = (int) $stmt->fetchColumn();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brokers - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
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

        .form-control {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        .form-control:focus {
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

        .btn-outline-primary {
            border: 2px solid var(--primary, #2563eb);
            color: var(--primary, #2563eb);
        }
        .btn-outline-primary:hover {
            background: var(--primary, #2563eb);
            color: white;
            transform: translateY(-4px);
        }

        .btn-outline-secondary {
            border: 2px solid #cbd5e1;
            color: #64748b;
        }
        .btn-outline-secondary:hover {
            background: #f1f5f9;
            transform: translateY(-4px);
        }

        .btn-sm {
            border-radius: 50px;
            padding: 6px 16px;
            font-weight: 600;
            font-size: 13px;
        }

        /* Contact Broker Button */
        .btn-contact-broker {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-contact-broker:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37,99,235,0.3);
            color: white;
        }

        .btn-contact-broker-active {
            background: #10b981;
            color: white;
            border: none;
        }
        .btn-contact-broker-active:hover {
            background: #059669;
            color: white;
            transform: translateY(-3px);
        }

        .broker-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
            background: white;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .broker-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(37,99,235,0.12);
        }

        .broker-card .card-body {
            padding: 25px;
            text-align: center;
        }

        .broker-card .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #fef3c7);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
            color: var(--primary, #2563eb);
            border: 3px solid white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .broker-card h5 {
            font-weight: 800;
            color: var(--dark, #0f172a);
            margin-bottom: 4px;
        }

        .broker-card .email,
        .broker-card .phone {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .badge-broker {
            background: rgba(37,99,235,0.1);
            color: var(--primary, #2563eb);
            padding: 4px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 12px;
        }

        .broker-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin-top: 10px;
            text-align: left;
            font-size: 14px;
        }

        .broker-details p {
            margin-bottom: 6px;
            color: #334155;
        }
        .broker-details strong {
            color: #0f172a;
        }

        .alert {
            border-radius: 15px;
            border: none;
        }

        @media (max-width: 768px) {
            .search-card {
                padding: 20px;
            }
            .broker-card .avatar {
                width: 60px;
                height: 60px;
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">👨‍💼 Our Brokers</h2>

    <!-- Search Form -->
    <form method="get" class="search-card mb-4 fade-in">
        <div class="row g-3">
            <div class="col-md-9">
                <label class="form-label">Search Brokers</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
        </div>
    </form>

    <?php if (empty($brokers)): ?>
        <div class="alert alert-info fade-in">No brokers found <?= !empty($_GET['search']) ? 'matching your search' : 'yet' ?>.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($brokers as $broker): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card broker-card fade-in">
                        <div class="card-body">
                            <div class="avatar">
                                <?= htmlspecialchars(substr($broker['name'], 0, 2)) ?>
                            </div>
                            <h5><?= htmlspecialchars($broker['name']) ?></h5>
                            <p class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($broker['email']) ?></p>
                            <p class="phone"><i class="fas fa-phone"></i> <?= htmlspecialchars($broker['phone']) ?></p>
                            <span class="badge-broker">Verified Broker</span>

                            <!-- More Details Button -->
                            <button class="btn btn-outline-secondary btn-sm mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#details-<?= $broker['user_id'] ?>" aria-expanded="false">
                                More Details
                            </button>

                            <!-- Collapsible Details Section -->
                            <div class="collapse" id="details-<?= $broker['user_id'] ?>">
                                <div class="broker-details">
                                    <?php if (!empty($broker['company'])): ?>
                                        <p><strong>Company:</strong> <?= htmlspecialchars($broker['company']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($broker['experience'])): ?>
                                        <p><strong>Experience:</strong> <?= htmlspecialchars($broker['experience']) ?> years</p>
                                    <?php endif; ?>
                                    <?php if (!empty($broker['commission'])): ?>
                                        <p><strong>Commission:</strong> <?= htmlspecialchars($broker['commission']) ?>%</p>
                                    <?php endif; ?>
                                    <?php if (!empty($broker['contact_number'])): ?>
                                        <p><strong>Contact:</strong> <?= htmlspecialchars($broker['contact_number']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($broker['office_address'])): ?>
                                        <p><strong>Office:</strong> <?= htmlspecialchars($broker['office_address']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($broker['about'])): ?>
                                        <p><strong>About:</strong> <?= nl2br(htmlspecialchars($broker['about'])) ?></p>
                                    <?php endif; ?>
                                    <?php if (empty($broker['company']) && empty($broker['experience']) && empty($broker['commission']) && empty($broker['contact_number']) && empty($broker['office_address']) && empty($broker['about'])): ?>
                                        <p class="text-muted">No additional details available.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- ============================================== -->
                            <!-- CONTACT BROKER BUTTON – WORKS! -->
                            <!-- ============================================== -->
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'buyer'): ?>
                                <?php 
                                $hasConversation = isset($conversationCounts[$broker['user_id']]) && $conversationCounts[$broker['user_id']] > 0;
                                ?>
                                <div class="mt-3">
                                    <a href="<?= BASE_URL ?>/messaging/inbox.php?broker_id=<?= $broker['user_id'] ?>" 
                                       class="btn <?= $hasConversation ? 'btn-contact-broker-active' : 'btn-contact-broker' ?> btn-sm w-100">
                                        <?php if ($hasConversation): ?>
                                            <i class="fas fa-comment-dots"></i> Continue Chat
                                        <?php else: ?>
                                            <i class="fas fa-comment"></i> Contact Broker
                                        <?php endif; ?>
                                    </a>
                                </div>
                            <?php elseif (!isset($_SESSION['user_id'])): ?>
                                <div class="mt-3">
                                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="fas fa-sign-in-alt"></i> Login to Contact
                                    </a>
                                </div>
                            <?php endif; ?>
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