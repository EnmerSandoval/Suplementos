<?php
/**
 * Configuración general del sistema
 */

// Zona horaria
date_default_timezone_set('America/Guatemala');

// Configuración de errores (cambiar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Configuración JWT
define('JWT_SECRET_KEY', 'tu_clave_secreta_muy_segura_cambiar_en_produccion');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION_TIME', 28800); // 8 horas en segundos

// Configuración de la API
define('API_VERSION', 'v1');
define('API_BASE_PATH', '/api/' . API_VERSION);

// Configuración de CORS
define('CORS_ALLOWED_ORIGINS', '*'); // Cambiar en producción a dominio específico
define('CORS_ALLOWED_METHODS', 'GET, POST, PUT, DELETE, OPTIONS');
define('CORS_ALLOWED_HEADERS', 'Content-Type, Authorization');

// Configuración de paginación
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Rutas del sistema
define('BASE_PATH', dirname(dirname(__DIR__)));
define('SRC_PATH', BASE_PATH . '/src');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Autoload de clases
spl_autoload_register(function ($class) {
    $directories = [
        SRC_PATH . '/controllers/',
        SRC_PATH . '/models/',
        SRC_PATH . '/middleware/',
        SRC_PATH . '/utils/',
        SRC_PATH . '/config/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Función auxiliar para respuestas JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función auxiliar para respuestas de error
function errorResponse($message, $statusCode = 400, $errors = []) {
    jsonResponse([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], $statusCode);
}

// Función auxiliar para respuestas exitosas
function successResponse($data = [], $message = 'Operación exitosa') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], 200);
}
