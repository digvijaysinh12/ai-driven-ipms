/**
 * questions.js
 * Handles: approve/reject question via AJAX (no page reload), counter update
 */
document.addEventListener('DOMContentLoaded', function () {

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const counter   = document.getElementById('approved-counter');

    // Approve / Reject buttons
    document.querySelectorAll('[data-action="approve"], [data-action="reject"]').forEach(btn => {
        btn.addEventListener('click', async function () {
            const action     = this.dataset.action;
            const questionId = this.dataset.questionId;
            const card       = document.getElementById(`question-card-${questionId}`);

            btn.disabled = true;
            btn.textContent = action === 'approve' ? 'Approving…' : 'Rejecting…';

            try {
                const res = await fetch(`/mentor/questions/${questionId}/${action}`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await res.json();

                if (data.success) {
                    if (action === 'reject') {
                        card?.remove();
                    } else {
                        card?.classList.add('question-approved');
                        btn.textContent = '✓ Approved';
                        btn.style.color = '#1a6a1a';
                    }
                    // Update counter
                    if (counter && data.approvedCount !== undefined) {
                        counter.textContent = data.approvedCount;
                    }
                } else {
                    alert(data.error || 'Action failed.');
                    btn.disabled = false;
                    btn.textContent = action === 'approve' ? 'Approve' : 'Reject';
                }
            } catch (e) {
                alert('Network error.');
                btn.disabled = false;
                btn.textContent = action === 'approve' ? 'Approve' : 'Reject';
            }
        });
    });
});