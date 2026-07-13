/**
 * =============================================
 * MÓDULO DE PEDIDOS - JavaScript
 * Sistema de Gestión de Cafetería
 * =============================================
 * Maneja operaciones CRUD para pedidos:
 * Listar, Buscar, Crear (con productos), Editar estado, 
 * Ver detalle y Eliminar
 */

// ==========================================
// VARIABLES GLOBALES
// ==========================================

/** URLs de las APIs */
const API_URL = '../api/pedidos_api.php';
const CLIENTES_API = '../api/clientes_api.php';
const EMPLEADOS_API = '../api/empleados_api.php';
const PRODUCTOS_API = '../api/productos_api.php';

/** Array con todos los pedidos cargados */
let pedidos = [];

/** ID del pedido en edición (null si es nuevo) */
let editingId = null;

/** Productos agregados al pedido actual (en el modal de nuevo pedido) */
let detallesPedido = [];

/** Listas para llenar los selects dinámicos */
let productosDisponibles = [];
let clientesDisponibles = [];
let empleadosDisponibles = [];

// ==========================================
// FUNCIONES DE CARGA DE DATOS
// ==========================================

/**
 * Carga todos los pedidos desde la API
 * Los pedidos incluyen datos de JOIN (nombre de cliente/empleado)
 */
async function loadPedidos() {
    try {
        const response = await fetch(API_URL);

        if (!response.ok) {
            throw new Error('Error al cargar pedidos');
        }

        pedidos = await response.json();
        renderTable(pedidos);
    } catch (error) {
        console.error('Error al cargar pedidos:', error);
        showMessage('Error al cargar los pedidos', 'error');
    }
}

/**
 * Carga clientes, empleados y productos para los selects dinámicos
 * Usa Promise.all para ejecutar las 3 peticiones en paralelo
 */
async function loadSelects() {
    try {
        // Ejecutar las 3 peticiones simultáneamente
        const [clientesRes, empleadosRes, productosRes] = await Promise.all([
            fetch(CLIENTES_API),
            fetch(EMPLEADOS_API),
            fetch(PRODUCTOS_API)
        ]);

        // Parsear las respuestas JSON
        clientesDisponibles = await clientesRes.json();
        empleadosDisponibles = await empleadosRes.json();
        productosDisponibles = await productosRes.json();

        // Llenar el select de clientes
        const clienteSelect = document.getElementById('id_cliente');
        clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';
        clientesDisponibles.forEach(function(cliente) {
            const nombre = cliente.nombre + ' ' + (cliente.apellido || '');
            clienteSelect.innerHTML += `<option value="${cliente.id_cliente}">${nombre.trim()}</option>`;
        });

        // Llenar el select de empleados
        const empleadoSelect = document.getElementById('id_empleado');
        empleadoSelect.innerHTML = '<option value="">Seleccione un empleado</option>';
        empleadosDisponibles.forEach(function(empleado) {
            const nombre = empleado.nombre + ' ' + empleado.apellido;
            empleadoSelect.innerHTML += `<option value="${empleado.id_empleado}">${nombre}</option>`;
        });

        // Llenar el select de productos
        const productoSelect = document.getElementById('productoSelect');
        productoSelect.innerHTML = '<option value="">Seleccione un producto</option>';
        productosDisponibles.forEach(function(producto) {
            productoSelect.innerHTML += `<option value="${producto.id_producto}">${producto.nombre} - ${formatCurrency(producto.precio)}</option>`;
        });

    } catch (error) {
        console.error('Error al cargar selects:', error);
        showMessage('Error al cargar datos de selección', 'error');
    }
}

// ==========================================
// FUNCIONES DE RENDERIZADO
// ==========================================

/**
 * Renderiza la tabla principal de pedidos
 * @param {Array} data - Array de objetos pedido a mostrar
 */
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');

    // Mensaje si no hay datos
    if (!data || data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i>
                    <p style="color: #999; margin-top: 0.5rem;">No se encontraron pedidos</p>
                </td>
            </tr>
        `;
        return;
    }

    let html = '';
    data.forEach(function(pedido) {
        // Determinar el nombre del cliente (puede venir del JOIN)
        const clienteNombre = pedido.cliente_nombre || ('Cliente #' + pedido.id_cliente);

        // Determinar el nombre del empleado
        const empleadoNombre = pedido.empleado_nombre || ('Empleado #' + pedido.id_empleado);

        // Clase del badge según el estado
        const badgeClass = getBadgeClass(pedido.estado);

        // Formatear el estado para mostrar
        const estadoTexto = formatEstado(pedido.estado);

        // Formatear el total como moneda
        const totalFormateado = formatCurrency(pedido.total);

        // Formatear la fecha
        const fechaFormateada = formatDate(pedido.fecha || pedido.created_at);

        // Formatear método de pago con primera letra mayúscula
        const metodoPago = pedido.metodo_pago ? 
            pedido.metodo_pago.charAt(0).toUpperCase() + pedido.metodo_pago.slice(1) : '—';

        html += `
            <tr>
                <td>${pedido.id_pedido}</td>
                <td>${clienteNombre}</td>
                <td>${empleadoNombre}</td>
                <td>${totalFormateado}</td>
                <td><span class="status-badge ${badgeClass}">${estadoTexto}</span></td>
                <td>${metodoPago}</td>
                <td>${fechaFormateada}</td>
                <td>
                    <button class="btn btn-edit" onclick="openDetailModal(${pedido.id_pedido})" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-edit" onclick="openEditModal(${pedido.id_pedido})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-delete" onclick="handleDelete(${pedido.id_pedido})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

/**
 * Renderiza la tabla de productos agregados al pedido actual (en el modal)
 */
function renderPedidoDetail() {
    const tbody = document.getElementById('pedidoDetailBody');

    // Si no hay productos, mostrar mensaje
    if (detallesPedido.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; color: #999; padding: 1rem;">
                    No hay productos agregados
                </td>
            </tr>
        `;
        document.getElementById('pedidoTotal').textContent = '$0.00';
        return;
    }

    let html = '';
    detallesPedido.forEach(function(detalle, index) {
        const subtotal = detalle.cantidad * detalle.precio;
        html += `
            <tr>
                <td>${detalle.nombre}</td>
                <td>${detalle.cantidad}</td>
                <td>${formatCurrency(detalle.precio)}</td>
                <td>${formatCurrency(subtotal)}</td>
                <td>
                    <button type="button" class="btn btn-delete" onclick="removeProductFromPedido(${index})" title="Quitar">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;

    // Actualizar el total
    const total = calculateTotal();
    document.getElementById('pedidoTotal').textContent = formatCurrency(total);
}

// ==========================================
// FUNCIONES AUXILIARES
// ==========================================

/**
 * Obtiene la clase CSS del badge según el estado del pedido
 * @param {string} estado - Estado del pedido
 * @returns {string} Clase CSS correspondiente
 */
function getBadgeClass(estado) {
    switch (estado) {
        case 'pendiente':
            return 'badge-pendiente';
        case 'en_preparacion':
            return 'badge-en_preparacion';
        case 'completado':
            return 'badge-completado';
        case 'cancelado':
            return 'badge-cancelado';
        default:
            return 'badge-pendiente';
    }
}

/**
 * Formatea el estado para mostrar al usuario
 * @param {string} estado - Estado original
 * @returns {string} Estado formateado
 */
function formatEstado(estado) {
    switch (estado) {
        case 'pendiente':
            return 'Pendiente';
        case 'en_preparacion':
            return 'En Preparación';
        case 'completado':
            return 'Completado';
        case 'cancelado':
            return 'Cancelado';
        default:
            return estado;
    }
}

/**
 * Calcula el total del pedido actual sumando todos los subtotales
 * @returns {number} Total del pedido
 */
function calculateTotal() {
    let total = 0;

    // Recorrer cada detalle y sumar su subtotal
    detallesPedido.forEach(function(detalle) {
        total += detalle.cantidad * detalle.precio;
    });

    return total;
}

// ==========================================
// FUNCIONES DE BÚSQUEDA
// ==========================================

/**
 * Filtra los pedidos por nombre de cliente
 * @param {string} term - Término de búsqueda
 */
function handleSearch(term) {
    const termLower = term.toLowerCase().trim();

    if (termLower === '') {
        renderTable(pedidos);
        return;
    }

    const filtrados = pedidos.filter(function(pedido) {
        const clienteNombre = (pedido.cliente_nombre || '').toLowerCase();
        return clienteNombre.includes(termLower);
    });

    renderTable(filtrados);
}

// ==========================================
// FUNCIONES DE PRODUCTOS EN EL PEDIDO
// ==========================================

/**
 * Agrega un producto al pedido actual
 * Obtiene el producto seleccionado y la cantidad del formulario
 */
function addProductToPedido() {
    const productoSelect = document.getElementById('productoSelect');
    const cantidadInput = document.getElementById('cantidadInput');

    const productoId = productoSelect.value;
    const cantidad = parseInt(cantidadInput.value);

    // Validar que se haya seleccionado un producto
    if (!productoId) {
        showMessage('Seleccione un producto', 'error');
        return;
    }

    // Validar que la cantidad sea mayor a 0
    if (!cantidad || cantidad <= 0) {
        showMessage('La cantidad debe ser mayor a 0', 'error');
        return;
    }

    // Buscar el producto en la lista de disponibles
    const producto = productosDisponibles.find(function(p) {
        return p.id_producto == productoId;
    });

    if (!producto) {
        showMessage('Producto no encontrado', 'error');
        return;
    }

    // Verificar si el producto ya está en los detalles
    const existente = detallesPedido.find(function(d) {
        return d.id_producto == productoId;
    });

    if (existente) {
        // Si ya existe, incrementar la cantidad
        existente.cantidad += cantidad;
    } else {
        // Agregar nuevo producto a los detalles del pedido
        detallesPedido.push({
            id_producto: producto.id_producto,
            nombre: producto.nombre,
            precio: parseFloat(producto.precio),
            cantidad: cantidad
        });
    }

    // Resetear los controles de selección
    productoSelect.value = '';
    cantidadInput.value = 1;

    // Re-renderizar la lista de productos y recalcular total
    renderPedidoDetail();
}

/**
 * Quita un producto del pedido actual por su índice
 * @param {number} index - Índice del producto en el array detallesPedido
 */
function removeProductFromPedido(index) {
    // Eliminar el producto del array usando splice
    detallesPedido.splice(index, 1);

    // Re-renderizar la lista y recalcular total
    renderPedidoDetail();
}

// ==========================================
// FUNCIONES DEL MODAL — NUEVO PEDIDO
// ==========================================

/**
 * Abre el modal de nuevo pedido
 * Carga los selects dinámicos y limpia los detalles
 */
async function openModal() {
    const modalOverlay = document.getElementById('modalOverlay');
    const form = document.getElementById('entityForm');

    // Resetear formulario y detalles
    form.reset();
    editingId = null;
    detallesPedido = [];

    // Cargar los selects de clientes, empleados y productos
    await loadSelects();

    // Renderizar la tabla de detalles vacía
    renderPedidoDetail();

    // Mostrar el modal
    modalOverlay.classList.add('active');
}

/**
 * Cierra el modal de nuevo pedido
 */
function closeModal() {
    const modalOverlay = document.getElementById('modalOverlay');
    modalOverlay.classList.remove('active');

    // Limpiar formulario y detalles
    document.getElementById('entityForm').reset();
    detallesPedido = [];
    editingId = null;
}

// ==========================================
// FUNCIONES DEL MODAL — EDITAR PEDIDO
// ==========================================

/**
 * Abre el modal simplificado para editar estado y método de pago
 * @param {number} id - ID del pedido a editar
 */
function openEditModal(id) {
    const modalOverlay = document.getElementById('editModalOverlay');

    // Buscar el pedido en el array local
    const pedido = pedidos.find(function(p) {
        return p.id_pedido == id;
    });

    if (!pedido) {
        showMessage('Pedido no encontrado', 'error');
        return;
    }

    // Llenar los campos del modal de edición
    document.getElementById('editPedidoId').value = pedido.id_pedido;
    document.getElementById('editEstado').value = pedido.estado;
    document.getElementById('editMetodoPago').value = pedido.metodo_pago || 'efectivo';

    // Mostrar el modal
    modalOverlay.classList.add('active');
}

/**
 * Cierra el modal de edición
 */
function closeEditModal() {
    const modalOverlay = document.getElementById('editModalOverlay');
    modalOverlay.classList.remove('active');
    document.getElementById('editForm').reset();
}

// ==========================================
// FUNCIONES DEL MODAL — VER DETALLE
// ==========================================

/**
 * Abre el modal de detalle del pedido
 * Realiza un fetch GET con el ID para obtener los detalles completos
 * @param {number} id - ID del pedido a consultar
 */
async function openDetailModal(id) {
    try {
        const response = await fetch(API_URL + '?id=' + id);

        if (!response.ok) {
            throw new Error('Error al cargar detalle');
        }

        const pedido = await response.json();

        // Mostrar la información general del pedido
        const detailInfo = document.getElementById('detailInfo');
        const clienteNombre = pedido.pedido.cliente_nombre || ('Cliente #' + pedido.pedido.id_cliente);
        const empleadoNombre = pedido.pedido.empleado_nombre || ('Empleado #' + pedido.pedido.id_empleado);
        const estadoTexto = formatEstado(pedido.pedido.estado);
        const badgeClass = getBadgeClass(pedido.pedido.estado);

        detailInfo.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <p><strong>Pedido #:</strong> ${pedido.pedido.id_pedido}</p>
                <p><strong>Fecha:</strong> ${formatDate(pedido.pedido.fecha || pedido.pedido.created_at)}</p>
                <p><strong>Cliente:</strong> ${clienteNombre}</p>
                <p><strong>Empleado:</strong> ${empleadoNombre}</p>
                <p><strong>Estado:</strong> <span class="status-badge ${badgeClass}">${estadoTexto}</span></p>
                <p><strong>Método de Pago:</strong> ${pedido.pedido.metodo_pago ? pedido.pedido.metodo_pago.charAt(0).toUpperCase() + pedido.pedido.metodo_pago.slice(1) : '—'}</p>
            </div>
        `;

        // Renderizar la tabla de detalles (productos del pedido)
        const detailBody = document.getElementById('detailTableBody');
        const detalles = pedido.detalles || [];

        if (detalles.length === 0) {
            detailBody.innerHTML = `
                <tr>
                    <td colspan="4" style="text-align: center; color: #999;">Sin productos</td>
                </tr>
            `;
            document.getElementById('detailTotal').textContent = formatCurrency(pedido.pedido.total || 0);
        } else {
            let html = '';
            let totalCalculado = 0;

            detalles.forEach(function(detalle) {
                const subtotal = detalle.cantidad * detalle.precio_unitario;
                totalCalculado += subtotal;

                html += `
                    <tr>
                        <td>${detalle.producto_nombre || detalle.nombre || ('Producto #' + detalle.id_producto)}</td>
                        <td>${detalle.cantidad}</td>
                        <td>${formatCurrency(detalle.precio_unitario)}</td>
                        <td>${formatCurrency(subtotal)}</td>
                    </tr>
                `;
            });

            detailBody.innerHTML = html;
            document.getElementById('detailTotal').textContent = formatCurrency(pedido.pedido.total || totalCalculado);
        }

        // Mostrar el modal de detalle
        document.getElementById('detailModalOverlay').classList.add('active');

    } catch (error) {
        console.error('Error al cargar detalle del pedido:', error);
        showMessage('Error al cargar el detalle del pedido', 'error');
    }
}

/**
 * Cierra el modal de detalle
 */
function closeDetailModal() {
    document.getElementById('detailModalOverlay').classList.remove('active');
}

// ==========================================
// FUNCIONES DE VALIDACIÓN
// ==========================================

/**
 * Valida el formulario de nuevo pedido
 * Verifica que se hayan seleccionado cliente, empleado y al menos un producto
 * @returns {boolean} true si es válido
 */
function validateForm() {
    const idCliente = document.getElementById('id_cliente').value;
    const idEmpleado = document.getElementById('id_empleado').value;
    const metodoPago = document.getElementById('metodo_pago').value;

    // Validar cliente seleccionado
    if (!idCliente) {
        showMessage('Debe seleccionar un cliente', 'error');
        return false;
    }

    // Validar empleado seleccionado
    if (!idEmpleado) {
        showMessage('Debe seleccionar un empleado', 'error');
        return false;
    }

    // Validar método de pago
    if (!metodoPago) {
        showMessage('Debe seleccionar un método de pago', 'error');
        return false;
    }

    // Validar que haya al menos un producto
    if (detallesPedido.length === 0) {
        showMessage('Debe agregar al menos un producto al pedido', 'error');
        return false;
    }

    return true;
}

// ==========================================
// FUNCIONES CRUD
// ==========================================

/**
 * Maneja el envío del formulario de nuevo pedido
 * Envía los datos del pedido junto con los detalles (productos)
 * @param {Event} e - Evento del formulario
 */
async function handleSubmit(e) {
    e.preventDefault();

    // Validar el formulario completo
    if (!validateForm()) {
        return;
    }

    // Construir el objeto de datos del pedido
    const datos = {
        id_cliente: document.getElementById('id_cliente').value,
        id_empleado: document.getElementById('id_empleado').value,
        metodo_pago: document.getElementById('metodo_pago').value,
        total: calculateTotal(),
        estado: 'pendiente',
        detalles: detallesPedido.map(function(detalle) {
            return {
                id_producto: detalle.id_producto,
                cantidad: detalle.cantidad,
                precio_unitario: detalle.precio
            };
        })
    };

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            showMessage(resultado.message, 'success');
            closeModal();
            loadPedidos(); // Recargar la tabla
        } else {
            showMessage(resultado.error || 'Error al guardar el pedido', 'error');
        }
    } catch (error) {
        console.error('Error al crear pedido:', error);
        showMessage('Error de conexión al guardar el pedido', 'error');
    }
}

/**
 * Maneja el envío del formulario de edición (solo estado y método de pago)
 * @param {Event} e - Evento del formulario
 */
async function handleEditSubmit(e) {
    e.preventDefault();

    const id = document.getElementById('editPedidoId').value;
    const estado = document.getElementById('editEstado').value;
    const metodoPago = document.getElementById('editMetodoPago').value;

    // Validar campos
    if (!estado) {
        showMessage('Debe seleccionar un estado', 'error');
        return;
    }

    // Construir datos para actualizar
    const datos = {
        id_pedido: parseInt(id),
        estado: estado,
        metodo_pago: metodoPago
    };

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        });

        const resultado = await response.json();

        if (resultado.success) {
            showMessage(resultado.message, 'success');
            closeEditModal();
            loadPedidos(); // Recargar la tabla
        } else {
            showMessage(resultado.error || 'Error al actualizar', 'error');
        }
    } catch (error) {
        console.error('Error al actualizar pedido:', error);
        showMessage('Error de conexión al actualizar', 'error');
    }
}

/**
 * Maneja la eliminación de un pedido
 * @param {number} id - ID del pedido a eliminar
 */
async function handleDelete(id) {
    // Pedir confirmación
    const confirmado = await confirmDelete('pedido');

    if (!confirmado) {
        return;
    }

    try {
        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_pedido: id })
        });

        const resultado = await response.json();

        if (resultado.success) {
            showMessage(resultado.message, 'success');
            loadPedidos(); // Recargar la tabla
        } else {
            showMessage(resultado.error || 'Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Error al eliminar pedido:', error);
        showMessage('Error de conexión al eliminar', 'error');
    }
}

// ==========================================
// INICIALIZACIÓN
// ==========================================

/**
 * Al cargar el DOM, inicializar el módulo cargando los pedidos
 */
document.addEventListener('DOMContentLoaded', function() {
    loadPedidos();
});
