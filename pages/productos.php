<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-mug-hot"></i> Productos</h1>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Agregar Producto
        </button>
    </div>

    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Buscar producto..." onkeyup="handleSearch(this.value)">
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Se llena con JS -->
            </tbody>
        </table>
    </div>
</main>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-header">
            <h2 id="modalTitle">Agregar Producto</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="entityForm" onsubmit="handleSubmit(event)">
            <div class="modal-body">
                <input type="hidden" id="entityId">
                <div class="form-group">
                    <label for="nombre">Nombre <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="nombre" 
                           placeholder="Nombre del producto" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" rows="3"
                              placeholder="Descripción del producto"></textarea>
                </div>
                <div class="form-group">
                    <label for="precio">Precio <span style="color:red;">*</span></label>
                    <input type="number" class="form-control" id="precio" 
                           placeholder="0.00" min="0.01" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock <span style="color:red;">*</span></label>
                    <input type="number" class="form-control" id="stock" 
                           placeholder="0" min="0" required>
                </div>
                <div class="form-group">
                    <label for="id_categoria">Categoría <span style="color:red;">*</span></label>
                    <select class="form-control" id="id_categoria" required>
                        <option value="">Seleccionar categoría...</option>
                        <!-- Se llena dinámicamente con JS -->
                    </select>
                </div>
                <div class="form-group">
                    <label for="activo">Estado</label>
                    <select class="form-control" id="activo">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/productos.js?v=<?php echo time(); ?>"></script>
