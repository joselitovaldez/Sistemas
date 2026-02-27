/* Mi Perfil JS (extraido de inline) */

function activateTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    const content = document.getElementById(tabId);
    if (content) content.classList.add('active');

    const button = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
    if (button) button.classList.add('active');
}

function togglePasswordByButton(button) {
    const inputId = button.dataset.target;
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
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

function initPasswordStrength() {
    const passwordInput = document.getElementById('pass_nueva');
    if (!passwordInput) return;

    const strengthContainer = document.getElementById('password-strength');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');

    passwordInput.addEventListener('input', function () {
        const password = this.value;

        if (!strengthContainer || !strengthBar || !strengthText) return;

        if (password.length === 0) {
            strengthContainer.classList.remove('show');
            return;
        }

        strengthContainer.classList.add('show');

        let strength = 0;
        if (password.length >= 6) strength += 1;
        if (password.length >= 8) strength += 1;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
        if (/\d/.test(password)) strength += 1;
        if (/[^a-zA-Z0-9]/.test(password)) strength += 1;

        strengthBar.className = 'strength-bar-fill';
        strengthText.className = 'strength-text';

        if (strength <= 2) {
            strengthBar.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = 'Debil';
        } else if (strength <= 4) {
            strengthBar.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = 'Media';
        } else {
            strengthBar.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = 'Fuerte';
        }
    });
}

document.addEventListener('click', event => {
    const tabBtn = event.target.closest('.tab-btn[data-tab]');
    if (tabBtn) {
        event.preventDefault();
        activateTab(tabBtn.dataset.tab);
        return;
    }

    const actionEl = event.target.closest('[data-action]');
    if (!actionEl) return;

    if (actionEl.dataset.action === 'toggle-password') {
        event.preventDefault();
        togglePasswordByButton(actionEl);
    }

    if (actionEl.dataset.action === 'open-foto-tab') {
        event.preventDefault();
        activateTab('foto');
    }
});

document.addEventListener('DOMContentLoaded', () => {
    initPasswordStrength();
});
