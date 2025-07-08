# 📋 Guía de Instalación ALARA en XAMPP

## 🚀 Instalación Rápida (5 minutos)

### 1. **Preparar XAMPP**
- ✅ Inicia **Apache** y **MySQL** desde el Panel de Control de XAMPP
- ✅ Verifica que ambos servicios estén en verde

### 2. **Subir Archivos por FTP**

#### Opción A: Instalación en la Raíz
```
Subir todos los archivos a:
C:\xampp\htdocs\
```
URL de acceso: `http://localhost/`

#### Opción B: Instalación en Subdirectorio (Recomendado)
```
Crear carpeta:
C:\xampp\htdocs\alara\

Subir todos los archivos dentro de esa carpeta
```
URL de acceso: `http://localhost/alara/`

### 3. **Estructura de Archivos**
```
htdocs/alara/
├── app/
│   ├── config/
│   ├── Controllers/
│   ├── Models/
│   ├── views/
│   └── ...
├── public/
│   ├── css/
│   ├── js/
│   ├── static/
│   └── uploads/     (se creará automáticamente)
├── database/
│   └── schema.sql
├── logs/            (se creará automáticamente)
├── .htaccess
├── index.php
└── install.php
```

### 4. **Ejecutar Instalador**

1. Abre tu navegador y ve a:
   - Si instalaste en raíz: `http://localhost/install.php`
   - Si instalaste en subdirectorio: `http://localhost/alara/install.php`

2. El instalador verificará:
   - ✅ Versión de PHP (necesitas 7.4+)
   - ✅ Extensiones requeridas
   - ✅ Permisos de carpetas
   - ✅ Conexión a MySQL

3. Completa el formulario:
   - **Host de Base de Datos:** `localhost`
   - **Nombre de Base de Datos:** `alara_db` (se creará automáticamente)
   - **Usuario de Base de Datos:** `root`
   - **Contraseña de Base de Datos:** (dejar vacío en XAMPP)
   - **Email del Administrador:** tu-email@ejemplo.com
   - **Contraseña del Administrador:** (mínimo 6 caracteres)

4. Click en **🚀 Instalar ALARA**

### 5. **Post-Instalación**

#### ⚠️ **MUY IMPORTANTE:**
```
ELIMINA el archivo install.php después de la instalación
```

#### Acceder al Sistema:
- **Sitio Web:** `http://localhost/alara/`
- **Panel Admin:** `http://localhost/alara/login`

## 🔧 Solución de Problemas

### Error: "No se puede conectar a MySQL"
- Verifica que MySQL esté ejecutándose en XAMPP
- El usuario por defecto es `root` sin contraseña

### Error: "mod_rewrite no está habilitado"
1. Abre `C:\xampp\apache\conf\httpd.conf`
2. Busca la línea: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Quita el `#` del inicio
4. Reinicia Apache

### Error 404 en todas las páginas
1. Verifica que el archivo `.htaccess` esté presente
2. En `httpd.conf`, busca `AllowOverride None` y cámbialo a `AllowOverride All`
3. Reinicia Apache

### Las imágenes no se suben
- En XAMPP local esto no debería pasar
- Si ocurre, crea manualmente las carpetas:
  - `public/uploads/cars/`
  - `public/uploads/documents/`

## 📁 Estructura de URLs

### Si instalaste en subdirectorio `/alara/`:
- Inicio: `http://localhost/alara/`
- Inventario: `http://localhost/alara/inventario.html`
- Admin: `http://localhost/alara/login`
- Dashboard: `http://localhost/alara/dashboard`

### Si instalaste en raíz:
- Inicio: `http://localhost/`
- Inventario: `http://localhost/inventario.html`
- Admin: `http://localhost/login`
- Dashboard: `http://localhost/dashboard`

## 🔐 Seguridad para Desarrollo

En XAMPP local no es crítico, pero es buena práctica:

1. **Cambiar contraseña de MySQL:**
   - Abre phpMyAdmin
   - Ve a "Cuentas de usuario"
   - Edita el usuario `root`
   - Establece una contraseña

2. **Proteger carpetas sensibles:**
   - El `.htaccess` ya protege `/app`, `/database` y `/logs`

## 🚦 Próximos Pasos

1. **Configurar Email (Opcional):**
   - Edita `.env` y agrega tus credenciales SMTP
   - Para pruebas locales puedes usar Mailtrap.io

2. **Subir Imágenes de Vehículos:**
   - Ve a Dashboard > Inventario > Agregar Vehículo
   - Las imágenes se guardarán en `public/uploads/cars/`

3. **Personalizar Contenido:**
   - Dashboard > Control de Contenido
   - Genera contenido con IA o edita manualmente

4. **Crear Usuarios:**
   - Dashboard > Gestión de Usuarios
   - Crea usuarios con roles: Admin, Gerencia o Ventas

## 💡 Tips para Desarrollo

- **Ver errores PHP:** Cambia `DEBUG_MODE=true` en `.env`
- **Logs:** Revisa `logs/php_errors.log` para debugging
- **Base de datos:** Usa phpMyAdmin en `http://localhost/phpmyadmin`

## 📞 ¿Necesitas Ayuda?

Si encuentras algún problema:
1. Revisa los logs en la carpeta `/logs`
2. Verifica que Apache y MySQL estén ejecutándose
3. Asegúrate de haber seguido todos los pasos

---

**¡Listo! Tu sistema ALARA está instalado y funcionando en XAMPP** 🎉