# MODELO DE DATOS - SISTEMA DE GESTIÓN DE INVENTARIO Y VENTAS MULTISUCURSAL

## 1. DESCRIPCIÓN GENERAL

Este documento describe el modelo de datos relacional para el sistema de gestión de inventario y ventas multisucursal enfocado en negocios de suplementos para gimnasio y farmacias.

## 2. DIAGRAMA LÓGICO DE ENTIDADES

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│   Roles     │────────<│   Usuarios   │>────────│  Sucursales │
└─────────────┘         └──────────────┘         └─────────────┘
                              │                         │
                              │                         │
                              ▼                         ▼
                        ┌──────────┐            ┌─────────────┐
                        │  Ventas  │────────────│  Productos  │
                        └──────────┘            └─────────────┘
                              │                         │
                              │                         │
                        ┌─────┴──────┐                 │
                        │            │                 │
                        ▼            ▼                 ▼
              ┌────────────────┐  ┌──────────┐  ┌──────────┐
              │ Detalle_Ventas │  │ Clientes │  │   Lotes  │
              └────────────────┘  └──────────┘  └──────────┘
                                        │              │
                                        │              │
                                        ▼              ▼
                                  ┌──────────┐  ┌────────────┐
                                  │ Creditos │  │ Inventario │
                                  └──────────┘  └────────────┘
                                        │              │
                                        │              │
                                        ▼              ▼
                                  ┌──────────┐  ┌───────────────────┐
                                  │   Pagos  │  │ Movimientos_Stock │
                                  └──────────┘  └───────────────────┘
```

## 3. ENTIDADES Y RELACIONES

### 3.1 ROLES
Define los tipos de usuarios del sistema.

**Campos:**
- `id_rol` (INT): Identificador único del rol (PK)
- `nombre` (VARCHAR): Nombre del rol (Administrador, Vendedor)
- `descripcion` (TEXT): Descripción del rol
- `estado` (TINYINT): Estado activo/inactivo

**Relaciones:**
- Un rol puede tener muchos usuarios (1:N con Usuarios)

---

### 3.2 USUARIOS
Almacena información de los usuarios del sistema.

**Campos:**
- `id_usuario` (INT): Identificador único del usuario (PK)
- `id_rol` (INT): Rol del usuario (FK a Roles)
- `id_sucursal_principal` (INT): Sucursal principal asignada (FK a Sucursales)
- `nombre_completo` (VARCHAR): Nombre completo del usuario
- `usuario` (VARCHAR): Nombre de usuario para login (UNIQUE)
- `contrasena` (VARCHAR): Contraseña hasheada
- `email` (VARCHAR): Correo electrónico
- `telefono` (VARCHAR): Teléfono de contacto
- `estado` (TINYINT): Estado activo/inactivo
- `fecha_creacion` (TIMESTAMP): Fecha de creación del registro
- `fecha_modificacion` (TIMESTAMP): Última modificación

**Relaciones:**
- Pertenece a un rol (N:1 con Roles)
- Pertenece a una sucursal principal (N:1 con Sucursales)
- Puede realizar muchas ventas (1:N con Ventas)
- Puede crear muchas cotizaciones (1:N con Cotizaciones)

---

### 3.3 SUCURSALES
Almacena información de las sucursales/puntos de venta.

**Campos:**
- `id_sucursal` (INT): Identificador único de la sucursal (PK)
- `codigo_sucursal` (VARCHAR): Código único de la sucursal (UNIQUE)
- `nombre` (VARCHAR): Nombre de la sucursal
- `tipo_sucursal` (ENUM): 'NORMAL' o 'CONVENIO_GIMNASIO'
- `direccion` (VARCHAR): Dirección física
- `telefono` (VARCHAR): Teléfono de contacto
- `email` (VARCHAR): Correo electrónico
- `responsable` (VARCHAR): Nombre del responsable
- `estado` (TINYINT): Estado activo/inactivo
- `fecha_creacion` (TIMESTAMP): Fecha de creación
- `fecha_modificacion` (TIMESTAMP): Última modificación

**Relaciones:**
- Tiene muchos usuarios asignados (1:N con Usuarios)
- Tiene inventario de muchos productos (1:N con Inventario)
- Tiene muchas ventas (1:N con Ventas)
- Tiene muchos movimientos de stock (1:N con Movimientos_Stock)
- Tiene muchos cierres de caja (1:N con Cierres_Caja)
- Puede tener precios específicos (1:N con Precios_Sucursal)

---

### 3.4 PRODUCTOS
Catálogo de productos disponibles.

**Campos:**
- `id_producto` (INT): Identificador único del producto (PK)
- `codigo_producto` (VARCHAR): Código único del producto (UNIQUE)
- `nombre` (VARCHAR): Nombre del producto
- `descripcion` (TEXT): Descripción detallada
- `categoria` (VARCHAR): Categoría del producto (suplemento, vitamina, medicamento, etc.)
- `unidad_medida` (VARCHAR): Unidad de medida (tabletas, frascos, sobres, cajas, etc.)
- `precio_base` (DECIMAL): Precio base del producto
- `requiere_lote` (TINYINT): Si requiere control de lotes (1=sí, 0=no)
- `estado` (TINYINT): Estado activo/inactivo
- `fecha_creacion` (TIMESTAMP): Fecha de creación
- `fecha_modificacion` (TIMESTAMP): Última modificación

**Relaciones:**
- Tiene muchos lotes (1:N con Lotes)
- Tiene inventario en muchas sucursales (1:N con Inventario)
- Aparece en muchos detalles de ventas (1:N con Detalle_Ventas)
- Aparece en muchos detalles de cotizaciones (1:N con Detalle_Cotizaciones)
- Puede tener precios específicos por sucursal (1:N con Precios_Sucursal)

---

### 3.5 LOTES
Control de lotes de productos con fecha de vencimiento.

**Campos:**
- `id_lote` (INT): Identificador único del lote (PK)
- `id_producto` (INT): Producto al que pertenece el lote (FK a Productos)
- `numero_lote` (VARCHAR): Número de lote del proveedor
- `fecha_fabricacion` (DATE): Fecha de fabricación
- `fecha_vencimiento` (DATE): Fecha de vencimiento
- `estado` (TINYINT): Estado activo/inactivo
- `fecha_creacion` (TIMESTAMP): Fecha de creación del registro

**Relaciones:**
- Pertenece a un producto (N:1 con Productos)
- Tiene inventario en muchas sucursales (1:N con Inventario)
- Aparece en muchos movimientos de stock (1:N con Movimientos_Stock)

---

### 3.6 INVENTARIO
Control de stock por sucursal y lote.

**Campos:**
- `id_inventario` (INT): Identificador único (PK)
- `id_sucursal` (INT): Sucursal donde está el stock (FK a Sucursales)
- `id_producto` (INT): Producto en inventario (FK a Productos)
- `id_lote` (INT): Lote específico (FK a Lotes, NULL si no requiere lote)
- `cantidad_disponible` (DECIMAL): Cantidad disponible actualmente
- `cantidad_minima` (DECIMAL): Cantidad mínima de stock (para alertas)
- `fecha_actualizacion` (TIMESTAMP): Última actualización del inventario

**Relaciones:**
- Pertenece a una sucursal (N:1 con Sucursales)
- Pertenece a un producto (N:1 con Productos)
- Pertenece a un lote (N:1 con Lotes)

**Restricciones:**
- UNIQUE (id_sucursal, id_producto, id_lote)

---

### 3.7 MOVIMIENTOS_STOCK
Registro de todos los movimientos de inventario.

**Campos:**
- `id_movimiento` (INT): Identificador único (PK)
- `id_sucursal_origen` (INT): Sucursal origen (FK a Sucursales, NULL en entradas)
- `id_sucursal_destino` (INT): Sucursal destino (FK a Sucursales, NULL en salidas)
- `id_producto` (INT): Producto del movimiento (FK a Productos)
- `id_lote` (INT): Lote del movimiento (FK a Lotes, NULL si no aplica)
- `tipo_movimiento` (ENUM): 'ENTRADA', 'SALIDA', 'TRASLADO', 'AJUSTE'
- `cantidad` (DECIMAL): Cantidad del movimiento
- `motivo` (VARCHAR): Motivo del movimiento
- `referencia_venta` (INT): ID de venta si es por venta (FK a Ventas, NULL si no aplica)
- `id_usuario` (INT): Usuario que realizó el movimiento (FK a Usuarios)
- `fecha_movimiento` (TIMESTAMP): Fecha y hora del movimiento
- `observaciones` (TEXT): Observaciones adicionales

**Relaciones:**
- Puede tener sucursal origen (N:1 con Sucursales)
- Puede tener sucursal destino (N:1 con Sucursales)
- Pertenece a un producto (N:1 con Productos)
- Pertenece a un lote (N:1 con Lotes)
- Puede estar asociado a una venta (N:1 con Ventas)
- Realizado por un usuario (N:1 con Usuarios)

---

### 3.8 PRECIOS_SUCURSAL
Precios específicos de productos por sucursal (sobrescribe precio base).

**Campos:**
- `id_precio_sucursal` (INT): Identificador único (PK)
- `id_producto` (INT): Producto (FK a Productos)
- `id_sucursal` (INT): Sucursal (FK a Sucursales)
- `precio_venta` (DECIMAL): Precio de venta en esta sucursal
- `fecha_vigencia_inicio` (DATE): Fecha de inicio de vigencia
- `fecha_vigencia_fin` (DATE): Fecha de fin de vigencia (NULL si es indefinido)
- `estado` (TINYINT): Estado activo/inactivo
- `fecha_creacion` (TIMESTAMP): Fecha de creación

**Relaciones:**
- Pertenece a un producto (N:1 con Productos)
- Pertenece a una sucursal (N:1 con Sucursales)

**Restricciones:**
- UNIQUE (id_producto, id_sucursal) para vigencia activa

---

### 3.9 CLIENTES
Información de clientes.

**Campos:**
- `id_cliente` (INT): Identificador único (PK)
- `codigo_cliente` (VARCHAR): Código único del cliente (UNIQUE)
- `nombre_completo` (VARCHAR): Nombre completo
- `identificacion` (VARCHAR): DPI/NIT
- `telefono` (VARCHAR): Teléfono
- `email` (VARCHAR): Correo electrónico
- `direccion` (TEXT): Dirección
- `limite_credito` (DECIMAL): Límite de crédito autorizado
- `saldo_actual` (DECIMAL): Saldo pendiente actual
- `estado` (TINYINT): Estado activo/inactivo
- `fecha_creacion` (TIMESTAMP): Fecha de registro
- `fecha_modificacion` (TIMESTAMP): Última modificación

**Relaciones:**
- Tiene muchas ventas (1:N con Ventas)
- Tiene muchas cotizaciones (1:N con Cotizaciones)
- Tiene muchos créditos (1:N con Creditos)

---

### 3.10 VENTAS
Registro de ventas realizadas.

**Campos:**
- `id_venta` (INT): Identificador único (PK)
- `numero_venta` (VARCHAR): Número de venta correlativo (UNIQUE)
- `id_sucursal` (INT): Sucursal donde se realizó la venta (FK a Sucursales)
- `id_vendedor` (INT): Vendedor que realizó la venta (FK a Usuarios)
- `id_cliente` (INT): Cliente (FK a Clientes, NULL si es venta genérica)
- `tipo_venta` (ENUM): 'CONTADO', 'CREDITO'
- `subtotal` (DECIMAL): Subtotal de la venta
- `descuento` (DECIMAL): Descuento aplicado
- `total` (DECIMAL): Total de la venta
- `metodo_pago` (VARCHAR): Método de pago (efectivo, tarjeta, transferencia, etc.)
- `estado` (ENUM): 'COMPLETADA', 'ANULADA'
- `fecha_venta` (TIMESTAMP): Fecha y hora de la venta
- `observaciones` (TEXT): Observaciones

**Relaciones:**
- Pertenece a una sucursal (N:1 con Sucursales)
- Realizada por un vendedor (N:1 con Usuarios)
- Pertenece a un cliente (N:1 con Clientes)
- Tiene muchos detalles (1:N con Detalle_Ventas)
- Genera movimientos de stock (1:N con Movimientos_Stock)
- Puede generar un crédito (1:1 con Creditos)

---

### 3.11 DETALLE_VENTAS
Detalle de productos vendidos en cada venta.

**Campos:**
- `id_detalle_venta` (INT): Identificador único (PK)
- `id_venta` (INT): Venta a la que pertenece (FK a Ventas)
- `id_producto` (INT): Producto vendido (FK a Productos)
- `id_lote` (INT): Lote del que se vendió (FK a Lotes, NULL si no aplica)
- `cantidad` (DECIMAL): Cantidad vendida
- `precio_unitario` (DECIMAL): Precio unitario al momento de la venta
- `subtotal` (DECIMAL): Subtotal de la línea
- `descuento` (DECIMAL): Descuento aplicado a la línea
- `total` (DECIMAL): Total de la línea

**Relaciones:**
- Pertenece a una venta (N:1 con Ventas)
- Pertenece a un producto (N:1 con Productos)
- Pertenece a un lote (N:1 con Lotes)

---

### 3.12 CREDITOS
Control de ventas a crédito.

**Campos:**
- `id_credito` (INT): Identificador único (PK)
- `id_venta` (INT): Venta que genera el crédito (FK a Ventas)
- `id_cliente` (INT): Cliente que tiene el crédito (FK a Clientes)
- `monto_total` (DECIMAL): Monto total del crédito
- `saldo_pendiente` (DECIMAL): Saldo pendiente por pagar
- `fecha_limite_pago` (DATE): Fecha límite de pago
- `estado` (ENUM): 'PENDIENTE', 'PAGADO_PARCIAL', 'PAGADO', 'VENCIDO'
- `fecha_creacion` (TIMESTAMP): Fecha de creación del crédito

**Relaciones:**
- Pertenece a una venta (1:1 con Ventas)
- Pertenece a un cliente (N:1 con Clientes)
- Tiene muchos pagos (1:N con Pagos_Credito)

---

### 3.13 PAGOS_CREDITO
Registro de pagos/abonos a créditos.

**Campos:**
- `id_pago` (INT): Identificador único (PK)
- `id_credito` (INT): Crédito al que se abona (FK a Creditos)
- `monto_pago` (DECIMAL): Monto del pago
- `metodo_pago` (VARCHAR): Método de pago
- `fecha_pago` (TIMESTAMP): Fecha y hora del pago
- `id_usuario_registro` (INT): Usuario que registró el pago (FK a Usuarios)
- `observaciones` (TEXT): Observaciones

**Relaciones:**
- Pertenece a un crédito (N:1 con Creditos)
- Registrado por un usuario (N:1 con Usuarios)

---

### 3.14 COTIZACIONES
Cotizaciones generadas para clientes.

**Campos:**
- `id_cotizacion` (INT): Identificador único (PK)
- `numero_cotizacion` (VARCHAR): Número de cotización (UNIQUE)
- `id_cliente` (INT): Cliente (FK a Clientes, NULL si es genérico)
- `id_vendedor` (INT): Vendedor que generó la cotización (FK a Usuarios)
- `id_sucursal` (INT): Sucursal donde se generó (FK a Sucursales)
- `subtotal` (DECIMAL): Subtotal de la cotización
- `descuento` (DECIMAL): Descuento aplicado
- `total` (DECIMAL): Total de la cotización
- `estado` (ENUM): 'PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'VENCIDA', 'CONVERTIDA_VENTA'
- `fecha_cotizacion` (TIMESTAMP): Fecha de generación
- `fecha_vencimiento` (DATE): Fecha de vencimiento de la cotización
- `id_venta_generada` (INT): ID de venta si se convirtió (FK a Ventas, NULL si no se convirtió)
- `observaciones` (TEXT): Observaciones

**Relaciones:**
- Pertenece a un cliente (N:1 con Clientes)
- Generada por un vendedor (N:1 con Usuarios)
- Pertenece a una sucursal (N:1 con Sucursales)
- Tiene muchos detalles (1:N con Detalle_Cotizaciones)
- Puede convertirse en una venta (N:1 con Ventas)

---

### 3.15 DETALLE_COTIZACIONES
Detalle de productos en cada cotización.

**Campos:**
- `id_detalle_cotizacion` (INT): Identificador único (PK)
- `id_cotizacion` (INT): Cotización a la que pertenece (FK a Cotizaciones)
- `id_producto` (INT): Producto cotizado (FK a Productos)
- `cantidad` (DECIMAL): Cantidad cotizada
- `precio_unitario` (DECIMAL): Precio unitario
- `subtotal` (DECIMAL): Subtotal de la línea
- `descuento` (DECIMAL): Descuento aplicado
- `total` (DECIMAL): Total de la línea

**Relaciones:**
- Pertenece a una cotización (N:1 con Cotizaciones)
- Pertenece a un producto (N:1 con Productos)

---

### 3.16 CIERRES_CAJA
Control de cierres de caja diarios (solo para sucursales normales).

**Campos:**
- `id_cierre` (INT): Identificador único (PK)
- `id_sucursal` (INT): Sucursal del cierre (FK a Sucursales)
- `id_usuario_cierre` (INT): Usuario que realizó el cierre (FK a Usuarios)
- `fecha_cierre` (DATE): Fecha del cierre
- `hora_cierre` (TIME): Hora del cierre
- `total_ventas` (DECIMAL): Total de ventas del día (calculado)
- `total_efectivo` (DECIMAL): Total en efectivo (calculado)
- `total_tarjeta` (DECIMAL): Total en tarjeta (calculado)
- `total_otros` (DECIMAL): Total otros métodos (calculado)
- `monto_declarado` (DECIMAL): Monto declarado por el cajero
- `diferencia` (DECIMAL): Diferencia entre calculado y declarado
- `estado` (ENUM): 'ABIERTO', 'CERRADO'
- `observaciones` (TEXT): Observaciones
- `fecha_creacion` (TIMESTAMP): Fecha de creación del registro

**Relaciones:**
- Pertenece a una sucursal (N:1 con Sucursales)
- Realizado por un usuario (N:1 con Usuarios)

---

## 4. ÍNDICES RECOMENDADOS

Para optimizar las consultas, se recomienda crear índices en las siguientes columnas:

### Usuarios
- `usuario` (UNIQUE)
- `id_rol`
- `id_sucursal_principal`
- `estado`

### Sucursales
- `codigo_sucursal` (UNIQUE)
- `tipo_sucursal`
- `estado`

### Productos
- `codigo_producto` (UNIQUE)
- `categoria`
- `estado`

### Lotes
- `id_producto`
- `fecha_vencimiento`
- `numero_lote`

### Inventario
- (id_sucursal, id_producto, id_lote) (UNIQUE)
- `cantidad_disponible`

### Movimientos_Stock
- `id_sucursal_origen`
- `id_sucursal_destino`
- `id_producto`
- `tipo_movimiento`
- `fecha_movimiento`

### Ventas
- `numero_venta` (UNIQUE)
- `id_sucursal`
- `id_vendedor`
- `id_cliente`
- `fecha_venta`
- `tipo_venta`
- `estado`

### Clientes
- `codigo_cliente` (UNIQUE)
- `identificacion`
- `estado`

### Creditos
- `id_cliente`
- `estado`
- `fecha_limite_pago`

### Cotizaciones
- `numero_cotizacion` (UNIQUE)
- `id_cliente`
- `id_sucursal`
- `estado`
- `fecha_vencimiento`

### Cierres_Caja
- `id_sucursal`
- `fecha_cierre`
- `estado`

---

## 5. LÓGICA DE NEGOCIO CLAVE

### 5.1 Control de Lotes
- Cada producto puede tener múltiples lotes con diferentes fechas de vencimiento
- El campo `requiere_lote` en Productos indica si el producto debe tener control de lotes
- Al vender, se debe especificar de qué lote se vende (preferiblemente FIFO - First In, First Out)

### 5.2 Movimientos de Inventario
- **ENTRADA**: Incrementa inventario en sucursal destino
- **SALIDA**: Decrementa inventario en sucursal origen (usualmente por venta)
- **TRASLADO**: Decrementa en origen e incrementa en destino
- **AJUSTE**: Para correcciones de inventario

### 5.3 Ventas a Crédito
- Al crear una venta tipo CREDITO, se debe crear automáticamente un registro en Creditos
- El saldo_actual del cliente debe actualizarse
- Los pagos van reduciendo el saldo_pendiente del crédito

### 5.4 Cotizaciones a Ventas
- Una cotización puede convertirse en venta
- Al convertir, se crea una nueva venta y se actualiza la cotización con id_venta_generada
- El estado cambia a 'CONVERTIDA_VENTA'

### 5.5 Cierres de Caja
- Solo para sucursales tipo 'NORMAL'
- Se calcula el total de ventas del día por método de pago
- Se compara con el monto declarado por el cajero

### 5.6 Convenios con Gimnasios
- Sucursales tipo 'CONVENIO_GIMNASIO' no requieren cierres diarios
- Se pueden generar reportes mensuales de ventas para estos convenios

---

## 6. CONSIDERACIONES DE INTEGRIDAD

1. **Integridad Referencial**: Todas las relaciones FK deben tener ON DELETE RESTRICT para evitar eliminaciones accidentales
2. **Triggers Recomendados**:
   - Actualizar inventario automáticamente al crear detalle de venta
   - Actualizar saldo_actual del cliente al crear créditos o pagos
   - Validar que la cantidad de venta no exceda el inventario disponible
3. **Transacciones**: Operaciones como ventas y traslados deben ser transaccionales

---

Este modelo proporciona una base sólida y escalable para el sistema de gestión de inventario y ventas multisucursal.
