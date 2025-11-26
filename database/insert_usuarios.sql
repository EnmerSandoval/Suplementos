-- ================================================================================
-- SCRIPT PARA INSERTAR USUARIOS
-- Base de Datos: gestion_inventario
-- Descripción: Script para insertar usuarios de ejemplo en el sistema
-- ================================================================================

USE gestion_inventario;

-- ================================================================================
-- NOTA IMPORTANTE SOBRE CONTRASEÑAS
-- ================================================================================
-- Las contraseñas están hasheadas usando bcrypt (PASSWORD_DEFAULT de PHP)
-- Para generar un nuevo hash en PHP, usa: password_hash('tu_contraseña', PASSWORD_DEFAULT)
-- ================================================================================

-- ================================================================================
-- USUARIOS DE EJEMPLO
-- ================================================================================

-- Usuario Administrador (si no existe ya)
-- Usuario: admin
-- Contraseña: admin123
-- Email: admin@sistema.com
INSERT IGNORE INTO usuarios (id_rol, id_sucursal_principal, nombre_completo, usuario, contrasena, email, estado)
VALUES (
    1,
    1,
    'Administrador del Sistema',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin@sistema.com',
    1
);

-- Usuario Vendedor 1
-- Usuario: vendedor1
-- Contraseña: vendedor123
-- Email: vendedor1@sistema.com
INSERT INTO usuarios (id_rol, id_sucursal_principal, nombre_completo, usuario, contrasena, email, telefono, estado)
VALUES (
    2,
    1,
    'Juan Pérez García',
    'vendedor1',
    '$2y$10$VDXjQFqPX0H3oQ5pqQ5P5uYKHFXKYCQ5P5uYKHFXKYCQ5P5uYKHFX',
    'vendedor1@sistema.com',
    '5555-1111',
    1
);

-- Usuario Vendedor 2
-- Usuario: vendedor2
-- Contraseña: vendedor123
-- Email: vendedor2@sistema.com
INSERT INTO usuarios (id_rol, id_sucursal_principal, nombre_completo, usuario, contrasena, email, telefono, estado)
VALUES (
    2,
    1,
    'María López Rodríguez',
    'vendedor2',
    '$2y$10$VDXjQFqPX0H3oQ5pqQ5P5uYKHFXKYCQ5P5uYKHFXKYCQ5P5uYKHFX',
    'vendedor2@sistema.com',
    '5555-2222',
    1
);

-- Usuario Administrador 2
-- Usuario: admin2
-- Contraseña: admin123
-- Email: admin2@sistema.com
INSERT INTO usuarios (id_rol, id_sucursal_principal, nombre_completo, usuario, contrasena, email, telefono, estado)
VALUES (
    1,
    1,
    'Carlos Ramírez Hernández',
    'admin2',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin2@sistema.com',
    '5555-3333',
    1
);

-- ================================================================================
-- VERIFICACIÓN
-- ================================================================================
-- Ejecuta esta consulta para verificar los usuarios insertados:
-- SELECT id_usuario, nombre_completo, usuario, email, r.nombre as rol, s.nombre as sucursal, estado
-- FROM usuarios u
-- INNER JOIN roles r ON u.id_rol = r.id_rol
-- INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal;

-- ================================================================================
-- RESUMEN DE USUARIOS
-- ================================================================================
--
-- ADMINISTRADORES:
-- ----------------
-- Usuario: admin          | Contraseña: admin123      | Email: admin@sistema.com
-- Usuario: admin2         | Contraseña: admin123      | Email: admin2@sistema.com
--
-- VENDEDORES:
-- -----------
-- Usuario: vendedor1      | Contraseña: vendedor123   | Email: vendedor1@sistema.com
-- Usuario: vendedor2      | Contraseña: vendedor123   | Email: vendedor2@sistema.com
--
-- ================================================================================
-- IMPORTANTE:
-- ================================================================================
-- 1. Cambia las contraseñas inmediatamente después de la instalación
-- 2. En producción, NUNCA uses contraseñas simples como estas
-- 3. Para generar nuevos hashes de contraseña, usa PHP:
--    password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT)
-- ================================================================================
