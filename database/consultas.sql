-- ============================================
-- CONSULTAS SQL — Sistema de Gestión de Cafetería
-- Indicador 5 de la rúbrica
-- Autor: Jesús Alexis Aldana García
-- ============================================

-- Estas consultas están diseñadas para funcionar con la base de datos
-- cafeteria_db creada por el script cafeteria.sql.
-- Cada consulta incluye el tipo de operación SQL y su propósito.


-- ============================================
-- CONSULTA 1: SELECT — Listar todos los productos activos
-- Propósito: Obtener el catálogo completo de productos disponibles
-- para mostrar en la sección de Productos del sistema.
-- ============================================
SELECT 
    p.id,
    p.nombre,
    p.descripcion,
    p.precio,
    c.nombre AS categoria,
    p.estado,
    p.fecha_creacion
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
WHERE p.estado = 'activo';


-- ============================================
-- CONSULTA 2: SELECT — Listar todos los clientes registrados
-- Propósito: Obtener la lista completa de clientes para la
-- gestión de clientes y asignación en pedidos.
-- ============================================
SELECT 
    id,
    nombre,
    apellido,
    email,
    telefono,
    direccion,
    fecha_registro
FROM clientes
ORDER BY fecha_registro DESC;


-- ============================================
-- CONSULTA 3: SELECT — Listar todos los pedidos
-- Propósito: Obtener todos los pedidos del sistema con
-- información básica para la vista general de pedidos.
-- ============================================
SELECT 
    p.id,
    p.cliente_id,
    p.empleado_id,
    p.total,
    p.estado,
    p.observaciones,
    p.fecha_pedido
FROM pedidos p
ORDER BY p.fecha_pedido DESC;


-- ============================================
-- CONSULTA 4: SELECT — Listar todas las categorías
-- Propósito: Obtener las categorías de productos para
-- filtrado, formularios de alta y organización del menú.
-- ============================================
SELECT 
    id,
    nombre,
    descripcion,
    estado,
    fecha_creacion
FROM categorias
ORDER BY nombre ASC;


-- ============================================
-- CONSULTA 5: SELECT — Listar empleados activos
-- Propósito: Obtener empleados activos para asignarlos
-- a pedidos y mostrar en la sección de Empleados.
-- ============================================
SELECT 
    id,
    nombre,
    apellido,
    email,
    telefono,
    cargo,
    estado,
    fecha_contratacion
FROM empleados
WHERE estado = 'activo'
ORDER BY nombre ASC;


-- ============================================
-- CONSULTA 6: WHERE — Buscar productos por nombre
-- Propósito: Permitir la búsqueda de productos por coincidencia
-- parcial del nombre. Se usa en el buscador de la sección Productos.
-- El término de búsqueda se pasa como parámetro (ej: '%Latte%').
-- ============================================
SELECT 
    p.id,
    p.nombre,
    p.descripcion,
    p.precio,
    c.nombre AS categoria,
    p.estado
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
WHERE p.nombre LIKE '%Latte%';


-- ============================================
-- CONSULTA 7: WHERE — Filtrar pedidos por estado específico
-- Propósito: Filtrar pedidos por su estado actual para que el
-- personal de cocina vea solo los pendientes, o administración
-- revise los completados. Estado puede ser: 'pendiente',
-- 'en_preparacion', 'completado', 'cancelado'.
-- ============================================
SELECT 
    p.id,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
    p.total,
    p.estado,
    p.fecha_pedido
FROM pedidos p
INNER JOIN clientes c ON p.cliente_id = c.id
INNER JOIN empleados e ON p.empleado_id = e.id
WHERE p.estado = 'pendiente';


-- ============================================
-- CONSULTA 8: ORDER BY — Productos ordenados por precio ascendente
-- Propósito: Mostrar productos del más barato al más caro,
-- útil para que los clientes encuentren opciones económicas
-- o para análisis de precios por parte de administración.
-- ============================================
SELECT 
    p.id,
    p.nombre,
    c.nombre AS categoria,
    p.precio
FROM productos p
INNER JOIN categorias c ON p.categoria_id = c.id
WHERE p.estado = 'activo'
ORDER BY p.precio ASC;


-- ============================================
-- CONSULTA 9: ORDER BY — Pedidos ordenados por fecha descendente
-- Propósito: Mostrar los pedidos más recientes primero.
-- Se usa en el Dashboard para la tabla de "Últimos Pedidos"
-- y en la vista general de Pedidos.
-- ============================================
SELECT 
    p.id,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
    CONCAT(e.nombre, ' ', e.apellido) AS empleado,
    p.total,
    p.estado,
    p.fecha_pedido
FROM pedidos p
INNER JOIN clientes c ON p.cliente_id = c.id
INNER JOIN empleados e ON p.empleado_id = e.id
ORDER BY p.fecha_pedido DESC
LIMIT 5;


-- ============================================
-- CONSULTA 10: INNER JOIN — Pedidos con nombre del cliente y empleado
-- Propósito: Obtener información completa de cada pedido,
-- uniendo las tablas pedidos, clientes y empleados para mostrar
-- nombres legibles en lugar de solo IDs.
-- ============================================
SELECT 
    p.id AS pedido_id,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
    c.email AS cliente_email,
    CONCAT(e.nombre, ' ', e.apellido) AS empleado_nombre,
    e.cargo AS empleado_cargo,
    p.total,
    p.estado,
    p.observaciones,
    p.fecha_pedido
FROM pedidos p
INNER JOIN clientes c ON p.cliente_id = c.id
INNER JOIN empleados e ON p.empleado_id = e.id
ORDER BY p.fecha_pedido DESC;


-- ============================================
-- CONSULTA 11: INNER JOIN — Detalle de un pedido con productos
-- Propósito: Obtener el desglose completo de un pedido específico,
-- mostrando cada producto, su cantidad, precio unitario y subtotal.
-- Se usa al ver el detalle de un pedido (ej: pedido con id = 1).
-- ============================================
SELECT 
    dp.id AS detalle_id,
    p.nombre AS producto,
    dp.cantidad,
    dp.precio_unitario,
    (dp.cantidad * dp.precio_unitario) AS subtotal
FROM detalle_pedidos dp
INNER JOIN productos p ON dp.producto_id = p.id
WHERE dp.pedido_id = 1
ORDER BY dp.id ASC;


-- ============================================
-- CONSULTA 12: GROUP BY — Total de ventas agrupado por categoría
-- Propósito: Analizar qué categorías de productos generan más
-- ingresos. Útil para reportes y toma de decisiones sobre
-- el menú de la cafetería.
-- ============================================
SELECT 
    cat.nombre AS categoria,
    COUNT(DISTINCT dp.pedido_id) AS total_pedidos,
    SUM(dp.cantidad) AS productos_vendidos,
    SUM(dp.cantidad * dp.precio_unitario) AS ingresos_totales
FROM detalle_pedidos dp
INNER JOIN productos p ON dp.producto_id = p.id
INNER JOIN categorias cat ON p.categoria_id = cat.id
INNER JOIN pedidos ped ON dp.pedido_id = ped.id
WHERE ped.estado != 'cancelado'
GROUP BY cat.id, cat.nombre
ORDER BY ingresos_totales DESC;


-- ============================================
-- CONSULTA 13: COUNT — Cantidad de pedidos agrupados por estado
-- Propósito: Obtener un resumen rápido de cuántos pedidos hay
-- en cada estado. Se puede usar para indicadores del Dashboard
-- o para reportes de productividad.
-- ============================================
SELECT 
    estado,
    COUNT(*) AS cantidad_pedidos
FROM pedidos
GROUP BY estado
ORDER BY cantidad_pedidos DESC;


-- ============================================
-- CONSULTA 14: UPDATE — Cambiar el estado de un pedido
-- Propósito: Actualizar el estado de un pedido cuando el
-- personal de cocina comienza a prepararlo. Cambia de
-- 'pendiente' a 'en_preparacion'. También se puede usar
-- para marcar como 'completado' o 'cancelado'.
-- ============================================
UPDATE pedidos
SET estado = 'en_preparacion'
WHERE id = 1 AND estado = 'pendiente';

-- Verificar el cambio realizado:
SELECT id, estado, fecha_pedido
FROM pedidos
WHERE id = 1;


-- ============================================
-- CONSULTAS ADICIONALES ÚTILES
-- ============================================

-- CONSULTA 15: Resumen del Dashboard (estadísticas generales)
-- Propósito: Obtener todos los conteos y totales necesarios
-- para las cards del Dashboard en una sola consulta.
SELECT 
    (SELECT COUNT(*) FROM productos WHERE estado = 'activo') AS total_productos,
    (SELECT COUNT(*) FROM clientes) AS total_clientes,
    (SELECT COUNT(*) FROM pedidos) AS total_pedidos,
    (SELECT COUNT(*) FROM empleados WHERE estado = 'activo') AS total_empleados,
    (SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado = 'completado') AS total_ingresos;


-- ============================================
-- FIN DE LAS CONSULTAS
-- ============================================
