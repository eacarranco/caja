# Snapshot v1.0.0 — Caja de Ahorro Pujota-Simbaña

## Información del snapshot
- **Fecha:** 28/06/2026
- **Commit:** `5e90c82` (tag `v1.0.0`)
- **Rama:** `dev`
- **URL producción:** https://caja.sga-sp.com

## Contenido
Este snapshot captura el estado completo del sistema en el hito v1.0.0, incluyendo:

- Código fuente completo (PHP 8.4 MVC)
- Esquema de base de datos (29 tablas)
- Migraciones aplicadas
- Datos semilla (roles, permisos, parámetros, catálogos)
- Dump completo con datos transaccionales limpios

## Cómo restaurar desde cero

### 1. Base de datos
```bash
mysql -u root -p < basedatos/caja_ahorro_pujota_completo.sql
```

Esto crea la BD `caja_ahorro_pujota` con:
- 29 tablas con estructura completa
- Índices, FK, ENUMs
- Datos de catálogos (provincias, cantones, entidades públicas)
- Roles (7), permisos (34), matriz roles_permisos
- Parámetros del sistema (23)
- Usuario admin

### 2. Migraciones (alternativa al dump completo)
Si se parte de `database/schema.sql` + `database/seeds.sql`:
```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seeds.sql
php database/seed_admin.php
php database/migrate.php
```

### 3. Aplicación
```bash
git clone https://github.com/eacarranco/caja.git
git checkout tags/v1.0.0
composer install --no-dev --optimize-autoloader
```

Configurar `config/database.php`, `config/email.php`, `config/pusher.php` con credenciales de producción.

### 4. Permisos
```bash
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage/ webhook/
```

---

## Esquema de Base de Datos

### Tablas del Sistema (5)
| Tabla | Columnas | Propósito |
|-------|----------|-----------|
| `usuarios` | id_usuario, cedula, nombres, apellidos, correo_electronico, contrasena, activo, 2fa_activado, 2fa_secreto, intentos_fallidos, bloqueado_hasta, reset_token, reset_token_expiracion, reset_intentos, reset_token_usos, fecha_contrasena, ultimo_acceso, created_at | Usuarios del sistema con autenticación, 2FA, control de intentos |
| `roles` | id_rol, nombre, descripcion, endosable | Roles personalizables (7 roles) |
| `permisos` | id_permiso, codigo, nombre, descripcion, modulo | Catálogo de 34 permisos en 7 módulos |
| `roles_usuarios` | id_usuario, id_rol | Asignación N:M usuarios ↔ roles |
| `roles_permisos` | id_rol, id_permiso, permitir | Matriz de permisos por rol |

### Tablas de Socios (3)
| Tabla | Columnas | Propósito |
|-------|----------|-----------|
| `socios` | id_socio, cedula, nombre1, nombre2, apellido1, apellido2, correo_electronico, telefono, celular, direccion, id_provincia, id_canton, parroquia, fecha_nacimiento, estado_civil, nivel_instruccion, profesion, lugar_trabajo,代表ante_nombre, representante_cedula, fecha_ingreso, estado, hash_integridad, created_at, updated_at | Datos personales y estado del socio |
| `cuentas_ahorro` | id_cuenta_ahorro, id_socio, saldo_obligatorio, saldo_excedente, saldo_disponible, fecha_ultimo_movimiento | Cuentas de ahorro con 3 saldos |
| `capital_inversion` | id_capital_inversion, id_socio, saldo, fecha_ultimo_movimiento | Capital independiente para inversiones |
| `archivos` | id_archivo, entidad_tipo, entidad_id, nombre_original, nombre_archivo, mime_type, tamano, hash_sha256, created_at | Gestión polimórfica de archivos |

### Tablas Operativas (10)
| Tabla | Columnas | Propósito |
|-------|----------|-----------|
| `sesiones_mensuales` | id_sesion, numero_sesion, fecha_sesion, titulo, tipo, estado, fecha_apertura, fecha_cierre, usuario_cierre, acta_cierre_pdf, total_recaudado, total_desembolsado, saldo_caja | Sesiones ordinarias/extraordinarias/informativas |
| `asistencias` | id_asistencia, id_socio, id_sesion, tipo, justificacion, estado justificacion, fecha_justificacion, revisado_por | Registro de asistencia |
| `obligaciones_sesion` | id_obligacion, id_sesion, id_socio, tipo, concepto, monto, id_referencia, pagada, id_cobro, fecha_registro | Obligaciones de pago generadas al abrir sesión |
| `cobros` | id_cobro, id_sesion, id_socio, tipo, monto, medio_pago, referencia, id_referencia, anulado, motivo_anulacion, usuario_cobro, fecha_registro | Registro de cobros |
| `caja_movimientos` | id_movimiento, id_sesion, id_cobro, tipo_movimiento, concepto, monto, saldo_anterior, saldo_posterior, fecha_registro | Libro mayor de caja |
| `multas` | id_multa, id_socio, id_sesion, tipo, motivo, monto, estado, impugnacion_observacion, revisado_por, fecha_impugnacion, created_at | Multas con flujo de impugnación |
| `creditos` | id_credito, id_socio, id_producto, monto_solicitado, monto_aprobado, plazo, interes, metodo_amortizacion, estado, id_sesion_aprobacion, fecha_solicitud, fecha_aprobacion, fecha_desembolso | Solicitudes y desembolsos de créditos |
| `amortizaciones` | id_amortizacion, id_credito, numero_cuota, fecha_vencimiento, capital, interes, total, saldo_pendiente, estado, id_cobro | Tabla de amortización por cuota |
| `garantes` | id_garante, id_credito, id_socio, tipo_garante | Garantes de créditos |
| `inversiones` | id_inversion, id_socio, id_producto, monto, plazo_dias, tasa_interes, fecha_inicio, fecha_vencimiento, fecha_cierre, estado, destino_final, id_cobro_destino | Inversiones a plazo fijo |
| `solicitudes_retiro` | id_solicitud, id_socio, monto, estado, id_sesion, fecha_solicitud, fecha_gestion, revisado_por | Solicitudes de retiro de ahorro |

### Tablas de Seguimiento (4)
| Tabla | Columnas | Propósito |
|-------|----------|-----------|
| `historial_operaciones` | id_operacion, id_socio, id_usuario, tipo_operacion, monto, saldo_anterior, saldo_posterior, descripcion, id_referencia, hash_anterior, hash_operacion, id_sesion, created_at | Historial inmodificable con SHA-256 |
| `notificaciones` | id_notificacion, id_socio, id_usuario, tipo, titulo, mensaje, leida, buzon, created_at | Buzón de notificaciones |
| `reglas_notificacion` | id_regla, tipo_evento, activo, created_at | Reglas de notificación |
| `reglas_notificacion_destinatarios` | id_regla, id_rol | Destinatarios por regla |

### Tablas de Configuración (6)
| Tabla | Columnas | Propósito |
|-------|----------|-----------|
| `parametros` | id_parametro, codigo, nombre, valor, tipo, modulo, descripcion | 23 parámetros configurables |
| `productos_financieros` | id_producto, nombre, descripcion, tipo, condiciones, tasa_interes, plazo_minimo, plazo_maximo, monto_minimo, monto_maximo, activo | Productos de crédito e inversión |
| `provincias` | id_provincia, nombre | Catálogo de provincias Ecuador |
| `cantones` | id_canton, id_provincia, nombre | Catálogo de cantones |
| `catastro_entidades_publicas` | id_entidad, nombre | Entidades públicas |

## Migraciones Aplicadas (4)

| Archivo | Descripción |
|---------|-------------|
| `database/migracion_usuarios_columnas.php` | Agrega columnas a usuarios (reset_token, reset_intentos, etc.), notificaciones (buzon), multas (impugnacion_observacion, fecha_impugnacion) |
| `database/migracion_permiso_socio_eliminar.php` | Crea permiso `socio.eliminar` (ID 34) y lo asigna a rol Administrador (ID 1) |
| `database/migracion_parametro_retencion.php` | Crea parámetro `retencion_papelera_dias` (default 30, módulo general) |
| `database/migracion_sesiones_tipo.php` | Agrega columna `tipo` ENUM a `sesiones_mensuales` (ordinaria/extraordinaria/informativa) |

## Roles y Permisos

### Roles (7)
| ID | Nombre | Endosable |
|----|--------|-----------|
| 1 | Administrador Técnico | No |
| 2 | Presidente | No |
| 3 | Analista Financiero | **Sí** |
| 4 | Tesorero | No |
| 5 | Asistente Tesorería | No |
| 6 | Socio | No |
| 7 | Secretario/a | No |

### Permisos (34)
`auth.login`, `auth.ver_2fa`, `socio.registrar`, `socio.editar`, `socio.cambiar_estado`, `socio.consultar`, `socio.ver_financiero`, `socio.eliminar`, `param.usuarios`, `param.roles`, `param.imagen`, `param.catalogos`, `param.financiero`, `producto.crear`, `producto.editar`, `producto.activar`, `cobro.aporte`, `cobro.cuota_credito`, `cobro.multa`, `cobro.inversion`, `cobro.desembolso`, `cobro.anular`, `cobro.cierre_sesion`, `calculo.intereses`, `calculo.excedentes`, `calculo.aprobar_excedentes`, `reporte.socios`, `reporte.financiero`, `reporte.cobros`, `credito.aprobar`, `multa.impugnar`, `multa.autorizar_impugnacion`, `notificacion.configurar`, `inversion.aprobar`

## Parámetros del Sistema (23)

| Código | Tipo | Default |
|--------|------|---------|
| tasa_interes_credito | decimal | 6.00 |
| metodo_interes_default | texto | simple |
| tasa_interes_ahorro | decimal | 0.00 |
| tasa_interes_inversion | decimal | 6.00 |
| aporte_obligatorio_mensual | decimal | 10.00 |
| cuota_ingreso | decimal | 20.00 |
| multa_retraso_10min | decimal | 1.00 |
| multa_retraso_30min | decimal | 5.00 |
| multa_inasistencia | decimal | 5.00 |
| multa_mora_credito | decimal | 5.00 |
| multa_cuota_impaga | decimal | 5.00 |
| limite_credito_emergente | decimal | 300.00 |
| plazo_minimo_inversion | numero | 6 |
| intentos_max_login | numero | 3 |
| bloqueo_minutos | numero | 15 |
| session_timeout_minutos | numero | 30 |
| pin_2fa_digitos | numero | 6 |
| pin_2fa_expiracion_min | numero | 5 |
| max_reenvio_pin_hora | numero | 3 |
| retencion_papelera_dias | numero | 30 |
| logo_sidebar | texto | — |
| logo_sd | texto | — |
| abrev_caja | texto | P&S |

---

*Snapshot generado el 28/06/2026 — tag `v1.0.0`*
