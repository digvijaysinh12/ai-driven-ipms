import './bootstrap';

const toggleModal = (id, shouldShow) => {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.hidden = !shouldShow;
    modal.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
    document.body.classList.toggle('modal-open', shouldShow);
};

document.addEventListener('click', (event) => {
    const openTrigger = event.target.closest('[data-modal-open]');
    const closeTrigger = event.target.closest('[data-modal-close]');

    if (openTrigger) {
        event.preventDefault();
        toggleModal(openTrigger.getAttribute('data-modal-open'), true);
    }

    if (closeTrigger) {
        event.preventDefault();
        toggleModal(closeTrigger.getAttribute('data-modal-close'), false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    document.querySelectorAll('.ui-modal').forEach((modal) => {
        if (!modal.hidden) {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
        }
    });

    document.body.classList.remove('modal-open');
});
