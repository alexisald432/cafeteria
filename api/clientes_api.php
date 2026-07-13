<?php
/**
 * API REST para Clientes - Sistema de Gestión de Cafetería
 * 
 * Endpoints:
 *   GET    - Listar todos, obtener por ID, buscar por nombre/apellido/email
 *   POST   - Crear nuevo cliente
 *   PUT    - Actualizar cliente existente
 *   DELETE - Eliminar cliente (con validación de FK)
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
        // GET - Obtener clientes
        // =============================================
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener un cliente por ID
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
                $stmt->execute([$_GET['id']]);
                $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($cliente) {
                    echo json_encode($cliente);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Cliente no encontrado']);
                }

            } elseif (isset($_GET['search'])) {
                // Buscar clientes por nombre, apellido o email
                $busqueda = '%' . $_GET['search'] . '%';
                $stmt = $pdo->prepare(
                    "SELECT * FROM clientes 
                     WHERE nombre LIKE ? OR apellido LIKE ? OR email LIKE ? 
                     ORDER BY apellido, nombre"
                );
                $stmt->execute([$busqueda, $busqueda, $busqueda]);
                $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($clientes);

            } else {
                // Listar todos los clientes
                $stmt = $pdo->query("SELECT * FROM clientes ORDER BY apellido, nombre");
                $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($clientes);
            }
            break;

        // =============================================
        // POST - Crear nuevo cliente
        // =============================================
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['nombre']) || empty($data['apellido'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "nombre" y "apellido" son requeridos']);
                exit;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO clientes (nombre, apellido, email, telefono, direccion) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                trim($data['nombre']),
                trim($data['apellido']),
                isset($data['email']) ? trim($data['email']) : null,
                isset($data['telefono']) ? trim($data['telefono']) : null,
                isset($data['direccion']) ? trim($data['direccion']) : null
            ]);

            $id = $pdo->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'id' => $id
            ]);
            break;

        // =============================================
        // PUT - Actualizar cliente existente
        // =============================================
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['id_cliente']) || empty($data['nombre']) || empty($data['apellido'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "id_cliente", "nombre" y "apellido" son requeridos']);
                exit;
            }

            $stmt = $pdo->prepare(
                "UPDATE clientes 
                 SET nombre = ?, apellido = ?, email = ?, telefono = ?, direccion = ? 
                 WHERE id_cliente = ?"
            );
            $stmt->execute([
                trim($data['nombre']),
                trim($data['apellido']),
                isset($data['email']) ? trim($data['email']) : null,
                isset($data['telefono']) ? trim($data['telefono']) : null,
                isset($data['direccion']) ? trim($data['direccion']) : null,
                $data['id_cliente']
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Cliente no encontrado o sin cambios']);
            }
            break;

        // =============================================
        // DELETE - Eliminar cliente
        // =============================================
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar ID
            if (empty($data['id_cliente'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "id_cliente" es requerido']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM clientes WHERE id_cliente = ?");
                $stmt->execute([$data['id_cliente']]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Cliente eliminado exitosamente'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Cliente no encontrado']);
                }
            } catch (PDOException $e) {
                // Error de clave foránea: el cliente tiene pedidos asociados
                if ($e->getCode() == '23000') {
                    http_response_code(409);
                    echo json_encode([
                        'error' => 'No se puede eliminar el cliente porque tiene pedidos asociados'
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
