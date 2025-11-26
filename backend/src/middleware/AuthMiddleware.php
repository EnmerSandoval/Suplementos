<?php
/**
 * Middleware de autenticación
 */

class AuthMiddleware {

    /**
     * Verificar que el usuario esté autenticado
     */
    public static function authenticate() {
        try {
            $payload = JWT::validateToken();
            return $payload;
        } catch (Exception $e) {
            errorResponse('No autorizado: ' . $e->getMessage(), 401);
        }
    }

    /**
     * Verificar que el usuario tenga un rol específico
     */
    public static function requireRole($allowedRoles = []) {
        $payload = self::authenticate();

        if (!in_array($payload['rol'], $allowedRoles)) {
            errorResponse('No tiene permisos para realizar esta acción', 403);
        }

        return $payload;
    }

    /**
     * Verificar que el usuario sea administrador
     */
    public static function requireAdmin() {
        return self::requireRole(['Administrador']);
    }

    /**
     * Verificar que el usuario sea vendedor o administrador
     */
    public static function requireVendedor() {
        return self::requireRole(['Administrador', 'Vendedor']);
    }

    /**
     * Verificar que el usuario pertenezca a una sucursal específica
     */
    public static function checkSucursalAccess($idSucursal) {
        $payload = self::authenticate();

        // Los administradores tienen acceso a todas las sucursales
        if ($payload['rol'] === 'Administrador') {
            return true;
        }

        // Los vendedores solo pueden acceder a su sucursal asignada
        if ($payload['id_sucursal_principal'] != $idSucursal) {
            errorResponse('No tiene acceso a esta sucursal', 403);
        }

        return true;
    }
}
