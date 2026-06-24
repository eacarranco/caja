USE caja_ahorro_pujota;

INSERT INTO roles (nombre, descripcion, endosable) VALUES
('Administrador Técnico', 'Gobierna usuarios, roles, permisos e imagen corporativa. Sin acceso financiero', FALSE),
('Presidente', 'Representante legal, convocatorias, supervisión, firma de certificados', FALSE),
('Analista Financiero', 'Configura productos financieros, parametros, calculos y distribucion de excedentes', FALSE),
('Tesorero', 'Ejecución financiera diaria: cobros, desembolsos, cierre de sesión', FALSE),
('Asistente de Tesorería', 'Apoyo en cobros de aportes, cuotas y multas', FALSE),
('Socio', 'Acceso al portal personal: consultas, solicitudes, comprobantes', FALSE),
('Secretario/a', 'Gestion administrativa: actas, comunicaciones, soporte a tesoreria', FALSE);

INSERT INTO permisos (codigo, nombre, descripcion, modulo) VALUES
('auth.login', 'Ingresar al sistema', 'Permite iniciar sesión en el sistema', 'Autenticacion'),
('auth.ver_2fa', 'Acceder con 2FA', 'Permite acceder con autenticación de dos factores', 'Autenticacion'),
('socio.registrar', 'Registrar nuevo socio', 'Permite registrar un nuevo socio en el sistema', 'Socios'),
('socio.editar', 'Editar datos de socio', 'Permite modificar los datos de un socio existente', 'Socios'),
('socio.cambiar_estado', 'Cambiar estado del socio', 'Permite cambiar el estado de un socio en su ciclo de vida', 'Socios'),
('socio.consultar', 'Consultar lista de socios', 'Permite consultar el listado de socios registrados', 'Socios'),
('socio.ver_financiero', 'Ver datos financieros del socio', 'Permite visualizar la información financiera del socio', 'Socios'),
('param.usuarios', 'Gestionar usuarios del sistema', 'CRUD completo de usuarios del sistema', 'Parametros del Sistema'),
('param.roles', 'Gestionar roles y permisos', 'Crear, editar y eliminar roles con permisos personalizados', 'Parametros del Sistema'),
('param.imagen', 'Configurar imagen corporativa', 'Gestionar logo, colores, membrete y razón social', 'Parametros del Sistema'),
('param.catalogos', 'Editar catálogos', 'Gestionar provincias, cantones y entidades públicas', 'Parametros del Sistema'),
('param.financiero', 'Configurar parametros financieros', 'Configurar tasas, montos, plazos y métodos de interes', 'Parametros del Sistema'),
('producto.crear', 'Crear productos financieros', 'Crear nuevos productos de crédito e inversión', 'Productos Financieros'),
('producto.editar', 'Editar productos', 'Modificar productos financieros existentes', 'Productos Financieros'),
('producto.activar', 'Activar/desactivar productos', 'Activar o desactivar productos financieros', 'Productos Financieros'),
('cobro.aporte', 'Registrar cobro de aporte', 'Registrar cobro de aporte obligatorio y voluntario', 'Cobros'),
('cobro.cuota_credito', 'Registrar cobro de cuota de crédito', 'Registrar cobro de cuotas de crédito', 'Cobros'),
('cobro.multa', 'Registrar cobro de multa', 'Registrar cobro de multas generadas', 'Cobros'),
('cobro.inversion', 'Registrar inversión voluntaria', 'Registrar apertura de inversión a plazo fijo', 'Cobros'),
('cobro.desembolso', 'Realizar desembolso de crédito', 'Ejecutar el desembolso de un crédito aprobado', 'Cobros'),
('cobro.anular', 'Anular cobro registrado', 'Anular un cobro previamente registrado', 'Cobros'),
('cobro.cierre_sesion', 'Ejecutar cierre de sesión mensual', 'Cerrar la sesión mensual con generación de acta', 'Cobros'),
('calculo.intereses', 'Ejecutar cálculo de intereses', 'Calcular intereses de creditos, ahorros e inversiones', 'Calculos Financieros'),
('calculo.excedentes', 'Calcular distribución de excedentes', 'Calcular la distribución de excedentes entre los socios', 'Calculos Financieros'),
('calculo.aprobar_excedentes', 'Aprobar distribución de excedentes', 'Aprobar la distribución de excedentes calculada', 'Calculos Financieros'),
('reporte.socios', 'Generar reportes de socios', 'Generar reportes del modulo de socios', 'Reportes'),
('reporte.financiero', 'Generar reportes financieros', 'Generar reportes del modulo financiero', 'Reportes'),
('reporte.cobros', 'Generar reportes de cobros', 'Generar reportes del modulo de cobros', 'Reportes'),
('credito.aprobar', 'Aprobar/rechazar creditos', 'Permite aprobar o rechazar solicitudes de credito en la bandeja de aprobacion', 'Creditos'),
('multa.impugnar', 'Impugnar multas', 'Permite autorizar la impugnacion de multas presentadas por los socios', 'Multas'),
('multa.autorizar_impugnacion', 'Autorizar impugnacion', 'Permite autorizar o rechazar impugnaciones de multas presentadas por los socios', 'Multas'),
('notificacion.configurar', 'Configurar reglas de notificacion', 'Permite configurar las reglas de envio de notificaciones', 'Notificaciones'),
('inversion.aprobar', 'Aprobar/rechazar inversiones', 'Permite aprobar o rechazar solicitudes de inversion en la bandeja de aprobacion', 'Inversiones'),
('socio.eliminar', 'Eliminar socio', 'Permite eliminar un socio del sistema de forma permanente', 'Socios');


INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES
(1, 1, TRUE), (1, 2, TRUE), (1, 6, TRUE), (1, 7, TRUE), (1, 8, TRUE), (1, 9, TRUE), (1, 10, TRUE), (1, 11, TRUE), (1, 26, TRUE), (1, 34, TRUE),
(2, 1, TRUE), (2, 2, TRUE), (2, 3, TRUE), (2, 4, TRUE), (2, 5, TRUE), (2, 6, TRUE), (2, 7, TRUE), (2, 21, TRUE), (2, 22, TRUE), (2, 25, TRUE), (2, 26, TRUE), (2, 27, TRUE), (2, 28, TRUE),
(3, 1, TRUE), (3, 2, TRUE), (3, 4, TRUE), (3, 6, TRUE), (3, 7, TRUE), (3, 12, TRUE), (3, 13, TRUE), (3, 14, TRUE), (3, 15, TRUE), (3, 21, TRUE), (3, 22, TRUE), (3, 23, TRUE), (3, 24, TRUE), (3, 26, TRUE), (3, 27, TRUE), (3, 28, TRUE),
(4, 1, TRUE), (4, 2, TRUE), (4, 3, TRUE), (4, 4, TRUE), (4, 6, TRUE), (4, 7, TRUE), (4, 16, TRUE), (4, 17, TRUE), (4, 18, TRUE), (4, 19, TRUE), (4, 20, TRUE), (4, 21, TRUE), (4, 22, TRUE), (4, 26, TRUE), (4, 27, TRUE), (4, 28, TRUE),
(5, 1, TRUE), (5, 16, TRUE), (5, 17, TRUE), (5, 18, TRUE), (5, 19, TRUE), (5, 26, TRUE), (5, 28, TRUE),
(6, 1, TRUE);

-- credito.aprobar asignado a Presidente (2) y Tesorero (4). Analista Financiero (3) hereda por endosable
INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES
(2, (SELECT id_permiso FROM permisos WHERE codigo = 'credito.aprobar'), TRUE),
(4, (SELECT id_permiso FROM permisos WHERE codigo = 'credito.aprobar'), TRUE);

-- Permisos para Secretario/a (rol 7)
INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'auth.login'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'auth.ver_2fa'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'socio.registrar'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'socio.editar'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'socio.cambiar_estado'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'socio.consultar'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'socio.ver_financiero'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'cobro.aporte'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'reporte.socios'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'multa.impugnar'), TRUE),
((SELECT id_rol FROM roles WHERE nombre = 'Secretario/a'), (SELECT id_permiso FROM permisos WHERE codigo = 'multa.autorizar_impugnacion'), TRUE);

INSERT INTO parametros (codigo, nombre, valor, tipo, modulo) VALUES
('tasa_interes_crédito', 'Tasa de interes para creditos', '6.00', 'decimal', 'financiero'),
('metodo_interes_default', 'Método de interes por defecto', 'simple', 'texto', 'financiero'),
('tasa_interes_ahorro', 'Tasa de interes sobre ahorros', '0.00', 'decimal', 'financiero'),
('tasa_interes_inversión', 'Tasa de interes para inversiones', '6.00', 'decimal', 'financiero'),
('aporte_obligatorio_mensual', 'Aporte obligatorio mensual', '10.00', 'decimal', 'financiero'),
('cuota_ingreso', 'Cuota única de ingreso', '20.00', 'decimal', 'financiero'),
('multa_retraso_10min', 'Multa retraso 10-30 minutos', '1.00', 'decimal', 'financiero'),
('multa_retraso_30min', 'Multa retraso >=30 minutos', '5.00', 'decimal', 'financiero'),
('multa_inasistencia', 'Multa por inasistencia', '5.00', 'decimal', 'financiero'),
('multa_mora_credito', 'Multa por mora de crédito', '5.00', 'decimal', 'financiero'),
('multa_cuota_impaga', 'Multa por cuota mensual impaga', '5.00', 'decimal', 'financiero'),
('límite_crédito_emergente', 'Límite crédito emergente', '300.00', 'decimal', 'financiero'),
('plazo_mínimo_inversión', 'Plazo mínimo inversión (meses)', '6', 'numero', 'financiero'),
('intentos_máx_login', 'Intentos máximo de login', '3', 'numero', 'seguridad'),
('bloqueo_minutos', 'Minutos de bloqueo', '15', 'numero', 'seguridad'),
('session_timeout_minutos', 'Timeout de sesión (minutos)', '30', 'numero', 'seguridad'),
('pin_2fa_dígitos', 'Dígitos del PIN 2FA', '6', 'numero', 'seguridad'),
('pin_2fa_expiracion_min', 'Expiración PIN 2FA (minutos)', '5', 'numero', 'seguridad'),
('máx_reenvío_pin_hora', 'Máximo reenvíos PIN por hora', '3', 'numero', 'seguridad'),
('logo_sidebar', 'Logo del sidebar', '', 'texto', 'imagen'),
('logo_sd', 'Logo sin fondo', '', 'texto', 'imagen'),
('abrev_caja', 'Abreviatura Caja', 'P&amp;S', 'texto', 'imagen');

INSERT INTO provincias (nombre) VALUES ('Pichincha');
INSERT INTO cantones (id_provincia, nombre) VALUES (1, 'Pedro Moncayo');
