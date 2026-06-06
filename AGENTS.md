# AGENTS.md — Contexto del proyecto

## Proyecto
**Caja de Ahorro y Crédito Solidaria Familiar Pujota-Simbaña**
Sistema web MVC PHP 8.4 + MySQL 8 para gestión integral de una caja de ahorro.

## Stack
- PHP 8.4 MVC puro (Front Controller + Route Map)
- MySQL 8.4 InnoDB utf8mb4_unicode_ci
- Bootstrap 5.3.3 + icons
- PHPMailer v6.12 (SMTP)
- HTML print (`@media print`) para PDF/comprobantes
- Pusher vía HTTP API cURL (pendiente configurar credenciales reales)
- Chart.js en dashboard

## Entorno
- Servidor: Laragon (Apache 2.4.62, PHP 8.4.12, MySQL 8.4.3)
- URL base: `http://localhost/caja/`
- Ruta raíz: `C:\laragon\www\caja\`
- Windows 11

## Estructura
```
caja/
├── index.php          # Front Controller + Route Map (110 rutas)
├── .htaccess          # RewriteRule todo a index.php
├── config/
│   ├── app.php        # Constantes globales
│   ├── database.php   # PDO singleton
│   ├── email.php      # SMTP (mail.titanix-ec.com:587)
│   └── pusher.php     # Credenciales Pusher (vacías)
├── database/
│   ├── schema.sql     # 24 tablas
│   ├── seeds.sql      # 6 roles, 28 permisos, matriz, 18 parámetros
│   └── seed_admin.php # Admin user creator
├── app/
│   ├── controllers/   # 22 (BaseController + 21 módulos)
│   ├── helpers/       # 11 helpers
│   ├── models/        # 3 (BaseModel, Socio, Usuario)
│   └── views/         # 56 vistas en 18 subdirectorios
│       └── layouts/   # header.php (sidebar dinámico) + footer.php
├── public/
│   └── assets/
│       ├── css/style.css
│       ├── js/app.js
│       └── images/
├── storage/
│   ├── documentos/    # HTML generados (comprobantes, actas, contratos)
│   ├── fotos/         # Fotos de socios
│   └── logs/
├── vendor/            # PHPMailer v6.12
├── composer.json
└── AGENTS.md
```

## Base de datos — 24 tablas
usuarios, roles, permisos, roles_usuarios, roles_permisos, socios, sesiones_mensuales, asistencias, cuentas_ahorro, productos_financieros, créditos, amortizaciones, inversiones, cobros, multas, historial_operaciones, notificaciones, parámetros, provincias, cantones, catastro_entidades_públicas, garantes, solicitudes_retiro

## RBAC
- **28 permisos** en 7 módulos
- **6 roles**: Administrador Técnico (solo técnico), Presidente, Analista Financiero (endosable=TRUE → hereda TODOS los permisos), Tesorero, Asistente Tesorería, Socio
- Roles endosables: si usuario tiene rol con endosable=TRUE, obtiene todos los permisos

## Controladores (21)
Auth, Socio, Parametro, Usuario, Rol, Catalogo, Imagen, Producto, Sesion, Cobro, Calculo (CalculadoraInteres: Simple, Francés, Alemán), Credito, Inversion, Reporte, Dashboard, Documento, Notificacion, Portal, Multa, Retiro, Asistencia

## Ayudantes (11)
UUIDGenerator, CedulaEcuador, Validator, Auth, RBAC, CSRFMiddleware, PDFGenerator, PusherHelper, NotificacionHelper, EmailHelper, CalculadoraInteres

## Seguridad
- CSRF por sesión
- 2FA PIN 6 dígitos vía SMTP (PHPMailer)
- bcrypt en contraseñas
- Prepared statements (PDO)
- Bloqueo 3 intentos, timeout 30 min
- SHA-256 en historial_operaciones (inmodificable)
- Middleware requireAuth() en BaseController

## Módulos implementados
- **Auth**: Login, 2FA PIN SMTP, logout, timeout, cambio contraseña (admin + portal)
- **Socios**: CRUD, búsqueda + paginación, cambio estado AJAX + acta PDF, subir foto/documentos, estado de cuenta PDF
- **Parámetros**: Listar + editar (inputs tipo-aware)
- **Usuarios**: CRUD con checkboxes roles, 2FA toggle, protege auto-eliminación
- **Roles**: CRUD + matriz permisos por módulo + endosable
- **Catálogos**: Provincias, cantones, entidades públicas
- **Imagen corporativa**: Logo + color picker
- **Productos financieros**: CRUD + toggle estado
- **Sesiones y Cobros**: Abrir sesión, check-in, registro cobro AJAX, cierre con acta + multas automáticas + historial, anular cobro, pago cuota crédito
- **Cálculos**: Simulador 3 métodos, tabla amortización, excedentes + aprobar, intereses de ahorro mensuales
- **Créditos**: Solicitar (con garantes), ver, aprobar, desembolsar, rechazar, mora automática
- **Inversiones**: Apertura con contrato PDF, listar, retiro anticipado, cierre automático vencidas
- **Multas**: CRUD, justificar portal, aprobar/rechazar, marcar pagada
- **Asistencias**: Listar, justificar portal, aprobar/rechazar admin
- **Retiros de ahorro**: Solicitud portal → admin aprueba/rechaza/desembolsa
- **Garantes**: Tabla propia, selección multi-checkbox, validaciones
- **Dashboard**: Tarjetas, últimos cobros, Chart.js
- **Portal socio**: Datos, cuenta, créditos, inversiones, cobros, historial, multas, retiros, asistencias, notificaciones, cambio contraseña
- **Reportes**: Socios + CSV, financiero, cobros + CSV, morosidad, historial operaciones
- **Certificados**: Constancia socio activo, libre deuda, estado de cuenta PDF
- **Notificaciones**: Helper inserts, listar + marcar leídas, vista portal
- **PDF/HTML**: Comprobantes cobro, actas cierre, constancias, libre deuda, estado cuenta, contrato inversión

## Responsive
- Sidebar colapsable con hamburguesa (transform translateX + overlay)
- 31 tablas con `table-responsive`
- Portal con `table-responsive-stack` (cards apiladas en móvil)
- Media queries en style.css

## Convenciones
- Nombres columnas en español con acentos (cédula, correo_electrónico, etc.)
- UUIDs como PK (`UUIDGenerator::generate()`)
- CONCAT_WS para nombres completos
- Ruteo híbrido: mapa explícito + fallback por convención
- Historial operaciones: `historialInsert()` en BaseController
- Roles endosables heredan todos los permisos
- Interés moratorio: `total * tasa * (días/30)` desde parámetro `multa_mora_crédito`
- Interés ahorro: `saldo * tasa / 100 / 12`, acredita a `saldo_excedente`
- PHPMailer vía Composer

## Usuarios de prueba
- **admin** / admin123 — Admin Técnico (sin 2FA, sin permisos financieros)
- **1002606083** / Admin123 — Admin Técnico (sin 2FA, sin permisos financieros)
- Para probar todo el sistema: Analista Financiero (rol endosable) hereda todos los permisos

## Estado actual
- 100 archivos PHP propios, 75 vendor (PHPMailer) = 175 total
- 0 errores de sintaxis
- Todas las rutas HTTP funcionales (200/302/403 según permisos)
- SMTP funcional con PHPMailer
- Pusher pendiente: configurar credenciales reales en `config/pusher.php`

## Próximos pasos
- Configurar credenciales Pusher reales en `config/pusher.php`
- Población de datos de prueba (socios, sesiones, cobros, créditos, inversiones)
- Pruebas de integración end-to-end
- Despliegue a producción
