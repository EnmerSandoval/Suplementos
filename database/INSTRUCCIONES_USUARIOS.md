# Instrucciones para Insertar Usuarios

## Requisitos Previos

1. Tener MySQL/MariaDB instalado y corriendo
2. Haber ejecutado el script `schema.sql` para crear la base de datos

## Pasos para Insertar Usuarios

### Opción 1: Desde la línea de comandos

```bash
mysql -u root -p gestion_inventario < insert_usuarios.sql
```

### Opción 2: Desde MySQL Workbench o phpMyAdmin

1. Abre MySQL Workbench o phpMyAdmin
2. Selecciona la base de datos `gestion_inventario`
3. Abre el archivo `insert_usuarios.sql`
4. Ejecuta el script

### Opción 3: Desde la consola de MySQL

```bash
mysql -u root -p
```

Luego ejecuta:

```sql
USE gestion_inventario;
SOURCE /ruta/al/archivo/insert_usuarios.sql;
```

## Usuarios Disponibles

### Administradores

| Usuario | Contraseña | Email |
|---------|------------|-------|
| admin | admin123 | admin@sistema.com |
| admin2 | admin123 | admin2@sistema.com |

### Vendedores

| Usuario | Contraseña | Email |
|---------|------------|-------|
| vendedor1 | vendedor123 | vendedor1@sistema.com |
| vendedor2 | vendedor123 | vendedor2@sistema.com |

## Acceder al Sistema

1. Abre tu navegador y ve a la URL del frontend (ej: `http://localhost:5173`)
2. Ingresa el email y contraseña de cualquier usuario de la tabla anterior
3. Haz clic en "Iniciar Sesión"

## Cambiar Contraseñas

⚠️ **IMPORTANTE**: Cambia las contraseñas por defecto inmediatamente después de la instalación.

Para generar un nuevo hash de contraseña en PHP:

```php
<?php
$password = 'tu_nueva_contraseña';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>
```

Luego actualiza la base de datos:

```sql
UPDATE usuarios
SET contrasena = '$2y$10$nuevo_hash_aqui'
WHERE usuario = 'nombre_usuario';
```

## Verificar Usuarios Insertados

Para verificar que los usuarios se insertaron correctamente, ejecuta:

```sql
SELECT
    u.id_usuario,
    u.nombre_completo,
    u.usuario,
    u.email,
    r.nombre as rol,
    s.nombre as sucursal,
    CASE WHEN u.estado = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
FROM usuarios u
INNER JOIN roles r ON u.id_rol = r.id_rol
INNER JOIN sucursales s ON u.id_sucursal_principal = s.id_sucursal;
```

## Troubleshooting

### Error: "Duplicate entry for key 'usuario'"

Esto significa que el usuario ya existe. Puedes:

1. Usar un nombre de usuario diferente
2. Eliminar el usuario existente:

```sql
DELETE FROM usuarios WHERE usuario = 'nombre_usuario';
```

### Error: "Cannot add foreign key constraint"

Asegúrate de que:

1. La base de datos `gestion_inventario` existe
2. Las tablas `roles` y `sucursales` tienen datos
3. Los IDs de rol y sucursal existen

Para verificar:

```sql
SELECT * FROM roles;
SELECT * FROM sucursales;
```

## Notas de Seguridad

🔒 **Recomendaciones de Seguridad**:

1. **Nunca** uses estas contraseñas en producción
2. Cambia todas las contraseñas por defecto inmediatamente
3. Usa contraseñas fuertes (mínimo 12 caracteres, mayúsculas, minúsculas, números y símbolos)
4. Implementa políticas de expiración de contraseñas
5. Habilita autenticación de dos factores si es posible
6. Revisa regularmente los logs de acceso
