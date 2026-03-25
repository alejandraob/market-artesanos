Guía para Actualizar el Sitio en Producción sin Romper Nada
📋 Índice
Preparación Local

Respaldo de Producción

Proceso de Actualización

Verificaciones Post-Despliegue

Rollback (Plan de Reversión)

Checklist Rápido

Preparación Local
1. Antes de empezar a codificar
bash
# En tu entorno local (backend)
cd /ruta/a/tu/backend
git status  # Verifica que estás en la rama correcta
git pull origin main  # Actualiza con lo último (si usas git)
2. Ambiente local vs producción
Aspecto	Local	Producción
APP_ENV	local	production
APP_DEBUG	true	false
Base de datos	local	Hostinger
3. Pruebas locales exhaustivas
Antes de subir cualquier cambio, prueba:

bash
# Backend
php artisan test  # Si tienes tests
php artisan route:list  # Verifica rutas no se rompen
php artisan migrate:fresh --seed  # Prueba migraciones en BD local

# Frontend
npm run build  # Verifica que compila sin errores
npm run preview  # Prueba el build localmente
Respaldo de Producción
1. Respaldo de base de datos (¡CRÍTICO!)
bash
# Conéctate por SSH a producción
ssh -p [PUERTO] [USUARIO]@[IP]

# Crear backup de la BD
cd /home/u218633035/domains/artesanoscatriel.com
mysqldump -u u218633035_root -p u218633035_artesanos > backup_$(date +%Y%m%d_%H%M%S).sql

# Descargar backup a tu local (desde otra terminal)
scp -P [PUERTO] [USUARIO]@[IP]:/home/u218633035/domains/artesanoscatriel.com/backup_*.sql ./
2. Respaldo de archivos
bash
# Crear backup comprimido del proyecto
tar -czf backup_proyecto_$(date +%Y%m%d_%H%M%S).tar.gz backend/ public_html/

# Descargar a local
scp -P [PUERTO] [USUARIO]@[IP]:/home/u218633035/domains/artesanoscatriel.com/backup_proyecto_*.tar.gz ./
3. Registrar estado actual
bash
# Guardar lista de rutas actuales
php artisan route:list > rutas_actuales.txt

# Guardar versión de dependencias
composer show > dependencias_actuales.txt
npm list > frontend_dependencias.txt
Proceso de Actualización
Fase 1: Subir cambios de backend
Opción A: Cambios pequeños (solo PHP/config)
bash
# 1. Subir archivos específicos
rsync -avz -e "ssh -p [PUERTO]" \
  --exclude '.env' \
  --exclude 'vendor/' \
  --exclude 'node_modules/' \
  --exclude 'storage/' \
  ./backend/ [USUARIO]@[IP]:/home/u218633035/domains/artesanoscatriel.com/backend/

# 2. SSH a producción
ssh -p [PUERTO] [USUARIO]@[IP]
cd /home/u218633035/domains/artesanoscatriel.com/backend

# 3. Actualizar dependencias (solo si composer.json cambió)
composer install --no-dev --optimize-autoloader

# 4. Ejecutar migraciones (con precaución)
php artisan migrate --force  # Responde "yes" cuando pregunte

# 5. Limpiar caché
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
Opción B: Cambios grandes (migraciones/estructura)
bash
# Modo mantenimiento (opcional pero recomendado)
php artisan down --secret="tu-clave-secreta"
# Acceder al sitio con: https://artesanoscatriel.com/tu-clave-secreta

# [Ejecutar pasos de Opción A aquí]

# Reactivar sitio
php artisan up
Fase 2: Subir cambios de frontend
bash
# 1. En LOCAL, compilar frontend
cd /ruta/frontend
npm run build  # Genera carpeta dist/ con archivos nuevos

# 2. Subir archivos compilados
rsync -avz -e "ssh -p [PUERTO]" \
  ./dist/ [USUARIO]@[IP]:/home/u218633035/domains/artesanoscatriel.com/public_html/

# 3. Verificar que los nombres de archivos (hashes) se actualizaron
# El index.html nuevo tendrá referencias a los nuevos archivos JS/CSS
Fase 3: Actualizar .env (si es necesario)
bash
# NUNCA subas .env directamente. Cambia variables manualmente:
ssh -p [PUERTO] [USUARIO]@[IP]
cd /home/u218633035/domains/artesanoscatriel.com/backend
nano .env
# Solo modifica líneas específicas, pega con Ctrl+Shift+V
# Guarda con Ctrl+X, Y, Enter

# Limpia caché de configuración
php artisan config:clear
php artisan config:cache  # Opcional, en producción
Verificaciones Post-Despliegue
Checklist de verificación
bash
# 1. Verificar que el sitio carga
curl -I https://artesanoscatriel.com
# Esperado: HTTP/2 200

# 2. Verificar API
curl https://artesanoscatriel.com/api/test
# Esperado: {"message":"API funcionando"}

# 3. Verificar rutas principales
curl https://artesanoscatriel.com/api/products
curl https://artesanoscatriel.com/api/categories
curl https://artesanoscatriel.com/api/artisans
# Esperado: JSON, no 404

# 4. Verificar imágenes
curl -I https://artesanoscatriel.com/storage/products/[alguna-imagen].jpg
# Esperado: HTTP/2 200

# 5. Verificar frontend
# Abrir navegador en modo incógnito y probar:
# - Homepage
# - Catálogo
# - Detalle de producto
# - Carrito
# - Login/Registro
# - Panel admin (si aplica)

# 6. Revisar logs
tail -f /home/u218633035/domains/artesanoscatriel.com/backend/storage/logs/laravel.log
# No deben aparecer errores nuevos
Verificaciones automáticas (script)
bash
#!/bin/bash
# guardar como verify.sh y ejecutar después del deploy

echo "🔍 Verificando sitio..."
curl -s -o /dev/null -w "Home: %{http_code}\n" https://artesanoscatriel.com
curl -s -o /dev/null -w "API Test: %{http_code}\n" https://artesanoscatriel.com/api/test
curl -s -o /dev/null -w "Products: %{http_code}\n" https://artesanoscatriel.com/api/products

echo "📊 Revisando últimos errores en log:"
tail -5 /home/u218633035/domains/artesanoscatriel.com/backend/storage/logs/laravel.log
Rollback (Plan de Reversión)
Si algo sale MAL, vuelve atrás rápidamente
Opción 1: Revertir base de datos
bash
# 1. Restaurar backup de BD
cd /home/u218633035/domains/artesanoscatriel.com
mysql -u u218633035_root -p u218633035_artesanos < backup_[FECHA].sql

# 2. Revertir migración específica (si fue solo 1 migración)
php artisan migrate:rollback --step=1 --force
Opción 2: Revertir archivos
bash
# Restaurar backup de archivos
tar -xzf backup_proyecto_[FECHA].tar.gz -C /

# O deshacer cambios específicos con git (si usas git en producción)
git reset --hard HEAD~1
Opción 3: Rollback completo rápido
bash
# Poner sitio en mantenimiento
php artisan down --message="Actualizando sistema, volvemos en 5 minutos"

# Restaurar backup completo (BD + archivos)
# ... ejecutar restauración ...

# Reactivar sitio
php artisan up
Checklist Rápido (para pegar en tu pared)
✅ ANTES de subir
Hacer backup de BD producción

Hacer backup de archivos

Probar cambios en local exhaustivamente

Compilar frontend y probar build local

Registrar rutas actuales (por si acaso)

✅ DURANTE la subida
Subir backend (excluyendo .env, vendor, node_modules)

Ejecutar composer install --no-dev si composer.json cambió

Ejecutar php artisan migrate --force con precaución

Subir frontend compilado (carpeta dist/)

Limpiar cachés (config:clear, route:clear, etc.)

✅ DESPUÉS de subir
Verificar home (HTTP 200)

Verificar API test

Verificar endpoints principales

Verificar imágenes

Verificar navegación en modo incógnito

Revisar logs (tail -f storage/logs/laravel.log)

Monitorear 15-30 minutos

🆘 SI FALLA
Activar modo mantenimiento (php artisan down)

Restaurar backup de BD

Restaurar backup de archivos

Desactivar modo mantenimiento (php artisan up)

Analizar qué salió mal para la próxima

Comandos Útiles para SSH
bash
# Conectarte
ssh -p [PUERTO] [USUARIO]@[IP]

# Navegar al proyecto
cd /home/u218633035/domains/artesanoscatriel.com/backend

# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver errores recientes
tail -100 storage/logs/laravel.log | grep -i error

# Ver estado de migraciones
php artisan migrate:status

# Ver rutas
php artisan route:list --path=api

# Modo mantenimiento
php artisan down --secret=clave
php artisan up

# Limpiar todo
php artisan optimize:clear
Notas Importantes
NUNCA subas el archivo .env al servidor

SIEMPRE prueba en local antes de subir

SIEMPRE haz backup antes de migraciones

USA modo mantenimiento para cambios grandes

MONITOREA logs después del deploy

COMUNICA a los usuarios si habrá downtime