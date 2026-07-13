<?php
// Detectar página actual para marcar nav-item activo
$currentPage = basename($_SERVER['PHP_SELF']);

// Ajustar rutas según si estamos en raíz o /pages/
$isInPages = strpos($_SERVER['PHP_SELF'], '/pages/') !== false;
$rootPath = $isInPages ? '../' : '';
$pagesPath = $isInPages ? '' : 'pages/';
?>

<!-- Botón hamburguesa para mobile -->
<button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay para cerrar sidebar en mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <div class="logo-icon">
            ☕
        </div>
        <div class="logo-text">Café<span>Admin</span></div>
    </div>

    <div class="sidebar-divider"></div>

    <nav class="nav-menu">
        <div class="nav-item <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
            <a href="<?php echo $rootPath; ?>index.php" class="nav-link">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <?php if (isset($_SESSION['puesto']) && $_SESSION['puesto'] === 'Administrador'): ?>
        <div class="nav-item <?php echo ($currentPage === 'categorias.php') ? 'active' : ''; ?>">
            <a href="<?php echo $pagesPath; ?>categorias.php" class="nav-link">
                <i class="fas fa-tags"></i>
                <span>Categorías</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="nav-item <?php echo ($currentPage === 'productos.php') ? 'active' : ''; ?>">
            <a href="<?php echo $pagesPath; ?>productos.php" class="nav-link">
                <i class="fas fa-mug-hot"></i>
                <span>Productos</span>
            </a>
        </div>

        <div class="nav-item <?php echo ($currentPage === 'clientes.php') ? 'active' : ''; ?>">
            <a href="<?php echo $pagesPath; ?>clientes.php" class="nav-link">
                <i class="fas fa-users"></i>
                <span>Clientes</span>
            </a>
        </div>

        <?php if (isset($_SESSION['puesto']) && $_SESSION['puesto'] === 'Administrador'): ?>
        <div class="nav-item <?php echo ($currentPage === 'empleados.php') ? 'active' : ''; ?>">
            <a href="<?php echo $pagesPath; ?>empleados.php" class="nav-link">
                <i class="fas fa-id-badge"></i>
                <span>Empleados</span>
            </a>
        </div>
        <?php endif; ?>

        <div class="nav-item <?php echo ($currentPage === 'pedidos.php') ? 'active' : ''; ?>">
            <a href="<?php echo $pagesPath; ?>pedidos.php" class="nav-link">
                <i class="fas fa-receipt"></i>
                <span>Pedidos</span>
            </a>
        </div>
    </nav>
    
    <div style="margin-top: auto; padding: 1.5rem; text-align: center; border-top: 1px solid var(--border-color);">
        <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem; display: flex; flex-direction: column; align-items: center; gap: 5px;">
            <i class="fas fa-user-circle" style="font-size: 2rem; color: var(--gold-light);"></i>
            <strong style="color: var(--text-light);"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></strong>
            <span style="font-size: 0.75rem; background: rgba(212, 175, 55, 0.1); padding: 2px 8px; border-radius: 10px; color: var(--gold-light);"><?php echo htmlspecialchars($_SESSION['puesto'] ?? 'Rol'); ?></span>
        </div>
        <button id="logoutBtn" class="btn" style="width: 100%; background: transparent; border: 1px solid var(--danger-color); color: var(--danger-color); padding: 0.5rem; border-radius: var(--radius-md); transition: all 0.3s ease;">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            const isInPages = window.location.pathname.includes('/pages/');
            const apiPath = isInPages ? '../api/auth_api.php?action=logout' : 'api/auth_api.php?action=logout';
            
            fetch(apiPath, { method: 'POST' })
                .then(() => window.location.href = (isInPages ? '../login.php' : 'login.php'));
        });
    }
});
</script>
