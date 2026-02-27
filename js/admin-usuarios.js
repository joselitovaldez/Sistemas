/* Admin - Usuarios JS (extraido de inline) */

function mostrarAlerta(mensaje) {
    alert(mensaje);
}

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

async function crearUsuario(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('ajax_crear_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Usuario creado correctamente');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo crear el usuario');
        }
    } catch (error) {
        mostrarAlerta('Error al crear usuario');
    }
}

async function toggleUsuario(usuarioId) {
    const formData = new FormData();
    formData.append('usuario_id', usuarioId);

    try {
        const response = await fetch('ajax_toggle_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Estado actualizado');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo actualizar el estado');
        }
    } catch (error) {
        mostrarAlerta('Error al actualizar estado');
    }
}

async function eliminarUsuario(usuarioId) {
    if (!confirm('¿Seguro que deseas eliminar este usuario?')) {
        return;
    }

    const formData = new FormData();
    formData.append('usuario_id', usuarioId);

    try {
        const response = await fetch('ajax_eliminar_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            mostrarAlerta('Usuario eliminado');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo eliminar el usuario');
        }
    } catch (error) {
        mostrarAlerta('Error al eliminar usuario');
    }
}

function handleAction(actionEl) {
    const action = actionEl.dataset.action;
    const id = actionEl.dataset.id;

    switch (action) {
        case 'toggle-password':
            togglePasswordByData(actionEl.dataset.target, actionEl.dataset.icon);
            break;
        case 'toggle-usuario':
            if (id) toggleUsuario(id);
            break;
        case 'eliminar-usuario':
            if (id) eliminarUsuario(id);
            break;
        default:
            break;
    }
}

document.addEventListener('click', event => {
    const actionEl = event.target.closest('[data-action]');
    if (actionEl) {
        event.preventDefault();
        handleAction(actionEl);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCrearUsuario');
    if (form) form.addEventListener('submit', crearUsuario);
});
