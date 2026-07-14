<?php
// Configuración de la base de datos — Sistema de Gestión de Cafetería
// Soporta variables de entorno para Railway y conexión local

// Primero, intentamos obtener y parsear la variable MYSQL_URL o DATABASE_URL que da Railway
$databaseUrl = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');

if ($databaseUrl) {
    // Formato típico: mysql://user:password@host:port/dbname
    $dbparts = parse_url($databaseUrl);
    $host = $dbparts['host'];
    $username = $dbparts['user'];
    $password = isset($dbparts['pass']) ? $dbparts['pass'] : '';
    $port = isset($dbparts['port']) ? $dbparts['port'] : '3306';
    $dbname = ltrim($dbparts['path'], '/');
} else {
    // Si no hay URL, usamos variables individuales o valores locales por defecto
    $host = getenv('MYSQLHOST') ?: (getenv('DB_HOST') ?: 'localhost');
    $dbname = getenv('MYSQLDATABASE') ?: (getenv('DB_NAME') ?: 'cafeteria_db');
    $username = getenv('MYSQLUSER') ?: (getenv('DB_USER') ?: 'root');
    $password = getenv('MYSQLPASSWORD') ?: (getenv('DB_PASS') ?: '');
    $port = getenv('MYSQLPORT') ?: (getenv('DB_PORT') ?: '3306');
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Error de conexión a la BD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error de conexión a la base de datos',
        'detalle' => $e->getMessage(),
        'debug_host' => $host,
        'debug_port' => $port,
        'debug_dbname' => $dbname
    ]);
    exit;
}
?>
