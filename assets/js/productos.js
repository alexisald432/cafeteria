/**
 * productos.js — Módulo de Productos
 * CRUD completo para gestión de productos de la cafetería.
 * Indicador 7: variables, funciones, condicionales, ciclos, eventos, validaciones, mensajes.
 */

// ============================================================
// VARIABLES
// ============================================================
const API_URL = '../api/productos_api.php';
const CATEGORIAS_API = '../api/categorias_api.php';
let productos = [];
let categoriasList = [];
let editingId = null;

// ============================================================
// FUNCIÓN: loadProductos()
// Fetch GET para obtener todos los productos.
// Usa async/await. Guarda en variable y llama renderTable().
// ============================================================
async function loadProductos() {
    try {
        const response = await fetch(API_URL);

        // Condicional: verificar si la respuesta es exitosa
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        productos = data;
        renderTable(productos);

    } catch (error) {
        console.error('Error al cargar productos:', error);
        showMessage('Error al cargar los productos: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: loadCategorias()
// Fetch para llenar el select de categorías en el formulario.
// ============================================================
async function loadCategorias() {
    try {
        const response = await fetch(CATEGORIAS_API);

        // Condicional: verificar respuesta
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();
        categoriasList = data;

        // Llenar el select de categorías
        const select = document.getElementById('id_categoria');
        select.innerHTML = '<option value="">Seleccionar categoría...</option>';

        // Ciclo: recorrer cada categoría para generar opciones
        categoriasList.forEach(function(categoria) {
            const option = document.createElement('option');
            option.value = categoria.id_categoria;
            option.textContent = categoria.nombre;
            select.appendChild(option);
        });

    } catch (error) {
        console.error('Error al cargar categorías:', error);
        showMessage('Error al cargar las categorías: ' + error.message, 'error');
    }
}

// ============================================================
// FUNCIÓN: getCategoriaName(id)
// Buscar el nombre de categoría por su ID.
// ============================================================
function getCategoriaName(id) {
    // Ciclo implícito con find
    const cat = categoriasList.find(function(c) {
        return c.id_categoria == id;
    });
    return cat ? cat.nombre : 'Sin categoría';
}

// ============================================================
// FUNCIÓN: renderTable(data)
// Ciclo forEach para generar filas HTML.
// Muestra precio formateado y estado con badge.
// ============================================================
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');

    // Condicional: verificar si hay datos
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #888;">
                    <i class="fas fa-box-open" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
                    No se encontraron registros
                </td>
            </tr>
        `;
        return;
    }

    let html = '';

    // Ciclo: recorrer cada producto para generar filas
    data.forEach(function(producto) {
        // Condicional: determinar el badge según el estado
        const estadoBadge = producto.activo == 1
            ? '<span class="status-badge badge-completado">Activo</span>'
            : '<span class="status-badge badge-cancelado">Inactivo</span>';

        // Obtener nombre de categoría
        const categoriaNombre = producto.categoria_nombre || getCategoriaName(producto.id_categoria);

        html += `
            <tr>
                <td>${producto.id_producto}</td>
                <td>${producto.nombre}</td>
                <td>${categoriaNombre}</td>
                <td>$${parseFloat(producto.precio).toFixed(2)}</td>
                <td>${producto.stock}</td>
                <td>${estadoBadge}</td>
                <td>
                    <button class="btn btn-edit" onclick="openModal(${producto.id_producto})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="handleDelete(${producto.id_producto})" title="Eliminar">
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
        // Condicional: si el término está vacío, mostrar todos los productos
        if (!term || term.trim() === '') {
            renderTable(productos);
            return;
        }

        const termLower = term.toLowerCase().trim();

        // Ciclo implícito con filter: recorrer productos y filtrar
        const filtered = productos.filter(function(producto) {
            const categoriaNombre = producto.categoria_nombre || getCategoriaName(producto.id_categoria);
            return (
                producto.nombre.toLowerCase().includes(termLower) ||
                (producto.descripcion && producto.descripcion.toLowerCase().includes(termLower)) ||
                categoriaNombre.toLowerCase().includes(termLower)
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
// El formulario carga las categorías disponibles al abrir el modal.
// ============================================================
async function openModal(id = null) {
    try {
        const modal = document.getElementById('modalOverlay');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('entityForm');

        // Resetear formulario
        form.reset();
        document.getElementById('entityId').value = '';

        // Cargar categorías disponibles al abrir el modal
        await loadCategorias();

        // Condicional: determinar si es modo crear o editar
        if (id !== null && id !== undefined) {
            // Modo editar: buscar el producto por id
            editingId = id;
            title.textContent = 'Editar Producto';

            // Ciclo implícito con find: buscar el producto
            const producto = productos.find(function(prod) {
                return prod.id_producto == id;
            });

            // Condicional: verificar si se encontró el producto
            if (producto) {
                document.getElementById('entityId').value = producto.id_producto;
                document.getElementById('nombre').value = producto.nombre;
                document.getElementById('descripcion').value = producto.descripcion || '';
                document.getElementById('precio').value = producto.precio;
                document.getElementById('stock').value = producto.stock;
                document.getElementById('id_categoria').value = producto.id_categoria;
                document.getElementById('activo').value = producto.activo;
            } else {
                showMessage('No se encontró el producto', 'error');
                return;
            }
        } else {
            // Modo crear
            editingId = null;
            title.textContent = 'Agregar Producto';
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
// Validación adicional: precio > 0, stock >= 0, categoría seleccionada.
// ============================================================
function validateForm() {
    const nombre = document.getElementById('nombre').value.trim();
    const precio = parseFloat(document.getElementById('precio').value);
    const stock = parseInt(document.getElementById('stock').value);
    const idCategoria = document.getElementById('id_categoria').value;

    // Validación: nombre es requerido
    if (!nombre || nombre === '') {
        showMessage('El nombre del producto es obligatorio', 'error');
        document.getElementById('nombre').focus();
        return false;
    }

    // Validación: nombre con longitud mínima
    if (nombre.length < 2) {
        showMessage('El nombre debe tener al menos 2 caracteres', 'error');
        document.getElementById('nombre').focus();
        return false;
    }

    // Validación: precio debe ser mayor a 0
    if (isNaN(precio) || precio <= 0) {
        showMessage('El precio debe ser mayor a $0.00', 'error');
        document.getElementById('precio').focus();
        return false;
    }

    // Validación: stock debe ser >= 0
    if (isNaN(stock) || stock < 0) {
        showMessage('El stock no puede ser negativo', 'error');
        document.getElementById('stock').focus();
        return false;
    }

    // Validación: categoría seleccionada
    if (!idCategoria || idCategoria === '') {
        showMessage('Debe seleccionar una categoría', 'error');
        document.getElementById('id_categoria').focus();
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
        const formData = {
            nombre: document.getElementById('nombre').value.trim(),
            descripcion: document.getElementById('descripcion').value.trim(),
            precio: parseFloat(document.getElementById('precio').value),
            stock: parseInt(document.getElementById('stock').value),
            id_categoria: parseInt(document.getElementById('id_categoria').value),
            activo: parseInt(document.getElementById('activo').value)
        };

        let response;

        // Condicional: determinar si es crear (POST) o editar (PUT)
        if (editingId !== null) {
            // PUT — Actualizar producto existente
            formData.id_producto = editingId;
            response = await fetch(API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        } else {
            // POST — Crear nuevo producto
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
            await loadProductos();
        } else {
            showMessage(result.error || 'Error en la operación', 'error');
        }

    } catch (error) {
        console.error('Error al guardar producto:', error);
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
        if (!confirmDelete('producto')) {
            return;
        }

        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_producto: id })
        });

        const result = await response.json();

        // Condicional: verificar resultado de la eliminación
        if (result.success) {
            showMessage(result.message || 'Producto eliminado correctamente', 'success');
            await loadProductos();
        } else {
            showMessage(result.error || 'Error al eliminar el producto', 'error');
        }

    } catch (error) {
        console.error('Error al eliminar producto:', error);
        showMessage('Error de conexión: ' + error.message, 'error');
    }
}

// ============================================================
// EVENTO: DOMContentLoaded
// Llamar loadProductos() y loadCategorias() al cargar la página.
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    loadProductos();
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
