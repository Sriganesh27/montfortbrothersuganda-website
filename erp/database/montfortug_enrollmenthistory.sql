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
-- Table structure for table `enrollmenthistory`
--

DROP TABLE IF EXISTS `enrollmenthistory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollmenthistory` (
  `EnrollmentHistoryID` int(11) NOT NULL AUTO_INCREMENT,
  `AdmissionNo` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `AcademicYear` varchar(20) NOT NULL,
  `Level` varchar(50) NOT NULL,
  `Class` varchar(50) NOT NULL,
  `Term` varchar(50) NOT NULL,
  `Stream` varchar(50) DEFAULT NULL,
  `Residence` varchar(50) NOT NULL,
  `EntryStatus` varchar(50) NOT NULL,
  `DateMoved` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`EnrollmentHistoryID`),
  KEY `AdmissionNo` (`AdmissionNo`),
  KEY `fk_enroll_history_branch` (`branch_id`),
  CONSTRAINT `fk_enroll_history_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`branch_id`),
  CONSTRAINT `fk_history_student` FOREIGN KEY (`AdmissionNo`) REFERENCES `students` (`AdmissionNo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enrollmenthistory`
--

LOCK TABLES `enrollmenthistory` WRITE;
/*!40000 ALTER TABLE `enrollmenthistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `enrollmenthistory` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-20 12:40:33
