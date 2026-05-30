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
-- Table structure for table `parents`
--

DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parents` (
  `ParentId` int(11) NOT NULL,
  `AdmissionNo` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `father_age` int(11) NOT NULL,
  `father_contact` varchar(50) NOT NULL,
  `father_email` varchar(150) NOT NULL,
  `father_occupation` varchar(100) NOT NULL,
  `father_education` varchar(100) NOT NULL,
  `mother_name` varchar(255) NOT NULL,
  `mother_age` int(11) NOT NULL,
  `mother_contact` varchar(50) NOT NULL,
  `mother_email` varchar(150) NOT NULL,
  `mother_occupation` varchar(100) NOT NULL,
  `mother_education` varchar(100) NOT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_relation` enum('Brother','Sister','Uncle','Aunt','Grandparent','Other') DEFAULT NULL,
  `guardian_age` int(11) DEFAULT NULL,
  `guardian_contact` varchar(50) DEFAULT NULL,
  `guardian_email` varchar(150) DEFAULT NULL,
  `guardian_occupation` varchar(100) DEFAULT NULL,
  `guardian_education` varchar(100) DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `MoreInformation` text DEFAULT NULL,
  PRIMARY KEY (`ParentId`),
  UNIQUE KEY `Unique_Student_Parent` (`AdmissionNo`),
  KEY `fk_parents_branch` (`branch_id`),
  CONSTRAINT `fk_parents_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`),
  CONSTRAINT `fk_parents_student` FOREIGN KEY (`AdmissionNo`) REFERENCES `students` (`AdmissionNo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parents`
--

LOCK TABLES `parents` WRITE;
/*!40000 ALTER TABLE `parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `parents` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-20 12:40:21
