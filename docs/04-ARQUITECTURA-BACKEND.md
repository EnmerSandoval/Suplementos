# ARQUITECTURA DEL BACKEND - PHP PURO

## 1. VISIÓN GENERAL

El backend está desarrollado en **PHP puro** (sin frameworks) y expone una **API REST** que es consumida por el frontend React. La arquitectura sigue un patrón **MVC simplificado** adaptado para APIs.

### Características Principales
- PHP nativo sin dependencias de frameworks
- API RESTful con respuestas JSON
- Autenticación JWT sin librerías externas
- Router personalizado
- PDO para acceso a base de datos
- Middleware para autenticación y autorización

---

## 2. ESTRUCTURA DE CARPETAS

```
backend/
├── public/                    # Carpeta pública (document root)
│   ├── index.php              # Punto de entrada principal
│   └── .htaccess              # Reescritura de URLs
├── src/
│   ├── config/                # Configuraciones
│   │   ├── config.php         # Configuración general
│   │   └── database.php       # Conexión a BD (Singleton)
│   ├── controllers/           # Controladores (lógica de negocio)
│   │   ├── AuthController.php
│   │   ├── VentasController.php
│   │   ├── ProductosController.php
│   │   ├── InventarioController.php
│   │   ├── ClientesController.php
│   │   ├── CotizacionesController.php
│   │   ├── CreditosController.php
│   │   ├── SucursalesController.php
│   │   ├── UsuariosController.php
│   │   ├── CierresCajaController.php
│   │   └── ReportesController.php
│   ├── models/                # Modelos (opcional, para lógica de datos)
│   ├── middleware/            # Middlewares
│   │   └── AuthMiddleware.php # Autenticación y autorización
│   └── utils/                 # Utilidades
│       ├── Router.php         # Router personalizado
│       └── JWT.php            # Manejo de tokens JWT
└── database/
    └── schema.sql             # Script de base de datos
```

---

## 3. FLUJO DE UNA PETICIÓN

```
Cliente (React)
    │
    │ HTTP Request (JSON)
    ↓
Apache/Nginx
    │
    │ .htaccess (rewrite)
    ↓
public/index.php
    │
    │ Cargar configuración
    ↓
Router
    │
    │ Matchear ruta y método
    ↓
Middleware (si aplica)
    │
    │ Validar JWT
    │ Verificar permisos
    ↓
Controller
    │
    │ Validar datos
    │ Lógica de negocio
    │ Consultar BD (PDO)
    ↓
Respuesta JSON
    │
    ↓
Cliente (React)
```

---

## 4. COMPONENTES PRINCIPALES

### 4.1 Punto de Entrada: `public/index.php`

Este archivo es el **único punto de entrada** de todas las peticiones HTTP.

**Responsabilidades:**
1. Configurar headers CORS
2. Cargar configuración general
3. Instanciar el router
4. Definir todas las rutas
5. Ejecutar el router
6. Manejar excepciones globales

**Ejemplo:**
```php
<?php
// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Cargar configuración
require_once '../src/config/config.php';

// Crear router
$router = new Router();

// Definir rutas
$router->post('/api/v1/auth/login', function() {
    $controller = new AuthController();
    $controller->login();
});

// ... más rutas

// Ejecutar
$router->run();
```

---

### 4.2 Router Personalizado: `src/utils/Router.php`

Un router simple que maneja el enrutamiento de peticiones sin dependencias externas.

**Características:**
- Soporte para GET, POST, PUT, DELETE
- Parámetros dinámicos en URLs: `/usuarios/{id}`
- Conversión de rutas a expresiones regulares
- Callback 404 personalizable

**Métodos principales:**
- `get($path, $callback)` - Registrar ruta GET
- `post($path, $callback)` - Registrar ruta POST
- `put($path, $callback)` - Registrar ruta PUT
- `delete($path, $callback)` - Registrar ruta DELETE
- `run()` - Ejecutar el router
- `getRequestBody()` - Obtener body de la petición
- `getQueryParams()` - Obtener query params

**Ejemplo de uso:**
```php
$router->get('/api/v1/productos/{id}', function($id) {
    $controller = new ProductosController();
    $controller->show($id);
});
```

---

### 4.3 Conexión a Base de Datos: `src/config/database.php`

Implementa el **patrón Singleton** para la conexión a la base de datos usando PDO.

**Características:**
- Una única instancia de conexión durante toda la ejecución
- PDO con prepared statements para prevenir SQL injection
- Manejo de errores con excepciones
- Configuración centralizada

**Ejemplo:**
```php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = "mysql:host=localhost;dbname=gestion_inventario;charset=utf8mb4";
        $this->connection = new PDO($dsn, $username, $password, $options);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Uso:
$db = Database::getInstance()->getConnection();
```

---

### 4.4 JWT (JSON Web Tokens): `src/utils/JWT.php`

Implementación de JWT sin librerías externas para autenticación.

**Métodos:**
- `encode($payload)` - Generar token
- `decode($token)` - Decodificar y validar token
- `getTokenFromHeader()` - Extraer token del header Authorization
- `validateToken()` - Validar token y retornar payload

**Estructura del Token:**
```
Header.Payload.Signature
```

**Payload ejemplo:**
```json
{
  "id_usuario": 1,
  "usuario": "admin",
  "rol": "Administrador",
  "id_sucursal_principal": 1,
  "iat": 1705350000,
  "exp": 1705378800
}
```

**Configuración:**
- Secret Key: `JWT_SECRET_KEY` en config.php
- Algoritmo: HS256
- Expiración: 8 horas (configurable)

---

### 4.5 Middleware de Autenticación: `src/middleware/AuthMiddleware.php`

Middleware para proteger rutas y verificar permisos.

**Métodos:**
- `authenticate()` - Verificar que el usuario esté autenticado
- `requireRole($roles)` - Verificar roles específicos
- `requireAdmin()` - Requiere rol Administrador
- `requireVendedor()` - Requiere Vendedor o Administrador
- `checkSucursalAccess($idSucursal)` - Verificar acceso a sucursal

**Ejemplo de uso:**
```php
// En una ruta protegida
$router->post('/api/v1/productos', function() {
    // Solo administradores pueden crear productos
    AuthMiddleware::requireAdmin();

    $controller = new ProductosController();
    $controller->create();
});
```

---

### 4.6 Controladores

Los controladores contienen la **lógica de negocio** de la aplicación.

**Estructura típica de un controlador:**

```php
class ProductosController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Listar productos
    public function list() {
        AuthMiddleware::requireVendedor();

        $params = Router::getQueryParams();
        // ... lógica de consulta
        successResponse($productos);
    }

    // Crear producto
    public function create() {
        AuthMiddleware::requireAdmin();

        $data = Router::getRequestBody();
        // ... validación y creación
        successResponse($producto);
    }

    // Obtener por ID
    public function show($id) {
        // ... lógica
    }

    // Actualizar
    public function update($id) {
        // ... lógica
    }

    // Eliminar
    public function delete($id) {
        // ... lógica
    }
}
```

**Controladores principales:**

1. **AuthController** - Autenticación y sesión
   - `login()` - Iniciar sesión
   - `logout()` - Cerrar sesión
   - `me()` - Info del usuario actual
   - `changePassword()` - Cambiar contraseña

2. **VentasController** - Gestión de ventas
   - `create()` - Crear venta (transaccional)
   - `list()` - Listar con filtros
   - `show($id)` - Detalle de venta

3. **InventarioController** - Gestión de inventario
   - `list()` - Inventario consolidado
   - `entrada()` - Registrar entrada
   - `traslado()` - Traslado entre sucursales
   - `ajuste()` - Ajuste de inventario

4. **CreditosController** - Gestión de créditos
   - `list()` - Listar créditos
   - `registrarPago($id)` - Registrar pago/abono

5. **CotizacionesController** - Gestión de cotizaciones
   - `create()` - Crear cotización
   - `convertirAVenta($id)` - Convertir a venta

6. **ReportesController** - Generación de reportes
   - `inventarioPorSucursal()`
   - `productosPorVencer()`
   - `ventasPorSucursal()`
   - `creditosPorCliente()`

---

## 5. MANEJO DE RESPUESTAS

### Funciones Auxiliares

Definidas en `src/config/config.php`:

```php
// Respuesta JSON genérica
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Respuesta de éxito
function successResponse($data = [], $message = 'Operación exitosa') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ], 200);
}

// Respuesta de error
function errorResponse($message, $statusCode = 400, $errors = []) {
    jsonResponse([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ], $statusCode);
}
```

### Estructura de Respuestas

**Éxito:**
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { ... }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": []
}
```

---

## 6. SEGURIDAD

### 6.1 Autenticación

- **JWT**: Tokens firmados con HMAC-SHA256
- **Expiración**: Tokens expiran en 8 horas
- **Header**: `Authorization: Bearer {token}`
- **Validación**: En cada petición protegida

### 6.2 Autorización

- **Por Rol**: Administrador vs Vendedor
- **Por Sucursal**: Vendedores solo acceden a su sucursal
- **Middleware**: Verificación centralizada

### 6.3 Base de Datos

- **PDO**: Con prepared statements
- **Prevención SQL Injection**: Bind de parámetros
- **Transacciones**: Para operaciones críticas

Ejemplo:
```php
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
$stmt->execute(['id' => $id]);
```

### 6.4 Contraseñas

- **Hash**: bcrypt con `password_hash()`
- **Verificación**: `password_verify()`
- **Salt**: Automático en PHP

```php
// Crear hash
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verificar
if (password_verify($inputPassword, $storedHash)) {
    // Válida
}
```

### 6.5 CORS

Configurado en `public/index.php`:
```php
header('Access-Control-Allow-Origin: *'); // Cambiar en producción
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### 6.6 Headers de Seguridad

Configurados en `.htaccess`:
```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
```

---

## 7. TRANSACCIONES

Para operaciones críticas como ventas, se usan transacciones:

```php
try {
    $db->beginTransaction();

    // Insertar venta
    $stmt = $db->prepare("INSERT INTO ventas ...");
    $stmt->execute([...]);
    $idVenta = $db->lastInsertId();

    // Insertar detalles
    foreach ($productos as $producto) {
        $stmt = $db->prepare("INSERT INTO detalle_ventas ...");
        $stmt->execute([...]);

        // Actualizar inventario
        $stmt = $db->prepare("UPDATE inventario ...");
        $stmt->execute([...]);
    }

    $db->commit();
    successResponse($venta);

} catch (Exception $e) {
    $db->rollBack();
    errorResponse('Error: ' . $e->getMessage(), 500);
}
```

---

## 8. PAGINACIÓN

La paginación se implementa con LIMIT y OFFSET:

```php
$page = isset($params['page']) ? intval($params['page']) : 1;
$pageSize = isset($params['page_size']) ? intval($params['page_size']) : 20;
$offset = ($page - 1) * $pageSize;

$stmt = $db->prepare("
    SELECT * FROM productos
    WHERE estado = 1
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$productos = $stmt->fetchAll();

// Contar total
$stmt = $db->prepare("SELECT COUNT(*) as total FROM productos WHERE estado = 1");
$stmt->execute();
$total = $stmt->fetch()['total'];

successResponse([
    'productos' => $productos,
    'pagination' => [
        'page' => $page,
        'page_size' => $pageSize,
        'total' => intval($total),
        'total_pages' => ceil($total / $pageSize)
    ]
]);
```

---

## 9. VALIDACIONES

Las validaciones se hacen en los controladores:

```php
public function create() {
    $data = Router::getRequestBody();

    // Validar campos requeridos
    if (empty($data['nombre']) || empty($data['codigo_producto'])) {
        errorResponse('Nombre y código son requeridos', 400);
    }

    // Validar formato
    if (!is_numeric($data['precio_base']) || $data['precio_base'] < 0) {
        errorResponse('Precio debe ser un número positivo', 400);
    }

    // Validar duplicados
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM productos WHERE codigo_producto = :codigo");
    $stmt->execute(['codigo' => $data['codigo_producto']]);

    if ($stmt->fetch()['count'] > 0) {
        errorResponse('El código de producto ya existe', 400);
    }

    // Continuar con la creación...
}
```

---

## 10. CONFIGURACIÓN DE APACHE

### `.htaccess` en `public/`

```apache
# Habilitar reescritura
RewriteEngine On

# Redirigir todo a index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]

# Headers de seguridad
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "*"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Evitar listado de directorios
Options -Indexes

# Tipos MIME
AddType application/json .json
```

### VirtualHost recomendado

```apache
<VirtualHost *:80>
    ServerName api.tudominio.com
    DocumentRoot /var/www/html/api/public

    <Directory /var/www/html/api/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/api_error.log
    CustomLog ${APACHE_LOG_DIR}/api_access.log combined
</VirtualHost>
```

---

## 11. MEJORES PRÁCTICAS IMPLEMENTADAS

1. **Separación de Responsabilidades**
   - Controllers: Lógica de negocio
   - Middleware: Autenticación/Autorización
   - Utils: Funcionalidades reutilizables

2. **DRY (Don't Repeat Yourself)**
   - Funciones auxiliares para respuestas
   - Clase Database singleton
   - Middleware reutilizable

3. **Seguridad First**
   - Prepared statements siempre
   - Validación de entrada
   - Tokens con expiración
   - Autorización granular

4. **Código Limpio**
   - Nombres descriptivos
   - Comentarios donde es necesario
   - Estructura consistente

5. **Manejo de Errores**
   - Try-catch en operaciones críticas
   - Mensajes de error descriptivos
   - Rollback de transacciones

---

## 12. ESCALABILIDAD

El sistema está preparado para escalar:

### Horizontal (más servidores)
- API stateless con JWT
- Sin sesiones en servidor
- Base de datos centralizada

### Vertical (más recursos)
- Consultas optimizadas con índices
- Paginación en listados
- Lazy loading cuando es posible

### Mejoras Futuras Posibles
- Cache con Redis
- Queue para operaciones pesadas
- Microservicios para módulos específicos
- Logging centralizado

---

## 13. DEPLOYMENT EN PRODUCCIÓN

### Checklist de Producción

1. **Configuración**
   - [ ] Cambiar `JWT_SECRET_KEY`
   - [ ] Desactivar `display_errors`
   - [ ] Configurar `error_log`
   - [ ] Cambiar credenciales de BD
   - [ ] Configurar CORS específico (no *)
   - [ ] Habilitar HTTPS

2. **Base de Datos**
   - [ ] Cambiar contraseña del admin
   - [ ] Crear usuario de BD con permisos limitados
   - [ ] Habilitar logs de consultas lentas
   - [ ] Configurar backups automáticos

3. **Servidor**
   - [ ] Configurar SSL/TLS
   - [ ] Limitar tamaño de uploads
   - [ ] Configurar rate limiting
   - [ ] Habilitar compresión gzip

4. **Monitoreo**
   - [ ] Configurar logs de errores
   - [ ] Monitoreo de recursos
   - [ ] Alertas de caídas

---

Este backend en PHP puro proporciona una base sólida, segura y escalable para el sistema de gestión de inventario y ventas.
