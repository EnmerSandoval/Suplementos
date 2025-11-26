# Sistema de Gestión de Inventario y Ventas Multisucursal

Sistema completo de gestión de inventario y ventas diseñado para negocios de suplementos para gimnasio y farmacias, con soporte para múltiples sucursales y convenios con gimnasios.

## Características Principales

### ✅ Gestión Multisucursal
- Manejo de sucursales normales (tiendas propias)
- Soporte para convenios con gimnasios
- Traslados de productos entre sucursales
- Control de inventario independiente por sucursal

### ✅ Control de Inventario
- Gestión de productos con categorías
- Control de lotes con fechas de vencimiento
- Alertas de productos próximos a vencer
- Movimientos de inventario (entradas, salidas, traslados, ajustes)
- Stock mínimo por producto

### ✅ Ventas y Cotizaciones
- Ventas de contado y a crédito
- Generación de cotizaciones
- Conversión de cotizaciones a ventas
- Métodos de pago flexibles
- Descuentos por venta y por línea

### ✅ Gestión de Clientes
- Registro de clientes con límite de crédito
- Control de ventas a crédito
- Registro de pagos parciales
- Historial de créditos por cliente

### ✅ Caja y Cierres
- Cuadre de caja diario para sucursales normales
- Resumen mensual para convenios con gimnasios
- Control de diferencias en caja
- Reportes por método de pago

### ✅ Reportes
- Inventario por sucursal
- Productos próximos a vencer
- Ventas por período y sucursal
- Cuadres de caja
- Convenios mensuales
- Estado de créditos

### ✅ Seguridad y Usuarios
- Autenticación con JWT
- Dos roles: Administrador y Vendedor
- Permisos granulares por rol
- Acceso restringido por sucursal para vendedores

---

## Tecnologías Utilizadas

### Backend
- **PHP 8.0+** (PHP puro, sin frameworks)
- **MySQL/MariaDB** (Base de datos relacional)
- **PDO** (Conexión a base de datos)
- **JWT** (Autenticación)
- **Apache/Nginx** (Servidor web)

### Frontend
- **React 18** (SPA)
- **React Router DOM** (Navegación)
- **Axios** (Cliente HTTP)
- **React Query** (Manejo de estado del servidor)
- **Vite** (Herramienta de build)

---

## Estructura del Proyecto

```
Suplementos/
├── backend/                    # Backend en PHP puro
│   ├── public/                 # Punto de entrada público
│   │   ├── index.php           # Router principal
│   │   └── .htaccess           # Configuración Apache
│   ├── src/
│   │   ├── config/             # Configuración
│   │   │   ├── config.php      # Config general
│   │   │   └── database.php    # Conexión DB
│   │   ├── controllers/        # Controladores
│   │   ├── models/             # Modelos (opcional)
│   │   ├── middleware/         # Middlewares
│   │   │   └── AuthMiddleware.php
│   │   └── utils/              # Utilidades
│   │       ├── JWT.php         # Manejo de tokens
│   │       └── Router.php      # Router custom
│   └── database/
│       └── schema.sql          # Script de base de datos
├── frontend/                   # Frontend en React
│   ├── public/
│   ├── src/
│   │   ├── components/         # Componentes reutilizables
│   │   ├── pages/              # Páginas/Vistas
│   │   ├── services/           # Servicios de API
│   │   ├── contexts/           # Contextos de React
│   │   ├── hooks/              # Custom hooks
│   │   ├── utils/              # Utilidades
│   │   ├── App.jsx
│   │   └── index.js
│   └── package.json
└── docs/                       # Documentación
    ├── 01-MODELO-DE-DATOS.md
    ├── 02-API-ENDPOINTS.md
    └── 03-FRONTEND-REACT.md
```

---

## Instalación

### Requisitos Previos
- PHP 8.0 o superior
- MySQL 5.7+ o MariaDB 10.3+
- Apache o Nginx con mod_rewrite habilitado
- Node.js 18+ y npm (para frontend)
- Composer (opcional, no se usa en esta implementación)

### 1. Configurar Base de Datos

```bash
# Importar el script SQL
mysql -u root -p < database/schema.sql
```

Esto creará la base de datos `gestion_inventario` con todas las tablas necesarias.

**Usuario por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

⚠️ **IMPORTANTE**: Cambiar la contraseña del administrador en producción.

### 2. Configurar Backend

1. Copiar el contenido de `backend/` a la carpeta de tu servidor web:
   ```bash
   # Ejemplo con Apache
   cp -r backend/* /var/www/html/api/
   ```

2. Configurar conexión a base de datos en `backend/src/config/database.php`:
   ```php
   private $host = 'localhost';
   private $database = 'gestion_inventario';
   private $username = 'root';
   private $password = 'tu_password';
   ```

3. Configurar JWT secret en `backend/src/config/config.php`:
   ```php
   define('JWT_SECRET_KEY', 'tu_clave_secreta_muy_segura');
   ```

4. Asegurar que el archivo `.htaccess` esté presente en `backend/public/`

5. Verificar permisos:
   ```bash
   chmod -R 755 /var/www/html/api/
   ```

### 3. Configurar Frontend

1. Navegar a la carpeta frontend:
   ```bash
   cd frontend
   ```

2. Instalar dependencias:
   ```bash
   npm install
   ```

3. Configurar la URL de la API en `frontend/src/services/api.js`:
   ```javascript
   const API_BASE_URL = 'http://localhost/api/v1';
   ```

4. Iniciar servidor de desarrollo:
   ```bash
   npm run dev
   ```

5. Para producción, construir el proyecto:
   ```bash
   npm run build
   ```

---

## Uso del Sistema

### Acceso Inicial

1. Acceder al frontend: `http://localhost:5173` (desarrollo) o tu dominio
2. Iniciar sesión con:
   - Usuario: `admin`
   - Contraseña: `admin123`

### Flujo Básico

1. **Configuración Inicial** (Administrador)
   - Crear sucursales
   - Crear usuarios (vendedores)
   - Registrar productos
   - Configurar lotes con fechas de vencimiento

2. **Carga de Inventario** (Administrador)
   - Registrar entradas de productos
   - Asignar productos a sucursales
   - Realizar traslados si es necesario

3. **Gestión de Ventas** (Vendedor/Administrador)
   - Registrar clientes
   - Crear cotizaciones
   - Convertir cotizaciones a ventas
   - Registrar ventas directas
   - Registrar pagos de créditos

4. **Cierre de Caja** (Vendedor/Administrador)
   - Para sucursales normales: cuadre diario
   - Para convenios: reporte mensual

5. **Reportes** (Administrador)
   - Consultar inventarios
   - Ver productos por vencer
   - Analizar ventas
   - Revisar estado de créditos

---

## API REST

El backend expone una API REST completa. Ver documentación detallada en: `docs/02-API-ENDPOINTS.md`

### Base URL
```
http://localhost/api/v1
```

### Autenticación
Todas las rutas (excepto login) requieren token JWT en el header:
```
Authorization: Bearer {token}
```

### Ejemplo de uso con cURL

```bash
# Login
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"admin","contrasena":"admin123"}'

# Crear producto (requiere token)
curl -X POST http://localhost/api/v1/productos \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {tu_token}" \
  -d '{
    "codigo_producto": "PROT001",
    "nombre": "Proteína Whey 2lb",
    "categoria": "suplemento",
    "precio_base": 250.00
  }'
```

---

## Modelo de Datos

El sistema cuenta con 16 tablas principales:

1. **roles** - Tipos de usuarios
2. **usuarios** - Usuarios del sistema
3. **sucursales** - Sucursales/puntos de venta
4. **productos** - Catálogo de productos
5. **lotes** - Lotes con fechas de vencimiento
6. **inventario** - Stock por sucursal y lote
7. **precios_sucursal** - Precios específicos por sucursal
8. **clientes** - Información de clientes
9. **ventas** - Registro de ventas
10. **detalle_ventas** - Líneas de ventas
11. **movimientos_stock** - Historial de movimientos
12. **creditos** - Ventas a crédito
13. **pagos_credito** - Pagos de créditos
14. **cotizaciones** - Cotizaciones generadas
15. **detalle_cotizaciones** - Líneas de cotizaciones
16. **cierres_caja** - Cierres de caja diarios

Ver diagrama completo en: `docs/01-MODELO-DE-DATOS.md`

---

## Roles y Permisos

### Administrador
- Acceso completo al sistema
- Gestión de usuarios y sucursales
- Gestión de productos e inventario
- Visualización de todas las sucursales
- Generación de todos los reportes
- Traslados entre sucursales

### Vendedor
- Acceso a su sucursal asignada únicamente
- Registro de ventas y cotizaciones
- Registro de clientes
- Consulta de inventario de su sucursal
- Cierre de caja de su sucursal
- Registro de pagos de créditos

---

## Características Técnicas

### Backend

**Arquitectura:**
- Patrón MVC simplificado
- Router custom sin dependencias
- Autenticación JWT sin librerías externas
- PDO con prepared statements
- Respuestas JSON estandarizadas

**Seguridad:**
- Contraseñas hasheadas con bcrypt
- Tokens JWT con expiración
- Validación de entrada en todos los endpoints
- Autorización por roles
- Protección contra SQL injection

### Frontend

**Arquitectura:**
- Single Page Application (SPA)
- Componentes funcionales con Hooks
- Context API para estado global
- React Router para navegación
- Axios con interceptores

**Características:**
- Autenticación persistente
- Manejo de errores centralizado
- Validación de formularios
- Paginación de datos
- Búsqueda en tiempo real

---

## Adaptabilidad del Sistema

Este sistema está diseñado para funcionar en:

### 1. Negocios de Suplementos para Gimnasio
- Control de suplementos deportivos
- Convenios con gimnasios
- Gestión de proteínas, vitaminas, etc.
- Unidades de medida: frascos, sobres, cajas

### 2. Farmacias
- Control de medicamentos
- Productos médicos
- Categorías: medicamento, vitamina, suplemento
- Control estricto de lotes y vencimientos
- Unidades de medida: tabletas, cajas, frascos

### Parametrización:
- Categorías de productos personalizables
- Unidades de medida configurables
- Tipos de sucursal flexibles
- Métodos de pago adaptables

---

## Mantenimiento

### Backup de Base de Datos

```bash
# Backup completo
mysqldump -u root -p gestion_inventario > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u root -p gestion_inventario < backup_20250115.sql
```

### Logs

Los errores de PHP se registran en:
- Apache: `/var/log/apache2/error.log`
- Nginx: `/var/log/nginx/error.log`

Para debug, habilitar `display_errors` en `backend/src/config/config.php`:
```php
ini_set('display_errors', 1);
```

---

## Contribuciones

Este es un proyecto de código completo y documentado. Para personalizaciones:

1. Backend: Agregar nuevos controladores en `backend/src/controllers/`
2. Frontend: Agregar nuevas páginas en `frontend/src/pages/`
3. Base de datos: Agregar migraciones en `database/`

---

## Licencia

Este proyecto es de código abierto para uso educativo y comercial.

---

## Soporte

Para consultas sobre implementación o personalización:
- Revisar documentación en carpeta `docs/`
- Consultar ejemplos de código en controllers y services
- Verificar logs del servidor

---

## Changelog

### Versión 1.0 (Enero 2025)
- Implementación completa del sistema
- Backend PHP puro con API REST
- Frontend React SPA
- 16 tablas en base de datos
- Autenticación JWT
- Gestión multisucursal
- Control de inventario con lotes
- Ventas y cotizaciones
- Créditos y pagos
- Cierres de caja
- Sistema de reportes

---

**Desarrollado con PHP Puro y React - Sistema Multisucursal Completo**
