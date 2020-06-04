SET AUTOCOMMIT = 0;

START TRANSACTION;

SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `n_seolink_group` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL,
  `IBLOCK_ID` int(11) NOT NULL,
  `ZONE` enum('E','S') DEFAULT 'E',
  `FIELD` text,
  `PROPERTY` text,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='группы поиска';

CREATE TABLE IF NOT EXISTS `n_seolink_request` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `URL` int(11) NOT NULL,
  `TEXT` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `URL` (`URL`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='склонения запросов';

CREATE TABLE IF NOT EXISTS `n_seolink_stats` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `URL` int(11) DEFAULT NULL,
  `KEY` int(11) DEFAULT NULL,
  `GROUP` int(11) DEFAULT NULL,
  `COUNT` int(11) DEFAULT NULL,
  `TYPE` enum('URL','KEY','KU') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='статистика ключевых слов';

CREATE TABLE IF NOT EXISTS `n_seolink_url` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) DEFAULT NULL,
  `URL` varchar(255) DEFAULT NULL,
  `GLOBAL_URL` varchar(255) DEFAULT NULL,
  `LOCAL_URL` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='адреса и группы запросов';

CREATE TABLE IF NOT EXISTS `n_seolink_zamena` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `KEY` int(11) NOT NULL,
  `ZONE` enum('E','S') COLLATE utf8_unicode_ci NOT NULL,
  `OBJECT` int(11) NOT NULL,
  `FIELD` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Кандидаты на замену';

COMMIT;