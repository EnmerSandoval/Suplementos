<?php
/**
 * Modelo de Usuario
 * Maneja todas las operaciones relacionadas con usuarios
 */

class User {
    private $db;
    private $table = 'usuarios';

    // Propiedades del usuario
    public $id_usuario;
    public $id_rol;
    public $id_sucursal_principal;
    public $nombre_completo;
    public $usuario;
    public $contrasena;
    public $email;
    public $telefono;
    public $estado;
    public $fecha_creacion;
    public $fecha_modificacion;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Autenticar usuario por credenciales
     *
     * @param string $usuario Nombre de usuario
     * @param string $contrasena Contraseña en texto plano
     * @return array|false Datos del usuario o false si falla
     */
    public function authenticate($usuario, $contrasena) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.id_sucursal_principal,
                    u.nombre_completo,
                    u.usuario,
                    u.contrasena,
                    u.email,
                    u.telefono,
                    u.estado,
                    r.nombre as rol,
                    s.nombre as sucursal
                FROM {$this->table} u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal
                WHERE u.usuario = :usuario
            ");

            $stmt->execute(['usuario' => $usuario]);
            $user = $stmt->fetch();

            // Verificar si el usuario existe
            if (!$user) {
                return false;
            }

            // Verificar si el usuario está activo
            if ($user['estado'] != 1) {
                return false;
            }

            // Verificar contraseña
            if (!password_verify($contrasena, $user['contrasena'])) {
                return false;
            }

            // Remover contraseña del resultado
            unset($user['contrasena']);

            return $user;

        } catch (PDOException $e) {
            error_log("Error en authenticate: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuario por ID
     *
     * @param int $id_usuario ID del usuario
     * @return array|false Datos del usuario o false
     */
    public function findById($id_usuario) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    u.id_usuario,
                    u.id_rol,
                    u.id_sucursal_principal,
                    u.nombre_completo,
                    u.usuario,
                    u.email,
                    u.telefono,
                    u.estado,
                    u.fecha_creacion,
                    u.fecha_modificacion,
                    r.nombre as rol,
                    s.nombre as sucursal
                FROM {$this->table} u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal
                WHERE u.id_usuario = :id_usuario
            ");

            $stmt->execute(['id_usuario' => $id_usuario]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Error en findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar usuario por nombre de usuario
     *
     * @param string $usuario Nombre de usuario
     * @return array|false Datos del usuario o false
     */
    public function findByUsername($usuario) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE usuario = :usuario
            ");

            $stmt->execute(['usuario' => $usuario]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Error en findByUsername: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo usuario
     *
     * @param array $data Datos del usuario
     * @return int|false ID del usuario creado o false
     */
    public function create($data) {
        try {
            // Validar que el usuario no exista
            if ($this->findByUsername($data['usuario'])) {
                throw new Exception('El nombre de usuario ya existe');
            }

            // Hashear contraseña
            $hashedPassword = password_hash($data['contrasena'], PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    id_rol,
                    id_sucursal_principal,
                    nombre_completo,
                    usuario,
                    contrasena,
                    email,
                    telefono,
                    estado
                ) VALUES (
                    :id_rol,
                    :id_sucursal_principal,
                    :nombre_completo,
                    :usuario,
                    :contrasena,
                    :email,
                    :telefono,
                    :estado
                )
            ");

            $result = $stmt->execute([
                'id_rol' => $data['id_rol'],
                'id_sucursal_principal' => $data['id_sucursal_principal'],
                'nombre_completo' => $data['nombre_completo'],
                'usuario' => $data['usuario'],
                'contrasena' => $hashedPassword,
                'email' => $data['email'],
                'telefono' => $data['telefono'] ?? null,
                'estado' => $data['estado'] ?? 1
            ]);

            if ($result) {
                return $this->db->lastInsertId();
            }

            return false;

        } catch (PDOException $e) {
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar usuario
     *
     * @param int $id_usuario ID del usuario
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó, false si falló
     */
    public function update($id_usuario, $data) {
        try {
            $fields = [];
            $params = ['id_usuario' => $id_usuario];

            // Construir campos dinámicamente
            if (isset($data['nombre_completo'])) {
                $fields[] = "nombre_completo = :nombre_completo";
                $params['nombre_completo'] = $data['nombre_completo'];
            }

            if (isset($data['email'])) {
                $fields[] = "email = :email";
                $params['email'] = $data['email'];
            }

            if (isset($data['telefono'])) {
                $fields[] = "telefono = :telefono";
                $params['telefono'] = $data['telefono'];
            }

            if (isset($data['id_rol'])) {
                $fields[] = "id_rol = :id_rol";
                $params['id_rol'] = $data['id_rol'];
            }

            if (isset($data['id_sucursal_principal'])) {
                $fields[] = "id_sucursal_principal = :id_sucursal_principal";
                $params['id_sucursal_principal'] = $data['id_sucursal_principal'];
            }

            if (isset($data['estado'])) {
                $fields[] = "estado = :estado";
                $params['estado'] = $data['estado'];
            }

            if (empty($fields)) {
                return false;
            }

            $fields[] = "fecha_modificacion = NOW()";

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id_usuario = :id_usuario";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar contraseña de usuario
     *
     * @param int $id_usuario ID del usuario
     * @param string $nuevaContrasena Nueva contraseña
     * @return bool True si se actualizó, false si falló
     */
    public function changePassword($id_usuario, $nuevaContrasena) {
        try {
            $hashedPassword = password_hash($nuevaContrasena, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET contrasena = :contrasena,
                    fecha_modificacion = NOW()
                WHERE id_usuario = :id_usuario
            ");

            return $stmt->execute([
                'contrasena' => $hashedPassword,
                'id_usuario' => $id_usuario
            ]);

        } catch (PDOException $e) {
            error_log("Error en changePassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los usuarios
     *
     * @param array $filters Filtros opcionales (estado, id_rol, id_sucursal)
     * @return array Lista de usuarios
     */
    public function getAll($filters = []) {
        try {
            $where = [];
            $params = [];

            if (isset($filters['estado'])) {
                $where[] = "u.estado = :estado";
                $params['estado'] = $filters['estado'];
            }

            if (isset($filters['id_rol'])) {
                $where[] = "u.id_rol = :id_rol";
                $params['id_rol'] = $filters['id_rol'];
            }

            if (isset($filters['id_sucursal'])) {
                $where[] = "u.id_sucursal_principal = :id_sucursal";
                $params['id_sucursal'] = $filters['id_sucursal'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $this->db->prepare("
                SELECT
                    u.id_usuario,
                    u.nombre_completo,
                    u.usuario,
                    u.email,
                    u.telefono,
                    u.estado,
                    u.fecha_creacion,
                    r.nombre as rol,
                    s.nombre as sucursal
                FROM {$this->table} u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal
                {$whereClause}
                ORDER BY u.fecha_creacion DESC
            ");

            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Eliminar usuario (soft delete - cambiar estado a 0)
     *
     * @param int $id_usuario ID del usuario
     * @return bool True si se eliminó, false si falló
     */
    public function delete($id_usuario) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET estado = 0,
                    fecha_modificacion = NOW()
                WHERE id_usuario = :id_usuario
            ");

            return $stmt->execute(['id_usuario' => $id_usuario]);

        } catch (PDOException $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si un usuario tiene permisos específicos
     *
     * @param int $id_usuario ID del usuario
     * @param string $permiso Nombre del permiso
     * @return bool True si tiene el permiso, false si no
     */
    public function hasPermission($id_usuario, $permiso) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN permisos_rol pr ON r.id_rol = pr.id_rol
                INNER JOIN permisos p ON pr.id_permiso = p.id_permiso
                WHERE u.id_usuario = :id_usuario
                AND p.nombre = :permiso
                AND u.estado = 1
            ");

            $stmt->execute([
                'id_usuario' => $id_usuario,
                'permiso' => $permiso
            ]);

            $result = $stmt->fetch();
            return $result['count'] > 0;

        } catch (PDOException $e) {
            error_log("Error en hasPermission: " . $e->getMessage());
            return false;
        }
    }
}
