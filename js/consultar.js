/* Consultar Reclamacion JS (extraido de inline) */

document.addEventListener('click', event => {
    const actionEl = event.target.closest('[data-action]');
    if (!actionEl) return;

    const action = actionEl.dataset.action;
    if (action === 'copy-folio') {
        event.preventDefault();
        const folio = actionEl.dataset.folio;
        if (folio && typeof copiarTexto === 'function') {
            copiarTexto(folio);
        }
        return;
    }

    if (action === 'reload-page') {
        event.preventDefault();
        window.location.reload();
        return;
    }

    if (action === 'new-search') {
        event.preventDefault();
        window.location.href = 'consultar_reclamacion.php';
    }
});
