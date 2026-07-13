<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-id-badge"></i> Empleados</h1>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Agregar Empleado
        </button>
    </div>

    <div class="search-container">
        <i class="fas fa-search"></i>
        <input type="text" class="search-input" id="searchInput"
               placeholder="Buscar por nombre, apellido o puesto..." onkeyup="handleSearch(this.value)">
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Puesto</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Fecha Contratación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</main>

<!-- Modal para Agregar/Editar Empleado -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-header">
            <h2 id="modalTitle">Agregar Empleado</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="entityForm" onsubmit="handleSubmit(event)">
            <div class="modal-body">
                <input type="hidden" id="entityId">

                <!-- Campo: Nombre -->
                <div class="form-group">
                    <label for="nombre">Nombre <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="nombre" 
                           placeholder="Ingrese el nombre" required>
                </div>

                <!-- Campo: Apellido -->
                <div class="form-group">
                    <label for="apellido">Apellido <span style="color:red;">*</span></label>
                    <input type="text" class="form-control" id="apellido" 
                           placeholder="Ingrese el apellido" required>
                </div>

                <!-- Campo: Puesto -->
                <div class="form-group">
                    <label for="puesto">Puesto <span style="color:red;">*</span></label>
                    <select class="form-control" id="puesto" required>
                        <option value="">Seleccione un puesto</option>
                        <option value="Cajero">Cajero</option>
                        <option value="Barista">Barista</option>
                        <option value="Gerente">Gerente</option>
                        <option value="Mesero">Mesero</option>
                    </select>
                </div>

                <!-- Campo: Teléfono -->
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" 
                           placeholder="Ingrese el teléfono">
                </div>

                <!-- Campo: Fecha de Contratación -->
                <div class="form-group">
                    <label for="fecha_contratacion">Fecha de Contratación <span style="color:red;">*</span></label>
                    <input type="date" class="form-control" id="fecha_contratacion" required>
                </div>

                <!-- Campo: Estado -->
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
<script src="../assets/js/empleados.js?v=<?php echo time(); ?>"></script>
