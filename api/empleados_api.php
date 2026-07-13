<?php
/**
 * API REST para Empleados - Sistema de Gestión de Cafetería
 * 
 * Endpoints:
 *   GET    - Listar todos, obtener por ID, buscar por nombre/apellido/puesto
 *   POST   - Crear nuevo empleado
 *   PUT    - Actualizar empleado existente
 *   DELETE - Eliminar empleado (con validación de FK)
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
        // GET - Obtener empleados
        // =============================================
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener un empleado por ID
                $stmt = $pdo->prepare("SELECT * FROM empleados WHERE id_empleado = ?");
                $stmt->execute([$_GET['id']]);
                $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($empleado) {
                    echo json_encode($empleado);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Empleado no encontrado']);
                }

            } elseif (isset($_GET['search'])) {
                // Buscar empleados por nombre, apellido o puesto
                $busqueda = '%' . $_GET['search'] . '%';
                $stmt = $pdo->prepare(
                    "SELECT * FROM empleados 
                     WHERE nombre LIKE ? OR apellido LIKE ? OR puesto LIKE ? 
                     ORDER BY apellido, nombre"
                );
                $stmt->execute([$busqueda, $busqueda, $busqueda]);
                $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($empleados);

            } else {
                // Listar todos los empleados
                $stmt = $pdo->query("SELECT * FROM empleados ORDER BY apellido, nombre");
                $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($empleados);
            }
            break;

        // =============================================
        // POST - Crear nuevo empleado
        // =============================================
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['nombre']) || empty($data['apellido']) || empty($data['puesto']) || empty($data['fecha_contratacion'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "nombre", "apellido", "puesto" y "fecha_contratacion" son requeridos']);
                exit;
            }

            $stmt = $pdo->prepare(
                "INSERT INTO empleados (nombre, apellido, puesto, telefono, fecha_contratacion, activo) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                trim($data['nombre']),
                trim($data['apellido']),
                trim($data['puesto']),
                isset($data['telefono']) ? trim($data['telefono']) : null,
                $data['fecha_contratacion'],
                isset($data['activo']) ? $data['activo'] : 1
            ]);

            $id = $pdo->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'id' => $id
            ]);
            break;

        // =============================================
        // PUT - Actualizar empleado existente
        // =============================================
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['id_empleado']) || empty($data['nombre']) || empty($data['apellido']) || empty($data['puesto']) || empty($data['fecha_contratacion'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "id_empleado", "nombre", "apellido", "puesto" y "fecha_contratacion" son requeridos']);
                exit;
            }

            $stmt = $pdo->prepare(
                "UPDATE empleados 
                 SET nombre = ?, apellido = ?, puesto = ?, telefono = ?, fecha_contratacion = ?, activo = ? 
                 WHERE id_empleado = ?"
            );
            $stmt->execute([
                trim($data['nombre']),
                trim($data['apellido']),
                trim($data['puesto']),
                isset($data['telefono']) ? trim($data['telefono']) : null,
                $data['fecha_contratacion'],
                isset($data['activo']) ? $data['activo'] : 1,
                $data['id_empleado']
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Empleado actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Empleado no encontrado o sin cambios']);
            }
            break;

        // =============================================
        // DELETE - Eliminar empleado
        // =============================================
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar ID
            if (empty($data['id_empleado'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "id_empleado" es requerido']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM empleados WHERE id_empleado = ?");
                $stmt->execute([$data['id_empleado']]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Empleado eliminado exitosamente'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Empleado no encontrado']);
                }
            } catch (PDOException $e) {
                // Error de clave foránea: el empleado tiene pedidos asociados
                if ($e->getCode() == '23000') {
                    http_response_code(409);
                    echo json_encode([
                        'error' => 'No se puede eliminar el empleado porque tiene pedidos asociados'
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
