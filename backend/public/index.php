<?php
/**
 * Punto de entrada principal de la API
 */

// Configurar headers CORS
header('Access-Control-Allow-Origin: ' . CORS_ALLOWED_ORIGINS);
header('Access-Control-Allow-Methods: ' . CORS_ALLOWED_METHODS);
header('Access-Control-Allow-Headers: ' . CORS_ALLOWED_HEADERS);

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir configuración
require_once '../src/config/config.php';

// Crear instancia del router
$router = new Router();

// ============================================================================
// RUTAS DE AUTENTICACIÓN
// ============================================================================
$router->post('/api/v1/auth/login', function() {
    $controller = new AuthController();
    $controller->login();
});

$router->get('/api/v1/auth/me', function() {
    $controller = new AuthController();
    $controller->me();
});

$router->post('/api/v1/auth/logout', function() {
    $controller = new AuthController();
    $controller->logout();
});

$router->post('/api/v1/auth/change-password', function() {
    $controller = new AuthController();
    $controller->changePassword();
});

// ============================================================================
// RUTAS DE VENTAS
// ============================================================================
$router->post('/api/v1/ventas', function() {
    $controller = new VentasController();
    $controller->create();
});

$router->get('/api/v1/ventas', function() {
    $controller = new VentasController();
    $controller->list();
});

$router->get('/api/v1/ventas/{id}', function($id) {
    $controller = new VentasController();
    $controller->show($id);
});

// ============================================================================
// RUTAS DE USUARIOS (solo administrador)
// ============================================================================
$router->get('/api/v1/usuarios', function() {
    AuthMiddleware::requireAdmin();
    $controller = new UsuariosController();
    $controller->list();
});

$router->post('/api/v1/usuarios', function() {
    AuthMiddleware::requireAdmin();
    $controller = new UsuariosController();
    $controller->create();
});

$router->get('/api/v1/usuarios/{id}', function($id) {
    AuthMiddleware::requireAdmin();
    $controller = new UsuariosController();
    $controller->show($id);
});

$router->put('/api/v1/usuarios/{id}', function($id) {
    AuthMiddleware::requireAdmin();
    $controller = new UsuariosController();
    $controller->update($id);
});

$router->delete('/api/v1/usuarios/{id}', function($id) {
    AuthMiddleware::requireAdmin();
    $controller = new UsuariosController();
    $controller->delete($id);
});

// ============================================================================
// RUTAS DE SUCURSALES
// ============================================================================
$router->get('/api/v1/sucursales', function() {
    $controller = new SucursalesController();
    $controller->list();
});

$router->post('/api/v1/sucursales', function() {
    AuthMiddleware::requireAdmin();
    $controller = new SucursalesController();
    $controller->create();
});

$router->get('/api/v1/sucursales/{id}', function($id) {
    $controller = new SucursalesController();
    $controller->show($id);
});

$router->put('/api/v1/sucursales/{id}', function($id) {
    AuthMiddleware::requireAdmin();
    $controller = new SucursalesController();
    $controller->update($id);
});

// ============================================================================
// RUTAS DE PRODUCTOS
// ============================================================================
$router->get('/api/v1/productos', function() {
    $controller = new ProductosController();
    $controller->list();
});

$router->post('/api/v1/productos', function() {
    AuthMiddleware::requireAdmin();
    $controller = new ProductosController();
    $controller->create();
});

$router->get('/api/v1/productos/{id}', function($id) {
    $controller = new ProductosController();
    $controller->show($id);
});

$router->put('/api/v1/productos/{id}', function($id) {
    AuthMiddleware::requireAdmin();
    $controller = new ProductosController();
    $controller->update($id);
});

// ============================================================================
// RUTAS DE INVENTARIO
// ============================================================================
$router->get('/api/v1/inventario', function() {
    $controller = new InventarioController();
    $controller->list();
});

$router->get('/api/v1/inventario/sucursal/{id}', function($id) {
    $controller = new InventarioController();
    $controller->getBySucursal($id);
});

$router->post('/api/v1/inventario/entrada', function() {
    AuthMiddleware::requireAdmin();
    $controller = new InventarioController();
    $controller->entrada();
});

$router->post('/api/v1/inventario/traslado', function() {
    AuthMiddleware::requireAdmin();
    $controller = new InventarioController();
    $controller->traslado();
});

$router->post('/api/v1/inventario/ajuste', function() {
    AuthMiddleware::requireAdmin();
    $controller = new InventarioController();
    $controller->ajuste();
});

// ============================================================================
// RUTAS DE CLIENTES
// ============================================================================
$router->get('/api/v1/clientes', function() {
    $controller = new ClientesController();
    $controller->list();
});

$router->post('/api/v1/clientes', function() {
    $controller = new ClientesController();
    $controller->create();
});

$router->get('/api/v1/clientes/{id}', function($id) {
    $controller = new ClientesController();
    $controller->show($id);
});

$router->put('/api/v1/clientes/{id}', function($id) {
    $controller = new ClientesController();
    $controller->update($id);
});

// ============================================================================
// RUTAS DE CRÉDITOS
// ============================================================================
$router->get('/api/v1/creditos', function() {
    $controller = new CreditosController();
    $controller->list();
});

$router->get('/api/v1/creditos/{id}', function($id) {
    $controller = new CreditosController();
    $controller->show($id);
});

$router->post('/api/v1/creditos/{id}/pagar', function($id) {
    $controller = new CreditosController();
    $controller->registrarPago($id);
});

$router->get('/api/v1/creditos/cliente/{id}', function($id) {
    $controller = new CreditosController();
    $controller->getByCliente($id);
});

// ============================================================================
// RUTAS DE COTIZACIONES
// ============================================================================
$router->get('/api/v1/cotizaciones', function() {
    $controller = new CotizacionesController();
    $controller->list();
});

$router->post('/api/v1/cotizaciones', function() {
    $controller = new CotizacionesController();
    $controller->create();
});

$router->get('/api/v1/cotizaciones/{id}', function($id) {
    $controller = new CotizacionesController();
    $controller->show($id);
});

$router->put('/api/v1/cotizaciones/{id}', function($id) {
    $controller = new CotizacionesController();
    $controller->update($id);
});

$router->post('/api/v1/cotizaciones/{id}/convertir', function($id) {
    $controller = new CotizacionesController();
    $controller->convertirAVenta($id);
});

// ============================================================================
// RUTAS DE CIERRES DE CAJA
// ============================================================================
$router->get('/api/v1/cierres-caja', function() {
    $controller = new CierresCajaController();
    $controller->list();
});

$router->post('/api/v1/cierres-caja', function() {
    $controller = new CierresCajaController();
    $controller->crear();
});

$router->get('/api/v1/cierres-caja/{id}', function($id) {
    $controller = new CierresCajaController();
    $controller->show($id);
});

$router->put('/api/v1/cierres-caja/{id}/cerrar', function($id) {
    $controller = new CierresCajaController();
    $controller->cerrar($id);
});

// ============================================================================
// RUTAS DE REPORTES
// ============================================================================
$router->get('/api/v1/reportes/inventario', function() {
    $controller = new ReportesController();
    $controller->inventarioPorSucursal();
});

$router->get('/api/v1/reportes/productos-por-vencer', function() {
    $controller = new ReportesController();
    $controller->productosPorVencer();
});

$router->get('/api/v1/reportes/ventas', function() {
    $controller = new ReportesController();
    $controller->ventasPorSucursal();
});

$router->get('/api/v1/reportes/cuadre-caja', function() {
    $controller = new ReportesController();
    $controller->cuadreCaja();
});

$router->get('/api/v1/reportes/convenios-mensuales', function() {
    $controller = new ReportesController();
    $controller->conveniosMensuales();
});

$router->get('/api/v1/reportes/creditos-cliente', function() {
    $controller = new ReportesController();
    $controller->creditosPorCliente();
});

// ============================================================================
// RUTA NO ENCONTRADA
// ============================================================================
$router->notFound(function() {
    errorResponse('Endpoint no encontrado', 404);
});

// Ejecutar router
try {
    $router->run();
} catch (Exception $e) {
    errorResponse('Error interno del servidor: ' . $e->getMessage(), 500);
}
