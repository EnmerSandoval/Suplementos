<?php
/**
 * Controlador de autenticación
 */

class AuthController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Login de usuario
     * POST /api/v1/auth/login
     */
    public function login() {
        $data = Router::getRequestBody();

        // Validar datos requeridos
        if (empty($data['usuario']) || empty($data['contrasena'])) {
            errorResponse('Usuario y contraseña son requeridos', 400);
        }

        try {
            // Buscar usuario
            $stmt = $this->db->prepare("
                SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.id_sucursal_principal,
                    u.nombre_completo,
                    u.usuario,
                    u.contrasena,
                    u.email,
                    u.estado,
                    r.nombre as rol,
                    s.nombre as sucursal
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal
                WHERE u.usuario = :usuario
            ");

            $stmt->execute(['usuario' => $data['usuario']]);
            $user = $stmt->fetch();

            // Verificar si el usuario existe
            if (!$user) {
                errorResponse('Credenciales inválidas', 401);
            }

            // Verificar si el usuario está activo
            if ($user['estado'] != 1) {
                errorResponse('Usuario inactivo', 401);
            }

            // Verificar contraseña
            if (!password_verify($data['contrasena'], $user['contrasena'])) {
                errorResponse('Credenciales inválidas', 401);
            }

            // Generar token JWT
            $payload = [
                'id_usuario' => $user['id_usuario'],
                'usuario' => $user['usuario'],
                'nombre_completo' => $user['nombre_completo'],
                'rol' => $user['rol'],
                'id_rol' => $user['id_rol'],
                'id_sucursal_principal' => $user['id_sucursal_principal'],
                'sucursal' => $user['sucursal']
            ];

            $token = JWT::encode($payload);

            // Respuesta exitosa
            successResponse([
                'token' => $token,
                'usuario' => [
                    'id_usuario' => $user['id_usuario'],
                    'nombre_completo' => $user['nombre_completo'],
                    'usuario' => $user['usuario'],
                    'email' => $user['email'],
                    'rol' => $user['rol'],
                    'sucursal' => $user['sucursal']
                ]
            ], 'Login exitoso');

        } catch (Exception $e) {
            errorResponse('Error al procesar login: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     * GET /api/v1/auth/me
     */
    public function me() {
        try {
            // Validar token y obtener payload
            $payload = AuthMiddleware::authenticate();

            // Obtener información actualizada del usuario
            $stmt = $this->db->prepare("
                SELECT
                    u.id_usuario,
                    u.nombre_completo,
                    u.usuario,
                    u.email,
                    u.telefono,
                    r.nombre as rol,
                    s.nombre as sucursal,
                    s.id_sucursal
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal
                WHERE u.id_usuario = :id_usuario AND u.estado = 1
            ");

            $stmt->execute(['id_usuario' => $payload['id_usuario']]);
            $user = $stmt->fetch();

            if (!$user) {
                errorResponse('Usuario no encontrado', 404);
            }

            successResponse($user);

        } catch (Exception $e) {
            errorResponse('Error al obtener información del usuario: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Logout (invalidar token - del lado del cliente)
     * POST /api/v1/auth/logout
     */
    public function logout() {
        AuthMiddleware::authenticate();

        // En una implementación con JWT sin estado, el logout se maneja en el cliente
        // eliminando el token. Aquí solo confirmamos que el token era válido.

        successResponse([], 'Logout exitoso');
    }

    /**
     * Cambiar contraseña
     * POST /api/v1/auth/change-password
     */
    public function changePassword() {
        $payload = AuthMiddleware::authenticate();
        $data = Router::getRequestBody();

        // Validar datos requeridos
        if (empty($data['contrasena_actual']) || empty($data['contrasena_nueva'])) {
            errorResponse('Contraseña actual y nueva contraseña son requeridas', 400);
        }

        // Validar longitud de contraseña nueva
        if (strlen($data['contrasena_nueva']) < 6) {
            errorResponse('La contraseña nueva debe tener al menos 6 caracteres', 400);
        }

        try {
            $this->db->beginTransaction();

            // Obtener contraseña actual del usuario
            $stmt = $this->db->prepare("SELECT contrasena FROM usuarios WHERE id_usuario = :id_usuario");
            $stmt->execute(['id_usuario' => $payload['id_usuario']]);
            $user = $stmt->fetch();

            // Verificar contraseña actual
            if (!password_verify($data['contrasena_actual'], $user['contrasena'])) {
                errorResponse('Contraseña actual incorrecta', 400);
            }

            // Actualizar contraseña
            $nuevaContrasenaHash = password_hash($data['contrasena_nueva'], PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                UPDATE usuarios
                SET contrasena = :contrasena
                WHERE id_usuario = :id_usuario
            ");

            $stmt->execute([
                'contrasena' => $nuevaContrasenaHash,
                'id_usuario' => $payload['id_usuario']
            ]);

            $this->db->commit();

            successResponse([], 'Contraseña actualizada exitosamente');

        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Error al cambiar contraseña: ' . $e->getMessage(), 500);
        }
    }
}
