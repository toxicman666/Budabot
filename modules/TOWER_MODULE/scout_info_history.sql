-- MySQL dump 10.11
--
-- Host: localhost    Database: warbot
-- ------------------------------------------------------
-- Server version	5.0.45

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
-- Table structure for table `scout_info_history`
--

DROP TABLE IF EXISTS `scout_info_history`;
CREATE TABLE `scout_info_history` (`id` int(11) NOT NULL auto_increment, `playfield_id` int(11) NOT NULL, `site_number` smallint(6) NOT NULL, `scouted_on` datetime NOT NULL, `scouted_by` varchar(20) NOT NULL, `ct_ql` smallint(6) NOT NULL, `guild_name` varchar(50) NOT NULL, `faction` char(4) NOT NULL default '', `close_time` int(11) NOT NULL, `force` tinyint(1) default '0', PRIMARY KEY  (`id`), KEY `playfield_id` (`playfield_id`,`site_number`)) ENGINE=MyISAM DEFAULT CHARSET=latin1;

