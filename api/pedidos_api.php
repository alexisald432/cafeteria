<?php
/**
 * API REST para Pedidos - Sistema de Gestión de Cafetería
 * 
 * API más compleja: maneja pedidos + detalle_pedidos con transacciones.
 * 
 * Endpoints:
 *   GET    - Listar todos (con JOIN), obtener por ID (con detalles),
 *            buscar por cliente, filtrar por estado, estadísticas (action=stats)
 *   POST   - Crear pedido completo con detalles (usa transacción)
 *   PUT    - Actualizar estado y método de pago
 *   DELETE - Eliminar pedido (CASCADE en detalles)
 */

require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {

        // =============================================
        // GET - Obtener pedidos, detalles y estadísticas
        // =============================================
        case 'GET':

            // -----------------------------------------
            // Endpoint especial: Estadísticas para Dashboard
            // -----------------------------------------
            if (isset($_GET['action']) && $_GET['action'] === 'stats') {
                $stats = [];

                // Total de productos
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
                $stats['total_productos'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Total de clientes
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
                $stats['total_clientes'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Total de pedidos
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
                $stats['total_pedidos'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Total de empleados
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM empleados");
                $stats['total_empleados'] = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Ingresos totales (solo pedidos completados)
                $stmt = $pdo->query("SELECT COALESCE(SUM(total), 0) as ingresos FROM pedidos WHERE estado = 'completado'");
                $stats['ingresos_totales'] = (float) $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'];

                // Últimos 5 pedidos
                $stmt = $pdo->query(
                    "SELECT p.id_pedido, p.fecha_pedido, p.total, p.estado,
                            CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                            CONCAT(e.nombre, ' ', e.apellido) AS empleado_nombre
                     FROM pedidos p
                     INNER JOIN clientes c ON p.id_cliente = c.id_cliente
                     INNER JOIN empleados e ON p.id_empleado = e.id_empleado
                     ORDER BY p.fecha_pedido DESC
                     LIMIT 5"
                );
                $stats['ultimos_pedidos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($stats);
                break;
            }

            // Query base con JOINs para nombres de cliente y empleado
            $queryBase = "SELECT p.*, 
                                 CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                                 CONCAT(e.nombre, ' ', e.apellido) AS empleado_nombre
                          FROM pedidos p
                          INNER JOIN clientes c ON p.id_cliente = c.id_cliente
                          INNER JOIN empleados e ON p.id_empleado = e.id_empleado";

            if (isset($_GET['id'])) {
                // -----------------------------------------
                // Obtener pedido por ID + sus detalles
                // -----------------------------------------

                // 1. Obtener datos del pedido
                $stmt = $pdo->prepare($queryBase . " WHERE p.id_pedido = ?");
                $stmt->execute([$_GET['id']]);
                $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$pedido) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Pedido no encontrado']);
                    break;
                }

                // 2. Obtener detalles del pedido con nombre de producto
                $stmtDetalles = $pdo->prepare(
                    "SELECT dp.*, pr.nombre AS producto_nombre 
                     FROM detalle_pedidos dp 
                     INNER JOIN productos pr ON dp.id_producto = pr.id_producto 
                     WHERE dp.id_pedido = ?"
                );
                $stmtDetalles->execute([$_GET['id']]);
                $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

                // Devolver pedido con sus detalles
                echo json_encode([
                    'pedido' => $pedido,
                    'detalles' => $detalles
                ]);

            } elseif (isset($_GET['estado'])) {
                // -----------------------------------------
                // Filtrar pedidos por estado
                // -----------------------------------------
                $stmt = $pdo->prepare($queryBase . " WHERE p.estado = ? ORDER BY p.fecha_pedido DESC");
                $stmt->execute([$_GET['estado']]);
                $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($pedidos);

            } elseif (isset($_GET['search'])) {
                // -----------------------------------------
                // Buscar pedidos por nombre de cliente
                // -----------------------------------------
                $busqueda = '%' . $_GET['search'] . '%';
                $stmt = $pdo->prepare(
                    $queryBase . " WHERE CONCAT(c.nombre, ' ', c.apellido) LIKE ? 
                     ORDER BY p.fecha_pedido DESC"
                );
                $stmt->execute([$busqueda]);
                $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($pedidos);

            } else {
                // -----------------------------------------
                // Listar todos los pedidos
                // -----------------------------------------
                $stmt = $pdo->query($queryBase . " ORDER BY p.fecha_pedido DESC");
                $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($pedidos);
            }
            break;

        // =============================================
        // POST - Crear nuevo pedido con detalles
        // Usa transacción para garantizar integridad
        // =============================================
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['id_cliente']) || empty($data['id_empleado']) || empty($data['detalles'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "id_cliente", "id_empleado" y "detalles" son requeridos']);
                exit;
            }

            // Validar que detalles sea un array con al menos un item
            if (!is_array($data['detalles']) || count($data['detalles']) === 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Debe incluir al menos un producto en los detalles']);
                exit;
            }

            // Iniciar transacción
            $pdo->beginTransaction();

            try {
                // 1. Calcular el total del pedido sumando precio * cantidad por cada producto
                $total = 0;
                $detallesConPrecio = [];

                foreach ($data['detalles'] as $detalle) {
                    // Validar que cada detalle tenga id_producto y cantidad
                    if (empty($detalle['id_producto']) || empty($detalle['cantidad']) || $detalle['cantidad'] <= 0) {
                        throw new Exception('Cada detalle debe tener "id_producto" y "cantidad" válidos');
                    }

                    // Obtener el precio actual y stock del producto
                    $stmtProducto = $pdo->prepare("SELECT precio, nombre, stock FROM productos WHERE id_producto = ? FOR UPDATE");
                    $stmtProducto->execute([$detalle['id_producto']]);
                    $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

                    if (!$producto) {
                        throw new Exception('Producto con ID ' . $detalle['id_producto'] . ' no encontrado');
                    }

                    // Validar que haya stock suficiente
                    if ($producto['stock'] < $detalle['cantidad']) {
                        throw new Exception('Stock insuficiente para el producto: ' . $producto['nombre']);
                    }

                    $subtotal = $producto['precio'] * $detalle['cantidad'];
                    $total += $subtotal;

                    $detallesConPrecio[] = [
                        'id_producto' => $detalle['id_producto'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $producto['precio'],
                        'subtotal' => $subtotal
                    ];
                }

                // 2. Insertar el pedido
                $stmtPedido = $pdo->prepare(
                    "INSERT INTO pedidos (id_cliente, id_empleado, total, estado, metodo_pago) 
                     VALUES (?, ?, ?, 'pendiente', ?)"
                );
                $stmtPedido->execute([
                    $data['id_cliente'],
                    $data['id_empleado'],
                    $total,
                    isset($data['metodo_pago']) ? $data['metodo_pago'] : 'efectivo'
                ]);

                $idPedido = $pdo->lastInsertId();

                // 3. Insertar cada detalle del pedido y descontar el stock
                $stmtDetalle = $pdo->prepare(
                    "INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) 
                     VALUES (?, ?, ?, ?, ?)"
                );
                
                $stmtStock = $pdo->prepare(
                    "UPDATE productos SET stock = stock - ? WHERE id_producto = ?"
                );

                foreach ($detallesConPrecio as $det) {
                    // Insertar detalle
                    $stmtDetalle->execute([
                        $idPedido,
                        $det['id_producto'],
                        $det['cantidad'],
                        $det['precio_unitario'],
                        $det['subtotal']
                    ]);
                    
                    // Descontar stock
                    $stmtStock->execute([
                        $det['cantidad'],
                        $det['id_producto']
                    ]);
                }

                // Confirmar transacción
                $pdo->commit();

                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido creado exitosamente',
                    'id' => $idPedido,
                    'total' => $total
                ]);

            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        // =============================================
        // PUT - Actualizar estado y método de pago del pedido
        // =============================================
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['id_pedido'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "id_pedido" es requerido']);
                exit;
            }

            // Construir la actualización dinámicamente según los campos enviados
            $campos = [];
            $valores = [];

            if (isset($data['estado'])) {
                // Validar estados permitidos (según el ENUM de la BD)
                $estadosValidos = ['pendiente', 'en_preparacion', 'completado', 'cancelado'];
                if (!in_array($data['estado'], $estadosValidos)) {
                    http_response_code(400);
                    echo json_encode([
                        'error' => 'Estado no válido. Estados permitidos: ' . implode(', ', $estadosValidos)
                    ]);
                    exit;
                }
                $campos[] = "estado = ?";
                $valores[] = $data['estado'];
            }

            if (isset($data['metodo_pago'])) {
                $campos[] = "metodo_pago = ?";
                $valores[] = $data['metodo_pago'];
            }

            if (empty($campos)) {
                http_response_code(400);
                echo json_encode(['error' => 'Debe enviar al menos "estado" o "metodo_pago" para actualizar']);
                exit;
            }

            $valores[] = $data['id_pedido'];

            $stmt = $pdo->prepare(
                "UPDATE pedidos SET " . implode(', ', $campos) . " WHERE id_pedido = ?"
            );
            $stmt->execute($valores);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Pedido no encontrado o sin cambios']);
            }
            break;

        // =============================================
        // DELETE - Eliminar pedido (detalles se borran en CASCADE)
        // =============================================
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar ID
            if (empty($data['id_pedido'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "id_pedido" es requerido']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
            $stmt->execute([$data['id_pedido']]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido eliminado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Pedido no encontrado']);
            }
            break;

        // =============================================
        // Método no permitido
        // =============================================
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
