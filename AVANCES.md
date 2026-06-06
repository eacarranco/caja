# Avances del Proyecto — Caja de Ahorro Pujota-Simbaña

## Resumen
Sistema web MVC en PHP 8.4 + MySQL 8 para gestión integral. 100 archivos PHP propios, 75 vendor (PHPMailer), 56 vistas, 22 controladores, 11 helpers, 24 tablas.

---

## Módulos Implementados

### 1. Autenticación y Seguridad
- Login con cédula/contraseña, bloqueo por 3 intentos, timeout 30 min
- 2FA con PIN de 6 dígitos enviado por SMTP (PHPMailer) con template HTML
- Cambio de contraseña desde admin y desde portal socio
- CSRF por sesión
- Middleware `requireAuth()` en BaseController

### 2. Socios
- CRUD completo con búsqueda y paginación
- Cambio de estado (pendiente → pre_activo → activo → suspendido/retiro/exclusión/fallecido)
- Subida de foto y documentos de identidad (anverso/reverso)
- Acta de aprobación en PDF (HTML print)
- Estado de cuenta PDF

### 3. Parámetros del Sistema
- Listado y edición de parámetros con inputs tipo-aware (decimal, número, texto, booleano, color)
- 18 parámetros iniciales en seeds

### 4. Usuarios del Sistema
- CRUD completo
- Asignación de roles por checkboxes
- Toggle 2FA obligatorio
- Protección contra auto-eliminación

### 5. Roles y Permisos (RBAC)
- CRUD de roles
- Matriz de permisos por módulo con checkboxes
- Lógica de roles endosables (heredan TODOS los permisos)
- 28 permisos en 7 módulos, 6 roles

### 6. Catálogos
- Provincias, cantones, entidades públicas
- Agregar, editar, eliminar

### 7. Imagen Corporativa
- Subir logo (PNG/JPG)
- Color picker para color corporativo
- Persistencia en tabla `parámetros`

### 8. Productos Financieros
- CRUD completo (crédito e inversión)
- Tasa, plazo, monto mínimo/máximo, método de interés, requiere garante, penalidad
- Toggle activo/inactivo

### 9. Sesiones Mensuales
- Abrir sesión (número correlativo automático)
- Check-in de socios (registra asistencia + genera cuenta si no existe)
- Cierre de sesión con acta PDF
- Generación automática de multas al cerrar (retraso e inasistencia)
- Historial de operaciones por sesión

### 10. Cobros
- Registro AJAX (aporte obligatorio, excedente, cuota crédito, multa, inversión, desembolso, otro)
- Pago de cuota de crédito: actualiza amortización + cuenta ahorro
- Pago de multa: actualiza multa como pagada
- Anulación de cobro con motivo
- Historial por sesión
- Comprobante PDF

### 11. Cálculos Financieros
- **Simulador de crédito**: 3 métodos (Simple, Francés, Alemán) con tabla de amortización
- **Excedentes**: Cálculo y aprobación de distribución
- **Intereses de ahorro**: Cálculo mensual, acredita a `saldo_excedente`
- CalculadoraInteres.php con los 3 métodos

### 12. Créditos
- Solicitud con selección de garantes (multi-checkbox)
- Validación: garante requerido según producto, no auto-garante
- Aprobar, desembolsar, rechazar
- Cálculo automático de mora con intereses moratorios
- Tabla de amortización generada al aprobar

### 13. Inversiones
- Apertura con contrato PDF
- Listado con filtros
- Retiro anticipado con penalidad
- Cierre automático de inversiones vencidas

### 14. Multas
- Listado con filtros + paginación (tipo, socio, pagada/no pagada)
- Ver detalle
- Justificación desde portal (texto + archivo)
- Aprobación/rechazo de justificación desde admin
- Marcar como pagada

### 15. Asistencias
- Listado con filtros + paginación
- Justificación desde portal (texto + archivo)
- Aprobación/rechazo desde admin con botones en listado

### 16. Retiros de Ahorro
- Solicitud desde portal con validación de saldo disponible
- Admin aprueba/rechaza
- Desembolso: actualiza cuenta + cobro + historial

### 17. Dashboard
- Tarjetas resumen (socios activos, sesión activa, cobros mes, créditos vigentes)
- Últimos cobros registrados
- Sesiones del año
- Chart.js: cobros por mes + por tipo

### 18. Portal del Socio
- Datos personales y cuenta de ahorro
- Créditos (solicitar, ver estado)
- Inversiones (apertura, ver)
- Cobros registrados
- Historial de operaciones
- Multas (ver, justificar)
- Retiros (solicitar)
- Asistencias (ver, justificar)
- Notificaciones (ver, marcar leídas)
- Cambio de contraseña
- Diseño responsive con cards apiladas en móvil

### 19. Reportes
- Socios (PDF + CSV)
- Financiero (PDF)
- Cobros (PDF + CSV)
- Morosidad (con días de vencido y colores)
- Historial de operaciones (auditoría)

### 20. Certificados (PDF/HTML)
- Constancia de socio activo
- Certificado de libre deuda
- Estado de cuenta con movimientos

### 21. Notificaciones
- Helper `NotificacionHelper` para inserts desde cobros, créditos, sesiones
- Listar + marcar como leídas
- Vista en portal socio
- Integración con Pusher (pendiente credenciales reales)

### 22. Documentos (PDF/HTML)
- Comprobantes de cobro
- Actas de cierre de sesión
- Constancias
- Libre deuda
- Estado de cuenta
- Contrato de inversión
- Todos generados como HTML standalone con `@media print`

---

## Técnico

### Base de datos
- 24 tablas InnoDB con utf8mb4_unicode_ci
- UUIDs como claves primarias
- SHA-256 en registros inmodificables (historial_operaciones, cobros, socios)
- Índices en columnas de búsqueda frecuente

### Backend
- Front Controller con mapa de rutas explícito (110+ rutas)
- PDO singleton con prepared statements
- MVC puro: controladores delgados, helpers especializados
- Try/catch global con vista 500
- Encoding UTF-8 en toda la aplicación

### Frontend
- Bootstrap 5.3.3 + icons
- Sidebar responsive con toggle hamburguesa (transform + overlay)
- 31 tablas con `table-responsive`
- Portal con stacked cards en móvil (`table-responsive-stack`)
- Chart.js en dashboard
- AJAX para operaciones críticas (cobro, cambio estado)

### Seguridad
- bcrypt en contraseñas
- CSRF tokens por sesión
- 2FA PIN 6 dígitos SMTP
- Bloqueo 3 intentos, timeout 30 min
- Validación de cédula ecuatoriana
- Prepared statements en todas las consultas

### Correo (SMTP)
- PHPMailer v6.12 instalado vía Composer
- Config: `mail.titanix-ec.com:587`, TLS
- Usuario: `mailing@titanix-ec.com`
- Envío de PIN 2FA con template HTML

---

## Pendiente / Próximos Pasos

1. **Configurar Pusher**: Credenciales reales en `config/pusher.php` para notificaciones en tiempo real
2. **Poblar datos de prueba**: Socios, sesiones, cobros, créditos, inversiones
3. **Pruebas de integración**: Probar flujos completos
4. **Despliegue a producción**: Configurar dominio, SSL, BD producción

---

## Historial de Correcciones (Bugs)
- `saldo_actual` → `saldo_disponible` en estadoCuenta()
- `socio.listar` → `socio.consultar`
- `private` → `protected` en `mapearTipoHistorial`
- ENUM cobros.tipo agregado 'desembolso'
- Validación de monto en `aplicarPagoCuota`
- JOIN socios en consultas de crédito
- Permiso `aprobarJustificacion` corregido
- `requireAuth()` en DocumentoController
- 3 permisos faltantes implementados
- `ORDER BY apellidos` corregido en consultas

---

## Última actualización
Junio 2026
