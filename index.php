<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$stmt = $pdo->query("
    SELECT p.*, u.name AS seller_name,
           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) AS thumb
    FROM properties p
    JOIN users u ON p.seller_id = u.id
    WHERE p.status = 'Approved'
    ORDER BY p.created_at DESC
    LIMIT 12
");
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>RealEstate | India's Smart Property Platform</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

<!-- AOS CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

</head>

<body>

<?php include __DIR__.'/includes/navbar.php'; ?>


<!-- HERO -->

<section class="hero">

<div class="hero-overlay"></div>

<!-- Decorative floating circles -->
<div class="hero-circle hero-circle-1"></div>
<div class="hero-circle hero-circle-2"></div>

<div class="container">

<div class="row align-items-center min-vh-100">

<div class="col-lg-6 hero-left" data-aos="fade-right" data-aos-duration="1000">

<span class="hero-badge">
🏡 India's Smart Property Platform
</span>

<h1>

List Your Property

<span>FREE</span>

</h1>

<p>

Buyers and sellers pay nothing to list properties.

We help promote approved listings using digital marketing

while buyers and sellers communicate directly.

Our platform earns only after a successful deal.

</p>

<div class="hero-buttons">

<a href="<?= BASE_URL ?>/register.php" class="btn hero-btn">

List Property Free

</a>

<a href="<?= BASE_URL ?>/search.php" class="btn hero-btn-outline">

Browse Properties

</a>

</div>

<div class="hero-stats">

<div data-aos="zoom-in" data-aos-delay="200">

<h2>100%</h2>

<p>Free Listing</p>

</div>

<div data-aos="zoom-in" data-aos-delay="400">

<h2>24/7</h2>

<p>Support</p>

</div>

<div data-aos="zoom-in" data-aos-delay="600">

<h2>Verified</h2>

<p>Properties</p>

</div>

</div>

</div>

<div class="col-lg-6 text-center" data-aos="fade-left" data-aos-duration="1000">

<img src="assets\images\apartment.jpg"

class="img-fluid floating-house"

alt="House">

</div>

</div>

</div>

</section>





<!-- ================= HOW IT WORKS (6 CARDS – ADVANCED ANIMATIONS) ================= -->

<section class="how-it-works py-5" data-aos="fade-up">

<div class="container">

<div class="text-center mb-5">

<h2 class="section-title">How Our Platform Works</h2>

<p class="section-subtitle">A simple and transparent process for buyers and sellers.</p>

</div>

<div class="row g-4">

<!-- Step 1 -->
<div class="col-lg-4 d-flex" data-aos="flip-up" data-aos-delay="100">
    <div class="work-card w-100" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-glare="true" data-tilt-max-glare="0.2">
        <div class="step-icon"><i class="fas fa-cloud-upload-alt"></i></div>
        <span class="step-badge">01</span>
        <h4>Upload Property</h4>
        <p>Create your free account and list your property in minutes. Add high-quality photos, set your price, and provide all necessary details. Your listing goes instantly – no waiting.</p>
    </div>
</div>

<!-- Step 2 -->
<div class="col-lg-4 d-flex" data-aos="flip-up" data-aos-delay="200">
    <div class="work-card w-100" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-glare="true" data-tilt-max-glare="0.2">
        <div class="step-icon"><i class="fas fa-user-check"></i></div>
        <span class="step-badge">02</span>
        <h4>Admin Verification</h4>
        <p>Our dedicated team carefully reviews each listing to verify authenticity and quality. We ensure only genuine properties appear, giving buyers confidence and trust in every listing.</p>
    </div>
</div>

<!-- Step 3 -->
<div class="col-lg-4 d-flex" data-aos="flip-up" data-aos-delay="300">
    <div class="work-card w-100" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-glare="true" data-tilt-max-glare="0.2">
        <div class="step-icon"><i class="fas fa-globe-asia"></i></div>
        <span class="step-badge">03</span>
        <h4>Listing Goes Live</h4>
        <p>Once verified, your property is published on our platform and becomes visible to thousands of potential buyers actively searching for their dream home across India.</p>
    </div>
</div>

<!-- Step 4 -->
<div class="col-lg-4 d-flex" data-aos="flip-up" data-aos-delay="400">
    <div class="work-card w-100" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-glare="true" data-tilt-max-glare="0.2">
        <div class="step-icon"><i class="fas fa-bullhorn"></i></div>
        <span class="step-badge">04</span>
        <h4>We Promote It</h4>
        <p>We don't just list – we actively promote! Our marketing experts run targeted digital campaigns across multiple channels to attract qualified buyers – completely free for you.</p>
    </div>
</div>

<!-- Step 5 -->
<div class="col-lg-4 d-flex" data-aos="fade-up" data-aos-delay="500">
    <div class="work-card w-100" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-glare="true" data-tilt-max-glare="0.2">
        <div class="step-icon"><i class="fas fa-handshake"></i></div>
        <span class="step-badge">05</span>
        <h4>Buyers Contact Sellers</h4>
        <p>Buyers can directly contact sellers through our secure messaging system. They can ask questions, schedule site visits, and negotiate – all without any middlemen.</p>
    </div>
</div>

<!-- Step 6 -->
<div class="col-lg-4 d-flex" data-aos="fade-up" data-aos-delay="600">
    <div class="work-card w-100" data-tilt data-tilt-max="10" data-tilt-speed="400" data-tilt-glare="true" data-tilt-max-glare="0.2">
        <div class="step-icon"><i class="fas fa-check-double"></i></div>
        <span class="step-badge">06</span>
        <h4>Successful Deal</h4>
        <p>When the deal is successfully closed, we charge a small, agreed‑upon service fee. Our success is tied to yours – we only earn when you sell.</p>
    </div>
</div>

</div>

</div>

</section>

<!-- ================= META ADS SECTION ================= -->

<section class="marketing-section py-5" data-aos="fade-up">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6" data-aos="zoom-in-right">

<img src="assets/images/digital.jpg"

class="img-fluid marketing-img">

</div>

<div class="col-lg-6" data-aos="zoom-in-left">

<span class="marketing-badge">

🚀 Digital Marketing

</span>

<h2 class="display-5 fw-bold mt-3">

We Don't Just List

Your Property.

We Market It.

</h2>

<p class="lead mt-4">

Unlike ordinary property websites,

our platform actively promotes approved

properties using powerful online marketing.

Our goal is to bring more genuine buyers

to your property and help you sell faster.

</p>

<!-- removed the row with platform icons -->

<a href="<?= BASE_URL ?>/register.php"

class="btn btn-warning btn-lg mt-4">

Start Listing FREE

</a>

</div>

</div>

</div>

</section>





<!-- ================= WHY CHOOSE US ================= -->

<section class="why-section py-5 bg-light" data-aos="fade-up">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-5 fw-bold">

Why Choose RealEstate?

</h2>

<p class="lead text-muted">

Everything you need in one trusted platform.

</p>

</div>

<div class="row g-4">

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="50">

<div class="why-card">

<div class="why-icon">🏠</div>

<h4>Free Listing</h4>

<p>

No charges to upload

your property.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="100">

<div class="why-card">

<div class="why-icon">✔</div>

<h4>Verified Listings</h4>

<p>

Every property is approved

by admin.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="150">

<div class="why-card">

<div class="why-icon">📢</div>

<h4>Marketing Support</h4>

<p>

We promote listings

to reach more buyers.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="200">

<div class="why-card">

<div class="why-icon">🤝</div>

<h4>Direct Deals</h4>

<p>

Buyers and sellers

communicate directly.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="250">

<div class="why-card">

<div class="why-icon">🏢</div>

<h4>Trusted Brokers</h4>

<p>

Professional brokers

available for help.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="300">

<div class="why-card">

<div class="why-icon">🔒</div>

<h4>Secure Platform</h4>

<p>

Safe and transparent

property marketplace.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="350">

<div class="why-card">

<div class="why-icon">💬</div>

<h4>Easy Communication</h4>

<p>

Chat with buyers,

sellers and brokers.

</p>

</div>

</div>

<div class="col-md-3" data-aos="zoom-in" data-aos-delay="400">

<div class="why-card">

<div class="why-icon">💰</div>

<h4>Success Based</h4>

<p>

Platform earns only

after successful deals.

</p>

</div>

</div>

</div>

</div>

</section>





<!-- ================= SERVICES ================= -->

<section class="py-5" data-aos="fade-up">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-5 fw-bold">

Our Services

</h2>

<p class="lead text-muted">

Helping buyers, sellers and brokers

at every stage.

</p>

</div>

<div class="row g-4">

<div class="col-md-4" data-aos="flip-left" data-aos-delay="100">

<div class="service-card">

<img src="assets/images/buy.jpg">

<h4>Buy Property</h4>

<p>

Find verified homes,

plots and commercial spaces.

</p>

</div>

</div>

<div class="col-md-4" data-aos="flip-left" data-aos-delay="200">

<div class="service-card">

<img src="assets/images/sale.jpg">

<h4>Sell Property</h4>

<p>

Upload unlimited properties

absolutely FREE.

</p>

</div>

</div>

<div class="col-md-4" data-aos="flip-left" data-aos-delay="300">

<div class="service-card">

<img src="assets/images/broker.jpg">

<h4>Broker Services</h4>

<p>

Professional brokers help

buyers and sellers.

</p>

</div>

</div>

</div>

</div>

</section>





<!-- ================= STATS ================= -->

<section class="stats-section" data-aos="fade-up">

<div class="container">

<div class="row text-center">

<div class="col-md-3" data-aos="counter-up" data-aos-delay="100">

<h2 class="counter">

500+

</h2>

<p>

Properties Listed

</p>

</div>

<div class="col-md-3" data-aos="counter-up" data-aos-delay="200">

<h2 class="counter">

1200+

</h2>

<p>

Happy Users

</p>

</div>

<div class="col-md-3" data-aos="counter-up" data-aos-delay="300">

<h2 class="counter">

300+

</h2>

<p>

Successful Deals

</p>

</div>

<div class="col-md-3" data-aos="counter-up" data-aos-delay="400">

<h2 class="counter">

99%

</h2>

<p>

Customer Satisfaction

</p>

</div>

</div>

</div>

</section>

<!-- ================= TESTIMONIALS ================= -->

<section class="testimonial-section py-5" data-aos="fade-up">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-5 fw-bold">

What Our Users Say

</h2>

<p class="lead text-muted">

Trusted by buyers, sellers and brokers.

</p>

</div>

<div class="row g-4">

<div class="col-lg-4" data-aos="fade-right" data-aos-delay="100">

<div class="testimonial-card">

<img src="<?= BASE_URL ?>/assets/images/user1.jpg">

<h4>Rahul Sharma</h4>

★★★★★

<p>

I listed my property for free and received several genuine enquiries within a few days. The promotion really helped.

</p>

</div>

</div>

<div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">

<div class="testimonial-card">

<img src="<?= BASE_URL ?>/assets/images/user2.jpg">

<h4>Priya Nair</h4>

★★★★★

<p>

The admin verification gave me confidence that I was browsing genuine properties. The experience was smooth.

</p>

</div>

</div>

<div class="col-lg-4" data-aos="fade-left" data-aos-delay="300">

<div class="testimonial-card">

<img src="<?= BASE_URL ?>/assets/images/user3.jpg">

<h4>Arjun Patel</h4>

★★★★★

<p>

Connecting with brokers and sellers through one platform made my property search much easier.

</p>

</div>

</div>

</div>

</div>

</section>





<!-- ================= FAQ ================= -->

<section class="faq-section py-5 bg-light" data-aos="fade-up">

<div class="container">

<div class="text-center mb-5">

<h2 class="display-5 fw-bold">

Frequently Asked Questions

</h2>

</div>

<div class="accordion" id="faq">

<div class="accordion-item" data-aos="fade-right" data-aos-delay="50">

<h2 class="accordion-header">

<button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#q1">

Is property listing free?

</button>

</h2>

<div id="q1" class="accordion-collapse collapse show">

<div class="accordion-body">

Yes. Sellers can upload properties completely free.

</div>

</div>

</div>

<div class="accordion-item" data-aos="fade-left" data-aos-delay="100">

<h2 class="accordion-header">

<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q2">

How does the platform earn money?

</button>

</h2>

<div id="q2" class="accordion-collapse collapse">

<div class="accordion-body">

The platform receives an agreed service fee or commission only after a successful property transaction.

</div>

</div>

</div>

<div class="accordion-item" data-aos="fade-right" data-aos-delay="150">

<h2 class="accordion-header">

<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q3">

Who promotes my property?

</button>

</h2>

<div id="q3" class="accordion-collapse collapse">

<div class="accordion-body">

Our team promotes approved properties using digital marketing strategies, helping attract more genuine buyers.

</div>

</div>

</div>

<div class="accordion-item" data-aos="fade-left" data-aos-delay="200">

<h2 class="accordion-header">

<button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#q4">

Can buyers contact sellers directly?

</button>

</h2>

<div id="q4" class="accordion-collapse collapse">

<div class="accordion-body">

Yes. Buyers and sellers communicate directly through the platform.

</div>

</div>

</div>

</div>

</div>

</section>





<!-- ================= CALL TO ACTION ================= -->

<section class="cta-section" data-aos="zoom-in">

<div class="cta-circle cta-circle-1"></div>

<div class="cta-circle cta-circle-2"></div>

<div class="container text-center">

<h2 class="display-4 fw-bold">

Ready To Sell Your Property?

</h2>

<p class="lead">

Join India's fastest growing smart property marketplace.

</p>

<a href="<?= BASE_URL ?>/register.php" class="btn btn-warning btn-lg px-5">

Register FREE

</a>

</div>

</section>

<?php include __DIR__.'/includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Vanilla Tilt JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/vanilla-tilt/1.8.1/vanilla-tilt.min.js"></script>

<!-- AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Init AOS
    AOS.init({
        duration: 800,
        once: true,
        easing: 'ease-in-out'
    });

    // Init Vanilla Tilt for 3D mouse-follow effect
    VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
        max: 12,
        speed: 400,
        glare: true,
        "max-glare": 0.15,
        scale: 1.02,
        perspective: 800,
    });
</script>

</body>

</html>