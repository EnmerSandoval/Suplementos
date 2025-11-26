# GUÍA DE INSTALACIÓN PASO A PASO

Esta guía te llevará desde cero hasta tener el sistema completamente funcional.

---

## REQUISITOS DEL SISTEMA

### Software Necesario

#### Servidor Web
- **Apache 2.4+** o **Nginx 1.18+**
- Módulo `mod_rewrite` habilitado (Apache)
- Módulo `mod_headers` habilitado (Apache)

#### PHP
- **PHP 8.0 o superior**
- Extensiones requeridas:
  - `pdo_mysql`
  - `mbstring`
  - `json`
  - `openssl`

#### Base de Datos
- **MySQL 5.7+** o **MariaDB 10.3+**

#### Frontend (Desarrollo)
- **Node.js 18+**
- **npm 9+** o **yarn**

### Verificar Requisitos

```bash
# Verificar PHP
php -v
php -m | grep pdo_mysql

# Verificar MySQL
mysql --version

# Verificar Node.js
node -v
npm -v
```

---

## INSTALACIÓN EN DESARROLLO (Linux/Ubuntu)

### Paso 1: Instalar LAMP Stack

```bash
# Actualizar sistema
sudo apt update
sudo apt upgrade -y

# Instalar Apache
sudo apt install apache2 -y

# Instalar PHP 8.x
sudo apt install php8.1 php8.1-cli php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl -y

# Instalar MySQL
sudo apt install mysql-server -y

# Habilitar módulos de Apache
sudo a2enmod rewrite
sudo a2enmod headers

# Reiniciar Apache
sudo systemctl restart apache2
```

### Paso 2: Configurar MySQL

```bash
# Ejecutar script de seguridad de MySQL
sudo mysql_secure_installation

# Responder:
# - Set root password: YES (establecer contraseña segura)
# - Remove anonymous users: YES
# - Disallow root login remotely: YES
# - Remove test database: YES
# - Reload privilege tables: YES

# Acceder a MySQL
sudo mysql -u root -p
```

En el prompt de MySQL:

```sql
-- Crear usuario para la aplicación (cambiar password)
CREATE USER 'inventario_user'@'localhost' IDENTIFIED BY 'password_seguro_123';

-- Salir temporalmente
EXIT;
```

### Paso 3: Clonar o Copiar el Proyecto

```bash
# Opción A: Si tienes Git
cd /var/www/html
sudo git clone tu-repositorio gestion-inventario

# Opción B: Copiar archivos manualmente
sudo mkdir -p /var/www/html/gestion-inventario
sudo cp -r /ruta/de/tu/proyecto/* /var/www/html/gestion-inventario/

# Dar permisos
sudo chown -R www-data:www-data /var/www/html/gestion-inventario
sudo chmod -R 755 /var/www/html/gestion-inventario
```

### Paso 4: Configurar Base de Datos

```bash
# Acceder a MySQL
sudo mysql -u root -p

# Ejecutar script SQL
mysql -u root -p < /var/www/html/gestion-inventario/database/schema.sql

# O manualmente:
# mysql -u root -p
# source /var/www/html/gestion-inventario/database/schema.sql
```

Otorgar permisos al usuario:

```sql
GRANT ALL PRIVILEGES ON gestion_inventario.* TO 'inventario_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Verificar que se creó correctamente:

```bash
mysql -u inventario_user -p

# En MySQL:
SHOW DATABASES;
USE gestion_inventario;
SHOW TABLES;
EXIT;
```

### Paso 5: Configurar Backend

#### A. Configurar conexión a base de datos

Editar `/var/www/html/gestion-inventario/backend/src/config/database.php`:

```bash
sudo nano /var/www/html/gestion-inventario/backend/src/config/database.php
```

Cambiar:
```php
private $host = 'localhost';
private $database = 'gestion_inventario';
private $username = 'inventario_user';      // Tu usuario
private $password = 'password_seguro_123';  // Tu contraseña
```

#### B. Configurar JWT Secret

Editar `/var/www/html/gestion-inventario/backend/src/config/config.php`:

```bash
sudo nano /var/www/html/gestion-inventario/backend/src/config/config.php
```

Generar y cambiar:
```php
// Generar una clave aleatoria segura:
// openssl rand -base64 32
define('JWT_SECRET_KEY', 'tu_clave_secreta_generada_aleatoriamente');
```

#### C. Configurar VirtualHost de Apache

Crear archivo de configuración:

```bash
sudo nano /etc/apache2/sites-available/gestion-inventario-api.conf
```

Contenido:
```apache
<VirtualHost *:80>
    ServerName localhost
    ServerAlias api.localhost

    DocumentRoot /var/www/html/gestion-inventario/backend/public

    <Directory /var/www/html/gestion-inventario/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Asegurar que .htaccess funcione
        RewriteEngine On
    </Directory>

    # Logs
    ErrorLog ${APACHE_LOG_DIR}/gestion-inventario-api-error.log
    CustomLog ${APACHE_LOG_DIR}/gestion-inventario-api-access.log combined
</VirtualHost>
```

Habilitar sitio:
```bash
sudo a2ensite gestion-inventario-api.conf
sudo systemctl reload apache2
```

#### D. Verificar que `.htaccess` existe

```bash
ls -la /var/www/html/gestion-inventario/backend/public/.htaccess

# Si no existe, crearlo:
sudo nano /var/www/html/gestion-inventario/backend/public/.htaccess
```

Debe contener:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

### Paso 6: Probar el Backend

```bash
# Probar con curl
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"admin","contrasena":"admin123"}'

# Deberías recibir:
# {"success":true,"message":"Login exitoso","data":{...}}
```

Si hay error, revisar logs:
```bash
sudo tail -f /var/log/apache2/gestion-inventario-api-error.log
```

### Paso 7: Configurar Frontend

#### A. Instalar Node.js (si no está instalado)

```bash
# Descargar e instalar Node.js 18 LTS
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verificar
node -v
npm -v
```

#### B. Instalar dependencias del frontend

```bash
cd /var/www/html/gestion-inventario/frontend
npm install
```

#### C. Configurar URL de la API

Editar `frontend/src/services/api.js`:

```bash
nano src/services/api.js
```

Cambiar:
```javascript
const API_BASE_URL = 'http://localhost/api/v1';
```

#### D. Iniciar servidor de desarrollo

```bash
npm run dev
```

Deberías ver:
```
  VITE v5.0.0  ready in 500 ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
```

### Paso 8: Acceder al Sistema

1. Abrir navegador en `http://localhost:5173`
2. Iniciar sesión:
   - Usuario: `admin`
   - Contraseña: `admin123`

---

## INSTALACIÓN EN WINDOWS (XAMPP)

### Paso 1: Instalar XAMPP

1. Descargar XAMPP desde: https://www.apachefriends.org/
2. Instalar con PHP 8.0 o superior
3. Iniciar Apache y MySQL desde el panel de control de XAMPP

### Paso 2: Configurar Base de Datos

1. Abrir http://localhost/phpmyadmin
2. Ir a "SQL" y ejecutar el contenido de `database/schema.sql`
3. Verificar que se creó la base de datos `gestion_inventario`

### Paso 3: Copiar Backend

1. Copiar carpeta `backend` a `C:\xampp\htdocs\gestion-inventario\backend`
2. Editar `backend/src/config/database.php`:
```php
private $host = 'localhost';
private $database = 'gestion_inventario';
private $username = 'root';
private $password = ''; // En XAMPP, generalmente vacío
```

3. Editar `backend/src/config/config.php` y cambiar JWT secret

### Paso 4: Configurar Virtual Host (Opcional)

Editar `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/gestion-inventario/backend/public"
    ServerName api.localhost
    <Directory "C:/xampp/htdocs/gestion-inventario/backend/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Agregar en `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 api.localhost
```

Reiniciar Apache desde XAMPP.

### Paso 5: Probar Backend

Abrir navegador en: `http://localhost/gestion-inventario/backend/public/`

O con Postman/cURL:
```bash
curl -X POST http://localhost/gestion-inventario/backend/public/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"usuario\":\"admin\",\"contrasena\":\"admin123\"}"
```

### Paso 6: Configurar Frontend

1. Instalar Node.js desde: https://nodejs.org/
2. Abrir CMD en la carpeta `frontend`:
```cmd
cd C:\ruta\al\proyecto\frontend
npm install
```

3. Editar `frontend/src/services/api.js`:
```javascript
const API_BASE_URL = 'http://localhost/gestion-inventario/backend/public/api/v1';
```

4. Iniciar frontend:
```cmd
npm run dev
```

5. Abrir `http://localhost:5173`

---

## DEPLOYMENT EN PRODUCCIÓN

### Preparativos

1. **Servidor VPS/Dedicado** con Ubuntu 20.04+ o similar
2. **Dominio** configurado (ej: `tuempresa.com`)
3. **Certificado SSL** (Let's Encrypt gratuito)

### Paso 1: Configurar Servidor

```bash
# Conectar por SSH
ssh root@tu-servidor-ip

# Actualizar sistema
apt update && apt upgrade -y

# Instalar LAMP
apt install apache2 php8.1 php8.1-mysql mysql-server -y
a2enmod rewrite headers ssl
```

### Paso 2: Configurar SSL con Let's Encrypt

```bash
# Instalar Certbot
apt install certbot python3-certbot-apache -y

# Obtener certificado
certbot --apache -d tuempresa.com -d api.tuempresa.com

# Renovación automática ya está configurada
```

### Paso 3: Configurar Base de Datos

```bash
mysql_secure_installation

mysql -u root -p
```

```sql
CREATE DATABASE gestion_inventario;
CREATE USER 'inventario_prod'@'localhost' IDENTIFIED BY 'password_muy_seguro_cambiar';
GRANT ALL PRIVILEGES ON gestion_inventario.* TO 'inventario_prod'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Importar datos:
```bash
mysql -u inventario_prod -p gestion_inventario < /path/to/schema.sql
```

### Paso 4: Subir Backend

```bash
# Con Git
cd /var/www
git clone tu-repositorio gestion-inventario
cd gestion-inventario/backend

# O con SCP desde tu máquina local:
# scp -r backend/ root@tu-servidor:/var/www/gestion-inventario/
```

Configurar:
```bash
nano /var/www/gestion-inventario/backend/src/config/database.php
# Actualizar credenciales

nano /var/www/gestion-inventario/backend/src/config/config.php
# Cambiar JWT_SECRET_KEY
# Desactivar display_errors
# Cambiar CORS_ALLOWED_ORIGINS a tu dominio
```

### Paso 5: Configurar Apache para API

```bash
nano /etc/apache2/sites-available/api.tuempresa.com.conf
```

```apache
<VirtualHost *:443>
    ServerName api.tuempresa.com

    DocumentRoot /var/www/gestion-inventario/backend/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/api.tuempresa.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/api.tuempresa.com/privkey.pem

    <Directory /var/www/gestion-inventario/backend/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/api-error.log
    CustomLog ${APACHE_LOG_DIR}/api-access.log combined
</VirtualHost>

# Redireccionar HTTP a HTTPS
<VirtualHost *:80>
    ServerName api.tuempresa.com
    Redirect permanent / https://api.tuempresa.com/
</VirtualHost>
```

```bash
a2ensite api.tuempresa.com.conf
systemctl reload apache2
```

### Paso 6: Build y Deploy Frontend

En tu máquina local:

```bash
cd frontend

# Actualizar URL de API
nano src/services/api.js
# Cambiar a: https://api.tuempresa.com/v1

# Build para producción
npm run build

# Esto crea carpeta dist/ con archivos optimizados
```

Subir al servidor:
```bash
scp -r dist/ root@tu-servidor:/var/www/gestion-inventario/frontend/
```

En el servidor:

```bash
nano /etc/apache2/sites-available/tuempresa.com.conf
```

```apache
<VirtualHost *:443>
    ServerName tuempresa.com
    ServerAlias www.tuempresa.com

    DocumentRoot /var/www/gestion-inventario/frontend/dist

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/tuempresa.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/tuempresa.com/privkey.pem

    <Directory /var/www/gestion-inventario/frontend/dist>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # React Router - todas las rutas a index.html
        RewriteEngine On
        RewriteBase /
        RewriteRule ^index\.html$ - [L]
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule . /index.html [L]
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/frontend-error.log
    CustomLog ${APACHE_LOG_DIR}/frontend-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName tuempresa.com
    Redirect permanent / https://tuempresa.com/
</VirtualHost>
```

```bash
a2ensite tuempresa.com.conf
systemctl reload apache2
```

### Paso 7: Verificar Producción

1. Acceder a `https://tuempresa.com`
2. Login con admin/admin123
3. **CAMBIAR CONTRASEÑA INMEDIATAMENTE**

---

## SOLUCIÓN DE PROBLEMAS

### Error: "404 Not Found" en API

**Causa**: `.htaccess` no funciona o `mod_rewrite` deshabilitado

**Solución**:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2

# Verificar que AllowOverride All está en la configuración
```

### Error: "Connection refused" desde frontend

**Causa**: CORS o URL de API incorrecta

**Solución**:
```bash
# Revisar URL en frontend/src/services/api.js
# Verificar CORS en backend/src/config/config.php
```

### Error: "Access denied for user"

**Causa**: Credenciales de BD incorrectas

**Solución**:
```bash
# Verificar backend/src/config/database.php
# Probar conexión manualmente:
mysql -u usuario -p nombre_bd
```

### Error: "Token inválido"

**Causa**: JWT_SECRET_KEY diferente entre backend y token guardado

**Solución**:
1. Limpiar localStorage del navegador
2. Hacer login nuevamente

### Frontend no carga después del build

**Causa**: Rutas absolutas incorrectas

**Solución**:
```bash
# En vite.config.js agregar:
export default {
  base: '/'
}
```

---

## MANTENIMIENTO

### Backup Automático de Base de Datos

Crear script de backup:
```bash
sudo nano /usr/local/bin/backup-inventario.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backups/inventario"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

mysqldump -u inventario_prod -p'password' gestion_inventario > $BACKUP_DIR/backup_$DATE.sql

# Mantener solo últimos 30 días
find $BACKUP_DIR -name "backup_*.sql" -mtime +30 -delete
```

```bash
sudo chmod +x /usr/local/bin/backup-inventario.sh

# Configurar cron (todos los días a las 2 AM)
sudo crontab -e
# Agregar:
0 2 * * * /usr/local/bin/backup-inventario.sh
```

### Actualizar Sistema

```bash
# Conectar al servidor
ssh root@tu-servidor

# Hacer backup
/usr/local/bin/backup-inventario.sh

# Pull cambios (si usas Git)
cd /var/www/gestion-inventario
git pull

# Reiniciar servicios
systemctl restart apache2
```

---

¡Sistema instalado y funcionando! 🎉
