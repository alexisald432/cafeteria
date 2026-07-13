<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Clientes</h1>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Agregar Cliente
        </button>
    </div>

    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput" 
               placeholder="Buscar cliente..." onkeyup="handleSearch(this.value)">
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Fecha Registro</th>
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
            <h2 id="modalTitle">Agregar Cliente</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="entityForm" onsubmit="handleSubmit(event)">
            <div class="modal-body">
                <input type="hidden" id="entityId">
                <div class="form-group">
                    <label for="nombre">Nombre <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="nombre" 
                           placeholder="Nombre del cliente" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="apellido" 
                           placeholder="Apellido del cliente" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" 
                           placeholder="correo@ejemplo.com">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" 
                           placeholder="10 dígitos" maxlength="10">
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" class="form-control" id="direccion" 
                           placeholder="Dirección del cliente">
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
<script src="../assets/js/clientes.js?v=<?php echo time(); ?>"></script>
