-- MySQL dump 10.13  Distrib 5.5.33, for Linux (i686)
--
-- Host: localhost    Database: flipIt
-- ------------------------------------------------------
-- Server version	5.5.33

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `info_treatment`
--

DROP TABLE IF EXISTS `info_treatment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `info_treatment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `information_sets` text NOT NULL,
  `opponent_description` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `info_treatment`
--

LOCK TABLES `info_treatment` WRITE;
/*!40000 ALTER TABLE `info_treatment` DISABLE KEYS */;
INSERT INTO `info_treatment` VALUES (1,'None','You do not get any information about how your opponent is playing. He may adapt to the moves you make.',1,'2013-02-06 23:33:22'),(2,'I1','Your opponent is not adapting to the moves you are playing. He is a computer that has a fixed strategy.',1,'2013-02-20 05:58:48'),(3,'I1,I2','Your opponent is not adapting to the moves you are playing. He is a computer that has a fixed, periodic strategy. This means that he the distance between his flips will be constant (e.g. in game 1, he may flip every 6 seconds). This flip rate changes every game.',1,'2013-02-19 23:36:29'),(4,'I1,I3','Your opponent is not adapting to the moves you are playing. However, he is playing at an average flip rate of %{alpha}. Some flips may be shorter or farther apart than this rate, but the average is %{alpha}.',0,'2013-03-18 16:56:55'),(5,'I1,I2,I3','Your opponent is not adapting to the moves you are playing. He is a computer that has a fixed, periodic strategy. This means that he the distance between his flips will be constant For this game, he will be flipping at a rate of %{alpha}. This flip rate changes every game, but you will know about it.',1,'2013-02-07 18:49:49'),(6,'I1,I2,I3,I4','Your opponent is not adapting to the moves you are playing. He is a computer that has a fixed, periodic strategy. This means that he the distance between his flips will be constant For this game, he will be flipping at a rate of %{alpha}. His first flip will be at time %{anchor} (this first flip time is called the anchor). This flip rate and anchor change every game, but you will know about what the new values are.',0,'2013-03-18 16:57:08');
/*!40000 ALTER TABLE `info_treatment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visual_treatment`
--

DROP TABLE IF EXISTS `visual_treatment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `visual_treatment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_type` text NOT NULL,
  `feedback_type_full` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visual_treatment`
--

LOCK TABLES `visual_treatment` WRITE;
/*!40000 ALTER TABLE `visual_treatment` DISABLE KEYS */;
INSERT INTO `visual_treatment` VALUES (1,'NA','Nonadaptive',0,'2013-03-15 15:32:31'),(2,'LM','Last move',1,'2013-03-15 15:33:10'),(3,'FH','Full history',1,'2013-03-15 15:33:19');
/*!40000 ALTER TABLE `visual_treatment` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-10-17 19:09:41
