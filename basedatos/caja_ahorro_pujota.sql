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
INSERT INTO `asistencias` VALUES ('0374fba9-6bed-4f12-af1b-730270ba7180','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:10'),('0a51081e-3607-4bfd-84b7-450ac288191a','caaf8155-4c10-4e84-aa7b-ba4183906421','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:41'),('0b064649-9e51-474a-8312-df03d58501a5','32d4ffda-eec7-4299-885f-f320557da01e','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:44'),('0d0290bd-fc2d-40b4-b67e-bbd35fb0670c','c26b7a29-755b-4665-8912-397c05d48a27','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:46'),('12551776-4021-4822-96e4-844b97a98adb','9e52d148-927b-4784-b290-b8d9f9b1c35f','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:49:00'),('1ca9be89-2d3a-4c07-9d61-260cc37ff8fb','5afb15ad-ced5-431b-9fc2-970cf4919433','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:18'),('22e1f5b5-5a70-44cc-b0f0-f230b285fdf4','32d4ffda-eec7-4299-885f-f320557da01e','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:38'),('251dc3d5-a489-470e-9ece-a9327780c2b6','00e16557-e3cf-4738-8516-7f3fb6ddb96d','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:43'),('288bf606-a20a-4635-a166-2f37459bb0d2','9e52d148-927b-4784-b290-b8d9f9b1c35f','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:37'),('2e2c4432-141a-4e8a-90e6-9e396a686f48','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:48:59'),('331723d5-55e3-4f97-906d-dca59b2a4b33','caaf8155-4c10-4e84-aa7b-ba4183906421','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:49:01'),('3490071b-b2f5-4c05-b651-0cd1221cd793','5afb15ad-ced5-431b-9fc2-970cf4919433','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:49:02'),('550b2ea0-701f-46d8-8d77-66cfa8058dea','392cced6-d52b-464b-9829-51aa9ce12468','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:48:57'),('5d0e99ca-7e89-4856-bd48-07073318b214','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:28'),('6575d65d-9f2c-4680-bc4b-68ccea2a5168','c26b7a29-755b-4665-8912-397c05d48a27','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:40'),('681bd6cb-50af-4879-ab2b-c68f0b866ca9','5afb15ad-ced5-431b-9fc2-970cf4919433','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:33'),('7d841c10-263f-47d4-915e-ba586ecfe5b2','00e16557-e3cf-4738-8516-7f3fb6ddb96d','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:49:03'),('80e6f09f-c724-49f8-92ff-8e09a65eb466','c26b7a29-755b-4665-8912-397c05d48a27','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:14'),('91d5b785-4804-4d06-b04a-653f3d141b23','00e16557-e3cf-4738-8516-7f3fb6ddb96d','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:13'),('b44d2ce6-4f32-42a7-a8d4-7d1ccf1b9701','caaf8155-4c10-4e84-aa7b-ba4183906421','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:34'),('b4ab8422-75f9-4b13-85c1-568caa6bcd75','392cced6-d52b-464b-9829-51aa9ce12468','4ffe0148-d8eb-4007-825c-f197e5974beb','retraso_10min',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:28:56'),('b71add30-edec-414f-bcf0-dd2da9bc760a','392cced6-d52b-464b-9829-51aa9ce12468','96915aed-fdcd-4785-b9b5-30d58b4c942a','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:24'),('bc728a0d-98cb-41ab-ab0d-c583dca8de7b','32d4ffda-eec7-4299-885f-f320557da01e','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:49:04'),('c99e5f39-3b07-40ee-b896-f57b1218dfcb','32d4ffda-eec7-4299-885f-f320557da01e','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:15'),('cdfcca22-e388-494b-8c3f-cc4804c2a17c','9e52d148-927b-4784-b290-b8d9f9b1c35f','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:11'),('ce068155-7a8c-477a-aa64-67a2025274bb','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:11'),('ce386266-6adb-4bf3-8371-d7960d7cb962','00e16557-e3cf-4738-8516-7f3fb6ddb96d','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:36'),('d7a6c62a-2432-480e-9f08-aa6b1227be7b','392cced6-d52b-464b-9829-51aa9ce12468','1811a1af-1852-481c-94df-dce4edc301f0','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:10:47'),('d7bb0857-8034-49c0-b836-683b7c380e3f','c26b7a29-755b-4665-8912-397c05d48a27','08ee471e-597e-4712-8d18-ab95adac7694','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 15:49:06'),('e238c6f1-38b4-4429-8706-770f3c4dea34','caaf8155-4c10-4e84-aa7b-ba4183906421','4ffe0148-d8eb-4007-825c-f197e5974beb','a_tiempo',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:29:12'),('e2c2462b-7824-4aa8-b974-c80e13aba767','5afb15ad-ced5-431b-9fc2-970cf4919433','1811a1af-1852-481c-94df-dce4edc301f0','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:11:39'),('e6aa1bd8-9c1f-4fe4-a7f6-beaf89cfa64d','9e52d148-927b-4784-b290-b8d9f9b1c35f','96915aed-fdcd-4785-b9b5-30d58b4c942a','falta',NULL,NULL,0,'516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:12:30');
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
INSERT INTO `caja_movimientos` VALUES ('0543cabb-ea3e-4a13-8fad-4317ff0e3b8e','4ffe0148-d8eb-4007-825c-f197e5974beb','392cced6-d52b-464b-9829-51aa9ce12468','a9cb765c-2b77-4307-8d0b-fe1f306ba5b9','ingreso','Multa por Retraso 30min - Sesion #2 del 12/06/2026 - pagada en Sesion #3','multa',5.00,31.00,36.00,'2026-06-11 15:40:29'),('12372051-3e05-4373-83d3-dbeeca05d030','96915aed-fdcd-4785-b9b5-30d58b4c942a','392cced6-d52b-464b-9829-51aa9ce12468','57ae462a-c151-4cba-9aa4-83e1480f445e','ingreso','Multa - 1002003000 - Sesion #2','multa',1.00,20.00,21.00,'2026-06-11 13:21:10'),('32b512f5-c5dd-42a7-a5f7-ef1efb498b2e','4ffe0148-d8eb-4007-825c-f197e5974beb','392cced6-d52b-464b-9829-51aa9ce12468','fe1acab1-8d3b-4fd6-908d-bac882ad6a99','ingreso','Cuota mensual - 1002003000 - Sesion #3','aporte_obligatorio',10.00,21.00,31.00,'2026-06-11 13:28:13'),('87795fa1-329f-4a91-bbd6-c30963c20bd1','1811a1af-1852-481c-94df-dce4edc301f0','392cced6-d52b-464b-9829-51aa9ce12468','b0f08cbd-f019-4d26-a684-5cd0b512c26c','ingreso','Cuota mensual - 1002003000 - Sesion #1','aporte_obligatorio',10.00,0.00,10.00,'2026-06-11 13:10:53'),('931c5421-5cd1-44e0-85b1-faab526bdd41','96915aed-fdcd-4785-b9b5-30d58b4c942a','392cced6-d52b-464b-9829-51aa9ce12468','a4c9cde2-23df-41e6-ba7a-d6e56539f36a','ingreso','Cuota mensual - 1002003000 - Sesion #2','aporte_obligatorio',10.00,10.00,20.00,'2026-06-11 13:13:12'),('a2bc066b-4d81-4a1e-ae66-41b4618f7e95','08ee471e-597e-4712-8d18-ab95adac7694','392cced6-d52b-464b-9829-51aa9ce12468','b39fadc0-44ec-41fa-806c-18598a6ab085','ingreso','Cuota mensual - 1002003000 - Sesion #4','aporte_obligatorio',10.00,36.00,46.00,'2026-06-11 15:48:33'),('d8f6436b-056d-4792-a27c-8d7624bc8113','4ffe0148-d8eb-4007-825c-f197e5974beb','392cced6-d52b-464b-9829-51aa9ce12468','08526154-f5ff-4c3b-8def-818fe7acec0c','egreso','Anulacion cobro #08526154 (multa) - Error al aplicar multa.','anulacion',5.00,36.00,31.00,'2026-06-11 15:13:28'),('ee42a414-1d7f-4162-9554-87815747798e','08ee471e-597e-4712-8d18-ab95adac7694','392cced6-d52b-464b-9829-51aa9ce12468','b0c88331-4244-4671-81cf-da34164c51c8','ingreso','Multa por Retraso 10min - Sesion #3 del 26/07/2026 - pagada en Sesion #4','multa',1.00,46.00,47.00,'2026-06-11 15:48:34'),('efdea198-d266-4076-91e2-ca6aadb79551','4ffe0148-d8eb-4007-825c-f197e5974beb','392cced6-d52b-464b-9829-51aa9ce12468','08526154-f5ff-4c3b-8def-818fe7acec0c','ingreso','Multa por Retraso 30min - Sesion #2 del 12/06/2026 - pagada en Sesion #3','multa',5.00,31.00,36.00,'2026-06-11 13:28:13');
/*!40000 ALTER TABLE `caja_movimientos` ENABLE KEYS */;
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
  `id_capital_inversion` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador unico del registro de capital de inversion (UUID)',
  `id_socio` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK al socio',
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
  `tipo` enum('aporte_obligatorio','aporte_excedente','cuota_credito','multa','inversion','interes','desembolso','otro','deposito_capital_inversion') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de cobro o transaccion',
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
INSERT INTO `cobros` VALUES ('08526154-f5ff-4c3b-8def-818fe7acec0c','392cced6-d52b-464b-9829-51aa9ce12468','4ffe0148-d8eb-4007-825c-f197e5974beb','multa','0e1a4187-74fa-4ad8-aca2-38d23765ce8a',5.00,'efectivo',NULL,'8029db4a16e409d58ca3ed2447c6b6403e5affc695f10a45278847ac0cbb2054','516363c5-c79a-4491-83b4-b8303ce1f286',1,'Error al aplicar multa.','2026-06-11 15:13:24','516363c5-c79a-4491-83b4-b8303ce1f286','2026-06-11 13:28:13'),('57ae462a-c151-4cba-9aa4-83e1480f445e','392cced6-d52b-464b-9829-51aa9ce12468','96915aed-fdcd-4785-b9b5-30d58b4c942a','multa','bc026e44-32a5-4976-9683-ba8e062a9398',1.00,'efectivo',NULL,'32303f8a9e3e4b2b4531a935c82d3b6e22df9d7f9981de57fb6183a4a1b65579','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 13:21:08'),('a4c9cde2-23df-41e6-ba7a-d6e56539f36a','392cced6-d52b-464b-9829-51aa9ce12468','96915aed-fdcd-4785-b9b5-30d58b4c942a','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'5e99a7689ca0fd9afd1f09a8ea9b4806c115a65deb228be5105141595e3db387','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 13:13:02'),('a9cb765c-2b77-4307-8d0b-fe1f306ba5b9','392cced6-d52b-464b-9829-51aa9ce12468','4ffe0148-d8eb-4007-825c-f197e5974beb','multa','0e1a4187-74fa-4ad8-aca2-38d23765ce8a',5.00,'efectivo',NULL,'c388b741caa5771bf5b66d143f01398f04ddda80917cf8f4d1ebd66dc72cb170','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 15:40:28'),('b0c88331-4244-4671-81cf-da34164c51c8','392cced6-d52b-464b-9829-51aa9ce12468','08ee471e-597e-4712-8d18-ab95adac7694','multa','f6b42436-825e-4239-ac68-1fcbcac2ba44',1.00,'efectivo',NULL,'d2573d62024848b04b05d57f9aa4b3349bfd7fea291db2551f708b49a0226833','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 15:48:33'),('b0f08cbd-f019-4d26-a684-5cd0b512c26c','392cced6-d52b-464b-9829-51aa9ce12468','1811a1af-1852-481c-94df-dce4edc301f0','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'5a97def85988c5756127e11274faca1e3e6a9afc145f916def25e8697cda2ade','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 13:10:52'),('b39fadc0-44ec-41fa-806c-18598a6ab085','392cced6-d52b-464b-9829-51aa9ce12468','08ee471e-597e-4712-8d18-ab95adac7694','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'a047a4702c7f2a6d16eaad29f1400788ae2f9264367610ef403df2ff6833ef1d','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 15:48:32'),('fe1acab1-8d3b-4fd6-908d-bac882ad6a99','392cced6-d52b-464b-9829-51aa9ce12468','4ffe0148-d8eb-4007-825c-f197e5974beb','aporte_obligatorio',NULL,10.00,'efectivo',NULL,'735aa5d0a9c095d83104d517a6e613ea7aefe64e1d7969afef404713ba988215','516363c5-c79a-4491-83b4-b8303ce1f286',0,NULL,NULL,NULL,'2026-06-11 13:28:12');
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
INSERT INTO `cuentas_ahorro` VALUES ('35091853-e6e7-4dcd-a292-5c97229a972a','32d4ffda-eec7-4299-885f-f320557da01e',0.00,0.00,0.00,NULL),('72f76cda-20e3-460a-8533-fab738f82b92','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a',0.00,0.00,0.00,NULL),('7638e37c-72e9-48ba-bfba-0f885133e41a','caaf8155-4c10-4e84-aa7b-ba4183906421',0.00,0.00,0.00,NULL),('95dba24c-5c65-4262-8053-e783ea1c0621','5afb15ad-ced5-431b-9fc2-970cf4919433',0.00,0.00,0.00,NULL),('a09b3d26-dff6-4218-8a1a-f9edc4f10cc7','00e16557-e3cf-4738-8516-7f3fb6ddb96d',0.00,0.00,0.00,NULL),('a8ecf0a7-776c-42dc-b573-1e4433d16989','c26b7a29-755b-4665-8912-397c05d48a27',0.00,0.00,0.00,NULL),('ab9d9a6b-f5dd-4850-95fc-c98de0635c18','9e52d148-927b-4784-b290-b8d9f9b1c35f',0.00,0.00,0.00,NULL),('c3df6ac3-fc84-4376-8228-1aaabb9beea0','392cced6-d52b-464b-9829-51aa9ce12468',40.00,0.00,40.00,'2026-06-11 15:48:32');
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
  `tipo_operacion` enum('aporte_obligatorio','aporte_excedente','retiro_ahorro','desembolso_credito','pago_cuota','pago_multa','inversion_apertura','inversion_retiro','interes_ganado','interes_pagado','cierre_sesion','anulacion','deposito_capital_inversion','retiro_capital_inversion','anulacion_inversion') COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `historial_operaciones` VALUES ('34c83af4-6841-4556-a0a0-3dc0d6cbd3f0','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'b39fadc0-44ec-41fa-806c-18598a6ab085','08ee471e-597e-4712-8d18-ab95adac7694','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 15:48:32'),('66363ba4-d2f0-4227-812d-2d600921e5ca','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'fe1acab1-8d3b-4fd6-908d-bac882ad6a99','4ffe0148-d8eb-4007-825c-f197e5974beb','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 13:28:12'),('70a4ffe5-0da2-49b1-a1df-2971fc3fb294','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'b0f08cbd-f019-4d26-a684-5cd0b512c26c','1811a1af-1852-481c-94df-dce4edc301f0','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 13:10:52'),('7ed5e90a-9bdc-40af-861e-c5fe03837f6f','392cced6-d52b-464b-9829-51aa9ce12468','aporte_obligatorio',10.00,NULL,NULL,'a4c9cde2-23df-41e6-ba7a-d6e56539f36a','96915aed-fdcd-4785-b9b5-30d58b4c942a','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 13:13:02'),('a218fc66-13c9-45cb-be49-3b0c38424cae','392cced6-d52b-464b-9829-51aa9ce12468','pago_multa',5.00,NULL,NULL,'08526154-f5ff-4c3b-8def-818fe7acec0c','4ffe0148-d8eb-4007-825c-f197e5974beb','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 13:28:13'),('a71df678-8819-4514-8f07-86f271ee3b32','392cced6-d52b-464b-9829-51aa9ce12468','pago_multa',1.00,NULL,NULL,'b0c88331-4244-4671-81cf-da34164c51c8','08ee471e-597e-4712-8d18-ab95adac7694','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 15:48:33'),('af264d17-73a5-4fd4-ba55-ccd21f367fb7','392cced6-d52b-464b-9829-51aa9ce12468','pago_multa',1.00,NULL,NULL,'57ae462a-c151-4cba-9aa4-83e1480f445e','96915aed-fdcd-4785-b9b5-30d58b4c942a','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 13:21:08'),('d7da6e2b-0a0d-403e-a7e3-ffca46068977','392cced6-d52b-464b-9829-51aa9ce12468','anulacion',5.00,NULL,NULL,'08526154-f5ff-4c3b-8def-818fe7acec0c','4ffe0148-d8eb-4007-825c-f197e5974beb','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 15:13:24'),('e4175000-10d7-407a-a4e5-cf70aa0c5cfc','392cced6-d52b-464b-9829-51aa9ce12468','pago_multa',5.00,NULL,NULL,'a9cb765c-2b77-4307-8d0b-fe1f306ba5b9','4ffe0148-d8eb-4007-825c-f197e5974beb','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,NULL,'::1','2026-06-11 15:40:28');
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
  `estado` enum('activa','vencida','retiro_anticipado','cancelada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `notificado_devolucion` tinyint(1) DEFAULT '0' COMMENT 'Indica si se notificó la próxima devolución',
  `destino_final` enum('capital_inversion','efectivo','transferencia') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'capital_inversion' COMMENT 'Destino al vencimiento: reinversion, efectivo o transferencia',
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
  `tipo` enum('retraso_10min','retraso_30min','inasistencia','mora_credito','cuota_impaga','otro') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL COMMENT 'Monto de la multa en USD',
  `estado` enum('activa','anulada','impugnada') COLLATE utf8mb4_unicode_ci DEFAULT 'activa' COMMENT 'Estado de la multa',
  `justificacion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Justificación presentada por el socio',
  `justificacion_aprobada` tinyint(1) DEFAULT '0' COMMENT 'Indica si la justificación fue aprobada',
  `justificacion_pdf` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Archivo PDF de la justificación',
  `fecha_generacion` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de generación de la multa',
  PRIMARY KEY (`id_multa`),
  UNIQUE KEY `uk_socio_sesion_tipo` (`id_socio`,`id_sesion`,`tipo`),
  KEY `id_sesi├│n` (`id_sesion`),
  KEY `idx_multas_socio` (`id_socio`),
  CONSTRAINT `multas_ibfk_1` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`),
  CONSTRAINT `multas_ibfk_2` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_mensuales` (`id_sesion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Multas generadas por inasistencia, retraso o mora ÔÇö base legal Art.11 Estatuto';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multas`
--

LOCK TABLES `multas` WRITE;
/*!40000 ALTER TABLE `multas` DISABLE KEYS */;
INSERT INTO `multas` VALUES ('0f3914aa-0d33-46d4-be5e-6d85bce88ddc','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('17465abc-39f8-44bb-bf5f-24b44112843b','c26b7a29-755b-4665-8912-397c05d48a27','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('35bfb54e-d429-4dcc-82ae-3100f1785759','9e52d148-927b-4784-b290-b8d9f9b1c35f','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('3f28bfb0-ce4c-4a49-9d9f-5ab5f8849013','9e52d148-927b-4784-b290-b8d9f9b1c35f','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('465df615-4153-47d5-8dab-dbfa2d3d39a0','32d4ffda-eec7-4299-885f-f320557da01e','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('495a0d89-4aa0-48fd-a2b5-a16546697042','00e16557-e3cf-4738-8516-7f3fb6ddb96d','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('516219b1-a3b5-4b54-990b-0f86875f38ac','c26b7a29-755b-4665-8912-397c05d48a27','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('51becd24-861e-48c8-8a96-a6ca5488757f','caaf8155-4c10-4e84-aa7b-ba4183906421','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('5ffb147f-b77b-4b7f-84ab-468902924401','5afb15ad-ced5-431b-9fc2-970cf4919433','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('8b09d9bb-d23a-40c7-9732-e636058170ed','32d4ffda-eec7-4299-885f-f320557da01e','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('9f5dccd5-ab1f-40f2-afaf-eff315cf8baf','00e16557-e3cf-4738-8516-7f3fb6ddb96d','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('afa9aedc-a64e-42d0-b1f7-3f332afd22e5','caaf8155-4c10-4e84-aa7b-ba4183906421','96915aed-fdcd-4785-b9b5-30d58b4c942a','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:27:10'),('c2c74722-6f27-4da2-bb81-7d8188632f28','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('dd83dd5b-9bbd-4d07-b779-fa908a3175fe','5afb15ad-ced5-431b-9fc2-970cf4919433','1811a1af-1852-481c-94df-dce4edc301f0','inasistencia',5.00,'activa',NULL,0,NULL,'2026-06-11 13:11:51'),('f6b42436-825e-4239-ac68-1fcbcac2ba44','392cced6-d52b-464b-9829-51aa9ce12468','4ffe0148-d8eb-4007-825c-f197e5974beb','retraso_10min',1.00,'activa','No había buses perros.',0,NULL,'2026-06-11 15:45:52');
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
  `buzon` enum('entrada','archivadas','papelera') COLLATE utf8mb4_unicode_ci DEFAULT 'entrada' COMMENT 'Buzon: entrada, archivadas, papelera',
  `fecha_eliminacion` datetime DEFAULT NULL COMMENT 'Fecha en que se movio a papelera',
  PRIMARY KEY (`id_notificacion`),
  KEY `idx_notificaciones_usuario` (`id_usuario`),
  KEY `idx_notificaciones_socio` (`id_socio`),
  KEY `idx_notificaciones_le├¡da` (`leida`),
  KEY `idx_buzon` (`buzon`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `notificaciones_ibfk_2` FOREIGN KEY (`id_socio`) REFERENCES `socios` (`id_socio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Buz├│n de notificaciones persistido en BD + env├¡o en tiempo real por Pusher';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notificaciones`
--

LOCK TABLES `notificaciones` WRITE;
/*!40000 ALTER TABLE `notificaciones` DISABLE KEYS */;
INSERT INTO `notificaciones` VALUES ('0533f23b-2a02-40df-ba5c-30f708b7973b','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 13:29:26',NULL,'entrada',NULL),('1de8f7fa-410c-4545-ba1f-cfb1c088a3a4','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #2 cerrada','La sesion #2 ha sido cerrada. Total recaudado: $11.00',0,1,'2026-06-11 15:40:11',NULL,'entrada',NULL),('3047f44a-4dab-408d-be6f-4fd557c03c3e',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Pago registrado','Multa por Retraso 10min - Sesion #3 del 26/07/2026 ha sido pagada en Sesion #4 del 11/06/2026',0,1,'2026-06-11 15:48:33',NULL,'entrada',NULL),('40a4e925-2026-45f5-b127-5b5c13a97e90',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','cobro','Pago registrado','Cuota mensual de $10.00 ha sido registrada en Sesion #4 del 11/06/2026',0,1,'2026-06-11 15:48:32',NULL,'entrada',NULL),('5823a590-9a6d-40f8-b70c-4fcb9c6c5477','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 15:12:59',NULL,'entrada',NULL),('5a58bc1a-f0eb-4b1a-91e6-88046fad25a4','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 15:40:37',NULL,'entrada',NULL),('6cdac959-1901-4ef5-ad67-dd2009c0e256','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 15:45:34',NULL,'entrada',NULL),('7670de5e-a4c9-4424-99c7-a8da60923fdd','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #1 cerrada','La sesion #1 ha sido cerrada. Total recaudado: $10.00',0,1,'2026-06-11 13:11:58',NULL,'entrada',NULL),('9589ae96-32b7-49ef-9c46-206b9186b012',NULL,'392cced6-d52b-464b-9829-51aa9ce12468','multa','Multa generada','Se ha generado una multa por Retraso 10min de $1 en la sesion #3',1,1,'2026-06-11 15:45:52','2026-06-11 15:48:06','entrada',NULL),('b0d9cdef-039a-4b64-86b4-23fd07079c80','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #1 cerrada','La sesion #1 ha sido cerrada. Total recaudado: $10.00',0,1,'2026-06-11 15:39:47',NULL,'entrada',NULL),('c5a3fed2-bbde-465f-93ee-a106ba36168a','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 15:45:53',NULL,'entrada',NULL),('e2b9400e-c1ee-49e5-8b2c-b59e2a8f2a74','ce86e169-fa0a-468d-bb04-ca7b8c7a5291',NULL,'sesion','Sesion #2 cerrada','La sesion #2 ha sido cerrada. Total recaudado: $11.00',0,1,'2026-06-11 13:27:17',NULL,'entrada',NULL),('f32895cf-a727-4379-93f9-6e09fa68d017','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 15:45:33',NULL,'entrada',NULL),('f4b170f5-7fa9-4de1-a184-5435ef126da1','516363c5-c79a-4491-83b4-b8303ce1f286',NULL,'sesion','Sesion #3 cerrada','La sesion #3 ha sido cerrada. Total recaudado: $15.00',0,1,'2026-06-11 15:45:53',NULL,'entrada',NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Obligaciones de pago generadas al abrir una sesion — calculadas segun fecha de reunion';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obligaciones_sesion`
--

LOCK TABLES `obligaciones_sesion` WRITE;
/*!40000 ALTER TABLE `obligaciones_sesion` DISABLE KEYS */;
INSERT INTO `obligaciones_sesion` VALUES ('048ff31f-8725-434f-a84a-1e0bb50e313f','1811a1af-1852-481c-94df-dce4edc301f0','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('07a8a6b2-824b-445b-98bc-8accd6c0a5af','1811a1af-1852-481c-94df-dce4edc301f0','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('087f4c63-f74f-48a4-86dd-8dcd3f99acce','08ee471e-597e-4712-8d18-ab95adac7694','5afb15ad-ced5-431b-9fc2-970cf4919433','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'5ffb147f-b77b-4b7f-84ab-468902924401',0,NULL,'2026-06-11 15:47:26'),('0b8e8e91-2818-438f-b771-0d59b4398b46','08ee471e-597e-4712-8d18-ab95adac7694','c26b7a29-755b-4665-8912-397c05d48a27','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'516219b1-a3b5-4b54-990b-0f86875f38ac',0,NULL,'2026-06-11 15:47:26'),('0e806757-6a8a-4371-a012-7698ecff1cd2','08ee471e-597e-4712-8d18-ab95adac7694','392cced6-d52b-464b-9829-51aa9ce12468','multa','Multa por Retraso 10min - Sesion #3 del 26/07/2026',1.00,'f6b42436-825e-4239-ac68-1fcbcac2ba44',1,'b0c88331-4244-4671-81cf-da34164c51c8','2026-06-11 15:47:26'),('13ba3cdd-0949-4ad2-86cc-f57f7ad3d1a3','4ffe0148-d8eb-4007-825c-f197e5974beb','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('15b06a6c-78a3-4caa-8b2d-9181e8a62d33','08ee471e-597e-4712-8d18-ab95adac7694','00e16557-e3cf-4738-8516-7f3fb6ddb96d','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'9f5dccd5-ab1f-40f2-afaf-eff315cf8baf',0,NULL,'2026-06-11 15:47:26'),('1cd6b4d5-219c-47c7-adc2-1eadaa471e7e','1811a1af-1852-481c-94df-dce4edc301f0','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,1,'b0f08cbd-f019-4d26-a684-5cd0b512c26c','2026-06-11 13:10:28'),('24180028-eb2d-4837-9252-d648de55ec2c','1811a1af-1852-481c-94df-dce4edc301f0','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('284d0b53-72e1-4b91-972c-47f25e9e2bff','4ffe0148-d8eb-4007-825c-f197e5974beb','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('310f5dda-77c7-4679-bc13-33c73e8fd67a','08ee471e-597e-4712-8d18-ab95adac7694','9e52d148-927b-4784-b290-b8d9f9b1c35f','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'3f28bfb0-ce4c-4a49-9d9f-5ab5f8849013',0,NULL,'2026-06-11 15:47:26'),('3eead0d4-2823-4fb7-8905-96ec739f949b','96915aed-fdcd-4785-b9b5-30d58b4c942a','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:04'),('3f8674a8-2faf-460b-8897-635b6b71b3c9','96915aed-fdcd-4785-b9b5-30d58b4c942a','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,1,'a4c9cde2-23df-41e6-ba7a-d6e56539f36a','2026-06-11 13:12:04'),('4406cb19-8d2d-40c0-b1da-611f6c092245','08ee471e-597e-4712-8d18-ab95adac7694','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('48c3cb5b-da90-427c-bde5-4ff5ac713a34','08ee471e-597e-4712-8d18-ab95adac7694','caaf8155-4c10-4e84-aa7b-ba4183906421','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'afa9aedc-a64e-42d0-b1f7-3f332afd22e5',0,NULL,'2026-06-11 15:47:26'),('4c26848c-5bdb-4552-b050-b4cf3ce1ca48','96915aed-fdcd-4785-b9b5-30d58b4c942a','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:04'),('514bcef5-c29c-4453-b222-afc436945f5a','1811a1af-1852-481c-94df-dce4edc301f0','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('5b233b09-ee21-4aa0-a780-781797d1db94','4ffe0148-d8eb-4007-825c-f197e5974beb','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('5db5976a-8180-4a5d-b9d7-6b777a978ecb','4ffe0148-d8eb-4007-825c-f197e5974beb','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('5fec9518-bf60-4e7d-9206-5c078bea8b0e','08ee471e-597e-4712-8d18-ab95adac7694','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,1,'b39fadc0-44ec-41fa-806c-18598a6ab085','2026-06-11 15:47:26'),('6e8d4956-d56f-445f-bc3b-3cfbffcc94a4','4ffe0148-d8eb-4007-825c-f197e5974beb','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('76ffa84e-ede4-4e52-8551-01474bdd3158','08ee471e-597e-4712-8d18-ab95adac7694','00e16557-e3cf-4738-8516-7f3fb6ddb96d','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'495a0d89-4aa0-48fd-a2b5-a16546697042',0,NULL,'2026-06-11 15:47:26'),('7dcac885-7dfd-474f-b534-9334c73ddf0c','08ee471e-597e-4712-8d18-ab95adac7694','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('81171c94-b557-433c-9adc-ed7028b74add','08ee471e-597e-4712-8d18-ab95adac7694','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('82e1f23a-5620-4432-bced-1bc2b929cd61','96915aed-fdcd-4785-b9b5-30d58b4c942a','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:04'),('8aff63ac-bcf9-4f5e-a451-d3aed1cb8bf9','1811a1af-1852-481c-94df-dce4edc301f0','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('8fe90f87-6652-4518-880c-6a53114de52e','4ffe0148-d8eb-4007-825c-f197e5974beb','32d4ffda-eec7-4299-885f-f320557da01e','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('91ddd185-420e-4778-8c58-fc2f04c2fba7','08ee471e-597e-4712-8d18-ab95adac7694','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'c2c74722-6f27-4da2-bb81-7d8188632f28',0,NULL,'2026-06-11 15:47:26'),('987325f3-21ab-456d-96a6-e70c6e30050d','08ee471e-597e-4712-8d18-ab95adac7694','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'0f3914aa-0d33-46d4-be5e-6d85bce88ddc',0,NULL,'2026-06-11 15:47:26'),('a12e30ca-65e3-48ea-ad43-de353c04e69f','08ee471e-597e-4712-8d18-ab95adac7694','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('ad1c208e-1be0-4b83-95d8-134540d65534','4ffe0148-d8eb-4007-825c-f197e5974beb','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,0,NULL,'2026-06-11 13:27:53'),('b7024dae-fb45-46c2-a4b3-362a728e86e1','96915aed-fdcd-4785-b9b5-30d58b4c942a','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:05'),('bec8d862-9977-4003-949c-984fdfd9b2fa','08ee471e-597e-4712-8d18-ab95adac7694','c26b7a29-755b-4665-8912-397c05d48a27','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'17465abc-39f8-44bb-bf5f-24b44112843b',0,NULL,'2026-06-11 15:47:26'),('c0adfe23-dc32-4be1-b880-4ae5fb236721','4ffe0148-d8eb-4007-825c-f197e5974beb','392cced6-d52b-464b-9829-51aa9ce12468','multa','Multa por Retraso 30min - Sesion #2 del 12/06/2026',5.00,'0e1a4187-74fa-4ad8-aca2-38d23765ce8a',1,'a9cb765c-2b77-4307-8d0b-fe1f306ba5b9','2026-06-11 13:27:53'),('c4ae18bb-0a3c-4a8c-8579-26570c79fd34','08ee471e-597e-4712-8d18-ab95adac7694','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('c5995a03-64ce-42ee-a353-657f4dbe2b3c','1811a1af-1852-481c-94df-dce4edc301f0','00e16557-e3cf-4738-8516-7f3fb6ddb96d','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('cb89a07f-89ff-4625-bd76-13c76a5b98a2','08ee471e-597e-4712-8d18-ab95adac7694','caaf8155-4c10-4e84-aa7b-ba4183906421','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'51becd24-861e-48c8-8a96-a6ca5488757f',0,NULL,'2026-06-11 15:47:26'),('cbdad019-ec63-4833-b76d-5734abb43d51','96915aed-fdcd-4785-b9b5-30d58b4c942a','392cced6-d52b-464b-9829-51aa9ce12468','multa','Multa por Retraso 10min - Sesion #1 del 11/06/2026',1.00,'bc026e44-32a5-4976-9683-ba8e062a9398',1,'57ae462a-c151-4cba-9aa4-83e1480f445e','2026-06-11 13:12:04'),('cedfe02a-e71e-4b76-bad1-517a89dd11b5','08ee471e-597e-4712-8d18-ab95adac7694','32d4ffda-eec7-4299-885f-f320557da01e','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'8b09d9bb-d23a-40c7-9732-e636058170ed',0,NULL,'2026-06-11 15:47:26'),('d40a1351-0348-473d-93cc-752e8ac35766','08ee471e-597e-4712-8d18-ab95adac7694','5afb15ad-ced5-431b-9fc2-970cf4919433','multa','Multa por Inasistencia - Sesion #1 del 11/06/2026',5.00,'dd83dd5b-9bbd-4d07-b779-fa908a3175fe',0,NULL,'2026-06-11 15:47:26'),('d4be9e85-98e3-46f4-a019-196a8082b778','1811a1af-1852-481c-94df-dce4edc301f0','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #1 del 11/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:10:28'),('d77c849f-4dc3-4b6a-aafb-5b956825ce93','08ee471e-597e-4712-8d18-ab95adac7694','5afb15ad-ced5-431b-9fc2-970cf4919433','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('df9ad9e5-bb48-4fe1-b6c8-8a983e761082','08ee471e-597e-4712-8d18-ab95adac7694','caaf8155-4c10-4e84-aa7b-ba4183906421','cuota_mensual','Cuota mensual - Sesion #4 del 27/09/2026',10.00,NULL,0,NULL,'2026-06-11 15:47:26'),('e0a01f95-844d-48e8-a940-6ab822bc853a','08ee471e-597e-4712-8d18-ab95adac7694','32d4ffda-eec7-4299-885f-f320557da01e','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'465df615-4153-47d5-8dab-dbfa2d3d39a0',0,NULL,'2026-06-11 15:47:26'),('e56037b6-ae4c-4316-ad1b-64ddb2939ebc','96915aed-fdcd-4785-b9b5-30d58b4c942a','9e52d148-927b-4784-b290-b8d9f9b1c35f','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:05'),('e57f9978-6408-45e1-9a54-34f178e67699','96915aed-fdcd-4785-b9b5-30d58b4c942a','c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:05'),('e71265b8-7783-4930-8720-1c952cabbe81','4ffe0148-d8eb-4007-825c-f197e5974beb','392cced6-d52b-464b-9829-51aa9ce12468','cuota_mensual','Cuota mensual - Sesion #3 del 26/07/2026',10.00,NULL,1,'fe1acab1-8d3b-4fd6-908d-bac882ad6a99','2026-06-11 13:27:53'),('eafe6fac-2eb9-4ef2-97c9-f03e98e83fce','96915aed-fdcd-4785-b9b5-30d58b4c942a','c26b7a29-755b-4665-8912-397c05d48a27','cuota_mensual','Cuota mensual - Sesion #2 del 12/06/2026',10.00,NULL,0,NULL,'2026-06-11 13:12:05'),('f6e85c23-6bec-4b30-8a22-565c093fedde','08ee471e-597e-4712-8d18-ab95adac7694','9e52d148-927b-4784-b290-b8d9f9b1c35f','multa','Multa por Inasistencia - Sesion #2 del 12/06/2026',5.00,'35bfb54e-d429-4dcc-82ae-3100f1785759',0,NULL,'2026-06-11 15:47:26');
/*!40000 ALTER TABLE `obligaciones_sesion` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Par├ímetros configurables del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametros`
--

LOCK TABLES `parametros` WRITE;
/*!40000 ALTER TABLE `parametros` DISABLE KEYS */;
INSERT INTO `parametros` VALUES (1,'tasa_interés_crédito','Tasa de interés para créditos','6.00','decimal','financiero',1),(2,'método_interés_default','Método de interés por defecto','simple','texto','financiero',1),(3,'tasa_interés_ahorro','Tasa de interés sobre ahorros','0.00','decimal','financiero',1),(4,'tasa_interés_inversión','Tasa de interés para inversiones','6.00','decimal','financiero',1),(5,'aporte_obligatorio_mensual','Aporte obligatorio mensual','10.00','decimal','financiero',1),(6,'cuota_ingreso','Cuota única de ingreso','20.00','decimal','financiero',1),(7,'multa_retraso_10min','Multa retraso 10-30 minutos','1.00','decimal','financiero',1),(8,'multa_retraso_30min','Multa retraso >=30 minutos','5.00','decimal','financiero',1),(9,'multa_inasistencia','Multa por inasistencia','5.00','decimal','financiero',1),(10,'multa_mora_crédito','Multa por mora de crédito','5.00','decimal','financiero',1),(11,'límite_crédito_emergente','Límite crédito emergente','300.00','decimal','financiero',1),(12,'plazo_mínimo_inversión','Plazo mínimo inversión (meses)','6','numero','financiero',1),(13,'intentos_máx_login','Intentos máximo de login','3','numero','seguridad',1),(14,'bloqueo_minutos','Minutos de bloqueo','15','numero','seguridad',1),(15,'session_timeout_minutos','Timeout de sesión (minutos)','30','numero','seguridad',1),(16,'pin_2fa_dígitos','Dígitos del PIN 2FA','6','numero','seguridad',1),(17,'pin_2fa_expiracion_min','Expiración PIN 2FA (minutos)','5','numero','seguridad',1),(18,'máx_reenvío_pin_hora','Máximo reenvíos PIN por hora','3','numero','seguridad',1),(19,'logo_sidebar','Logo del sidebar','ca62b9e0-de01-42cc-9bb6-0826f49dce00','texto','imagen',1),(20,'logo_sd','Logo sin fondo','d9433f2e-ffa1-48c9-bf86-b338e6796ff2','texto','imagen',1),(21,'retencion_papelera_dias','Retencion papelera (dias)','30','numero','seguridad',1),(22,'multa_cuota_impaga','Multa por cuota mensual impaga','2.00','decimal','financiero',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cat├ílogo de permisos disponibles en el sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
INSERT INTO `permisos` VALUES (1,'auth.login','Ingresar al sistema','Permite iniciar sesión en el sistema'),(2,'auth.ver_2fa','Acceder con 2FA','Permite acceder con autenticación de dos factores'),(3,'socio.registrar','Registrar nuevo socio','Permite registrar un nuevo socio en el sistema'),(4,'socio.editar','Editar datos de socio','Permite modificar los datos de un socio existente'),(5,'socio.cambiar_estado','Cambiar estado del socio','Permite cambiar el estado de un socio en su ciclo de vida'),(6,'socio.consultar','Consultar lista de socios','Permite consultar el listado de socios registrados'),(7,'socio.ver_financiero','Ver datos financieros del socio','Permite visualizar la información financiera del socio'),(8,'param.usuarios','Gestionar usuarios del sistema','CRUD completo de usuarios del sistema'),(9,'param.roles','Gestionar roles y permisos','Crear, editar y eliminar roles con permisos personalizados'),(10,'param.imagen','Configurar imagen corporativa','Gestionar logo, colores, membrete y razón social'),(11,'param.catalogos','Editar catálogos','Gestionar provincias, cantones y entidades públicas'),(12,'param.financiero','Configurar parámetros financieros','Configurar tasas, montos, plazos y métodos de interés'),(13,'producto.crear','Crear productos financieros','Crear nuevos productos de crédito e inversión'),(14,'producto.editar','Editar productos','Modificar productos financieros existentes'),(15,'producto.activar','Activar/desactivar productos','Activar o desactivar productos financieros'),(16,'cobro.aporte','Registrar cobro de aporte','Registrar cobro de aporte obligatorio y voluntario'),(17,'cobro.cuota_credito','Registrar cobro de cuota de crédito','Registrar cobro de cuotas de crédito'),(18,'cobro.multa','Registrar cobro de multa','Registrar cobro de multas generadas'),(19,'cobro.inversion','Registrar inversión voluntaria','Registrar apertura de inversión a plazo fijo'),(20,'cobro.desembolso','Realizar desembolso de crédito','Ejecutar el desembolso de un crédito aprobado'),(21,'cobro.anular','Anular cobro registrado','Anular un cobro previamente registrado'),(22,'cobro.cierre_sesion','Ejecutar cierre de sesión mensual','Cerrar la sesión mensual con generación de acta'),(23,'calculo.intereses','Ejecutar cálculo de intereses','Calcular intereses de créditos, ahorros e inversiones'),(24,'calculo.excedentes','Calcular distribución de excedentes','Calcular la distribución de excedentes entre los socios'),(25,'calculo.aprobar_excedentes','Aprobar distribución de excedentes','Aprobar la distribución de excedentes calculada'),(26,'reporte.socios','Generar reportes de socios','Generar reportes del módulo de socios'),(27,'reporte.financiero','Generar reportes financieros','Generar reportes del módulo financiero'),(28,'reporte.cobros','Generar reportes de cobros','Generar reportes del módulo de cobros'),(29,'credito.aprobar','Aprobar/rechazar creditos','Permite aprobar o rechazar solicitudes de credito en la bandeja de aprobacion'),(30,'multa.impugnar','Impugnar multas','Permite autorizar la impugnacion de multas presentadas por los socios');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cat├ílogo de productos financieros parametrizables por el Analista Financiero';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos_financieros`
--

LOCK TABLES `productos_financieros` WRITE;
/*!40000 ALTER TABLE `productos_financieros` DISABLE KEYS */;
INSERT INTO `productos_financieros` VALUES ('95f3f25d-542c-455c-a160-c65b99b9e778','Crédito Ordinario','credito',6.00,'simple',1,12,50.00,1000.00,0,0.00,1,'2026-06-09 16:32:07','<p><b>Condiciones de Crédito</b></p><p><b>Denominación:</b> Crédito Ordinario</p><p>Estas son las condiciones del producto de crédito.</p><p>De aceptar estas condiciones.</p><p>Atentamente,</p><p>La Administración.</p>',0,50.00,'porcentaje',0,0.00,15,6,'meses',1),('b53305e5-5102-49c0-9176-d164d3e98c58','Inversión 3 meses','inversion',6.00,'simple',3,3,50.00,5000.00,0,5.00,1,'2026-06-06 14:16:51','<p><br></p>',0,0.00,'dolares',0,0.00,0,0,'meses',1);
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
INSERT INTO `roles_permisos` VALUES (1,1,1),(1,2,1),(1,6,1),(1,7,1),(1,8,1),(1,9,1),(1,10,1),(1,11,1),(1,26,1),(2,1,1),(2,2,1),(2,3,1),(2,4,1),(2,5,1),(2,6,1),(2,7,1),(2,21,1),(2,22,1),(2,25,1),(2,26,1),(2,27,1),(2,28,1),(2,29,1),(3,1,1),(3,2,1),(3,4,1),(3,6,1),(3,7,1),(3,12,1),(3,13,1),(3,14,1),(3,15,1),(3,21,1),(3,22,1),(3,23,1),(3,24,1),(3,26,1),(3,27,1),(3,28,1),(4,1,1),(4,2,1),(4,3,1),(4,4,1),(4,6,1),(4,7,1),(4,16,1),(4,17,1),(4,18,1),(4,19,1),(4,20,1),(4,21,1),(4,22,1),(4,26,1),(4,27,1),(4,28,1),(4,29,1),(4,30,1),(5,1,1),(5,6,1),(5,7,1),(5,16,1),(5,17,1),(5,18,1),(5,19,1),(5,26,1),(5,28,1),(6,1,1),(7,1,1),(7,2,1),(7,3,1),(7,4,1),(7,5,1),(7,6,1),(7,7,1),(7,16,1),(7,26,1),(7,30,1);
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
  `fecha_sesion` date DEFAULT NULL COMMENT 'Fecha programada de la reunion (corte para calculo de obligaciones)',
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
INSERT INTO `sesiones_mensuales` VALUES ('08ee471e-597e-4712-8d18-ab95adac7694',4,'2026-09-27','Sesión Ordinaria Septiembre 2026','abierta','2026-06-11 15:47:26',NULL,NULL,NULL,0.00,0.00,0.00),('1811a1af-1852-481c-94df-dce4edc301f0',1,'2026-06-11','Sesión Ordinaria Junio 2026','cerrada','2026-06-11 13:10:28','2026-06-11 15:39:41','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_1_20260611.html',10.00,0.00,10.00),('4ffe0148-d8eb-4007-825c-f197e5974beb',3,'2026-07-26','Sesión Ordinaria Agosto 2026','cerrada','2026-06-11 13:27:53','2026-06-11 15:45:52','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_3_20260611.html',15.00,0.00,15.00),('96915aed-fdcd-4785-b9b5-30d58b4c942a',2,'2026-06-12','Sesión Ordinaria Julio 2026','cerrada','2026-06-11 13:12:04','2026-06-11 15:40:05','516363c5-c79a-4491-83b4-b8303ce1f286','acta_sesion_2_20260611.html',11.00,0.00,11.00);
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
INSERT INTO `socios` VALUES ('00e16557-e3cf-4738-8516-7f3fb6ddb96d','1755566677','SANCHEZ','TORRES','PEDRO','ANDRÉS','1992-11-30','masculino','soltero','Av. Central 789','023456789','0987654321','pedro.sanchez@email.com','Profesor',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('32d4ffda-eec7-4299-885f-f320557da01e','1766677788','VARGAS','CRUZ','CARLOS','MANUEL','1975-07-18','masculino','casado','Av. Sur 654','025678901','0965432109','carlos.vargas@email.com','Abogado',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('392cced6-d52b-464b-9829-51aa9ce12468','1002003000','CARRANCO','GONZALEZ','GAVINO','ALEXANDER','1983-01-19','masculino',NULL,'IBARRA','062640879','0996755645','gcarranco@hotmail.com','Msc.','foto_392cced6.jpg',NULL,NULL,'activo','2026-06-06','2026-06-06','1','acta_aprobacion_392cced6.pdf',NULL,NULL,NULL,NULL,NULL,0,'','','','',NULL,'6639e5094ac2466f6884b5fbf6ee971948e87e1ab42b4d85aea60fe4abb0689b','2026-06-06 20:39:04'),('5afb15ad-ced5-431b-9fc2-970cf4919433','1712345678','MARTÍNEZ','GÓMEZ','JUAN','CARLOS','1990-05-15','masculino','soltero','Av. Principal 123','022345678','0991234567','juan.martinez@email.com','Ingeniero',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('9e52d148-927b-4784-b290-b8d9f9b1c35f','1787654321','LÓPEZ','RAMOS','MARÍA','ELENA','1985-08-22','femenino','casado','Calle Secundaria 456','022987654','0999876543','maria.lopez@email.com','Licenciada',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('c19ef60e-9f5b-4750-a6d2-afd8f8e9ea9a','1766611122','CORDERO','QUIMI','LUIS','FELIPE','1982-09-05','masculino','casado','Av. Occidental 147','027890123','0943210987','luis.cordero@email.com','Contador',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,'8652dfff92bd9c2f868aeeaa6df9b318609755b2d466e92e9e7a97bb71cb323e','2026-06-06 14:16:51'),('c26b7a29-755b-4665-8912-397c05d48a27','1711199900','ZAMBRANO','ROSALES','MÓNICA','LISBETH','1995-01-25','femenino','union_libre','Calle Oriente 987','026789012','0954321098','monica.zambrano@email.com','Arquitecto',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51'),('caaf8155-4c10-4e84-aa7b-ba4183906421','1722233344','RAMÍREZ','VÉLEZ','ANA','LUCÍA','1988-03-10','femenino','divorciado','Calle Norte 321','024567890','0976543210','ana.ramirez@email.com','Médico',NULL,NULL,NULL,'activo','2026-06-06',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-06 14:16:51');
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
INSERT INTO `usuarios` VALUES ('1673019a-c66d-4bb8-9158-1729fa6b064a','Gavino','Carranco','1002003000','gavinocg@gmail.com','0996755645','gcarranco','$2y$12$QkuzAcoAFQ7C9f5GMMeGS.1smyFeCvsvJeESlsmkB00oBEfzlLYjO',1,0,0,NULL,0,'2026-06-06 16:38:03','2026-06-11 15:02:36'),('516363c5-c79a-4491-83b4-b8303ce1f286','Tesorero','Caja','1003560438','gcarranco@hotmail.com','','tesorero','$2y$12$/gRI9LwajMIzc8e/NYxO6.hCsUvfbH3c.yxuEKpkpRT7AXoL2ojxe',1,0,0,NULL,0,'2026-06-06 18:23:36','2026-06-11 15:02:46'),('6600ae1d-e99d-4986-b337-0741de09df84','CARLOS MANUEL','VARGAS CRUZ','1766677788','carlos.vargas@email.com','','1766677788','$2y$12$wx0/HsCyDTfUlKWE8twT/uRPv2o/MEOWXbadf9piFa6So5g39Trie',1,0,0,NULL,0,'2026-06-06 19:35:51','2026-06-06 19:44:55'),('ce86e169-fa0a-468d-bb04-ca7b8c7a5291','Admin','Sistema','1002606083','admin@caja.test','0999999999','admin','$2y$12$IP4hst3.3yCimzqw/bO8JOYscRjkeQADlesFcttSetTnxNCRY.N8G',1,0,0,NULL,0,'2026-06-06 14:16:51','2026-06-11 15:20:56');
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

-- Dump completed on 2026-06-11 15:56:25
