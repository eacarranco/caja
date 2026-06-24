CREATE DATABASE IF NOT EXISTS caja_ahorro_pujota
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE caja_ahorro_pujota;

CREATE TABLE usuarios (
    id_usuario CHAR(36) PRIMARY KEY COMMENT 'Identificador único del usuario (UUID)',
    nombres VARCHAR(100) NOT NULL COMMENT 'Nombres del usuario',
    apellidos VARCHAR(100) NOT NULL COMMENT 'Apellidos del usuario',
    cedula VARCHAR(10) UNIQUE NOT NULL COMMENT 'Cédula de identidad ecuatoriana',
    correo_electronico VARCHAR(100) UNIQUE NOT NULL COMMENT 'Correo electrónico del usuario',
    telefono VARCHAR(15) COMMENT 'Número de telefono',
    nombre_usuario VARCHAR(50) UNIQUE NOT NULL COMMENT 'Nombre de usuario para inicio de sesión',
    contrasena VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt de la contrasena',
    token_activacion VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 del token de activacion de cuenta (nuevo socio)',
    token_activacion_expira DATETIME DEFAULT NULL COMMENT 'Expiracion del token de activacion',
    fecha_contrasena DATETIME DEFAULT NULL COMMENT 'Fecha del ultimo cambio de contrasena (control vencimiento 6 meses)',
    reset_token_hash VARCHAR(64) DEFAULT NULL COMMENT 'SHA-256 del token de restablecimiento de contrasena (olvide mi contrasena)',
    reset_token_expira DATETIME DEFAULT NULL COMMENT 'Expiracion del token de restablecimiento',
    reset_token_usos INT DEFAULT 0 COMMENT 'Contador de usos del token de restablecimiento',
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si el usuario está activo en el sistema',
    _2fa_obligatorio BOOLEAN DEFAULT FALSE COMMENT 'Indica si el 2FA es obligatorio para este usuario',
    _2fa_activo BOOLEAN DEFAULT FALSE COMMENT 'Indica si el 2FA está actualmente activo',
    bloqueado_hasta DATETIME NULL COMMENT 'Fecha/hasta cuándo está bloqueado (3 intentos fallidos)',
    intentos_fallidos INT DEFAULT 0 COMMENT 'Contador de intentos fallidos de inicio de sesión',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
    fecha_ultimo_acceso DATETIME NULL COMMENT 'Fecha y hora del último inicio de sesión exitoso',
    INDEX idx_usuarios_cedula (cedula),
    INDEX idx_usuarios_correo (correo_electronico),
    INDEX idx_usuarios_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema con credenciales de acceso y control 2FA';

CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador numérico del rol',
    nombre VARCHAR(50) UNIQUE NOT NULL COMMENT 'Nombre personalizable del rol (ej: Presidente, Tesorero)',
    descripcion VARCHAR(255) COMMENT 'Descripción de las funciones del rol',
    endosable BOOLEAN DEFAULT FALSE COMMENT 'Si es TRUE, este rol puede acumular permisos de otros roles'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema — 100% personalizables desde el panel de administración';

CREATE TABLE permisos (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador numérico del permiso',
    codigo VARCHAR(100) UNIQUE NOT NULL COMMENT 'Código único del permiso (ej: socio.registrar)',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo del permiso',
    descripcion VARCHAR(255) COMMENT 'Descripción detallada del alcance del permiso',
    modulo VARCHAR(50) DEFAULT '' COMMENT 'Modulo/categoria para agrupar permisos en la interfaz'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de permisos disponibles en el sistema';

CREATE TABLE roles_usuarios (
    id_usuario CHAR(36) NOT NULL COMMENT 'FK al UUID del usuario',
    id_rol INT NOT NULL COMMENT 'FK al ID del rol',
    PRIMARY KEY (id_usuario, id_rol),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asignación de roles a usuarios (relación muchos-a-muchos)';

CREATE TABLE roles_permisos (
    id_rol INT NOT NULL COMMENT 'FK al ID del rol',
    id_permiso INT NOT NULL COMMENT 'FK al ID del permiso',
    permitir BOOLEAN DEFAULT TRUE COMMENT 'TRUE = concedido, FALSE = denegado explícitamente',
    PRIMARY KEY (id_rol, id_permiso),
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES permisos(id_permiso) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz de permisos asignados a cada rol (gestión por checkboxes)';

CREATE TABLE socios (
    id_socio CHAR(36) PRIMARY KEY COMMENT 'Identificador único del socio (UUID)',
    cedula VARCHAR(10) UNIQUE NOT NULL COMMENT 'Cédula de identidad ecuatoriana (10 dígitos, dígito verificador)',
    apellido1 VARCHAR(50) NOT NULL COMMENT 'Primer apellido (mayúsculas)',
    apellido2 VARCHAR(50) COMMENT 'Segundo apellido (mayúsculas)',
    nombre1 VARCHAR(50) NOT NULL COMMENT 'Primer nombre (mayúsculas)',
    nombre2 VARCHAR(50) COMMENT 'Segundo nombre (mayúsculas)',
    fecha_nacimiento DATE NOT NULL COMMENT 'Fecha de nacimiento',
    genero ENUM('masculino','femenino') NOT NULL COMMENT 'Género del socio',
    estado_civil ENUM('soltero','casado','divorciado','viudo','unión_libre') COMMENT 'Estado civil del socio',
    direccion VARCHAR(200) NOT NULL COMMENT 'Dirección de residencia',
    telefono VARCHAR(15) COMMENT 'Número de telefono fijo',
    celular VARCHAR(15) NOT NULL COMMENT 'Número de celular',
    correo_electronico VARCHAR(100) UNIQUE NOT NULL COMMENT 'Correo electrónico (validado con PIN 6 dígitos)',
    profesion VARCHAR(100) COMMENT 'Profesión u ocupación',
    foto_url VARCHAR(255) COMMENT 'URL de la fotografía del socio',
    documento_identidad_anverso VARCHAR(255) COMMENT 'Archivo PDF/JPG del anverso de la cedula',
    documento_identidad_reverso VARCHAR(255) COMMENT 'Archivo PDF/JPG del reverso de la cedula',
    estado ENUM('pendiente','pre_activo','activo','suspendido','retiro_voluntario','excluido','fallecido') DEFAULT 'pendiente' COMMENT 'Estado actual del socio en el ciclo de vida',
    fecha_ingreso DATE NOT NULL COMMENT 'Fecha de solicitud de ingreso',
    fecha_aprobacion DATE COMMENT 'Fecha de aprobación por la Asamblea',
    numero_acta_aprobacion VARCHAR(20) COMMENT 'Número de acta de la Asamblea que aprobó el ingreso',
    acta_aprobacion_pdf VARCHAR(255) COMMENT 'Archivo PDF del acta de aprobación',
    observaciones TEXT COMMENT 'Observaciones generales del socio',
    fecha_retiro DATE COMMENT 'Fecha de retiro voluntario',
    motivo_retiro VARCHAR(300) COMMENT 'Motivo del retiro voluntario',
    fecha_exclusion DATE COMMENT 'Fecha de exclusión (Art.14 Estatuto)',
    motivo_exclusion VARCHAR(300) COMMENT 'Motivo de la exclusión',
    menor_edad BOOLEAN DEFAULT FALSE COMMENT 'Indica si el socio es menor de edad',
    representante_nombres VARCHAR(100) COMMENT 'Nombres del representante legal (menores de edad)',
    representante_cedula VARCHAR(10) COMMENT 'Cédula del representante legal',
    representante_telefono VARCHAR(15) COMMENT 'Teléfono del representante legal',
    representante_correo VARCHAR(100) COMMENT 'Correo del representante legal',
    representante_documento_pdf VARCHAR(255) COMMENT 'Documento legal del representante (PDF)',
    hash_integridad VARCHAR(64) COMMENT 'SHA-256 de integridad del registro',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
    INDEX idx_socios_cedula (cedula),
    INDEX idx_socios_correo (correo_electronico),
    INDEX idx_socios_estado (estado),
    INDEX idx_socios_apellidos (apellido1, apellido2),
    INDEX idx_socios_nombres (nombre1, nombre2)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de socios de la Caja de Ahorro con datos personales, estado y representación';

CREATE TABLE sesiones_mensuales (
    id_sesion CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la sesión mensual (UUID)',
    numero_sesion INT NOT NULL COMMENT 'Número correlativo de la sesión mensual',
    fecha DATE NOT NULL COMMENT 'Fecha de la sesión mensual',
    titulo VARCHAR(100) COMMENT 'Título o nombre de la sesión',
    estado ENUM('abierta','cerrada') DEFAULT 'abierta' COMMENT 'Estado de la sesión: abierta (en curso) o cerrada (finalizada)',
    fecha_apertura DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de apertura de la sesión',
    fecha_cierre DATETIME NULL COMMENT 'Fecha y hora de cierre de la sesión',
    usuario_cierre CHAR(36) COMMENT 'Usuario que ejecutó el cierre de sesión',
    acta_cierre_pdf VARCHAR(255) COMMENT 'Archivo PDF del acta de cierre',
    total_recaudado DECIMAL(12,2) DEFAULT 0 COMMENT 'Total recaudado en la sesión',
    total_desembolsado DECIMAL(12,2) DEFAULT 0 COMMENT 'Total desembolsado en la sesión',
    saldo_caja DECIMAL(12,2) DEFAULT 0 COMMENT 'Saldo final de caja (recaudado - desembolsado)',
    FOREIGN KEY (usuario_cierre) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesiones mensuales de cobro y check-in — núcleo operativo del sistema';

CREATE TABLE asistencias (
    id_asistencia CHAR(36) PRIMARY KEY COMMENT 'Identificador único del registro de asistencia (UUID)',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio que asiste',
    id_sesion CHAR(36) NOT NULL COMMENT 'FK a la sesión mensual',
    tipo ENUM('a_tiempo','retraso_10min','retraso_30min','falta') NOT NULL COMMENT 'Tipo de asistencia registrada',
    justificacion TEXT COMMENT 'Justificación presentada por el socio (opcional)',
    justificacion_pdf VARCHAR(255) COMMENT 'Archivo PDF de la justificacion',
    justificacion_aprobada BOOLEAN DEFAULT FALSE COMMENT 'Indica si la justificacion fue aprobada',
    usuario_registra CHAR(36) NOT NULL COMMENT 'Usuario que registró la asistencia',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    FOREIGN KEY (id_sesion) REFERENCES sesiones_mensuales(id_sesion),
    FOREIGN KEY (usuario_registra) REFERENCES usuarios(id_usuario),
    UNIQUE KEY (id_socio, id_sesion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de asistencia a sesiones mensuales con tipo y justificacion';

CREATE TABLE cuentas_ahorro (
    id_cuenta_ahorro CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la cuenta de ahorro (UUID)',
    id_socio CHAR(36) UNIQUE NOT NULL COMMENT 'FK al socio propietario de la cuenta',
    saldo_obligatorio DECIMAL(12,2) DEFAULT 0 COMMENT 'Saldo del aporte obligatorio (USD 10/mes)',
    saldo_excedente DECIMAL(12,2) DEFAULT 0 COMMENT 'Saldo de aportes voluntarios/excedentes',
    saldo_disponible DECIMAL(12,2) DEFAULT 0 COMMENT 'Saldo total disponible para retiro según reglas',
    fecha_ultimo_movimiento DATETIME COMMENT 'Fecha del último movimiento registrado',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuentas de ahorro de los socios — capital separado de inversiones';

CREATE TABLE productos_financieros (
    id_producto CHAR(36) PRIMARY KEY COMMENT 'Identificador único del producto financiero (UUID)',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del producto (ej: Crédito Ordinario, Inversión 6 Meses)',
    tipo ENUM('crédito','inversión') NOT NULL COMMENT 'Tipo de producto: crédito o inversión',
    tasa_interes_anual DECIMAL(5,2) NOT NULL DEFAULT 6.00 COMMENT 'Tasa de interes anual en porcentaje',
    metodo_interes ENUM('simple','francés','alemán') DEFAULT 'simple' COMMENT 'Método de cálculo de intereses',
    plazo_min_meses INT NOT NULL COMMENT 'Plazo mínimo en meses',
    plazo_max_meses INT NOT NULL COMMENT 'Plazo máximo en meses',
    monto_min DECIMAL(10,2) NOT NULL COMMENT 'Monto mínimo del producto',
    monto_max DECIMAL(10,2) NOT NULL COMMENT 'Monto maximo del producto',
    dias_gracia INT DEFAULT 0 COMMENT 'Meses de gracia antes de la primera cuota de credito',
    requiere_garante BOOLEAN DEFAULT FALSE COMMENT 'Indica si el producto requiere garante',
    penalidad_retiro_anticipado DECIMAL(5,2) DEFAULT 0 COMMENT 'Penalidad por retiro anticipado (%)',
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si el producto está activo para nuevas solicitudes',
    condiciones_html TEXT COMMENT 'Condiciones generales del credito en HTML (WYSIWYG)',
    min_permanencia_meses INT DEFAULT 0 COMMENT 'Minimo de permanencia como socio activo (meses)',
    min_ahorro DECIMAL(10,2) DEFAULT 0 COMMENT 'Minimo de ahorro acumulado requerido',
    es_emergente BOOLEAN DEFAULT FALSE COMMENT 'Si es credito emergente (no requiere sesion de aprobacion)',
    monto_max_emergente DECIMAL(10,2) DEFAULT 0 COMMENT 'Monto maximo para credito emergente',
    requiere_documento_firmado BOOLEAN DEFAULT TRUE COMMENT 'Si requiere documento firmado escaneado antes del desembolso',
    min_ahorro_unidad VARCHAR(20) DEFAULT 'dolares' COMMENT 'Unidad del monto minimo de ahorro: dolares o porcentaje',
    min_destino_caracteres INT DEFAULT 0 COMMENT 'Minimo de caracteres para destino del credito',
    min_permanencia_valor INT DEFAULT 0 COMMENT 'Valor minimo de permanencia',
    min_permanencia_unidad VARCHAR(10) DEFAULT 'meses' COMMENT 'Unidad de permanencia: dias, meses, anios',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del producto',
    INDEX idx_productos_tipo (tipo),
    INDEX idx_productos_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de productos financieros parametrizables por el Analista Financiero';

CREATE TABLE creditos (
    id_credito CHAR(36) PRIMARY KEY COMMENT 'Identificador único del crédito (UUID)',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio solicitante',
    id_producto CHAR(36) NOT NULL COMMENT 'FK al producto financiero asociado',
    id_sesion_aprobacion CHAR(36) COMMENT 'FK a la sesión donde se aprobó el crédito',
    monto_solicitado DECIMAL(12,2) NOT NULL COMMENT 'Monto solicitado por el socio',
    monto_aprobado DECIMAL(12,2) COMMENT 'Monto aprobado por la Asamblea',
    plazo_meses INT NOT NULL COMMENT 'Plazo del crédito en meses',
    tasa_interes DECIMAL(5,2) NOT NULL COMMENT 'Tasa de interes anual aplicada',
    metodo_interes ENUM('simple','francés','alemán') NOT NULL COMMENT 'Método de interes aplicado a este crédito',
    destino TEXT COMMENT 'Destino o propósito del crédito',
    estado ENUM('ingresado','pendiente','aprobado','legalizado','desembolsado','rechazado','cancelado') DEFAULT 'ingresado' COMMENT 'Estado actual de la solicitud de credito',
    justificacion TEXT COMMENT 'Justificacion de rechazo o puesta en espera',
    acta_aprobacion_pdf VARCHAR(255) COMMENT 'Archivo PDF del acta de aprobación',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud del crédito',
    fecha_aprobacion DATETIME COMMENT 'Fecha de aprobación',
    fecha_desembolso DATETIME COMMENT 'Fecha de desembolso del crédito',
    usuario_aprueba CHAR(36) COMMENT 'Usuario que aprobó el crédito',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    FOREIGN KEY (id_producto) REFERENCES productos_financieros(id_producto),
    FOREIGN KEY (id_sesion_aprobacion) REFERENCES sesiones_mensuales(id_sesion),
    FOREIGN KEY (usuario_aprueba) REFERENCES usuarios(id_usuario),
    INDEX idx_creditos_estado (estado),
    INDEX idx_creditos_socio (id_socio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes y desembolsos de creditos de los socios';

CREATE TABLE amortizaciones (
    id_amortizacion CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la amortización (UUID)',
    id_credito CHAR(36) NOT NULL COMMENT 'FK al crédito asociado',
    numero_cuota INT NOT NULL COMMENT 'Número de cuota (1, 2, 3...)',
    fecha_vencimiento DATE NOT NULL COMMENT 'Fecha de vencimiento de la cuota',
    capital DECIMAL(12,2) NOT NULL COMMENT 'Porción de capital de la cuota',
    interes DECIMAL(12,2) NOT NULL COMMENT 'Porción de interes de la cuota',
    total DECIMAL(12,2) NOT NULL COMMENT 'Total de la cuota (capital + interes)',
    saldo_restante DECIMAL(12,2) NOT NULL COMMENT 'Saldo de capital pendiente después de esta cuota',
    estado ENUM('pendiente','pagada','vencida') DEFAULT 'pendiente' COMMENT 'Estado de la cuota',
    id_cobro CHAR(36) COMMENT 'FK al cobro cuando la cuota es pagada',
    INDEX idx_amortizaciones_crédito (id_credito),
    INDEX idx_amortizaciones_estado (estado),
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de amortización de creditos — cuotas generadas según método de interes';

CREATE TABLE inversiones (
    id_inversion CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la inversión (UUID)',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio inversionista',
    id_producto CHAR(36) NOT NULL COMMENT 'FK al producto de inversión',
    monto DECIMAL(12,2) NOT NULL COMMENT 'Monto invertido',
    plazo_meses INT NOT NULL COMMENT 'Plazo de la inversión en meses',
    tasa_interes DECIMAL(5,2) NOT NULL COMMENT 'Tasa de interes anual aplicada',
    fecha_inicio DATE NOT NULL COMMENT 'Fecha de inicio de la inversión',
    fecha_vencimiento DATE NOT NULL COMMENT 'Fecha de vencimiento',
    rendimiento_proyectado DECIMAL(12,2) COMMENT 'Rendimiento proyectado al vencimiento',
    destino_final ENUM('capital_inversion','efectivo','transferencia') DEFAULT 'capital_inversion' COMMENT 'Destino de los fondos al vencimiento',
    estado ENUM('pendiente','activa','vencida','retiro_anticipado','cancelada','rechazada') DEFAULT 'pendiente' COMMENT 'Estado actual de la inversión',
    notificado_devolucion BOOLEAN DEFAULT FALSE COMMENT 'Indica si se notificó la próxima devolución',
    contrato_pdf VARCHAR(255) COMMENT 'Archivo PDF del contrato de inversión',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro de la inversión',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    FOREIGN KEY (id_producto) REFERENCES productos_financieros(id_producto),
    INDEX idx_inversiones_estado (estado),
    INDEX idx_inversiones_socio (id_socio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inversiones a plazo fijo de los socios — capital separado de cuenta de ahorro';

CREATE TABLE cobros (
    id_cobro CHAR(36) PRIMARY KEY COMMENT 'Identificador único del cobro (UUID)',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio que realiza el pago',
    id_sesion CHAR(36) NULL COMMENT 'FK a la sesión mensual donde se registra el cobro',
    tipo ENUM('aporte_obligatorio','aporte_excedente','cuota_credito','multa','inversion','interes','desembolso','otro','deposito_capital_inversion','retiro_inversion') NOT NULL COMMENT 'Tipo de cobro',
    id_referencia CHAR(36) COMMENT 'ID de referencia según el tipo (id_amortizacion, id_multa, etc.)',
    monto DECIMAL(12,2) NOT NULL COMMENT 'Monto cobrado',
    medio_pago ENUM('efectivo','transferencia','compensación','digital') NOT NULL COMMENT 'Medio de pago utilizado',
    comprobante_pdf VARCHAR(255) COMMENT 'Archivo PDF del comprobante de pago',
    hash_integridad VARCHAR(64) COMMENT 'SHA-256 de integridad del registro',
    usuario_registra CHAR(36) NOT NULL COMMENT 'Usuario que registró el cobro',
    anulado BOOLEAN DEFAULT FALSE COMMENT 'Indica si el cobro fue anulado',
    motivo_anulacion VARCHAR(255) COMMENT 'Motivo de la anulación',
    fecha_anulacion DATETIME COMMENT 'Fecha de anulación',
    usuario_anula CHAR(36) COMMENT 'Usuario que anuló el cobro',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro del cobro',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    FOREIGN KEY (id_sesion) REFERENCES sesiones_mensuales(id_sesion),
    FOREIGN KEY (usuario_registra) REFERENCES usuarios(id_usuario),
    INDEX idx_cobros_socio (id_socio),
    INDEX idx_cobros_tipo (tipo),
    INDEX idx_cobros_sesión (id_sesion),
    INDEX idx_cobros_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de cobros — transacciones financieras diarias';

CREATE TABLE multas (
    id_multa CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la multa (UUID)',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio multado',
    id_sesion CHAR(36) NOT NULL COMMENT 'FK a la sesión donde se generó la multa',
    tipo ENUM('retraso_10min','retraso_30min','inasistencia','mora_credito','cuota_impaga','otro') NOT NULL COMMENT 'Tipo de multa',
    monto DECIMAL(10,2) NOT NULL COMMENT 'Monto de la multa en USD',
    justificacion TEXT COMMENT 'Justificación presentada por el socio',
    justificacion_aprobada BOOLEAN DEFAULT FALSE COMMENT 'Indica si la justificacion fue aprobada',
    justificacion_pdf VARCHAR(255) COMMENT 'Archivo PDF de la justificacion',
    estado ENUM('activa','impugnada','anulada') DEFAULT 'activa' COMMENT 'Estado de la multa: activa, impugnada (justificacion aceptada) o anulada',
    pagada BOOLEAN DEFAULT FALSE COMMENT 'Indica si la multa fue pagada',
    fecha_pago DATETIME COMMENT 'Fecha de pago de la multa',
    id_cobro CHAR(36) COMMENT 'FK al cobro cuando la multa es pagada',
    fecha_generacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de generación de la multa',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    FOREIGN KEY (id_sesion) REFERENCES sesiones_mensuales(id_sesion),
    FOREIGN KEY (id_cobro) REFERENCES cobros(id_cobro),
    INDEX idx_multas_socio (id_socio),
    INDEX idx_multas_pagada (pagada)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multas generadas por inasistencia, retraso o mora — base legal Art.11 Estatuto';

CREATE TABLE historial_operaciones (
    id_operacion CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la operación (UUID)',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio asociado a la operación',
    tipo_operacion ENUM('aporte_obligatorio','aporte_excedente','retiro_ahorro','desembolso_credito','pago_cuota','pago_multa','inversion_apertura','inversion_retiro','interes_ganado','interes_pagado','cierre_sesion','anulacion','deposito_capital_inversion') NOT NULL COMMENT 'Tipo de operación financiera',
    monto DECIMAL(12,2) NOT NULL COMMENT 'Monto de la operación',
    saldo_anterior DECIMAL(12,2) COMMENT 'Saldo anterior a la operación',
    saldo_posterior DECIMAL(12,2) COMMENT 'Saldo posterior a la operación',
    id_referencia CHAR(36) COMMENT 'ID de referencia a la entidad origen',
    id_sesion CHAR(36) COMMENT 'FK a la sesión mensual',
    id_usuario_registra CHAR(36) COMMENT 'Usuario que registró la operación',
    comprobante_pdf VARCHAR(255) COMMENT 'Archivo PDF del comprobante',
    hash_integridad VARCHAR(64) COMMENT 'SHA-256 de integridad del registro (inmodificable)',
    ip_registro VARCHAR(45) COMMENT 'Dirección IP desde donde se registró la operación',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    FOREIGN KEY (id_sesion) REFERENCES sesiones_mensuales(id_sesion),
    FOREIGN KEY (id_usuario_registra) REFERENCES usuarios(id_usuario),
    INDEX idx_historial_socio (id_socio),
    INDEX idx_historial_tipo (tipo_operacion),
    INDEX idx_historial_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial inmodificable de operaciones financieras — solo inserción, sin DELETE/UPDATE';

CREATE TABLE notificaciones (
    id_notificacion CHAR(36) PRIMARY KEY COMMENT 'Identificador único de la notificación (UUID)',
    id_usuario CHAR(36) COMMENT 'FK al usuario destinatario (si es administrativo)',
    id_socio CHAR(36) COMMENT 'FK al socio destinatario (si es socio)',
    tipo VARCHAR(50) NOT NULL COMMENT 'Tipo de notificación (ej: cobro, crédito, multa)',
    titulo VARCHAR(200) NOT NULL COMMENT 'Título de la notificación',
    mensaje TEXT NOT NULL COMMENT 'Cuerpo del mensaje',
    leida BOOLEAN DEFAULT FALSE COMMENT 'Indica si el destinatario leyó la notificación',
    buzon ENUM('entrada','archivadas','papelera') DEFAULT 'entrada' COMMENT 'Buzon donde se encuentra la notificación',
    enviada_pusher BOOLEAN DEFAULT FALSE COMMENT 'Indica si ya se envió por Pusher',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación de la notificación',
    fecha_lectura DATETIME COMMENT 'Fecha en que se leyó la notificación',
    fecha_eliminacion DATETIME COMMENT 'Fecha en que se movió a la papelera',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio),
    INDEX idx_notificaciones_usuario (id_usuario),
    INDEX idx_notificaciones_socio (id_socio),
    INDEX idx_notificaciones_leida (leida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Buzón de notificaciones persistido en BD + envío en tiempo real por Pusher';

CREATE TABLE parametros (
    id_parametro INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador numérico del parámetro',
    codigo VARCHAR(50) UNIQUE NOT NULL COMMENT 'Código único del parámetro (ej: tasa_interes_crédito)',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo del parámetro',
    valor VARCHAR(255) NOT NULL COMMENT 'Valor del parámetro',
    tipo ENUM('texto','número','decimal','booleano','color') DEFAULT 'texto' COMMENT 'Tipo de dato del valor',
    modulo ENUM('general','financiero','seguridad','imagen') DEFAULT 'general' COMMENT 'Módulo al que pertenece el parámetro',
    editable BOOLEAN DEFAULT TRUE COMMENT 'Indica si el parámetro puede ser editado desde el panel'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Parámetros configurables del sistema';

CREATE TABLE provincias (
    id_provincia INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador numérico de la provincia',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre de la provincia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de provincias del Ecuador';

CREATE TABLE cantones (
    id_canton INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador numérico del cantón',
    id_provincia INT NOT NULL COMMENT 'FK a la provincia',
    nombre VARCHAR(100) NOT NULL COMMENT 'Nombre del cantón',
    FOREIGN KEY (id_provincia) REFERENCES provincias(id_provincia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de cantones por provincia';

CREATE TABLE catastro_entidades_publicas (
    id_entidad INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador numérico de la entidad',
    ruc VARCHAR(13) NOT NULL COMMENT 'RUC de la entidad pública',
    razon_social VARCHAR(200) NOT NULL COMMENT 'Razón social de la entidad'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catastro de entidades públicas para registro de socios';

CREATE TABLE garantes (
    id_garante CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID del garante',
    id_credito CHAR(36) NOT NULL COMMENT 'FK al crédito',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio garante',
    tipo_garante ENUM('fiador_solidario','prendario','hipotecario') DEFAULT 'fiador_solidario' COMMENT 'Tipo de garantía',
    monto_garantizado DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT 'Monto garantizado',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
    FOREIGN KEY (id_credito) REFERENCES creditos(id_credito) ON DELETE CASCADE,
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Garantes de creditos';

CREATE TABLE archivos (
    id_archivo CHAR(36) PRIMARY KEY COMMENT 'Identificador único del archivo (UUID)',
    nombre_original VARCHAR(255) NOT NULL COMMENT 'Nombre original del archivo subido',
    nombre_archivo VARCHAR(255) NOT NULL COMMENT 'Nombre interno en disco (UUID + extension)',
    mime_type VARCHAR(100) NOT NULL COMMENT 'Tipo MIME del archivo',
    tamano BIGINT NOT NULL COMMENT 'Tamaño en bytes',
    extension VARCHAR(10) NOT NULL COMMENT 'Extensión del archivo (pdf, jpg, png, etc)',
    ruta VARCHAR(500) NOT NULL COMMENT 'Ruta relativa desde storage/archivos/',
    hash_sha256 VARCHAR(64) NOT NULL COMMENT 'SHA-256 del contenido del archivo',
    entidad_tipo VARCHAR(50) COMMENT 'Nombre de la tabla o modulo asociado (socio, credito, multa, etc)',
    entidad_id CHAR(36) COMMENT 'UUID del registro asociado en la entidad',
    subdirectorio VARCHAR(100) DEFAULT 'general' COMMENT 'Subdirectorio dentro de storage/archivos/',
    id_usuario_subio CHAR(36) COMMENT 'Usuario que subió el archivo',
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de subida del archivo',
    FOREIGN KEY (id_usuario_subio) REFERENCES usuarios(id_usuario),
    INDEX idx_archivos_entidad (entidad_tipo, entidad_id),
    INDEX idx_archivos_hash (hash_sha256)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión centralizada de archivos — metadatos en BD, archivos fuera del public root';

CREATE TABLE solicitudes_retiro (
    id_solicitud CHAR(36) NOT NULL PRIMARY KEY COMMENT 'UUID de la solicitud',
    id_socio CHAR(36) NOT NULL COMMENT 'FK al socio solicitante',
    monto DECIMAL(12,2) NOT NULL COMMENT 'Monto solicitado',
    motivo TEXT COMMENT 'Motivo del retiro',
    estado ENUM('pendiente','aprobado','rechazado') DEFAULT 'pendiente' COMMENT 'Estado de la solicitud',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud',
    fecha_respuesta DATETIME COMMENT 'Fecha de aprobación/rechazo',
    usuario_respuesta CHAR(36) COMMENT 'Usuario que aprobó/rechazó',
    id_cobro CHAR(36) COMMENT 'Cobro generado al aprobar',
    FOREIGN KEY (id_socio) REFERENCES socios(id_socio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de retiro de ahorro';
