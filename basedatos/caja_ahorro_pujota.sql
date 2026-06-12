-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: caja_ahorro_pujota
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
  `id_amortizacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la amortizaci+¦n (UUID)',
  `id_credito` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al cr+®dito asociado',
  `numero_cuota` int NOT NULL COMMENT 'N+¦mero de cuota (1, 2, 3...)',
  `fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento de la cuota',
  `capital` decimal(12,2) NOT NULL COMMENT 'Porci+¦n de capital de la cuota',
  `interes` decimal(12,2) NOT NULL COMMENT 'Porci+¦n de inter+®s de la cuota',
  `total` decimal(12,2) NOT NULL COMMENT 'Total de la cuota (capital + inter+®s)',
  `saldo_restante` decimal(12,2) NOT NULL COMMENT 'Saldo de capital pendiente despu+®s de esta cuota',
  `estado` enum('pendiente','pagada','vencida') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado de la cuota',
  `id_cobro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al cobro cuando la cuota es pagada',
  PRIMARY KEY (`id_amortizacion`),
  KEY `idx_amortizaciones_crÔö£-«dito` (`id_credito`),
  KEY `idx_amortizaciones_estado` (`estado`),
  CONSTRAINT `amortizaciones_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id_credito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de amortizaciÔö£Ôöén de crÔö£-«ditos +ö+ç+Â cuotas generadas segÔö£Ôòæn mÔö£-«todo de interÔö£-«s';
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
  `id_archivo` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del archivo (UUID)',
  `nombre_original` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre original del archivo subido',
  `nombre_archivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre interno en disco (UUID + extensi+¦n)',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo MIME del archivo',
  `tamano` bigint NOT NULL COMMENT 'Tama+¦o en bytes',
  `extension` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Extensi+¦n del archivo (pdf, jpg, png, etc)',
  `ruta` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ruta relativa desde storage/archivos/',
  `hash_sha256` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 del contenido del archivo',
  `entidad_tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre de la tabla o m+¦dulo asociado (socio, credito, multa, etc)',
  `entidad_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UUID del registro asociado en la entidad',
  `subdirectorio` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'Subdirectorio dentro de storage/archivos/',
  `id_usuario_subio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que subi+¦ el archivo',
  `fecha_subida` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de subida del archivo',
  PRIMARY KEY (`id_archivo`),
  KEY `id_usuario_subio` (`id_usuario_subio`),
  KEY `idx_archivos_entidad` (`entidad_tipo`,`entidad_id`),
  KEY `idx_archivos_hash` (`hash_sha256`),
  CONSTRAINT `archivos_ibfk_1` FOREIGN KEY (`id_usuario_subio`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='GestiÔö£Ôöén centralizada de archivos ? metadatos en BD, archivos fuera del public root';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archivos`
--

LOCK TABLES `archivos` WRITE;
/*!40000 ALTER TABLE `archivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `archivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencias`
--

DROP TABLE IF EXISTS `asistencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencias` (
  `id_asistencia` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del registro de asistencia (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio que asiste',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesi+¦n mensual',
  `tipo` enum('a_tiempo','retraso_10min','retraso_30min','falta') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de asistencia registrada',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificaci+¦n presentada por el socio (opcional)',
  `justificacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificaci+¦n',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificaci+¦n fue aprobada',
  `usuario_registra` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que registr+¦ la asistencia',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_asistencia`),
  UNIQUE KEY `id_socio` (`id_socio`,`id_sesion`),
  KEY `id_sesiÔö£Ôöén` (`id_sesion`),
  KEY `usuario_registra` (`usuario_registra`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `asistencias_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `asistencias_ibfk_3` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de asistencia a sesiones mensuales con tipo y justificaciÔö£Ôöén';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencias`
--

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;
INSERT INTO `asistencias` VALUES ('080791c0-36bd-4a9e-925a-b2951a38eb0c','392cced6-d52b-464b-9829-51aa9ce12468','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:09'),('22f382c4-ff1a-473d-a6bb-8ba4f10ff4bd','392cced6-d52b-464b-9829-51aa9ce12468','31badd94-726c-4f6b-887b-856291cbf36e','retraso_10min',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:56:58'),('298550b5-c0b0-4c51-b128-169628827732','9e52d148-927b-4784-b290-b8d9f9b1c35f','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:31'),('2a280050-bdb3-4f5e-aa22-4f1bcae42064','c26b7a29-755b-4665-8912-397c05d48a27','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:34'),('39c16798-7060-46b4-a444-2ad73660137a','32d4ffda-eec7-4299-885f-f320557da01e','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:33'),('3e390606-d8e0-480d-b390-aa51398967d3','5afb15ad-ced5-431b-9fc2-970cf4919433','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:31'),('484657ed-438a-485e-acee-19f3b880650a','caaf8155-4c10-4e84-aa7b-ba4183906421','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:11'),('52b4a250-7378-494a-b7d6-64a3d50b5b84','c26b7a29-755b-4665-8912-397c05d48a27','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:13'),('5a9c2c14-39e7-40f2-a822-8f18f017a9c7','9e52d148-927b-4784-b290-b8d9f9b1c35f','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:42'),('5c77ae64-116e-4044-a246-1cdc13336035','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:44'),('648758c0-4748-4361-9463-b7b430da95c6','9e52d148-927b-4784-b290-b8d9f9b1c35f','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:10'),('6c0b0825-de7f-427d-89b7-6bd94322b47c','5afb15ad-ced5-431b-9fc2-970cf4919433','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:43'),('80e70294-13c2-49ce-8054-dae50f42991c','392cced6-d52b-464b-9829-51aa9ce12468','cefa9922-8910-4842-84a0-cf465fe7db3f','retraso_30min',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:40'),('85ce16bf-f769-4727-9f11-c99a93477eed','caaf8155-4c10-4e84-aa7b-ba4183906421','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:44'),('88d04845-a0ce-4edb-ae10-01de2e8ed057','5afb15ad-ced5-431b-9fc2-970cf4919433','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:11'),('8f8dc4de-ff82-433f-a426-7e06aea2e4e0','caaf8155-4c10-4e84-aa7b-ba4183906421','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:32'),('a77211ce-d2e8-4689-a7c0-c737471c9d31','c26b7a29-755b-4665-8912-397c05d48a27','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:48'),('b6434035-6c67-455f-9601-b2148c2729a5','32d4ffda-eec7-4299-885f-f320557da01e','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:45'),('b8fde2fd-6eee-4bf0-a537-25f97e3a6ac0','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:30'),('c6d173c3-6eb4-4fbb-a17f-b664f756f633','32d4ffda-eec7-4299-885f-f320557da01e','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:12'),('ce6f61b2-04d9-438c-9045-f73724580cfc','00e16557-e3cf-4738-8516-7f3fb6ddb96d','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:12'),('ed709a20-5c80-4dbb-8976-eac2b306b437','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','918649f3-988e-4e16-a371-73c7988300e3','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:04:10'),('f067d8a2-d593-4ec9-b7f4-af5b17160304','00e16557-e3cf-4738-8516-7f3fb6ddb96d','31badd94-726c-4f6b-887b-856291cbf36e','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 17:03:33'),('f31ee32d-8ecc-4dd6-baa0-d7725b669f58','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cefa9922-8910-4842-84a0-cf465fe7db3f','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 16:47:41');
/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caja_movimientos`
--

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

--
-- Dumping data for table `caja_movimientos`
--

LOCK TABLES `caja_movimientos` WRITE;
/*!40000 ALTER TABLE `caja_movimientos` DISABLE KEYS */;
INSERT INTO `caja_movimientos` VALUES ('0813b0b5-67cb-4196-977c-ab8081228cfd','31badd94-726c-4f6b-887b-856291cbf36e','392cced6-d52b-464b-9829-51aa9ce12468','920fa280-5ec4-4526-ab7b-374ade25b1cb','ingreso','Multa por Retraso 10min - Sesion #2 del 26/07/2026 - pagada en Sesion #2','multa',1.00,25.00,26.00,'2026-06-11 16:59:55'),('39ef2561-3604-4f9f-9917-0b37cd73d2b8','31badd94-726c-4f6b-887b-856291cbf36e','392cced6-d52b-464b-9829-51aa9ce12468','f12fd18c-67f3-450b-bd20-becfafaf0fec','ingreso','Multa por Retraso 30min - Sesion #1 del 28/06/2026 - pagada en Sesion #2','multa',5.00,20.00,25.00,'2026-06-11 16:59:54'),('48e2fe27-546a-4060-ab5d-eb6059c886b8','cefa9922-8910-4842-84a0-cf465fe7db3f','392cced6-d52b-464b-9829-51aa9ce12468','9e39b516-77f5-47f4-85ac-f5cf32d1a34f','ingreso','Cuota mensual - 1002003000 - Sesion #1','aporte_obligatorio',10.00,0.00,10.00,'2026-06-11 16:47:53'),('93deb6f0-4cf5-4471-a001-becc261db331','918649f3-988e-4e16-a371-73c7988300e3','392cced6-d52b-464b-9829-51aa9ce12468','337202dc-6218-47f6-b70c-676f9dc9aa97','ingreso','Cuota mensual - 1002003000 - Sesion #3','aporte_obligatorio',10.00,26.00,36.00,'2026-06-11 17:05:39'),('ee8db628-b69b-4d8a-b194-9eea16e259f7','31badd94-726c-4f6b-887b-856291cbf36e','392cced6-d52b-464b-9829-51aa9ce12468','9db3d287-ebb0-4640-9a08-84d9e196ae8a','ingreso','Cuota mensual - 1002003000 - Sesion #2','aporte_obligatorio',10.00,10.00,20.00,'2026-06-11 16:59:53');
/*!40000 ALTER TABLE `caja_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cantones`
--

DROP TABLE IF EXISTS `cantones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cantones` (
  `id_canton` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador num+®rico del cant+¦n',
  `id_provincia` int NOT NULL COMMENT 'FK a la provincia',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del cant+¦n',
  PRIMARY KEY (`id_canton`),
  KEY `id_provincia` (`id_provincia`),
  CONSTRAINT `cantones_ibfk_1` FOREIGN KEY (`id_provincia`) REFERENCES `provincias` (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CatÔö£+¡logo de cantones por provincia';
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
  `id_capital_inversion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador unico del registro de capital de inversion (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio',
  `saldo` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo disponible para invertir',
  `fecha_ultimo_movimiento` datetime DEFAULT NULL COMMENT 'Fecha del ultimo movimiento',
  PRIMARY KEY (`id_capital_inversion`),
  UNIQUE KEY `id_socio` (`id_socio`),
  CONSTRAINT `capital_inversion_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Capital de inversion del socio ÔÇö independiente de la cuenta de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `capital_inversion`
--

LOCK TABLES `capital_inversion` WRITE;
/*!40000 ALTER TABLE `capital_inversion` DISABLE KEYS */;
/*!40000 ALTER TABLE `capital_inversion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catastro_entidades_publicas`
--

DROP TABLE IF EXISTS `catastro_entidades_publicas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catastro_entidades_publicas` (
  `id_entidad` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador num+®rico de la entidad',
  `ruc` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC de la entidad p+¦blica',
  `razon_social` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Raz+¦n social de la entidad',
  PRIMARY KEY (`id_entidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catastro de entidades pÔö£Ôòæblicas para registro de socios';
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
  `id_cobro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del cobro (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio que realiza el pago',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesi+¦n mensual donde se registra el cobro',
  `tipo` enum('aporte_obligatorio','aporte_excedente','cuota_credito','multa','inversion','interes','desembolso','otro','deposito_capital_inversion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de cobro o transaccion',
  `id_referencia` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID de referencia seg+¦n el tipo (id_amortizaci+¦n, id_multa, etc.)',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto cobrado',
  `medio_pago` enum('efectivo','transferencia','compensacion','digital') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comprobante_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del comprobante de pago',
  `hash_integridad` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro',
  `usuario_registra` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Usuario que registr+¦ el cobro',
  `anulado` tinyint(1) DEFAULT '0' COMMENT 'Indica si el cobro fue anulado',
  `motivo_anulacion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de la anulaci+¦n',
  `fecha_anulacion` datetime DEFAULT NULL COMMENT 'Fecha de anulaci+¦n',
  `usuario_anula` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que anul+¦ el cobro',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro del cobro',
  PRIMARY KEY (`id_cobro`),
  KEY `usuario_registra` (`usuario_registra`),
  KEY `idx_cobros_socio` (`id_socio`),
  KEY `idx_cobros_tipo` (`tipo`),
  KEY `idx_cobros_sesiÔö£Ôöén` (`id_sesion`),
  KEY `idx_cobros_fecha` (`fecha_registro`),
  CONSTRAINT `cobros_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `cobros_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `cobros_ibfk_3` FOREIGN KEY (`usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de cobros +ö+ç+Â transacciones financieras diarias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cobros`
--

LOCK TABLES `cobros` WRITE;
/*!40000 ALTER TABLE `cobros` DISABLE KEYS */;
INSERT INTO `cobros` VALUES ('337202dc-6218-47f6-b70c-676f9dc9aa97','392cced6-d52b-464b-9829-51aa9ce12468','918649f3-988e-4e16-a371-73c7988300e3','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'d1fe587e6981103f31a98a98423fe6c80dce5aa3544d452285aeadaa827897b7','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 17:05:38'),('920fa280-5ec4-4526-ab7b-374ade25b1cb','392cced6-d52b-464b-9829-51aa9ce12468','31badd94-726c-4f6b-887b-856291cbf36e','multa','f558521b-c934-42c3-9e68-540041093af9',1.00,'efectivo',NULL,'446692097403ab8a7e6efbe87eec8ea7acf70105fcf6ed3c8f9bf7673cea7b71','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 16:59:54'),('9db3d287-ebb0-4640-9a08-84d9e196ae8a','392cced6-d52b-464b-9829-51aa9ce12468','31badd94-726c-4f6b-887b-856291cbf36e','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'cc88d1f8ee22151e52faeaebfe581deeb2a168f369a64afdf3e23f5fd3965364','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 16:59:52'),('9e39b516-77f5-47f4-85ac-f5cf32d1a34f','392cced6-d52b-464b-9829-51aa9ce12468','cefa9922-8910-4842-84a0-cf465fe7db3f','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'4701964be26690007380609d9e8f887bc5cfbb2c55b521c78e7d6917585a9a99','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 16:47:52'),('f12fd18c-67f3-450b-bd20-becfafaf0fec','392cced6-d52b-464b-9829-51aa9ce12468','31badd94-726c-4f6b-887b-856291cbf36e','multa','83c3991c-4a37-40e6-84cc-d4af0386b6fa',5.00,'efectivo',NULL,'ed3981b24a74286a4f2ccc033d857ad982c8c562c55c211361e8a79de19db82f','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 16:59:53');
/*!40000 ALTER TABLE `cobros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `creditos`
--

DROP TABLE IF EXISTS `creditos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `creditos` (
  `id_credito` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del cr+®dito (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio solicitante',
  `id_producto` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al producto financiero asociado',
  `id_sesion_aprobacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesi+¦n donde se aprob+¦ el cr+®dito',
  `monto_solicitado` decimal(12,2) NOT NULL COMMENT 'Monto solicitado por el socio',
  `monto_aprobado` decimal(12,2) DEFAULT NULL COMMENT 'Monto aprobado por la Asamblea',
  `plazo_meses` int NOT NULL COMMENT 'Plazo del cr+®dito en meses',
  `tasa_interes` decimal(5,2) NOT NULL COMMENT 'Tasa de inter+®s anual aplicada',
  `metodo_interes` enum('simple','frances','aleman') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Metodo de interes aplicado a este credito',
  `destino` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Destino o prop+¦sito del cr+®dito',
  `estado` enum('ingresado','pendiente','aprobado','legalizado','desembolsado','rechazado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ingresado' COMMENT 'Estado actual de la solicitud de credito',
  `acta_aprobacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de aprobaci+¦n',
  `fecha_solicitud` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de solicitud del cr+®dito',
  `fecha_aprobacion` datetime DEFAULT NULL COMMENT 'Fecha de aprobaci+¦n',
  `fecha_desembolso` datetime DEFAULT NULL COMMENT 'Fecha de desembolso del cr+®dito',
  `usuario_aprueba` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que aprob+¦ el cr+®dito',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificacion de rechazo o puesta en espera',
  PRIMARY KEY (`id_credito`),
  KEY `id_producto` (`id_producto`),
  KEY `id_sesiÔö£Ôöén_aprobaciÔö£Ôöén` (`id_sesion_aprobacion`),
  KEY `usuario_aprueba` (`usuario_aprueba`),
  KEY `idx_crÔö£-«ditos_estado` (`estado`),
  KEY `idx_crÔö£-«ditos_socio` (`id_socio`),
  CONSTRAINT `creditos_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `creditos_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_financieros` (`id_producto`),
  CONSTRAINT `creditos_ibfk_3` FOREIGN KEY (`id_sesion_aprobacion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `creditos_ibfk_4` FOREIGN KEY (`usuario_aprueba`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes y desembolsos de crÔö£-«ditos de los socios';
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
  `id_cuenta_ahorro` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la cuenta de ahorro (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio propietario de la cuenta',
  `saldo_obligatorio` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo del aporte obligatorio (USD 10/mes)',
  `saldo_excedente` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo de aportes voluntarios/excedentes',
  `saldo_disponible` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo total disponible para retiro seg+¦n reglas',
  `fecha_ultimo_movimiento` datetime DEFAULT NULL COMMENT 'Fecha del +¦ltimo movimiento registrado',
  PRIMARY KEY (`id_cuenta_ahorro`),
  UNIQUE KEY `id_socio` (`id_socio`),
  CONSTRAINT `cuentas_ahorro_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuentas de ahorro de los socios +ö+ç+Â capital separado de inversiones';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_ahorro`
--

LOCK TABLES `cuentas_ahorro` WRITE;
/*!40000 ALTER TABLE `cuentas_ahorro` DISABLE KEYS */;
INSERT INTO `cuentas_ahorro` VALUES ('35091853-e6e7-4dcd-a292-5c97229a972a','32d4ffda-eec7-4299-885f-f320557da01e',0.00,0.00,0.00,NULL),('72f76cda-20e3-460a-8533-fab738f82b92','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a',0.00,0.00,10.00,'2026-06-11 16:00:35'),('7638e37c-72e9-48ba-bfba-0f885133e41a','caaf8155-4c10-4e84-aa7b-ba4183906421',0.00,0.00,0.00,NULL),('95dba24c-5c65-4262-8053-e783ea1c0621','5afb15ad-ced5-431b-9fc2-970cf4919433',0.00,0.00,0.00,NULL),('a09b3d26-dff6-4218-8a1a-f9edc4f10cc7','00e16557-e3cf-4738-8516-7f3fb6ddb96d',0.00,0.00,0.00,NULL),('a8ecf0a7-776c-42dc-b573-1e4433d16989','c26b7a29-755b-4665-8912-397c05d48a27',0.00,0.00,0.00,NULL),('ab9d9a6b-f5dd-4850-95fc-c98de0635c18','9e52d148-927b-4784-b290-b8d9f9b1c35f',0.00,0.00,0.00,NULL),('c3df6ac3-fc84-4376-8228-1aaabb9beea0','392cced6-d52b-464b-9829-51aa9ce12468',30.00,0.00,50.00,'2026-06-11 17:05:38');
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
  `id_credito` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al cr+®dito',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio garante',
  `tipo_garante` enum('fiador_solidario','prendario','hipotecario') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fiador_solidario' COMMENT 'Tipo de garant+¡a',
  `monto_garantizado` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto garantizado',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro',
  PRIMARY KEY (`id_garante`),
  KEY `id_socio` (`id_socio`),
  KEY `garantes_ibfk_1` (`id_credito`),
  CONSTRAINT `garantes_ibfk_1` FOREIGN KEY (`id_credito`) REFERENCES `creditos` (`id_credito`),
  CONSTRAINT `garantes_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Garantes de crÔö£-«ditos';
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
  `id_operacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la operaci+¦n (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio asociado a la operaci+¦n',
  `tipo_operacion` enum('aporte_obligatorio','aporte_excedente','retiro_ahorro','desembolso_credito','pago_cuota','pago_multa','inversion_apertura','inversion_retiro','interes_ganado','interes_pagado','cierre_sesion','anulacion','deposito_capital_inversion','retiro_capital_inversion','anulacion_inversion') COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto de la operaci+¦n',
  `saldo_anterior` decimal(12,2) DEFAULT NULL COMMENT 'Saldo anterior a la operaci+¦n',
  `saldo_posterior` decimal(12,2) DEFAULT NULL COMMENT 'Saldo posterior a la operaci+¦n',
  `id_referencia` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID de referencia a la entidad origen',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK a la sesi+¦n mensual',
  `id_usuario_registra` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que registr+¦ la operaci+¦n',
  `comprobante_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del comprobante',
  `hash_integridad` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro (inmodificable)',
  `ip_registro` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Direcci+¦n IP desde donde se registr+¦ la operaci+¦n',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del registro',
  PRIMARY KEY (`id_operacion`),
  KEY `id_sesiÔö£Ôöén` (`id_sesion`),
  KEY `id_usuario_registra` (`id_usuario_registra`),
  KEY `idx_historial_socio` (`id_socio`),
  KEY `idx_historial_tipo` (`tipo_operacion`),
  KEY `idx_historial_fecha` (`fecha_registro`),
  CONSTRAINT `historial_operaciones_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `historial_operaciones_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`),
  CONSTRAINT `historial_operaciones_ibfk_3` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial inmodificable de operaciones financieras +ö+ç+Â solo inserciÔö£Ôöén, sin DELETE/UPDATE';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historial_operaciones`
--

LOCK TABLES `historial_operaciones` WRITE;
/*!40000 ALTER TABLE `historial_operaciones` DISABLE KEYS */;
INSERT INTO `historial_operaciones` VALUES ('6df9a1ee-f87f-484a-9efb-f77e1b80f045','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'337202dc-6218-47f6-b70c-676f9dc9aa97','918649f3-988e-4e16-a371-73c7988300e3','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 17:05:38'),('7116532e-666d-46b4-b9cc-0d4206fecce8','392cced6-d52b-464b-9829-51aa9ce12468','pago_multa',1.00,NULL,NULL,'920fa280-5ec4-4526-ab7b-374ade25b1cb','31badd94-726c-4f6b-887b-856291cbf36e','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 16:59:54'),('b47b5817-03bb-4eb8-94de-3dd1cbd33595','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'9db3d287-ebb0-4640-9a08-84d9e196ae8a','31badd94-726c-4f6b-887b-856291cbf36e','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 16:59:52'),('ce3892cf-7f50-419e-b1a9-959c1bfeac51','392cced6-d52b-464b-9829-51aa9ce12468','pago_multa',5.00,NULL,NULL,'f12fd18c-67f3-450b-bd20-becfafaf0fec','31badd94-726c-4f6b-887b-856291cbf36e','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 16:59:53'),('d9fca44a-c35f-4213-885e-117d5eba9610','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'9e39b516-77f5-47f4-85ac-f5cf32d1a34f','cefa9922-8910-4842-84a0-cf465fe7db3f','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 16:47:52');
/*!40000 ALTER TABLE `historial_operaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inversiones`
--

DROP TABLE IF EXISTS `inversiones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inversiones` (
  `id_inversion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la inversi+¦n (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio inversionista',
  `id_producto` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al producto de inversi+¦n',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto invertido',
  `plazo_meses` int NOT NULL COMMENT 'Plazo de la inversi+¦n en meses',
  `tasa_interes` decimal(5,2) NOT NULL COMMENT 'Tasa de inter+®s anual aplicada',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha de inicio de la inversi+¦n',
  `fecha_vencimiento` date NOT NULL COMMENT 'Fecha de vencimiento',
  `rendimiento_proyectado` decimal(12,2) DEFAULT NULL COMMENT 'Rendimiento proyectado al vencimiento',
  `estado` enum('activa','vencida','retiro_anticipado','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `notificado_devolucion` tinyint(1) DEFAULT '0' COMMENT 'Indica si se notific+¦ la pr+¦xima devoluci+¦n',
  `destino_final` enum('capital_inversion','efectivo','transferencia') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'capital_inversion' COMMENT 'Destino al vencimiento: reinversion, efectivo o transferencia',
  `contrato_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del contrato de inversi+¦n',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de registro de la inversi+¦n',
  PRIMARY KEY (`id_inversion`),
  KEY `id_producto` (`id_producto`),
  KEY `idx_inversiones_estado` (`estado`),
  KEY `idx_inversiones_socio` (`id_socio`),
  CONSTRAINT `inversiones_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `inversiones_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_financieros` (`id_producto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Inversiones a plazo fijo de los socios +ö+ç+Â capital separado de cuenta de ahorro';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inversiones`
--

LOCK TABLES `inversiones` WRITE;
/*!40000 ALTER TABLE `inversiones` DISABLE KEYS */;
/*!40000 ALTER TABLE `inversiones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `multas`
--

DROP TABLE IF EXISTS `multas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `multas` (
  `id_multa` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la multa (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio multado',
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK a la sesi+¦n donde se gener+¦ la multa',
  `tipo` enum('retraso_10min','retraso_30min','inasistencia','mora_credito','cuota_impaga','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto de la multa en USD',
  `estado` enum('activa','anulada','impugnada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa' COMMENT 'Estado de la multa',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificaci+¦n presentada por el socio',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificaci+¦n fue aprobada',
  `justificacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificaci+¦n',
  `fecha_generacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de generaci+¦n de la multa',
  PRIMARY KEY (`id_multa`),
  UNIQUE KEY `uk_socio_sesion_tipo` (`id_socio`,`id_sesion`,`tipo`),
  KEY `id_sesiÔö£Ôöén` (`id_sesion`),
  KEY `idx_multas_socio` (`id_socio`),
  CONSTRAINT `multas_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `multas_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multas generadas por inasistencia, retraso o mora +ö+ç+Â base legal Art.11 Estatuto';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multas`
--

LOCK TABLES `multas` WRITE;
/*!40000 ALTER TABLE `multas` DISABLE KEYS */;
INSERT INTO `multas` VALUES ('0947b2f1-ff5f-43eb-aee5-b7b7336b7829','c26b7a29-755b-4665-8912-397c05d48a27','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('0a1c17f3-4797-4f29-879b-23d52645bffa','caaf8155-4c10-4e84-aa7b-ba4183906421','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('0bf72d4e-b8c9-4d9e-83ea-c09924105e62','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('293f4d4f-53ec-4cef-9215-6cda020392f6','32d4ffda-eec7-4299-885f-f320557da01e','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('3bce3ead-eac8-4677-ac23-751dbaaf2f2b','32d4ffda-eec7-4299-885f-f320557da01e','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('3d223ef1-38f5-488b-8442-4236aaff1d44','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('50f286c0-20d4-4256-a96f-78dd4f6d7ec8','5afb15ad-ced5-431b-9fc2-970cf4919433','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('66103248-da2b-4c87-8df9-ba0f2048e9ea','32d4ffda-eec7-4299-885f-f320557da01e','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('7e86bdb1-5264-41f8-b31e-e5e794fd7889','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('83c3991c-4a37-40e6-84cc-d4af0386b6fa','392cced6-d52b-464b-9829-51aa9ce12468','cefa9922-8910-4842-84a0-cf465fe7db3f','retraso_30min',5.00,'activa',NULL,0,NULL,'2026-06-11 16:55:57'),('894bf270-6b7a-46e2-8e5a-ad6ddbb54c3c','9e52d148-927b-4784-b290-b8d9f9b1c35f','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('8f170023-d342-4a42-8ea0-a47bba1ac481','00e16557-e3cf-4738-8516-7f3fb6ddb96d','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('938c3af6-360f-4bd3-a044-fdc04c255be9','5afb15ad-ced5-431b-9fc2-970cf4919433','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('b04edad4-b4ae-4705-b1b9-1aa0b321add2','00e16557-e3cf-4738-8516-7f3fb6ddb96d','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('b9c1bedd-cebc-4cac-a617-1a61acd16f75','c26b7a29-755b-4665-8912-397c05d48a27','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('cc38020c-9673-4190-bc8a-311bf0b3975a','c26b7a29-755b-4665-8912-397c05d48a27','31badd94-726c-4f6b-887b-856291cbf36e','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:03:43'),('cd5f773d-f896-4388-b4b0-1b7d0f523a95','caaf8155-4c10-4e84-aa7b-ba4183906421','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('d29f79d6-073b-4e18-994d-118768a75c21','9e52d148-927b-4784-b290-b8d9f9b1c35f','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('d7e75778-0d7a-4f4e-9910-4a419e7548c0','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('db7f23d3-cf2e-463e-b8b6-22946a90c0e7','5afb15ad-ced5-431b-9fc2-970cf4919433','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('e2356707-cb49-499b-9bbe-8c269f4b5f61','caaf8155-4c10-4e84-aa7b-ba4183906421','918649f3-988e-4e16-a371-73c7988300e3','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 17:05:44'),('ed17bc07-8218-4c11-a289-42c7af29778c','9e52d148-927b-4784-b290-b8d9f9b1c35f','cefa9922-8910-4842-84a0-cf465fe7db3f','cuota_impaga',2.00,'activa',NULL,0,NULL,'2026-06-11 16:48:02'),('f558521b-c934-42c3-9e68-540041093af9','392cced6-d52b-464b-9829-51aa9ce12468','31badd94-726c-4f6b-887b-856291cbf36e','retraso_10min',1.00,'activa',NULL,0,NULL,'2026-06-11 16:59:26');
/*!40000 ALTER TABLE `multas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notificaciones`
--

DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la notificaci+¦n (UUID)',
  `id_usuario` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al usuario destinatario (si es administrativo)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'FK al socio destinatario (si es socio)',
  `tipo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de notificaci+¦n (ej: cobro, cr+®dito, multa)',
  `titulo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'T+¡tulo de la notificaci+¦n',
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Cuerpo del mensaje',
  `leida` tinyint(1) DEFAULT '0' COMMENT 'Indica si el destinatario ley+¦ la notificaci+¦n',
  `enviada_pusher` tinyint(1) DEFAULT '0' COMMENT 'Indica si ya se envi+¦ por Pusher',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creaci+¦n de la notificaci+¦n',
  `fecha_lectura` datetime DEFAULT NULL COMMENT 'Fecha en que se ley+¦ la notificaci+¦n',
  `buzon` enum('entrada','archivadas','papelera') COLLATE utf8mb4_unicode_ci DEFAULT 'entrada' COMMENT 'Buzon: entrada, archivadas, papelera',
  `fecha_eliminacion` datetime DEFAULT NULL COMMENT 'Fecha en que se movio a papelera',
  PRIMARY KEY (`id_notificacion`),
  KEY `idx_notificaciones_usuario` (`id_usuario`),
  KEY `idx_notificaciones_socio` (`id_socio`),
  KEY `idx_notificaciones_leÔö£-ída` (`leida`),
  KEY `idx_buzon` (`buzon`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BuzÔö£Ôöén de notificaciones persistido en BD + envÔö£-ío en tiempo real por Pusher';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `notificaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obligaciones_sesion`
--

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Obligaciones de pago generadas al abrir una sesion ÔÇö calculadas segun fecha de reunion';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obligaciones_sesion`
--

LOCK TABLES `obligaciones_sesion` WRITE;
/*!40000 ALTER TABLE `obligaciones_sesion` DISABLE KEYS */;
INSERT INTO `obligaciones_sesion` VALUES ('01326583-d84f-4bc7-8a03-d15f8cbb65d2','cefa9922-8910-4842-84a0-cf465fe7db3f','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('0349b7b9-9af8-4d4b-a351-7b32f9525760','cefa9922-8910-4842-84a0-cf465fe7db3f','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,1,'9e39b516-77f5-47f4-85ac-f5cf32d1a34f','2026-06-11 16:47:34'),('09821210-d5a8-4778-acf3-7e2f78f4e207','918649f3-988e-4e16-a371-73c7988300e3','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('1a7b4dd2-abfa-4107-ba95-d9ac80f2b9bf','31badd94-726c-4f6b-887b-856291cbf36e','392cced6-d52b-464b-9829-51aa9ce12468','multa','Multa por Retraso 10min - Sesion #2 del 26/07/2026',1.00,'f558521b-c934-42c3-9e68-540041093af9',1,'920fa280-5ec4-4526-ab7b-374ade25b1cb','2026-06-11 16:59:26'),('30efa6a6-cdb8-4a41-af8c-5c3cad77a776','31badd94-726c-4f6b-887b-856291cbf36e','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('3159e09e-ae1a-4210-bfc6-9f5255d3abd4','918649f3-988e-4e16-a371-73c7988300e3','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,1,'337202dc-6218-47f6-b70c-676f9dc9aa97','2026-06-11 17:04:01'),('31657b54-3b52-419e-9bf1-cbf926b1ca93','cefa9922-8910-4842-84a0-cf465fe7db3f','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('37095efa-dda2-42be-a6be-e0fbd0ebfde0','918649f3-988e-4e16-a371-73c7988300e3','5afb15ad-ced5-431b-9fc2-970cf4919433','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'50f286c0-20d4-4256-a96f-78dd4f6d7ec8',0,NULL,'2026-06-11 17:04:01'),('396768ff-5326-4b49-b182-d3e259fe1b0a','918649f3-988e-4e16-a371-73c7988300e3','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'0bf72d4e-b8c9-4d9e-83ea-c09924105e62',0,NULL,'2026-06-11 17:05:44'),('3a44ac2e-1ea6-430d-b6cf-670ec3ce2b45','918649f3-988e-4e16-a371-73c7988300e3','c26b7a29-755b-4665-8912-397c05d48a27','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'cc38020c-9673-4190-bc8a-311bf0b3975a',0,NULL,'2026-06-11 17:04:01'),('41811e88-05be-446e-90c7-502ea2991017','cefa9922-8910-4842-84a0-cf465fe7db3f','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('445eac98-5430-48df-acbe-b1ae961f7793','31badd94-726c-4f6b-887b-856291cbf36e','392cced6-d52b-464b-9829-51aa9ce12468','multa','Multa por Retraso 30min - Sesion #1 del 28/06/2026',5.00,'83c3991c-4a37-40e6-84cc-d4af0386b6fa',1,'f12fd18c-67f3-450b-bd20-becfafaf0fec','2026-06-11 16:56:13'),('48dab0dc-6728-44fb-907a-6ea2b702a938','918649f3-988e-4e16-a371-73c7988300e3','caaf8155-4c10-4e84-aa7b-ba4183906421','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'e2356707-cb49-499b-9bbe-8c269f4b5f61',0,NULL,'2026-06-11 17:05:44'),('5721aa67-763e-43a8-ab37-7d292ffc6638','918649f3-988e-4e16-a371-73c7988300e3','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('5912e8bf-d97d-4499-b142-8411c98dbe1e','918649f3-988e-4e16-a371-73c7988300e3','32d4ffda-eec7-4299-885f-f320557da01e','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'293f4d4f-53ec-4cef-9215-6cda020392f6',0,NULL,'2026-06-11 17:04:01'),('59d7e49f-2a13-4ae6-9db1-eefecc16f0c9','918649f3-988e-4e16-a371-73c7988300e3','00e16557-e3cf-4738-8516-7f3fb6ddb96d','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'8f170023-d342-4a42-8ea0-a47bba1ac481',0,NULL,'2026-06-11 17:04:01'),('5d5450d9-ea00-44b4-9dd5-b0448a1f2926','918649f3-988e-4e16-a371-73c7988300e3','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('5dca8edc-c392-453e-a7f2-d3c5dcc4a48b','918649f3-988e-4e16-a371-73c7988300e3','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('66da6000-414f-4bcb-bd4c-2eb377c6e524','918649f3-988e-4e16-a371-73c7988300e3','c26b7a29-755b-4665-8912-397c05d48a27','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'0947b2f1-ff5f-43eb-aee5-b7b7336b7829',0,NULL,'2026-06-11 17:05:44'),('6d37f8c2-4de4-4252-8f67-b556b24de160','31badd94-726c-4f6b-887b-856291cbf36e','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('6f48b0ac-4e1e-4b56-9e78-067696c9e0e7','cefa9922-8910-4842-84a0-cf465fe7db3f','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('799e035d-cec1-402f-8831-ab17d46577a9','918649f3-988e-4e16-a371-73c7988300e3','00e16557-e3cf-4738-8516-7f3fb6ddb96d','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'3d223ef1-38f5-488b-8442-4236aaff1d44',0,NULL,'2026-06-11 17:04:01'),('7e8af5a7-799d-4e34-8c70-1e5be505116b','31badd94-726c-4f6b-887b-856291cbf36e','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('85b24e0b-4858-4cb8-93e5-52c96a142757','cefa9922-8910-4842-84a0-cf465fe7db3f','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('865ca1a5-fcc1-4694-b3d4-6655ad6e7488','31badd94-726c-4f6b-887b-856291cbf36e','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('884eef4e-7a72-46fb-a683-23f5cf1b7a07','31badd94-726c-4f6b-887b-856291cbf36e','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,1,'9db3d287-ebb0-4640-9a08-84d9e196ae8a','2026-06-11 16:56:13'),('8e06de30-d928-457f-ba97-ab4ff460d00d','31badd94-726c-4f6b-887b-856291cbf36e','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('8fe66178-89af-42bb-99c3-368d643278cb','cefa9922-8910-4842-84a0-cf465fe7db3f','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('91d23434-eee0-4902-b973-26a1a0f80500','918649f3-988e-4e16-a371-73c7988300e3','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('92a98f9b-be81-4bb4-8bfe-8f2e28641fa6','31badd94-726c-4f6b-887b-856291cbf36e','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('9af0d170-eb73-4a27-84e8-53fb5d3f755a','31badd94-726c-4f6b-887b-856291cbf36e','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #2 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 16:56:13'),('a47ecb55-7587-4d0d-b749-350f37932a84','918649f3-988e-4e16-a371-73c7988300e3','5afb15ad-ced5-431b-9fc2-970cf4919433','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'db7f23d3-cf2e-463e-b8b6-22946a90c0e7',0,NULL,'2026-06-11 17:04:01'),('a770c336-4235-41ea-8505-2647a21d4225','918649f3-988e-4e16-a371-73c7988300e3','caaf8155-4c10-4e84-aa7b-ba4183906421','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'0a1c17f3-4797-4f29-879b-23d52645bffa',0,NULL,'2026-06-11 17:04:01'),('ac7c8ca9-e5e1-4721-be03-96dbe81bdf5d','918649f3-988e-4e16-a371-73c7988300e3','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('ad478916-f1a7-41fc-ae92-0b06bfcbab59','918649f3-988e-4e16-a371-73c7988300e3','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #3 del 30/08/2026',10.00,NULL,0,NULL,'2026-06-11 17:04:01'),('b91e8e4c-478b-4243-8b92-0176cb4c5b75','918649f3-988e-4e16-a371-73c7988300e3','9e52d148-927b-4784-b290-b8d9f9b1c35f','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'894bf270-6b7a-46e2-8e5a-ad6ddbb54c3c',0,NULL,'2026-06-11 17:04:01'),('c034b4c8-eaaa-49f3-8af2-4c6340f05da9','918649f3-988e-4e16-a371-73c7988300e3','32d4ffda-eec7-4299-885f-f320557da01e','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'66103248-da2b-4c87-8df9-ba0f2048e9ea',0,NULL,'2026-06-11 17:05:44'),('c05e2563-20fe-4afb-bf51-fb69daf82f44','918649f3-988e-4e16-a371-73c7988300e3','5afb15ad-ced5-431b-9fc2-970cf4919433','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'938c3af6-360f-4bd3-a044-fdc04c255be9',0,NULL,'2026-06-11 17:05:44'),('c66cde7d-fc80-44a8-8bf0-67e2f12c6728','918649f3-988e-4e16-a371-73c7988300e3','9e52d148-927b-4784-b290-b8d9f9b1c35f','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'d29f79d6-073b-4e18-994d-118768a75c21',0,NULL,'2026-06-11 17:05:44'),('c8abc42b-7c14-494f-b5a0-19786af3d12d','cefa9922-8910-4842-84a0-cf465fe7db3f','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #1 del 28/06/2026',10.00,NULL,0,NULL,'2026-06-11 16:47:34'),('cb3b4c31-1097-47f4-b1e4-887f9253f35c','918649f3-988e-4e16-a371-73c7988300e3','9e52d148-927b-4784-b290-b8d9f9b1c35f','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'ed17bc07-8218-4c11-a289-42c7af29778c',0,NULL,'2026-06-11 17:04:01'),('cbf7ced2-de57-4e5b-aef5-abb272cc0b8d','918649f3-988e-4e16-a371-73c7988300e3','00e16557-e3cf-4738-8516-7f3fb6ddb96d','multa','Multa por cuota impaga - Sesion #3 del 30/08/2026',2.00,'b04edad4-b4ae-4705-b1b9-1aa0b321add2',0,NULL,'2026-06-11 17:05:44'),('cd36500f-90c9-4fe2-b240-ab08171c08eb','918649f3-988e-4e16-a371-73c7988300e3','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'7e86bdb1-5264-41f8-b31e-e5e794fd7889',0,NULL,'2026-06-11 17:04:01'),('d932b921-bec9-4e2e-b7a2-692e72bff1c1','918649f3-988e-4e16-a371-73c7988300e3','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'d7e75778-0d7a-4f4e-9910-4a419e7548c0',0,NULL,'2026-06-11 17:04:01'),('e9356f5a-7a6a-4367-ba1c-d2b6949966a9','918649f3-988e-4e16-a371-73c7988300e3','caaf8155-4c10-4e84-aa7b-ba4183906421','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'cd5f773d-f896-4388-b4b0-1b7d0f523a95',0,NULL,'2026-06-11 17:04:01'),('eb49b997-b19e-415c-8af6-cc8b4e7f41ae','918649f3-988e-4e16-a371-73c7988300e3','c26b7a29-755b-4665-8912-397c05d48a27','multa','Multa por Cuota impaga - Sesion #1 del 28/06/2026',2.00,'b9c1bedd-cebc-4cac-a617-1a61acd16f75',0,NULL,'2026-06-11 17:04:01'),('f31d2cba-dad7-4814-9668-82043ca23d63','918649f3-988e-4e16-a371-73c7988300e3','32d4ffda-eec7-4299-885f-f320557da01e','multa','Multa por Cuota impaga - Sesion #2 del 26/07/2026',2.00,'3bce3ead-eac8-4677-ac23-751dbaaf2f2b',0,NULL,'2026-06-11 17:04:01');
/*!40000 ALTER TABLE `obligaciones_sesion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parametros`
--

DROP TABLE IF EXISTS `parametros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parametros` (
  `id_parametro` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador num+®rico del par+ímetro',
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'C+¦digo +¦nico del par+ímetro (ej: tasa_inter+®s_cr+®dito)',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo del par+ímetro',
  `valor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Valor del par+ímetro',
  `tipo` enum('texto','numero','decimal','booleano','color') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'texto' COMMENT 'Tipo de dato del valor',
  `modulo` enum('general','financiero','seguridad','imagen') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general' COMMENT 'M+¦dulo al que pertenece el par+ímetro',
  `editable` tinyint(1) DEFAULT '1' COMMENT 'Indica si el par+ímetro puede ser editado desde el panel',
  PRIMARY KEY (`id_parametro`),
  UNIQUE KEY `cÔö£Ôöédigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ParÔö£+¡metros configurables del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametros`
--

LOCK TABLES `parametros` WRITE;
/*!40000 ALTER TABLE `parametros` DISABLE KEYS */;
INSERT INTO `parametros` VALUES (1,'tasa_inter+®s_cr+®dito','Tasa de inter+®s para cr+®ditos','6.00','decimal','financiero',1),(2,'m+®todo_inter+®s_default','M+®todo de inter+®s por defecto','simple','texto','financiero',1),(3,'tasa_inter+®s_ahorro','Tasa de inter+®s sobre ahorros','0.00','decimal','financiero',1),(4,'tasa_inter+®s_inversi+¦n','Tasa de inter+®s para inversiones','6.00','decimal','financiero',1),(5,'aporte_obligatorio_mensual','Aporte obligatorio mensual','10.00','decimal','financiero',1),(6,'cuota_ingreso','Cuota +¦nica de ingreso','20.00','decimal','financiero',1),(7,'multa_retraso_10min','Multa retraso 10-30 minutos','1.00','decimal','financiero',1),(8,'multa_retraso_30min','Multa retraso >=30 minutos','5.00','decimal','financiero',1),(9,'multa_inasistencia','Multa por inasistencia','5.00','decimal','financiero',1),(10,'multa_mora_credito','Multa por mora de cr+®dito','5.00','decimal','financiero',1),(11,'l+¡mite_cr+®dito_emergente','L+¡mite cr+®dito emergente','300.00','decimal','financiero',1),(12,'plazo_m+¡nimo_inversi+¦n','Plazo m+¡nimo inversi+¦n (meses)','6','numero','financiero',1),(13,'intentos_m+íx_login','Intentos m+íximo de login','3','numero','seguridad',1),(14,'bloqueo_minutos','Minutos de bloqueo','15','numero','seguridad',1),(15,'session_timeout_minutos','Timeout de sesi+¦n (minutos)','30','numero','seguridad',1),(16,'pin_2fa_d+¡gitos','D+¡gitos del PIN 2FA','6','numero','seguridad',1),(17,'pin_2fa_expiracion_min','Expiraci+¦n PIN 2FA (minutos)','5','numero','seguridad',1),(18,'m+íx_reenv+¡o_pin_hora','M+íximo reenv+¡os PIN por hora','3','numero','seguridad',1),(19,'logo_sidebar','Logo del sidebar','ca62b9e0-de01-42cc-9bb6-0826f49dce00','texto','imagen',1),(20,'logo_sd','Logo sin fondo','d9433f2e-ffa1-48c9-bf86-b338e6796ff2','texto','imagen',1),(21,'retencion_papelera_dias','Retencion papelera (dias)','30','numero','seguridad',1),(22,'multa_cuota_impaga','Multa por cuota mensual impaga','2.00','decimal','financiero',1);
/*!40000 ALTER TABLE `parametros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `id_permiso` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador num+®rico del permiso',
  `codigo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'C+¦digo +¦nico del permiso (ej: socio.registrar)',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre descriptivo del permiso',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripci+¦n detallada del alcance del permiso',
  PRIMARY KEY (`id_permiso`),
  UNIQUE KEY `cÔö£Ôöédigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CatÔö£+¡logo de permisos disponibles en el sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'auth.login','Ingresar al sistema','Permite iniciar sesi+¦n en el sistema'),(2,'auth.ver_2fa','Acceder con 2FA','Permite acceder con autenticaci+¦n de dos factores'),(3,'socio.registrar','Registrar nuevo socio','Permite registrar un nuevo socio en el sistema'),(4,'socio.editar','Editar datos de socio','Permite modificar los datos de un socio existente'),(5,'socio.cambiar_estado','Cambiar estado del socio','Permite cambiar el estado de un socio en su ciclo de vida'),(6,'socio.consultar','Consultar lista de socios','Permite consultar el listado de socios registrados'),(7,'socio.ver_financiero','Ver datos financieros del socio','Permite visualizar la informaci+¦n financiera del socio'),(8,'param.usuarios','Gestionar usuarios del sistema','CRUD completo de usuarios del sistema'),(9,'param.roles','Gestionar roles y permisos','Crear, editar y eliminar roles con permisos personalizados'),(10,'param.imagen','Configurar imagen corporativa','Gestionar logo, colores, membrete y raz+¦n social'),(11,'param.catalogos','Editar cat+ílogos','Gestionar provincias, cantones y entidades p+¦blicas'),(12,'param.financiero','Configurar par+ímetros financieros','Configurar tasas, montos, plazos y m+®todos de inter+®s'),(13,'producto.crear','Crear productos financieros','Crear nuevos productos de cr+®dito e inversi+¦n'),(14,'producto.editar','Editar productos','Modificar productos financieros existentes'),(15,'producto.activar','Activar/desactivar productos','Activar o desactivar productos financieros'),(16,'cobro.aporte','Registrar cobro de aporte','Registrar cobro de aporte obligatorio y voluntario'),(17,'cobro.cuota_credito','Registrar cobro de cuota de cr+®dito','Registrar cobro de cuotas de cr+®dito'),(18,'cobro.multa','Registrar cobro de multa','Registrar cobro de multas generadas'),(19,'cobro.inversion','Registrar inversi+¦n voluntaria','Registrar apertura de inversi+¦n a plazo fijo'),(20,'cobro.desembolso','Realizar desembolso de cr+®dito','Ejecutar el desembolso de un cr+®dito aprobado'),(21,'cobro.anular','Anular cobro registrado','Anular un cobro previamente registrado'),(22,'cobro.cierre_sesion','Ejecutar cierre de sesi+¦n mensual','Cerrar la sesi+¦n mensual con generaci+¦n de acta'),(23,'calculo.intereses','Ejecutar c+ílculo de intereses','Calcular intereses de cr+®ditos, ahorros e inversiones'),(24,'calculo.excedentes','Calcular distribuci+¦n de excedentes','Calcular la distribuci+¦n de excedentes entre los socios'),(25,'calculo.aprobar_excedentes','Aprobar distribuci+¦n de excedentes','Aprobar la distribuci+¦n de excedentes calculada'),(26,'reporte.socios','Generar reportes de socios','Generar reportes del m+¦dulo de socios'),(27,'reporte.financiero','Generar reportes financieros','Generar reportes del m+¦dulo financiero'),(28,'reporte.cobros','Generar reportes de cobros','Generar reportes del m+¦dulo de cobros'),(29,'credito.aprobar','Aprobar/rechazar creditos','Permite aprobar o rechazar solicitudes de credito en la bandeja de aprobacion'),(30,'multa.impugnar','Impugnar multas','Permite autorizar la impugnacion de multas presentadas por los socios'),(31,'multa.autorizar_impugnacion','Autorizar impugnacion','Permite autorizar o rechazar impugnaciones de multas presentadas por los socios');
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos_financieros`
--

DROP TABLE IF EXISTS `productos_financieros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos_financieros` (
  `id_producto` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del producto financiero (UUID)',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre del producto (ej: Cr+®dito Ordinario, Inversi+¦n 6 Meses)',
  `tipo` enum('credito','inversion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tasa_interes_anual` decimal(5,2) NOT NULL DEFAULT '6.00' COMMENT 'Tasa de inter+®s anual en porcentaje',
  `metodo_interes` enum('simple','frances','aleman') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'simple' COMMENT 'Metodo de calculo de intereses',
  `plazo_min_meses` int NOT NULL COMMENT 'Plazo m+¡nimo en meses',
  `plazo_max_meses` int NOT NULL COMMENT 'Plazo m+íximo en meses',
  `monto_min` decimal(10,2) NOT NULL COMMENT 'Monto m+¡nimo del producto',
  `monto_max` decimal(10,2) NOT NULL COMMENT 'Monto m+íximo del producto',
  `requiere_garante` tinyint(1) DEFAULT '0' COMMENT 'Indica si el producto requiere garante',
  `penalidad_retiro_anticipado` decimal(5,2) DEFAULT '0.00' COMMENT 'Penalidad por retiro anticipado (%)',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Indica si el producto est+í activo para nuevas solicitudes',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creaci+¦n del producto',
  `condiciones_html` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Condiciones generales del credito en HTML (WYSIWYG)',
  `min_permanencia_meses` int DEFAULT '0' COMMENT 'Minimo de permanencia como socio activo (meses)',
  `min_ahorro` decimal(10,2) DEFAULT '0.00' COMMENT 'Minimo de ahorro acumulado requerido',
  `min_ahorro_unidad` enum('dolares','porcentaje') COLLATE utf8mb4_unicode_ci DEFAULT 'dolares' COMMENT 'Unidad del ahorro minimo: dolares fijo o porcentaje del credito',
  `es_emergente` tinyint(1) DEFAULT '0' COMMENT 'Si es credito emergente (no requiere sesion de aprobacion)',
  `monto_max_emergente` decimal(10,2) DEFAULT '0.00' COMMENT 'Monto maximo para credito emergente',
  `min_destino_caracteres` int DEFAULT '0' COMMENT 'Minimo de caracteres para el campo destino del credito',
  `min_permanencia_valor` int DEFAULT '0' COMMENT 'Valor de permanencia minima',
  `min_permanencia_unidad` enum('dias','meses','anios') COLLATE utf8mb4_unicode_ci DEFAULT 'meses' COMMENT 'Unidad de permanencia minima',
  `requiere_documento_firmado` tinyint(1) DEFAULT '1' COMMENT 'Si requiere documento firmado escaneado antes del desembolso',
  PRIMARY KEY (`id_producto`),
  KEY `idx_productos_tipo` (`tipo`),
  KEY `idx_productos_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CatÔö£+¡logo de productos financieros parametrizables por el Analista Financiero';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_financieros`
--

LOCK TABLES `productos_financieros` WRITE;
/*!40000 ALTER TABLE `productos_financieros` DISABLE KEYS */;
INSERT INTO `productos_financieros` VALUES ('95f3f25d-542c-455c-a160-c65b99b9e778','Cr+®dito Ordinario','credito',6.00,'simple',1,12,50.00,1000.00,0,0.00,1,'2026-06-09 16:32:07','<p><b>Condiciones de Cr+®dito</b></p><p><b>Denominaci+¦n:</b> Cr+®dito Ordinario</p><p>Estas son las condiciones del producto de cr+®dito.</p><p>De aceptar estas condiciones.</p><p>Atentamente,</p><p>La Administraci+¦n.</p>',0,50.00,'porcentaje',0,0.00,15,6,'meses',1),('b53305e5-5102-49c0-9176-d164d3e98c58','Inversi+¦n 3 meses','inversion',6.00,'simple',3,3,50.00,5000.00,0,5.00,1,'2026-06-06 14:16:51','<p><br></p>',0,0.00,'dolares',0,0.00,0,0,'meses',1);
/*!40000 ALTER TABLE `productos_financieros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provincias`
--

DROP TABLE IF EXISTS `provincias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provincias` (
  `id_provincia` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador num+®rico de la provincia',
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la provincia',
  PRIMARY KEY (`id_provincia`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='CatÔö£+¡logo de provincias del Ecuador';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provincias`
--

LOCK TABLES `provincias` WRITE;
/*!40000 ALTER TABLE `provincias` DISABLE KEYS */;
INSERT INTO `provincias` VALUES (1,'Pichincha'),(2,'Imbabura');
/*!40000 ALTER TABLE `provincias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador num+®rico del rol',
  `nombre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre personalizable del rol (ej: Presidente, Tesorero)',
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripci+¦n de las funciones del rol',
  `endosable` tinyint(1) DEFAULT '0' COMMENT 'Si es TRUE, este rol puede acumular permisos de otros roles',
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema +ö+ç+Â 100% personalizables desde el panel de administraciÔö£Ôöén';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador T+®cnico','Gobierna usuarios, roles, permisos e imagen corporativa. Sin acceso financiero',0),(2,'Presidente','Representante legal, convocatorias, supervisi+¦n, firma de certificados',0),(3,'Analista Financiero','Configura productos financieros, par+ímetros, c+ílculos y distribuci+¦n de excedentes',1),(4,'Tesorero','Ejecuci+¦n financiera diaria: cobros, desembolsos, cierre de sesi+¦n',0),(5,'Asistente de Tesorer+¡a','Apoyo en cobros de aportes, cuotas y multas',0),(6,'Socio','Acceso al portal personal: consultas, solicitudes, comprobantes',0),(7,'Secretario/a','Gesti+¦n documental, registro de socios, certificados, actas y convocatorias',0);
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
  `permitir` tinyint(1) DEFAULT '1' COMMENT 'TRUE = concedido, FALSE = denegado expl+¡citamente',
  PRIMARY KEY (`id_rol`,`id_permiso`),
  KEY `id_permiso` (`id_permiso`),
  CONSTRAINT `roles_permisos_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE,
  CONSTRAINT `roles_permisos_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Matriz de permisos asignados a cada rol (gestiÔö£Ôöén por checkboxes)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_permisos`
--

LOCK TABLES `roles_permisos` WRITE;
/*!40000 ALTER TABLE `roles_permisos` DISABLE KEYS */;
INSERT INTO `roles_permisos` VALUES (1,1,1),(1,2,1),(1,6,1),(1,7,1),(1,8,1),(1,9,1),(1,10,1),(1,11,1),(1,26,1),(2,1,1),(2,2,1),(2,3,1),(2,4,1),(2,5,1),(2,6,1),(2,7,1),(2,21,1),(2,22,1),(2,25,1),(2,26,1),(2,27,1),(2,28,1),(2,29,1),(3,1,1),(3,2,1),(3,4,1),(3,6,1),(3,7,1),(3,12,1),(3,13,1),(3,14,1),(3,15,1),(3,21,1),(3,22,1),(3,23,1),(3,24,1),(3,26,1),(3,27,1),(3,28,1),(4,1,1),(4,2,1),(4,3,1),(4,4,1),(4,6,1),(4,7,1),(4,16,1),(4,17,1),(4,18,1),(4,19,1),(4,20,1),(4,21,1),(4,22,1),(4,26,1),(4,27,1),(4,28,1),(4,29,1),(4,30,1),(5,1,1),(5,6,1),(5,7,1),(5,16,1),(5,17,1),(5,18,1),(5,19,1),(5,26,1),(5,28,1),(6,1,1),(7,1,1),(7,2,1),(7,3,1),(7,4,1),(7,5,1),(7,6,1),(7,7,1),(7,16,1),(7,26,1),(7,30,1),(7,31,1);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AsignaciÔö£Ôöén de roles a usuarios (relaciÔö£Ôöén muchos-a-muchos)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_usuarios`
--

LOCK TABLES `roles_usuarios` WRITE;
/*!40000 ALTER TABLE `roles_usuarios` DISABLE KEYS */;
INSERT INTO `roles_usuarios` VALUES ('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',1),('516363c5-c79a-4491-83b4-b8303ce1f286',2),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',2),('516363c5-c79a-4491-83b4-b8303ce1f286',3),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',3),('516363c5-c79a-4491-83b4-b8303ce1f286',4),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',4),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',5),('1673019a-c66d-4bb8-9158-1729fa6b064a',6),('516363c5-c79a-4491-83b4-b8303ce1f286',7),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291',7);
/*!40000 ALTER TABLE `roles_usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_mensuales`
--

DROP TABLE IF EXISTS `sesiones_mensuales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesiones_mensuales` (
  `id_sesion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico de la sesi+¦n mensual (UUID)',
  `numero_sesion` int NOT NULL COMMENT 'N+¦mero correlativo de la sesi+¦n mensual',
  `fecha_sesion` date DEFAULT NULL COMMENT 'Fecha programada de la reunion (corte para calculo de obligaciones)',
  `titulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'T+¡tulo o nombre de la sesi+¦n',
  `estado` enum('abierta','cerrada') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'abierta' COMMENT 'Estado de la sesi+¦n: abierta (en curso) o cerrada (finalizada)',
  `fecha_apertura` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de apertura de la sesi+¦n',
  `fecha_cierre` datetime DEFAULT NULL COMMENT 'Fecha y hora de cierre de la sesi+¦n',
  `usuario_cierre` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que ejecut+¦ el cierre de sesi+¦n',
  `acta_cierre_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de cierre',
  `total_recaudado` decimal(12,2) DEFAULT '0.00' COMMENT 'Total recaudado en la sesi+¦n',
  `total_desembolsado` decimal(12,2) DEFAULT '0.00' COMMENT 'Total desembolsado en la sesi+¦n',
  `saldo_caja` decimal(12,2) DEFAULT '0.00' COMMENT 'Saldo final de caja (recaudado - desembolsado)',
  PRIMARY KEY (`id_sesion`),
  KEY `usuario_cierre` (`usuario_cierre`),
  CONSTRAINT `sesiones_mensuales_ibfk_1` FOREIGN KEY (`usuario_cierre`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sesiones mensuales de cobro y check-in +ö+ç+Â nÔö£Ôòæcleo operativo del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_mensuales`
--

LOCK TABLES `sesiones_mensuales` WRITE;
/*!40000 ALTER TABLE `sesiones_mensuales` DISABLE KEYS */;
INSERT INTO `sesiones_mensuales` VALUES ('31badd94-726c-4f6b-887b-856291cbf36e',2,'2026-07-26','Sesi+¦n Ordinaria Julio 2026','cerrada','2026-06-11 16:56:13','2026-06-11 17:03:43','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_2_20260611.html',16.00,0.00,16.00),('918649f3-988e-4e16-a371-73c7988300e3',3,'2026-08-30','Sesi+¦n Ordinaria Ago 2026','cerrada','2026-06-11 17:04:01','2026-06-11 17:05:44','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_3_20260611.html',10.00,0.00,10.00),('cefa9922-8910-4842-84a0-cf465fe7db3f',1,'2026-06-28','Sesi+¦n Ordinaria Junio 2026','cerrada','2026-06-11 16:47:34','2026-06-11 16:56:04','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_1_20260611.html',10.00,0.00,10.00);
/*!40000 ALTER TABLE `sesiones_mensuales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `socios`
--

DROP TABLE IF EXISTS `socios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `socios` (
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del socio (UUID)',
  `cedula` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'C+®dula de identidad ecuatoriana (10 d+¡gitos, d+¡gito verificador)',
  `apellido1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primer apellido (may+¦sculas)',
  `apellido2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Segundo apellido (may+¦sculas)',
  `nombre1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Primer nombre (may+¦sculas)',
  `nombre2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Segundo nombre (may+¦sculas)',
  `fecha_nacimiento` date NOT NULL COMMENT 'Fecha de nacimiento',
  `genero` enum('masculino','femenino') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'G+®nero del socio',
  `estado_civil` enum('soltero','casado','divorciado','viudo','union_libre') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Direcci+¦n de residencia',
  `telefono` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'N+¦mero de tel+®fono fijo',
  `celular` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'N+¦mero de celular',
  `correo_electronico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correo electr+¦nico (validado con PIN 6 d+¡gitos)',
  `profesion` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Profesi+¦n u ocupaci+¦n',
  `foto_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL de la fotograf+¡a del socio',
  `documento_identidad_anverso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF/JPG del anverso de la c+®dula',
  `documento_identidad_reverso` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF/JPG del reverso de la c+®dula',
  `estado` enum('pendiente','pre_activo','activo','suspendido','retiro_voluntario','excluido','fallecido') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente' COMMENT 'Estado actual del socio en el ciclo de vida',
  `fecha_ingreso` date NOT NULL COMMENT 'Fecha de solicitud de ingreso',
  `fecha_aprobacion` date DEFAULT NULL COMMENT 'Fecha de aprobaci+¦n por la Asamblea',
  `numero_acta_aprobacion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'N+¦mero de acta de la Asamblea que aprob+¦ el ingreso',
  `acta_aprobacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF del acta de aprobaci+¦n',
  `observaciones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones generales del socio',
  `fecha_retiro` date DEFAULT NULL COMMENT 'Fecha de retiro voluntario',
  `motivo_retiro` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo del retiro voluntario',
  `fecha_exclusion` date DEFAULT NULL COMMENT 'Fecha de exclusi+¦n (Art.14 Estatuto)',
  `motivo_exclusion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de la exclusi+¦n',
  `menor_edad` tinyint(1) DEFAULT '0' COMMENT 'Indica si el socio es menor de edad',
  `representante_nombres` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombres del representante legal (menores de edad)',
  `representante_cedula` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'C+®dula del representante legal',
  `representante_telefono` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tel+®fono del representante legal',
  `representante_correo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Correo del representante legal',
  `representante_documento_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Documento legal del representante (PDF)',
  `hash_integridad` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SHA-256 de integridad del registro',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creaci+¦n del registro',
  PRIMARY KEY (`id_socio`),
  UNIQUE KEY `cÔö£-«dula` (`cedula`),
  UNIQUE KEY `correo_electrÔö£Ôöénico` (`correo_electronico`),
  KEY `idx_socios_cÔö£-«dula` (`cedula`),
  KEY `idx_socios_correo` (`correo_electronico`),
  KEY `idx_socios_estado` (`estado`),
  KEY `idx_socios_apellidos` (`apellido1`,`apellido2`),
  KEY `idx_socios_nombres` (`nombre1`,`nombre2`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de socios de la Caja de Ahorro con datos personales, estado y representaciÔö£Ôöén';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socios`
--

LOCK TABLES `socios` WRITE;
/*!40000 ALTER TABLE `socios` DISABLE KEYS */;
INSERT INTO `socios` VALUES ('00e16557-e3cf-4738-8516-7f3fb6ddb96d','1755566677','SANCHEZ','TORRES','PEDRO','ANDR+ëS','1992-11-30','masculino','soltero','Av. Central 789','023456789','0987654321','pedro.sanchez@email.com','Profesor',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('32d4ffda-eec7-4299-885f-f320557da01e','1766677788','VARGAS','CRUZ','CARLOS','MANUEL','1975-07-18','masculino','casado','Av. Sur 654','025678901','0965432109','carlos.vargas@email.com','Abogado',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('392cced6-d52b-464b-9829-51aa9ce12468','1002003000','CARRANCO','GONZALEZ','GAVINO','ALEXANDER','1983-01-19','masculino',NULL,'IBARRA','062640879','0996755645','gcarranco@hotmail.com','Msc.','foto_392cced6.jpg',NULL,NULL,'activo','2026-06-06','2026-06-06','1','acta_aprobacion_392cced6.pdf',NULL,NULL,NULL,NULL,NULL,0,'','','','',NULL,'6639e5094ac2466f6884b5fbf6ee971948e87e1ab42b4d85aea60fe4abb0689b','2026-06-06 20:39:04'),('5afb15ad-ced5-431b-9fc2-970cf4919433','1712345678','MART+ìNEZ','G+ôMEZ','JUAN','CARLOS','1990-05-15','masculino','soltero','Av. Principal 123','022345678','0991234567','juan.martinez@email.com','Ingeniero',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('9e52d148-927b-4784-b290-b8d9f9b1c35f','1787654321','L+ôPEZ','RAMOS','MAR+ìA','ELENA','1985-08-22','femenino','casado','Calle Secundaria 456','022987654','0999876543','maria.lopez@email.com','Licenciada',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','1766611122','CORDERO','QUIMI','LUIS','FELIPE','1982-09-05','masculino','casado','Av. Occidental 147','027890123','0943210987','luis.cordero@email.com','Contador',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'8652dfff92bd9c2f868aeeaa6df9b318609755b2d466e92e9e7a97bb71cb323e','2026-06-06 14:16:51'),('c26b7a29-755b-4665-8912-397c05d48a27','1711199900','ZAMBRANO','ROSALES','M+ôNICA','LISBETH','1995-01-25','femenino','union_libre','Calle Oriente 987','026789012','0954321098','monica.zambrano@email.com','Arquitecto',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('caaf8155-4c10-4e84-aa7b-ba4183906421','1722233344','RAM+ìREZ','V+ëLEZ','ANA','LUC+ìA','1988-03-10','femenino','divorciado','Calle Norte 321','024567890','0976543210','ana.ramirez@email.com','M+®dico',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51');
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
  `fecha_respuesta` datetime DEFAULT NULL COMMENT 'Fecha de aprobaci+¦n/rechazo',
  `usuario_respuesta` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario que aprob+¦/rechaz+¦',
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
/*!40000 ALTER TABLE `solicitudes_retiro` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador +¦nico del usuario (UUID)',
  `nombres` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombres del usuario',
  `apellidos` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Apellidos del usuario',
  `cedula` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'C+®dula de identidad ecuatoriana',
  `correo_electronico` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Correo electr+¦nico del usuario',
  `telefono` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'N+¦mero de tel+®fono',
  `nombre_usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de usuario para inicio de sesi+¦n',
  `contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash bcrypt de la contrase+¦a',
  `activo` tinyint(1) DEFAULT '1' COMMENT 'Indica si el usuario est+í activo en el sistema',
  `_2fa_obligatorio` tinyint(1) DEFAULT '0' COMMENT 'Indica si el 2FA es obligatorio para este usuario',
  `_2fa_activo` tinyint(1) DEFAULT '0' COMMENT 'Indica si el 2FA est+í actualmente activo',
  `bloqueado_hasta` datetime DEFAULT NULL COMMENT 'Fecha/hasta cu+índo est+í bloqueado (3 intentos fallidos)',
  `intentos_fallidos` int DEFAULT '0' COMMENT 'Contador de intentos fallidos de inicio de sesi+¦n',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creaci+¦n del registro',
  `fecha_ultimo_acceso` datetime DEFAULT NULL COMMENT 'Fecha y hora del +¦ltimo inicio de sesi+¦n exitoso',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `cÔö£-«dula` (`cedula`),
  UNIQUE KEY `correo_electrÔö£Ôöénico` (`correo_electronico`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  KEY `idx_usuarios_cÔö£-«dula` (`cedula`),
  KEY `idx_usuarios_correo` (`correo_electronico`),
  KEY `idx_usuarios_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuarios del sistema con credenciales de acceso y control 2FA';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('1673019a-c66d-4bb8-9158-1729fa6b064a','Gavino','Carranco','1002003000','gavinocg@gmail.com','0996755645','gcarranco','$2y$12$QkuzAcoAFQ7C9f5GMMeGS.1smyFeCvsvJeESlsmkB00oBEfzlLYjO',1,0,0,NULL,0,'2026-06-06 16:38:03','2026-06-12 08:32:54'),('516363c5-c79a-4491-83b4-b8303ce1f286','Directivo','Total','1003560438','gcarranco@hotmail.com','','tesorero','$2y$12$/gRI9LwajMIzc8e/NYxO6.hCsUvfbH3c.yxuEKpkpRT7AXoL2ojxe',1,0,0,NULL,0,'2026-06-06 18:23:36','2026-06-12 08:39:46'),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291','Admin','Sistema','1002606083','admin@caja.test','0999999999','admin','$2y$12$IP4hst3.3yCimzqw/bO8JOYscRjkeQADlesFcttSetTnxNCRY.N8G',1,0,0,NULL,0,'2026-06-06 14:16:51','2026-06-12 09:20:14');
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

-- Dump completed on 2026-06-12  9:35:51
