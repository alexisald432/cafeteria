<?php
require_once 'config/database.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p class="subtitle">Resumen general del sistema</p>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards" id="dashboardCards">
        <!-- Card: Total Productos -->
        <div class="card">
            <div class="card-icon accent">
                <i class="fas fa-mug-hot"></i>
            </div>
            <div class="card-value" id="totalProductos">
                <span class="skeleton" style="display:inline-block;width:60px;height:28px;"></span>
            </div>
            <div class="card-label">Total Productos</div>
        </div>

        <!-- Card: Total Clientes -->
        <div class="card">
            <div class="card-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-value" id="totalClientes">
                <span class="skeleton" style="display:inline-block;width:60px;height:28px;"></span>
            </div>
            <div class="card-label">Total Clientes</div>
        </div>

        <!-- Card: Total Pedidos -->
        <div class="card">
            <div class="card-icon green">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="card-value" id="totalPedidos">
                <span class="skeleton" style="display:inline-block;width:60px;height:28px;"></span>
            </div>
            <div class="card-label">Total Pedidos</div>
        </div>

        <!-- Card: Total Empleados -->
        <div class="card">
            <div class="card-icon orange">
                <i class="fas fa-id-badge"></i>
            </div>
            <div class="card-value" id="totalEmpleados">
                <span class="skeleton" style="display:inline-block;width:60px;height:28px;"></span>
            </div>
            <div class="card-label">Total Empleados</div>
        </div>

        <!-- Card: Ingresos Totales -->
        <div class="card">
            <div class="card-icon red">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-value" id="totalIngresos">
                <span class="skeleton" style="display:inline-block;width:80px;height:28px;"></span>
            </div>
            <div class="card-label">Ingresos Totales</div>
        </div>
    </div>

    <!-- Últimos Pedidos -->
    <div class="recent-orders">
        <h2 class="section-title">
            <i class="fas fa-clock"></i>
            Últimos 5 Pedidos
        </h2>
        <div class="table-container">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Empleado</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="recentOrdersBody">
                        <tr>
                            <td colspan="6" class="table-empty">
                                <i class="fas fa-spinner fa-spin"></i>
                                Cargando pedidos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar estadísticas del dashboard
    loadDashboardStats();

    // Toggle sidebar en mobile
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
});

function loadDashboardStats() {
    fetch('api/pedidos_api.php?action=stats')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalProductos').textContent = data.total_productos || 0;
            document.getElementById('totalClientes').textContent = data.total_clientes || 0;
            document.getElementById('totalPedidos').textContent = data.total_pedidos || 0;
            document.getElementById('totalEmpleados').textContent = data.total_empleados || 0;
            document.getElementById('totalIngresos').textContent = '$' + (parseFloat(data.ingresos_totales) || 0).toFixed(2);
            
            const tbody = document.getElementById('recentOrdersBody');
            if (data.ultimos_pedidos && data.ultimos_pedidos.length > 0) {
                tbody.innerHTML = data.ultimos_pedidos.map(order => `
                    <tr>
                        <td>#${order.id_pedido}</td>
                        <td>${order.cliente_nombre}</td>
                        <td>${order.empleado_nombre}</td>
                        <td class="price">${formatCurrency(order.total)}</td>
                        <td><span class="status-badge badge-${order.estado}">${formatStatus(order.estado)}</span></td>
                        <td>${formatDate(order.fecha_pedido)}</td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="table-empty">
                            <i class="fas fa-inbox"></i>
                            No hay pedidos registrados
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error cargando stats:', error);
            // Mostrar valores por defecto si falla la API
            document.getElementById('totalProductos').textContent = '—';
            document.getElementById('totalClientes').textContent = '—';
            document.getElementById('totalPedidos').textContent = '—';
            document.getElementById('totalEmpleados').textContent = '—';
            document.getElementById('totalIngresos').textContent = '$0.00';
            
            document.getElementById('recentOrdersBody').innerHTML = `
                <tr>
                    <td colspan="6" class="table-empty">
                        <i class="fas fa-exclamation-triangle"></i>
                        Error al cargar los pedidos
                    </td>
                </tr>
            `;
        });
}

function formatStatus(status) {
    const labels = {
        'pendiente': 'Pendiente',
        'en_preparacion': 'En Preparación',
        'completado': 'Completado',
        'cancelado': 'Cancelado'
    };
    return labels[status] || status;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
</script>

<?php include 'includes/footer.php'; ?>
