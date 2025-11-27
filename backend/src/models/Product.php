<?php
/**
 * Modelo de Producto
 * Maneja productos, lotes y stock por sucursal
 */

class Product {
    private $db;
    private $table = 'productos';
    private $tableLotes = 'lotes';
    private $tableInventario = 'inventario_sucursales';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los productos con información de stock
     *
     * @param array $filters Filtros (id_sucursal, categoria, activo)
     * @return array Lista de productos
     */
    public function getAll($filters = []) {
        try {
            $where = ["p.activo = 1"];
            $params = [];

            if (isset($filters['categoria'])) {
                $where[] = "p.categoria = :categoria";
                $params['categoria'] = $filters['categoria'];
            }

            if (isset($filters['buscar'])) {
                $where[] = "(p.nombre LIKE :buscar OR p.codigo_barras LIKE :buscar OR p.descripcion LIKE :buscar)";
                $params['buscar'] = '%' . $filters['buscar'] . '%';
            }

            $whereClause = implode(' AND ', $where);

            // Si se filtra por sucursal, incluir stock de esa sucursal
            if (isset($filters['id_sucursal'])) {
                $params['id_sucursal'] = $filters['id_sucursal'];

                $stmt = $this->db->prepare("
                    SELECT
                        p.*,
                        COALESCE(inv.stock_actual, 0) as stock_actual,
                        COALESCE(inv.stock_minimo, 0) as stock_minimo,
                        COALESCE(inv.stock_maximo, 0) as stock_maximo
                    FROM {$this->table} p
                    LEFT JOIN {$this->tableInventario} inv
                        ON p.id_producto = inv.id_producto
                        AND inv.id_sucursal = :id_sucursal
                    WHERE {$whereClause}
                    ORDER BY p.nombre ASC
                ");
            } else {
                // Sin filtro de sucursal, mostrar stock total
                $stmt = $this->db->prepare("
                    SELECT
                        p.*,
                        COALESCE(SUM(inv.stock_actual), 0) as stock_total
                    FROM {$this->table} p
                    LEFT JOIN {$this->tableInventario} inv ON p.id_producto = inv.id_producto
                    WHERE {$whereClause}
                    GROUP BY p.id_producto
                    ORDER BY p.nombre ASC
                ");
            }

            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en Product::getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar producto por ID
     *
     * @param int $id_producto ID del producto
     * @return array|false Datos del producto
     */
    public function findById($id_producto) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE id_producto = :id_producto
            ");

            $stmt->execute(['id_producto' => $id_producto]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Error en findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar producto por código de barras
     *
     * @param string $codigoBarras Código de barras
     * @return array|false Datos del producto
     */
    public function findByBarcode($codigoBarras) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE codigo_barras = :codigo_barras AND activo = 1
            ");

            $stmt->execute(['codigo_barras' => $codigoBarras]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Error en findByBarcode: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo producto
     *
     * @param array $data Datos del producto
     * @return int|false ID del producto creado o false
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    nombre,
                    descripcion,
                    codigo_barras,
                    categoria,
                    marca,
                    precio_compra,
                    precio_venta,
                    precio_mayoreo,
                    requiere_lote,
                    activo
                ) VALUES (
                    :nombre,
                    :descripcion,
                    :codigo_barras,
                    :categoria,
                    :marca,
                    :precio_compra,
                    :precio_venta,
                    :precio_mayoreo,
                    :requiere_lote,
                    :activo
                )
            ");

            $result = $stmt->execute([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'codigo_barras' => $data['codigo_barras'] ?? null,
                'categoria' => $data['categoria'] ?? null,
                'marca' => $data['marca'] ?? null,
                'precio_compra' => $data['precio_compra'],
                'precio_venta' => $data['precio_venta'],
                'precio_mayoreo' => $data['precio_mayoreo'] ?? $data['precio_venta'],
                'requiere_lote' => $data['requiere_lote'] ?? 0,
                'activo' => $data['activo'] ?? 1
            ]);

            if ($result) {
                $id_producto = $this->db->lastInsertId();
                $this->db->commit();
                return $id_producto;
            }

            $this->db->rollBack();
            return false;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar producto
     *
     * @param int $id_producto ID del producto
     * @param array $data Datos a actualizar
     * @return bool
     */
    public function update($id_producto, $data) {
        try {
            $fields = [];
            $params = ['id_producto' => $id_producto];

            $allowedFields = [
                'nombre', 'descripcion', 'codigo_barras', 'categoria', 'marca',
                'precio_compra', 'precio_venta', 'precio_mayoreo', 'requiere_lote', 'activo'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[$field] = $data[$field];
                }
            }

            if (empty($fields)) {
                return false;
            }

            $fields[] = "fecha_actualizacion = NOW()";

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id_producto = :id_producto";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Error en update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener stock de un producto en una sucursal específica
     *
     * @param int $id_producto ID del producto
     * @param int $id_sucursal ID de la sucursal
     * @return array Stock información
     */
    public function getStock($id_producto, $id_sucursal) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    inv.stock_actual,
                    inv.stock_minimo,
                    inv.stock_maximo,
                    inv.ultima_actualizacion
                FROM {$this->tableInventario} inv
                WHERE inv.id_producto = :id_producto
                AND inv.id_sucursal = :id_sucursal
            ");

            $stmt->execute([
                'id_producto' => $id_producto,
                'id_sucursal' => $id_sucursal
            ]);

            $result = $stmt->fetch();

            if (!$result) {
                return [
                    'stock_actual' => 0,
                    'stock_minimo' => 0,
                    'stock_maximo' => 0,
                    'ultima_actualizacion' => null
                ];
            }

            return $result;

        } catch (PDOException $e) {
            error_log("Error en getStock: " . $e->getMessage());
            return ['stock_actual' => 0];
        }
    }

    /**
     * Obtener lotes de un producto en una sucursal
     *
     * @param int $id_producto ID del producto
     * @param int $id_sucursal ID de la sucursal
     * @param bool $soloActivos Solo lotes con stock > 0
     * @return array Lista de lotes
     */
    public function getLotes($id_producto, $id_sucursal, $soloActivos = true) {
        try {
            $where = "l.id_producto = :id_producto AND l.id_sucursal = :id_sucursal";

            if ($soloActivos) {
                $where .= " AND l.cantidad_actual > 0";
            }

            $stmt = $this->db->prepare("
                SELECT
                    l.*,
                    CASE
                        WHEN l.fecha_vencimiento < CURDATE() THEN 'vencido'
                        WHEN l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'proximo_a_vencer'
                        ELSE 'vigente'
                    END as estado_vencimiento
                FROM {$this->tableLotes} l
                WHERE {$where}
                ORDER BY l.fecha_vencimiento ASC, l.fecha_ingreso ASC
            ");

            $stmt->execute([
                'id_producto' => $id_producto,
                'id_sucursal' => $id_sucursal
            ]);

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en getLotes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear nuevo lote de producto
     *
     * @param array $data Datos del lote
     * @return int|false ID del lote creado
     */
    public function createLote($data) {
        try {
            $this->db->beginTransaction();

            // Insertar lote
            $stmt = $this->db->prepare("
                INSERT INTO {$this->tableLotes} (
                    id_producto,
                    id_sucursal,
                    numero_lote,
                    fecha_vencimiento,
                    cantidad_inicial,
                    cantidad_actual,
                    precio_compra_unitario,
                    fecha_ingreso
                ) VALUES (
                    :id_producto,
                    :id_sucursal,
                    :numero_lote,
                    :fecha_vencimiento,
                    :cantidad,
                    :cantidad,
                    :precio_compra,
                    NOW()
                )
            ");

            $stmt->execute([
                'id_producto' => $data['id_producto'],
                'id_sucursal' => $data['id_sucursal'],
                'numero_lote' => $data['numero_lote'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'cantidad' => $data['cantidad'],
                'precio_compra' => $data['precio_compra']
            ]);

            $id_lote = $this->db->lastInsertId();

            // Actualizar inventario de la sucursal
            $this->updateInventarioAfterLote($data['id_producto'], $data['id_sucursal'], $data['cantidad']);

            $this->db->commit();
            return $id_lote;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en createLote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar inventario después de agregar un lote
     *
     * @param int $id_producto
     * @param int $id_sucursal
     * @param int $cantidad Cantidad a sumar
     */
    private function updateInventarioAfterLote($id_producto, $id_sucursal, $cantidad) {
        // Verificar si existe registro de inventario
        $stmt = $this->db->prepare("
            SELECT id_inventario FROM {$this->tableInventario}
            WHERE id_producto = :id_producto AND id_sucursal = :id_sucursal
        ");

        $stmt->execute([
            'id_producto' => $id_producto,
            'id_sucursal' => $id_sucursal
        ]);

        $inventario = $stmt->fetch();

        if ($inventario) {
            // Actualizar existente
            $stmt = $this->db->prepare("
                UPDATE {$this->tableInventario}
                SET stock_actual = stock_actual + :cantidad,
                    ultima_actualizacion = NOW()
                WHERE id_producto = :id_producto AND id_sucursal = :id_sucursal
            ");
        } else {
            // Crear nuevo
            $stmt = $this->db->prepare("
                INSERT INTO {$this->tableInventario} (
                    id_producto,
                    id_sucursal,
                    stock_actual,
                    stock_minimo,
                    stock_maximo
                ) VALUES (
                    :id_producto,
                    :id_sucursal,
                    :cantidad,
                    0,
                    0
                )
            ");
        }

        $stmt->execute([
            'id_producto' => $id_producto,
            'id_sucursal' => $id_sucursal,
            'cantidad' => $cantidad
        ]);
    }

    /**
     * Reducir stock de lotes (FIFO - First In First Out)
     * Utilizado al realizar ventas
     *
     * @param int $id_producto
     * @param int $id_sucursal
     * @param int $cantidad Cantidad a reducir
     * @return array|false Lotes afectados o false si no hay stock
     */
    public function reducirStock($id_producto, $id_sucursal, $cantidad) {
        try {
            $this->db->beginTransaction();

            // Obtener lotes disponibles ordenados por FIFO
            $lotes = $this->getLotes($id_producto, $id_sucursal, true);

            if (empty($lotes)) {
                $this->db->rollBack();
                return false;
            }

            // Calcular stock total disponible
            $stockTotal = array_sum(array_column($lotes, 'cantidad_actual'));

            if ($stockTotal < $cantidad) {
                $this->db->rollBack();
                return false; // Stock insuficiente
            }

            $lotesAfectados = [];
            $cantidadRestante = $cantidad;

            foreach ($lotes as $lote) {
                if ($cantidadRestante <= 0) {
                    break;
                }

                $cantidadARestar = min($lote['cantidad_actual'], $cantidadRestante);

                // Actualizar lote
                $stmt = $this->db->prepare("
                    UPDATE {$this->tableLotes}
                    SET cantidad_actual = cantidad_actual - :cantidad
                    WHERE id_lote = :id_lote
                ");

                $stmt->execute([
                    'cantidad' => $cantidadARestar,
                    'id_lote' => $lote['id_lote']
                ]);

                $lotesAfectados[] = [
                    'id_lote' => $lote['id_lote'],
                    'numero_lote' => $lote['numero_lote'],
                    'cantidad_restada' => $cantidadARestar
                ];

                $cantidadRestante -= $cantidadARestar;
            }

            // Actualizar inventario general
            $stmt = $this->db->prepare("
                UPDATE {$this->tableInventario}
                SET stock_actual = stock_actual - :cantidad,
                    ultima_actualizacion = NOW()
                WHERE id_producto = :id_producto AND id_sucursal = :id_sucursal
            ");

            $stmt->execute([
                'cantidad' => $cantidad,
                'id_producto' => $id_producto,
                'id_sucursal' => $id_sucursal
            ]);

            $this->db->commit();
            return $lotesAfectados;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error en reducirStock: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar productos para POS (búsqueda rápida)
     *
     * @param string $busqueda Término de búsqueda
     * @param int $id_sucursal ID de la sucursal
     * @param int $limite Límite de resultados
     * @return array Lista de productos
     */
    public function searchForPOS($busqueda, $id_sucursal, $limite = 20) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.id_producto,
                    p.nombre,
                    p.codigo_barras,
                    p.precio_venta,
                    p.precio_mayoreo,
                    p.requiere_lote,
                    COALESCE(inv.stock_actual, 0) as stock_disponible
                FROM {$this->table} p
                LEFT JOIN {$this->tableInventario} inv
                    ON p.id_producto = inv.id_producto
                    AND inv.id_sucursal = :id_sucursal
                WHERE p.activo = 1
                AND (
                    p.nombre LIKE :busqueda
                    OR p.codigo_barras LIKE :busqueda
                )
                ORDER BY p.nombre ASC
                LIMIT :limite
            ");

            $stmt->bindValue(':busqueda', '%' . $busqueda . '%', PDO::PARAM_STR);
            $stmt->bindValue(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en searchForPOS: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener productos con bajo stock
     *
     * @param int $id_sucursal ID de la sucursal
     * @return array Productos con bajo stock
     */
    public function getLowStock($id_sucursal) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.id_producto,
                    p.nombre,
                    inv.stock_actual,
                    inv.stock_minimo
                FROM {$this->table} p
                INNER JOIN {$this->tableInventario} inv
                    ON p.id_producto = inv.id_producto
                WHERE inv.id_sucursal = :id_sucursal
                AND inv.stock_actual <= inv.stock_minimo
                AND p.activo = 1
                ORDER BY inv.stock_actual ASC
            ");

            $stmt->execute(['id_sucursal' => $id_sucursal]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en getLowStock: " . $e->getMessage());
            return [];
        }
    }
}
