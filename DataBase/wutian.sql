-- MySQL dump 10.13  Distrib 5.6.24, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: wutian
-- ------------------------------------------------------
-- Server version	5.6.24-log

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `CategoryId` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(100) NOT NULL,
  `ParentId` int(11) NOT NULL,
  `DeptList` varchar(255) DEFAULT 'All',
  `FilePath` varchar(255) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `PAList` varchar(255) DEFAULT 'All',
  `ProductList` varchar(255) DEFAULT 'All',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`CategoryId`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'总分类',0,'All','d:/tmp/file/',1,'All','All',1,'2015-05-20 14:56:32',1,'2015-05-20 14:56:36'),(2,'分类1',1,',1,,2,,6,,7,,3,,10,,21,','d:/tmp/file/2',1,',9,,11,',',7,,14,,15,',1,'2015-05-20 15:14:10',1,'2015-06-04 11:42:48'),(3,'分类2',1,',1,,2,,6,,7,,3,,10,,21,','d:/tmp/file/2',1,',9,,11,',',7,,14,,15,',1,'2015-05-20 15:14:10',1,'2015-06-04 11:42:48'),(4,'分类3',1,',1,,2,,6,,7,,3,,10,,21,','d:/tmp/file/2',1,',9,,11,',',7,,14,,15,',1,'2015-05-20 15:14:10',1,'2015-06-04 11:42:48'),(5,'分类4',1,',1,,2,,18,,3,,10,','d:/tmp/file//5',1,',12,',',17,',1,'2015-06-16 19:58:54',1,'2015-06-16 19:58:54'),(6,'C1001',4,',1,,2,,20,,27,','d:/tmp/file/2/6',1,',21,',',30,',1,'2015-06-17 12:20:16',1,'2015-06-17 12:20:16');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `depts`
--

DROP TABLE IF EXISTS `depts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `depts` (
  `DeptId` int(11) NOT NULL AUTO_INCREMENT,
  `DeptName` varchar(100) NOT NULL,
  `DeptCode` varchar(20) DEFAULT NULL,
  `ParentId` int(11) NOT NULL DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '0',
  `PAList` varchar(255) NOT NULL DEFAULT 'All',
  `ProductList` varchar(255) NOT NULL DEFAULT 'All',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`DeptId`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `depts`
--

LOCK TABLES `depts` WRITE;
/*!40000 ALTER TABLE `depts` DISABLE KEYS */;
INSERT INTO `depts` VALUES (1,'总公司','HQ',0,1,'ALL','ALL',1,'2015-01-01 00:00:00',1,'2015-01-01 00:00:00'),(2,'北京分公司','Beijing',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(3,'上海分公司','Shanghai',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(4,'广州分公司','Guangzhou',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(5,'北京销售1','BJ_sales_1',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(6,'北京销售2','BJ_sales_2',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(7,'北京销售3','BJ_sales_3',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(8,'上海销售1','SH_sales_1',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(9,'上海销售2','SH_sales_2',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(10,'上海销售3','SH_sales_3',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(11,'广州销售1','GZ_sales_1',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(12,'广州销售2','GZ_sales_2',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(13,'广州销售3','GZ_sales_3',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(14,'总公司','HQ',0,1,'ALL','ALL',1,'2015-01-01 00:00:00',1,'2015-01-01 00:00:00'),(15,'北京分公司','Beijing',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(16,'上海分公司','Shanghai',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(17,'广州分公司','Guangzhou',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(18,'北京销售1','BJ_sales_1',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(19,'北京销售2','BJ_sales_2',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(20,'北京销售3','BJ_sales_3',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(21,'上海销售1','SH_sales_1',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(22,'上海销售2','SH_sales_2',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(23,'上海销售3','SH_sales_3',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(24,'广州销售1','GZ_sales_1',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(25,'广州销售2','GZ_sales_2',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(26,'广州销售3','GZ_sales_3',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(27,'我的部门','我的部门',20,1,',11,',',16,',1,'2015-06-16 19:57:42',1,'2015-06-16 19:57:42'),(28,'Dept11','Dept11',10,1,',19,',',27,',1,'2015-06-17 12:18:47',1,'2015-06-17 12:18:47');
/*!40000 ALTER TABLE `depts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `examanswer`
--

DROP TABLE IF EXISTS `examanswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examanswer` (
  `ExamId` int(11) NOT NULL,
  `ProblemId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `ProblemType` int(11) NOT NULL,
  `Score` float NOT NULL,
  `Answer` varchar(100) NOT NULL,
  `Result` int(11) NOT NULL,
  PRIMARY KEY (`ExamId`,`UserId`,`ProblemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `examanswer`
--

LOCK TABLES `examanswer` WRITE;
/*!40000 ALTER TABLE `examanswer` DISABLE KEYS */;
/*!40000 ALTER TABLE `examanswer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `examroll`
--

DROP TABLE IF EXISTS `examroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examroll` (
  `ExamId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `IsSubmit` int(11) NOT NULL DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ExamId`,`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `examroll`
--

LOCK TABLES `examroll` WRITE;
/*!40000 ALTER TABLE `examroll` DISABLE KEYS */;
/*!40000 ALTER TABLE `examroll` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exams` (
  `ExamId` int(11) NOT NULL AUTO_INCREMENT,
  `ExamName` varchar(100) NOT NULL,
  `ExamType` int(11) NOT NULL DEFAULT '0',
  `ExamLocation` int(11) DEFAULT NULL,
  `ExamBegin` datetime DEFAULT NULL,
  `ExamEnd` datetime DEFAULT NULL,
  `ExamAnsType` int(11) NOT NULL DEFAULT '2',
  `ExamPassword` varchar(100) DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT '0',
  `ExamDesc` varchar(255) DEFAULT NULL,
  `ExamContent` varchar(255) NOT NULL,
  `ExpireTime` datetime NOT NULL,
  `CreatedUser` int(11) NOT NULL,
  `CreatedTime` datetime NOT NULL,
  `EditUser` int(11) NOT NULL,
  `EditTime` datetime NOT NULL,
  PRIMARY KEY (`ExamId`),
  UNIQUE KEY `ExamName` (`ExamName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `examscore`
--

DROP TABLE IF EXISTS `examscore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examscore` (
  `ExamId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Score` float NOT NULL,
  PRIMARY KEY (`ExamId`,`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `examscore`
--

LOCK TABLES `examscore` WRITE;
/*!40000 ALTER TABLE `examscore` DISABLE KEYS */;
/*!40000 ALTER TABLE `examscore` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `files` (
  `FileId` int(11) NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) NOT NULL,
  `FileTitle` varchar(255) DEFAULT NULL,
  `FileDesc` varchar(255) DEFAULT NULL,
  `FilePath` varchar(255) NOT NULL,
  `SmallGifPath` varchar(255) NOT NULL,
  `PageNo` int(11) NOT NULL,
  `FileType` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `CategoryId` int(11) DEFAULT NULL,
  `ZipSize` int(11) DEFAULT NULL,
  PRIMARY KEY (`FileId`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
INSERT INTO `files` VALUES (1,'119.pdf','test1','test1','d:/tmp/file/2/1','',4,1,0,1,'2015-06-09 12:52:16',1,'2015-06-09 12:52:16',2,831106),(2,'119.pdf','test2','test2','d:/tmp/file/2/2','',4,1,0,1,'2015-06-09 13:04:45',1,'2015-06-09 13:04:45',2,831111),(3,'123.pdf','test2','test3','d:/tmp/file/2/3','',0,1,-1,1,'2015-06-12 11:54:42',1,'2015-06-12 11:54:42',4,NULL),(4,'119.pdf','test3','test1','d:/tmp/file/2/4','',0,1,-1,1,'2015-06-12 11:56:05',1,'2015-06-12 11:56:05',4,NULL),(5,'119.pdf','test3','test2','d:/tmp/file/2/5','',0,1,-1,1,'2015-06-12 12:06:59',1,'2015-06-12 12:06:59',4,NULL),(6,'119.pdf','test2','test1','d:/tmp/file/2/6','',4,1,-1,1,'2015-06-12 12:09:19',1,'2015-06-12 12:09:19',4,831116),(7,'119.pdf','test4','test4','d:/tmp/file/2/7','',4,1,-1,1,'2015-06-12 12:09:44',1,'2015-06-12 12:09:44',4,831113),(8,'123.pdf','test5','test5','d:/tmp/file/2/8','',18,1,0,1,'2015-06-12 12:11:47',1,'2015-06-12 12:11:47',4,5639914),(9,'SOAP Login.zip','test17','test17','d:/tmp/file/2/9','',0,3,0,1,'2015-06-17 15:50:12',1,'2015-06-17 15:50:12',2,NULL),(10,'SOAP Login.zip','test17-1','test17-1','d:/tmp/file/2/6/10','',0,3,-1,1,'2015-06-17 15:55:45',1,'2015-06-17 15:55:45',6,NULL),(11,'SOAP Login.zip','test17-1','test17-1','d:/tmp/file/2/11','',0,3,-1,1,'2015-06-17 15:56:31',1,'2015-06-17 15:56:31',4,NULL),(12,'119.zip','test17-1','test17-1','d:/tmp/file/2/12','',0,3,-1,1,'2015-06-17 15:57:13',1,'2015-06-17 15:57:13',4,NULL),(13,'SOAP Login.zip','test17-1','test17-1','d:/tmp/file/2/13','',0,3,-1,1,'2015-06-17 16:00:52',1,'2015-06-17 16:00:52',4,NULL),(14,'119.zip','test17-1','test17-1','d:/tmp/file/2/6/14','',0,3,-1,1,'2015-06-17 16:01:18',1,'2015-06-17 16:01:18',6,NULL),(15,'SOAP Login.zip','test17-1','test17-1','d:/tmp/file/2/6/15','',0,3,-1,1,'2015-06-17 16:02:56',1,'2015-06-17 16:02:56',6,NULL),(16,'SOAP Login.zip','test17-1','test17-1','d:/tmp/file/2/6/16','',0,4,0,1,'2015-06-17 16:08:12',1,'2015-06-17 16:08:12',6,NULL),(17,'SOAP Login.zip','test17-2','test17-2','d:/tmp/file/2/6/17','',0,4,0,1,'2015-06-17 16:13:36',1,'2015-06-17 16:13:36',6,5919),(18,'C&B新员工入职培训20141125.pdf','abc','abc','d:/tmp/file/2/6/18','',0,1,0,1,'2015-06-17 19:30:47',1,'2015-06-17 19:30:47',6,NULL);
/*!40000 ALTER TABLE `files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `functions`
--

DROP TABLE IF EXISTS `functions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `functions` (
  `FunctionId` int(11) NOT NULL AUTO_INCREMENT,
  `FunctionName` varchar(50) NOT NULL,
  `FunctionType` int(11) NOT NULL DEFAULT '0',
  `CreatedTime` datetime DEFAULT NULL,
  `Icon` varchar(100) DEFAULT NULL,
  `CodePath` varchar(200) DEFAULT NULL,
  `Rank` int(11) DEFAULT NULL,
  PRIMARY KEY (`FunctionId`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `functions`
--

LOCK TABLES `functions` WRITE;
/*!40000 ALTER TABLE `functions` DISABLE KEYS */;
INSERT INTO `functions` VALUES (1,'公告管理',0,'2015-05-21 15:57:45','news','New\\News_list.php',1),(2,'人员管理',0,'2015-05-21 15:57:45','userMgmt','User\\Users_list.php',2),(3,'部门管理',0,'2015-05-21 15:57:45','depts','Dept\\Depts_list.php',3),(4,'分类管理',0,'2015-05-21 15:57:45','categories','Category\\Categories_list.php',4),(5,'后台权限管理',0,'2015-05-21 17:04:20','setting','Admin\\Privileges_list.php',6),(6,'文档管理',0,'2015-05-22 08:00:00','files','File\\Files_list.php',5),(7,'内分泌',2,'2015-06-04 10:37:33',NULL,NULL,NULL),(8,'必洛斯',1,'2015-06-04 10:38:00',NULL,NULL,NULL),(9,'艾可拓',1,'2015-06-04 10:38:00',NULL,NULL,NULL),(10,'倍欣',1,'2015-06-04 10:38:00',NULL,NULL,NULL),(11,'尼欣那',1,'2015-06-04 10:38:00',NULL,NULL,NULL),(12,'潘针',1,'2015-06-04 10:38:00',NULL,NULL,NULL),(14,'心血管',2,'2015-06-04 10:38:00',NULL,NULL,NULL),(15,'消化',2,'2015-06-04 10:38:00',NULL,NULL,NULL),(16,'乳腺',2,'2015-06-04 10:38:00',NULL,NULL,NULL),(17,'泌尿',2,'2015-06-04 10:38:00',NULL,NULL,NULL),(18,'潘片',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(19,'达克普隆',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(20,'普托平',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(21,'抑那通',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(22,'亚宁定',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(23,'开思亭',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(24,'爱宝疗',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(25,'其他',1,'2015-06-11 12:00:43',NULL,NULL,NULL),(26,'儿科',2,'2015-06-11 12:03:58',NULL,NULL,NULL),(27,'妇科',2,'2015-06-11 12:03:58',NULL,NULL,NULL),(28,'危重症',2,'2015-06-11 12:03:58',NULL,NULL,NULL),(29,'抗过敏',2,'2015-06-11 12:03:58',NULL,NULL,NULL),(30,'其他',2,'2015-06-11 12:03:58',NULL,NULL,NULL),(31,'题目管理',0,'2015-05-21 17:04:20','problem','Problem/Problems_list.php',8),(32,'考卷管理',0,'2015-05-21 17:04:20','exam','Exam/Exams_list.php',7),(33,'乳腺癌',1,'2015-05-21 15:57:45',NULL,NULL,NULL),(34,'前列腺癌',1,'2015-05-21 15:57:45',NULL,NULL,NULL),(35,'其他',3,'2015-05-21 15:57:45',NULL,NULL,NULL),(36,'产品知识',3,'2015-05-21 15:57:45',NULL,NULL,NULL),(37,'市场策略',3,'2015-05-21 15:57:45',NULL,NULL,NULL),(38,'文献',3,'2015-05-21 15:57:45',NULL,NULL,NULL),(39,'医学知识',3,'2015-05-21 15:57:45',NULL,NULL,NULL),(40,'文献解读',3,'2015-05-21 15:57:45',NULL,NULL,NULL),(41,'合规',3,'2015-05-21 15:57:45',NULL,NULL,NULL);
/*!40000 ALTER TABLE `functions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `LogId` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` varchar(200) NOT NULL,
  `FunctionName` varchar(200) NOT NULL,
  `ActionName` varchar(200) NOT NULL,
  `ActionTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ActionReturn` text NOT NULL,
  `ActionObject` varchar(200) NOT NULL,
  PRIMARY KEY (`LogId`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
INSERT INTO `log` VALUES (1,'UserId001logapi','FunctionName002logapi','ActionName002logapi','2015-06-01 18:18:18','ActionReturn--092logapi','ActionObject--003logapi');
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `NewId` int(11) NOT NULL AUTO_INCREMENT,
  `NewTitle` varchar(255) NOT NULL,
  `NewMsg` varchar(255) NOT NULL,
  `DeptList` varchar(255) NOT NULL DEFAULT 'All',
  `Status` int(11) NOT NULL DEFAULT '0',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `OccurTime` datetime DEFAULT NULL,
  PRIMARY KEY (`NewId`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES (1,'公告测试1','公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1公告测试1',',1,,2,,5,,3,,8,,9,,10,,21,,22,,23,,4,,11,,12,,13,,24,,25,,26,,15,,16,,17,',1,1,'2015-06-01 21:06:38',1,'2015-06-04 10:28:01',NULL),(2,'公告测试2','公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2公告测试2',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(3,'公告测试3','公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3公告测试3',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(4,'公告测试4','公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4公告测试4',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(5,'公告测试5','公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5公告测试5',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(6,'公告测试6','公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6公告测试6',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(7,'公告测试7','公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7公告测试7',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(8,'公告测试8','公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8公告测试8',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(9,'公告测试9','公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9公告测试9',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(10,'公告测试10','公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10公告测试10',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38',NULL),(11,'活动测试1','活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1活动测试1',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(12,'活动测试2','活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2活动测试2',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(13,'活动测试3','活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3活动测试3',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(14,'活动测试4','活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4活动测试4',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(15,'活动测试5','活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5活动测试5',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(16,'活动测试6','活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6活动测试6',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(17,'活动测试7','活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7活动测试7',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(18,'活动测试8','活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8活动测试8',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(19,'活动测试9','活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9活动测试9',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(20,'活动测试10','活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10活动测试10',',1,',1,1,'2015-06-01 21:06:38',1,'2015-06-01 21:06:38','2015-06-02 15:30:36'),(21,'ssss','ssss','',-1,1,'2015-06-17 12:03:23',1,'2015-06-17 12:03:23','2015-06-24 00:00:00'),(22,'ssss','ssss','',-1,1,'2015-06-17 12:04:55',1,'2015-06-17 12:04:55','2015-06-24 00:00:00'),(23,'sss','ssss','',-1,1,'2015-06-17 12:10:53',1,'2015-06-17 12:10:53','2015-06-24 00:00:00'),(24,'sss','sss','',-1,1,'2015-06-17 12:13:49',1,'2015-06-17 12:13:49','2015-06-24 00:00:00'),(25,'sss','sss','',-1,1,'2015-06-17 12:14:06',1,'2015-06-17 12:14:06','2015-06-24 00:00:00'),(26,'sss','sss',',1,,2,,18,',-1,1,'2015-06-17 12:15:50',1,'2015-06-17 12:15:50','2015-06-24 00:00:00'),(27,'sss','sss',',1,,2,,18,',-1,1,'2015-06-17 12:27:16',1,'2015-06-17 12:27:16','2015-06-24 00:00:00');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privileges`
--

DROP TABLE IF EXISTS `privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privileges` (
  `PrivilegeId` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL,
  `FunctionId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`PrivilegeId`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privileges`
--

LOCK TABLES `privileges` WRITE;
/*!40000 ALTER TABLE `privileges` DISABLE KEYS */;
INSERT INTO `privileges` VALUES (5,2,2,1,1,'2015-05-21 16:01:48',1,'2015-05-21 16:01:48'),(6,2,4,1,1,'2015-05-21 16:01:48',1,'2015-05-21 16:01:48'),(26,10,4,0,1,'2015-06-17 12:21:03',1,'2015-06-17 12:21:03'),(27,1,1,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(28,1,2,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(29,1,3,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(30,1,4,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(31,1,5,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(32,1,6,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(33,1,31,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28'),(34,1,32,0,1,'2015-06-18 11:04:28',1,'2015-06-18 11:04:28');
/*!40000 ALTER TABLE `privileges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `problems`
--

DROP TABLE IF EXISTS `problems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problems` (
  `ProblemId` int(11) NOT NULL AUTO_INCREMENT,
  `ProblemType` int(11) DEFAULT NULL,
  `ProblemDesc` varchar(512) NOT NULL,
  `ProblemSelectA` varchar(512) DEFAULT NULL,
  `ProblemSelectB` varchar(512) DEFAULT NULL,
  `ProblemSelectC` varchar(512) DEFAULT NULL,
  `ProblemSelectD` varchar(512) DEFAULT NULL,
  `ProblemSelectE` varchar(512) DEFAULT NULL,
  `ProblemSelectF` varchar(512) DEFAULT NULL,
  `ProblemSelectG` varchar(512) DEFAULT NULL,
  `ProblemSelectH` varchar(512) DEFAULT NULL,
  `ProblemSelectI` varchar(512) DEFAULT NULL,
  `ProblemAnswer` varchar(512) DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT '0',
  `ProblemCategory` varchar(50) NOT NULL,
  `ProblemLevel` int(11) NOT NULL DEFAULT '1',
  `ProblemMemo` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`ProblemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `problems`
--

LOCK TABLES `problems` WRITE;
/*!40000 ALTER TABLE `problems` DISABLE KEYS */;
/*!40000 ALTER TABLE `problems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `UserId` int(11) NOT NULL AUTO_INCREMENT,
  `UserName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `DeptId` int(11) DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '1',
  `CanApprove` int(11) NOT NULL DEFAULT '0',
  `JobGrade` int(11) DEFAULT NULL,
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `EmployeeId` varchar(20) NOT NULL,
  `LastModifyTime` datetime DEFAULT NULL,
  PRIMARY KEY (`UserId`),
  UNIQUE KEY `EmployeeId` (`EmployeeId`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Phantom','phantom@intF1ocus.com',10,1,1,1,1,'2015-05-19 16:10:44',1,'2015-05-20 19:43:50','p100','2015-06-18 13:55:39'),(2,'phantom2','phantom@163com',10,0,0,1,1,'2015-05-19 18:38:41',1,'2015-05-20 19:31:45','2',NULL),(3,'姓名1','Email1',8,-1,0,0,1,'2015-05-20 17:55:40',1,'2015-05-20 17:55:40','工号1',NULL),(4,'姓名2','Email2',8,-1,0,0,1,'2015-05-20 17:55:40',1,'2015-05-20 17:55:40','工号2',NULL),(10,'姓名3','Email3',12,1,0,0,1,'2015-05-20 18:01:35',1,'2015-05-20 18:01:35','工号3',NULL),(11,'姓名4','Email4',12,1,0,0,1,'2015-05-20 18:01:35',1,'2015-05-20 18:01:35','工号4',NULL),(12,'Phantom100','phantom100@163.com',6,1,0,1,1,'2015-05-20 18:45:25',1,'2015-05-20 18:48:48','100',NULL),(13,'Phantom101','phantom101@163.com',7,1,0,1,1,'2015-05-20 18:47:50',1,'2015-05-20 18:47:50','101',NULL),(14,'姓名5','Email5@abc.com',18,1,0,1,1,'2015-05-20 19:59:08',1,'2015-05-20 19:59:08','工号5',NULL),(15,'姓名6','Email6@ccc.com',18,1,0,1,1,'2015-05-20 19:59:08',1,'2015-05-20 19:59:08','工号6',NULL),(16,'Phantom3','phantom3@163.com',6,1,0,1,1,'2015-05-21 14:46:46',1,'2015-05-21 14:46:46','p300','2015-06-17 15:49:06'),(17,'eric1','eric1@126.com',19,1,0,1,1,'2015-06-17 12:17:44',1,'2015-06-17 12:17:44','eric1',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-06-18 14:23:15
