-- MySQL dump 10.13  Distrib 5.6.24, for Win64 (x86_64)
--
-- Host: localhost    Database: wutian
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'总分类',0,'All','d:\\\\tmp\\\\file\\\\',1,'All','All',1,'2015-05-20 14:56:32',1,'2015-05-20 14:56:36'),(2,'分类1',1,'All','d:\\tmp\\file\\',1,',PAL3,',',PL3,',1,'2015-05-20 15:14:10',1,'2015-05-20 15:14:10');
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `depts`
--

LOCK TABLES `depts` WRITE;
/*!40000 ALTER TABLE `depts` DISABLE KEYS */;
INSERT INTO `depts` VALUES (1,'总公司','HQ',0,1,'ALL','ALL',1,'2015-01-01 00:00:00',1,'2015-01-01 00:00:00'),(2,'北京分公司','Beijing',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(3,'上海分公司','Shanghai',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(4,'广州分公司','Guangzhou',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(5,'北京销售1','BJ_sales_1',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(6,'北京销售2','BJ_sales_2',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(7,'北京销售3','BJ_sales_3',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(8,'上海销售1','SH_sales_1',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(9,'上海销售2','SH_sales_2',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(10,'上海销售3','SH_sales_3',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(11,'广州销售1','GZ_sales_1',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(12,'广州销售2','GZ_sales_2',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(13,'广州销售3','GZ_sales_3',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(14,'总公司','HQ',0,1,'ALL','ALL',1,'2015-01-01 00:00:00',1,'2015-01-01 00:00:00'),(15,'北京分公司','Beijing',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(16,'上海分公司','Shanghai',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(17,'广州分公司','Guangzhou',1,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(18,'北京销售1','BJ_sales_1',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(19,'北京销售2','BJ_sales_2',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(20,'北京销售3','BJ_sales_3',2,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(21,'上海销售1','SH_sales_1',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(22,'上海销售2','SH_sales_2',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(23,'上海销售3','SH_sales_3',3,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(24,'广州销售1','GZ_sales_1',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(25,'广州销售2','GZ_sales_2',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00'),(26,'广州销售3','GZ_sales_3',4,1,'ALL','ALL',1,'2001-01-01 00:00:00',1,'2015-01-01 00:00:00');
/*!40000 ALTER TABLE `depts` ENABLE KEYS */;
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
  `CategeryId` int(11) NOT NULL,
  `FilePath` varchar(255) NOT NULL,
  `SmallGifPath` varchar(255) NOT NULL,
  `PageNo` int(11) NOT NULL,
  `FileType` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`FileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `files`
--

LOCK TABLES `files` WRITE;
/*!40000 ALTER TABLE `files` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `functions`
--

LOCK TABLES `functions` WRITE;
/*!40000 ALTER TABLE `functions` DISABLE KEYS */;
INSERT INTO `functions` VALUES (0, '公告管理',0,'2015-05-21 15:57:45', 'news', 'New/News_list.php', 1),(1, '人员管理',0,'2015-05-21 15:57:45', 'userMgmt', 'User/Users_list.php', 2),(2,'部门管理',0,'2015-05-21 15:57:45', 'depts', 'Dept/Depts_list.php', 3),(3,'分类管理',0,'2015-05-21 15:57:45', 'categories', 'Category/Categories_list.php', 4),(4,'后台权限管理',0,'2015-05-21 17:04:20', 'setting', 'Admin/Privileges_list.php',8), (5,'档案管理',0,'2015-05-21 17:04:20','files','File/Files_list.php',5),(6,'题目管理',0,'2015-05-21 17:04:20','problem','Problem/Problems_list.php',6),(7,'考卷管理',0,'2015-05-21 17:04:20','exam','Exam/Exams_list.php',7);
INSERT INTO `functions` VALUES (50, '乳腺癌',1,'2015-05-21 15:57:45',null,null,null),(51,'儿科',1,'2015-05-21 15:57:45',null,null,null),(52,'妇科',1,'2015-05-21 15:57:45',null,null,null), (53, '前列腺癌',1,'2015-05-21 15:57:45',null,null,null);
INSERT INTO `functions` VALUES (60, '尼欣那',2,'2015-05-21 15:57:45',null,null,null),(61,'潘针',2,'2015-05-21 15:57:45',null,null,null),(62,'潘片',2,'2015-05-21 15:57:45',null,null,null), (63, '艾可拓',2,'2015-05-21 15:57:45',null,null,null), (64, '倍欣',2,'2015-05-21 15:57:45',null,null,null), (65, '必洛斯',2,'2015-05-21 15:57:45',null,null,null);
INSERT INTO `functions` VALUES (80, '达克普隆',2,'2015-05-21 15:57:45',null,null,null),(81,'普托平',2,'2015-05-21 15:57:45',null,null,null),(82,'抑那通',2,'2015-05-21 15:57:45',null,null,null),(83, '开思亭',2,'2015-05-21 15:57:45',null,null,null),(84, '亚宁定',2,'2015-05-21 15:57:45',null,null,null);
INSERT INTO `functions` VALUES (70, '其他',3,'2015-05-21 15:57:45',null,null,null),(71,'产品知识',3,'2015-05-21 15:57:45',null,null,null),(72,'市场策略',3,'2015-05-21 15:57:45',null,null,null),(73, '文献',3,'2015-05-21 15:57:45',null,null,null),(74, '医学知识',3,'2015-05-21 15:57:45',null,null,null),(75, '文献解读',3,'2015-05-21 15:57:45',null,null,null),(76, '合规',3,'2015-05-21 15:57:45',null,null,null);
/*!40000 ALTER TABLE `functions` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privileges`
--

LOCK TABLES `privileges` WRITE;
/*!40000 ALTER TABLE `privileges` DISABLE KEYS */;
INSERT INTO `privileges` VALUES (5,2,2,1,1,'2015-05-21 16:01:48',1,'2015-05-21 16:01:48'),(6,2,4,1,1,'2015-05-21 16:01:48',1,'2015-05-21 16:01:48'),(20,1,1,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57'),(21,1,2,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57'),(22,1,3,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57'),(23,1,4,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57'),(24,1,5,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57'),(25,1,6,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57'),(26,1,7,0,1,'2015-05-21 18:22:57',1,'2015-05-21 18:22:57');
/*!40000 ALTER TABLE `privileges` ENABLE KEYS */;
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
  PRIMARY KEY (`UserId`),
  UNIQUE KEY `EmployeeId` (`EmployeeId`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Phantom','phantom@intF1ocus.com',10,0,1,1,1,'2015-05-19 16:10:44',1,'2015-05-20 19:43:50','ph1'),(2,'phantom2','phantom@163com',10,0,0,1,1,'2015-05-19 18:38:41',1,'2015-05-20 19:31:45','ph2'),(3,'姓名1','Email1',8,-1,0,0,1,'2015-05-20 17:55:40',1,'2015-05-20 17:55:40','ph3'),(4,'姓名2','Email2',8,-1,0,0,1,'2015-05-20 17:55:40',1,'2015-05-20 17:55:40','ph4'),(10,'姓名3','Email3',12,1,0,0,1,'2015-05-20 18:01:35',1,'2015-05-20 18:01:35','ph5'),(11,'姓名4','Email4',12,1,0,0,1,'2015-05-20 18:01:35',1,'2015-05-20 18:01:35','ph6'),(12,'Phantom100','phantom100@163.com',6,1,0,1,1,'2015-05-20 18:45:25',1,'2015-05-20 18:48:48','ph7'),(13,'Phantom101','phantom101@163.com',7,1,0,1,1,'2015-05-20 18:47:50',1,'2015-05-20 18:47:50','ph8'),(14,'姓名5','Email5@abc.com',18,1,0,1,1,'2015-05-20 19:59:08',1,'2015-05-20 19:59:08','ph9'),(15,'姓名6','Email6@ccc.com',18,1,0,1,1,'2015-05-20 19:59:08',1,'2015-05-20 19:59:08','ph10'),(16,'Phantom3','phantom3@163.com',6,1,0,1,1,'2015-05-21 14:46:46',1,'2015-05-21 14:46:46','p300');
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

-- Dump completed on 2015-05-21 18:45:36

DROP TABLE IF EXISTS `problems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `problems` (
  `ProblemId` int(11) NOT NULL AUTO_INCREMENT,
  `ProblemType` int(11),
  `ProblemDesc` varchar(512) NOT NULL,
  `ProblemSelectA` varchar(512),
  `ProblemSelectB` varchar(512),
  `ProblemSelectC` varchar(512),
  `ProblemSelectD` varchar(512),
  `ProblemSelectE` varchar(512),
  `ProblemSelectF` varchar(512),
  `ProblemSelectG` varchar(512),
  `ProblemSelectH` varchar(512),
  `ProblemSelectI` varchar(512),
  `ProblemAnswer` varchar(512),
  `Status` int(11) NOT NULL DEFAULT '0',
  `ProblemCategory` varchar(50) NOT NULL,
  `ProblemLevel` int(11) NOT NULL DEFAULT '1',
  `ProblemMemo` varchar(1000),
  PRIMARY KEY (`ProblemId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exams` (
  `ExamId` int(11) NOT NULL AUTO_INCREMENT,
  `ExamName` varchar(100) UNIQUE KEY NOT NULL,
  `ExamType` int(11) NOT NULL DEFAULT '0',
  `ExamLocation` int(11) NULL,
  `ExamBegin` Datetime DEFAULT NULL,
  `ExamEnd` Datetime DEFAULT NULL,
  `ExamAnsType` int(11) NOT NULL DEFAULT '2',
  `ExamPassword` varchar(100),
  `Status` int(11) NOT NULL DEFAULT '0',
  `ExamDesc` varchar(255),
  `ExamContent` varchar(255) NOT NULL,
  `ExpireTime` Datetime NOT NULL,
  `CreatedUser` int(11) NOT NULL,
  `CreatedTime` Datetime NOT NULL,
  `EditUser` int(11) NOT NULL,
  `EditTime` Datetime NOT NULL,
  PRIMARY KEY (`ExamId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `examroll`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `examroll` (
  `ExamId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `IsSubmit` int(11) NOT NULL DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ExamId`, `UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ExamAnswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExamAnswer` (
  `ExamId` int(11) NOT NULL,
  `ProblemId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `ProblemType` int(11) NOT NULL,
  `Score` float NOT NULL,
  `Answer` VARCHAR(100) NOT NULL,
  `Result` int(11) NOT NULL,
  PRIMARY KEY (`ExamId`, `UserID`, `ProblemId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `ExamScore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExamScore` (
  `ExamId` int(11) NOT NULL,
  `UserId` int(11) NOT NULL,
  `Score` float NOT NULL,
  PRIMARY KEY (`ExamId`, `UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `ExamDetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ExamDetail` (
  `ExamId` int(11) NOT NULL,
  `ProblemId` int(11) NOT NULL,
  PRIMARY KEY (`ExamId`, `ProblemId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;



