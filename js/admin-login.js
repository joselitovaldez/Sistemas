/* Admin - Login JS (extraido de inline) */

function togglePasswordByData(targetId, iconId) {
    const input = document.getElementById(targetId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

document.addEventListener('click', event => {
    const actionEl = event.target.closest('[data-action]');
    if (!actionEl) return;

    if (actionEl.dataset.action === 'toggle-password') {
        event.preventDefault();
        togglePasswordByData(actionEl.dataset.target, actionEl.dataset.icon);
    }
});
