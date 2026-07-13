<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-clipboard-list"></i> Pedidos</h1>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Agregar Pedido
        </button>
    </div>

    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar por cliente..." onkeyup="handleSearch(this.value)">
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Empleado</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Método de Pago</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</main>

<!-- ============================================ -->
<!-- Modal: Nuevo Pedido (con productos dinámicos) -->
<!-- ============================================ -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal" style="max-width: 750px;">
        <div class="modal-header">
            <h2 id="modalTitle">Nuevo Pedido</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="entityForm" onsubmit="handleSubmit(event)">
            <div class="modal-body">
                <input type="hidden" id="entityId">

                <!-- ====== SECCIÓN 1: Datos del Pedido ====== -->
                <h3 style="margin-bottom: 1rem; color: var(--primary-color, #333);">
                    <i class="fas fa-info-circle"></i> Datos del Pedido
                </h3>

                <!-- Campo: Cliente -->
                <div class="form-group">
                    <label for="id_cliente">Cliente <span style="color:red;">*</span></label>
                    <select class="form-control" id="id_cliente" required>
                        <option value="">Seleccione un cliente</option>
                    </select>
                </div>

                <!-- Campo: Empleado -->
                <div class="form-group">
                    <label for="id_empleado">Empleado <span style="color:red;">*</span></label>
                    <select class="form-control" id="id_empleado" required>
                        <option value="">Seleccione un empleado</option>
                    </select>
                </div>

                <!-- Campo: Método de Pago -->
                <div class="form-group">
                    <label for="metodo_pago">Método de Pago <span style="color:red;">*</span></label>
                    <select class="form-control" id="metodo_pago" required>
                        <option value="">Seleccione método de pago</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>

                <hr style="margin: 1.5rem 0; border-color: #eee;">

                <!-- ====== SECCIÓN 2: Productos del Pedido ====== -->
                <h3 style="margin-bottom: 1rem; color: var(--primary-color, #333);">
                    <i class="fas fa-shopping-cart"></i> Productos del Pedido
                </h3>

                <!-- Controles para agregar producto -->
                <div style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 1rem; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 2; margin-bottom: 0;">
                        <label for="productoSelect">Producto</label>
                        <select class="form-control" id="productoSelect">
                            <option value="">Seleccione un producto</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label for="cantidadInput">Cantidad</label>
                        <input type="number" class="form-control" id="cantidadInput" 
                               min="1" value="1" placeholder="Cant.">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addProductToPedido()" 
                            style="height: fit-content;">
                        <i class="fas fa-plus"></i> Agregar
                    </button>
                </div>

                <!-- Tabla de productos agregados al pedido -->
                <div id="pedidoDetailContainer">
                    <table class="data-table" style="font-size: 0.9rem;">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                                <th>Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="pedidoDetailBody">
                            <tr>
                                <td colspan="5" style="text-align: center; color: #999; padding: 1rem;">
                                    No hay productos agregados
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- Total del pedido -->
                    <div style="text-align: right; margin-top: 0.75rem; font-size: 1.1rem; font-weight: 600;">
                        Total: <span id="pedidoTotal">$0.00</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Pedido</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- Modal: Editar Pedido (solo estado y método)  -->
<!-- ============================================ -->
<div class="modal-overlay" id="editModalOverlay">
    <div class="modal">
        <div class="modal-header">
            <h2>Editar Pedido</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" onsubmit="handleEditSubmit(event)">
            <div class="modal-body">
                <input type="hidden" id="editPedidoId">

                <!-- Campo: Estado -->
                <div class="form-group">
                    <label for="editEstado">Estado <span style="color:red;">*</span></label>
                    <select class="form-control" id="editEstado" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_preparacion">En Preparación</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <!-- Campo: Método de Pago -->
                <div class="form-group">
                    <label for="editMetodoPago">Método de Pago <span style="color:red;">*</span></label>
                    <select class="form-control" id="editMetodoPago" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================ -->
<!-- Modal: Ver Detalle del Pedido                -->
<!-- ============================================ -->
<div class="modal-overlay" id="detailModalOverlay">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h2><i class="fas fa-receipt"></i> Detalle del Pedido</h2>
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Información general del pedido -->
            <div id="detailInfo" style="margin-bottom: 1.5rem;"></div>
            <!-- Tabla de productos del pedido -->
            <table class="data-table" style="font-size: 0.9rem;">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detailTableBody"></tbody>
            </table>
            <div style="text-align: right; margin-top: 0.75rem; font-size: 1.1rem; font-weight: 600;">
                Total: <span id="detailTotal">$0.00</span>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeDetailModal()">Cerrar</button>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/pedidos.js?v=<?php echo time(); ?>"></script>
