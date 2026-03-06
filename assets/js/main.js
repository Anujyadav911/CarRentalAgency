/**
 * DriveEasy Rentals — Client-side JavaScript
 *
 * Responsibilities:
 *  1. Live rental-cost calculator on the Available Cars page
 *  2. Enforce minimum date (today) on date pickers
 *  3. Bootstrap tooltip activation
 *  4. Password visibility toggle
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Live rental-cost calculator ─────────────────────────────────────
    // Each booking form carries data-price-per-day on its container element.
    document.querySelectorAll('.booking-form').forEach(form => {
        const daysSelect  = form.querySelector('.days-select');
        const startDate   = form.querySelector('.start-date');
        const pricePerDay = parseFloat(form.dataset.pricePerDay || '0');
        const totalSpan   = form.querySelector('.total-amount');

        if (!daysSelect || !totalSpan) return;

        const updateTotal = () => {
            const days  = parseInt(daysSelect.value, 10) || 1;
            const total = (days * pricePerDay).toFixed(2);
            totalSpan.textContent = '₹' + Number(total).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        };

        daysSelect.addEventListener('change', updateTotal);
        updateTotal(); // Initialise on page load
    });

    // ── 2. Restrict date inputs to today or later ───────────────────────────
    const todayISO = new Date().toISOString().split('T')[0];

    document.querySelectorAll('input[type="date"].future-date').forEach(input => {
        if (!input.min) {
            input.min = todayISO;
        }
    });

    // ── 3. Bootstrap tooltip activation ────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getOrCreateInstance(el);
    });

    // ── 4. Password visibility toggle ───────────────────────────────────────
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const input    = document.getElementById(targetId);
            if (!input) return;

            const isHidden  = input.type === 'password';
            input.type      = isHidden ? 'text' : 'password';
            const icon      = btn.querySelector('i');
            if (icon) {
                icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
            }
        });
    });

});
