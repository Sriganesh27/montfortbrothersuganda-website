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
-- Table structure for table `academichistory`
--

DROP TABLE IF EXISTS `academichistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `academichistory` (
  `HistoryID` int(11) NOT NULL,
  `AdmissionNo` int(11) NOT NULL,
  `FormerSchool` varchar(255) NOT NULL,
  `PLEIndexNumber` varchar(50) NOT NULL,
  `PLEAggregate` int(11) NOT NULL,
  `UCEIndexNumber` varchar(50) NOT NULL,
  `UCEResult` text NOT NULL,
  `LIN` varchar(50) DEFAULT NULL,
  `Combination` enum('PCM/ICT','BCM/ICT','PCB/SUBMATH','MEG/ICT','DEG/ICT','BEM/ICT','HEG/ICT','PEM/ICT') DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  PRIMARY KEY (`HistoryID`),
  UNIQUE KEY `Unique_Student_History` (`AdmissionNo`),
  KEY `fk_history_branch` (`branch_id`),
  CONSTRAINT `fk_academic_student` FOREIGN KEY (`AdmissionNo`) REFERENCES `students` (`AdmissionNo`) ON DELETE CASCADE,
  CONSTRAINT `fk_history_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academichistory`
--

LOCK TABLES `academichistory` WRITE;
/*!40000 ALTER TABLE `academichistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `academichistory` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-20 12:40:28
