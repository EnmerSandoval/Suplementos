<?php
/**
 * Utilidad para generar hashes de contraseñas
 *
 * USO:
 * php backend/src/utils/GenerateHash.php "tu_contraseña"
 *
 * O incluirlo en otro script:
 * require_once 'GenerateHash.php';
 * $hash = GenerateHash::create("tu_contraseña");
 */

class GenerateHash {

    /**
     * Genera un hash usando PASSWORD_DEFAULT (actualmente Bcrypt)
     *
     * @param string $password La contraseña a hashear
     * @return string El hash generado
     */
    public static function create($password) {
        if (empty($password)) {
            throw new Exception("La contraseña no puede estar vacía");
        }

        // PASSWORD_DEFAULT usa actualmente BCRYPT
        // Opciones: cost = 12 (más seguro, pero más lento)
        $options = [
            'cost' => 12,
        ];

        return password_hash($password, PASSWORD_DEFAULT, $options);
    }

    /**
     * Genera un hash usando Argon2i (si está disponible)
     *
     * @param string $password La contraseña a hashear
     * @return string El hash generado
     */
    public static function createArgon2($password) {
        if (empty($password)) {
            throw new Exception("La contraseña no puede estar vacía");
        }

        // Verificar si Argon2 está disponible
        if (!defined('PASSWORD_ARGON2I')) {
            throw new Exception("Argon2 no está disponible en esta instalación de PHP");
        }

        return password_hash($password, PASSWORD_ARGON2I);
    }

    /**
     * Verifica si un hash es válido para una contraseña
     *
     * @param string $password La contraseña en texto plano
     * @param string $hash El hash a verificar
     * @return bool True si coincide, false si no
     */
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Genera múltiples hashes para pruebas
     *
     * @param array $passwords Array de contraseñas
     * @return array Array asociativo de contraseña => hash
     */
    public static function createBatch($passwords) {
        $results = [];
        foreach ($passwords as $password) {
            $results[$password] = self::create($password);
        }
        return $results;
    }
}

// Si se ejecuta directamente desde línea de comandos
if (php_sapi_name() === 'cli') {
    echo "\n=== GENERADOR DE HASHES DE CONTRASEÑAS ===\n\n";

    // Verificar si se pasó una contraseña como argumento
    if ($argc > 1) {
        $password = $argv[1];

        try {
            echo "Contraseña: $password\n";
            echo "----------------------------------------\n\n";

            // Generar hash con Bcrypt (PASSWORD_DEFAULT)
            $hashBcrypt = GenerateHash::create($password);
            echo "HASH (Bcrypt - PASSWORD_DEFAULT):\n";
            echo "$hashBcrypt\n\n";

            // Intentar generar con Argon2 si está disponible
            try {
                $hashArgon2 = GenerateHash::createArgon2($password);
                echo "HASH (Argon2i):\n";
                echo "$hashArgon2\n\n";
            } catch (Exception $e) {
                echo "Argon2: No disponible\n\n";
            }

            // Verificar que el hash funciona
            if (GenerateHash::verify($password, $hashBcrypt)) {
                echo "✓ Verificación exitosa - El hash es compatible con password_verify()\n\n";
            } else {
                echo "✗ Error en verificación\n\n";
            }

            echo "Para insertar en la BD, copia el HASH de arriba.\n";
            echo "Ejemplo SQL:\n";
            echo "INSERT INTO usuarios (usuario, contrasena, nombre_completo, email, id_rol, id_sucursal_principal) \n";
            echo "VALUES ('admin', '$hashBcrypt', 'Administrador', 'admin@example.com', 1, 1);\n\n";

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n\n";
            exit(1);
        }

    } else {
        // Modo interactivo
        echo "Ingresa la contraseña que deseas hashear:\n";
        $password = trim(fgets(STDIN));

        if (empty($password)) {
            echo "Error: La contraseña no puede estar vacía\n";
            exit(1);
        }

        try {
            $hash = GenerateHash::create($password);
            echo "\nContraseña: $password\n";
            echo "----------------------------------------\n";
            echo "HASH:\n";
            echo "$hash\n\n";

            // Verificar
            if (GenerateHash::verify($password, $hash)) {
                echo "✓ Hash verificado correctamente\n\n";
            }

            echo "Copia el hash de arriba para insertarlo en la base de datos.\n\n";

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}
