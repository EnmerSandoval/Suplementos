<?php
/**
 * Controlador de ventas
 */

class VentasController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear una nueva venta
     * POST /api/v1/ventas
     */
    public function create() {
        $payload = AuthMiddleware::requireVendedor();
        $data = Router::getRequestBody();

        // Validar datos requeridos
        if (empty($data['id_sucursal']) || empty($data['productos']) || !is_array($data['productos'])) {
            errorResponse('Datos incompletos para crear la venta', 400);
        }

        try {
            $this->db->beginTransaction();

            // Generar número de venta correlativo
            $stmt = $this->db->prepare("
                SELECT COALESCE(MAX(CAST(SUBSTRING(numero_venta, 4) AS UNSIGNED)), 0) + 1 as siguiente
                FROM ventas
                WHERE numero_venta LIKE 'V-%'
            ");
            $stmt->execute();
            $siguiente = $stmt->fetch()['siguiente'];
            $numeroVenta = 'V-' . str_pad($siguiente, 8, '0', STR_PAD_LEFT);

            // Calcular subtotal y total
            $subtotal = 0;
            $descuento = isset($data['descuento']) ? floatval($data['descuento']) : 0;

            foreach ($data['productos'] as $producto) {
                $subtotal += floatval($producto['precio_unitario']) * floatval($producto['cantidad']);
            }

            $total = $subtotal - $descuento;

            // Validar cliente si es venta a crédito
            $idCliente = isset($data['id_cliente']) ? $data['id_cliente'] : null;
            $tipoVenta = isset($data['tipo_venta']) ? $data['tipo_venta'] : 'CONTADO';

            if ($tipoVenta === 'CREDITO' && !$idCliente) {
                errorResponse('Se requiere un cliente para ventas a crédito', 400);
            }

            // Validar límite de crédito del cliente
            if ($tipoVenta === 'CREDITO') {
                $stmt = $this->db->prepare("
                    SELECT limite_credito, saldo_actual
                    FROM clientes
                    WHERE id_cliente = :id_cliente
                ");
                $stmt->execute(['id_cliente' => $idCliente]);
                $cliente = $stmt->fetch();

                if ($cliente['saldo_actual'] + $total > $cliente['limite_credito']) {
                    errorResponse('El cliente ha excedido su límite de crédito', 400);
                }
            }

            // Insertar venta
            $stmt = $this->db->prepare("
                INSERT INTO ventas (
                    numero_venta, id_sucursal, id_vendedor, id_cliente,
                    tipo_venta, subtotal, descuento, total, metodo_pago, observaciones
                ) VALUES (
                    :numero_venta, :id_sucursal, :id_vendedor, :id_cliente,
                    :tipo_venta, :subtotal, :descuento, :total, :metodo_pago, :observaciones
                )
            ");

            $stmt->execute([
                'numero_venta' => $numeroVenta,
                'id_sucursal' => $data['id_sucursal'],
                'id_vendedor' => $payload['id_usuario'],
                'id_cliente' => $idCliente,
                'tipo_venta' => $tipoVenta,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'metodo_pago' => isset($data['metodo_pago']) ? $data['metodo_pago'] : 'efectivo',
                'observaciones' => isset($data['observaciones']) ? $data['observaciones'] : null
            ]);

            $idVenta = $this->db->lastInsertId();

            // Insertar detalle de ventas y actualizar inventario
            foreach ($data['productos'] as $producto) {
                $idProducto = $producto['id_producto'];
                $idLote = isset($producto['id_lote']) ? $producto['id_lote'] : null;
                $cantidad = floatval($producto['cantidad']);
                $precioUnitario = floatval($producto['precio_unitario']);
                $descuentoLinea = isset($producto['descuento']) ? floatval($producto['descuento']) : 0;
                $subtotalLinea = $precioUnitario * $cantidad;
                $totalLinea = $subtotalLinea - $descuentoLinea;

                // Verificar disponibilidad en inventario
                $stmt = $this->db->prepare("
                    SELECT cantidad_disponible
                    FROM inventario
                    WHERE id_sucursal = :id_sucursal
                    AND id_producto = :id_producto
                    AND (id_lote = :id_lote OR (id_lote IS NULL AND :id_lote IS NULL))
                ");
                $stmt->execute([
                    'id_sucursal' => $data['id_sucursal'],
                    'id_producto' => $idProducto,
                    'id_lote' => $idLote
                ]);
                $inventario = $stmt->fetch();

                if (!$inventario || $inventario['cantidad_disponible'] < $cantidad) {
                    $this->db->rollBack();
                    errorResponse('Stock insuficiente para el producto ID: ' . $idProducto, 400);
                }

                // Insertar detalle de venta
                $stmt = $this->db->prepare("
                    INSERT INTO detalle_ventas (
                        id_venta, id_producto, id_lote, cantidad,
                        precio_unitario, subtotal, descuento, total
                    ) VALUES (
                        :id_venta, :id_producto, :id_lote, :cantidad,
                        :precio_unitario, :subtotal, :descuento, :total
                    )
                ");
                $stmt->execute([
                    'id_venta' => $idVenta,
                    'id_producto' => $idProducto,
                    'id_lote' => $idLote,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotalLinea,
                    'descuento' => $descuentoLinea,
                    'total' => $totalLinea
                ]);

                // Actualizar inventario (restar cantidad)
                $stmt = $this->db->prepare("
                    UPDATE inventario
                    SET cantidad_disponible = cantidad_disponible - :cantidad
                    WHERE id_sucursal = :id_sucursal
                    AND id_producto = :id_producto
                    AND (id_lote = :id_lote OR (id_lote IS NULL AND :id_lote IS NULL))
                ");
                $stmt->execute([
                    'cantidad' => $cantidad,
                    'id_sucursal' => $data['id_sucursal'],
                    'id_producto' => $idProducto,
                    'id_lote' => $idLote
                ]);

                // Registrar movimiento de stock (SALIDA)
                $stmt = $this->db->prepare("
                    INSERT INTO movimientos_stock (
                        id_sucursal_origen, id_producto, id_lote,
                        tipo_movimiento, cantidad, motivo,
                        referencia_venta, id_usuario
                    ) VALUES (
                        :id_sucursal, :id_producto, :id_lote,
                        'SALIDA', :cantidad, 'Venta',
                        :referencia_venta, :id_usuario
                    )
                ");
                $stmt->execute([
                    'id_sucursal' => $data['id_sucursal'],
                    'id_producto' => $idProducto,
                    'id_lote' => $idLote,
                    'cantidad' => $cantidad,
                    'referencia_venta' => $idVenta,
                    'id_usuario' => $payload['id_usuario']
                ]);
            }

            // Si es venta a crédito, crear registro en créditos
            if ($tipoVenta === 'CREDITO') {
                $fechaLimitePago = isset($data['fecha_limite_pago'])
                    ? $data['fecha_limite_pago']
                    : date('Y-m-d', strtotime('+30 days'));

                $stmt = $this->db->prepare("
                    INSERT INTO creditos (
                        id_venta, id_cliente, monto_total,
                        saldo_pendiente, fecha_limite_pago
                    ) VALUES (
                        :id_venta, :id_cliente, :monto_total,
                        :saldo_pendiente, :fecha_limite_pago
                    )
                ");
                $stmt->execute([
                    'id_venta' => $idVenta,
                    'id_cliente' => $idCliente,
                    'monto_total' => $total,
                    'saldo_pendiente' => $total,
                    'fecha_limite_pago' => $fechaLimitePago
                ]);

                // Actualizar saldo del cliente
                $stmt = $this->db->prepare("
                    UPDATE clientes
                    SET saldo_actual = saldo_actual + :monto
                    WHERE id_cliente = :id_cliente
                ");
                $stmt->execute([
                    'monto' => $total,
                    'id_cliente' => $idCliente
                ]);
            }

            $this->db->commit();

            // Obtener venta completa para respuesta
            $venta = $this->getVentaById($idVenta);

            successResponse($venta, 'Venta creada exitosamente');

        } catch (Exception $e) {
            $this->db->rollBack();
            errorResponse('Error al crear venta: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Listar ventas con filtros
     * GET /api/v1/ventas
     */
    public function list() {
        $payload = AuthMiddleware::requireVendedor();
        $params = Router::getQueryParams();

        try {
            $where = ['1=1'];
            $bindings = [];

            // Filtros
            if (isset($params['id_sucursal'])) {
                // Los vendedores solo pueden ver su sucursal
                if ($payload['rol'] !== 'Administrador') {
                    if ($params['id_sucursal'] != $payload['id_sucursal_principal']) {
                        errorResponse('No tiene acceso a esta sucursal', 403);
                    }
                }
                $where[] = 'v.id_sucursal = :id_sucursal';
                $bindings['id_sucursal'] = $params['id_sucursal'];
            } else {
                // Si no especifica sucursal y es vendedor, mostrar solo su sucursal
                if ($payload['rol'] !== 'Administrador') {
                    $where[] = 'v.id_sucursal = :id_sucursal';
                    $bindings['id_sucursal'] = $payload['id_sucursal_principal'];
                }
            }

            if (isset($params['fecha_inicio'])) {
                $where[] = 'DATE(v.fecha_venta) >= :fecha_inicio';
                $bindings['fecha_inicio'] = $params['fecha_inicio'];
            }

            if (isset($params['fecha_fin'])) {
                $where[] = 'DATE(v.fecha_venta) <= :fecha_fin';
                $bindings['fecha_fin'] = $params['fecha_fin'];
            }

            if (isset($params['tipo_venta'])) {
                $where[] = 'v.tipo_venta = :tipo_venta';
                $bindings['tipo_venta'] = $params['tipo_venta'];
            }

            if (isset($params['estado'])) {
                $where[] = 'v.estado = :estado';
                $bindings['estado'] = $params['estado'];
            }

            // Paginación
            $page = isset($params['page']) ? intval($params['page']) : 1;
            $pageSize = isset($params['page_size']) ? intval($params['page_size']) : DEFAULT_PAGE_SIZE;
            $offset = ($page - 1) * $pageSize;

            // Consulta principal
            $whereClause = implode(' AND ', $where);

            $stmt = $this->db->prepare("
                SELECT
                    v.id_venta,
                    v.numero_venta,
                    v.fecha_venta,
                    s.nombre as sucursal,
                    u.nombre_completo as vendedor,
                    c.nombre_completo as cliente,
                    v.tipo_venta,
                    v.total,
                    v.metodo_pago,
                    v.estado
                FROM ventas v
                INNER JOIN sucursales s ON v.id_sucursal = s.id_sucursal
                INNER JOIN usuarios u ON v.id_vendedor = u.id_usuario
                LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
                WHERE {$whereClause}
                ORDER BY v.fecha_venta DESC
                LIMIT :limit OFFSET :offset
            ");

            foreach ($bindings as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $ventas = $stmt->fetchAll();

            // Contar total de registros
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total
                FROM ventas v
                WHERE {$whereClause}
            ");

            foreach ($bindings as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();
            $total = $stmt->fetch()['total'];

            successResponse([
                'ventas' => $ventas,
                'pagination' => [
                    'page' => $page,
                    'page_size' => $pageSize,
                    'total' => intval($total),
                    'total_pages' => ceil($total / $pageSize)
                ]
            ]);

        } catch (Exception $e) {
            errorResponse('Error al listar ventas: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener detalle de una venta
     * GET /api/v1/ventas/{id}
     */
    public function show($id) {
        AuthMiddleware::requireVendedor();

        try {
            $venta = $this->getVentaById($id);

            if (!$venta) {
                errorResponse('Venta no encontrada', 404);
            }

            successResponse($venta);

        } catch (Exception $e) {
            errorResponse('Error al obtener venta: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Método auxiliar para obtener venta completa por ID
     */
    private function getVentaById($id) {
        // Obtener venta
        $stmt = $this->db->prepare("
            SELECT
                v.*,
                s.nombre as sucursal,
                u.nombre_completo as vendedor,
                c.nombre_completo as cliente,
                c.identificacion as cliente_identificacion
            FROM ventas v
            INNER JOIN sucursales s ON v.id_sucursal = s.id_sucursal
            INNER JOIN usuarios u ON v.id_vendedor = u.id_usuario
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            WHERE v.id_venta = :id
        ");
        $stmt->execute(['id' => $id]);
        $venta = $stmt->fetch();

        if (!$venta) {
            return null;
        }

        // Obtener detalle de productos
        $stmt = $this->db->prepare("
            SELECT
                dv.*,
                p.nombre as producto,
                p.codigo_producto,
                l.numero_lote,
                l.fecha_vencimiento
            FROM detalle_ventas dv
            INNER JOIN productos p ON dv.id_producto = p.id_producto
            LEFT JOIN lotes l ON dv.id_lote = l.id_lote
            WHERE dv.id_venta = :id
        ");
        $stmt->execute(['id' => $id]);
        $venta['productos'] = $stmt->fetchAll();

        return $venta;
    }
}
