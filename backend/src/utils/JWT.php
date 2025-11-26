<?php
/**
 * Clase para manejo de JSON Web Tokens (JWT)
 * Implementación simple sin dependencias externas
 */

class JWT {

    /**
     * Generar un token JWT
     */
    public static function encode($payload) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];

        // Agregar tiempo de expiración
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRATION_TIME;

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            JWT_SECRET_KEY,
            true
        );

        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * Decodificar y validar un token JWT
     */
    public static function decode($token) {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new Exception('Token inválido');
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // Verificar firma
        $signature = self::base64UrlDecode($base64UrlSignature);
        $expectedSignature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            JWT_SECRET_KEY,
            true
        );

        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Firma del token inválida');
        }

        // Decodificar payload
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token expirado');
        }

        return $payload;
    }

    /**
     * Codificar en base64 URL-safe
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificar base64 URL-safe
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Obtener el token del header Authorization
     */
    public static function getTokenFromHeader() {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authHeader = $headers['Authorization'];

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Validar y obtener datos del usuario desde el token
     */
    public static function validateToken() {
        $token = self::getTokenFromHeader();

        if (!$token) {
            throw new Exception('Token no proporcionado');
        }

        try {
            $payload = self::decode($token);
            return $payload;
        } catch (Exception $e) {
            throw new Exception('Token inválido: ' . $e->getMessage());
        }
    }
}
