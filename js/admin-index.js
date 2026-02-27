/* Admin - Index JS (extraido de inline) */

const sectionTitles = {
    dashboard: 'Dashboard',
    reclamaciones: 'Gestionar Reclamaciones',
    usuarios: 'Gestionar Usuarios',
    reportes: 'Reportes y Estadisticas',
    perfil: 'Mi Perfil',
    roles: 'Gestion de Roles y Areas'
};

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    if (sidebar) sidebar.classList.toggle('collapsed');
    if (mainContent) mainContent.classList.toggle('expanded');
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userMenuDropdown');
    if (dropdown) dropdown.classList.toggle('show');
}

function closeUserMenu() {
    const dropdown = document.getElementById('userMenuDropdown');
    if (dropdown) dropdown.classList.remove('show');
}

function showSection(sectionName) {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });

    const target = document.getElementById(`section-${sectionName}`);
    if (target) target.classList.add('active');

    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });

    const activeNav = document.querySelector(`.nav-item[data-section="${sectionName}"]`);
    if (activeNav) activeNav.classList.add('active');

    const title = sectionTitles[sectionName];
    const titleEl = document.getElementById('pageTitle');
    if (title && titleEl) titleEl.textContent = title;
}

function getEstadoClass(estado) {
    if (!estado) return 'estado-pendiente';
    const estadoNormalizado = estado.toLowerCase().trim();
    if (estadoNormalizado === 'resuelto') return 'estado-resuelto';
    if (estadoNormalizado === 'en revision' || estadoNormalizado === 'en revisión') return 'estado-revision';
    if (estadoNormalizado === 'no procede') return 'estado-no-procede';
    if (estadoNormalizado === 'pendiente') return 'estado-pendiente';
    return 'estado-pendiente';
}

function getEstadoIcon(estado) {
    if (!estado) return '📋';
    const estadoNormalizado = estado.toLowerCase().trim();
    if (estadoNormalizado === 'resuelto') return '✓';
    if (estadoNormalizado === 'en revision' || estadoNormalizado === 'en revisión') return '⏳';
    if (estadoNormalizado === 'no procede') return '✗';
    return '📋';
}

function verDetalleReclamo(id) {
    fetch(`ajax_ver_reclamo.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                mostrarAlerta(`Error: ${data.message || 'Error al cargar detalle'}`, 'error');
                return;
            }

            const r = data.reclamacion;
            const archivoNombre = r.archivo_adjunto ? r.archivo_adjunto.split('/').pop() : '';
            const archivoExt = archivoNombre && archivoNombre.includes('.') ? archivoNombre.split('.').pop().toLowerCase() : '';
            const esImagen = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(archivoExt);
            const nombreAdjunto = archivoExt ? (esImagen ? `foto.${archivoExt}` : `documento.${archivoExt}`) : 'documento';
            const estadoClass = getEstadoClass(r.estado);
            const estadoIcon = getEstadoIcon(r.estado);

            let contenido = '<div class="modal-content-wrapper">';

            contenido += `
                <div class="modal-hero-card">
                    <div class="modal-hero-content">
                        <div>
                            <p class="modal-folio-label">Folio del Reclamo</p>
                            <h2 class="modal-folio-number">${r.folio || 'N/A'}</h2>
                        </div>
                        <div class="modal-estado-wrapper">
                            <p class="modal-estado-label">Estado</p>
                            <div class="modal-estado-badge ${estadoClass}">
                                <span class="modal-estado-icon">${estadoIcon}</span>
                                <span class="modal-estado-text">${r.estado}</span>
                            </div>
                        </div>
                    </div>
                </div>`;

            contenido += `
                <div>
                    <h3 class="modal-section-header">
                        <i class="fas fa-user"></i> Datos del Reclamante
                    </h3>
                    <div class="modal-info-card modal-info-grid-vertical">`;

            if (r.nombres) {
                const nombreCompleto = `${r.nombres || ''} ${r.apellido_paterno || ''} ${r.apellido_materno || ''}`.trim();
                contenido += `
                    <div>
                        <label class="modal-field-label">
                            <i class="fas fa-id-card"></i> Nombre Completo
                        </label>
                        <p class="modal-field-value-strong">${nombreCompleto}</p>
                    </div>`;
            }

            if (r.dni_ce) {
                contenido += `
                    <div>
                        <label class="modal-field-label">
                            <i class="fas fa-fingerprint"></i> DNI/CE
                        </label>
                        <p class="modal-field-value">${r.dni_ce}</p>
                    </div>`;
            }

            if (r.telefono) {
                contenido += `
                    <div>
                        <label class="modal-field-label">
                            <i class="fas fa-phone"></i> Telefono
                        </label>
                        <p class="modal-field-value"><a href="tel:${r.telefono}" class="modal-field-link">${r.telefono}</a></p>
                    </div>`;
            }

            if (r.email) {
                contenido += `
                    <div>
                        <label class="modal-field-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <p class="modal-field-value"><a href="mailto:${r.email}" class="modal-field-link">${r.email}</a></p>
                    </div>`;
            }

            if (r.domicilio) {
                contenido += `
                    <div>
                        <label class="modal-field-label">
                            <i class="fas fa-map-marker-alt"></i> Domicilio
                        </label>
                        <p class="modal-field-value">${r.domicilio}</p>
                    </div>`;
            }

            contenido += '</div></div>';

            if (r.campus || r.departamento || r.area) {
                contenido += `
                    <div>
                        <h3 class="modal-section-header">
                            <i class="fas fa-building"></i> Ubicacion
                        </h3>
                        <div class="modal-info-card modal-info-grid-3">`;

                if (r.campus) {
                    contenido += `
                        <div>
                            <label class="modal-field-label">
                                <i class="fas fa-university"></i> Campus
                            </label>
                            <p class="modal-field-value">${r.campus}</p>
                        </div>`;
                }
                if (r.departamento) {
                    contenido += `
                        <div>
                            <label class="modal-field-label">
                                <i class="fas fa-sitemap"></i> Departamento
                            </label>
                            <p class="modal-field-value">${r.departamento}</p>
                        </div>`;
                }
                if (r.area) {
                    contenido += `
                        <div>
                            <label class="modal-field-label">
                                <i class="fas fa-map-pin"></i> Area
                            </label>
                            <p class="modal-field-value">${r.area}</p>
                        </div>`;
                }

                contenido += '</div></div>';
            }

            contenido += `
                <div>
                    <h3 class="modal-section-header">
                        <i class="fas fa-info-circle"></i> Informacion del Reclamo
                    </h3>
                    <div class="modal-grid-gap">`;

            if (r.fecha_registro || r.tipo_bien || r.tipo_registro) {
                contenido += '<div class="modal-info-card modal-info-grid-3">';

                if (r.fecha_registro) {
                    const fecha = `${new Date(r.fecha_registro).toLocaleDateString('es-PE', {year: 'numeric', month: 'short', day: 'numeric'})} ${new Date(r.fecha_registro).toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'})}`;
                    contenido += `
                        <div>
                            <label class="modal-field-label">
                                <i class="fas fa-calendar-alt"></i> Fecha Registro
                            </label>
                            <p class="modal-field-value">${fecha}</p>
                        </div>`;
                }
                if (r.tipo_bien) {
                    contenido += `
                        <div>
                            <label class="modal-field-label">
                                <i class="fas fa-box"></i> Tipo de Bien
                            </label>
                            <p class="modal-field-value">${r.tipo_bien}</p>
                        </div>`;
                }
                if (r.tipo_registro) {
                    contenido += `
                        <div>
                            <label class="modal-field-label">
                                <i class="fas fa-exclamation-circle"></i> Tipo de Reclamo
                            </label>
                            <p class="modal-field-value">${r.tipo_registro}</p>
                        </div>`;
                }
                contenido += '</div>';
            }

            if (r.descripcion_asunto) {
                contenido += `
                    <div class="modal-content-box">
                        <label class="modal-field-label modal-field-label-spaced">
                            <i class="fas fa-align-left"></i> Asunto/Descripcion
                        </label>
                        <div class="modal-content-text">${r.descripcion_asunto}</div>
                    </div>`;
            }

            if (r.detalle_reclamacion) {
                contenido += `
                    <div class="modal-content-box">
                        <label class="modal-field-label modal-field-label-spaced">
                            <i class="fas fa-clipboard-list icon-warning"></i> Detalle de la Reclamacion
                        </label>
                        <div class="modal-content-text-warning">${r.detalle_reclamacion}</div>
                    </div>`;
            }

            if (r.pedido) {
                contenido += `
                    <div class="modal-content-box">
                        <label class="modal-field-label modal-field-label-spaced">
                            <i class="fas fa-hand-holding icon-warning"></i> Pedido del Consumidor
                        </label>
                        <div class="modal-content-text-info">${r.pedido}</div>
                    </div>`;
            }

            const labelAdjunto = esImagen ? 'Foto Adjunta' : 'Documento Adjunto';
            const iconoAdjunto = esImagen ? 'fa-image' : 'fa-file-pdf';
            if (r.archivo_adjunto) {
                contenido += `
                    <div class="modal-content-box">
                        <label class="modal-field-label modal-field-label-spaced">
                            <i class="fas ${iconoAdjunto} icon-info"></i> ${labelAdjunto}
                        </label>
                        <div class="modal-attachment-box">
                            <div class="modal-attachment-name">
                                <i class="fas ${iconoAdjunto}"></i> ${nombreAdjunto}
                            </div>
                            <a href="../descargar.php?archivo=${encodeURIComponent(r.archivo_adjunto)}" download class="modal-download-btn">
                                <i class="fas fa-download"></i> Descargar
                            </a>
                        </div>
                    </div>`;
            } else {
                contenido += `
                    <div class="modal-content-box">
                        <label class="modal-field-label modal-field-label-spaced">
                            <i class="fas fa-paperclip icon-muted"></i> Documento Adjunto
                        </label>
                        <div class="modal-no-attachment">No se adjunto documento</div>
                    </div>`;
            }

            contenido += '</div></div>';

            if (r.respuesta || r.fecha_respuesta) {
                contenido += `
                    <div>
                        <h3 class="modal-section-header">
                            <i class="fas fa-check-circle icon-success"></i> Respuesta
                        </h3>
                        <div class="modal-grid-gap">`;

                if (r.respuesta) {
                    contenido += `
                        <div class="modal-content-box">
                            <label class="modal-field-label modal-field-label-spaced">
                                <i class="fas fa-reply icon-success"></i> Respuesta Oficial
                            </label>
                            <div class="modal-content-text-success">${r.respuesta}</div>
                        </div>`;
                }

                if (r.fecha_respuesta) {
                    const fechaResp = `${new Date(r.fecha_respuesta).toLocaleDateString('es-PE', {year: 'numeric', month: 'short', day: 'numeric'})} a las ${new Date(r.fecha_respuesta).toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'})}`;
                    contenido += `
                        <div class="modal-content-box">
                            <label class="modal-field-label">
                                <i class="fas fa-clock icon-success"></i> Fecha de Respuesta
                            </label>
                            <p class="modal-field-value modal-field-value-strong">${fechaResp}</p>
                        </div>`;
                }

                contenido += '</div></div>';
            }

            contenido += `
                <div class="modal-actions">
                    <button type="button" class="modal-btn modal-btn-success" data-action="responder-modal" data-id="${r.id}">
                        <i class="fas fa-reply"></i> Responder
                    </button>
                    <button type="button" class="modal-btn modal-btn-primary" data-action="derivar-modal" data-id="${r.id}">
                        <i class="fas fa-share"></i> Derivar
                    </button>
                </div>`;

            contenido += '</div>';

            const contenidoModal = document.getElementById('contenidoModalDetalle');
            if (contenidoModal) contenidoModal.innerHTML = contenido;

            const modalDetalle = document.getElementById('modalDetalleReclamo');
            if (modalDetalle) modalDetalle.classList.add('show');
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta(`Error de conexion: ${error.message}`, 'error');
        });
}

function cerrarModalDetalle() {
    const modal = document.getElementById('modalDetalleReclamo');
    if (modal) modal.classList.remove('show');
}

function abrirModalCrearUsuario() {
    const modal = document.getElementById('modalCrearUsuario');
    const form = document.getElementById('formCrearUsuario');
    if (form) form.reset();
    if (modal) modal.classList.add('show');

    const input = document.getElementById('nuevo_usuario');
    if (input) input.focus();
}

function cerrarModalCrearUsuario() {
    const modal = document.getElementById('modalCrearUsuario');
    if (modal) modal.classList.remove('show');
}

function responderReclamoModal(id) {
    fetch(`ajax_ver_reclamo.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.reclamacion) {
                const r = data.reclamacion;
                const folio = document.getElementById('responderFolio');
                if (folio) folio.textContent = r.folio || 'N/A';
                const textarea = document.getElementById('textareaRespuesta');
                if (textarea) {
                    textarea.value = '';
                    textarea.focus();
                }

                const modal = document.getElementById('modalResponderReclamo');
                if (modal) {
                    modal.dataset.reclamoId = id;
                    modal.classList.add('is-open');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al cargar el reclamo', 'error');
        });
}

function cerrarModalResponder() {
    const modal = document.getElementById('modalResponderReclamo');
    if (modal) modal.classList.remove('is-open');

    const textarea = document.getElementById('textareaRespuesta');
    if (textarea) textarea.value = '';
}

function guardarRespuesta() {
    const modal = document.getElementById('modalResponderReclamo');
    if (!modal) return;

    const id = modal.dataset.reclamoId;
    const textarea = document.getElementById('textareaRespuesta');
    const respuesta = textarea ? textarea.value.trim() : '';

    if (!respuesta) {
        mostrarAlerta('La respuesta no puede estar vacia', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('respuesta', respuesta);

    fetch('ajax_guardar_respuesta.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlerta('Respuesta guardada correctamente', 'success');
                cerrarModalResponder();
                verDetalleReclamo(id);
            } else {
                mostrarAlerta(data.message || 'Error al guardar respuesta', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al guardar respuesta', 'error');
        });
}

function derivarReclamoModal(id) {
    fetch(`ajax_ver_reclamo.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.reclamacion) {
                mostrarAlerta('Error al cargar reclamo', 'error');
                return;
            }

            const r = data.reclamacion;

            // Determinar clase de badge según estado
            let estadoBadgeClass = 'badge-info';
            if (r.estado) {
                const estadoNormalizado = r.estado.toLowerCase().trim();
                if (estadoNormalizado === 'resuelto') {
                    estadoBadgeClass = 'badge-success';
                } else if (estadoNormalizado === 'en revision' || estadoNormalizado === 'en revisión') {
                    estadoBadgeClass = 'badge-warning';
                } else if (estadoNormalizado === 'no procede') {
                    estadoBadgeClass = 'badge-danger';
                } else if (estadoNormalizado === 'pendiente') {
                    estadoBadgeClass = 'badge-info';
                }
            }

            fetch('ajax_get_departamentos.php')
                .then(response => response.json())
                .then(deptData => {
                    let html = `
                        <div class="derivar-summary">
                            <label class="derivar-summary-label">Resumen del Reclamo</label>
                            <p class="derivar-summary-line"><strong>Folio:</strong> ${r.folio || 'N/A'}</p>
                            <p class="derivar-summary-line"><strong>Tipo de Bien:</strong> ${r.tipo_bien || 'N/A'}</p>
                            <p class="derivar-summary-line"><strong>Tipo de Reclamo:</strong> ${r.tipo_registro || 'N/A'}</p>
                            <p class="derivar-summary-line"><strong>Estado Actual:</strong> <span class="derivar-estado-badge ${estadoBadgeClass}">${r.estado}</span></p>
                        </div>

                        <div class="derivar-form-group">
                            <label class="derivar-label">Departamento</label>
                            <select id="selectDepartamento" class="derivar-select" data-action="select-departamento">
                                <option value="">-- Selecciona un departamento --</option>`;

                    if (deptData.success && deptData.departamentos && deptData.departamentos.length > 0) {
                        deptData.departamentos.forEach(dept => {
                            html += `<option value="${dept.id}">${dept.nombre}</option>`;
                        });
                    } else {
                        html += '<option value="">No hay departamentos disponibles</option>';
                    }

                    html += `</select>
                        </div>

                        <div class="derivar-form-group">
                            <label class="derivar-label">Area</label>
                            <select id="selectArea" class="derivar-select" data-action="select-area">
                                <option value="">-- Selecciona un area --</option>
                            </select>
                        </div>

                        <div id="usuariosContainer" class="derivar-usuarios-container">
                            <label class="derivar-label">Usuario Asignado</label>
                            <div id="usuariosInfo" class="derivar-usuarios-info"></div>
                        </div>

                        <div class="derivar-actions">
                            <button type="button" class="btn btn-secondary" data-action="close-derivar">Cancelar</button>
                            <button type="button" class="btn btn-primary" data-action="save-derivar">
                                <i class="fas fa-share"></i> Derivar
                            </button>
                        </div>`;

                    const contenido = document.getElementById('contenidoDerivar');
                    if (contenido) contenido.innerHTML = html;

                    const modal = document.getElementById('modalDerivarReclamo');
                    if (modal) {
                        modal.dataset.reclamoId = id;
                        modal.classList.add('is-open');
                    }
                })
                .catch(error => {
                    console.error('Error al cargar departamentos:', error);
                    mostrarAlerta(`Error al cargar departamentos: ${error.message}`, 'error');
                });
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta(`Error: ${error.message}`, 'error');
        });
}

function cargarAreas(departamentoId) {
    const selectArea = document.getElementById('selectArea');
    const usuariosContainer = document.getElementById('usuariosContainer');

    if (!selectArea || !usuariosContainer) return;

    if (!departamentoId) {
        selectArea.innerHTML = '<option value="">-- Selecciona un area --</option>';
        usuariosContainer.classList.remove('is-visible');
        return;
    }

    const formData = new FormData();
    formData.append('departamento_id', departamentoId);

    fetch('ajax_get_areas.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar areas');
            return response.json();
        })
        .then(data => {
            let opciones = '<option value="">-- Selecciona un area --</option>';
            if (data.success && data.areas && data.areas.length > 0) {
                data.areas.forEach(area => {
                    opciones += `<option value="${area.id}">${area.nombre}</option>`;
                });
            } else {
                opciones += '<option value="">No hay areas disponibles</option>';
            }
            selectArea.innerHTML = opciones;
            usuariosContainer.classList.remove('is-visible');
        })
        .catch(error => {
            console.error('Error al cargar areas:', error);
            selectArea.innerHTML = '<option value="">Error al cargar areas</option>';
            mostrarAlerta(`Error: ${error.message}`, 'error');
        });
}

function cargarUsuariosArea() {
    const departamento = document.getElementById('selectDepartamento');
    const area = document.getElementById('selectArea');
    const usuariosContainer = document.getElementById('usuariosContainer');
    const usuariosInfo = document.getElementById('usuariosInfo');

    if (!departamento || !area || !usuariosContainer || !usuariosInfo) return;

    if (!departamento.value || !area.value) {
        usuariosContainer.classList.remove('is-visible');
        return;
    }

    const formData = new FormData();
    formData.append('departamento', departamento.value);
    formData.append('area', area.value);

    fetch('ajax_get_usuario_area.php', {
        method: 'POST',
        body: formData
    })
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar usuarios');
            return response.json();
        })
        .then(data => {
            if (data.success && data.usuarios && data.usuarios.length > 0) {
                let html = '';
                data.usuarios.forEach(usuario => {
                    const nombreCompleto = `${usuario.nombre} ${usuario.apellido_paterno || ''} ${usuario.apellido_materno || ''}`.trim();
                    html += `
                        <div class="usuarios-info-item">
                            <p class="usuarios-info-name">${nombreCompleto}</p>
                            <p class="usuarios-info-meta">📧 ${usuario.email || 'N/A'}</p>
                            <p class="usuarios-info-meta">📱 ${usuario.telefono || 'N/A'}</p>
                        </div>`;
                });
                usuariosInfo.innerHTML = html;
                usuariosContainer.classList.add('is-visible');

                const modal = document.getElementById('modalDerivarReclamo');
                if (modal) {
                    modal.dataset.usuarioAsignado = data.usuarios[0].id;
                    modal.dataset.usuarioNombre = `${data.usuarios[0].nombre} ${data.usuarios[0].apellido_paterno || ''}`.trim();
                }
            } else {
                usuariosInfo.innerHTML = '<p class="usuarios-info-error">No hay usuarios asignados a esta area</p>';
                usuariosContainer.classList.add('is-visible');
                const modal = document.getElementById('modalDerivarReclamo');
                if (modal) {
                    modal.dataset.usuarioAsignado = '';
                    modal.dataset.usuarioNombre = '';
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
            usuariosInfo.innerHTML = '<p class="usuarios-info-error">Error al cargar usuarios</p>';
            usuariosContainer.classList.add('is-visible');
        });
}

function cerrarModalDerivar() {
    const modal = document.getElementById('modalDerivarReclamo');
    if (modal) modal.classList.remove('is-open');
}

function guardarDerivacion() {
    const modal = document.getElementById('modalDerivarReclamo');
    if (!modal) return;

    const id = modal.dataset.reclamoId;
    const selectArea = document.getElementById('selectArea');
    const usuarioAsignado = modal.dataset.usuarioAsignado;

    if (!selectArea || !selectArea.value) {
        mostrarAlerta('Debes seleccionar un area', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('id', id);
    formData.append('area_destino', selectArea.value);
    formData.append('usuario_asignado', usuarioAsignado);

    fetch('ajax_derivar_reclamo.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const nombre = modal.dataset.usuarioNombre || 'el usuario asignado';
                mostrarAlerta(`Reclamo derivado correctamente a ${nombre}`, 'success');
                cerrarModalDerivar();
                cerrarModalDetalle();
                window.location.reload();
            } else {
                mostrarAlerta(data.message || 'Error al derivar reclamo', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlerta('Error al derivar reclamo', 'error');
        });
}

function responderReclamo(id) {
    mostrarAlerta('Funcion aun en desarrollo', 'info');
}

function derivarReclamo(id) {
    mostrarAlerta('Funcion aun en desarrollo', 'info');
}

function mostrarAlerta(mensaje, tipo = 'info', callback = null) {
    const overlay = document.getElementById('modalAlerta');
    const header = document.querySelector('.modal-alerta-header');
    const titulo = document.getElementById('modalAlertaTitulo');
    const contenido = document.getElementById('modalAlertaMensaje');
    const btn = document.getElementById('modalAlertaBtn');
    
    if (!overlay || !header || !titulo || !contenido || !btn) {
        console.warn('Elementos del modal no encontrados');
        alert(mensaje);
        if (callback) callback();
        return;
    }

    // Mapeo de tipos a iconos y títulos
    const typeConfig = {
        success: {
            icon: 'fas fa-check-circle',
            title: 'Éxito',
            class: 'modal-alerta-success',
            color: '#28a745'
        },
        error: {
            icon: 'fas fa-exclamation-circle',
            title: 'Error',
            class: 'modal-alerta-error',
            color: '#dc3545'
        },
        warning: {
            icon: 'fas fa-exclamation-triangle',
            title: 'Advertencia',
            class: 'modal-alerta-warning',
            color: '#ffc107'
        },
        info: {
            icon: 'fas fa-info-circle',
            title: 'Información',
            class: 'modal-alerta-info',
            color: '#3e85cc'
        }
    };

    const config = typeConfig[tipo] || typeConfig.info;

    // Actualizar header con clase apropiada
    header.className = 'modal-alerta-header ' + config.class;
    
    // Actualizar icono
    const iconElement = header.querySelector('.modal-alerta-icon');
    if (iconElement) {
        iconElement.className = `modal-alerta-icon ${config.icon}`;
    }

    // Actualizar título y mensaje
    titulo.textContent = config.title;
    contenido.textContent = mensaje;

    // Actualizar clase del botón según el tipo
    btn.className = '';
    if (tipo === 'error') {
        btn.className = 'btn-alerta-error';
    } else if (tipo === 'warning') {
        btn.className = 'btn-alerta-warning';
    } else if (tipo === 'info') {
        btn.className = 'btn-alerta-info';
    }
    // Si es success, no agrega clase (usa el estilo verde por defecto)

    // Mostrar modal
    overlay.classList.add('active');

    // Función para cerrar el modal
    const cerrarModal = function() {
        overlay.classList.remove('active');
        // Ejecutar callback si existe
        if (callback) {
            callback();
        }
        // Limpiar el listener del botón
        btn.onclick = null;
    };

    // Listener para cerrar SOLO al hacer clic en el botón
    btn.onclick = cerrarModal;
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
            mostrarAlerta('Usuario creado correctamente', 'success');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo crear el usuario', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al crear usuario', 'error');
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
            mostrarAlerta('Estado actualizado', 'success');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo actualizar el estado', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al actualizar estado', 'error');
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
            mostrarAlerta('Usuario eliminado', 'success');
            window.location.reload();
        } else {
            mostrarAlerta(data.message || 'No se pudo eliminar el usuario', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al eliminar usuario', 'error');
    }
}

function editarUsuario(usuarioId) {
    // Cargar datos del usuario mediante AJAX
    fetch(`ajax_obtener_usuario.php?id=${usuarioId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.usuario) {
                const usuario = data.usuario;
                // Llenar el formulario con los datos del usuario
                document.getElementById('editar_usuario_id').value = usuario.id;
                document.getElementById('editar_nombre').value = usuario.nombre || '';
                document.getElementById('editar_apellido_paterno').value = usuario.apellido_paterno || '';
                document.getElementById('editar_apellido_materno').value = usuario.apellido_materno || '';
                document.getElementById('editar_dni').value = usuario.dni || '';
                document.getElementById('editar_telefono').value = usuario.telefono || '';
                document.getElementById('editar_email').value = usuario.email || '';
                
                // Asignar el rol de forma más robusta
                const rolSelect = document.getElementById('editar_rol');
                rolSelect.value = usuario.rol || '';
                
                // Si el valor no se asignó correctamente, intentar nuevamente
                if (rolSelect.value !== usuario.rol && usuario.rol) {
                    setTimeout(() => {
                        rolSelect.value = usuario.rol;
                    }, 0);
                }
                
                document.getElementById('editar_usuario').value = usuario.usuario || '';
                
                // Mostrar el modal
                document.getElementById('modalEditarUsuario').classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                mostrarAlerta('No se pudieron cargar los datos del usuario', 'error');
            }
        })
        .catch(error => {
            mostrarAlerta('Error al cargar datos del usuario', 'error');
        });
}

function cerrarModalEditar() {
    document.getElementById('modalEditarUsuario').classList.remove('active');
    document.body.style.overflow = '';
}

function abrirModalPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function cerrarModalPerfil() {
    const modal = document.getElementById('modalEditarPerfil');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

async function guardarPerfil(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('ajax_actualizar_perfil.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            cerrarModalPerfil();
            mostrarAlerta('Perfil actualizado correctamente', 'success', function() {
                window.location.reload();
            });
        } else {
            mostrarAlerta(data.message || 'No se pudo actualizar el perfil', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al actualizar perfil', 'error');
    }
}

async function guardarEdicionUsuario(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('ajax_actualizar_usuario.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            // Cerrar modal de edición primero
            cerrarModalEditar();
            
            // Mostrar alerta de éxito con callback para recargar
            mostrarAlerta('Usuario actualizado correctamente', 'success', function() {
                window.location.reload();
            });
        } else {
            mostrarAlerta(data.message || 'No se pudo actualizar el usuario', 'error');
        }
    } catch (error) {
        mostrarAlerta('Error al actualizar usuario', 'error');
    }
}

function cambiarPasswordUsuario(usuarioId, nombreUsuario) {
    // Guardar el ID del usuario y su nombre en el modal
    document.getElementById('passwordUserId').value = usuarioId;
    document.getElementById('passwordUserName').textContent = nombreUsuario;
    
    // Limpiar campos
    document.getElementById('nuevaPassword').value = '';
    document.getElementById('confirmarPassword').value = '';
    document.getElementById('nuevaPassword').setCustomValidity('');
    document.getElementById('confirmarPassword').setCustomValidity('');
    document.querySelector('.strength-bar-fill').className = 'strength-bar-fill';
    document.querySelector('.strength-text').textContent = '';
    document.querySelector('.password-match-message').textContent = '';
    document.querySelector('.password-match-message').className = 'password-match-message';
    
    // Mostrar modal
    document.getElementById('modalCambiarPassword').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalPassword() {
    document.getElementById('modalCambiarPassword').classList.remove('active');
    document.body.style.overflow = '';
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = document.querySelector(`[data-target="${inputId}"]`);
    const icon = button.querySelector('i');
    
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

function checkPasswordStrength(password) {
    const strengthBar = document.querySelector('.strength-bar-fill');
    const strengthText = document.querySelector('.strength-text');
    
    if (!password) {
        strengthBar.className = 'strength-bar-fill';
        strengthText.textContent = '';
        return;
    }
    
    let strength = 0;
    
    // Criterios de fortaleza
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Clasificar fortaleza
    if (strength <= 2) {
        strengthBar.className = 'strength-bar-fill weak';
        strengthText.className = 'strength-text weak';
        strengthText.textContent = 'Débil';
    } else if (strength <= 4) {
        strengthBar.className = 'strength-bar-fill medium';
        strengthText.className = 'strength-text medium';
        strengthText.textContent = 'Media';
    } else {
        strengthBar.className = 'strength-bar-fill strong';
        strengthText.className = 'strength-text strong';
        strengthText.textContent = 'Fuerte';
    }
}

function checkPasswordMatch() {
    const nuevaPassword = document.getElementById('nuevaPassword').value;
    const confirmarPassword = document.getElementById('confirmarPassword').value;
    const matchMessage = document.querySelector('.password-match-message');
    
    if (!confirmarPassword) {
        matchMessage.textContent = '';
        matchMessage.className = 'password-match-message';
        return false;
    }
    
    if (nuevaPassword === confirmarPassword) {
        matchMessage.textContent = '✓ Las contraseñas coinciden';
        matchMessage.className = 'password-match-message match';
        return true;
    } else {
        matchMessage.textContent = '✗ Las contraseñas no coinciden';
        matchMessage.className = 'password-match-message no-match';
        return false;
    }
}

function guardarNuevaPassword() {
    const usuarioId = document.getElementById('passwordUserId').value;
    const nuevaPassword = document.getElementById('nuevaPassword').value;
    const confirmarPassword = document.getElementById('confirmarPassword').value;
    const nuevaPasswordInput = document.getElementById('nuevaPassword');
    const confirmarPasswordInput = document.getElementById('confirmarPassword');
    
    // Validaciones con tooltip nativo
    nuevaPasswordInput.setCustomValidity('');
    confirmarPasswordInput.setCustomValidity('');

    if (!nuevaPasswordInput.checkValidity()) {
        nuevaPasswordInput.reportValidity();
        return;
    }

    if (!confirmarPasswordInput.checkValidity()) {
        confirmarPasswordInput.reportValidity();
        return;
    }

    if (nuevaPassword !== confirmarPassword) {
        confirmarPasswordInput.setCustomValidity('Las contraseñas no coinciden');
        confirmarPasswordInput.reportValidity();
        checkPasswordMatch();
        return;
    }
    
    const formData = new FormData();
    formData.append('usuario_id', usuarioId);
    formData.append('nueva_password', nuevaPassword);
    
    fetch('ajax_cambiar_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Contraseña actualizada correctamente', 'success');
            cerrarModalPassword();
        } else {
            mostrarAlerta(data.message || 'Error al cambiar la contraseña', 'error');
        }
    })
    .catch(error => {
        mostrarAlerta('Error al cambiar la contraseña', 'error');
    });
}

function getJsonData(id) {
    const el = document.getElementById(id);
    if (!el || !el.textContent) return null;
    try {
        return JSON.parse(el.textContent);
    } catch (error) {
        return null;
    }
}

function initCharts() {
    const getOrCreateLegendList = (chart, id) => {
        const legendContainer = document.getElementById(id);
        if (!legendContainer) return null;
        let listContainer = legendContainer.querySelector('ul');

        if (!listContainer) {
            listContainer = document.createElement('ul');
            listContainer.className = 'chart-legend';
            legendContainer.appendChild(listContainer);
        }

        return listContainer;
    };

    const htmlLegendPlugin = {
        id: 'htmlLegend',
        afterUpdate(chart, args, options) {
            const ul = getOrCreateLegendList(chart, options.containerID);
            if (!ul) return;

            while (ul.firstChild) {
                ul.firstChild.remove();
            }

            const items = chart.options.plugins.legend.labels.generateLabels(chart);

            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'chart-legend-item';
                li.addEventListener('click', () => {
                    const { type } = chart.config;
                    if (type === 'pie' || type === 'doughnut') {
                        chart.toggleDataVisibility(item.index);
                    } else {
                        chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                    }
                    chart.update();
                });

                const boxSpan = document.createElement('span');
                boxSpan.className = 'chart-legend-color';
                boxSpan.style.background = item.fillStyle;

                const textContainer = document.createElement('span');
                textContainer.className = `chart-legend-text${item.hidden ? ' is-hidden' : ''}`;
                textContainer.textContent = item.text;

                li.appendChild(boxSpan);
                li.appendChild(textContainer);
                ul.appendChild(li);
            });
        }
    };

    const ctxTipos = document.getElementById('chartTiposReclamos');
    if (ctxTipos) {
        const datosPorMes = getJsonData('datos-por-mes-data') || {};
        const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        const dataReclamos = [];
        const dataQuejas = [];
        const dataSugerencias = [];

        for (let i = 1; i <= 12; i++) {
            const mes = datosPorMes[i] || { reclamos: 0, quejas: 0, sugerencias: 0 };
            dataReclamos.push(parseInt(mes.reclamos, 10) || 0);
            dataQuejas.push(parseInt(mes.quejas, 10) || 0);
            dataSugerencias.push(parseInt(mes.sugerencias, 10) || 0);
        }

        new Chart(ctxTipos, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    {
                        label: 'Reclamos',
                        data: dataReclamos,
                        backgroundColor: '#4680ff',
                        borderColor: '#4680ff',
                        borderWidth: 0,
                        borderRadius: {
                            topLeft: 8,
                            topRight: 8,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        barPercentage: 0.55,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Quejas',
                        data: dataQuejas,
                        backgroundColor: '#ffba57',
                        borderColor: '#ffba57',
                        borderWidth: 0,
                        borderRadius: {
                            topLeft: 8,
                            topRight: 8,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        barPercentage: 0.55,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Sugerencias',
                        data: dataSugerencias,
                        backgroundColor: '#00d0ff',
                        borderColor: '#00d0ff',
                        borderWidth: 0,
                        borderRadius: {
                            topLeft: 8,
                            topRight: 8,
                            bottomLeft: 0,
                            bottomRight: 0
                        },
                        barPercentage: 0.55,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: true
                },
                plugins: {
                    htmlLegend: {
                        containerID: 'legend-container-tipos'
                    },
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        cornerRadius: 6,
                        titleFont: {
                            size: 13,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 12
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                family: 'Arial'
                            },
                            color: '#a0aec0',
                            padding: 5
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11,
                                family: 'Arial'
                            },
                            color: '#a0aec0',
                            padding: 10
                        },
                        grid: {
                            color: '#f7fafc',
                            drawBorder: false,
                            lineWidth: 1
                        }
                    }
                }
            },
            plugins: [htmlLegendPlugin]
        });
    }

    const ctxEstados = document.getElementById('chartEstados');
    if (ctxEstados) {
        const estadosData = getJsonData('estados-data') || {};

        new Chart(ctxEstados, {
            type: 'doughnut',
            data: {
                labels: ['Resueltos', 'En Revision', 'No Procede', 'Pendientes'],
                datasets: [{
                    data: [
                        parseInt(estadosData.resueltos, 10) || 0,
                        parseInt(estadosData.en_revision, 10) || 0,
                        parseInt(estadosData.no_procede, 10) || 0,
                        parseInt(estadosData.pendientes, 10) || 0
                    ],
                    backgroundColor: ['#28a745', '#ffbf00', '#dc3545', '#6c757d'],
                    borderWidth: 0,
                    hoverOffset: 4,
                    spacing: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: true,
                        cornerRadius: 4,
                        callbacks: {
                            label: function(context) {
                                return context.parsed;
                            }
                        }
                    }
                },
                cutout: '82%'
            }
        });
    }
}

function handleAction(actionEl) {
    const action = actionEl.dataset.action;
    const id = actionEl.dataset.id;

    switch (action) {
        case 'toggle-sidebar':
            toggleSidebar();
            break;
        case 'toggle-user-menu':
            toggleUserMenu();
            break;
        case 'alert':
            mostrarAlerta(actionEl.dataset.message || 'Accion no disponible', actionEl.dataset.type || 'info');
            break;
        case 'open-crear-usuario':
            abrirModalCrearUsuario();
            break;
        case 'close-crear-usuario':
            cerrarModalCrearUsuario();
            break;
        case 'close-editar-usuario':
            cerrarModalEditar();
            break;
        case 'open-perfil-modal':
            abrirModalPerfil();
            break;
        case 'open-password-modal': {
            const userId = actionEl.dataset.userId;
            const userName = actionEl.dataset.userName || '';
            if (userId) cambiarPasswordUsuario(userId, userName);
            break;
        }
        case 'open-perfil-foto': {
            const fileInput = document.getElementById('perfil_foto');
            if (fileInput) fileInput.click();
            break;
        }
        case 'close-perfil-modal':
            cerrarModalPerfil();
            break;
        case 'close-detalle':
            cerrarModalDetalle();
            break;
        case 'toggle-password':
            togglePasswordByData(actionEl.dataset.target, actionEl.dataset.icon);
            break;
        case 'ver-detalle':
            if (id) verDetalleReclamo(id);
            break;
        case 'responder-reclamo':
            if (id) responderReclamo(id);
            break;
        case 'derivar-reclamo':
            if (id) derivarReclamo(id);
            break;
        case 'editar-usuario':
            if (id) editarUsuario(id);
            break;
        case 'cambiar-password-usuario':
            if (id) cambiarPasswordUsuario(id, actionEl.dataset.nombre);
            break;
        case 'toggle-usuario':
            if (id) toggleUsuario(id);
            break;
        case 'eliminar-usuario':
            if (id) eliminarUsuario(id);
            break;
        case 'close-responder':
            cerrarModalResponder();
            break;
        case 'save-respuesta':
            guardarRespuesta();
            break;
        case 'responder-modal':
            if (id) responderReclamoModal(id);
            break;
        case 'derivar-modal':
            if (id) derivarReclamoModal(id);
            break;
        case 'close-derivar':
            cerrarModalDerivar();
            break;
        case 'save-derivar':
            guardarDerivacion();
            break;
        case 'close-password-modal':
            cerrarModalPassword();
            break;
        case 'save-password':
            guardarNuevaPassword();
            break;
        default:
            break;
    }
}

document.addEventListener('click', event => {
    const sectionEl = event.target.closest('[data-section]');
    if (sectionEl) {
        event.preventDefault();
        const sectionName = sectionEl.dataset.section;
        if (sectionName) showSection(sectionName);
        
        // Cerrar el menú de usuario si el elemento clickeado está dentro del dropdown
        const dropdown = document.getElementById('userMenuDropdown');
        if (dropdown && dropdown.contains(sectionEl)) {
            closeUserMenu();
        }
        return;
    }

    const actionEl = event.target.closest('[data-action]');
    if (actionEl) {
        event.preventDefault();
        handleAction(actionEl);
        return;
    }

    // Cerrar el menú de usuario si se hace clic fuera de él
    const userMenuContainer = event.target.closest('.user-menu-container');
    if (!userMenuContainer) {
        closeUserMenu();
    }
});

document.addEventListener('change', event => {
    const actionEl = event.target.closest('[data-action]');
    if (!actionEl) return;

    const action = actionEl.dataset.action;
    if (action === 'select-departamento') {
        cargarAreas(actionEl.value);
    }
    if (action === 'select-area') {
        cargarUsuariosArea();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formCrearUsuario');
    if (form) form.addEventListener('submit', crearUsuario);

    const formEditar = document.getElementById('formEditarUsuario');
    if (formEditar) formEditar.addEventListener('submit', guardarEdicionUsuario);

    const formPerfil = document.getElementById('formEditarPerfil');
    if (formPerfil) formPerfil.addEventListener('submit', guardarPerfil);

    const fotoInput = document.getElementById('perfil_foto');
    const fotoPreview = document.getElementById('perfil_foto_preview');
    const fotoPlaceholder = document.getElementById('perfil_foto_placeholder');
    if (fotoInput && fotoPreview) {
        fotoInput.addEventListener('change', () => {
            const file = fotoInput.files && fotoInput.files[0];
            if (!file) return;

            const url = URL.createObjectURL(file);
            fotoPreview.onload = () => URL.revokeObjectURL(url);
            fotoPreview.src = url;
            fotoPreview.style.display = 'block';
            if (fotoPlaceholder) fotoPlaceholder.style.display = 'none';
        });
    }

    initCharts();
    
    // Event listeners para modal de contraseña
    const nuevaPasswordInput = document.getElementById('nuevaPassword');
    const confirmarPasswordInput = document.getElementById('confirmarPassword');
    
    if (nuevaPasswordInput) {
        nuevaPasswordInput.addEventListener('input', (e) => {
            nuevaPasswordInput.setCustomValidity('');
            checkPasswordStrength(e.target.value);
            checkPasswordMatch();
        });
    }
    
    if (confirmarPasswordInput) {
        confirmarPasswordInput.addEventListener('input', () => {
            confirmarPasswordInput.setCustomValidity('');
            checkPasswordMatch();
        });
    }
    
    // Toggle password visibility buttons
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', (e) => {
            const targetId = e.currentTarget.dataset.target;
            togglePasswordVisibility(targetId);
        });
    });
    
    // Header Icons Functionality
    
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const darkModeIcon = darkModeToggle ? darkModeToggle.querySelector('i') : null;
    
    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        if (darkModeIcon) {
            darkModeIcon.classList.remove('fa-moon');
            darkModeIcon.classList.add('fa-sun');
        }
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            
            // Update icon
            if (darkModeIcon) {
                if (isDark) {
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                } else {
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                }
            }
            
            // Save preference
            localStorage.setItem('darkMode', isDark);
        });
    }
    
    // Notifications Dropdown Toggle
    const notificationsToggle = document.getElementById('notificationsToggle');
    const notificationsDropdown = document.getElementById('notificationsDropdown');
    
    if (notificationsToggle && notificationsDropdown) {
        notificationsToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationsDropdown.classList.toggle('show');
            
            // Close user menu if open
            const userMenu = document.getElementById('userMenuDropdown');
            if (userMenu && userMenu.classList.contains('show')) {
                userMenu.classList.remove('show');
            }
        });
    }
    
    // Language Toggle (placeholder for future implementation)
    const languageToggle = document.getElementById('languageToggle');
    if (languageToggle) {
        languageToggle.addEventListener('click', () => {
            // Placeholder for language switching functionality
            console.log('Language toggle clicked - To be implemented');
            // You can add a dropdown menu similar to notifications here
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (notificationsDropdown && !e.target.closest('.header-icon-wrapper')) {
            notificationsDropdown.classList.remove('show');
        }
        
        const userMenu = document.getElementById('userMenuDropdown');
        if (userMenu && !e.target.closest('.user-menu-container')) {
            userMenu.classList.remove('show');
        }
    });
    
    // Notification item click handler
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function() {
            // Mark as read
            this.classList.remove('unread');
            
            // Update badge count
            const badge = document.getElementById('notificationBadge');
            if (badge) {
                const currentCount = parseInt(badge.textContent);
                if (currentCount > 0) {
                    badge.textContent = currentCount - 1;
                    
                    // Hide badge if count is 0
                    if (currentCount - 1 === 0) {
                        badge.style.display = 'none';
                    }
                }
            }
            
            // Update notifications count in header
            const notificationsCount = document.querySelector('.notifications-count');
            if (notificationsCount) {
                const unreadCount = document.querySelectorAll('.notification-item.unread').length;
                notificationsCount.textContent = unreadCount + ' nuevas';
            }
        });
    });
    
    // ==================== THEME CUSTOMIZER ====================
    
    const themeCustomizer = document.getElementById('themeCustomizer');
    const customizerToggle = document.getElementById('customizerToggle');
    const customizerClose = document.getElementById('customizerClose');
    const resetThemeBtn = document.getElementById('resetTheme');
    
    // Default theme settings
    const defaultTheme = {
        style: 'light',
        headerColor: '#ffffff',
        sidebarColor: 'linear-gradient(135deg, #10506e 0%, #0f6c91 100%)'
    };
    
    // Load saved theme or use defaults
    function loadTheme() {
        const savedTheme = JSON.parse(localStorage.getItem('dashboardTheme')) || defaultTheme;
        
        // Apply theme style
        applyThemeStyle(savedTheme.style);
        
        // Apply header color
        if (savedTheme.headerColor) {
            applyHeaderColor(savedTheme.headerColor);
        }
        
        // Apply sidebar color
        if (savedTheme.sidebarColor) {
            applySidebarColor(savedTheme.sidebarColor);
        }
        
        // Update UI selections
        const styleRadio = document.querySelector(`input[name="theme-style"][value="${savedTheme.style}"]`);
        if (styleRadio) styleRadio.checked = true;
    }
    
    // Save theme to localStorage
    function saveTheme(theme) {
        const currentTheme = JSON.parse(localStorage.getItem('dashboardTheme')) || defaultTheme;
        const updatedTheme = { ...currentTheme, ...theme };
        localStorage.setItem('dashboardTheme', JSON.stringify(updatedTheme));
    }
    
    // Apply theme style (light, dark, semi-dark)
    function applyThemeStyle(style) {
        document.body.classList.remove('dark-mode', 'semi-dark-mode');
        
        if (style === 'dark') {
            document.body.classList.add('dark-mode');
            if (darkModeIcon) {
                darkModeIcon.classList.remove('fa-moon');
                darkModeIcon.classList.add('fa-sun');
            }
        } else if (style === 'semi-dark') {
            document.body.classList.add('semi-dark-mode');
            if (darkModeIcon) {
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
            }
        } else {
            if (darkModeIcon) {
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
            }
        }
        
        localStorage.setItem('darkMode', style === 'dark');
    }
    
    // Apply header color
    function applyHeaderColor(color) {
        const header = document.querySelector('.top-header');
        if (header) {
            header.style.background = color;
            
            // Adjust text color based on background brightness
            const isDark = isColorDark(color);
            header.style.color = isDark ? '#ffffff' : '#212529';
            
            // Update child elements colors
            const pageTitle = header.querySelector('.page-title');
            const sidebarToggle = header.querySelector('.sidebar-toggle');
            
            if (pageTitle) pageTitle.style.color = isDark ? '#ffffff' : '#212529';
            if (sidebarToggle) sidebarToggle.style.color = isDark ? '#ffffff' : '#212529';
        }
        
        saveTheme({ headerColor: color });
    }
    
    // Apply sidebar color
    function applySidebarColor(color) {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.background = color;
        }
        
        saveTheme({ sidebarColor: color });
    }
    
    // Check if color is dark (simple brightness calculation)
    function isColorDark(color) {
        // For gradients, default to light text
        if (color.includes('gradient') || color.includes('linear')) {
            return true;
        }
        
        // For hex colors
        if (color.startsWith('#')) {
            const hex = color.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const brightness = (r * 299 + g * 587 + b * 114) / 1000;
            return brightness < 128;
        }
        
        return false;
    }
    
    // Toggle customizer panel
    if (customizerToggle) {
        customizerToggle.addEventListener('click', () => {
            themeCustomizer.classList.add('show');
        });
    }
    
    if (customizerClose) {
        customizerClose.addEventListener('click', () => {
            themeCustomizer.classList.remove('show');
        });
    }
    
    // Close customizer when clicking outside
    document.addEventListener('click', (e) => {
        if (themeCustomizer && 
            !themeCustomizer.contains(e.target) && 
            !customizerToggle.contains(e.target) &&
            themeCustomizer.classList.contains('show')) {
            themeCustomizer.classList.remove('show');
        }
    });
    
    // Theme style selection
    document.querySelectorAll('input[name=\"theme-style\"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            const style = e.target.value;
            applyThemeStyle(style);
            saveTheme({ style: style });
        });
    });
    
    // Header color selection
    document.querySelectorAll('[data-header-color]').forEach(button => {
        button.addEventListener('click', () => {
            const color = button.getAttribute('data-header-color');
            applyHeaderColor(color);
            
            // Update active state
            document.querySelectorAll('[data-header-color]').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
        });
    });
    
    // Sidebar color selection
    document.querySelectorAll('[data-sidebar-color]').forEach(button => {
        button.addEventListener('click', () => {
            const color = button.getAttribute('data-sidebar-color');
            applySidebarColor(color);
            
            // Update active state
            document.querySelectorAll('[data-sidebar-color]').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');
        });
    });
    
    // Reset theme to defaults
    if (resetThemeBtn) {
        resetThemeBtn.addEventListener('click', () => {
            if (confirm('\u00bfEst\u00e1s seguro de que quieres restaurar los valores por defecto del tema?')) {
                // Clear localStorage
                localStorage.removeItem('dashboardTheme');
                localStorage.removeItem('darkMode');
                
                // Reset to defaults
                applyThemeStyle(defaultTheme.style);
                applyHeaderColor(defaultTheme.headerColor);
                applySidebarColor(defaultTheme.sidebarColor);
                
                // Update UI
                const styleRadio = document.querySelector('input[name=\"theme-style\"][value=\"light\"]');
                if (styleRadio) styleRadio.checked = true;
                
                // Remove active states
                document.querySelectorAll('.color-option').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Show confirmation
                alert('Tema restaurado a valores por defecto');
            }
        });
    }
    
    // Load theme on page load
    loadTheme();
    
    // El modal solo se cierra con los botones de cerrar o cancelar
});
