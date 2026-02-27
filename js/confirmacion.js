/* Confirmacion JS (extraido de inline) */

document.addEventListener('click', event => {
    const target = event.target.closest('[data-action="copy-folio"]');
    if (!target) return;

    event.preventDefault();
    const folio = target.dataset.folio;
    if (folio && typeof copiarTexto === 'function') {
        copiarTexto(folio);
    }
});
