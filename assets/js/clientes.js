/**
 * clientes.js — Módulo de Clientes
 * CRUD completo para gestión de clientes de la cafetería.
 * Indicador 7: variables, funciones, condicionales, ciclos, eventos, validaciones, mensajes.
 */

// ============================================================
// VARIABLES
// ============================================================
const API_URL = '../api/clientes_api.php';
let clientes = [];
let editingId = null;

// ============================================================
// FUNCIÓN: loadClientes()
// Fetch GET para obtener todos los clientes.
// Usa async/await. Guarda en variable y llama renderTable().
// ============================================================
async function loadClientes() {
    try {
        const response = await fetch(API_URL);

        // Condicional: verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        clientes = data;
        renderTable(clientes);

    } catch (error) {
        console.error('Error al cargar clientes:', error);
        showMessage('Error al cargar los clientes: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: renderTable(data)
// Ciclo forEach para generar filas HTML.
// Nombre completo = nombre + apellido.
// Si data está vacío, muestra "No se encontraron registros".
// ============================================================
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');

    // Condicional: verificar si hay datos
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                    <i class="fas fa-user-slash" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No se encontraron registros
                </td>
            </tr>
        `;
        return;
    }

    let html = '';

    // Ciclo: recorrer cada cliente para generar filas
    data.forEach(function(cliente) {
        // Nombre completo = nombre + apellido
        const nombreCompleto = `${cliente.nombre} ${cliente.apellido}`;

        html += `
            <tr>
                <td>${cliente.id_cliente}</td>
                <td>${nombreCompleto}</td>
                <td>${cliente.email || '—'}</td>
                <td>${cliente.telefono || '—'}</td>
                <td>${formatDate(cliente.fecha_registro)}</td>
                <td>
                    <button class="btn btn-edit" onclick="openModal(${cliente.id_cliente})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="handleDelete(${cliente.id_cliente})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

// ============================================================
// FUNCIÓN: handleSearch(term)
// Condicional: si term vacío, mostrar todo.
// Si no, filtrar con filter() e includes().
// ============================================================
function handleSearch(term) {
    try {
        // Condicional: si el término está vacío, mostrar todos los clientes
        if (!term || term.trim() === '') {
            renderTable(clientes);
            return;
        }

        const termLower = term.toLowerCase().trim();

        // Ciclo implícito con filter: recorrer clientes y filtrar
        const filtered = clientes.filter(function(cliente) {
            const nombreCompleto = `${cliente.nombre} ${cliente.apellido}`.toLowerCase();
            return (
                nombreCompleto.includes(termLower) ||
                (cliente.email && cliente.email.toLowerCase().includes(termLower)) ||
                (cliente.telefono && cliente.telefono.includes(termLower))
            );
        });

        renderTable(filtered);

    } catch (error) {
        console.error('Error en búsqueda:', error);
        showMessage('Error al buscar: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: openModal(id)
// Si id es null → modo crear. Si id tiene valor → modo editar.
// Evento: mostrar modal.
// ============================================================
function openModal(id = null) {
    try {
        const modal = document.getElementById('modalOverlay');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('entityForm');

        // Resetear formulario
        form.reset();
        document.getElementById('entityId').value = '';

        // Condicional: determinar si es modo crear o editar
        if (id !== null && id !== undefined) {
            // Modo editar: buscar el cliente por id
            editingId = id;
            title.textContent = 'Editar Cliente';

            // Ciclo implícito con find: buscar el cliente
            const cliente = clientes.find(function(cli) {
                return cli.id_cliente == id;
            });

            // Condicional: verificar si se encontró el cliente
            if (cliente) {
                document.getElementById('entityId').value = cliente.id_cliente;
                document.getElementById('nombre').value = cliente.nombre;
                document.getElementById('apellido').value = cliente.apellido;
                document.getElementById('email').value = cliente.email || '';
                document.getElementById('telefono').value = cliente.telefono || '';
                document.getElementById('direccion').value = cliente.direccion || '';
            } else {
                showMessage('No se encontró el cliente', 'error');
                return;
            }
        } else {
            // Modo crear
            editingId = null;
            title.textContent = 'Agregar Cliente';
        }

        // Evento: mostrar modal
        modal.style.display = 'flex';

    } catch (error) {
        console.error('Error al abrir modal:', error);
        showMessage('Error al abrir el formulario: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: closeModal()
// Ocultar modal y resetear formulario.
// ============================================================
function closeModal() {
    try {
        const modal = document.getElementById('modalOverlay');
        const form = document.getElementById('entityForm');

        modal.style.display = 'none';
        form.reset();
        editingId = null;
        document.getElementById('entityId').value = '';

    } catch (error) {
        console.error('Error al cerrar modal:', error);
    }
}

// ============================================================
// FUNCIÓN: validateEmail(email)
// Validación de email con regex.
// ============================================================
function validateEmail(email) {
    // Regex para validar formato de email
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

// ============================================================
// FUNCIÓN: validatePhone(phone)
// Validación de teléfono: solo números, 10 dígitos.
// ============================================================
function validatePhone(phone) {
    // Regex para validar solo números y exactamente 10 dígitos
    const phoneRegex = /^\d{10}$/;
    return phoneRegex.test(phone);
}

// ============================================================
// FUNCIÓN: validateForm()
// Verificar nombre y apellido no vacíos.
// Validar email y teléfono si se proporcionan.
// Retornar true/false.
// ============================================================
function validateForm() {
    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    const email = document.getElementById('email').value.trim();
    const telefono = document.getElementById('telefono').value.trim();

    // Validación: nombre es requerido
    if (!nombre || nombre === '') {
        showMessage('El nombre del cliente es obligatorio', 'error');
        document.getElementById('nombre').focus();
        return false;
    }

    // Validación: longitud mínima del nombre
    if (nombre.length < 2) {
        showMessage('El nombre debe tener al menos 2 caracteres', 'error');
        document.getElementById('nombre').focus();
        return false;
    }

    // Validación: apellido es requerido
    if (!apellido || apellido === '') {
        showMessage('El apellido del cliente es obligatorio', 'error');
        document.getElementById('apellido').focus();
        return false;
    }

    // Validación: longitud mínima del apellido
    if (apellido.length < 2) {
        showMessage('El apellido debe tener al menos 2 caracteres', 'error');
        document.getElementById('apellido').focus();
        return false;
    }

    // Validación: email con regex si se proporciona
    if (email && email !== '') {
        if (!validateEmail(email)) {
            showMessage('El formato del email no es válido', 'error');
            document.getElementById('email').focus();
            return false;
        }
    }

    // Validación: teléfono (solo números, 10 dígitos) si se proporciona
    if (telefono && telefono !== '') {
        if (!validatePhone(telefono)) {
            showMessage('El teléfono debe contener exactamente 10 dígitos numéricos', 'error');
            document.getElementById('telefono').focus();
            return false;
        }
    }

    return true;
}

// ============================================================
// FUNCIÓN: handleSubmit(e)
// e.preventDefault(). Validar campos.
// Si editingId → PUT, si no → POST. Mostrar mensaje éxito/error.
// ============================================================
async function handleSubmit(e) {
    // Evento: prevenir envío por defecto del formulario
    e.preventDefault();

    try {
        // Validación: verificar campos antes de enviar
        if (!validateForm()) {
            return;
        }

        // Obtener datos del formulario
        const formData = {
            nombre: document.getElementById('nombre').value.trim(),
            apellido: document.getElementById('apellido').value.trim(),
            email: document.getElementById('email').value.trim(),
            telefono: document.getElementById('telefono').value.trim(),
            direccion: document.getElementById('direccion').value.trim()
        };

        let response;

        // Condicional: determinar si es crear (POST) o editar (PUT)
        if (editingId !== null) {
            // PUT — Actualizar cliente existente
            formData.id_cliente = editingId;
            response = await fetch(API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        } else {
            // POST — Crear nuevo cliente
            response = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        }

        const result = await response.json();

        // Condicional: verificar resultado de la operación
        if (result.success) {
            showMessage(result.message || 'Operación exitosa', 'success');
            closeModal();
            await loadClientes();
        } else {
            showMessage(result.error || 'Error en la operación', 'error');
        }

    } catch (error) {
        console.error('Error al guardar cliente:', error);
        showMessage('Error de conexión: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: handleDelete(id)
// confirm('¿Estás seguro?'). fetch DELETE. Mostrar mensaje.
// ============================================================
async function handleDelete(id) {
    try {
        // Evento/Condicional: confirmar eliminación con el usuario
        if (!confirmDelete('cliente')) {
            return;
        }

        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_cliente: id })
        });

        const result = await response.json();

        // Condicional: verificar resultado de la eliminación
        if (result.success) {
            showMessage(result.message || 'Cliente eliminado correctamente', 'success');
            await loadClientes();
        } else {
            showMessage(result.error || 'Error al eliminar el cliente', 'error');
        }

    } catch (error) {
        console.error('Error al eliminar cliente:', error);
        showMessage('Error de conexión: ' + error.message, 'error');
    }
}

// ============================================================
// EVENTO: DOMContentLoaded
// Llamar loadClientes() al cargar la página.
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadClientes();

    // Evento: cerrar modal al hacer click fuera
    document.getElementById('modalOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Evento: cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modalOverlay');
            if (modal.style.display === 'flex') {
                closeModal();
            }
        }
    });
});
