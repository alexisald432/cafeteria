<?php
/**
 * API REST para Productos - Sistema de Gestión de Cafetería
 * 
 * Endpoints:
 *   GET    - Listar todos (con JOIN a categorías), obtener por ID, buscar por nombre
 *   POST   - Crear nuevo producto (con validaciones de precio y stock)
 *   PUT    - Actualizar producto existente
 *   DELETE - Eliminar producto (con validación de FK)
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
        // GET - Obtener productos (con nombre de categoría)
        // =============================================
        case 'GET':
            // Query base con JOIN para incluir el nombre de la categoría
            $queryBase = "SELECT p.*, c.nombre AS categoria_nombre 
                          FROM productos p 
                          INNER JOIN categorias c ON p.id_categoria = c.id_categoria";

            if (isset($_GET['id'])) {
                // Obtener un producto por ID
                $stmt = $pdo->prepare($queryBase . " WHERE p.id_producto = ?");
                $stmt->execute([$_GET['id']]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($producto) {
                    echo json_encode($producto);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                }

            } elseif (isset($_GET['search'])) {
                // Buscar productos por nombre
                $busqueda = '%' . $_GET['search'] . '%';
                $stmt = $pdo->prepare($queryBase . " WHERE p.nombre LIKE ? ORDER BY p.nombre");
                $stmt->execute([$busqueda]);
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($productos);

            } else {
                // Listar todos los productos
                $stmt = $pdo->query($queryBase . " ORDER BY p.nombre");
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($productos);
            }
            break;

        // =============================================
        // POST - Crear nuevo producto
        // =============================================
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['nombre']) || !isset($data['precio']) || !isset($data['stock']) || empty($data['id_categoria'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "nombre", "precio", "stock" e "id_categoria" son requeridos']);
                exit;
            }

            // Validar que el precio sea mayor a 0
            if ($data['precio'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'El precio debe ser mayor a 0']);
                exit;
            }

            // Validar que el stock sea >= 0
            if ($data['stock'] < 0) {
                http_response_code(400);
                echo json_encode(['error' => 'El stock no puede ser negativo']);
                exit;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO productos (nombre, descripcion, precio, stock, id_categoria, imagen, activo) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                trim($data['nombre']),
                isset($data['descripcion']) ? trim($data['descripcion']) : null,
                $data['precio'],
                $data['stock'],
                $data['id_categoria'],
                isset($data['imagen']) ? trim($data['imagen']) : null,
                isset($data['activo']) ? $data['activo'] : 1
            ]);

            $id = $pdo->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Producto creado exitosamente',
                'id' => $id
            ]);
            break;

        // =============================================
        // PUT - Actualizar producto existente
        // =============================================
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['id_producto']) || empty($data['nombre']) || !isset($data['precio']) || !isset($data['stock']) || empty($data['id_categoria'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "id_producto", "nombre", "precio", "stock" e "id_categoria" son requeridos']);
                exit;
            }

            // Validar precio y stock
            if ($data['precio'] <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'El precio debe ser mayor a 0']);
                exit;
            }

            if ($data['stock'] < 0) {
                http_response_code(400);
                echo json_encode(['error' => 'El stock no puede ser negativo']);
                exit;
            }

            $stmt = $pdo->prepare(
                "UPDATE productos 
                 SET nombre = ?, descripcion = ?, precio = ?, stock = ?, id_categoria = ?, imagen = ?, activo = ? 
                 WHERE id_producto = ?"
            );
            $stmt->execute([
                trim($data['nombre']),
                isset($data['descripcion']) ? trim($data['descripcion']) : null,
                $data['precio'],
                $data['stock'],
                $data['id_categoria'],
                isset($data['imagen']) ? trim($data['imagen']) : null,
                isset($data['activo']) ? $data['activo'] : 1,
                $data['id_producto']
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado o sin cambios']);
            }
            break;

        // =============================================
        // DELETE - Eliminar producto
        // =============================================
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar ID
            if (empty($data['id_producto'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "id_producto" es requerido']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
                $stmt->execute([$data['id_producto']]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Producto eliminado exitosamente'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                }
            } catch (PDOException $e) {
                // Error de clave foránea: el producto está en pedidos
                if ($e->getCode() == '23000') {
                    http_response_code(409);
                    echo json_encode([
                        'error' => 'No se puede eliminar el producto porque está asociado a pedidos existentes'
                    ]);
                } else {
                    throw $e;
                }
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
