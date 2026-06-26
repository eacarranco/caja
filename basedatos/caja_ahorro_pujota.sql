
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `amortizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `amortizaciones` (
  `id_amortizacion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la amortización (UUID)',
  `id_credito` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al crédito asociado',
  `numero_cuota` int NOT NULL COMMENT 'Número de cuota (1, 2, 3...)',
  `fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento de la cuota',
  `capital` decimal(12,2) NOT NULL COMMENT 'Porción de capital de la cuota',
  `interes` decimal(12,2) NOT NULL COMMENT 'Porción de interés de la cuota',
  `total` decimal(12,2) NOT NULL COMMENT 'Total de la cuota (capital + interés)',
  `saldo_restante` decimal(12,2) NOT NULL COMMENT 'Saldo de capital pendiente después de esta cuota',
  `estado` enum('pendiente','pagada','vencida') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado de la cuota',
  `id_cobro` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro cuando la cuota es pagada',
  PRIMARY KEY (`id_amortizacion`),
  KEY `idx_amortizaciones_crédito` (`id_credito`),
  KEY `idx_amortizaciones_estado` (`estado`),
  CONSTRAINT `amortizaciones_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id_credito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de amortización de créditos — cuotas generadas según método de interés';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `amortizaciones` WRITE;
/*!40000 ALTER TABLE `amortizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `amortizaciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos` (
  `id_archivo` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del archivo (UUID)',
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre original del archivo subido',
  `nombre_archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre interno en disco (UUID + extensión)',
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo MIME del archivo',
  `tamano` bigint NOT NULL COMMENT 'Tamaño en bytes',
  `extension` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Extensión del archivo (pdf, jpg, png, etc)',
  `ruta` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ruta relativa desde storage/archivos/',
  `hash_sha256` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 del contenido del archivo',
  `entidad_tipo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de la tabla o módulo asociado (socio, credito, multa, etc)',
  `entidad_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UUID del registro asociado en la entidad',
  `subdirectorio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'Subdirectorio dentro de storage/archivos/',
  `id_usuario_subio` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que subió el archivo',
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de subida del archivo',
  PRIMARY KEY (`id_archivo`),
  KEY `id_usuario_subio` (`id_usuario_subio`),
  KEY `idx_archivos_entidad` (`entidad_tipo`,`entidad_id`),
  KEY `idx_archivos_hash` (`hash_sha256`),
  CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_usuario_subio`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión centralizada de archivos ? metadatos en BD, archivos fuera del public root';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `archivos` WRITE;
/*!40000 ALTER TABLE `archivos` DISABLE KEYS */;
INSERT INTO `archivos` (`id_archivo`, `nombre_original`, `nombre_archivo`, `mime_type`, `tamano`, `extension`, `ruta`, `hash_sha256`, `entidad_tipo`, `entidad_id`, `subdirectorio`, `id_usuario_subio`, `fecha_subida`) VALUES ('ca62b9e0-de01-42cc-9bb6-0826f49dce00','LogoCorteNacJusticia.jpg','ca62b9e0-de01-42cc-9bb6-0826f49dce00.jpg','image/jpeg',11497,'jpg','imagen/ca62b9e0-de01-42cc-9bb6-0826f49dce00.jpg','f324df2dada0b396fb8cc06c9868076755bb36984618eb1fde1eb207b623da78','imagen','logo_sidebar','imagen','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','2026-06-06 23:34:48'),('d9433f2e-ffa1-48c9-bf86-b338e6796ff2','LogoCorteNacJusticia.jpg','d9433f2e-ffa1-48c9-bf86-b338e6796ff2.jpg','image/jpeg',11497,'jpg','imagen/d9433f2e-ffa1-48c9-bf86-b338e6796ff2.jpg','f324df2dada0b396fb8cc06c9868076755bb36984618eb1fde1eb207b623da78','imagen','logo_sd','imagen','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','2026-06-06 23:36:19');
/*!40000 ALTER TABLE `archivos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias` (
  `id_asistencia` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del registro de asistencia (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio que asiste',
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesión mensual',
  `tipo` enum('a_tiempo','retraso_10min','retraso_30min','falta') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de asistencia registrada',
  `justificacion` text COLLATE utf8mb4_unicode_ci COMMENT 'Justificación presentada por el socio (opcional)',
  `justificacion_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificación',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificación fue aprobada',
  `usuario_registra` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que registró la asistencia',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `id_socio` (`id_socio`,`id_sesion`),
  KEY `id_sesión` (`id_sesion`),
  KEY `usuario_registra` (`usuario_registra`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de asistencia a sesiones mensuales con tipo y justificación';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `caja_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caja_movimientos` (
  `id_movimiento` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador unico del movimiento (UUID)',
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesion donde ocurrio',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al socio relacionado',
  `id_referencia` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro, credito, inversion, etc',
  `tipo_movimiento` enum('ingreso','egreso') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ingreso o egreso',
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Concepto descriptivo de la operacion',
  `categoria` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Categoria: aporte_obligatorio, multa, desembolso, etc',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto del movimiento',
  `saldo_anterior` decimal(12,2) NOT NULL COMMENT 'Saldo antes del movimiento',
  `saldo_posterior` decimal(12,2) NOT NULL COMMENT 'Saldo despues del movimiento',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_movimiento`),
  KEY `idx_fecha` (`fecha_registro`),
  KEY `idx_categoria` (`categoria`),
  KEY `idx_sesion` (`id_sesion`),
  KEY `idx_referencia` (`id_referencia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Libro mayor de la Caja - estado de cuenta centralizado';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `caja_movimientos` WRITE;
/*!40000 ALTER TABLE `caja_movimientos` DISABLE KEYS */;
INSERT INTO `caja_movimientos` (`id_movimiento`, `id_sesion`, `id_socio`, `id_referencia`, `tipo_movimiento`, `concepto`, `categoria`, `monto`, `saldo_anterior`, `saldo_posterior`, `fecha_registro`) VALUES ('ab3c0374-9a6b-475d-a952-330b5d8ae838',NULL,'6819f961-b144-4c96-bbbd-8a0c0055cce1','2fdd0b75-0f3f-4507-8eab-b5cd4ce96cac','ingreso','Deposito capital inversion - CARRANCO  GAVINO ','deposito_capital_inversion',1000.00,3000.00,4000.00,'2026-06-20 17:07:50'),('fc525fc0-988c-4ae4-a04d-1333c743f9a1',NULL,'6819f961-b144-4c96-bbbd-8a0c0055cce1','91323767-bc89-4070-ac7a-8bb40ee43e48','ingreso','Deposito capital inversion - CARRANCO  GAVINO ','deposito_capital_inversion',3000.00,0.00,3000.00,'2026-06-20 17:07:27');
/*!40000 ALTER TABLE `caja_movimientos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cantones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cantones` (
  `id_canton` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del cantón',
  `id_provincia` int NOT NULL COMMENT 'FK a la provincia',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del cantón',
  PRIMARY KEY (`id_canton`),
  KEY `id_provincia` (`id_provincia`),
  CONSTRAINT `cantones_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `provincias` (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de cantones por provincia';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cantones` WRITE;
/*!40000 ALTER TABLE `cantones` DISABLE KEYS */;
INSERT INTO `cantones` (`id_canton`, `id_provincia`, `nombre`) VALUES (1,1,'Pedro Moncayo');
/*!40000 ALTER TABLE `cantones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `capital_inversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `capital_inversion` (
  `id_capital_inversion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador unico del registro de capital de inversion (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio',
  `saldo` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo disponible para invertir',
  `fecha_ultimo_movimiento` datetime DEFAULT NULL COMMENT 'Fecha del ultimo movimiento',
  PRIMARY KEY (`id_capital_inversion`),
  UNIQUE KEY `id_socio` (`id_socio`),
  CONSTRAINT `capital_inversion_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Capital de inversion del socio - independiente de la cuenta de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `capital_inversion` WRITE;
/*!40000 ALTER TABLE `capital_inversion` DISABLE KEYS */;
INSERT INTO `capital_inversion` (`id_capital_inversion`, `id_socio`, `saldo`, `fecha_ultimo_movimiento`) VALUES ('cec40bff-9ce5-4c0b-ae6b-68f6128d5aae','6819f961-b144-4c96-bbbd-8a0c0055cce1',0.00,'2026-06-20 17:11:24');
/*!40000 ALTER TABLE `capital_inversion` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `catastro_entidades_publicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catastro_entidades_publicas` (
  `id_entidad` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico de la entidad',
  `ruc` varchar(13) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC de la entidad pública',
  `razon_social` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Razón social de la entidad',
  PRIMARY KEY (`id_entidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catastro de entidades públicas para registro de socios';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `catastro_entidades_publicas` WRITE;
/*!40000 ALTER TABLE `catastro_entidades_publicas` DISABLE KEYS */;
/*!40000 ALTER TABLE `catastro_entidades_publicas` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cobros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cobros` (
  `id_cobro` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del cobro (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio que realiza el pago',
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesión mensual donde se registra el cobro',
  `tipo` enum('aporte_obligatorio','aporte_excedente','cuota_credito','multa','inversion','interes','desembolso','otro','deposito_capital_inversion','retiro_inversion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_referencia` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID de referencia según el tipo (id_amortización, id_multa, etc.)',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto cobrado',
  `medio_pago` enum('efectivo','transferencia','compensacion','digital') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del comprobante de pago',
  `hash_integridad` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro',
  `usuario_registra` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que registró el cobro',
  `anulado` tinyint(1) DEFAULT '0' COMMENT 'Indica si el cobro fue anulado',
  `motivo_anulacion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de la anulación',
  `fecha_anulacion` datetime DEFAULT NULL COMMENT 'Fecha de anulación',
  `usuario_anula` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que anuló el cobro',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro del cobro',
  PRIMARY KEY (`id_cobro`),
  KEY `usuario_registra` (`usuario_registra`),
  KEY `idx_cobros_socio` (`id_socio`),
  KEY `idx_cobros_tipo` (`tipo`),
  KEY `idx_cobros_sesión` (`id_sesion`),
  KEY `idx_cobros_fecha` (`fecha_registro`),
  CONSTRAINT `cobros_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `cobros_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `cobros_ibfk_3` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de cobros — transacciones financieras diarias';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cobros` WRITE;
/*!40000 ALTER TABLE `cobros` DISABLE KEYS */;
INSERT INTO `cobros` (`id_cobro`, `id_socio`, `id_sesion`, `tipo`, `id_referencia`, `monto`, `medio_pago`, `comprobante_pdf`, `hash_integridad`, `usuario_registra`, `anulado`, `motivo_anulacion`, `fecha_anulacion`, `usuario_anula`, `fecha_registro`) VALUES ('2fdd0b75-0f3f-4507-8eab-b5cd4ce96cac','6819f961-b144-4c96-bbbd-8a0c0055cce1',NULL,'deposito_capital_inversion',NULL,1000.00,'efectivo',NULL,'3667804fcb1987de08e53848da4fd8a3823dc286413922676aba000d80ae8f7f','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-20 17:07:49'),('91323767-bc89-4070-ac7a-8bb40ee43e48','6819f961-b144-4c96-bbbd-8a0c0055cce1',NULL,'deposito_capital_inversion',NULL,3000.00,'efectivo',NULL,'62fa38b43ef7afbc10fa9619ffc1a3f552f6c79e970dbee90e4b0c14eff0922e','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-20 17:07:26');
/*!40000 ALTER TABLE `cobros` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `creditos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `creditos` (
  `id_credito` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del crédito (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio solicitante',
  `id_producto` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al producto financiero asociado',
  `id_sesion_aprobacion` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesión donde se aprobó el crédito',
  `monto_solicitado` decimal(12,2) NOT NULL COMMENT 'Monto solicitado por el socio',
  `monto_aprobado` decimal(12,2) DEFAULT NULL COMMENT 'Monto aprobado por la Asamblea',
  `plazo_meses` int NOT NULL COMMENT 'Plazo del crédito en meses',
  `tasa_interes` decimal(5,2) NOT NULL COMMENT 'Tasa de interés anual aplicada',
  `metodo_interes` enum('simple','frances','aleman') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Metodo de interes aplicado a este credito',
  `destino` text COLLATE utf8mb4_unicode_ci COMMENT 'Destino o propósito del crédito',
  `estado` enum('ingresado','pendiente','aprobado','legalizado','desembolsado','rechazado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ingresado' COMMENT 'Estado actual de la solicitud de credito',
  `acta_aprobacion_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de aprobación',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud del crédito',
  `fecha_aprobacion` datetime DEFAULT NULL COMMENT 'Fecha de aprobación',
  `fecha_desembolso` datetime DEFAULT NULL COMMENT 'Fecha de desembolso del crédito',
  `usuario_aprueba` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que aprobó el crédito',
  `justificacion` text COLLATE utf8mb4_unicode_ci COMMENT 'Justificacion de rechazo o puesta en espera',
  PRIMARY KEY (`id_credito`),
  KEY `id_producto` (`id_producto`),
  KEY `id_sesión_aprobación` (`id_sesion_aprobacion`),
  KEY `usuario_aprueba` (`usuario_aprueba`),
  KEY `idx_créditos_estado` (`estado`),
  KEY `idx_créditos_socio` (`id_socio`),
  CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `creditos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_financieros` (`id_producto`),
  CONSTRAINT `creditos_ibfk_3` FOREIGN KEY (`id_sesion_aprobacion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `creditos_ibfk_4` FOREIGN KEY (`usuario_aprueba`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes y desembolsos de créditos de los socios';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `creditos` WRITE;
/*!40000 ALTER TABLE `creditos` DISABLE KEYS */;
/*!40000 ALTER TABLE `creditos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cuentas_ahorro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_ahorro` (
  `id_cuenta_ahorro` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la cuenta de ahorro (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio propietario de la cuenta',
  `saldo_obligatorio` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo del aporte obligatorio (USD 10/mes)',
  `saldo_excedente` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo de aportes voluntarios/excedentes',
  `saldo_disponible` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo total disponible para retiro según reglas',
  `fecha_ultimo_movimiento` datetime DEFAULT NULL COMMENT 'Fecha del último movimiento registrado',
  PRIMARY KEY (`id_cuenta_ahorro`),
  UNIQUE KEY `id_socio` (`id_socio`),
  CONSTRAINT `cuentas_ahorro_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuentas de ahorro de los socios — capital separado de inversiones';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cuentas_ahorro` WRITE;
/*!40000 ALTER TABLE `cuentas_ahorro` DISABLE KEYS */;
INSERT INTO `cuentas_ahorro` (`id_cuenta_ahorro`, `id_socio`, `saldo_obligatorio`, `saldo_excedente`, `saldo_disponible`, `fecha_ultimo_movimiento`) VALUES ('d3496eee-37e8-46b2-8bbf-c4180a07f54d','6819f961-b144-4c96-bbbd-8a0c0055cce1',0.00,0.00,0.00,NULL);
/*!40000 ALTER TABLE `cuentas_ahorro` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `garantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `garantes` (
  `id_garante` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID del garante',
  `id_credito` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al crédito',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio garante',
  `tipo_garante` enum('fiador_solidario','prendario','hipotecario') COLLATE utf8mb4_unicode_ci DEFAULT 'fiador_solidario' COMMENT 'Tipo de garantía',
  `monto_garantizado` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto garantizado',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
  PRIMARY KEY (`id_garante`),
  KEY `id_socio` (`id_socio`),
  KEY `garantes_ibfk_1` (`id_credito`),
  CONSTRAINT `garantes_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id_credito`),
  CONSTRAINT `garantes_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Garantes de créditos';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `garantes` WRITE;
/*!40000 ALTER TABLE `garantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `garantes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `historial_operaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_operaciones` (
  `id_operacion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la operación (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio asociado a la operación',
  `tipo_operacion` enum('aporte_obligatorio','aporte_excedente','retiro_ahorro','desembolso_credito','pago_cuota','pago_multa','inversion_apertura','inversion_retiro','interes_ganado','interes_pagado','cierre_sesion','anulacion','deposito_capital_inversion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto de la operación',
  `saldo_anterior` decimal(12,2) DEFAULT NULL COMMENT 'Saldo anterior a la operación',
  `saldo_posterior` decimal(12,2) DEFAULT NULL COMMENT 'Saldo posterior a la operación',
  `id_referencia` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID de referencia a la entidad origen',
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesión mensual',
  `id_usuario_registra` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que registró la operación',
  `comprobante_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del comprobante',
  `hash_integridad` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro (inmodificable)',
  `ip_registro` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección IP desde donde se registró la operación',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_operacion`),
  KEY `id_sesión` (`id_sesion`),
  KEY `id_usuario_registra` (`id_usuario_registra`),
  KEY `idx_historial_socio` (`id_socio`),
  KEY `idx_historial_tipo` (`tipo_operacion`),
  KEY `idx_historial_fecha` (`fecha_registro`),
  CONSTRAINT `historial_operaciones_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `historial_operaciones_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `historial_operaciones_ibfk_3` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial inmodificable de operaciones financieras — solo inserción, sin DELETE/UPDATE';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `historial_operaciones` WRITE;
/*!40000 ALTER TABLE `historial_operaciones` DISABLE KEYS */;
INSERT INTO `historial_operaciones` (`id_operacion`, `id_socio`, `tipo_operacion`, `monto`, `saldo_anterior`, `saldo_posterior`, `id_referencia`, `id_sesion`, `id_usuario_registra`, `comprobante_pdf`, `hash_integridad`, `ip_registro`, `fecha_registro`) VALUES ('0fa83646-5e09-4494-81bb-a42eead0a559','6819f961-b144-4c96-bbbd-8a0c0055cce1','inversion_apertura',4000.00,NULL,NULL,'20acb1db-b23c-4c61-9487-31ae7e390a14',NULL,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,NULL,'::1','2026-06-20 17:11:24'),('27ea1f9d-412b-4b16-a1ec-35715d77efd1','6819f961-b144-4c96-bbbd-8a0c0055cce1','deposito_capital_inversion',3000.00,NULL,NULL,'91323767-bc89-4070-ac7a-8bb40ee43e48',NULL,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,NULL,'::1','2026-06-20 17:07:26'),('e2a276d8-4347-4126-b28b-c8b6e9296ecd','6819f961-b144-4c96-bbbd-8a0c0055cce1','deposito_capital_inversion',1000.00,NULL,NULL,'2fdd0b75-0f3f-4507-8eab-b5cd4ce96cac',NULL,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,NULL,'::1','2026-06-20 17:07:49');
/*!40000 ALTER TABLE `historial_operaciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `inversiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inversiones` (
  `id_inversion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la inversión (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio inversionista',
  `id_producto` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al producto de inversión',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto invertido',
  `plazo_meses` int NOT NULL COMMENT 'Plazo de la inversión en meses',
  `tasa_interes` decimal(5,2) NOT NULL COMMENT 'Tasa de interés anual aplicada',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio de la inversión',
  `fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento',
  `rendimiento_proyectado` decimal(12,2) DEFAULT NULL COMMENT 'Rendimiento proyectado al vencimiento',
  `destino_final` enum('capital_inversion','efectivo','transferencia') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'capital_inversion',
  `estado` enum('pendiente','activa','vencida','retiro_anticipado','cancelada','rechazada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `notificado_devolucion` tinyint(1) DEFAULT '0' COMMENT 'Indica si se notificó la próxima devolución',
  `contrato_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del contrato de inversión',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro de la inversión',
  PRIMARY KEY (`id_inversion`),
  KEY `id_producto` (`id_producto`),
  KEY `idx_inversiones_estado` (`estado`),
  KEY `idx_inversiones_socio` (`id_socio`),
  CONSTRAINT `inversiones_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `inversiones_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_financieros` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inversiones a plazo fijo de los socios — capital separado de cuenta de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `inversiones` WRITE;
/*!40000 ALTER TABLE `inversiones` DISABLE KEYS */;
INSERT INTO `inversiones` (`id_inversion`, `id_socio`, `id_producto`, `monto`, `plazo_meses`, `tasa_interes`, `fecha_inicio`, `fecha_vencimiento`, `rendimiento_proyectado`, `destino_final`, `estado`, `notificado_devolucion`, `contrato_pdf`, `fecha_registro`) VALUES ('20acb1db-b23c-4c61-9487-31ae7e390a14','6819f961-b144-4c96-bbbd-8a0c0055cce1','2e4c5dbd-afa8-424e-9367-6687ad3c4490',4000.00,3,6.00,'2026-06-20','2026-09-20',60.00,'efectivo','activa',0,NULL,'2026-06-20 17:10:27');
/*!40000 ALTER TABLE `inversiones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `multas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multas` (
  `id_multa` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la multa (UUID)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio multado',
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesión donde se generó la multa',
  `tipo` enum('retraso_10min','retraso_30min','inasistencia','mora_credito','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto de la multa en USD',
  `justificacion` text COLLATE utf8mb4_unicode_ci COMMENT 'Justificación presentada por el socio',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificación fue aprobada',
  `justificacion_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificación',
  `pagada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la multa fue pagada',
  `estado` enum('activa','impugnada','anulada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa' COMMENT 'Estado de la multa',
  `fecha_pago` datetime DEFAULT NULL COMMENT 'Fecha de pago de la multa',
  `id_cobro` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro cuando la multa es pagada',
  `fecha_generacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de generación de la multa',
  PRIMARY KEY (`id_multa`),
  KEY `id_sesión` (`id_sesion`),
  KEY `id_cobro` (`id_cobro`),
  KEY `idx_multas_socio` (`id_socio`),
  KEY `idx_multas_pagada` (`pagada`),
  CONSTRAINT `multas_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `multas_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `multas_ibfk_3` FOREIGN KEY (`id_cobro`) REFERENCES `cobros` (`id_cobro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multas generadas por inasistencia, retraso o mora — base legal Art.11 Estatuto';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `multas` WRITE;
/*!40000 ALTER TABLE `multas` DISABLE KEYS */;
/*!40000 ALTER TABLE `multas` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la notificación (UUID)',
  `id_usuario` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al usuario destinatario (si es administrativo)',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al socio destinatario (si es socio)',
  `tipo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de notificación (ej: cobro, crédito, multa)',
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Título de la notificación',
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cuerpo del mensaje',
  `leida` tinyint(1) DEFAULT '0' COMMENT 'Indica si el destinatario leyó la notificación',
  `buzon` enum('entrada','archivadas','papelera') COLLATE utf8mb4_unicode_ci DEFAULT 'entrada' COMMENT 'Buzon donde se encuentra la notificacion',
  `enviada_pusher` tinyint(1) DEFAULT '0' COMMENT 'Indica si ya se envió por Pusher',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación de la notificación',
  `fecha_lectura` datetime DEFAULT NULL COMMENT 'Fecha en que se leyó la notificación',
  `fecha_eliminacion` datetime DEFAULT NULL COMMENT 'Fecha de eliminacion (movida a papelera)',
  PRIMARY KEY (`id_notificacion`),
  KEY `idx_notificaciones_usuario` (`id_usuario`),
  KEY `idx_notificaciones_socio` (`id_socio`),
  KEY `idx_notificaciones_leída` (`leida`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Buzón de notificaciones persistido en BD + envío en tiempo real por Pusher';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `id_socio`, `tipo`, `titulo`, `mensaje`, `leida`, `buzon`, `enviada_pusher`, `fecha_creacion`, `fecha_lectura`, `fecha_eliminacion`) VALUES ('31395426-4618-4300-ad72-da0344945cb0',NULL,'6819f961-b144-4c96-bbbd-8a0c0055cce1','inversion','Inversion aprobada','Inversion de $4000.00 para CARRANCO  GAVINO  ha sido aprobada',1,'entrada',1,'2026-06-20 17:11:24','2026-06-23 22:47:18',NULL),('44507da8-67c1-4c0c-9f55-a90c5126214d',NULL,'6819f961-b144-4c96-bbbd-8a0c0055cce1','inversion','Deposito a capital de inversion','Deposito de $3000 a capital de inversion de CARRANCO  GAVINO ',1,'entrada',1,'2026-06-20 17:07:26','2026-06-20 17:09:45',NULL),('aca01248-40df-4ad0-b037-2615f5a58eb4',NULL,'6819f961-b144-4c96-bbbd-8a0c0055cce1','inversion','Inversion solicitada','Inversion de $4000 para CARRANCO  GAVINO  ha sido solicitada',1,'entrada',1,'2026-06-20 17:10:27','2026-06-20 17:10:36',NULL),('b539a6d6-56ce-48f9-bcf0-5aa41da88f6a',NULL,'6819f961-b144-4c96-bbbd-8a0c0055cce1','inversion','Deposito a capital de inversion','Deposito de $1000 a capital de inversion de CARRANCO  GAVINO ',1,'entrada',1,'2026-06-20 17:07:49','2026-06-20 17:09:45',NULL);
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `obligaciones_sesion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `obligaciones_sesion` (
  `id_obligacion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador unico de la obligacion (UUID)',
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesion donde se genero',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio',
  `tipo` enum('cuota_mensual','cuota_credito','multa','otro') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de obligacion',
  `concepto` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripcion detallada de la obligacion',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto a pagar',
  `id_referencia` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a amortizacion, multa, etc',
  `pagada` tinyint(1) DEFAULT '0' COMMENT 'Indica si ya fue pagada',
  `id_cobro` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro cuando se paga',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion',
  PRIMARY KEY (`id_obligacion`),
  UNIQUE KEY `uk_sesion_socio_tipo_ref` (`id_sesion`,`id_socio`,`tipo`,`id_referencia`),
  KEY `id_sesion` (`id_sesion`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `obligaciones_sesion_ibfk_1` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `obligaciones_sesion_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Obligaciones de pago generadas al abrir una sesion - calculadas segun fecha de reunion';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `obligaciones_sesion` WRITE;
/*!40000 ALTER TABLE `obligaciones_sesion` DISABLE KEYS */;
/*!40000 ALTER TABLE `obligaciones_sesion` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `parametros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parametros` (
  `id_parametro` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del parámetro',
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del parámetro (ej: tasa_interés_crédito)',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo del parámetro',
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Valor del parámetro',
  `tipo` enum('texto','numero','decimal','booleano','color') COLLATE utf8mb4_unicode_ci DEFAULT 'texto' COMMENT 'Tipo de dato del valor',
  `modulo` enum('general','financiero','seguridad','imagen') COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'Módulo al que pertenece el parámetro',
  `editable` tinyint(1) DEFAULT '1' COMMENT 'Indica si el parámetro puede ser editado desde el panel',
  PRIMARY KEY (`id_parametro`),
  UNIQUE KEY `código` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Parámetros configurables del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `parametros` WRITE;
/*!40000 ALTER TABLE `parametros` DISABLE KEYS */;
INSERT INTO `parametros` (`id_parametro`, `codigo`, `nombre`, `valor`, `tipo`, `modulo`, `editable`) VALUES (1,'tasa_interés_crédito','Tasa de interés para créditos','6.00','decimal','financiero',1),(2,'método_interés_default','Método de interés por defecto','simple','texto','financiero',1),(3,'tasa_interés_ahorro','Tasa de interés sobre ahorros','0.00','decimal','financiero',1),(4,'tasa_interés_inversión','Tasa de interés para inversiones','6.00','decimal','financiero',1),(5,'aporte_obligatorio_mensual','Aporte obligatorio mensual','10.00','decimal','financiero',1),(6,'cuota_ingreso','Cuota única de ingreso','20.00','decimal','financiero',1),(7,'multa_retraso_10min','Multa retraso 10-30 minutos','1.00','decimal','financiero',1),(8,'multa_retraso_30min','Multa retraso >=30 minutos','5.00','decimal','financiero',1),(9,'multa_inasistencia','Multa por inasistencia','5.00','decimal','financiero',1),(10,'multa_mora_crédito','Multa por mora de crédito','5.00','decimal','financiero',1),(11,'límite_crédito_emergente','Límite crédito emergente','300.00','decimal','financiero',1),(12,'plazo_mínimo_inversión','Plazo mínimo inversión (meses)','6','numero','financiero',1),(13,'intentos_máx_login','Intentos máximo de login','3','numero','seguridad',1),(14,'bloqueo_minutos','Minutos de bloqueo','15','numero','seguridad',1),(15,'session_timeout_minutos','Timeout de sesión (minutos)','30','numero','seguridad',1),(16,'pin_2fa_dígitos','Dígitos del PIN 2FA','6','numero','seguridad',1),(17,'pin_2fa_expiracion_min','Expiración PIN 2FA (minutos)','5','numero','seguridad',1),(18,'máx_reenvío_pin_hora','Máximo reenvíos PIN por hora','3','numero','seguridad',1),(19,'logo_sidebar','Logo del sidebar','ca62b9e0-de01-42cc-9bb6-0826f49dce00','texto','imagen',1),(20,'logo_sd','Logo sin fondo','d9433f2e-ffa1-48c9-bf86-b338e6796ff2','texto','imagen',1);
/*!40000 ALTER TABLE `parametros` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id_permiso` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del permiso',
  `codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del permiso (ej: socio.registrar)',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo del permiso',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción detallada del alcance del permiso',
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '' COMMENT 'Modulo/categoria para agrupar permisos',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `código` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de permisos disponibles en el sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` (`id_permiso`, `codigo`, `nombre`, `descripcion`, `modulo`) VALUES (1,'auth.login','Ingresar al sistema','Permite iniciar sesión en el sistema','Autenticacion'),(2,'auth.ver_2fa','Acceder con 2FA','Permite acceder con autenticación de dos factores','Autenticacion'),(3,'socio.registrar','Registrar nuevo socio','Permite registrar un nuevo socio en el sistema','Socios'),(4,'socio.editar','Editar datos de socio','Permite modificar los datos de un socio existente','Socios'),(5,'socio.cambiar_estado','Cambiar estado del socio','Permite cambiar el estado de un socio en su ciclo de vida','Socios'),(6,'socio.consultar','Consultar lista de socios','Permite consultar el listado de socios registrados','Socios'),(7,'socio.ver_financiero','Ver datos financieros del socio','Permite visualizar la información financiera del socio','Socios'),(8,'param.usuarios','Gestionar usuarios del sistema','CRUD completo de usuarios del sistema','Parametros del Sistema'),(9,'param.roles','Gestionar roles y permisos','Crear, editar y eliminar roles con permisos personalizados','Parametros del Sistema'),(10,'param.imagen','Configurar imagen corporativa','Gestionar logo, colores, membrete y razón social','Parametros del Sistema'),(11,'param.catalogos','Editar catálogos','Gestionar provincias, cantones y entidades públicas','Parametros del Sistema'),(12,'param.financiero','Configurar parámetros financieros','Configurar tasas, montos, plazos y métodos de interés','Parametros del Sistema'),(13,'producto.crear','Crear productos financieros','Crear nuevos productos de crédito e inversión','Productos Financieros'),(14,'producto.editar','Editar productos','Modificar productos financieros existentes','Productos Financieros'),(15,'producto.activar','Activar/desactivar productos','Activar o desactivar productos financieros','Productos Financieros'),(16,'cobro.aporte','Registrar cobro de aporte','Registrar cobro de aporte obligatorio y voluntario','Cobros'),(17,'cobro.cuota_credito','Registrar cobro de cuota de crédito','Registrar cobro de cuotas de crédito','Cobros'),(18,'cobro.multa','Registrar cobro de multa','Registrar cobro de multas generadas','Cobros'),(19,'cobro.inversion','Registrar inversión voluntaria','Registrar apertura de inversión a plazo fijo','Cobros'),(20,'cobro.desembolso','Realizar desembolso de crédito','Ejecutar el desembolso de un crédito aprobado','Cobros'),(21,'cobro.anular','Anular cobro registrado','Anular un cobro previamente registrado','Cobros'),(22,'cobro.cierre_sesion','Ejecutar cierre de sesión mensual','Cerrar la sesión mensual con generación de acta','Cobros'),(23,'calculo.intereses','Ejecutar cálculo de intereses','Calcular intereses de créditos, ahorros e inversiones','Calculos Financieros'),(24,'calculo.excedentes','Calcular distribución de excedentes','Calcular la distribución de excedentes entre los socios','Calculos Financieros'),(25,'calculo.aprobar_excedentes','Aprobar distribución de excedentes','Aprobar la distribución de excedentes calculada','Calculos Financieros'),(26,'reporte.socios','Generar reportes de socios','Generar reportes del módulo de socios','Reportes'),(27,'reporte.financiero','Generar reportes financieros','Generar reportes del módulo financiero','Reportes'),(28,'reporte.cobros','Generar reportes de cobros','Generar reportes del módulo de cobros','Reportes'),(29,'credito.aprobar','Aprobar/rechazar creditos','Permite aprobar o rechazar solicitudes de credito en la bandeja de aprobacion','Creditos'),(30,'notificacion.configurar','Configurar reglas de notificacion','Gestionar las reglas de notificacion del sistema (canal y destinatarios)','Notificaciones'),(32,'multa.impugnar','Impugnar multas','Permite autorizar la impugnacion de multas presentadas por los socios','Multas'),(33,'multa.autorizar_impugnacion','Autorizar impugnacion','Permite autorizar o rechazar impugnaciones de multas presentadas por los socios','Multas'),(34,'inversion.aprobar','Aprobar/rechazar inversiones','Permite aprobar o rechazar solicitudes de inversion en la bandeja de aprobacion','Inversiones'),(35,'socio.eliminar','Eliminar socio','Permite eliminar un socio del sistema de forma permanente','Socios');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `productos_financieros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_financieros` (
  `id_producto` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del producto financiero (UUID)',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del producto (ej: Crédito Ordinario, Inversión 6 Meses)',
  `tipo` enum('credito','inversion') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tasa_interes_anual` decimal(5,2) NOT NULL DEFAULT '6.00' COMMENT 'Tasa de interés anual en porcentaje',
  `metodo_interes` enum('simple','frances','aleman') COLLATE utf8mb4_unicode_ci DEFAULT 'simple' COMMENT 'Metodo de calculo de intereses',
  `plazo_min_meses` int NOT NULL COMMENT 'Plazo mínimo en meses',
  `plazo_max_meses` int NOT NULL COMMENT 'Plazo máximo en meses',
  `monto_min` decimal(10,2) NOT NULL COMMENT 'Monto mínimo del producto',
  `monto_max` decimal(10,2) NOT NULL COMMENT 'Monto máximo del producto',
  `requiere_garante` tinyint(1) DEFAULT '0' COMMENT 'Indica si el producto requiere garante',
  `penalidad_retiro_anticipado` decimal(5,2) DEFAULT '0.00' COMMENT 'Penalidad por retiro anticipado (%)',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Indica si el producto está activo para nuevas solicitudes',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del producto',
  `condiciones_html` text COLLATE utf8mb4_unicode_ci COMMENT 'Condiciones generales del credito en HTML (WYSIWYG)',
  `min_permanencia_meses` int DEFAULT '0' COMMENT 'Minimo de permanencia como socio activo (meses)',
  `min_ahorro` decimal(10,2) DEFAULT '0.00' COMMENT 'Minimo de ahorro acumulado requerido',
  `es_emergente` tinyint(1) DEFAULT '0' COMMENT 'Si es credito emergente (no requiere sesion de aprobacion)',
  `monto_max_emergente` decimal(10,2) DEFAULT '0.00' COMMENT 'Monto maximo para credito emergente',
  `requiere_documento_firmado` tinyint(1) DEFAULT '1' COMMENT 'Si requiere documento firmado escaneado antes del desembolso',
  `dias_gracia` int DEFAULT '0' COMMENT 'Dias de gracia antes de primera cuota',
  `min_ahorro_unidad` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'dolares' COMMENT 'Unidad del monto minimo de ahorro: dolares o porcentaje',
  `min_destino_caracteres` int DEFAULT '0' COMMENT 'Minimo de caracteres para destino del credito',
  `min_permanencia_valor` int DEFAULT '0' COMMENT 'Valor minimo de permanencia',
  `min_permanencia_unidad` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'meses' COMMENT 'Unidad de permanencia: dias, meses, anios',
  PRIMARY KEY (`id_producto`),
  KEY `idx_productos_tipo` (`tipo`),
  KEY `idx_productos_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de productos financieros parametrizables por el Analista Financiero';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `productos_financieros` WRITE;
/*!40000 ALTER TABLE `productos_financieros` DISABLE KEYS */;
INSERT INTO `productos_financieros` (`id_producto`, `nombre`, `tipo`, `tasa_interes_anual`, `metodo_interes`, `plazo_min_meses`, `plazo_max_meses`, `monto_min`, `monto_max`, `requiere_garante`, `penalidad_retiro_anticipado`, `activo`, `fecha_creacion`, `condiciones_html`, `min_permanencia_meses`, `min_ahorro`, `es_emergente`, `monto_max_emergente`, `requiere_documento_firmado`, `dias_gracia`, `min_ahorro_unidad`, `min_destino_caracteres`, `min_permanencia_valor`, `min_permanencia_unidad`) VALUES ('2e4c5dbd-afa8-424e-9367-6687ad3c4490','Inversión ordinaria','inversion',6.00,'simple',3,12,500.00,10000.00,0,5.00,1,'2026-06-20 15:42:04','<p>Condiciones de Inversión</p><p>Estas son condiciones de inversión.</p><p>-Saludos</p>',0,0.00,0,0.00,1,0,'dolares',0,0,'meses'),('c3dd23b3-5eff-45f3-97c6-8343c340bfcc','Crédito Ordinario','credito',6.00,'simple',3,12,100.00,1000.00,0,0.00,1,'2026-06-07 18:03:07','<p>Condiciones del crédito</p><p>Estas son las condiciones que debe aceptar el socio para acceder al crédito.</p><p>-La Directiva</p>',0,20.00,0,0.00,0,0,'dolares',10,0,'meses');
/*!40000 ALTER TABLE `productos_financieros` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `provincias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provincias` (
  `id_provincia` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico de la provincia',
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la provincia',
  PRIMARY KEY (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de provincias del Ecuador';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `provincias` WRITE;
/*!40000 ALTER TABLE `provincias` DISABLE KEYS */;
INSERT INTO `provincias` (`id_provincia`, `nombre`) VALUES (1,'Pichincha');
/*!40000 ALTER TABLE `provincias` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reglas_notificacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reglas_notificacion` (
  `id_regla` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_evento` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo_evento` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canal` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'push',
  `para_todos` tinyint(1) DEFAULT '0',
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_regla`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_reglas_evento` (`tipo_evento`,`activo`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reglas_notificacion` WRITE;
/*!40000 ALTER TABLE `reglas_notificacion` DISABLE KEYS */;
INSERT INTO `reglas_notificacion` (`id_regla`, `codigo`, `nombre`, `tipo_evento`, `titulo_evento`, `canal`, `para_todos`, `activo`) VALUES (1,'solicitud_credito','Solicitud de credito','credito','Nueva solicitud de credito','push',0,1),(2,'credito_aprobado','Credito aprobado','credito','Credito aprobado','push',0,1),(3,'credito_rechazado','Credito rechazado','credito','Credito rechazado','push',0,1),(4,'credito_desembolsado','Credito desembolsado','credito','Credito desembolsado','push',0,1),(5,'credito_mora','Credito en mora','credito',NULL,'ambos',1,1),(6,'solicitud_retiro','Solicitud de retiro','cobro','Solicitud de retiro','push',0,1),(7,'sesion_cerrada','Sesion cerrada','sesion','Sesion cerrada','ambos',1,1);
/*!40000 ALTER TABLE `reglas_notificacion` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `reglas_notificacion_destinatarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reglas_notificacion_destinatarios` (
  `id_regla` int NOT NULL,
  `id_rol` int NOT NULL,
  PRIMARY KEY (`id_regla`,`id_rol`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `reglas_notificacion_destinatarios_ibfk_1` FOREIGN KEY (`id_regla`) REFERENCES `reglas_notificacion` (`id_regla`) ON DELETE CASCADE,
  CONSTRAINT `reglas_notificacion_destinatarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `reglas_notificacion_destinatarios` WRITE;
/*!40000 ALTER TABLE `reglas_notificacion_destinatarios` DISABLE KEYS */;
INSERT INTO `reglas_notificacion_destinatarios` (`id_regla`, `id_rol`) VALUES (1,2),(6,2),(2,4),(6,4);
/*!40000 ALTER TABLE `reglas_notificacion_destinatarios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del rol',
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre personalizable del rol (ej: Presidente, Tesorero)',
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción de las funciones del rol',
  `endosable` tinyint(1) DEFAULT '0' COMMENT 'Si es TRUE, este rol puede acumular permisos de otros roles',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema — 100% personalizables desde el panel de administración';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `endosable`) VALUES (1,'Administrador Técnico','Gobierna usuarios, roles, permisos e imagen corporativa. Sin acceso financiero',0),(2,'Presidente','Representante legal, convocatorias, supervisión, firma de certificados',0),(3,'Analista Financiero','Configura productos financieros, parámetros, cálculos y distribución de excedentes',1),(4,'Tesorero','Ejecución financiera diaria: cobros, desembolsos, cierre de sesión',0),(5,'Asistente de Tesorería','Apoyo en cobros de aportes, cuotas y multas',0),(6,'Socio','Acceso al portal personal: consultas, solicitudes, comprobantes',0),(7,'Secretario/a','Gestión documental, registro de socios, certificados, actas y convocatorias',0);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_permisos` (
  `id_rol` int NOT NULL COMMENT 'FK al ID del rol',
  `id_permiso` int NOT NULL COMMENT 'FK al ID del permiso',
  `permitir` tinyint(1) DEFAULT '1' COMMENT 'TRUE = concedido, FALSE = denegado explícitamente',
  PRIMARY KEY (`id_rol`,`id_permiso`),
  KEY `id_permiso` (`id_permiso`),
  CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE,
  CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz de permisos asignados a cada rol (gestión por checkboxes)';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
INSERT INTO `roles_permisos` (`id_rol`, `id_permiso`, `permitir`) VALUES (1,1,1),(1,2,1),(1,3,1),(1,4,1),(1,6,1),(1,8,1),(1,9,1),(1,10,1),(1,11,1),(1,30,1),(1,35,1),(2,1,1),(2,2,1),(2,3,1),(2,4,1),(2,5,1),(2,6,1),(2,7,1),(2,21,1),(2,22,1),(2,25,1),(2,26,1),(2,27,1),(2,28,1),(2,29,1),(2,30,1),(3,1,1),(3,2,1),(3,4,1),(3,6,1),(3,7,1),(3,12,1),(3,13,1),(3,14,1),(3,15,1),(3,21,1),(3,22,1),(3,23,1),(3,24,1),(3,26,1),(3,27,1),(3,28,1),(3,30,1),(4,1,1),(4,2,1),(4,3,1),(4,4,1),(4,6,1),(4,7,1),(4,16,1),(4,17,1),(4,18,1),(4,19,1),(4,20,1),(4,21,1),(4,22,1),(4,26,1),(4,27,1),(4,28,1),(4,29,1),(4,34,1),(5,1,1),(5,6,1),(5,7,1),(5,16,1),(5,17,1),(5,18,1),(5,19,1),(5,26,1),(5,28,1),(6,1,1),(7,1,1),(7,2,1),(7,3,1),(7,4,1),(7,5,1),(7,6,1),(7,7,1),(7,16,1),(7,21,1),(7,26,1),(7,30,1);
/*!40000 ALTER TABLE `roles_permisos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_usuarios` (
  `id_usuario` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al UUID del usuario',
  `id_rol` int NOT NULL COMMENT 'FK al ID del rol',
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `roles_usuarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `roles_usuarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asignación de roles a usuarios (relación muchos-a-muchos)';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles_usuarios` WRITE;
/*!40000 ALTER TABLE `roles_usuarios` DISABLE KEYS */;
INSERT INTO `roles_usuarios` (`id_usuario`, `id_rol`) VALUES ('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',1),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',2),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',3),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',4),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',5),('1673019a-c66d-4bb8-9158-1729fa6b064a',6),('6600ae1d-e99d-4986-b337-0741de09df84',6),('e33bb9c5-441e-4f55-9a1c-eb05a28cff3d',6),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',7);
/*!40000 ALTER TABLE `roles_usuarios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sesiones_mensuales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_mensuales` (
  `id_sesion` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la sesión mensual (UUID)',
  `numero_sesion` int NOT NULL COMMENT 'Número correlativo de la sesión mensual',
  `fecha_sesion` date NOT NULL COMMENT 'Fecha de la sesion',
  `titulo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Título o nombre de la sesión',
  `estado` enum('abierta','cerrada') COLLATE utf8mb4_unicode_ci DEFAULT 'abierta' COMMENT 'Estado de la sesión: abierta (en curso) o cerrada (finalizada)',
  `fecha_apertura` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de apertura de la sesión',
  `fecha_cierre` datetime DEFAULT NULL COMMENT 'Fecha y hora de cierre de la sesión',
  `usuario_cierre` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que ejecutó el cierre de sesión',
  `acta_cierre_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de cierre',
  `total_recaudado` decimal(12,2) DEFAULT '0.00' COMMENT 'Total recaudado en la sesión',
  `total_desembolsado` decimal(12,2) DEFAULT '0.00' COMMENT 'Total desembolsado en la sesión',
  `saldo_caja` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo final de caja (recaudado - desembolsado)',
  PRIMARY KEY (`id_sesion`),
  KEY `usuario_cierre` (`usuario_cierre`),
  CONSTRAINT `sesiones_mensuales_ibfk_1` FOREIGN KEY (`usuario_cierre`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesiones mensuales de cobro y check-in — núcleo operativo del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sesiones_mensuales` WRITE;
/*!40000 ALTER TABLE `sesiones_mensuales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sesiones_mensuales` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `socios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `socios` (
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del socio (UUID)',
  `cedula` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cédula de identidad ecuatoriana (10 dígitos, dígito verificador)',
  `apellido1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primer apellido (mayúsculas)',
  `apellido2` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Segundo apellido (mayúsculas)',
  `nombre1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primer nombre (mayúsculas)',
  `nombre2` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Segundo nombre (mayúsculas)',
  `fecha_nacimiento` date NOT NULL COMMENT 'Fecha de nacimiento',
  `genero` enum('masculino','femenino') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Género del socio',
  `estado_civil` enum('soltero','casado','divorciado','viudo','union_libre') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Dirección de residencia',
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de teléfono fijo',
  `celular` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número de celular',
  `correo_electronico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correo electrónico (validado con PIN 6 dígitos)',
  `profesion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Profesión u ocupación',
  `foto_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de la fotografía del socio',
  `documento_identidad_anverso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF/JPG del anverso de la cédula',
  `documento_identidad_reverso` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF/JPG del reverso de la cédula',
  `estado` enum('pendiente','pre_activo','activo','suspendido','retiro_voluntario','excluido','fallecido') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado actual del socio en el ciclo de vida',
  `fecha_ingreso` date NOT NULL COMMENT 'Fecha de solicitud de ingreso',
  `fecha_aprobacion` date DEFAULT NULL COMMENT 'Fecha de aprobación por la Asamblea',
  `numero_acta_aprobacion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de acta de la Asamblea que aprobó el ingreso',
  `acta_aprobacion_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de aprobación',
  `observaciones` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones generales del socio',
  `fecha_retiro` date DEFAULT NULL COMMENT 'Fecha de retiro voluntario',
  `motivo_retiro` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo del retiro voluntario',
  `fecha_exclusion` date DEFAULT NULL COMMENT 'Fecha de exclusión (Art.14 Estatuto)',
  `motivo_exclusion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de la exclusión',
  `menor_edad` tinyint(1) DEFAULT '0' COMMENT 'Indica si el socio es menor de edad',
  `representante_nombres` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombres del representante legal (menores de edad)',
  `representante_cedula` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cédula del representante legal',
  `representante_telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Teléfono del representante legal',
  `representante_correo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Correo del representante legal',
  `representante_documento_pdf` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Documento legal del representante (PDF)',
  `hash_integridad` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  PRIMARY KEY (`id_socio`),
  UNIQUE KEY `cédula` (`cedula`),
  UNIQUE KEY `correo_electrónico` (`correo_electronico`),
  KEY `idx_socios_cédula` (`cedula`),
  KEY `idx_socios_correo` (`correo_electronico`),
  KEY `idx_socios_estado` (`estado`),
  KEY `idx_socios_apellidos` (`apellido1`,`apellido2`),
  KEY `idx_socios_nombres` (`nombre1`,`nombre2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de socios de la Caja de Ahorro con datos personales, estado y representación';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `socios` WRITE;
/*!40000 ALTER TABLE `socios` DISABLE KEYS */;
INSERT INTO `socios` (`id_socio`, `cedula`, `apellido1`, `apellido2`, `nombre1`, `nombre2`, `fecha_nacimiento`, `genero`, `estado_civil`, `direccion`, `telefono`, `celular`, `correo_electronico`, `profesion`, `foto_url`, `documento_identidad_anverso`, `documento_identidad_reverso`, `estado`, `fecha_ingreso`, `fecha_aprobacion`, `numero_acta_aprobacion`, `acta_aprobacion_pdf`, `observaciones`, `fecha_retiro`, `motivo_retiro`, `fecha_exclusion`, `motivo_exclusion`, `menor_edad`, `representante_nombres`, `representante_cedula`, `representante_telefono`, `representante_correo`, `representante_documento_pdf`, `hash_integridad`, `fecha_creacion`) VALUES ('4a852897-8bd3-4d1f-8b49-55bd324207c8','1002251716','CORDERO','','LUIS','','2026-06-11','masculino',NULL,'IBARRA','','0996755645','jlcarranco@hotmail.com','',NULL,NULL,NULL,'pendiente','2026-06-24',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'','','','',NULL,'a211a945fef873398b9f528c5c5ff2906b22461a1f020bd3beb3a657e64e10f9','2026-06-24 00:42:38'),('6819f961-b144-4c96-bbbd-8a0c0055cce1','1002003000','CARRANCO','','GAVINO','','1983-01-19','masculino',NULL,'IBARRA','','0996755645','gavinocg@gmail.com','Msc.',NULL,NULL,NULL,'activo','2026-06-20','2026-06-20','','acta_aprobacion_6819f961.pdf',NULL,NULL,NULL,NULL,NULL,0,'','','','',NULL,'9f871cca110d70999d0aa86909774f8de9a668f1bb14703960475425e90f3acd','2026-06-20 14:20:38');
/*!40000 ALTER TABLE `socios` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `solicitudes_retiro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_retiro` (
  `id_solicitud` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID de la solicitud',
  `id_socio` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio solicitante',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto solicitado',
  `motivo` text COLLATE utf8mb4_unicode_ci COMMENT 'Motivo del retiro',
  `estado` enum('pendiente','aprobado','rechazado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado de la solicitud',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud',
  `fecha_respuesta` datetime DEFAULT NULL COMMENT 'Fecha de aprobación/rechazo',
  `usuario_respuesta` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que aprobó/rechazó',
  `id_cobro` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cobro generado al aprobar',
  PRIMARY KEY (`id_solicitud`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `solicitudes_retiro_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de retiro de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `solicitudes_retiro` WRITE;
/*!40000 ALTER TABLE `solicitudes_retiro` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitudes_retiro` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del usuario (UUID)',
  `nombres` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombres del usuario',
  `apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Apellidos del usuario',
  `cedula` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cédula de identidad ecuatoriana',
  `correo_electronico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correo electrónico del usuario',
  `telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de teléfono',
  `nombre_usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de usuario para inicio de sesión',
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash bcrypt de la contraseña',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Indica si el usuario está activo en el sistema',
  `_2fa_obligatorio` tinyint(1) DEFAULT '0' COMMENT 'Indica si el 2FA es obligatorio para este usuario',
  `_2fa_activo` tinyint(1) DEFAULT '0' COMMENT 'Indica si el 2FA está actualmente activo',
  `bloqueado_hasta` datetime DEFAULT NULL COMMENT 'Fecha/hasta cuándo está bloqueado (3 intentos fallidos)',
  `intentos_fallidos` int DEFAULT '0' COMMENT 'Contador de intentos fallidos de inicio de sesión',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `fecha_ultimo_acceso` datetime DEFAULT NULL COMMENT 'Fecha y hora del último inicio de sesión exitoso',
  `token_activacion` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 del token de activacion de cuenta',
  `token_activacion_expira` datetime DEFAULT NULL COMMENT 'Expiracion del token de activacion',
  `fecha_contrasena` datetime DEFAULT NULL COMMENT 'Fecha del ultimo cambio de contrasena',
  `reset_token_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 del token de restablecimiento de contrasena',
  `reset_token_expira` datetime DEFAULT NULL COMMENT 'Expiracion del token de restablecimiento',
  `reset_token_usos` int DEFAULT '0' COMMENT 'Contador de usos del token de restablecimiento',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `cédula` (`cedula`),
  UNIQUE KEY `correo_electrónico` (`correo_electronico`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `idx_usuarios_cédula` (`cedula`),
  KEY `idx_usuarios_correo` (`correo_electronico`),
  KEY `idx_usuarios_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema con credenciales de acceso y control 2FA';
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` (`id_usuario`, `nombres`, `apellidos`, `cedula`, `correo_electronico`, `telefono`, `nombre_usuario`, `contrasena`, `activo`, `_2fa_obligatorio`, `_2fa_activo`, `bloqueado_hasta`, `intentos_fallidos`, `fecha_creacion`, `fecha_ultimo_acceso`, `token_activacion`, `token_activacion_expira`, `fecha_contrasena`, `reset_token_hash`, `reset_token_expira`, `reset_token_usos`) VALUES ('1673019a-c66d-4bb8-9158-1729fa6b064a','Gavino','Carranco','1002003000','gavinocg@gmail.com','0996755645','gcarranco','$2y$12$he9h3v/EHzKV/O.6c4kt4uYsYGPt2aHoHjAOzhoqzrMKhUNfdz2KG',1,0,0,NULL,0,'2026-06-06 16:38:03','2026-06-25 20:35:13',NULL,NULL,'2026-06-06 16:38:03',NULL,NULL,0),('6600ae1d-e99d-4986-b337-0741de09df84','CARLOS MANUEL','VARGAS CRUZ','1766677788','carlos.vargas@email.com','','1766677788','$2y$12$wx0/HsCyDTfUlKWE8twT/uRPv2o/MEOWXbadf9piFa6So5g39Trie',1,0,0,NULL,0,'2026-06-06 19:35:51','2026-06-06 19:44:55',NULL,NULL,'2026-06-06 19:35:51',NULL,NULL,0),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291','Admin','Sistema','1002606083','admin@caja.test','0999999999','admin','$2y$12$he9h3v/EHzKV/O.6c4kt4uYsYGPt2aHoHjAOzhoqzrMKhUNfdz2KG',1,0,0,NULL,0,'2026-06-06 14:16:51','2026-06-24 00:04:42',NULL,NULL,'2026-06-20 14:39:19','$2y$12$Zv9DvqMKa/BSRVhgfSWxROh9zu75TawpzezMGBqt6EVYICZ3.aAvS','2026-06-24 00:03:57',0),('e33bb9c5-441e-4f55-9a1c-eb05a28cff3d','LUIS ','CORDERO ','1002251716','jlcarranco@hotmail.com','0996755645','1002251716','',1,0,0,NULL,0,'2026-06-24 00:42:38',NULL,'258ec0a1c9f8191609839d1324a7100b88450d867538d5de87f743f64981db48','2026-06-26 00:42:38',NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

