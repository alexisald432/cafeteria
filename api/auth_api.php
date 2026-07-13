<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_destroy();
        echo json_encode(['success' => true]);
        exit;
    }

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuario y contraseña son requeridos']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM empleados WHERE username = ? AND activo = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verificar contraseña (soporta texto plano para el seeder y hash para los nuevos)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id_empleado'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['puesto'] = $user['puesto'];
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Login exitoso',
                    'user' => [
                        'nombre' => $user['nombre'],
                        'puesto' => $user['puesto']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Contraseña incorrecta']);
            }
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Usuario no encontrado o inactivo']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error del servidor']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
