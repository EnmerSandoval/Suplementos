-- ================================================================================
-- SISTEMA DE GESTIÓN DE INVENTARIO Y VENTAS MULTISUCURSAL
-- Base de Datos: MySQL / MariaDB
-- Descripción: Script completo para crear la base de datos y todas las tablas
-- Versión: 1.0
-- ================================================================================

-- Crear base de datos
DROP DATABASE IF EXISTS gestion_inventario;
CREATE DATABASE gestion_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_inventario;

-- ================================================================================
-- TABLA: roles
-- Descripción: Define los tipos de usuarios del sistema
-- ================================================================================
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: sucursales
-- Descripción: Información de sucursales/puntos de venta
-- ================================================================================
CREATE TABLE sucursales (
    id_sucursal INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sucursal VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    tipo_sucursal ENUM('NORMAL', 'CONVENIO_GIMNASIO') NOT NULL DEFAULT 'NORMAL',
    direccion VARCHAR(255),
    telefono VARCHAR(20),
    email VARCHAR(100),
    responsable VARCHAR(100),
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo_sucursal),
    INDEX idx_tipo (tipo_sucursal),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: usuarios
-- Descripción: Información de usuarios del sistema
-- ================================================================================
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    id_sucursal_principal INT NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada',
    email VARCHAR(100),
    telefono VARCHAR(20),
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_sucursal_principal) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_usuario (usuario),
    INDEX idx_rol (id_rol),
    INDEX idx_sucursal (id_sucursal_principal),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: productos
-- Descripción: Catálogo de productos
-- ================================================================================
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo_producto VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50) NOT NULL COMMENT 'suplemento, vitamina, medicamento, etc.',
    unidad_medida VARCHAR(30) NOT NULL COMMENT 'tabletas, frascos, sobres, cajas, etc.',
    precio_base DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    requiere_lote TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=requiere control de lotes, 0=no requiere',
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo_producto),
    INDEX idx_categoria (categoria),
    INDEX idx_estado (estado),
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: lotes
-- Descripción: Control de lotes de productos con fecha de vencimiento
-- ================================================================================
CREATE TABLE lotes (
    id_lote INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    numero_lote VARCHAR(50) NOT NULL,
    fecha_fabricacion DATE,
    fecha_vencimiento DATE NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_producto (id_producto),
    INDEX idx_vencimiento (fecha_vencimiento),
    INDEX idx_numero_lote (numero_lote),
    UNIQUE KEY unique_lote_producto (id_producto, numero_lote)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: inventario
-- Descripción: Control de stock por sucursal y lote
-- ================================================================================
CREATE TABLE inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    id_sucursal INT NOT NULL,
    id_producto INT NOT NULL,
    id_lote INT NULL COMMENT 'NULL si el producto no requiere lote',
    cantidad_disponible DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cantidad_minima DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Para alertas de stock mínimo',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY unique_inventario (id_sucursal, id_producto, id_lote),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_producto (id_producto),
    INDEX idx_cantidad (cantidad_disponible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: precios_sucursal
-- Descripción: Precios específicos de productos por sucursal
-- ================================================================================
CREATE TABLE precios_sucursal (
    id_precio_sucursal INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_sucursal INT NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    fecha_vigencia_inicio DATE NOT NULL,
    fecha_vigencia_fin DATE NULL COMMENT 'NULL si es vigencia indefinida',
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_producto (id_producto),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_vigencia (fecha_vigencia_inicio, fecha_vigencia_fin),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: clientes
-- Descripción: Información de clientes
-- ================================================================================
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    codigo_cliente VARCHAR(20) NOT NULL UNIQUE,
    nombre_completo VARCHAR(150) NOT NULL,
    identificacion VARCHAR(50) NOT NULL COMMENT 'DPI/NIT',
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    limite_credito DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    saldo_actual DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo_cliente),
    INDEX idx_identificacion (identificacion),
    INDEX idx_estado (estado),
    INDEX idx_nombre (nombre_completo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: ventas
-- Descripción: Registro de ventas realizadas
-- ================================================================================
CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    numero_venta VARCHAR(50) NOT NULL UNIQUE,
    id_sucursal INT NOT NULL,
    id_vendedor INT NOT NULL,
    id_cliente INT NULL COMMENT 'NULL si es venta genérica',
    tipo_venta ENUM('CONTADO', 'CREDITO') NOT NULL DEFAULT 'CONTADO',
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    metodo_pago VARCHAR(50) NOT NULL COMMENT 'efectivo, tarjeta, transferencia, etc.',
    estado ENUM('COMPLETADA', 'ANULADA') NOT NULL DEFAULT 'COMPLETADA',
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_numero (numero_venta),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_vendedor (id_vendedor),
    INDEX idx_cliente (id_cliente),
    INDEX idx_fecha (fecha_venta),
    INDEX idx_tipo (tipo_venta),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: detalle_ventas
-- Descripción: Detalle de productos vendidos en cada venta
-- ================================================================================
CREATE TABLE detalle_ventas (
    id_detalle_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    id_lote INT NULL COMMENT 'NULL si el producto no requiere lote',
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_venta (id_venta),
    INDEX idx_producto (id_producto),
    INDEX idx_lote (id_lote)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: movimientos_stock
-- Descripción: Registro de todos los movimientos de inventario
-- ================================================================================
CREATE TABLE movimientos_stock (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_sucursal_origen INT NULL COMMENT 'NULL en ENTRADA o AJUSTE positivo',
    id_sucursal_destino INT NULL COMMENT 'NULL en SALIDA o AJUSTE negativo',
    id_producto INT NOT NULL,
    id_lote INT NULL COMMENT 'NULL si el producto no requiere lote',
    tipo_movimiento ENUM('ENTRADA', 'SALIDA', 'TRASLADO', 'AJUSTE') NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    referencia_venta INT NULL COMMENT 'ID de venta si es SALIDA por venta',
    id_usuario INT NOT NULL,
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT,
    FOREIGN KEY (id_sucursal_origen) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_sucursal_destino) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_lote) REFERENCES lotes(id_lote) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (referencia_venta) REFERENCES ventas(id_venta) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_origen (id_sucursal_origen),
    INDEX idx_destino (id_sucursal_destino),
    INDEX idx_producto (id_producto),
    INDEX idx_tipo (tipo_movimiento),
    INDEX idx_fecha (fecha_movimiento),
    INDEX idx_referencia_venta (referencia_venta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: creditos
-- Descripción: Control de ventas a crédito
-- ================================================================================
CREATE TABLE creditos (
    id_credito INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_cliente INT NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    fecha_limite_pago DATE NOT NULL,
    estado ENUM('PENDIENTE', 'PAGADO_PARCIAL', 'PAGADO', 'VENCIDO') NOT NULL DEFAULT 'PENDIENTE',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_venta (id_venta),
    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_fecha_limite (fecha_limite_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: pagos_credito
-- Descripción: Registro de pagos/abonos a créditos
-- ================================================================================
CREATE TABLE pagos_credito (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_credito INT NOT NULL,
    monto_pago DECIMAL(10,2) NOT NULL,
    metodo_pago VARCHAR(50) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario_registro INT NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_credito (id_credito),
    INDEX idx_fecha (fecha_pago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: cotizaciones
-- Descripción: Cotizaciones generadas para clientes
-- ================================================================================
CREATE TABLE cotizaciones (
    id_cotizacion INT AUTO_INCREMENT PRIMARY KEY,
    numero_cotizacion VARCHAR(50) NOT NULL UNIQUE,
    id_cliente INT NULL COMMENT 'NULL si es cotización genérica',
    id_vendedor INT NOT NULL,
    id_sucursal INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM('PENDIENTE', 'ACEPTADA', 'RECHAZADA', 'VENCIDA', 'CONVERTIDA_VENTA') NOT NULL DEFAULT 'PENDIENTE',
    fecha_cotizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento DATE NOT NULL,
    id_venta_generada INT NULL COMMENT 'ID de venta si se convirtió',
    observaciones TEXT,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_vendedor) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_venta_generada) REFERENCES ventas(id_venta) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_numero (numero_cotizacion),
    INDEX idx_cliente (id_cliente),
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: detalle_cotizaciones
-- Descripción: Detalle de productos en cada cotización
-- ================================================================================
CREATE TABLE detalle_cotizaciones (
    id_detalle_cotizacion INT AUTO_INCREMENT PRIMARY KEY,
    id_cotizacion INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_cotizacion) REFERENCES cotizaciones(id_cotizacion) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_cotizacion (id_cotizacion),
    INDEX idx_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- TABLA: cierres_caja
-- Descripción: Control de cierres de caja diarios (solo sucursales normales)
-- ================================================================================
CREATE TABLE cierres_caja (
    id_cierre INT AUTO_INCREMENT PRIMARY KEY,
    id_sucursal INT NOT NULL,
    id_usuario_cierre INT NOT NULL,
    fecha_cierre DATE NOT NULL,
    hora_cierre TIME NOT NULL,
    total_ventas DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_efectivo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_tarjeta DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_otros DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    monto_declarado DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    diferencia DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'total_ventas - monto_declarado',
    estado ENUM('ABIERTO', 'CERRADO') NOT NULL DEFAULT 'ABIERTO',
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sucursal) REFERENCES sucursales(id_sucursal) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario_cierre) REFERENCES usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_sucursal (id_sucursal),
    INDEX idx_fecha (fecha_cierre),
    INDEX idx_estado (estado),
    UNIQUE KEY unique_cierre (id_sucursal, fecha_cierre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================================
-- DATOS INICIALES
-- ================================================================================

-- Insertar roles predeterminados
INSERT INTO roles (nombre, descripcion, estado) VALUES
('Administrador', 'Usuario con acceso completo al sistema', 1),
('Vendedor', 'Usuario con acceso a ventas y consultas básicas', 1);

-- Insertar sucursal principal de ejemplo
INSERT INTO sucursales (codigo_sucursal, nombre, tipo_sucursal, direccion, telefono, responsable, estado) VALUES
('SUC001', 'Sucursal Principal', 'NORMAL', 'Zona 1, Ciudad de Guatemala', '2222-3333', 'Gerente General', 1);

-- Insertar usuario administrador por defecto (contraseña: admin123)
-- Nota: En producción, cambiar la contraseña inmediatamente
INSERT INTO usuarios (id_rol, id_sucursal_principal, nombre_completo, usuario, contrasena, email, estado) VALUES
(1, 1, 'Administrador del Sistema', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@sistema.com', 1);
-- Nota: El hash corresponde a "admin123" usando PASSWORD_DEFAULT de PHP

-- ================================================================================
-- VISTAS ÚTILES
-- ================================================================================

-- Vista: Inventario consolidado con información de productos y lotes
CREATE VIEW v_inventario_actual AS
SELECT
    i.id_inventario,
    i.id_sucursal,
    s.nombre AS sucursal,
    i.id_producto,
    p.codigo_producto,
    p.nombre AS producto,
    p.categoria,
    p.unidad_medida,
    i.id_lote,
    l.numero_lote,
    l.fecha_vencimiento,
    i.cantidad_disponible,
    i.cantidad_minima,
    CASE
        WHEN l.fecha_vencimiento IS NOT NULL AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'PROXIMO_A_VENCER'
        WHEN l.fecha_vencimiento IS NOT NULL AND l.fecha_vencimiento < CURDATE() THEN 'VENCIDO'
        WHEN i.cantidad_disponible <= i.cantidad_minima THEN 'STOCK_BAJO'
        ELSE 'NORMAL'
    END AS alerta_inventario
FROM inventario i
INNER JOIN sucursales s ON i.id_sucursal = s.id_sucursal
INNER JOIN productos p ON i.id_producto = p.id_producto
LEFT JOIN lotes l ON i.id_lote = l.id_lote
WHERE s.estado = 1 AND p.estado = 1;

-- Vista: Ventas con detalles
CREATE VIEW v_ventas_detalle AS
SELECT
    v.id_venta,
    v.numero_venta,
    v.fecha_venta,
    s.nombre AS sucursal,
    u.nombre_completo AS vendedor,
    c.nombre_completo AS cliente,
    c.identificacion AS cliente_identificacion,
    v.tipo_venta,
    v.subtotal,
    v.descuento,
    v.total,
    v.metodo_pago,
    v.estado
FROM ventas v
INNER JOIN sucursales s ON v.id_sucursal = s.id_sucursal
INNER JOIN usuarios u ON v.id_vendedor = u.id_usuario
LEFT JOIN clientes c ON v.id_cliente = c.id_cliente;

-- Vista: Créditos pendientes con información de clientes
CREATE VIEW v_creditos_pendientes AS
SELECT
    cr.id_credito,
    cr.id_venta,
    v.numero_venta,
    c.codigo_cliente,
    c.nombre_completo AS cliente,
    c.telefono,
    cr.monto_total,
    cr.saldo_pendiente,
    cr.fecha_limite_pago,
    cr.estado,
    DATEDIFF(cr.fecha_limite_pago, CURDATE()) AS dias_para_vencimiento,
    CASE
        WHEN CURDATE() > cr.fecha_limite_pago AND cr.estado != 'PAGADO' THEN 'VENCIDO'
        WHEN DATEDIFF(cr.fecha_limite_pago, CURDATE()) <= 5 AND cr.estado != 'PAGADO' THEN 'POR_VENCER'
        ELSE 'VIGENTE'
    END AS estado_plazo
FROM creditos cr
INNER JOIN ventas v ON cr.id_venta = v.id_venta
INNER JOIN clientes c ON cr.id_cliente = c.id_cliente
WHERE cr.estado IN ('PENDIENTE', 'PAGADO_PARCIAL');

-- ================================================================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- ================================================================================

DELIMITER //

-- Procedimiento: Actualizar saldo de cliente
CREATE PROCEDURE sp_actualizar_saldo_cliente(
    IN p_id_cliente INT
)
BEGIN
    UPDATE clientes
    SET saldo_actual = (
        SELECT COALESCE(SUM(saldo_pendiente), 0)
        FROM creditos
        WHERE id_cliente = p_id_cliente
        AND estado IN ('PENDIENTE', 'PAGADO_PARCIAL')
    )
    WHERE id_cliente = p_id_cliente;
END //

-- Procedimiento: Verificar disponibilidad de producto
CREATE PROCEDURE sp_verificar_disponibilidad(
    IN p_id_sucursal INT,
    IN p_id_producto INT,
    IN p_id_lote INT,
    IN p_cantidad DECIMAL(10,2),
    OUT p_disponible TINYINT
)
BEGIN
    DECLARE v_cantidad_disponible DECIMAL(10,2);

    SELECT COALESCE(cantidad_disponible, 0) INTO v_cantidad_disponible
    FROM inventario
    WHERE id_sucursal = p_id_sucursal
    AND id_producto = p_id_producto
    AND (id_lote = p_id_lote OR (id_lote IS NULL AND p_id_lote IS NULL));

    IF v_cantidad_disponible >= p_cantidad THEN
        SET p_disponible = 1;
    ELSE
        SET p_disponible = 0;
    END IF;
END //

DELIMITER ;

-- ================================================================================
-- FIN DEL SCRIPT
-- ================================================================================

-- Instrucciones de uso:
-- 1. Ejecutar este script en MySQL/MariaDB
-- 2. La base de datos 'gestion_inventario' será creada automáticamente
-- 3. Usuario por defecto: admin / admin123 (CAMBIAR EN PRODUCCIÓN)
-- 4. Verificar que todas las tablas se hayan creado correctamente con: SHOW TABLES;
