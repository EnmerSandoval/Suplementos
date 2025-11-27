<?php
/**
 * Controlador de Productos
 */

require_once __DIR__ . '/../models/Product.php';

class ProductController {

    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    /**
     * Obtener todos los productos
     * GET /api/v1/productos
     */
    public function index() {
        try {
            $payload = AuthMiddleware::authenticate();

            // Obtener filtros desde query params
            $filters = [];

            if (isset($_GET['sucursal'])) {
                $filters['id_sucursal'] = $_GET['sucursal'];
            } elseif (isset($payload['id_sucursal_principal'])) {
                $filters['id_sucursal'] = $payload['id_sucursal_principal'];
            }

            if (isset($_GET['categoria'])) {
                $filters['categoria'] = $_GET['categoria'];
            }

            if (isset($_GET['buscar'])) {
                $filters['buscar'] = $_GET['buscar'];
            }

            $productos = $this->productModel->getAll($filters);

            successResponse($productos);

        } catch (Exception $e) {
            errorResponse('Error al obtener productos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Buscar productos para POS
     * GET /api/v1/productos/search
     */
    public function search() {
        try {
            $payload = AuthMiddleware::authenticate();

            $busqueda = $_GET['q'] ?? '';
            $id_sucursal = $_GET['sucursal'] ?? $payload['id_sucursal_principal'];

            if (empty($busqueda)) {
                successResponse([]);
                return;
            }

            $productos = $this->productModel->searchForPOS($busqueda, $id_sucursal);

            successResponse($productos);

        } catch (Exception $e) {
            errorResponse('Error al buscar productos: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener producto por ID
     * GET /api/v1/productos/:id
     */
    public function show($id) {
        try {
            AuthMiddleware::authenticate();

            $producto = $this->productModel->findById($id);

            if (!$producto) {
                errorResponse('Producto no encontrado', 404);
            }

            successResponse($producto);

        } catch (Exception $e) {
            errorResponse('Error al obtener producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear nuevo producto
     * POST /api/v1/productos
     */
    public function store() {
        try {
            AuthMiddleware::authenticate();

            $data = Router::getRequestBody();

            // Validaciones
            if (empty($data['nombre'])) {
                errorResponse('El nombre del producto es requerido', 400);
            }

            if (empty($data['precio_venta']) || $data['precio_venta'] <= 0) {
                errorResponse('El precio de venta debe ser mayor a 0', 400);
            }

            $id_producto = $this->productModel->create($data);

            if (!$id_producto) {
                errorResponse('Error al crear producto', 500);
            }

            $producto = $this->productModel->findById($id_producto);

            successResponse($producto, 'Producto creado exitosamente', 201);

        } catch (Exception $e) {
            errorResponse('Error al crear producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Actualizar producto
     * PUT /api/v1/productos/:id
     */
    public function update($id) {
        try {
            AuthMiddleware::authenticate();

            $data = Router::getRequestBody();

            $result = $this->productModel->update($id, $data);

            if (!$result) {
                errorResponse('Error al actualizar producto', 500);
            }

            $producto = $this->productModel->findById($id);

            successResponse($producto, 'Producto actualizado exitosamente');

        } catch (Exception $e) {
            errorResponse('Error al actualizar producto: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener stock de un producto
     * GET /api/v1/productos/:id/stock
     */
    public function getStock($id) {
        try {
            $payload = AuthMiddleware::authenticate();

            $id_sucursal = $_GET['sucursal'] ?? $payload['id_sucursal_principal'];

            $stock = $this->productModel->getStock($id, $id_sucursal);

            successResponse($stock);

        } catch (Exception $e) {
            errorResponse('Error al obtener stock: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener lotes de un producto
     * GET /api/v1/productos/:id/lotes
     */
    public function getLotes($id) {
        try {
            $payload = AuthMiddleware::authenticate();

            $id_sucursal = $_GET['sucursal'] ?? $payload['id_sucursal_principal'];
            $soloActivos = isset($_GET['activos']) ? (bool)$_GET['activos'] : true;

            $lotes = $this->productModel->getLotes($id, $id_sucursal, $soloActivos);

            successResponse($lotes);

        } catch (Exception $e) {
            errorResponse('Error al obtener lotes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Crear nuevo lote
     * POST /api/v1/productos/:id/lotes
     */
    public function createLote($id) {
        try {
            $payload = AuthMiddleware::authenticate();

            $data = Router::getRequestBody();

            // Validaciones
            if (empty($data['numero_lote'])) {
                errorResponse('El número de lote es requerido', 400);
            }

            if (empty($data['cantidad']) || $data['cantidad'] <= 0) {
                errorResponse('La cantidad debe ser mayor a 0', 400);
            }

            if (empty($data['precio_compra']) || $data['precio_compra'] <= 0) {
                errorResponse('El precio de compra debe ser mayor a 0', 400);
            }

            $data['id_producto'] = $id;
            $data['id_sucursal'] = $data['id_sucursal'] ?? $payload['id_sucursal_principal'];

            $id_lote = $this->productModel->createLote($data);

            if (!$id_lote) {
                errorResponse('Error al crear lote', 500);
            }

            successResponse(['id_lote' => $id_lote], 'Lote creado exitosamente', 201);

        } catch (Exception $e) {
            errorResponse('Error al crear lote: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener productos con bajo stock
     * GET /api/v1/productos/low-stock
     */
    public function lowStock() {
        try {
            $payload = AuthMiddleware::authenticate();

            $id_sucursal = $_GET['sucursal'] ?? $payload['id_sucursal_principal'];

            $productos = $this->productModel->getLowStock($id_sucursal);

            successResponse($productos);

        } catch (Exception $e) {
            errorResponse('Error al obtener productos con bajo stock: ' . $e->getMessage(), 500);
        }
    }
}
