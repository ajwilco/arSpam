-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2015 at 04:55 AM
-- Server version: 5.6.21
-- PHP Version: 5.6.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `arspam`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE IF NOT EXISTS `classes` (
`id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
`id` int(11) NOT NULL,
  `name` text NOT NULL,
  `ISO` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `receivingip`
--

CREATE TABLE IF NOT EXISTS `receivingip` (
`id` int(11) NOT NULL,
  `ip` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `securetide`
--

CREATE TABLE IF NOT EXISTS `securetide` (
`id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `class` int(3) NOT NULL,
  `message` char(36) NOT NULL,
  `sendingIP` varchar(15) NOT NULL,
  `receivingIP` int(3) NOT NULL,
  `country` int(3) NOT NULL,
  `tests` set('419SCAM','8BIT','ADULTPHRASE','ADULTWORDS','ANGELFIRELINK','ARABIC-CHR','ARGDBL','ARMALWARE','ASIAN-CHR','ASIAN-SUB','BADCHARSET','BADHEADERS','BASE64NULL','BASE64TEXT','BOUNCEBLOCK','BOUNCELOOP','BOUNCETRACKER','BULKMAILER','COMMENTS','CYRILLIC-CHR','FILECHECK','FINGERPRINT','FROMISP','GARBAGEWORDS','GOOGLEGRPSLINK','GREEK-CHR','HEBREW-CHR','HELOBOGUS','HTMLSCRIPT','INVESTMENT','IPINURL','ISRUSSIAN','JAVAOBFUSCATE','JAVAWRITE','OPTOUT','PHISHAR','PHISHING','PORTINURL','PORTUGUESE-CHR','REDIRECTHOLE','RETURNPATH','REVDNS','ROUTING','SHORTURL','SIG-BLACK','SIG-CAUTION','SIGNATURE','SPACEDSUBJECT','SPAMBL','SPAMDOMAINS','SPAMPHRASE','SPANISH-CHR','TRUSTEDSOURCE','WEBBUG','WEIGHT10','WEIGHT15','WEIGHT20','WEIGHT30','WORDPRESSLINK','YAHOOGRPSLINK') NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=50001 DEFAULT CHARSET=utf8;

--
-- RELATIONS FOR TABLE `securetide`:
--   `class`
--       `classes` -> `id`
--   `country`
--       `countries` -> `id`
--   `receivingIP`
--       `receivingip` -> `id`
--

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE IF NOT EXISTS `tests` (
`id` int(11) NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `test_fails`
--

CREATE TABLE IF NOT EXISTS `test_fails` (
`id` int(11) NOT NULL,
  `test` int(11) NOT NULL,
  `record` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=150955 DEFAULT CHARSET=utf8 COMMENT='relational for tests and securetide';

--
-- RELATIONS FOR TABLE `test_fails`:
--   `test`
--       `tests` -> `id`
--   `record`
--       `securetide` -> `id`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_data`
--
CREATE TABLE IF NOT EXISTS `v_data` (
`emailID` int(11)
,`date` datetime
,`sendingIP` varchar(15)
,`receivingIP` text
,`countryID` int(11)
,`country` text
,`classID` int(11)
,`className` text
,`tests` set('419SCAM','8BIT','ADULTPHRASE','ADULTWORDS','ANGELFIRELINK','ARABIC-CHR','ARGDBL','ARMALWARE','ASIAN-CHR','ASIAN-SUB','BADCHARSET','BADHEADERS','BASE64NULL','BASE64TEXT','BOUNCEBLOCK','BOUNCELOOP','BOUNCETRACKER','BULKMAILER','COMMENTS','CYRILLIC-CHR','FILECHECK','FINGERPRINT','FROMISP','GARBAGEWORDS','GOOGLEGRPSLINK','GREEK-CHR','HEBREW-CHR','HELOBOGUS','HTMLSCRIPT','INVESTMENT','IPINURL','ISRUSSIAN','JAVAOBFUSCATE','JAVAWRITE','OPTOUT','PHISHAR','PHISHING','PORTINURL','PORTUGUESE-CHR','REDIRECTHOLE','RETURNPATH','REVDNS','ROUTING','SHORTURL','SIG-BLACK','SIG-CAUTION','SIGNATURE','SPACEDSUBJECT','SPAMBL','SPAMDOMAINS','SPAMPHRASE','SPANISH-CHR','TRUSTEDSOURCE','WEBBUG','WEIGHT10','WEIGHT15','WEIGHT20','WEIGHT30','WORDPRESSLINK','YAHOOGRPSLINK')
);
-- --------------------------------------------------------

--
-- Structure for view `v_data`
--
DROP TABLE IF EXISTS `v_data`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_data` AS select `s`.`id` AS `emailID`,`s`.`date` AS `date`,`s`.`sendingIP` AS `sendingIP`,`r`.`ip` AS `receivingIP`,`co`.`id` AS `countryID`,`co`.`name` AS `country`,`cl`.`id` AS `classID`,`cl`.`name` AS `className`,`s`.`tests` AS `tests` from (((`securetide` `s` join `classes` `cl` on((`cl`.`id` = `s`.`class`))) join `countries` `co` on((`co`.`id` = `s`.`country`))) join `receivingip` `r` on((`r`.`id` = `s`.`receivingIP`)));

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
 ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `receivingip`
--
ALTER TABLE `receivingip`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `securetide`
--
ALTER TABLE `securetide`
 ADD UNIQUE KEY `id` (`id`), ADD KEY `class` (`class`), ADD KEY `country` (`country`), ADD KEY `receivingIP` (`receivingIP`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
 ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `test_fails`
--
ALTER TABLE `test_fails`
 ADD UNIQUE KEY `id` (`id`), ADD KEY `test` (`test`), ADD KEY `record` (`record`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=130;
--
-- AUTO_INCREMENT for table `receivingip`
--
ALTER TABLE `receivingip`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `securetide`
--
ALTER TABLE `securetide`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=50001;
--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=106;
--
-- AUTO_INCREMENT for table `test_fails`
--
ALTER TABLE `test_fails`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=150955;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `securetide`
--
ALTER TABLE `securetide`
ADD CONSTRAINT `securetide_ibfk_1` FOREIGN KEY (`class`) REFERENCES `classes` (`id`) ON UPDATE NO ACTION,
ADD CONSTRAINT `securetide_ibfk_2` FOREIGN KEY (`country`) REFERENCES `countries` (`id`) ON UPDATE NO ACTION,
ADD CONSTRAINT `securetide_ibfk_3` FOREIGN KEY (`receivingIP`) REFERENCES `receivingip` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `test_fails`
--
ALTER TABLE `test_fails`
ADD CONSTRAINT `test_fails_ibfk_1` FOREIGN KEY (`test`) REFERENCES `tests` (`id`) ON UPDATE NO ACTION,
ADD CONSTRAINT `test_fails_ibfk_2` FOREIGN KEY (`record`) REFERENCES `securetide` (`id`) ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
