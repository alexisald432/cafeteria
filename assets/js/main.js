/**
 * main.js — Funciones globales compartidas para el Sistema de Gestión de Cafetería
 * Se carga automáticamente desde footer.php en todas las páginas.
 */

// ============================================================
// FUNCIÓN: showMessage(text, type)
// Crea una alerta flotante animada (position fixed, top right).
// Se auto-remueve después de 3 segundos usando setTimeout.
// ============================================================
function showMessage(text, type = 'success') {
    try {
        // Crear el div de alerta
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;

        // Seleccionar ícono según el tipo de mensaje
        let icon = 'fa-check-circle';
        if (type === 'error') {
            icon = 'fa-exclamation-circle';
        } else if (type === 'warning') {
            icon = 'fa-exclamation-triangle';
        } else if (type === 'info') {
            icon = 'fa-info-circle';
        }

        // Establecer contenido HTML con ícono y texto
        alert.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${text}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        // Estilos para la alerta flotante (position fixed, top right)
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.padding = '15px 20px';
        alert.style.borderRadius = '8px';
        alert.style.color = '#fff';
        alert.style.fontSize = '14px';
        alert.style.zIndex = '10000';
        alert.style.display = 'flex';
        alert.style.alignItems = 'center';
        alert.style.gap = '10px';
        alert.style.boxShadow = '0 4px 15px rgba(0,0,0,0.2)';
        alert.style.animation = 'slideInRight 0.4s ease-out';
        alert.style.maxWidth = '400px';
        alert.style.minWidth = '280px';

        // Colores según el tipo
        if (type === 'success') {
            alert.style.backgroundColor = '#28a745';
        } else if (type === 'error') {
            alert.style.backgroundColor = '#dc3545';
        } else if (type === 'warning') {
            alert.style.backgroundColor = '#ffc107';
            alert.style.color = '#333';
        } else if (type === 'info') {
            alert.style.backgroundColor = '#17a2b8';
        }

        // Estilo del botón de cerrar
        const closeBtn = alert.querySelector('.alert-close');
        if (closeBtn) {
            closeBtn.style.background = 'none';
            closeBtn.style.border = 'none';
            closeBtn.style.color = 'inherit';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.marginLeft = 'auto';
            closeBtn.style.fontSize = '16px';
        }

        // Agregar al DOM
        document.body.appendChild(alert);

        // Ajustar posición si ya hay otras alertas visibles
        const existingAlerts = document.querySelectorAll('.alert');
        if (existingAlerts.length > 1) {
            let topOffset = 20;
            existingAlerts.forEach((existing, index) => {
                if (index < existingAlerts.length - 1) {
                    topOffset += existing.offsetHeight + 10;
                }
            });
            alert.style.top = topOffset + 'px';
        }

        // Auto-remover después de 3 segundos con setTimeout
        setTimeout(() => {
            if (alert && alert.parentElement) {
                alert.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (alert && alert.parentElement) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 3000);

    } catch (error) {
        console.error('Error al mostrar mensaje:', error);
    }
}

// ============================================================
// FUNCIÓN: formatDate(dateString)
// Formatea una fecha ISO a formato dd/mm/yyyy HH:mm
// ============================================================
function formatDate(dateString) {
    try {
        if (!dateString) return 'N/A';

        const date = new Date(dateString);

        // Validar que la fecha sea válida
        if (isNaN(date.getTime())) return 'Fecha inválida';

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${day}/${month}/${year} ${hours}:${minutes}`;
    } catch (error) {
        console.error('Error al formatear fecha:', error);
        return 'Error en fecha';
    }
}

// ============================================================
// FUNCIÓN: formatCurrency(amount)
// Formatea un número a $XX.XX MXN
// ============================================================
function formatCurrency(amount) {
    try {
        const num = parseFloat(amount);

        // Validar que sea un número
        if (isNaN(num)) return '$0.00 MXN';

        return `$${num.toFixed(2)} MXN`;
    } catch (error) {
        console.error('Error al formatear moneda:', error);
        return '$0.00 MXN';
    }
}

// ============================================================
// FUNCIÓN: confirmDelete(entityName)
// Muestra un diálogo de confirmación antes de eliminar
// Retorna true o false
// ============================================================
function confirmDelete(entityName) {
    return confirm(`¿Estás seguro de eliminar este/a ${entityName}?`);
}

// ============================================================
// FUNCIÓN: toggleSidebar()
// Toggle de la clase .active en el sidebar para mobile
// ============================================================
function toggleSidebar() {
    try {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    } catch (error) {
        console.error('Error al toggle sidebar:', error);
    }
}

// ============================================================
// FUNCIÓN: closeSidebarOnMobile()
// Event listener para cerrar sidebar al hacer click fuera en mobile
// ============================================================
function closeSidebarOnMobile() {
    try {
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const hamburger = document.querySelector('.hamburger-btn');

            // Condicional: verificar si el sidebar está activo y el click fue fuera
            if (sidebar && sidebar.classList.contains('active')) {
                const isClickInsideSidebar = sidebar.contains(event.target);
                const isClickOnHamburger = hamburger && hamburger.contains(event.target);

                if (!isClickInsideSidebar && !isClickOnHamburger) {
                    sidebar.classList.remove('active');
                }
            }
        });
    } catch (error) {
        console.error('Error al configurar cierre de sidebar:', error);
    }
}

// ============================================================
// EVENTO: DOMContentLoaded
// Inicialización al cargar la página
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // Listener para el botón hamburguesa
    const hamburgerBtn = document.querySelector('.hamburger-btn');
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function(event) {
            event.stopPropagation();
            toggleSidebar();
        });
    }

    // Activar el cierre del sidebar en mobile
    closeSidebarOnMobile();

    // Inyectar animaciones CSS para las alertas
    if (!document.getElementById('mainjs-animations')) {
        const style = document.createElement('style');
        style.id = 'mainjs-animations';
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});
