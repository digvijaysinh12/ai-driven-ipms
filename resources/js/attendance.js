/**
 * attendance.js
 * Handles: check-in, check-out, timer display, IP validation feedback
 */
document.addEventListener('DOMContentLoaded', function () {

    const checkInBtn  = document.getElementById('btn-checkin');
    const checkOutBtn = document.getElementById('btn-checkout');
    const timerEl     = document.getElementById('checkin-timer');
    const csrfToken   = document.querySelector('meta[name="csrf-token"]').content;

    // Start live timer if already checked in
    if (timerEl && timerEl.dataset.checkinTime) {
        startTimer(new Date(timerEl.dataset.checkinTime));
    }

    if (checkInBtn) {
        checkInBtn.addEventListener('click', async function () {
            checkInBtn.disabled = true;
            checkInBtn.textContent = 'Checking in…';
            try {
                const res = await fetch('/intern/attendance/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Check-in failed. Make sure you are on the office network.');
                    checkInBtn.disabled = false;
                    checkInBtn.textContent = 'Check In';
                }
            } catch (e) {
                alert('Network error. Please try again.');
                checkInBtn.disabled = false;
                checkInBtn.textContent = 'Check In';
            }
        });
    }

    if (checkOutBtn) {
        checkOutBtn.addEventListener('click', async function () {
            if (!confirm('Check out now?')) return;
            checkOutBtn.disabled = true;
            checkOutBtn.textContent = 'Checking out…';
            try {
                const res = await fetch('/intern/attendance/check-out', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Check-out failed.');
                    checkOutBtn.disabled = false;
                    checkOutBtn.textContent = 'Check Out';
                }
            } catch (e) {
                alert('Network error.');
                checkOutBtn.disabled = false;
                checkOutBtn.textContent = 'Check Out';
            }
        });
    }

    function startTimer(startTime) {
        function update() {
            const diff = Math.floor((Date.now() - startTime) / 1000);
            const h = Math.floor(diff / 3600).toString().padStart(2, '0');
            const m = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
            const s = (diff % 60).toString().padStart(2, '0');
            if (timerEl) timerEl.textContent = `${h}:${m}:${s}`;
        }
        update();
        setInterval(update, 1000);
    }
});


