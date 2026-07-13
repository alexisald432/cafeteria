<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-tags"></i> Categorías</h1>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Agregar Categoría
        </button>
    </div>

    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Buscar categoría..." onkeyup="handleSearch(this.value)">
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Fecha Creación</th>
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
            <h2 id="modalTitle">Agregar Categoría</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="entityForm" onsubmit="handleSubmit(event)">
            <div class="modal-body">
                <input type="hidden" id="entityId">
                <div class="form-group">
                    <label for="nombre">Nombre <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="nombre" 
                           placeholder="Nombre de la categoría" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control" id="descripcion" rows="3"
                              placeholder="Descripción de la categoría"></textarea>
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
<script src="../assets/js/categorias.js?v=<?php echo time(); ?>"></script>
