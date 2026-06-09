-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: caja_ahorro_pujota
-- ------------------------------------------------------
-- Server version	8.4.3

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

--
-- Table structure for table `amortizaciones`
--

DROP TABLE IF EXISTS `amortizaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `amortizaciones` (
  `id_amortizacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la amortización (UUID)',
  `id_credito` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al crédito asociado',
  `numero_cuota` int NOT NULL COMMENT 'Número de cuota (1, 2, 3...)',
  `fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento de la cuota',
  `capital` decimal(12,2) NOT NULL COMMENT 'Porción de capital de la cuota',
  `interes` decimal(12,2) NOT NULL COMMENT 'Porción de interés de la cuota',
  `total` decimal(12,2) NOT NULL COMMENT 'Total de la cuota (capital + interés)',
  `saldo_restante` decimal(12,2) NOT NULL COMMENT 'Saldo de capital pendiente después de esta cuota',
  `estado` enum('pendiente','pagada','vencida') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado de la cuota',
  `id_cobro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro cuando la cuota es pagada',
  PRIMARY KEY (`id_amortizacion`),
  KEY `idx_amortizaciones_cr├®dito` (`id_credito`),
  KEY `idx_amortizaciones_estado` (`estado`),
  CONSTRAINT `amortizaciones_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id_credito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de amortizaci├│n de cr├®ditos ÔÇö cuotas generadas seg├║n m├®todo de inter├®s';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `amortizaciones`
--

LOCK TABLES `amortizaciones` WRITE;
/*!40000 ALTER TABLE `amortizaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `amortizaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archivos`
--

DROP TABLE IF EXISTS `archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archivos` (
  `id_archivo` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del archivo (UUID)',
  `nombre_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre original del archivo subido',
  `nombre_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre interno en disco (UUID + extensión)',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo MIME del archivo',
  `tamano` bigint NOT NULL COMMENT 'Tamaño en bytes',
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Extensión del archivo (pdf, jpg, png, etc)',
  `ruta` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ruta relativa desde storage/archivos/',
  `hash_sha256` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 del contenido del archivo',
  `entidad_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de la tabla o módulo asociado (socio, credito, multa, etc)',
  `entidad_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UUID del registro asociado en la entidad',
  `subdirectorio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'Subdirectorio dentro de storage/archivos/',
  `id_usuario_subio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que subió el archivo',
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de subida del archivo',
  PRIMARY KEY (`id_archivo`),
  KEY `id_usuario_subio` (`id_usuario_subio`),
  KEY `idx_archivos_entidad` (`entidad_tipo`,`entidad_id`),
  KEY `idx_archivos_hash` (`hash_sha256`),
  CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_usuario_subio`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gesti├│n centralizada de archivos ? metadatos en BD, archivos fuera del public root';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archivos`
--

LOCK TABLES `archivos` WRITE;
/*!40000 ALTER TABLE `archivos` DISABLE KEYS */;
INSERT INTO `archivos` VALUES ('ca62b9e0-de01-42cc-9bb6-0826f49dce00','LogoCorteNacJusticia.jpg','ca62b9e0-de01-42cc-9bb6-0826f49dce00.jpg','image/jpeg',11497,'jpg','imagen/ca62b9e0-de01-42cc-9bb6-0826f49dce00.jpg','f324df2dada0b396fb8cc06c9868076755bb36984618eb1fde1eb207b623da78','imagen','logo_sidebar','imagen','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','2026-06-06 23:34:48'),('d9433f2e-ffa1-48c9-bf86-b338e6796ff2','LogoCorteNacJusticia.jpg','d9433f2e-ffa1-48c9-bf86-b338e6796ff2.jpg','image/jpeg',11497,'jpg','imagen/d9433f2e-ffa1-48c9-bf86-b338e6796ff2.jpg','f324df2dada0b396fb8cc06c9868076755bb36984618eb1fde1eb207b623da78','imagen','logo_sd','imagen','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','2026-06-06 23:36:19');
/*!40000 ALTER TABLE `archivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias` (
  `id_asistencia` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del registro de asistencia (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio que asiste',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesión mensual',
  `tipo` enum('a_tiempo','retraso_10min','retraso_30min','falta') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de asistencia registrada',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificación presentada por el socio (opcional)',
  `justificacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificación',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificación fue aprobada',
  `usuario_registra` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que registró la asistencia',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `id_socio` (`id_socio`,`id_sesion`),
  KEY `id_sesi├│n` (`id_sesion`),
  KEY `usuario_registra` (`usuario_registra`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de asistencia a sesiones mensuales con tipo y justificaci├│n';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
INSERT INTO `asistencias` VALUES ('134c168d-ee3f-4cc4-90a8-590aa959a350','00e16557-e3cf-4738-8516-7f3fb6ddb96d','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','falta','Me dormí',NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-06 14:16:51'),('1811a689-3a57-4b92-a0e1-068847ce7144','5afb15ad-ced5-431b-9fc2-970cf4919433','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-06 14:16:51'),('3298afe1-51a2-466d-9645-5118612bbf30','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','retraso_10min','No había bus',NULL,0,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291','2026-06-06 21:18:50'),('36df9093-6580-46d7-8110-3b2d3e2066eb','32d4ffda-eec7-4299-885f-f320557da01e','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','falta','Me chumé',NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-06 14:16:51'),('51d8e52a-00a1-461b-a333-f39ac5751a2e','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','retraso_30min',NULL,NULL,0,'6600ae1d-e99d-4986-b337-0741de09df84','2026-06-06 14:16:51'),('966a1116-49c3-475f-b24b-cb85d1fa362d','9e52d148-927b-4784-b290-b8d9f9b1c35f','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-06 14:16:51'),('ea8f11cb-a9b2-4dd0-a775-923beba152ba','caaf8155-4c10-4e84-aa7b-ba4183906421','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-06 14:16:51'),('f2d12a9e-f20c-488b-a1af-992bb96cf9d6','c26b7a29-755b-4665-8912-397c05d48a27','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','falta',NULL,NULL,0,'1673019a-c66d-4bb8-9158-1729fa6b064a','2026-06-06 14:16:51');
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cantones`
--

DROP TABLE IF EXISTS `cantones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cantones` (
  `id_canton` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del cantón',
  `id_provincia` int NOT NULL COMMENT 'FK a la provincia',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del cantón',
  PRIMARY KEY (`id_canton`),
  KEY `id_provincia` (`id_provincia`),
  CONSTRAINT `cantones_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `provincias` (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cat├ílogo de cantones por provincia';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cantones`
--

LOCK TABLES `cantones` WRITE;
/*!40000 ALTER TABLE `cantones` DISABLE KEYS */;
INSERT INTO `cantones` VALUES (1,1,'Pedro Moncayo');
/*!40000 ALTER TABLE `cantones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `capital_inversion`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Capital de inversion del socio — independiente de la cuenta de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `capital_inversion`
--

LOCK TABLES `capital_inversion` WRITE;
/*!40000 ALTER TABLE `capital_inversion` DISABLE KEYS */;
INSERT INTO `capital_inversion` VALUES ('26f9e5ff-0d41-470f-880e-13537981c7ab','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a',0.00,NULL),('6b80fc2c-5101-4c7e-9590-d11f2291f7ea','32d4ffda-eec7-4299-885f-f320557da01e',0.00,NULL),('9b0d26e3-e951-4abb-b954-38baf9127bab','392cced6-d52b-464b-9829-51aa9ce12468',0.00,NULL),('a08f3ba6-ec4c-4537-b30e-2e015bcecc40','c26b7a29-755b-4665-8912-397c05d48a27',0.00,NULL),('b69e3e03-c419-41ba-890a-29cfe7f1521f','00e16557-e3cf-4738-8516-7f3fb6ddb96d',0.00,NULL),('e0c2adc3-686a-4832-b5db-e248ceff3389','caaf8155-4c10-4e84-aa7b-ba4183906421',0.00,NULL),('ea33dbee-6357-48f3-804f-7b2923696a97','5afb15ad-ced5-431b-9fc2-970cf4919433',0.00,NULL),('edd4f598-07ea-4bbb-8746-1f5abfba8e3f','9e52d148-927b-4784-b290-b8d9f9b1c35f',0.00,NULL);
/*!40000 ALTER TABLE `capital_inversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catastro_entidades_publicas`
--

DROP TABLE IF EXISTS `catastro_entidades_publicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catastro_entidades_publicas` (
  `id_entidad` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico de la entidad',
  `ruc` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC de la entidad pública',
  `razon_social` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Razón social de la entidad',
  PRIMARY KEY (`id_entidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catastro de entidades p├║blicas para registro de socios';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catastro_entidades_publicas`
--

LOCK TABLES `catastro_entidades_publicas` WRITE;
/*!40000 ALTER TABLE `catastro_entidades_publicas` DISABLE KEYS */;
/*!40000 ALTER TABLE `catastro_entidades_publicas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cobros`
--

DROP TABLE IF EXISTS `cobros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cobros` (
  `id_cobro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del cobro (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio que realiza el pago',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesión mensual donde se registra el cobro',
  `tipo` enum('aporte_obligatorio','aporte_excedente','cuota_credito','multa','inversion','interes','desembolso','otro','deposito_capital_inversion') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de cobro o transaccion',
  `id_referencia` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID de referencia según el tipo (id_amortización, id_multa, etc.)',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto cobrado',
  `medio_pago` enum('efectivo','transferencia','compensacion','digital') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del comprobante de pago',
  `hash_integridad` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro',
  `usuario_registra` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que registró el cobro',
  `anulado` tinyint(1) DEFAULT '0' COMMENT 'Indica si el cobro fue anulado',
  `motivo_anulacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de la anulación',
  `fecha_anulacion` datetime DEFAULT NULL COMMENT 'Fecha de anulación',
  `usuario_anula` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que anuló el cobro',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro del cobro',
  PRIMARY KEY (`id_cobro`),
  KEY `usuario_registra` (`usuario_registra`),
  KEY `idx_cobros_socio` (`id_socio`),
  KEY `idx_cobros_tipo` (`tipo`),
  KEY `idx_cobros_sesi├│n` (`id_sesion`),
  KEY `idx_cobros_fecha` (`fecha_registro`),
  CONSTRAINT `cobros_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `cobros_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `cobros_ibfk_3` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de cobros ÔÇö transacciones financieras diarias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cobros`
--

LOCK TABLES `cobros` WRITE;
/*!40000 ALTER TABLE `cobros` DISABLE KEYS */;
INSERT INTO `cobros` VALUES ('10cd5e4a-5196-4637-81ae-1d8abef60d6b','00e16557-e3cf-4738-8516-7f3fb6ddb96d','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'6bdbc82185bac2636adbdb232b6fdda6366438efed016b2c2ce7ec85982b0fed','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 14:16:51'),('1cce38e6-c1b2-4ffd-84f2-cb5f7b0b0d14','caaf8155-4c10-4e84-aa7b-ba4183906421','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','multa',NULL,5.00,'efectivo',NULL,'ab86e8399224107e9f4d9c1a8f85e261079d498b7d9f5a025963ecff36f65f55','1673019a-c66d-4bb8-9158-1729fa6b064a',0,NULL,NULL,NULL,'2026-06-06 19:29:26'),('209c4b3b-69c6-4ea7-a774-ef6c498bbfa1','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'ad0811573977fd05ffbfc918dfe998b192c96a65b35f8d38db9d5e97a2bc8256','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:24:31'),('2148b0f7-b5cf-48a7-b74f-0d35654b3386','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,15.00,'efectivo',NULL,'78771ec118f98cac20ff334062b052049b0274e3b05940649c74b4d0b2896168','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:32:17'),('2b478bcd-a5b9-48d1-ae8f-4d357d7295c9','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','inversion',NULL,1000.00,'efectivo',NULL,'2c4619d7dc2ef701a4fec8bb2a1293d9626c01d1d952ca3aae237338efd41e56','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:10:44'),('2c48cc98-acf7-4fe1-a23d-50a2baad13c0','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'9f425fcf0042e8d2ec789f4aa7a9d8c23f255742f411bb900b8034d663cd0341','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:17:36'),('2ffe6696-7c55-4197-9f79-3bb2fcc453e4','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'55dcdde60e77cf6210f2591ae2a174ab6b5ac06b7193a8cad92aa1d8507656be','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:14:53'),('302ea098-9035-450a-aadf-adc88999a823','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,11.00,'efectivo',NULL,'6048b019f1e1eaffba9f645429b2e8064286c2839281ef37dd4f2e2cf9dc1d4f','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:25:34'),('3f0614b3-1fb4-43c4-acda-a63214b488d0','c26b7a29-755b-4665-8912-397c05d48a27','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'aeca6dff50b2d241ca40238798df7e3e35a84970b24bc5c079afe6c36bf32448','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 14:16:51'),('500998a7-de41-4b85-8b30-87ab623628c6','caaf8155-4c10-4e84-aa7b-ba4183906421','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'c682314a11d623c5ee9c0dbce94018a647bcc4afbcfce5b2c85fb07ee6c8aa1b','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 14:16:51'),('5a741484-2a57-4077-b9a8-115da0f13d0f','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,17.00,'efectivo',NULL,'99100808183b764793c0883a5b49c6e986248eb69d5a7e898ef026c41ca5693e','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:34:58'),('799a7368-5976-4025-8cdd-47c98cd60f3f','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,18.00,'efectivo',NULL,'563c46cfd38c47a255685eb69b1694892c644faefce89eefee9c424453228819','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:35:07'),('7b0edfec-0cd1-4498-a1a5-b41dc9cd18b1','9e52d148-927b-4784-b290-b8d9f9b1c35f','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'58cdd4ca9de1bca85e30d7b73ec393ee58f7cc3896b84fbb2cf564bb9c32194d','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 14:16:51'),('81de5cae-e91a-4e61-9ad3-6364a306ab64','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,19.00,'efectivo',NULL,'4956c5859e2168398917c219ebc0530090e7dbb5cee3b6829af7dba5311ce8e8','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:54:08'),('82df7e3d-3e33-459f-98e3-6321e871fd80','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'070774431ff7140de07979809355a07ceabb390332649d696b4d19b434701dbe','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:21:12'),('841aad6d-485e-4683-af74-44c57f3ba870','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'21187c8248b20987b3001772eee8e2a79abc63152aef2d3e828797cdff0b9efc','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:17:59'),('8d3617d3-08f5-4ee6-8a5c-db69c934676c','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,14.00,'efectivo',NULL,'c3c0c149e429e8c5904d2a1eabc740e8fcdb325738925b88b2c07a13c9cb3b80','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:31:28'),('9274379f-b5e9-4a5f-8e4d-522f7dbeb282','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'86114b91942d6a026677c215f8e92f02b28ddae278f5cb6a6aa4a21e109c107d','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 14:16:51'),('9b694bce-10e0-45c4-8eb2-481e7e621040','5afb15ad-ced5-431b-9fc2-970cf4919433','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'03ccd2adbad2de6a57a2249e399391342c2bbf59aa8c02b0f5288c53e2eb1155','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',1,'Error en ingreso','2026-06-06 19:07:27','1673019a-c66d-4bb8-9158-1729fa6b064a','2026-06-06 14:16:51'),('9bccf12e-2920-42df-990c-0a3f62c64bef','00e16557-e3cf-4738-8516-7f3fb6ddb96d',NULL,'otro',NULL,10.00,'efectivo',NULL,'752334f4dbd8dbb3d7bdbb43362dabdb2040fb328a15c557d84823296c47da97','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 23:06:07'),('9fb156dd-a204-451d-b25d-7e4fe57663e6','392cced6-d52b-464b-9829-51aa9ce12468',NULL,'inversion','349ad017-3d29-444e-830e-10df42bc6664',500.00,'efectivo',NULL,'c5ec9f87536935aadaead36001bf9c6a3850982d962049d448d4295c0c18215a','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',0,NULL,NULL,NULL,'2026-06-06 23:42:19'),('b9d9b793-0cbb-42c8-a41a-d350fe57c95d','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,13.00,'efectivo',NULL,'acb8c5751210fdcc0cdd0559f7d3ca85d273d4fa145bf25c97431f823dc9410e','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:31:04'),('c8341bc6-96d2-46d9-b44e-a0c38a73b300','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,12.00,'efectivo',NULL,'b22f879f7f64a58294ae81737b474ef3bd4ea452b5b6df35d77247431180ccb9','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:26:43'),('ea379f32-c23a-4b76-bbb1-de670bee419a','32d4ffda-eec7-4299-885f-f320557da01e','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'b38f8a0561ecb0666474abbc66325ee8a1193f3922e3969da5ab031822dc164e','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',1,'Error en ingreso','2026-06-06 19:07:14','1673019a-c66d-4bb8-9158-1729fa6b064a','2026-06-06 14:16:51'),('eaadfa98-22fe-4f45-9967-3ca0bf280bf2','392cced6-d52b-464b-9829-51aa9ce12468','d7702800-27b1-461c-84bc-2587d460a6ef','aporte_obligatorio',NULL,16.00,'efectivo',NULL,'59e38743b42a4075c0e46ca9ca39fbea507f163ba5c15216201aedaf9a9f3866','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:34:45'),('fda69b6d-70e8-4863-beb8-5cc68aee1ef9','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'20d44a746b3a18192e56c0faef7e6a28c732a9c494a0c586990d026e8ae49b0f','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-06 21:08:56');
/*!40000 ALTER TABLE `cobros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `creditos`
--

DROP TABLE IF EXISTS `creditos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `creditos` (
  `id_credito` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del crédito (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio solicitante',
  `id_producto` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al producto financiero asociado',
  `id_sesion_aprobacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesión donde se aprobó el crédito',
  `monto_solicitado` decimal(12,2) NOT NULL COMMENT 'Monto solicitado por el socio',
  `monto_aprobado` decimal(12,2) DEFAULT NULL COMMENT 'Monto aprobado por la Asamblea',
  `plazo_meses` int NOT NULL COMMENT 'Plazo del crédito en meses',
  `tasa_interes` decimal(5,2) NOT NULL COMMENT 'Tasa de interés anual aplicada',
  `metodo_interes` enum('simple','frances','aleman') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Metodo de interes aplicado a este credito',
  `destino` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Destino o propósito del crédito',
  `estado` enum('ingresado','pendiente','aprobado','legalizado','desembolsado','rechazado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ingresado' COMMENT 'Estado actual de la solicitud de credito',
  `acta_aprobacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de aprobación',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud del crédito',
  `fecha_aprobacion` datetime DEFAULT NULL COMMENT 'Fecha de aprobación',
  `fecha_desembolso` datetime DEFAULT NULL COMMENT 'Fecha de desembolso del crédito',
  `usuario_aprueba` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que aprobó el crédito',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificacion de rechazo o puesta en espera',
  PRIMARY KEY (`id_credito`),
  KEY `id_producto` (`id_producto`),
  KEY `id_sesi├│n_aprobaci├│n` (`id_sesion_aprobacion`),
  KEY `usuario_aprueba` (`usuario_aprueba`),
  KEY `idx_cr├®ditos_estado` (`estado`),
  KEY `idx_cr├®ditos_socio` (`id_socio`),
  CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `creditos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_financieros` (`id_producto`),
  CONSTRAINT `creditos_ibfk_3` FOREIGN KEY (`id_sesion_aprobacion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `creditos_ibfk_4` FOREIGN KEY (`usuario_aprueba`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes y desembolsos de cr├®ditos de los socios';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `creditos`
--

LOCK TABLES `creditos` WRITE;
/*!40000 ALTER TABLE `creditos` DISABLE KEYS */;
/*!40000 ALTER TABLE `creditos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_ahorro`
--

DROP TABLE IF EXISTS `cuentas_ahorro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_ahorro` (
  `id_cuenta_ahorro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la cuenta de ahorro (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio propietario de la cuenta',
  `saldo_obligatorio` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo del aporte obligatorio (USD 10/mes)',
  `saldo_excedente` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo de aportes voluntarios/excedentes',
  `saldo_disponible` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo total disponible para retiro según reglas',
  `fecha_ultimo_movimiento` datetime DEFAULT NULL COMMENT 'Fecha del último movimiento registrado',
  PRIMARY KEY (`id_cuenta_ahorro`),
  UNIQUE KEY `id_socio` (`id_socio`),
  CONSTRAINT `cuentas_ahorro_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuentas de ahorro de los socios ÔÇö capital separado de inversiones';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_ahorro`
--

LOCK TABLES `cuentas_ahorro` WRITE;
/*!40000 ALTER TABLE `cuentas_ahorro` DISABLE KEYS */;
INSERT INTO `cuentas_ahorro` VALUES ('35091853-e6e7-4dcd-a292-5c97229a972a','32d4ffda-eec7-4299-885f-f320557da01e',30.00,0.00,30.00,NULL),('72f76cda-20e3-460a-8533-fab738f82b92','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a',30.00,0.00,30.00,NULL),('7638e37c-72e9-48ba-bfba-0f885133e41a','caaf8155-4c10-4e84-aa7b-ba4183906421',30.00,0.00,30.00,NULL),('95dba24c-5c65-4262-8053-e783ea1c0621','5afb15ad-ced5-431b-9fc2-970cf4919433',30.00,0.00,30.00,NULL),('a09b3d26-dff6-4218-8a1a-f9edc4f10cc7','00e16557-e3cf-4738-8516-7f3fb6ddb96d',30.00,0.00,20.00,'2026-06-06 23:06:07'),('a8ecf0a7-776c-42dc-b573-1e4433d16989','c26b7a29-755b-4665-8912-397c05d48a27',30.00,0.00,30.00,NULL),('ab9d9a6b-f5dd-4850-95fc-c98de0635c18','9e52d148-927b-4784-b290-b8d9f9b1c35f',30.00,0.00,30.00,NULL),('c3df6ac3-fc84-4376-8228-1aaabb9beea0','392cced6-d52b-464b-9829-51aa9ce12468',195.00,0.00,145.00,'2026-06-07 00:17:53');
/*!40000 ALTER TABLE `cuentas_ahorro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `garantes`
--

DROP TABLE IF EXISTS `garantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `garantes` (
  `id_garante` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID del garante',
  `id_credito` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al crédito',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio garante',
  `tipo_garante` enum('fiador_solidario','prendario','hipotecario') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fiador_solidario' COMMENT 'Tipo de garantía',
  `monto_garantizado` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto garantizado',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
  PRIMARY KEY (`id_garante`),
  KEY `id_socio` (`id_socio`),
  KEY `garantes_ibfk_1` (`id_credito`),
  CONSTRAINT `garantes_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id_credito`),
  CONSTRAINT `garantes_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Garantes de cr├®ditos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `garantes`
--

LOCK TABLES `garantes` WRITE;
/*!40000 ALTER TABLE `garantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `garantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historial_operaciones`
--

DROP TABLE IF EXISTS `historial_operaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historial_operaciones` (
  `id_operacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la operación (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio asociado a la operación',
  `tipo_operacion` enum('aporte_obligatorio','aporte_excedente','retiro_ahorro','desembolso_credito','pago_cuota','pago_multa','inversion_apertura','inversion_retiro','interes_ganado','interes_pagado','cierre_sesion','anulacion','deposito_capital_inversion','retiro_capital_inversion') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de operacion financiera',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto de la operación',
  `saldo_anterior` decimal(12,2) DEFAULT NULL COMMENT 'Saldo anterior a la operación',
  `saldo_posterior` decimal(12,2) DEFAULT NULL COMMENT 'Saldo posterior a la operación',
  `id_referencia` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID de referencia a la entidad origen',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesión mensual',
  `id_usuario_registra` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que registró la operación',
  `comprobante_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del comprobante',
  `hash_integridad` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro (inmodificable)',
  `ip_registro` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección IP desde donde se registró la operación',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_operacion`),
  KEY `id_sesi├│n` (`id_sesion`),
  KEY `id_usuario_registra` (`id_usuario_registra`),
  KEY `idx_historial_socio` (`id_socio`),
  KEY `idx_historial_tipo` (`tipo_operacion`),
  KEY `idx_historial_fecha` (`fecha_registro`),
  CONSTRAINT `historial_operaciones_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `historial_operaciones_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `historial_operaciones_ibfk_3` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial inmodificable de operaciones financieras ÔÇö solo inserci├│n, sin DELETE/UPDATE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_operaciones`
--

LOCK TABLES `historial_operaciones` WRITE;
/*!40000 ALTER TABLE `historial_operaciones` DISABLE KEYS */;
INSERT INTO `historial_operaciones` VALUES ('0e858176-d999-479f-81af-063a7cf8ca31','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'2ffe6696-7c55-4197-9f79-3bb2fcc453e4','98f6acb9-49b1-4760-9b90-f224dcb1d654','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:14:53'),('12d28ff9-6529-4da2-8a55-5c4894403258','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'2c48cc98-acf7-4fe1-a23d-50a2baad13c0','98f6acb9-49b1-4760-9b90-f224dcb1d654','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:17:36'),('2061af04-57b5-479c-a17a-12ccb32d7931','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',16.00,NULL,NULL,'eaadfa98-22fe-4f45-9967-3ca0bf280bf2','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:34:45'),('2779cc4e-7c3d-4ac4-aa37-01547051ba61','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',12.00,NULL,NULL,'c8341bc6-96d2-46d9-b44e-a0c38a73b300','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:26:43'),('3704e229-08b5-4973-8c4f-71d03df63a7e','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',18.00,NULL,NULL,'799a7368-5976-4025-8cdd-47c98cd60f3f','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:35:07'),('4a6ec990-f185-4df1-b47d-e32c017c3795','32d4ffda-eec7-4299-885f-f320557da01e','anulacion',10.00,NULL,NULL,'ea379f32-c23a-4b76-bbb1-de670bee419a','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','1673019a-c66d-4bb8-9158-1729fa6b064a',NULL,NULL,'::1','2026-06-06 19:07:14'),('4ab75456-c063-427f-a640-6556cdb38113','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',14.00,NULL,NULL,'8d3617d3-08f5-4ee6-8a5c-db69c934676c','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:31:28'),('60713457-9e08-4ee4-87b7-461925f74b05','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',15.00,NULL,NULL,'2148b0f7-b5cf-48a7-b74f-0d35654b3386','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:32:17'),('655a23d4-9f19-48f5-9a38-cb27bd90b5f1','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',13.00,NULL,NULL,'b9d9b793-0cbb-42c8-a41a-d350fe57c95d','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:31:04'),('78925b55-8a4c-4f3c-96b4-228e45aa7e3a','5afb15ad-ced5-431b-9fc2-970cf4919433','anulacion',10.00,NULL,NULL,'9b694bce-10e0-45c4-8eb2-481e7e621040','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','1673019a-c66d-4bb8-9158-1729fa6b064a',NULL,NULL,'::1','2026-06-06 19:07:27'),('8d251653-7070-4c55-ab1a-767ed71946ee','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'fda69b6d-70e8-4863-beb8-5cc68aee1ef9','98f6acb9-49b1-4760-9b90-f224dcb1d654','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:08:56'),('a6569691-8f49-4c89-9174-982fe7448a2a','392cced6-d52b-464b-9829-51aa9ce12468','inversion_apertura',500.00,NULL,NULL,'349ad017-3d29-444e-830e-10df42bc6664',NULL,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,NULL,'::1','2026-06-06 23:42:19'),('ac4799f0-488d-427f-8439-fd51631d1be0','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',11.00,NULL,NULL,'302ea098-9035-450a-aadf-adc88999a823','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:25:34'),('b85f2618-da68-4dad-9755-54f73f2e1d64','392cced6-d52b-464b-9829-51aa9ce12468','inversion_retiro',450.00,NULL,NULL,'349ad017-3d29-444e-830e-10df42bc6664',NULL,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,NULL,'::1','2026-06-07 00:17:53'),('ba48101b-174f-468d-a797-96b5868587a9','392cced6-d52b-464b-9829-51aa9ce12468','inversion_apertura',1000.00,NULL,NULL,'2b478bcd-a5b9-48d1-ae8f-4d357d7295c9','98f6acb9-49b1-4760-9b90-f224dcb1d654','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:10:44'),('be1b1740-58ca-4228-9e88-bd48cc2077cc','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',17.00,NULL,NULL,'5a741484-2a57-4077-b9a8-115da0f13d0f','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:34:58'),('ceca0417-f799-4b34-a58f-a6f8bd351ac5','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'841aad6d-485e-4683-af74-44c57f3ba870','98f6acb9-49b1-4760-9b90-f224dcb1d654','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:17:59'),('d364155d-e19e-4fbd-923e-ebcdca03b7cb','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'82df7e3d-3e33-459f-98e3-6321e871fd80','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:21:12'),('e3d7ca3a-e8f5-4af8-8a4c-5c333db91a31','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',19.00,NULL,NULL,'81de5cae-e91a-4e61-9ad3-6364a306ab64','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:54:08'),('ec660c44-0498-4840-916c-60b94031c7d7','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'209c4b3b-69c6-4ea7-a774-ef6c498bbfa1','d7702800-27b1-461c-84bc-2587d460a6ef','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'127.0.0.1','2026-06-06 21:24:31'),('fed3a1f8-1121-477d-b449-c30e2cec786c','00e16557-e3cf-4738-8516-7f3fb6ddb96d','retiro_ahorro',10.00,NULL,NULL,'9bccf12e-2920-42df-990c-0a3f62c64bef',NULL,'ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,NULL,'::1','2026-06-06 23:06:07');
/*!40000 ALTER TABLE `historial_operaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inversiones`
--

DROP TABLE IF EXISTS `inversiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inversiones` (
  `id_inversion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la inversión (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio inversionista',
  `id_producto` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al producto de inversión',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto invertido',
  `plazo_meses` int NOT NULL COMMENT 'Plazo de la inversión en meses',
  `tasa_interes` decimal(5,2) NOT NULL COMMENT 'Tasa de interés anual aplicada',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio de la inversión',
  `fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento',
  `rendimiento_proyectado` decimal(12,2) DEFAULT NULL COMMENT 'Rendimiento proyectado al vencimiento',
  `estado` enum('activa','vencida','retiro_anticipado','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'activa' COMMENT 'Estado actual de la inversión',
  `notificado_devolucion` tinyint(1) DEFAULT '0' COMMENT 'Indica si se notificó la próxima devolución',
  `destino_final` enum('capital_inversion','efectivo','transferencia') COLLATE utf8mb4_unicode_ci DEFAULT 'capital_inversion' COMMENT 'Destino al vencimiento: reinversion, efectivo o transferencia',
  `contrato_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del contrato de inversión',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro de la inversión',
  PRIMARY KEY (`id_inversion`),
  KEY `id_producto` (`id_producto`),
  KEY `idx_inversiones_estado` (`estado`),
  KEY `idx_inversiones_socio` (`id_socio`),
  CONSTRAINT `inversiones_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `inversiones_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_financieros` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inversiones a plazo fijo de los socios ÔÇö capital separado de cuenta de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inversiones`
--

LOCK TABLES `inversiones` WRITE;
/*!40000 ALTER TABLE `inversiones` DISABLE KEYS */;
INSERT INTO `inversiones` VALUES ('349ad017-3d29-444e-830e-10df42bc6664','392cced6-d52b-464b-9829-51aa9ce12468','802ad839-cfec-46aa-869f-9910912ea142',500.00,12,7.00,'2026-06-06','2027-06-06',35.00,'retiro_anticipado',0,'capital_inversion',NULL,'2026-06-06 23:42:19');
/*!40000 ALTER TABLE `inversiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multas`
--

DROP TABLE IF EXISTS `multas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multas` (
  `id_multa` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la multa (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio multado',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesión donde se generó la multa',
  `tipo` enum('retraso_10min','retraso_30min','inasistencia','mora_credito','otro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto de la multa en USD',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificación presentada por el socio',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificación fue aprobada',
  `justificacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificación',
  `pagada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la multa fue pagada',
  `fecha_pago` datetime DEFAULT NULL COMMENT 'Fecha de pago de la multa',
  `id_cobro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro cuando la multa es pagada',
  `fecha_generacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de generación de la multa',
  PRIMARY KEY (`id_multa`),
  KEY `id_sesi├│n` (`id_sesion`),
  KEY `id_cobro` (`id_cobro`),
  KEY `idx_multas_socio` (`id_socio`),
  KEY `idx_multas_pagada` (`pagada`),
  CONSTRAINT `multas_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `multas_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `multas_ibfk_3` FOREIGN KEY (`id_cobro`) REFERENCES `cobros` (`id_cobro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multas generadas por inasistencia, retraso o mora ÔÇö base legal Art.11 Estatuto';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multas`
--

LOCK TABLES `multas` WRITE;
/*!40000 ALTER TABLE `multas` DISABLE KEYS */;
INSERT INTO `multas` VALUES ('141a39c5-d4c5-4bfa-8c09-53ae6d97c083','32d4ffda-eec7-4299-885f-f320557da01e','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','inasistencia',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04'),('1d94a1c7-1a9f-4358-86f3-2bd6a0f43cde','c26b7a29-755b-4665-8912-397c05d48a27','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','inasistencia',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04'),('3cd9eafd-4700-4370-9386-7cb1fce7e42d','392cced6-d52b-464b-9829-51aa9ce12468','98f6acb9-49b1-4760-9b90-f224dcb1d654','retraso_10min',1.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 21:19:00'),('89c4529f-0af4-4b4a-80fc-adeffdd7bae4','caaf8155-4c10-4e84-aa7b-ba4183906421','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','inasistencia',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04'),('9d89e250-34fc-4ee3-abf9-f98984c0901d','9e52d148-927b-4784-b290-b8d9f9b1c35f','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','inasistencia',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04'),('a5bd8a36-0683-445f-9dd8-00336cf3951d','5afb15ad-ced5-431b-9fc2-970cf4919433','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','inasistencia',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04'),('c36387ec-e387-4107-ad66-1acc1484084f','caaf8155-4c10-4e84-aa7b-ba4183906421','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','retraso_30min',5.00,NULL,0,NULL,1,'2026-06-06 19:29:26','1cce38e6-c1b2-4ffd-84f2-cb5f7b0b0d14','2026-06-06 14:16:51'),('ed79c639-ae84-4a16-bdda-66486028c5b5','00e16557-e3cf-4738-8516-7f3fb6ddb96d','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','inasistencia',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04'),('ed8b15bb-6337-4251-892f-30a37ab1826f','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','b1dfe7b2-5758-4426-81e0-f22b51d9b29e','retraso_30min',5.00,NULL,0,NULL,0,NULL,NULL,'2026-06-06 20:02:04');
/*!40000 ALTER TABLE `multas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la notificación (UUID)',
  `id_usuario` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al usuario destinatario (si es administrativo)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al socio destinatario (si es socio)',
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de notificación (ej: cobro, crédito, multa)',
  `titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Título de la notificación',
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cuerpo del mensaje',
  `leida` tinyint(1) DEFAULT '0' COMMENT 'Indica si el destinatario leyó la notificación',
  `enviada_pusher` tinyint(1) DEFAULT '0' COMMENT 'Indica si ya se envió por Pusher',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación de la notificación',
  `fecha_lectura` datetime DEFAULT NULL COMMENT 'Fecha en que se leyó la notificación',
  PRIMARY KEY (`id_notificacion`),
  KEY `idx_notificaciones_usuario` (`id_usuario`),
  KEY `idx_notificaciones_socio` (`id_socio`),
  KEY `idx_notificaciones_le├¡da` (`leida`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Buz├│n de notificaciones persistido en BD + env├¡o en tiempo real por Pusher';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES ('1bfb3cb2-ac35-41c3-95f5-0fedfe6a85c7',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $18 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:35:07','2026-06-06 21:56:33'),('3e0556c7-731a-4293-b1d5-7b088d64f3e0','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,'cobro','Cobro registrado','Cobro de Inversión por $1000 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:10:44','2026-06-06 21:14:40'),('473bec64-6d2a-47a6-adf5-b20d71d64ae4',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $12 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:26:43','2026-06-06 21:31:11'),('5857e0ca-2377-4230-bba4-9b2908f28f21','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,'cobro','Cobro registrado','Cobro de Aporte obligatorio por $10 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:08:56','2026-06-06 21:14:39'),('64f018a7-a3b8-4fb2-a140-a67076736c69',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $10 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:17:59','2026-06-06 21:18:10'),('68a63ba1-c533-49ea-9b8f-8f28425c7311',NULL,NULL,'sesión','Sesión cerrada','Sesión #3 ha sido cerrada',1,1,'2026-06-06 22:21:51','2026-06-06 22:24:09'),('6c8f5b6f-cbda-465a-98c8-5bb4c98fdb0d',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $17 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:34:58','2026-06-06 21:56:26'),('6e868475-a16d-4271-86a4-c24728d454a9','516363c5-c79a-4491-83b4-b8303ce1f286','392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $10 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:14:54','2026-06-06 21:15:37'),('7d593341-46a8-4f00-b9ff-a188edfe24b8',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $10 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:17:36','2026-06-06 21:17:51'),('8344a53a-e441-4988-b9b7-cfc189cc637f',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $11 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:25:34','2026-06-06 21:26:16'),('949626eb-b300-4324-b1df-31becc426f57',NULL,NULL,'sesión','Sesión cerrada','Sesión #2 ha sido cerrada',1,1,'2026-06-06 21:19:00','2026-06-06 21:19:06'),('964d0187-b311-43a1-8399-296b267f6125',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $19 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:54:08','2026-06-06 21:56:36'),('b2fd7bb9-7e8d-485b-8e23-30f7baa67844',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $15 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:32:17','2026-06-06 21:33:26'),('bb308b81-5aaa-4c6b-b64b-1fc26b19492f',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $10 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:24:31','2026-06-06 21:25:11'),('d05e961d-0f56-4f47-8331-01d9081e2343',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $10.00 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:21:12','2026-06-06 21:23:47'),('e4c82659-b838-4553-8347-77b18ced137b',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $13 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:31:04','2026-06-06 21:31:11'),('f0d1d717-6543-4bb0-9da8-49ec10494823',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $14 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:31:28','2026-06-06 21:31:55'),('f1dc7bc3-6536-4df6-a0f9-604632fff468',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Cobro registrado','Cobro de Aporte obligatorio por $16 a CARRANCO  GAVINO ',1,1,'2026-06-06 21:34:45','2026-06-06 21:35:14');
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parametros`
--

DROP TABLE IF EXISTS `parametros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parametros` (
  `id_parametro` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del parámetro',
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del parámetro (ej: tasa_interés_crédito)',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo del parámetro',
  `valor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Valor del parámetro',
  `tipo` enum('texto','numero','decimal','booleano','color') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'texto' COMMENT 'Tipo de dato del valor',
  `modulo` enum('general','financiero','seguridad','imagen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'Módulo al que pertenece el parámetro',
  `editable` tinyint(1) DEFAULT '1' COMMENT 'Indica si el parámetro puede ser editado desde el panel',
  PRIMARY KEY (`id_parametro`),
  UNIQUE KEY `c├│digo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Par├ímetros configurables del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametros`
--

LOCK TABLES `parametros` WRITE;
/*!40000 ALTER TABLE `parametros` DISABLE KEYS */;
INSERT INTO `parametros` VALUES (1,'tasa_interés_crédito','Tasa de interés para créditos','6.00','decimal','financiero',1),(2,'método_interés_default','Método de interés por defecto','simple','texto','financiero',1),(3,'tasa_interés_ahorro','Tasa de interés sobre ahorros','0.00','decimal','financiero',1),(4,'tasa_interés_inversión','Tasa de interés para inversiones','6.00','decimal','financiero',1),(5,'aporte_obligatorio_mensual','Aporte obligatorio mensual','10.00','decimal','financiero',1),(6,'cuota_ingreso','Cuota única de ingreso','20.00','decimal','financiero',1),(7,'multa_retraso_10min','Multa retraso 10-30 minutos','1.00','decimal','financiero',1),(8,'multa_retraso_30min','Multa retraso >=30 minutos','5.00','decimal','financiero',1),(9,'multa_inasistencia','Multa por inasistencia','5.00','decimal','financiero',1),(10,'multa_mora_crédito','Multa por mora de crédito','5.00','decimal','financiero',1),(11,'límite_crédito_emergente','Límite crédito emergente','300.00','decimal','financiero',1),(12,'plazo_mínimo_inversión','Plazo mínimo inversión (meses)','6','numero','financiero',1),(13,'intentos_máx_login','Intentos máximo de login','3','numero','seguridad',1),(14,'bloqueo_minutos','Minutos de bloqueo','15','numero','seguridad',1),(15,'session_timeout_minutos','Timeout de sesión (minutos)','30','numero','seguridad',1),(16,'pin_2fa_dígitos','Dígitos del PIN 2FA','6','numero','seguridad',1),(17,'pin_2fa_expiracion_min','Expiración PIN 2FA (minutos)','5','numero','seguridad',1),(18,'máx_reenvío_pin_hora','Máximo reenvíos PIN por hora','3','numero','seguridad',1),(19,'logo_sidebar','Logo del sidebar','ca62b9e0-de01-42cc-9bb6-0826f49dce00','texto','imagen',1),(20,'logo_sd','Logo sin fondo','d9433f2e-ffa1-48c9-bf86-b338e6796ff2','texto','imagen',1);
/*!40000 ALTER TABLE `parametros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id_permiso` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del permiso',
  `codigo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único del permiso (ej: socio.registrar)',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo del permiso',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción detallada del alcance del permiso',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `c├│digo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cat├ílogo de permisos disponibles en el sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'auth.login','Ingresar al sistema','Permite iniciar sesión en el sistema'),(2,'auth.ver_2fa','Acceder con 2FA','Permite acceder con autenticación de dos factores'),(3,'socio.registrar','Registrar nuevo socio','Permite registrar un nuevo socio en el sistema'),(4,'socio.editar','Editar datos de socio','Permite modificar los datos de un socio existente'),(5,'socio.cambiar_estado','Cambiar estado del socio','Permite cambiar el estado de un socio en su ciclo de vida'),(6,'socio.consultar','Consultar lista de socios','Permite consultar el listado de socios registrados'),(7,'socio.ver_financiero','Ver datos financieros del socio','Permite visualizar la información financiera del socio'),(8,'param.usuarios','Gestionar usuarios del sistema','CRUD completo de usuarios del sistema'),(9,'param.roles','Gestionar roles y permisos','Crear, editar y eliminar roles con permisos personalizados'),(10,'param.imagen','Configurar imagen corporativa','Gestionar logo, colores, membrete y razón social'),(11,'param.catalogos','Editar catálogos','Gestionar provincias, cantones y entidades públicas'),(12,'param.financiero','Configurar parámetros financieros','Configurar tasas, montos, plazos y métodos de interés'),(13,'producto.crear','Crear productos financieros','Crear nuevos productos de crédito e inversión'),(14,'producto.editar','Editar productos','Modificar productos financieros existentes'),(15,'producto.activar','Activar/desactivar productos','Activar o desactivar productos financieros'),(16,'cobro.aporte','Registrar cobro de aporte','Registrar cobro de aporte obligatorio y voluntario'),(17,'cobro.cuota_credito','Registrar cobro de cuota de crédito','Registrar cobro de cuotas de crédito'),(18,'cobro.multa','Registrar cobro de multa','Registrar cobro de multas generadas'),(19,'cobro.inversion','Registrar inversión voluntaria','Registrar apertura de inversión a plazo fijo'),(20,'cobro.desembolso','Realizar desembolso de crédito','Ejecutar el desembolso de un crédito aprobado'),(21,'cobro.anular','Anular cobro registrado','Anular un cobro previamente registrado'),(22,'cobro.cierre_sesion','Ejecutar cierre de sesión mensual','Cerrar la sesión mensual con generación de acta'),(23,'calculo.intereses','Ejecutar cálculo de intereses','Calcular intereses de créditos, ahorros e inversiones'),(24,'calculo.excedentes','Calcular distribución de excedentes','Calcular la distribución de excedentes entre los socios'),(25,'calculo.aprobar_excedentes','Aprobar distribución de excedentes','Aprobar la distribución de excedentes calculada'),(26,'reporte.socios','Generar reportes de socios','Generar reportes del módulo de socios'),(27,'reporte.financiero','Generar reportes financieros','Generar reportes del módulo financiero'),(28,'reporte.cobros','Generar reportes de cobros','Generar reportes del módulo de cobros'),(29,'credito.aprobar','Aprobar/rechazar creditos','Permite aprobar o rechazar solicitudes de credito en la bandeja de aprobacion');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_financieros`
--

DROP TABLE IF EXISTS `productos_financieros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_financieros` (
  `id_producto` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del producto financiero (UUID)',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del producto (ej: Crédito Ordinario, Inversión 6 Meses)',
  `tipo` enum('credito','inversion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tasa_interes_anual` decimal(5,2) NOT NULL DEFAULT '6.00' COMMENT 'Tasa de interés anual en porcentaje',
  `metodo_interes` enum('simple','frances','aleman') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'simple' COMMENT 'Metodo de calculo de intereses',
  `plazo_min_meses` int NOT NULL COMMENT 'Plazo mínimo en meses',
  `plazo_max_meses` int NOT NULL COMMENT 'Plazo máximo en meses',
  `monto_min` decimal(10,2) NOT NULL COMMENT 'Monto mínimo del producto',
  `monto_max` decimal(10,2) NOT NULL COMMENT 'Monto máximo del producto',
  `requiere_garante` tinyint(1) DEFAULT '0' COMMENT 'Indica si el producto requiere garante',
  `penalidad_retiro_anticipado` decimal(5,2) DEFAULT '0.00' COMMENT 'Penalidad por retiro anticipado (%)',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Indica si el producto está activo para nuevas solicitudes',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del producto',
  `condiciones_html` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Condiciones generales del credito en HTML (WYSIWYG)',
  `min_permanencia_meses` int DEFAULT '0' COMMENT 'Minimo de permanencia como socio activo (meses)',
  `min_ahorro` decimal(10,2) DEFAULT '0.00' COMMENT 'Minimo de ahorro acumulado requerido',
  `es_emergente` tinyint(1) DEFAULT '0' COMMENT 'Si es credito emergente (no requiere sesion de aprobacion)',
  `monto_max_emergente` decimal(10,2) DEFAULT '0.00' COMMENT 'Monto maximo para credito emergente',
  `requiere_documento_firmado` tinyint(1) DEFAULT '1' COMMENT 'Si requiere documento firmado escaneado antes del desembolso',
  PRIMARY KEY (`id_producto`),
  KEY `idx_productos_tipo` (`tipo`),
  KEY `idx_productos_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cat├ílogo de productos financieros parametrizables por el Analista Financiero';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_financieros`
--

LOCK TABLES `productos_financieros` WRITE;
/*!40000 ALTER TABLE `productos_financieros` DISABLE KEYS */;
INSERT INTO `productos_financieros` VALUES ('802ad839-cfec-46aa-869f-9910912ea142','Inversión 12 Meses','inversion',7.00,'simple',12,12,100.00,10000.00,0,10.00,1,'2026-06-06 14:16:51',NULL,0,0.00,0,0.00,1),('900f2e04-b730-4bbf-9d83-d9c79ea6849e','Crédito Ordinario','credito',6.00,'simple',1,12,50.00,1500.00,1,0.00,1,'2026-06-06 14:16:51',NULL,0,0.00,0,0.00,1),('97daba5b-f71d-49aa-a826-b7f01c81fac1','Crédito Agrícola','credito',5.00,'frances',3,24,100.00,5000.00,1,0.00,1,'2026-06-06 14:16:51',NULL,0,0.00,0,0.00,1),('b53305e5-5102-49c0-9176-d164d3e98c58','Inversión 120 días','inversion',6.00,'simple',3,3,50.00,5000.00,0,5.00,1,'2026-06-06 14:16:51',NULL,0,0.00,0,0.00,1),('c5f8ab97-9808-4919-9e05-b6f89f473538','Crédito Emergente','credito',6.00,'simple',1,6,10.00,300.00,0,0.00,1,'2026-06-06 14:16:51',NULL,0,0.00,0,0.00,1);
/*!40000 ALTER TABLE `productos_financieros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provincias`
--

DROP TABLE IF EXISTS `provincias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provincias` (
  `id_provincia` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico de la provincia',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la provincia',
  PRIMARY KEY (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cat├ílogo de provincias del Ecuador';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provincias`
--

LOCK TABLES `provincias` WRITE;
/*!40000 ALTER TABLE `provincias` DISABLE KEYS */;
INSERT INTO `provincias` VALUES (1,'Pichincha');
/*!40000 ALTER TABLE `provincias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador numérico del rol',
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre personalizable del rol (ej: Presidente, Tesorero)',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción de las funciones del rol',
  `endosable` tinyint(1) DEFAULT '0' COMMENT 'Si es TRUE, este rol puede acumular permisos de otros roles',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema ÔÇö 100% personalizables desde el panel de administraci├│n';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador Técnico','Gobierna usuarios, roles, permisos e imagen corporativa. Sin acceso financiero',0),(2,'Presidente','Representante legal, convocatorias, supervisión, firma de certificados',0),(3,'Analista Financiero','Configura productos financieros, parámetros, cálculos y distribución de excedentes',1),(4,'Tesorero','Ejecución financiera diaria: cobros, desembolsos, cierre de sesión',0),(5,'Asistente de Tesorería','Apoyo en cobros de aportes, cuotas y multas',0),(6,'Socio','Acceso al portal personal: consultas, solicitudes, comprobantes',0),(7,'Secretario/a','Gestión documental, registro de socios, certificados, actas y convocatorias',0);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_permisos`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz de permisos asignados a cada rol (gesti├│n por checkboxes)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permisos`
--

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
INSERT INTO `roles_permisos` VALUES (1,1,1),(1,2,1),(1,6,1),(1,7,1),(1,8,1),(1,9,1),(1,10,1),(1,11,1),(1,26,1),(2,1,1),(2,2,1),(2,3,1),(2,4,1),(2,5,1),(2,6,1),(2,7,1),(2,21,1),(2,22,1),(2,25,1),(2,26,1),(2,27,1),(2,28,1),(2,29,1),(3,1,1),(3,2,1),(3,4,1),(3,6,1),(3,7,1),(3,12,1),(3,13,1),(3,14,1),(3,15,1),(3,21,1),(3,22,1),(3,23,1),(3,24,1),(3,26,1),(3,27,1),(3,28,1),(4,1,1),(4,2,1),(4,3,1),(4,4,1),(4,6,1),(4,7,1),(4,16,1),(4,17,1),(4,18,1),(4,19,1),(4,20,1),(4,21,1),(4,22,1),(4,26,1),(4,27,1),(4,28,1),(4,29,1),(5,1,1),(5,6,1),(5,7,1),(5,16,1),(5,17,1),(5,18,1),(5,19,1),(5,26,1),(5,28,1),(6,1,1),(7,1,1),(7,2,1),(7,3,1),(7,4,1),(7,5,1),(7,6,1),(7,7,1),(7,16,1),(7,26,1);
/*!40000 ALTER TABLE `roles_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_usuarios`
--

DROP TABLE IF EXISTS `roles_usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_usuarios` (
  `id_usuario` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al UUID del usuario',
  `id_rol` int NOT NULL COMMENT 'FK al ID del rol',
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `roles_usuarios_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `roles_usuarios_ibfk_2` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Asignaci├│n de roles a usuarios (relaci├│n muchos-a-muchos)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_usuarios`
--

LOCK TABLES `roles_usuarios` WRITE;
/*!40000 ALTER TABLE `roles_usuarios` DISABLE KEYS */;
INSERT INTO `roles_usuarios` VALUES ('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',1),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',2),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',3),('516363c5-c79a-4491-83b4-b8303ce1f286',4),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',4),('516363c5-c79a-4491-83b4-b8303ce1f286',5),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',5),('1673019a-c66d-4bb8-9158-1729fa6b064a',6),('6600ae1d-e99d-4986-b337-0741de09df84',6),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',7);
/*!40000 ALTER TABLE `roles_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_mensuales`
--

DROP TABLE IF EXISTS `sesiones_mensuales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_mensuales` (
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único de la sesión mensual (UUID)',
  `numero_sesion` int NOT NULL COMMENT 'Número correlativo de la sesión mensual',
  `fecha` date NOT NULL COMMENT 'Fecha de la sesión mensual',
  `titulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Título o nombre de la sesión',
  `estado` enum('abierta','cerrada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'abierta' COMMENT 'Estado de la sesión: abierta (en curso) o cerrada (finalizada)',
  `fecha_apertura` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de apertura de la sesión',
  `fecha_cierre` datetime DEFAULT NULL COMMENT 'Fecha y hora de cierre de la sesión',
  `usuario_cierre` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que ejecutó el cierre de sesión',
  `acta_cierre_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de cierre',
  `total_recaudado` decimal(12,2) DEFAULT '0.00' COMMENT 'Total recaudado en la sesión',
  `total_desembolsado` decimal(12,2) DEFAULT '0.00' COMMENT 'Total desembolsado en la sesión',
  `saldo_caja` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo final de caja (recaudado - desembolsado)',
  PRIMARY KEY (`id_sesion`),
  KEY `usuario_cierre` (`usuario_cierre`),
  CONSTRAINT `sesiones_mensuales_ibfk_1` FOREIGN KEY (`usuario_cierre`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesiones mensuales de cobro y check-in ÔÇö n├║cleo operativo del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_mensuales`
--

LOCK TABLES `sesiones_mensuales` WRITE;
/*!40000 ALTER TABLE `sesiones_mensuales` DISABLE KEYS */;
INSERT INTO `sesiones_mensuales` VALUES ('98f6acb9-49b1-4760-9b90-f224dcb1d654',2,'2026-06-07','Sesion Extraordinario','cerrada','2026-06-06 20:04:21','2026-06-06 21:19:00','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','acta_sesion_2_20260606.html',1040.00,0.00,1040.00),('b1dfe7b2-5758-4426-81e0-f22b51d9b29e',1,'2026-06-06','Primera Sesión - Julio 2026','cerrada','2026-06-06 14:16:51','2026-06-06 20:02:04','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','acta_sesion_1_20260606.html',55.00,0.00,55.00),('d7702800-27b1-461c-84bc-2587d460a6ef',3,'2026-06-07','Sesión Extraordinaria','cerrada','2026-06-06 21:20:40','2026-06-06 22:21:51','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_3_20260606.html',155.00,0.00,155.00);
/*!40000 ALTER TABLE `sesiones_mensuales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socios`
--

DROP TABLE IF EXISTS `socios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `socios` (
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del socio (UUID)',
  `cedula` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cédula de identidad ecuatoriana (10 dígitos, dígito verificador)',
  `apellido1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primer apellido (mayúsculas)',
  `apellido2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Segundo apellido (mayúsculas)',
  `nombre1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primer nombre (mayúsculas)',
  `nombre2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Segundo nombre (mayúsculas)',
  `fecha_nacimiento` date NOT NULL COMMENT 'Fecha de nacimiento',
  `genero` enum('masculino','femenino') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Género del socio',
  `estado_civil` enum('soltero','casado','divorciado','viudo','union_libre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Dirección de residencia',
  `telefono` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de teléfono fijo',
  `celular` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número de celular',
  `correo_electronico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correo electrónico (validado con PIN 6 dígitos)',
  `profesion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Profesión u ocupación',
  `foto_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de la fotografía del socio',
  `documento_identidad_anverso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF/JPG del anverso de la cédula',
  `documento_identidad_reverso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF/JPG del reverso de la cédula',
  `estado` enum('pendiente','pre_activo','activo','suspendido','retiro_voluntario','excluido','fallecido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado actual del socio en el ciclo de vida',
  `fecha_ingreso` date NOT NULL COMMENT 'Fecha de solicitud de ingreso',
  `fecha_aprobacion` date DEFAULT NULL COMMENT 'Fecha de aprobación por la Asamblea',
  `numero_acta_aprobacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de acta de la Asamblea que aprobó el ingreso',
  `acta_aprobacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de aprobación',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones generales del socio',
  `fecha_retiro` date DEFAULT NULL COMMENT 'Fecha de retiro voluntario',
  `motivo_retiro` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo del retiro voluntario',
  `fecha_exclusion` date DEFAULT NULL COMMENT 'Fecha de exclusión (Art.14 Estatuto)',
  `motivo_exclusion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de la exclusión',
  `menor_edad` tinyint(1) DEFAULT '0' COMMENT 'Indica si el socio es menor de edad',
  `representante_nombres` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombres del representante legal (menores de edad)',
  `representante_cedula` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cédula del representante legal',
  `representante_telefono` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Teléfono del representante legal',
  `representante_correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Correo del representante legal',
  `representante_documento_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Documento legal del representante (PDF)',
  `hash_integridad` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  PRIMARY KEY (`id_socio`),
  UNIQUE KEY `c├®dula` (`cedula`),
  UNIQUE KEY `correo_electr├│nico` (`correo_electronico`),
  KEY `idx_socios_c├®dula` (`cedula`),
  KEY `idx_socios_correo` (`correo_electronico`),
  KEY `idx_socios_estado` (`estado`),
  KEY `idx_socios_apellidos` (`apellido1`,`apellido2`),
  KEY `idx_socios_nombres` (`nombre1`,`nombre2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de socios de la Caja de Ahorro con datos personales, estado y representaci├│n';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socios`
--

LOCK TABLES `socios` WRITE;
/*!40000 ALTER TABLE `socios` DISABLE KEYS */;
INSERT INTO `socios` VALUES ('00e16557-e3cf-4738-8516-7f3fb6ddb96d','1755566677','SANCHEZ','TORRES','PEDRO','ANDRÉS','1992-11-30','masculino','soltero','Av. Central 789','023456789','0987654321','pedro.sanchez@email.com','Profesor',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('32d4ffda-eec7-4299-885f-f320557da01e','1766677788','VARGAS','CRUZ','CARLOS','MANUEL','1975-07-18','masculino','casado','Av. Sur 654','025678901','0965432109','carlos.vargas@email.com','Abogado',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('392cced6-d52b-464b-9829-51aa9ce12468','1002003000','CARRANCO','GONZALEZ','GAVINO','ALEXANDER','1983-01-19','masculino',NULL,'IBARRA','062640879','0996755645','gcarranco@hotmail.com','Ingeniero',NULL,NULL,NULL,'activo','2026-06-06','2026-06-06','1','acta_aprobacion_392cced6.pdf',NULL,NULL,NULL,NULL,NULL,0,'','','','',NULL,'0364ba8f4152507772a204a62c1b724890ca09dbe96fd165b1900decb0274c71','2026-06-06 20:39:04'),('5afb15ad-ced5-431b-9fc2-970cf4919433','1712345678','MARTÍNEZ','GÓMEZ','JUAN','CARLOS','1990-05-15','masculino','soltero','Av. Principal 123','022345678','0991234567','juan.martinez@email.com','Ingeniero',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('9e52d148-927b-4784-b290-b8d9f9b1c35f','1787654321','LÓPEZ','RAMOS','MARÍA','ELENA','1985-08-22','femenino','casado','Calle Secundaria 456','022987654','0999876543','maria.lopez@email.com','Licenciada',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','1766611122','CORDERO','QUIMI','LUIS','FELIPE','1982-09-05','masculino','casado','Av. Occidental 147','027890123','0943210987','luis.cordero@email.com','Contador',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'8652dfff92bd9c2f868aeeaa6df9b318609755b2d466e92e9e7a97bb71cb323e','2026-06-06 14:16:51'),('c26b7a29-755b-4665-8912-397c05d48a27','1711199900','ZAMBRANO','ROSALES','MÓNICA','LISBETH','1995-01-25','femenino','union_libre','Calle Oriente 987','026789012','0954321098','monica.zambrano@email.com','Arquitecto',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('caaf8155-4c10-4e84-aa7b-ba4183906421','1722233344','RAMÍREZ','VÉLEZ','ANA','LUCÍA','1988-03-10','femenino','divorciado','Calle Norte 321','024567890','0976543210','ana.ramirez@email.com','Médico',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51');
/*!40000 ALTER TABLE `socios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_retiro`
--

DROP TABLE IF EXISTS `solicitudes_retiro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_retiro` (
  `id_solicitud` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID de la solicitud',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio solicitante',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto solicitado',
  `motivo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Motivo del retiro',
  `estado` enum('pendiente','aprobado','rechazado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado de la solicitud',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud',
  `fecha_respuesta` datetime DEFAULT NULL COMMENT 'Fecha de aprobación/rechazo',
  `usuario_respuesta` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que aprobó/rechazó',
  `id_cobro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cobro generado al aprobar',
  PRIMARY KEY (`id_solicitud`),
  KEY `id_socio` (`id_socio`),
  CONSTRAINT `solicitudes_retiro_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de retiro de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_retiro`
--

LOCK TABLES `solicitudes_retiro` WRITE;
/*!40000 ALTER TABLE `solicitudes_retiro` DISABLE KEYS */;
INSERT INTO `solicitudes_retiro` VALUES ('6565f377-9e5e-4fb0-a40f-a814883666f3','00e16557-e3cf-4738-8516-7f3fb6ddb96d',10.00,'Urgencia económica.','aprobado','2026-06-06 19:13:54','2026-06-06 23:06:07','ce86e169-fa0a-468d-bb04-ca7b8c7a5291','9bccf12e-2920-42df-990c-0a3f62c64bef');
/*!40000 ALTER TABLE `solicitudes_retiro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador único del usuario (UUID)',
  `nombres` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombres del usuario',
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Apellidos del usuario',
  `cedula` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cédula de identidad ecuatoriana',
  `correo_electronico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correo electrónico del usuario',
  `telefono` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de teléfono',
  `nombre_usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de usuario para inicio de sesión',
  `contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash bcrypt de la contraseña',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Indica si el usuario está activo en el sistema',
  `_2fa_obligatorio` tinyint(1) DEFAULT '0' COMMENT 'Indica si el 2FA es obligatorio para este usuario',
  `_2fa_activo` tinyint(1) DEFAULT '0' COMMENT 'Indica si el 2FA está actualmente activo',
  `bloqueado_hasta` datetime DEFAULT NULL COMMENT 'Fecha/hasta cuándo está bloqueado (3 intentos fallidos)',
  `intentos_fallidos` int DEFAULT '0' COMMENT 'Contador de intentos fallidos de inicio de sesión',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del registro',
  `fecha_ultimo_acceso` datetime DEFAULT NULL COMMENT 'Fecha y hora del último inicio de sesión exitoso',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `c├®dula` (`cedula`),
  UNIQUE KEY `correo_electr├│nico` (`correo_electronico`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `idx_usuarios_c├®dula` (`cedula`),
  KEY `idx_usuarios_correo` (`correo_electronico`),
  KEY `idx_usuarios_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema con credenciales de acceso y control 2FA';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('1673019a-c66d-4bb8-9158-1729fa6b064a','Gavino','Carranco','1002003000','gavinocg@gmail.com','0996755645','gcarranco','$2y$12$QkuzAcoAFQ7C9f5GMMeGS.1smyFeCvsvJeESlsmkB00oBEfzlLYjO',1,0,0,NULL,0,'2026-06-06 16:38:03','2026-06-09 14:36:29'),('516363c5-c79a-4491-83b4-b8303ce1f286','Tesorero','Caja','1003560438','gcarranco@hotmail.com','','tesorero','$2y$12$/gRI9LwajMIzc8e/NYxO6.hCsUvfbH3c.yxuEKpkpRT7AXoL2ojxe',1,0,0,NULL,0,'2026-06-06 18:23:36','2026-06-09 14:38:40'),('6600ae1d-e99d-4986-b337-0741de09df84','CARLOS MANUEL','VARGAS CRUZ','1766677788','carlos.vargas@email.com','','1766677788','$2y$12$wx0/HsCyDTfUlKWE8twT/uRPv2o/MEOWXbadf9piFa6So5g39Trie',1,0,0,NULL,0,'2026-06-06 19:35:51','2026-06-06 19:44:55'),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291','Admin','Sistema','1002606083','admin@caja.test','0999999999','admin','$2y$12$IP4hst3.3yCimzqw/bO8JOYscRjkeQADlesFcttSetTnxNCRY.N8G',1,0,0,NULL,0,'2026-06-06 14:16:51','2026-06-09 14:37:35');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'caja_ahorro_pujota'
--

--
-- Dumping routines for database 'caja_ahorro_pujota'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-09 14:57:52
