// =====================================================
// MAIN.JS – All JavaScript for RealEstate Portal
// =====================================================

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {

    // =====================================================
    // AOS – Animate On Scroll
    // =====================================================
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-in-out'
        });
    }

    // =====================================================
    // Vanilla Tilt – 3D hover effect on cards
    // =====================================================
    if (typeof VanillaTilt !== 'undefined') {
        VanillaTilt.init(document.querySelectorAll("[data-tilt]"), {
            max: 12,
            speed: 400,
            glare: true,
            "max-glare": 0.15,
            scale: 1.02,
            perspective: 800,
        });
    }

    // =====================================================
    // SweetAlert2 – Confirm dialogs
    // =====================================================
    document.querySelectorAll('[data-confirm]').forEach(function(el) {
        el.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // =====================================================
    // Password Toggle (Login / Register pages)
    // =====================================================
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    });

    // =====================================================
    // Auto-dismiss alerts after 5 seconds
    // =====================================================
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // =====================================================
    // Handle Delete Property with SweetAlert2 (optional)
    // =====================================================
    document.querySelectorAll('.delete-property').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('href');
            if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
                window.location.href = url;
            }
        });
    });

    console.log('RealEstate Portal loaded successfully.');
});