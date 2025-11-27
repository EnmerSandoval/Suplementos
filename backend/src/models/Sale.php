<?php
/**
 * Modelo de Venta
 * Maneja ventas y sus detalles
 */

class Sale {
    private $db;
    private $table = 'ventas';
    private $tableDetalle = 'detalle_ventas';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear nueva venta con sus detalles
     *
     * @param array $data Datos de la venta
     * @return int|false ID de la venta creada o false
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // Crear venta
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (
                    id_sucursal,
                    id_usuario,
                    id_cliente,
                    tipo_venta,
                    subtotal,
                    descuento,
                    impuesto,
                    total,
                    metodo_pago,
                    estado,
                    fecha_venta
                ) VALUES (
                    :id_sucursal,
                    :id_usuario,
                    :id_cliente,
                    :tipo_venta,
                    :subtotal,
                    :descuento,
                    :impuesto,
                    :total,
                    :metodo_pago,
                    :estado,
                    NOW()
                )
            ");

            $stmt->execute([
                'id_sucursal' => $data['id_sucursal'],
                'id_usuario' => $data['id_usuario'],
                'id_cliente' => $data['id_cliente'] ?? null,
                'tipo_venta' => $data['tipo_venta'] ?? 'contado',
                'subtotal' => $data['subtotal'],
                'descuento' => $data['descuento'] ?? 0,
                'impuesto' => $data['impuesto'] ?? 0,
                'total' => $data['total'],
                'metodo_pago' => $data['metodo_pago'] ?? 'efectivo',
                'estado' => $data['estado'] ?? 'completada'
            ]);

            $id_venta = $this->db->lastInsertId();

            // Insertar detalles de venta
            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addDetalle($id_venta, $item);
                }
            }

            $this->db->commit();
            return $id_venta;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en Sale::create: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Agregar detalle a una venta
     *
     * @param int $id_venta ID de la venta
     * @param array $item Datos del item
     * @return bool
     */
    private function addDetalle($id_venta, $item) {
        try {
            // Insertar detalle de venta
            $stmt = $this->db->prepare("
                INSERT INTO {$this->tableDetalle} (
                    id_venta,
                    id_producto,
                    cantidad,
                    precio_unitario,
                    descuento,
                    subtotal
                ) VALUES (
                    :id_venta,
                    :id_producto,
                    :cantidad,
                    :precio_unitario,
                    :descuento,
                    :subtotal
                )
            ");

            $stmt->execute([
                'id_venta' => $id_venta,
                'id_producto' => $item['id_producto'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'descuento' => $item['descuento'] ?? 0,
                'subtotal' => $item['subtotal']
            ]);

            // Reducir stock del producto (usar método del modelo Product)
            require_once __DIR__ . '/Product.php';
            $productModel = new Product();

            $result = $productModel->reducirStock(
                $item['id_producto'],
                $item['id_sucursal'],
                $item['cantidad']
            );

            if (!$result) {
                throw new Exception("Stock insuficiente para el producto ID: {$item['id_producto']}");
            }

            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Obtener venta por ID
     *
     * @param int $id_venta ID de la venta
     * @return array|false Datos de la venta con detalles
     */
    public function findById($id_venta) {
        try {
            // Obtener venta
            $stmt = $this->db->prepare("
                SELECT
                    v.*,
                    u.nombre_completo as vendedor,
                    s.nombre as sucursal,
                    c.nombre as cliente,
                    c.telefono as cliente_telefono
                FROM {$this->table} v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                INNER JOIN sucursales s ON v.id_sucursal = s.id_sucursal
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                WHERE v.id_venta = :id_venta
            ");

            $stmt->execute(['id_venta' => $id_venta]);
            $venta = $stmt->fetch();

            if (!$venta) {
                return false;
            }

            // Obtener detalles
            $venta['items'] = $this->getDetalles($id_venta);

            return $venta;

        } catch (PDOException $e) {
            error_log("Error en findById: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener detalles de una venta
     *
     * @param int $id_venta ID de la venta
     * @return array Lista de items
     */
    public function getDetalles($id_venta) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    dv.*,
                    p.nombre as producto_nombre,
                    p.codigo_barras
                FROM {$this->tableDetalle} dv
                INNER JOIN productos p ON dv.id_producto = p.id_producto
                WHERE dv.id_venta = :id_venta
            ");

            $stmt->execute(['id_venta' => $id_venta]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en getDetalles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las ventas con filtros
     *
     * @param array $filters Filtros (id_sucursal, fecha_inicio, fecha_fin, estado)
     * @return array Lista de ventas
     */
    public function getAll($filters = []) {
        try {
            $where = [];
            $params = [];

            if (isset($filters['id_sucursal'])) {
                $where[] = "v.id_sucursal = :id_sucursal";
                $params['id_sucursal'] = $filters['id_sucursal'];
            }

            if (isset($filters['fecha_inicio'])) {
                $where[] = "DATE(v.fecha_venta) >= :fecha_inicio";
                $params['fecha_inicio'] = $filters['fecha_inicio'];
            }

            if (isset($filters['fecha_fin'])) {
                $where[] = "DATE(v.fecha_venta) <= :fecha_fin";
                $params['fecha_fin'] = $filters['fecha_fin'];
            }

            if (isset($filters['estado'])) {
                $where[] = "v.estado = :estado";
                $params['estado'] = $filters['estado'];
            }

            if (isset($filters['id_usuario'])) {
                $where[] = "v.id_usuario = :id_usuario";
                $params['id_usuario'] = $filters['id_usuario'];
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $this->db->prepare("
                SELECT
                    v.id_venta,
                    v.fecha_venta,
                    v.total,
                    v.estado,
                    v.metodo_pago,
                    v.tipo_venta,
                    u.nombre_completo as vendedor,
                    s.nombre as sucursal,
                    COALESCE(c.nombre, 'Público General') as cliente
                FROM {$this->table} v
                INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
                INNER JOIN sucursales s ON v.id_sucursal = s.id_sucursal
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                {$whereClause}
                ORDER BY v.fecha_venta DESC
            ");

            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener ventas del día
     *
     * @param int $id_sucursal ID de la sucursal
     * @return array Lista de ventas del día
     */
    public function getVentasDelDia($id_sucursal) {
        return $this->getAll([
            'id_sucursal' => $id_sucursal,
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => date('Y-m-d')
        ]);
    }

    /**
     * Cancelar venta
     *
     * @param int $id_venta ID de la venta
     * @param int $id_usuario ID del usuario que cancela
     * @param string $motivo Motivo de cancelación
     * @return bool
     */
    public function cancelar($id_venta, $id_usuario, $motivo = '') {
        try {
            $this->db->beginTransaction();

            // Obtener detalles de la venta para revertir stock
            $detalles = $this->getDetalles($id_venta);

            // Revertir stock de cada item
            require_once __DIR__ . '/Product.php';
            $productModel = new Product();

            $venta = $this->findById($id_venta);

            foreach ($detalles as $item) {
                // Crear un lote de "devolución" para revertir el stock
                $productModel->createLote([
                    'id_producto' => $item['id_producto'],
                    'id_sucursal' => $venta['id_sucursal'],
                    'numero_lote' => 'DEVOLUCION-' . $id_venta . '-' . time(),
                    'cantidad' => $item['cantidad'],
                    'precio_compra' => $item['precio_unitario'],
                    'fecha_vencimiento' => null
                ]);
            }

            // Actualizar estado de la venta
            $stmt = $this->db->prepare("
                UPDATE {$this->table}
                SET estado = 'cancelada'
                WHERE id_venta = :id_venta
            ");

            $stmt->execute(['id_venta' => $id_venta]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en cancelar: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener resumen de ventas
     *
     * @param array $filters Filtros
     * @return array Resumen estadístico
     */
    public function getResumen($filters = []) {
        try {
            $where = ["v.estado = 'completada'"];
            $params = [];

            if (isset($filters['id_sucursal'])) {
                $where[] = "v.id_sucursal = :id_sucursal";
                $params['id_sucursal'] = $filters['id_sucursal'];
            }

            if (isset($filters['fecha_inicio'])) {
                $where[] = "DATE(v.fecha_venta) >= :fecha_inicio";
                $params['fecha_inicio'] = $filters['fecha_inicio'];
            }

            if (isset($filters['fecha_fin'])) {
                $where[] = "DATE(v.fecha_venta) <= :fecha_fin";
                $params['fecha_fin'] = $filters['fecha_fin'];
            }

            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) as total_ventas,
                    SUM(v.total) as monto_total,
                    AVG(v.total) as ticket_promedio,
                    SUM(CASE WHEN v.metodo_pago = 'efectivo' THEN v.total ELSE 0 END) as total_efectivo,
                    SUM(CASE WHEN v.metodo_pago = 'tarjeta' THEN v.total ELSE 0 END) as total_tarjeta,
                    SUM(CASE WHEN v.metodo_pago = 'transferencia' THEN v.total ELSE 0 END) as total_transferencia
                FROM {$this->table} v
                WHERE {$whereClause}
            ");

            $stmt->execute($params);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Error en getResumen: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener productos más vendidos
     *
     * @param int $id_sucursal ID de la sucursal
     * @param int $limite Límite de resultados
     * @return array Lista de productos
     */
    public function getProductosMasVendidos($id_sucursal, $limite = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    p.id_producto,
                    p.nombre,
                    SUM(dv.cantidad) as total_vendido,
                    SUM(dv.subtotal) as total_ingresos
                FROM {$this->tableDetalle} dv
                INNER JOIN {$this->table} v ON dv.id_venta = v.id_venta
                INNER JOIN productos p ON dv.id_producto = p.id_producto
                WHERE v.id_sucursal = :id_sucursal
                AND v.estado = 'completada'
                AND DATE(v.fecha_venta) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY p.id_producto
                ORDER BY total_vendido DESC
                LIMIT :limite
            ");

            $stmt->bindValue(':id_sucursal', $id_sucursal, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Error en getProductosMasVendidos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si se puede realizar una venta
     * (valida stock disponible)
     *
     * @param array $items Items a vender
     * @param int $id_sucursal ID de la sucursal
     * @return array ['success' => bool, 'errors' => array]
     */
    public function validarStock($items, $id_sucursal) {
        try {
            require_once __DIR__ . '/Product.php';
            $productModel = new Product();

            $errors = [];

            foreach ($items as $item) {
                $stock = $productModel->getStock($item['id_producto'], $id_sucursal);

                if ($stock['stock_actual'] < $item['cantidad']) {
                    $producto = $productModel->findById($item['id_producto']);
                    $errors[] = "Stock insuficiente para {$producto['nombre']}. Disponible: {$stock['stock_actual']}, Solicitado: {$item['cantidad']}";
                }
            }

            return [
                'success' => empty($errors),
                'errors' => $errors
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'errors' => ['Error al validar stock: ' . $e->getMessage()]
            ];
        }
    }
}
