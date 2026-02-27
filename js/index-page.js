/* Index JS (extraido de inline) */

function cargarDatosDepartamentos() {
    const dataEl = document.getElementById('departamento-areas-data');
    if (!dataEl || !dataEl.textContent) return;
    try {
        window.departamentoAreas = JSON.parse(dataEl.textContent);
    } catch (error) {
        window.departamentoAreas = {};
    }
}

function bindArchivoChange() {
    const fileInput = document.getElementById('archivo');
    if (!fileInput) return;

    fileInput.addEventListener('change', () => {
        if (typeof validarArchivo === 'function') {
            validarArchivo(fileInput);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    cargarDatosDepartamentos();
    bindArchivoChange();
});
