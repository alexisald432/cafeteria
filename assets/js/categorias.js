/**
 * categorias.js — Módulo de Categorías
 * CRUD completo para gestión de categorías de la cafetería.
 * Indicador 7: variables, funciones, condicionales, ciclos, eventos, validaciones, mensajes.
 */

// ============================================================
// VARIABLES
// ============================================================
const API_URL = '../api/categorias_api.php';
let categorias = [];
let editingId = null;

// ============================================================
// FUNCIÓN: loadCategorias()
// Fetch GET para obtener todas las categorías.
// Usa async/await. Guarda en variable y llama renderTable().
// ============================================================
async function loadCategorias() {
    try {
        const response = await fetch(API_URL);

        // Condicional: verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        categorias = data;
        renderTable(categorias);

    } catch (error) {
        console.error('Error al cargar categorías:', error);
        showMessage('Error al cargar las categorías: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: renderTable(data)
// Ciclo forEach para generar filas HTML.
// Si data está vacío, muestra "No se encontraron registros".
// Cada fila tiene botones Editar y Eliminar.
// ============================================================
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');

    // Condicional: verificar si hay datos
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; padding: 40px; color: #888;">
                    <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No se encontraron registros
                </td>
            </tr>
        `;
        return;
    }

    let html = '';

    // Ciclo: recorrer cada categoría para generar filas
    data.forEach(function(categoria) {
        html += `
            <tr>
                <td>${categoria.id_categoria}</td>
                <td>${categoria.nombre}</td>
                <td>${categoria.descripcion || '—'}</td>
                <td>${formatDate(categoria.fecha_creacion)}</td>
                <td>
                    <button class="btn btn-edit" onclick="openModal(${categoria.id_categoria})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="handleDelete(${categoria.id_categoria})" title="Eliminar">
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
        // Condicional: si el término está vacío, mostrar todas las categorías
        if (!term || term.trim() === '') {
            renderTable(categorias);
            return;
        }

        const termLower = term.toLowerCase().trim();

        // Ciclo implícito con filter: recorrer categorías y filtrar
        const filtered = categorias.filter(function(categoria) {
            return (
                categoria.nombre.toLowerCase().includes(termLower) ||
                (categoria.descripcion && categoria.descripcion.toLowerCase().includes(termLower))
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
            // Modo editar: buscar la categoría por id
            editingId = id;
            title.textContent = 'Editar Categoría';

            // Ciclo implícito con find: buscar la categoría
            const categoria = categorias.find(function(cat) {
                return cat.id_categoria == id;
            });

            // Condicional: verificar si se encontró la categoría
            if (categoria) {
                document.getElementById('entityId').value = categoria.id_categoria;
                document.getElementById('nombre').value = categoria.nombre;
                document.getElementById('descripcion').value = categoria.descripcion || '';
            } else {
                showMessage('No se encontró la categoría', 'error');
                return;
            }
        } else {
            // Modo crear
            editingId = null;
            title.textContent = 'Agregar Categoría';
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
// FUNCIÓN: validateForm()
// Verificar que nombre no esté vacío. Retornar true/false.
// ============================================================
function validateForm() {
    const nombre = document.getElementById('nombre').value.trim();

    // Validación: nombre es requerido
    if (!nombre || nombre === '') {
        showMessage('El nombre de la categoría es obligatorio', 'error');
        document.getElementById('nombre').focus();
        return false;
    }

    // Validación: longitud mínima
    if (nombre.length < 2) {
        showMessage('El nombre debe tener al menos 2 caracteres', 'error');
        document.getElementById('nombre').focus();
        return false;
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
        const nombre = document.getElementById('nombre').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();

        const formData = {
            nombre: nombre,
            descripcion: descripcion
        };

        let response;

        // Condicional: determinar si es crear (POST) o editar (PUT)
        if (editingId !== null) {
            // PUT — Actualizar categoría existente
            formData.id_categoria = editingId;
            response = await fetch(API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        } else {
            // POST — Crear nueva categoría
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
            await loadCategorias();
        } else {
            showMessage(result.error || 'Error en la operación', 'error');
        }

    } catch (error) {
        console.error('Error al guardar categoría:', error);
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
        if (!confirmDelete('categoría')) {
            return;
        }

        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_categoria: id })
        });

        const result = await response.json();

        // Condicional: verificar resultado de la eliminación
        if (result.success) {
            showMessage(result.message || 'Categoría eliminada correctamente', 'success');
            await loadCategorias();
        } else {
            showMessage(result.error || 'Error al eliminar la categoría', 'error');
        }

    } catch (error) {
        console.error('Error al eliminar categoría:', error);
        showMessage('Error de conexión: ' + error.message, 'error');
    }
}

// ============================================================
// EVENTO: DOMContentLoaded
// Llamar loadCategorias() al cargar la página.
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadCategorias();

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
