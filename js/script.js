// Validar formulario antes de enviar
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formReclamacion');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validarFormulario()) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Cargar áreas según departamento
    const selectDepartamento = document.getElementById('departamento');
    if (selectDepartamento) {
        cargarAreas(selectDepartamento.value);
        selectDepartamento.addEventListener('change', function() {
            cargarAreas(this.value);
        });
    }
});

// Validar DNI/CE
function validarDNI(dni) {
    const patron = /^[0-9]{8}$|^[A-Z]{2}[0-9]{6}$/;
    return patron.test(dni.trim());
}

// Validar email
function validarEmailInput(email) {
    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return patron.test(email);
}

// Validar teléfono
function validarTelefono(telefono) {
    const patron = /^[0-9]{7,15}$/;
    return patron.test(telefono.replace(/\s/g, '').replace(/-/g, ''));
}

// Validar formulario completo
function validarFormulario() {
    let valido = true;
    
    // Campos requeridos
    const camposRequeridos = [
        'campus', 'departamento', 'area', 'nombres', 'apellido_paterno',
        'apellido_materno', 'dni_ce', 'email', 'telefono', 'domicilio',
        'tipo_bien', 'descripcion_asunto', 'tipo_registro', 'detalle_reclamacion'
    ];
    
    camposRequeridos.forEach(campo => {
        const input = document.getElementById(campo);
        if (input && input.value.trim() === '') {
            mostrarError(input, 'Este campo es requerido');
            valido = false;
        } else if (input) {
            limpiarError(input);
        }
    });
    
    // Validación específica de DNI
    const dni = document.getElementById('dni_ce');
    if (dni && dni.value.trim() !== '' && !validarDNI(dni.value)) {
        mostrarError(dni, 'DNI inválido (8 dígitos o formato CE válido)');
        valido = false;
    }
    
    // Validación de email
    const email = document.getElementById('email');
    if (email && email.value.trim() !== '' && !validarEmailInput(email.value)) {
        mostrarError(email, 'Email inválido');
        valido = false;
    }
    
    // Validación de teléfono
    const telefono = document.getElementById('telefono');
    if (telefono && telefono.value.trim() !== '' && !validarTelefono(telefono.value)) {
        mostrarError(telefono, 'Teléfono inválido');
        valido = false;
    }
    
    return valido;
}

// Mostrar error en campo
function mostrarError(input, mensaje) {
    input.classList.add('is-invalid');
    let error = input.nextElementSibling;
    if (!error || !error.classList.contains('error-message')) {
        error = document.createElement('small');
        error.classList.add('error-message');
        input.parentNode.insertBefore(error, input.nextSibling);
    }
    error.textContent = mensaje;
    error.style.color = '#e74c3c';
    error.style.display = 'block';
}

// Limpiar error
function limpiarError(input) {
    input.classList.remove('is-invalid');
    const error = input.nextElementSibling;
    if (error && error.classList.contains('error-message')) {
        error.remove();
    }
}

// Cargar áreas según departamento
function cargarAreas(departamento) {
    const selectArea = document.getElementById('area');
    if (!selectArea) return;

    selectArea.innerHTML = '<option value="">--- Selecciona un área ---</option>';

    if (!departamento || !window.departamentoAreas || !window.departamentoAreas[departamento]) {
        return;
    }

    window.departamentoAreas[departamento].forEach(area => {
        const option = document.createElement('option');
        option.value = area;
        option.textContent = area;
        selectArea.appendChild(option);
    });
}

// Validar tamaño de archivo
function validarArchivo(input) {
    const archivo = input.files[0];
    if (!archivo) return true;
    
    const tamanioMaximo = 5 * 1024 * 1024; // 5 MB
    const extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
    
    const extension = archivo.name.split('.').pop().toLowerCase();
    
    if (archivo.size > tamanioMaximo) {
        mostrarError(input, 'El archivo es demasiado grande (máximo 5 MB)');
        return false;
    }
    
    if (!extensionesPermitidas.includes(extension)) {
        mostrarError(input, 'Tipo de archivo no permitido (pdf, jpg, jpeg, png, doc, docx)');
        return false;
    }
    
    limpiarError(input);
    return true;
}

// Copiar texto
function copiarTexto(texto) {
    const temp = document.createElement('textarea');
    temp.value = texto;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    alert('¡Folio copiado al portapapeles!');
}
