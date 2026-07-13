<?php
/**
 * API REST para Categorías - Sistema de Gestión de Cafetería
 * 
 * Endpoints:
 *   GET    - Listar todas, obtener por ID, buscar por nombre
 *   POST   - Crear nueva categoría
 *   PUT    - Actualizar categoría existente
 *   DELETE - Eliminar categoría (con validación de FK)
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
        // GET - Obtener categorías
        // =============================================
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener una categoría por ID
                $stmt = $pdo->prepare("SELECT * FROM categorias WHERE id_categoria = ?");
                $stmt->execute([$_GET['id']]);
                $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($categoria) {
                    echo json_encode($categoria);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Categoría no encontrada']);
                }

            } elseif (isset($_GET['search'])) {
                // Buscar categorías por nombre
                $busqueda = '%' . $_GET['search'] . '%';
                $stmt = $pdo->prepare("SELECT * FROM categorias WHERE nombre LIKE ? ORDER BY nombre");
                $stmt->execute([$busqueda]);
                $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($categorias);

            } else {
                // Listar todas las categorías
                $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre");
                $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($categorias);
            }
            break;

        // =============================================
        // POST - Crear nueva categoría
        // =============================================
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['nombre'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "nombre" es requerido']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([
                trim($data['nombre']),
                isset($data['descripcion']) ? trim($data['descripcion']) : null
            ]);

            $id = $pdo->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'id' => $id
            ]);
            break;

        // =============================================
        // PUT - Actualizar categoría existente
        // =============================================
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar campos requeridos
            if (empty($data['id_categoria']) || empty($data['nombre'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Los campos "id_categoria" y "nombre" son requeridos']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id_categoria = ?");
            $stmt->execute([
                trim($data['nombre']),
                isset($data['descripcion']) ? trim($data['descripcion']) : null,
                $data['id_categoria']
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría actualizada exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Categoría no encontrada o sin cambios']);
            }
            break;

        // =============================================
        // DELETE - Eliminar categoría
        // =============================================
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar ID
            if (empty($data['id_categoria'])) {
                http_response_code(400);
                echo json_encode(['error' => 'El campo "id_categoria" es requerido']);
                exit;
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM categorias WHERE id_categoria = ?");
                $stmt->execute([$data['id_categoria']]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Categoría eliminada exitosamente'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Categoría no encontrada']);
                }
            } catch (PDOException $e) {
                // Error de clave foránea: la categoría tiene productos asignados
                if ($e->getCode() == '23000') {
                    http_response_code(409);
                    echo json_encode([
                        'error' => 'No se puede eliminar la categoría porque tiene productos asignados'
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
