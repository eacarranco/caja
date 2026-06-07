USE caja_ahorro_pujota;

INSERT INTO roles (nombre, descripción, endosable) VALUES
('Administrador Técnico', 'Gobierna usuarios, roles, permisos e imagen corporativa. Sin acceso financiero', FALSE),
('Presidente', 'Representante legal, convocatorias, supervisión, firma de certificados', FALSE),
('Analista Financiero', 'Configura productos financieros, parámetros, cálculos y distribución de excedentes', TRUE),
('Tesorero', 'Ejecución financiera diaria: cobros, desembolsos, cierre de sesión', FALSE),
('Asistente de Tesorería', 'Apoyo en cobros de aportes, cuotas y multas', FALSE),
('Socio', 'Acceso al portal personal: consultas, solicitudes, comprobantes', FALSE);

INSERT INTO permisos (código, nombre, descripción) VALUES
('auth.login', 'Ingresar al sistema', 'Permite iniciar sesión en el sistema'),
('auth.ver_2fa', 'Acceder con 2FA', 'Permite acceder con autenticación de dos factores'),
('socio.registrar', 'Registrar nuevo socio', 'Permite registrar un nuevo socio en el sistema'),
('socio.editar', 'Editar datos de socio', 'Permite modificar los datos de un socio existente'),
('socio.cambiar_estado', 'Cambiar estado del socio', 'Permite cambiar el estado de un socio en su ciclo de vida'),
('socio.consultar', 'Consultar lista de socios', 'Permite consultar el listado de socios registrados'),
('socio.ver_financiero', 'Ver datos financieros del socio', 'Permite visualizar la información financiera del socio'),
('param.usuarios', 'Gestionar usuarios del sistema', 'CRUD completo de usuarios del sistema'),
('param.roles', 'Gestionar roles y permisos', 'Crear, editar y eliminar roles con permisos personalizados'),
('param.imagen', 'Configurar imagen corporativa', 'Gestionar logo, colores, membrete y razón social'),
('param.catálogos', 'Editar catálogos', 'Gestionar provincias, cantones y entidades públicas'),
('param.financiero', 'Configurar parámetros financieros', 'Configurar tasas, montos, plazos y métodos de interés'),
('producto.crear', 'Crear productos financieros', 'Crear nuevos productos de crédito e inversión'),
('producto.editar', 'Editar productos', 'Modificar productos financieros existentes'),
('producto.activar', 'Activar/desactivar productos', 'Activar o desactivar productos financieros'),
('cobro.aporte', 'Registrar cobro de aporte', 'Registrar cobro de aporte obligatorio y voluntario'),
('cobro.cuota_crédito', 'Registrar cobro de cuota de crédito', 'Registrar cobro de cuotas de crédito'),
('cobro.multa', 'Registrar cobro de multa', 'Registrar cobro de multas generadas'),
('cobro.inversión', 'Registrar inversión voluntaria', 'Registrar apertura de inversión a plazo fijo'),
('cobro.desembolso', 'Realizar desembolso de crédito', 'Ejecutar el desembolso de un crédito aprobado'),
('cobro.anular', 'Anular cobro registrado', 'Anular un cobro previamente registrado'),
('cobro.cierre_sesión', 'Ejecutar cierre de sesión mensual', 'Cerrar la sesión mensual con generación de acta'),
('cálculo.intereses', 'Ejecutar cálculo de intereses', 'Calcular intereses de créditos, ahorros e inversiones'),
('cálculo.excedentes', 'Calcular distribución de excedentes', 'Calcular la distribución de excedentes entre los socios'),
('cálculo.aprobar_excedentes', 'Aprobar distribución de excedentes', 'Aprobar la distribución de excedentes calculada'),
('reporte.socios', 'Generar reportes de socios', 'Generar reportes del módulo de socios'),
('reporte.financiero', 'Generar reportes financieros', 'Generar reportes del módulo financiero'),
('reporte.cobros', 'Generar reportes de cobros', 'Generar reportes del módulo de cobros');

INSERT INTO roles_permisos (id_rol, id_permiso, permitir) VALUES
(1, 1, TRUE), (1, 2, TRUE), (1, 6, TRUE), (1, 7, TRUE), (1, 8, TRUE), (1, 9, TRUE), (1, 10, TRUE), (1, 11, TRUE), (1, 26, TRUE),
(2, 1, TRUE), (2, 2, TRUE), (2, 3, TRUE), (2, 4, TRUE), (2, 5, TRUE), (2, 6, TRUE), (2, 7, TRUE), (2, 21, TRUE), (2, 22, TRUE), (2, 25, TRUE), (2, 26, TRUE), (2, 27, TRUE), (2, 28, TRUE),
(3, 1, TRUE), (3, 2, TRUE), (3, 4, TRUE), (3, 6, TRUE), (3, 7, TRUE), (3, 12, TRUE), (3, 13, TRUE), (3, 14, TRUE), (3, 15, TRUE), (3, 21, TRUE), (3, 22, TRUE), (3, 23, TRUE), (3, 24, TRUE), (3, 26, TRUE), (3, 27, TRUE), (3, 28, TRUE),
(4, 1, TRUE), (4, 2, TRUE), (4, 3, TRUE), (4, 4, TRUE), (4, 6, TRUE), (4, 7, TRUE), (4, 16, TRUE), (4, 17, TRUE), (4, 18, TRUE), (4, 19, TRUE), (4, 20, TRUE), (4, 21, TRUE), (4, 22, TRUE), (4, 26, TRUE), (4, 27, TRUE), (4, 28, TRUE),
(5, 1, TRUE), (5, 6, TRUE), (5, 7, TRUE), (5, 16, TRUE), (5, 17, TRUE), (5, 18, TRUE), (5, 19, TRUE), (5, 26, TRUE), (5, 28, TRUE),
(6, 1, TRUE);

INSERT INTO parámetros (código, nombre, valor, tipo, módulo) VALUES
('tasa_interés_crédito', 'Tasa de interés para créditos', '6.00', 'decimal', 'financiero'),
('método_interés_default', 'Método de interés por defecto', 'simple', 'texto', 'financiero'),
('tasa_interés_ahorro', 'Tasa de interés sobre ahorros', '0.00', 'decimal', 'financiero'),
('tasa_interés_inversión', 'Tasa de interés para inversiones', '6.00', 'decimal', 'financiero'),
('aporte_obligatorio_mensual', 'Aporte obligatorio mensual', '10.00', 'decimal', 'financiero'),
('cuota_ingreso', 'Cuota única de ingreso', '20.00', 'decimal', 'financiero'),
('multa_retraso_10min', 'Multa retraso 10-30 minutos', '1.00', 'decimal', 'financiero'),
('multa_retraso_30min', 'Multa retraso >=30 minutos', '5.00', 'decimal', 'financiero'),
('multa_inasistencia', 'Multa por inasistencia', '5.00', 'decimal', 'financiero'),
('multa_mora_crédito', 'Multa por mora de crédito', '5.00', 'decimal', 'financiero'),
('límite_crédito_emergente', 'Límite crédito emergente', '300.00', 'decimal', 'financiero'),
('plazo_mínimo_inversión', 'Plazo mínimo inversión (meses)', '6', 'número', 'financiero'),
('intentos_máx_login', 'Intentos máximo de login', '3', 'número', 'seguridad'),
('bloqueo_minutos', 'Minutos de bloqueo', '15', 'número', 'seguridad'),
('session_timeout_minutos', 'Timeout de sesión (minutos)', '30', 'número', 'seguridad'),
('pin_2fa_dígitos', 'Dígitos del PIN 2FA', '6', 'número', 'seguridad'),
('pin_2fa_expiracion_min', 'Expiración PIN 2FA (minutos)', '5', 'número', 'seguridad'),
('máx_reenvío_pin_hora', 'Máximo reenvíos PIN por hora', '3', 'número', 'seguridad'),
('logo_sidebar', 'Logo del sidebar', '', 'texto', 'imagen'),
('logo_sd', 'Logo sin fondo', '', 'texto', 'imagen');

INSERT INTO provincias (nombre) VALUES ('Pichincha');
INSERT INTO cantones (id_provincia, nombre) VALUES (1, 'Pedro Moncayo');
