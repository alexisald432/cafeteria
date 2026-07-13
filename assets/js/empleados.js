/**
 * =============================================
 * MÓDULO DE EMPLEADOS - JavaScript
 * Sistema de Gestión de Cafetería
 * =============================================
 * Maneja operaciones CRUD para empleados:
 * Listar, Buscar, Crear, Editar y Eliminar
 */

// ==========================================
// VARIABLES GLOBALES
// ==========================================

/** URL base de la API de empleados */
const API_URL = '../api/empleados_api.php';

/** Array que almacena todos los empleados cargados */
let empleados = [];

/** ID del empleado que se está editando (null si es nuevo) */
let editingId = null;

// ==========================================
// FUNCIONES DE CARGA DE DATOS
// ==========================================

/**
 * Carga todos los empleados desde la API
 * Usa async/await para manejar la petición asíncrona
 */
async function loadEmpleados() {
    try {
        const response = await fetch(API_URL);

        // Verificar que la respuesta sea exitosa
        if (!response.ok) {
            throw new Error('Error al cargar los empleados');
        }

        // Parsear la respuesta JSON y almacenarla
        empleados = await response.json();

        // Renderizar la tabla con los datos obtenidos
        renderTable(empleados);
    } catch (error) {
        console.error('Error al cargar empleados:', error);
        showMessage('Error al cargar los empleados', 'error');
    }
}

// ==========================================
// FUNCIONES DE RENDERIZADO
// ==========================================

/**
 * Renderiza la tabla de empleados con los datos proporcionados
 * @param {Array} data - Array de objetos empleado a mostrar
 */
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');

    // Si no hay datos, mostrar mensaje vacío
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                    <p style="color: #999; margin-top: 0.5rem;">No se encontraron empleados</p>
                </td>
            </tr>
        `;
        return;
    }

    // Generar las filas HTML usando forEach
    let html = '';
    data.forEach(function(empleado) {
        // Construir nombre completo concatenando nombre y apellido
        const nombreCompleto = empleado.nombre + ' ' + empleado.apellido;

        // Determinar la clase del badge según el estado
        const estadoActivo = parseInt(empleado.activo);
        const badgeClass = estadoActivo ? 'badge-completado' : 'badge-cancelado';
        const estadoTexto = estadoActivo ? 'Activo' : 'Inactivo';

        // Formatear la fecha de contratación
        const fechaFormateada = formatDate(empleado.fecha_contratacion);

        // Construir la fila HTML
        html += `
            <tr>
                <td>${empleado.id_empleado}</td>
                <td>${nombreCompleto}</td>
                <td>${empleado.puesto}</td>
                <td>${empleado.telefono || '—'}</td>
                <td><span class="status-badge ${badgeClass}">${estadoTexto}</span></td>
                <td>${fechaFormateada}</td>
                <td>
                    <button class="btn btn-edit" onclick="openModal(${empleado.id_empleado})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="handleDelete(${empleado.id_empleado})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    // Insertar el HTML generado en el cuerpo de la tabla
    tableBody.innerHTML = html;
}

// ==========================================
// FUNCIONES DE BÚSQUEDA
// ==========================================

/**
 * Filtra los empleados por nombre, apellido o puesto
 * @param {string} term - Término de búsqueda ingresado por el usuario
 */
function handleSearch(term) {
    // Convertir a minúsculas para búsqueda insensible a mayúsculas
    const termLower = term.toLowerCase().trim();

    // Si el término está vacío, mostrar todos los empleados
    if (termLower === '') {
        renderTable(empleados);
        return;
    }

    // Filtrar empleados que coincidan con el término de búsqueda
    const filtrados = empleados.filter(function(empleado) {
        const nombre = empleado.nombre.toLowerCase();
        const apellido = empleado.apellido.toLowerCase();
        const puesto = empleado.puesto.toLowerCase();

        // Buscar en nombre, apellido o puesto
        return nombre.includes(termLower) || 
               apellido.includes(termLower) || 
               puesto.includes(termLower);
    });

    // Renderizar la tabla con los resultados filtrados
    renderTable(filtrados);
}

// ==========================================
// FUNCIONES DEL MODAL
// ==========================================

/**
 * Abre el modal en modo crear o editar
 * @param {number|null} id - ID del empleado a editar (null para crear nuevo)
 */
function openModal(id = null) {
    const modalOverlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('entityForm');

    // Resetear el formulario
    form.reset();

    if (id !== null) {
        // === MODO EDICIÓN ===
        editingId = id;
        modalTitle.textContent = 'Editar Empleado';

        // Buscar el empleado en el array local
        const empleado = empleados.find(function(e) {
            return e.id_empleado == id;
        });

        // Si se encontró, llenar los campos del formulario
        if (empleado) {
            document.getElementById('entityId').value = empleado.id_empleado;
            document.getElementById('nombre').value = empleado.nombre;
            document.getElementById('apellido').value = empleado.apellido;
            document.getElementById('puesto').value = empleado.puesto;
            document.getElementById('telefono').value = empleado.telefono || '';
            document.getElementById('fecha_contratacion').value = empleado.fecha_contratacion;
            document.getElementById('activo').value = empleado.activo;
        }
    } else {
        // === MODO CREACIÓN ===
        editingId = null;
        modalTitle.textContent = 'Agregar Empleado';
        document.getElementById('entityId').value = '';
    }

    // Mostrar el modal
    modalOverlay.classList.add('active');
}

/**
 * Cierra el modal y resetea el formulario
 */
function closeModal() {
    const modalOverlay = document.getElementById('modalOverlay');

    // Ocultar el modal
    modalOverlay.classList.remove('active');

    // Resetear el formulario
    document.getElementById('entityForm').reset();

    // Limpiar el ID de edición
    editingId = null;
}

// ==========================================
// FUNCIONES DE VALIDACIÓN
// ==========================================

/**
 * Valida que los campos obligatorios del formulario estén completos
 * @returns {boolean} true si el formulario es válido, false en caso contrario
 */
function validateForm() {
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    const puesto = document.getElementById('puesto').value;
    const fechaContratacion = document.getElementById('fecha_contratacion').value;

    // Validar nombre no vacío
    if (!nombre) {
        showMessage('El nombre es obligatorio', 'error');
        return false;
    }

    // Validar apellido no vacío
    if (!apellido) {
        showMessage('El apellido es obligatorio', 'error');
        return false;
    }

    // Validar puesto seleccionado
    if (!puesto) {
        showMessage('Debe seleccionar un puesto', 'error');
        return false;
    }

    // Validar fecha de contratación
    if (!fechaContratacion) {
        showMessage('La fecha de contratación es obligatoria', 'error');
        return false;
    }

    return true;
}

// ==========================================
// FUNCIONES CRUD (Crear, Actualizar, Eliminar)
// ==========================================

/**
 * Maneja el envío del formulario para crear o actualizar un empleado
 * @param {Event} e - Evento del formulario
 */
async function handleSubmit(e) {
    // Prevenir el envío tradicional del formulario
    e.preventDefault();

    // Validar el formulario antes de enviar
    if (!validateForm()) {
        return;
    }

    // Recopilar los datos del formulario
    const datos = {
        nombre: document.getElementById('nombre').value.trim(),
        apellido: document.getElementById('apellido').value.trim(),
        puesto: document.getElementById('puesto').value,
        telefono: document.getElementById('telefono').value.trim(),
        fecha_contratacion: document.getElementById('fecha_contratacion').value,
        activo: document.getElementById('activo').value
    };

    try {
        let response;

        if (editingId !== null) {
            // === ACTUALIZAR EMPLEADO (PUT) ===
            datos.id_empleado = editingId;
            response = await fetch(API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
        } else {
            // === CREAR EMPLEADO (POST) ===
            response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
        }

        // Parsear la respuesta
        const resultado = await response.json();

        // Verificar si la operación fue exitosa
        if (resultado.success) {
            showMessage(resultado.message, 'success');
            closeModal();
            loadEmpleados(); // Recargar la tabla
        } else {
            showMessage(resultado.error || 'Error al guardar', 'error');
        }
    } catch (error) {
        console.error('Error al guardar empleado:', error);
        showMessage('Error de conexión al guardar', 'error');
    }
}

/**
 * Maneja la eliminación de un empleado
 * @param {number} id - ID del empleado a eliminar
 */
async function handleDelete(id) {
    // Pedir confirmación al usuario
    const confirmado = await confirmDelete('empleado');

    if (!confirmado) {
        return; // El usuario canceló la eliminación
    }

    try {
        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_empleado: id })
        });

        const resultado = await response.json();

        if (resultado.success) {
            showMessage(resultado.message, 'success');
            loadEmpleados(); // Recargar la tabla
        } else {
            showMessage(resultado.error || 'Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Error al eliminar empleado:', error);
        showMessage('Error de conexión al eliminar', 'error');
    }
}

// ==========================================
// INICIALIZACIÓN
// ==========================================

/**
 * Al cargar el DOM, inicializar el módulo cargando los empleados
 */
document.addEventListener('DOMContentLoaded', function() {
    loadEmpleados();
});
