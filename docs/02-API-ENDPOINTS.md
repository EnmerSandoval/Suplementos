# DOCUMENTACIÓN DE ENDPOINTS - API REST

## Información General

- **Base URL**: `http://localhost/api/v1`
- **Formato de respuestas**: JSON
- **Autenticación**: JWT (JSON Web Token) vía header `Authorization: Bearer {token}`
- **Codificación**: UTF-8

## Estructura de Respuestas

### Respuesta Exitosa
```json
{
  "success": true,
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

### Respuesta de Error
```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": []
}
```

---

## 1. AUTENTICACIÓN

### 1.1 Login
Autenticar usuario y obtener token JWT.

- **URL**: `/auth/login`
- **Método**: `POST`
- **Autenticación**: No requerida
- **Body**:
```json
{
  "usuario": "admin",
  "contrasena": "admin123"
}
```
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Login exitoso",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "usuario": {
      "id_usuario": 1,
      "nombre_completo": "Administrador del Sistema",
      "usuario": "admin",
      "email": "admin@sistema.com",
      "rol": "Administrador",
      "sucursal": "Sucursal Principal"
    }
  }
}
```
- **Errores**:
  - 400: Datos incompletos
  - 401: Credenciales inválidas o usuario inactivo

---

### 1.2 Información del Usuario Autenticado
Obtener información del usuario actual.

- **URL**: `/auth/me`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Headers**: `Authorization: Bearer {token}`
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "id_usuario": 1,
    "nombre_completo": "Administrador del Sistema",
    "usuario": "admin",
    "email": "admin@sistema.com",
    "telefono": "1234-5678",
    "rol": "Administrador",
    "sucursal": "Sucursal Principal",
    "id_sucursal": 1
  }
}
```

---

### 1.3 Logout
Cerrar sesión (invalida token del lado del cliente).

- **URL**: `/auth/logout`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Logout exitoso",
  "data": {}
}
```

---

### 1.4 Cambiar Contraseña
Cambiar la contraseña del usuario autenticado.

- **URL**: `/auth/change-password`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "contrasena_actual": "admin123",
  "contrasena_nueva": "nuevacontrasena123"
}
```
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Contraseña actualizada exitosamente",
  "data": {}
}
```

---

## 2. USUARIOS

### 2.1 Listar Usuarios
Obtener lista de usuarios con paginación.

- **URL**: `/usuarios`
- **Método**: `GET`
- **Autenticación**: Requerida (Administrador)
- **Query Params**:
  - `page` (opcional): Número de página (default: 1)
  - `page_size` (opcional): Registros por página (default: 20)
  - `id_rol` (opcional): Filtrar por rol
  - `estado` (opcional): Filtrar por estado (1=activo, 0=inactivo)
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "usuarios": [
      {
        "id_usuario": 1,
        "nombre_completo": "Usuario Ejemplo",
        "usuario": "usuario1",
        "email": "usuario@ejemplo.com",
        "rol": "Vendedor",
        "sucursal": "Sucursal Principal",
        "estado": 1
      }
    ],
    "pagination": {
      "page": 1,
      "page_size": 20,
      "total": 10,
      "total_pages": 1
    }
  }
}
```

---

### 2.2 Crear Usuario
Crear un nuevo usuario.

- **URL**: `/usuarios`
- **Método**: `POST`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "id_rol": 2,
  "id_sucursal_principal": 1,
  "nombre_completo": "Nuevo Vendedor",
  "usuario": "vendedor1",
  "contrasena": "password123",
  "email": "vendedor@ejemplo.com",
  "telefono": "1234-5678"
}
```
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "data": {
    "id_usuario": 5,
    "nombre_completo": "Nuevo Vendedor",
    "usuario": "vendedor1",
    ...
  }
}
```

---

### 2.3 Obtener Usuario por ID
Ver detalles de un usuario específico.

- **URL**: `/usuarios/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida (Administrador)
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "id_usuario": 5,
    "nombre_completo": "Nuevo Vendedor",
    "usuario": "vendedor1",
    "email": "vendedor@ejemplo.com",
    "telefono": "1234-5678",
    "rol": "Vendedor",
    "sucursal": "Sucursal Principal",
    "estado": 1
  }
}
```

---

### 2.4 Actualizar Usuario
Actualizar información de un usuario.

- **URL**: `/usuarios/{id}`
- **Método**: `PUT`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "nombre_completo": "Usuario Actualizado",
  "email": "nuevo@email.com",
  "telefono": "9999-8888",
  "id_sucursal_principal": 2,
  "estado": 1
}
```

---

### 2.5 Eliminar Usuario (Desactivar)
Desactivar un usuario.

- **URL**: `/usuarios/{id}`
- **Método**: `DELETE`
- **Autenticación**: Requerida (Administrador)
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Usuario desactivado exitosamente",
  "data": {}
}
```

---

## 3. SUCURSALES

### 3.1 Listar Sucursales
Obtener lista de todas las sucursales.

- **URL**: `/sucursales`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `tipo_sucursal` (opcional): NORMAL o CONVENIO_GIMNASIO
  - `estado` (opcional): 1 o 0
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": [
    {
      "id_sucursal": 1,
      "codigo_sucursal": "SUC001",
      "nombre": "Sucursal Principal",
      "tipo_sucursal": "NORMAL",
      "direccion": "Zona 1, Ciudad",
      "telefono": "2222-3333",
      "responsable": "Juan Pérez",
      "estado": 1
    }
  ]
}
```

---

### 3.2 Crear Sucursal
Crear una nueva sucursal.

- **URL**: `/sucursales`
- **Método**: `POST`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "codigo_sucursal": "SUC002",
  "nombre": "Gimnasio Fitness Plus",
  "tipo_sucursal": "CONVENIO_GIMNASIO",
  "direccion": "Zona 10, Ciudad",
  "telefono": "3333-4444",
  "email": "gimnasio@example.com",
  "responsable": "María López"
}
```

---

### 3.3 Obtener Sucursal por ID
Ver detalles de una sucursal.

- **URL**: `/sucursales/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida

---

### 3.4 Actualizar Sucursal
Actualizar información de una sucursal.

- **URL**: `/sucursales/{id}`
- **Método**: `PUT`
- **Autenticación**: Requerida (Administrador)
- **Body**: Similar al de crear sucursal

---

## 4. PRODUCTOS

### 4.1 Listar Productos
Obtener lista de productos con paginación.

- **URL**: `/productos`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `page`, `page_size`
  - `categoria` (opcional)
  - `estado` (opcional)
  - `buscar` (opcional): Buscar por nombre o código
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "productos": [
      {
        "id_producto": 1,
        "codigo_producto": "PROT001",
        "nombre": "Proteína Whey 2lb",
        "descripcion": "Proteína de suero",
        "categoria": "suplemento",
        "unidad_medida": "frascos",
        "precio_base": 250.00,
        "estado": 1
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 4.2 Crear Producto
Crear un nuevo producto.

- **URL**: `/productos`
- **Método**: `POST`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "codigo_producto": "PROT001",
  "nombre": "Proteína Whey 2lb",
  "descripcion": "Proteína de suero de leche",
  "categoria": "suplemento",
  "unidad_medida": "frascos",
  "precio_base": 250.00,
  "requiere_lote": 1
}
```

---

### 4.3 Obtener Producto por ID
Ver detalles de un producto.

- **URL**: `/productos/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Respuesta**: Incluye información del producto y lotes disponibles

---

### 4.4 Actualizar Producto
Actualizar información de un producto.

- **URL**: `/productos/{id}`
- **Método**: `PUT`
- **Autenticación**: Requerida (Administrador)

---

## 5. INVENTARIO

### 5.1 Listar Inventario
Obtener inventario consolidado.

- **URL**: `/inventario`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `id_sucursal` (opcional)
  - `id_producto` (opcional)
  - `page`, `page_size`
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {
    "inventario": [
      {
        "id_inventario": 1,
        "sucursal": "Sucursal Principal",
        "producto": "Proteína Whey 2lb",
        "codigo_producto": "PROT001",
        "numero_lote": "LOT20250101",
        "fecha_vencimiento": "2026-12-31",
        "cantidad_disponible": 50,
        "cantidad_minima": 10,
        "alerta_inventario": "NORMAL"
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 5.2 Inventario por Sucursal
Obtener inventario de una sucursal específica.

- **URL**: `/inventario/sucursal/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida

---

### 5.3 Entrada de Inventario
Registrar entrada de productos al inventario.

- **URL**: `/inventario/entrada`
- **Método**: `POST`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "id_sucursal": 1,
  "id_producto": 1,
  "id_lote": 5,
  "cantidad": 100,
  "motivo": "Compra a proveedor"
}
```

---

### 5.4 Traslado entre Sucursales
Trasladar productos entre sucursales.

- **URL**: `/inventario/traslado`
- **Método**: `POST`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "id_sucursal_origen": 1,
  "id_sucursal_destino": 2,
  "id_producto": 1,
  "id_lote": 5,
  "cantidad": 20,
  "motivo": "Traslado por alta demanda"
}
```

---

### 5.5 Ajuste de Inventario
Realizar ajuste de inventario (positivo o negativo).

- **URL**: `/inventario/ajuste`
- **Método**: `POST`
- **Autenticación**: Requerida (Administrador)
- **Body**:
```json
{
  "id_sucursal": 1,
  "id_producto": 1,
  "id_lote": 5,
  "cantidad": -5,
  "motivo": "Corrección por conteo físico"
}
```

---

## 6. CLIENTES

### 6.1 Listar Clientes
Obtener lista de clientes.

- **URL**: `/clientes`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `page`, `page_size`
  - `buscar` (opcional): Buscar por nombre o identificación
  - `estado` (opcional)

---

### 6.2 Crear Cliente
Registrar un nuevo cliente.

- **URL**: `/clientes`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "nombre_completo": "Juan Pérez",
  "identificacion": "1234567890101",
  "telefono": "5555-6666",
  "email": "juan@example.com",
  "direccion": "Zona 5, Ciudad",
  "limite_credito": 5000.00
}
```

---

### 6.3 Obtener Cliente por ID
Ver detalles de un cliente.

- **URL**: `/clientes/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida

---

### 6.4 Actualizar Cliente
Actualizar información de un cliente.

- **URL**: `/clientes/{id}`
- **Método**: `PUT`
- **Autenticación**: Requerida

---

## 7. VENTAS

### 7.1 Crear Venta
Registrar una nueva venta.

- **URL**: `/ventas`
- **Método**: `POST`
- **Autenticación**: Requerida (Vendedor/Administrador)
- **Body**:
```json
{
  "id_sucursal": 1,
  "id_cliente": 5,
  "tipo_venta": "CONTADO",
  "metodo_pago": "efectivo",
  "descuento": 0,
  "observaciones": "Venta regular",
  "productos": [
    {
      "id_producto": 1,
      "id_lote": 5,
      "cantidad": 2,
      "precio_unitario": 250.00,
      "descuento": 0
    }
  ]
}
```
- **Respuesta exitosa** (200):
```json
{
  "success": true,
  "message": "Venta creada exitosamente",
  "data": {
    "id_venta": 15,
    "numero_venta": "V-00000015",
    "fecha_venta": "2025-01-15 14:30:00",
    "total": 500.00,
    ...
  }
}
```

---

### 7.2 Listar Ventas
Obtener lista de ventas con filtros.

- **URL**: `/ventas`
- **Método**: `GET`
- **Autenticación**: Requerida (Vendedor/Administrador)
- **Query Params**:
  - `id_sucursal` (opcional)
  - `fecha_inicio` (opcional): YYYY-MM-DD
  - `fecha_fin` (opcional): YYYY-MM-DD
  - `tipo_venta` (opcional): CONTADO o CREDITO
  - `estado` (opcional): COMPLETADA o ANULADA
  - `page`, `page_size`

---

### 7.3 Obtener Venta por ID
Ver detalles completos de una venta.

- **URL**: `/ventas/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Respuesta**: Incluye detalle de productos vendidos

---

## 8. CRÉDITOS

### 8.1 Listar Créditos
Obtener lista de créditos.

- **URL**: `/creditos`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `estado` (opcional): PENDIENTE, PAGADO_PARCIAL, PAGADO, VENCIDO
  - `id_cliente` (opcional)

---

### 8.2 Obtener Crédito por ID
Ver detalles de un crédito.

- **URL**: `/creditos/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Respuesta**: Incluye historial de pagos

---

### 8.3 Registrar Pago de Crédito
Registrar un abono a un crédito.

- **URL**: `/creditos/{id}/pagar`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "monto_pago": 500.00,
  "metodo_pago": "efectivo",
  "observaciones": "Abono parcial"
}
```

---

### 8.4 Créditos por Cliente
Obtener créditos de un cliente específico.

- **URL**: `/creditos/cliente/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida

---

## 9. COTIZACIONES

### 9.1 Listar Cotizaciones
Obtener lista de cotizaciones.

- **URL**: `/cotizaciones`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `estado` (opcional)
  - `id_cliente` (opcional)
  - `id_sucursal` (opcional)

---

### 9.2 Crear Cotización
Generar una nueva cotización.

- **URL**: `/cotizaciones`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "id_cliente": 5,
  "id_sucursal": 1,
  "fecha_vencimiento": "2025-02-15",
  "descuento": 0,
  "observaciones": "Cotización para cliente frecuente",
  "productos": [
    {
      "id_producto": 1,
      "cantidad": 5,
      "precio_unitario": 250.00,
      "descuento": 0
    }
  ]
}
```

---

### 9.3 Obtener Cotización por ID
Ver detalles de una cotización.

- **URL**: `/cotizaciones/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida

---

### 9.4 Actualizar Cotización
Actualizar estado u observaciones de una cotización.

- **URL**: `/cotizaciones/{id}`
- **Método**: `PUT`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "estado": "ACEPTADA",
  "observaciones": "Cliente aceptó la cotización"
}
```

---

### 9.5 Convertir Cotización a Venta
Convertir una cotización aceptada en venta.

- **URL**: `/cotizaciones/{id}/convertir`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "tipo_venta": "CONTADO",
  "metodo_pago": "tarjeta"
}
```

---

## 10. CIERRES DE CAJA

### 10.1 Listar Cierres de Caja
Obtener lista de cierres de caja.

- **URL**: `/cierres-caja`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `id_sucursal` (opcional)
  - `fecha_inicio` (opcional)
  - `fecha_fin` (opcional)
  - `estado` (opcional): ABIERTO o CERRADO

---

### 10.2 Crear Cierre de Caja
Abrir un nuevo cierre de caja diario.

- **URL**: `/cierres-caja`
- **Método**: `POST`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "id_sucursal": 1,
  "fecha_cierre": "2025-01-15"
}
```

---

### 10.3 Obtener Cierre por ID
Ver detalles de un cierre de caja.

- **URL**: `/cierres-caja/{id}`
- **Método**: `GET`
- **Autenticación**: Requerida

---

### 10.4 Cerrar Caja
Cerrar un cierre de caja y registrar monto declarado.

- **URL**: `/cierres-caja/{id}/cerrar`
- **Método**: `PUT`
- **Autenticación**: Requerida
- **Body**:
```json
{
  "monto_declarado": 5500.00,
  "observaciones": "Diferencia menor aceptable"
}
```

---

## 11. REPORTES

### 11.1 Reporte de Inventario por Sucursal
Generar reporte de inventario.

- **URL**: `/reportes/inventario`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `id_sucursal` (requerido)
  - `categoria` (opcional)

---

### 11.2 Reporte de Productos por Vencer
Obtener productos próximos a vencer.

- **URL**: `/reportes/productos-por-vencer`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `dias` (opcional, default: 30)
  - `id_sucursal` (opcional)

---

### 11.3 Reporte de Ventas por Sucursal
Generar reporte de ventas.

- **URL**: `/reportes/ventas`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `id_sucursal` (opcional)
  - `fecha_inicio` (requerido)
  - `fecha_fin` (requerido)
  - `tipo_sucursal` (opcional)

---

### 11.4 Reporte de Cuadre de Caja
Obtener cuadres de caja por fecha.

- **URL**: `/reportes/cuadre-caja`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `id_sucursal` (requerido)
  - `fecha` (requerido)

---

### 11.5 Reporte Mensual de Convenios
Generar reporte mensual para convenios con gimnasios.

- **URL**: `/reportes/convenios-mensuales`
- **Método**: `GET`
- **Autenticación**: Requerida (Administrador)
- **Query Params**:
  - `mes` (requerido): YYYY-MM
  - `id_sucursal` (opcional)

---

### 11.6 Reporte de Créditos por Cliente
Obtener estado de créditos de clientes.

- **URL**: `/reportes/creditos-cliente`
- **Método**: `GET`
- **Autenticación**: Requerida
- **Query Params**:
  - `id_cliente` (opcional)
  - `estado` (opcional)

---

## CÓDIGOS DE ESTADO HTTP

- `200`: Operación exitosa
- `400`: Petición incorrecta (datos inválidos)
- `401`: No autenticado (token inválido o ausente)
- `403`: No autorizado (sin permisos)
- `404`: Recurso no encontrado
- `500`: Error interno del servidor

---

## NOTAS DE SEGURIDAD

1. Todos los tokens JWT expiran en 8 horas
2. Las contraseñas se almacenan hasheadas con bcrypt
3. Los vendedores solo pueden acceder a datos de su sucursal asignada
4. Los administradores tienen acceso completo al sistema
5. Implementar HTTPS en producción
6. Cambiar JWT_SECRET_KEY en producción
