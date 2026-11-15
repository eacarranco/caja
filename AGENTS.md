# AGENTS.md — Caja de Ahorro Pujota-Simbaña

Sistema MVC PHP 8.4 + MySQL 8 sin framework para una caja de ahorro ecuatoriana. Producción real: https://caja.sga-sp.com. Rama de trabajo: `dev`.

## Comandos y entorno
- Local: `http://localhost/caja/` (Laragon/Apache). No hay npm, bundler, tests, lint ni CI de pruebas.
- `php` (8.4.12) está en PATH; verifica sintaxis con `php -l archivo.php`.
- No uses `php -S`: el ruteo depende de `.htaccess`/Apache.
- MySQL local: `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root caja_ahorro_pujota` (root, sin contraseña).
- Composer no está en PATH: `C:\laragon\bin\composer\composer.bat`. `vendor/` está ignorado en git pero ya instalado; el deploy corre `composer install --no-dev`.
- No hay autoload para clases de `app/`.

## Git y despliegue
- Push a `prod` dispara el webhook real: GitHub → `webhook/deploy.php` → `webhook/deploy-caja.sh`, que hace `git reset --hard origin/prod`, `composer install`, `php database/migrate.php` y permisos en `/var/www/caja.sga-sp.com`. No uses `prod` para experimentos.
- El deploy preserva `config/database.php`, `config/email.php`, `config/pusher.php` y `webhook/*`: cambios locales ahí nunca llegan a producción. `config/app.php` y el resto del código sí se despliegan.
- `config/app.php` tiene `DEBUG=true` (también en prod) y calcula `BASE_URL` desde el request (soporta `X-Forwarded-Proto`).
- `.htaccess` raíz bloquea `basedatos|database|config|app|storage|vendor|docs`; `webhook/` queda público a propósito. Bloquea cualquier directorio sensible nuevo.
- `.gitignore` ignora `storage/{documentos,fotos,logs}` pero NO `storage/archivos` (uploads). Revisa `git status`; evita `git add -A`.
- No imprimas ni pegues credenciales de `config/*.php`: las de Pusher/SMTP reales están versionadas.

## Ruteo y arquitectura
- `index.php` es el front controller: mapa explícito de rutas + fallback `/<segmento>/<metodo>` → `<Segmento>Controller->metodo`; también acepta `/<base>/<metodo>` si `<base>` está en el mapa.
- No hay middleware global: cada acción protegida debe llamar `requireAuth()` o `requirePermission('codigo')`. `app/middlewares/` existe pero no está conectado.
- `BaseController` provee `render()` (header/footer o `layouts/blank`), `json()`, `validateCSRF()`, `redirect()` e `historialInsert()`.
- Los controladores consultan PDO directo. Los únicos modelos (`BaseModel`, `Socio`, `Usuario`) se usan solo en Socios/Usuarios; el resto es SQL en controladores.
- Los helpers se cargan con `require_once` explícito: varios en `index.php`, otros dentro del controlador que los usa. Un helper nuevo no se autoloada.
- UI: tema Mazer por CDN (base Bootstrap 5), Bootstrap Icons, `public/assets/css/style.css` y `public/assets/js/app.js`. No hay build ni bundle local; no agregues otro Bootstrap.
- Sidebar en `app/views/layouts/header.php`: menú completo para staff; versión simplificada solo si el usuario tiene exactamente el rol `Socio`.

## Datos, migraciones y trampas de esquema
- BD local `caja_ahorro_pujota`: 29 tablas InnoDB, PK `CHAR(36)` UUID generadas por `UUIDGenerator` (sin default en BD).
- Dumps en `basedatos/`: `caja_ahorro_pujota_completo.sql` (más reciente, snapshot v1.0.0 del 28/06) y `caja_ahorro_pujota.sql` (dump de trabajo del 25/06); ambos tienen 29 tablas. El primero crea/usa `caja_ahorro_pujota`; no lo importes en producción (allí la BD es `db_caja_ahorro_pujota`).
- NO reconstruyas con `database/seeds.sql`: está desactualizado (roles `endosable=FALSE`, IDs de permisos viejos, códigos de parámetro con acentos). `database/schema.sql` también difiere de la BD viva en ENUMs (`'crédito'/'número'` vs `'credito'/'numero'`).
- `database/migrate.php` ejecuta todos los `migracion_*.php` en cada deploy, ordenados; sus errores no detienen el deploy. Las migraciones deben ser idempotentes (`SHOW COLUMNS`, `CREATE TABLE IF NOT EXISTS`). Los `migracion_*.sql` no se ejecutan solos (alguno hardcodea `USE caja_ahorro_pujota`).
- `database/seed_admin.php` hardcodea `C:\laragon\www\caja` y crea `Admin123`; solo para local. `database/limpiar_transaccional.sql` deja catálogos/usuarios y vacía lo operativo.
- Hay parámetros duplicados: `aporte_obligatorio_mensual`, `multa_mora_credito` y `multa_cuota_impaga` se leen de `parametros`; las multas de asistencia/retraso y los límites de login se leen de constantes en `config/app.php`. Revisa ambos al cambiar reglas.
- `parametros.codigo` conserva códigos legacy con acentos (ej. `tasa_interés_crédito`); copia el valor existente, no lo renombres.

## Invariantes de negocio
- Todo movimiento de dinero debe registrar el libro mayor con `CajaHelper::registrar()` (calcula `saldo_anterior`/`saldo_posterior` desde el último registro) y la auditoría con `historialInsert()`. Algunos flujos actuales silencian fallos con `try/catch`.
- `historial_operaciones` es solo inserción; `historialInsert()` no calcula hash. Los hashes SHA-256 de integridad se calculan en controladores para `cobros` y `socios`.
- Flujo de crédito: `ingresado → pendiente|aprobado → legalizado → desembolsado`; ver `README.md`.
- `obligaciones_sesion` es la deuda de la sesión: se genera al abrir/check-in, se paga con `id_cobro` y se usa al cerrar para multas. Al cobrar/anular actualiza `pagada`; no dupliques registros.
- Archivos: solo `FileManager` (whitelist, máx. 20 MB) en `storage/archivos/`, servidos por `ArchivoController` (nunca enlace directo).

## Convenciones
- Login por **cédula** (`Auth::login` consulta `usuarios.cedula`), no por `nombre_usuario`. Columnas 2FA con guion bajo: `_2fa_obligatorio`, `_2fa_activo`.
- Textos de interfaz en español con acentos/ñ; tablas, columnas, variables, permisos y constantes en ASCII sin acentos.
- Usa `BASE_URL`/`$baseUrl`; no hardcodees `/caja/` (local es subdirectorio, prod es raíz).
- POST de formularios: token CSRF + `validateCSRF()` y redirect con flash. JSON solo para AJAX explícito.
- Roles endosables: `Analista Financiero` (`endosable=1`) hereda todos los permisos.
- Seguridad: bcrypt, bloqueo tras 3 intentos, timeout 30 min, expiración de contraseña 180 días; `htmlspecialchars()` en salidas.

## Usuarios de prueba (BD local verificada)
| Cédula | Contraseña | Roles |
|---|---|---|
| `1002606083` | `admin123` | Todos los roles admin, incluido Analista endosable; 2FA off |
| `1002003000` | `admin123` | Solo Socio (portal) |
| `1003560438` | `admin123` | Tesorero |

La tabla de usuarios de prueba de `docs/SRS_RFS_27062026.md` no coincide con la BD actual (el login es por cédula).

## Trampas conocidas
- 2FA roto: `AuthController::login()` y `requireAuth()` redirigen a `/login/2fa`, pero el router resuelve eso como `AuthController::2fa()`; el método real es `_2fa()` (la ruta `/2fa` sí funciona). Los usuarios actuales tienen 2FA off.
- `AVANCES.md`, `CHANGELOG.md` y parte de `docs/` están desactualizados (conteos, credenciales, tablas). La referencia viva es la BD: 29 tablas, 34 permisos, 7 roles, 21 parámetros.
- `README.md` es la fuente más actual para el flujo de crédito; `docs/SRS_RFS_27062026.md` para arquitectura/despliegue; `docs/SNAPSHOT_v1.0.0.md` para restore (pero sus conteos de columnas no siempre coinciden).
