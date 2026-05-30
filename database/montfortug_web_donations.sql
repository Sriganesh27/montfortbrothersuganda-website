-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 68.178.237.26    Database: montfortug
-- ------------------------------------------------------
-- Server version	5.5.5-10.6.24-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `web_donations`
--

DROP TABLE IF EXISTS `web_donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `web_donations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receipt_number` varchar(20) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `is_anonymous` tinyint(1) DEFAULT 0,
  `contribution_purpose` varchar(100) NOT NULL,
  `project_id` varchar(20) DEFAULT NULL,
  `currency` varchar(20) NOT NULL,
  `amount` decimal(15,2) DEFAULT 0.00,
  `amount_received` decimal(15,2) DEFAULT NULL,
  `payment_status` enum('success','failed') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `web_donations`
--

LOCK TABLES `web_donations` WRITE;
/*!40000 ALTER TABLE `web_donations` DISABLE KEYS */;
INSERT INTO `web_donations` VALUES (1,'SSP001-001','Dennis Rodricks','dennisrodricks@yahoo.com','+1 ','Florida USA',0,'Scholarships for student','SSP001','USD',300.00,NULL,'success','R454886400657','','2026-03-09 11:14:39'),(2,'IDP001-001','Sri Ganesh','sriganeshgoud9154@gmail.comd','+91 9542814525','Hyderabad',1,'Infrastructure Development','IDP001','USD',10.00,NULL,'success','Ts23575','','2026-04-03 14:14:52'),(3,'IDP001-002','Sri Ganesh','sriganeshgoud9154@gmail.comd','+91 9542814525','Hyderabad',0,'Infrastructure Development','IDP001','USD',3.00,NULL,'success','dde54','','2026-04-03 14:30:52'),(4,'IDP001-003','Sri Ganesh Sudhagani','sriganeshgoud9154@gmail.com','+91 9542814525','Hyderabad',0,'Infrastructure Development','IDP001','USD',13.00,NULL,'success','j87n','','2026-04-15 13:39:59'),(5,'IDP001-004','Unite Peace','uniteallforpeace@gmail.com','+256 877889','mjhiu',0,'Infrastructure Development','IDP001','USD',1.00,NULL,'success','mj8','','2026-04-16 14:18:21');
/*!40000 ALTER TABLE `web_donations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-17 22:40:23
