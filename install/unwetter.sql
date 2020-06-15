SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `cap_data`;
CREATE TABLE IF NOT EXISTS `cap_data` (
  `cap_id` int(11) NOT NULL AUTO_INCREMENT,
  `guid` varchar(255) COLLATE utf8_bin NOT NULL,
  `iicode` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `sent` datetime NOT NULL,
  `effective` datetime NOT NULL,
  `onset` datetime NOT NULL,
  `expires` datetime NOT NULL,
  `sender_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `msgType_id` int(11) NOT NULL,
  `source_id` int(11) NOT NULL,
  `scope_id` int(11) NOT NULL,
  `severity_id` int(11) NOT NULL,
  `responseType_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `urgency_id` int(11) NOT NULL,
  `certainty_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `code_id` int(11) DEFAULT NULL,
  `license_id` int(11) NOT NULL,
  `areaColor_id` int(11) NOT NULL,
  `profileVersion_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cap_id`),
  UNIQUE KEY `cap_data-guid` (`guid`) USING BTREE,
  KEY `cap_data-sender_id` (`sender_id`),
  KEY `cap_data-code_id` (`code_id`),
  KEY `cap_data-event_id` (`event_id`),
  KEY `cap_data-certainty_id` (`certainty_id`),
  KEY `cap_data-profileVersion_id` (`profileVersion_id`) USING BTREE,
  KEY `cap_data-license_id` (`license_id`) USING BTREE,
  KEY `cap_data-scope_id` (`scope_id`) USING BTREE,
  KEY `cap_data-urgency_id` (`urgency_id`) USING BTREE,
  KEY `cap_data-severity_id` (`severity_id`) USING BTREE,
  KEY `cap_data-source_id` (`source_id`) USING BTREE,
  KEY `cap_data-msgType_id` (`msgType_id`) USING BTREE,
  KEY `cap_data-responseType_id` (`responseType_id`) USING BTREE,
  KEY `cap_data-category_id` (`category_id`) USING BTREE,
  KEY `cap_data-areaColor_id` (`areaColor_id`) USING BTREE,
  KEY `cap_data-status_scope_published_expires` (`status_id`,`scope_id`,`published`,`cap_id`,`expires`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3922 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='DWD Warnmeldungen';

DROP TABLE IF EXISTS `cap_data_areaColors`;
CREATE TABLE IF NOT EXISTS `cap_data_areaColors` (
  `areaColor_id` int(11) NOT NULL AUTO_INCREMENT,
  `areaColor_name` char(12) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`areaColor_id`),
  UNIQUE KEY `areaColor_name` (`areaColor_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_areas`;
CREATE TABLE IF NOT EXISTS `cap_data_areas` (
  `area_id` int(11) NOT NULL AUTO_INCREMENT,
  `cap_id` int(11) NOT NULL,
  `warncell` int(11) NOT NULL,
  `code` varchar(10) COLLATE utf8_bin NOT NULL,
  `county` varchar(5) COLLATE utf8_bin DEFAULT NULL,
  `altitude` double DEFAULT NULL,
  `ceiling` double DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`area_id`),
  UNIQUE KEY `cap_info_id` (`cap_id`,`warncell`,`altitude`,`ceiling`),
  KEY `code` (`code`),
  KEY `warncellid` (`warncell`),
  KEY `county` (`county`)
) ENGINE=InnoDB AUTO_INCREMENT=617094 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_groups`;
CREATE TABLE IF NOT EXISTS `cap_data_groups` (
  `cap_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `cap_id` (`cap_id`,`group_id`),
  KEY `cap_data_groups-group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_info`;
CREATE TABLE IF NOT EXISTS `cap_data_info` (
  `cap_info_id` int(11) NOT NULL AUTO_INCREMENT,
  `cap_id` int(11) NOT NULL,
  `language` varchar(30) COLLATE utf8_bin NOT NULL,
  `headline` longtext COLLATE utf8_bin NOT NULL,
  `description` longtext COLLATE utf8_bin NOT NULL,
  `instruction` longtext COLLATE utf8_bin,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastmodified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cap_info_id`),
  UNIQUE KEY `cap_id_language` (`cap_id`,`language`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7751 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_licenses`;
CREATE TABLE IF NOT EXISTS `cap_data_licenses` (
  `license_id` int(11) NOT NULL AUTO_INCREMENT,
  `license_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`license_id`),
  UNIQUE KEY `cap_data_license-name` (`license_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_parameters`;
CREATE TABLE IF NOT EXISTS `cap_data_parameters` (
  `param_id` int(11) NOT NULL AUTO_INCREMENT,
  `cap_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `value` varchar(30) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`param_id`),
  UNIQUE KEY `cap_id` (`cap_id`,`type_id`,`value`),
  KEY `cap_data_parameters-name_unit_value` (`cap_id`,`unit_id`,`type_id`) USING BTREE,
  KEY `cap_data_parameters-name` (`type_id`),
  KEY `cap_data_parameters-unit` (`unit_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4088 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_parameters_type`;
CREATE TABLE IF NOT EXISTS `cap_data_parameters_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `cap_data_parameters_type_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_parameters_units`;
CREATE TABLE IF NOT EXISTS `cap_data_parameters_units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `cap_data_parameter_unit_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_polygons`;
CREATE TABLE IF NOT EXISTS `cap_data_polygons` (
  `polygon_id` int(11) NOT NULL AUTO_INCREMENT,
  `cap_id` int(11) NOT NULL,
  `type` enum('include','exclude') COLLATE utf8_bin NOT NULL DEFAULT 'include',
  `polygon` longtext COLLATE utf8_bin NOT NULL,
  `altitude` float DEFAULT NULL,
  `ceiling` float DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`polygon_id`),
  KEY `cap_info_polygons-cap_id` (`cap_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7548 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_profileVersions`;
CREATE TABLE IF NOT EXISTS `cap_data_profileVersions` (
  `cap_data_profileVersion_id` int(11) NOT NULL AUTO_INCREMENT,
  `cap_data_profileVersion_name` varchar(10) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cap_data_profileVersion_id`),
  UNIQUE KEY `cap_data_profileVersion-name` (`cap_data_profileVersion_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_references`;
CREATE TABLE IF NOT EXISTS `cap_data_references` (
  `cap_id` int(11) NOT NULL,
  `reference` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `cap_id` (`cap_id`,`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_data_sender`;
CREATE TABLE IF NOT EXISTS `cap_data_sender` (
  `sender_id` int(11) NOT NULL AUTO_INCREMENT,
  `senderName` varchar(255) COLLATE utf8_bin NOT NULL,
  `web` varchar(255) COLLATE utf8_bin NOT NULL,
  `contact` varchar(255) COLLATE utf8_bin NOT NULL,
  `email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'CAP@dwd.de',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sender_id`),
  UNIQUE KEY `senderName` (`senderName`,`web`,`contact`,`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

DROP TABLE IF EXISTS `cap_event_group_ii`;
CREATE TABLE IF NOT EXISTS `cap_event_group_ii` (
  `cap_group_type_id` int(11) NOT NULL,
  `iicode` int(11) NOT NULL,
  `optional` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastmodified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `cap_event_group_id` (`cap_group_type_id`,`iicode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_event_group_ii` (`cap_group_type_id`, `iicode`, `optional`) VALUES
(1, 31, 0),
(1, 33, 0),
(1, 34, 0),
(1, 36, 0),
(1, 38, 0),
(1, 40, 0),
(1, 41, 0),
(1, 42, 0),
(1, 44, 0),
(1, 45, 0),
(1, 46, 0),
(1, 48, 0),
(1, 49, 0),
(1, 57, 1),
(1, 58, 1),
(1, 90, 0),
(1, 91, 0),
(1, 92, 0),
(1, 93, 0),
(1, 95, 0),
(1, 96, 0),
(2, 11, 0),
(2, 12, 0),
(2, 13, 0),
(2, 14, 0),
(2, 15, 0),
(2, 16, 0),
(2, 31, 1),
(2, 33, 0),
(2, 34, 1),
(2, 36, 0),
(2, 38, 0),
(2, 40, 0),
(2, 41, 0),
(2, 42, 1),
(2, 44, 0),
(2, 45, 0),
(2, 46, 1),
(2, 48, 0),
(2, 49, 0),
(2, 51, 0),
(2, 52, 0),
(2, 53, 0),
(2, 54, 0),
(2, 55, 0),
(2, 56, 0),
(2, 57, 0),
(2, 58, 0),
(2, 74, 0),
(2, 75, 0),
(2, 76, 0),
(2, 77, 0),
(2, 78, 0),
(2, 90, 1),
(2, 91, 1),
(2, 92, 1),
(2, 93, 1),
(2, 95, 1),
(2, 96, 0),
(3, 41, 1),
(3, 45, 1),
(3, 49, 1),
(3, 96, 1),
(4, 34, 0),
(4, 36, 0),
(4, 38, 0),
(4, 40, 1),
(4, 41, 1),
(4, 42, 0),
(4, 44, 0),
(4, 45, 0),
(4, 46, 0),
(4, 48, 0),
(4, 49, 0),
(4, 57, 1),
(4, 58, 1),
(4, 61, 0),
(4, 62, 0),
(4, 63, 0),
(4, 64, 0),
(4, 65, 0),
(4, 66, 0),
(4, 88, 0),
(4, 89, 0),
(4, 91, 1),
(4, 92, 1),
(4, 93, 1),
(4, 95, 0),
(4, 96, 0),
(5, 33, 1),
(5, 34, 1),
(5, 38, 0),
(5, 40, 1),
(5, 41, 1),
(5, 42, 1),
(5, 44, 1),
(5, 45, 1),
(5, 46, 0),
(5, 48, 0),
(5, 49, 0),
(5, 91, 1),
(5, 92, 1),
(5, 93, 1),
(5, 95, 0),
(5, 96, 0),
(6, 70, 0),
(6, 71, 0),
(6, 72, 0),
(6, 73, 0),
(6, 76, 0),
(6, 77, 0),
(6, 78, 0),
(7, 74, 0),
(7, 75, 0),
(7, 76, 0),
(7, 77, 0),
(7, 78, 0),
(8, 59, 0),
(9, 22, 0),
(9, 81, 0),
(9, 82, 0),
(9, 83, 0),
(10, 24, 0),
(10, 83, 0),
(10, 84, 0),
(10, 85, 0),
(10, 86, 0),
(10, 87, 0),
(11, 88, 0),
(11, 89, 0),
(12, 79, 0),
(13, 246, 0),
(14, 247, 0),
(15, 98, 0),
(15, 99, 0);

DROP TABLE IF EXISTS `cap_types_category`;
CREATE TABLE IF NOT EXISTS `cap_types_category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_category` (`category_id`, `category_name`) VALUES
(1, 'Met'),
(2, 'Health');

DROP TABLE IF EXISTS `cap_types_certainty`;
CREATE TABLE IF NOT EXISTS `cap_types_certainty` (
  `certainty_id` int(11) NOT NULL AUTO_INCREMENT,
  `certainty_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `certainty_description` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`certainty_id`),
  UNIQUE KEY `certainty_name` (`certainty_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_certainty` (`certainty_id`, `certainty_name`, `certainty_description`) VALUES
(1, 'Observe', 'Beobachtung'),
(2, 'Likely', 'Vorhersage, Auftreten wahrscheinlich (p > ~50%)');

DROP TABLE IF EXISTS `cap_types_code`;
CREATE TABLE IF NOT EXISTS `cap_types_code` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `code_name` varchar(20) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code_id`),
  UNIQUE KEY `code_name` (`code_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_code` (`code_id`, `code_name`) VALUES
(1, 'SILENT_UPDATE'),
(2, 'PARTIAL_CLEAR');

DROP TABLE IF EXISTS `cap_types_event`;
CREATE TABLE IF NOT EXISTS `cap_types_event` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `event_urgency` int(11) NOT NULL,
  `event_iicode` int(11) NOT NULL,
  `event_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `urgency-iicode` (`event_urgency`,`event_iicode`) USING BTREE,
  KEY `ii_code` (`event_iicode`) USING BTREE,
  KEY `id_urgency_iicode` (`event_id`,`event_urgency`,`event_iicode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_event` (`event_id`, `event_urgency`, `event_iicode`, `event_name`) VALUES
(1, 1, 11, 'BÖEN'),
(2, 1, 12, 'WIND'),
(3, 1, 13, 'STURM'),
(4, 1, 14, 'Starkwind'),
(5, 1, 15, 'Sturm'),
(6, 1, 16, 'Schwerer Sturm'),
(7, 1, 246, 'UV-INDEX'),
(8, 1, 247, 'HITZE'),
(9, 1, 22, 'FROST'),
(10, 1, 24, 'GLÄTTE'),
(11, 1, 31, 'GEWITTER'),
(12, 1, 33, 'STARKES GEWITTER'),
(13, 1, 34, 'STARKES GEWITTER'),
(14, 1, 36, 'STARKES GEWITTER'),
(15, 1, 38, 'STARKES GEWITTER'),
(16, 1, 40, 'SCHWERES GEWITTER mit ORKANBÖEN'),
(17, 1, 41, 'SCHWERES GEWITTER mit EXTREMEN ORKANBÖEN'),
(18, 1, 42, 'SCHWERES GEWITTERmit HEFTIGEM STARKREGEN'),
(19, 1, 44, 'SCHWERES GEWITTER mit ORKANBÖEN und HEFTIGEM STARKREGEN'),
(20, 1, 45, 'SCHWERES GEWITTER mit EXTREMEN ORKANBÖEN und HEFTIGEM STARKREGEN'),
(21, 1, 46, 'SCHWERES GEWITTER mit HEFTIGEM STARKREGEN und HAGEL'),
(22, 1, 48, 'SCHWERES GEWITTER mit ORKANBÖEN, HEFTIGEM STARKREGEN und HAGEL'),
(23, 1, 49, 'SCHWERES GEWITTER mit EXTREMEN ORKANBÖEN, HEFTIGEM STARKREGEN und HAGEL'),
(24, 1, 51, 'WINDBÖEN'),
(25, 1, 52, 'STURMBÖEN'),
(26, 1, 53, 'SCHWERE STURMBÖEN'),
(27, 1, 54, 'ORKANARTIGE BÖEN'),
(28, 1, 55, 'ORKANBÖEN'),
(29, 1, 56, 'EXTREME ORKANBÖEN'),
(30, 1, 57, 'STARKWIND'),
(31, 1, 58, 'STURM'),
(32, 1, 59, 'NEBEL'),
(33, 1, 61, 'STARKREGEN'),
(34, 1, 62, 'HEFTIGER STARKREGEN'),
(35, 1, 63, 'DAUERREGEN'),
(36, 1, 64, 'ERGIEBIGER DAUERREGEN'),
(37, 1, 65, 'EXTREM ERGIEBIGER DAUERREGEN'),
(38, 1, 66, 'EXTREM HEFTIGER STARKREGEN'),
(39, 1, 70, 'LEICHTER SCHNEEFALL'),
(40, 1, 71, 'SCHNEEFALL'),
(41, 1, 72, 'STARKER SCHNEEFALL'),
(42, 1, 73, 'EXTREM STARKER SCHNEEFALL'),
(43, 1, 74, 'SCHNEEVERWEHUNG'),
(44, 1, 75, 'STARKE SCHNEEVERWEHUNG'),
(45, 1, 76, 'SCHNEEFALL und SCHNEEVERWEHUNG'),
(46, 1, 77, 'STARKER SCHNEEFALL undSCHNEEVERWEHUNG'),
(47, 1, 78, 'EXTREM STARKER SCHNEEFALL undSCHNEEVERWEHUNG'),
(48, 1, 79, 'LEITERSEILSCHWINGUNGEN'),
(49, 1, 81, 'FROST'),
(50, 1, 82, 'STRENGER FROST'),
(51, 1, 84, 'GLÄTTE'),
(52, 1, 85, 'GLATTEIS'),
(53, 1, 87, 'GLATTEIS'),
(54, 1, 88, 'TAUWETTER'),
(55, 1, 89, 'STARKES TAUWETTER'),
(56, 1, 90, 'GEWITTER'),
(57, 1, 91, 'STARKES GEWITTER'),
(58, 1, 92, 'SCHWERES GEWITTER'),
(59, 1, 93, 'EXTREMES GEWITTER'),
(60, 1, 95, 'SCHWERES GEWITTER mit EXTREM HEFTIGEM STARKREGEN und HAGEL'),
(61, 1, 96, 'EXTREMES GEWITTER mit ORKANBÖEN, EXTREM HEFTIGEM STARKREGENund HAGEL'),
(62, 1, 98, 'TEST-WARNUNG'),
(63, 1, 99, 'TEST-UNWETTERWARNUNG'),
(64, 2, 40, 'VORABINFORMATION SCHWERES GEWITTER'),
(65, 2, 55, 'VORABINFORMATION ORKANBÖEN'),
(66, 2, 65, 'VORABINFORMATION HEFTIGER / ERGIEBIGER REGEN'),
(67, 2, 75, 'VORABINFORMATION STARKER SCHNEEFALL / SCHNEEVERWEHUNG'),
(68, 2, 85, 'VORABINFORMATION GLATTEIS'),
(69, 2, 89, 'VORABINFORMATION STARKES TAUWETTER'),
(70, 2, 99, 'TEST-VORABINFORMATION UNWETTER');

DROP TABLE IF EXISTS `cap_types_group`;
CREATE TABLE IF NOT EXISTS `cap_types_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `group_description` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastmodified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_group` (`group_id`, `group_name`, `group_description`) VALUES
(1, 'THUNDERSTORM', 'Gewitter'),
(2, 'WIND', 'Wind'),
(3, 'TORNADO', 'Tornado'),
(4, 'RAIN', 'Regen'),
(5, 'HAIL', 'Hagel'),
(6, 'SNOWFALL', 'Schneefall'),
(7, 'SNOWDRIFT', 'Schneeverwehung'),
(8, 'FOG', 'Nebel'),
(9, 'FROST', 'Frost'),
(10, 'GLAZE', 'Schneeschmelze'),
(11, 'THAW', 'Tauwetter'),
(12, 'POWERLINEVIBRATION', 'Stromleitungen'),
(13, 'UV', 'UV-Strahlung'),
(14, 'HEAT', 'Hitze'),
(15, 'TEST', 'Testmeldung');

DROP TABLE IF EXISTS `cap_types_msgType`;
CREATE TABLE IF NOT EXISTS `cap_types_msgType` (
  `msgType_id` int(11) NOT NULL AUTO_INCREMENT,
  `msgType_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`msgType_id`),
  UNIQUE KEY `msgType_name` (`msgType_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_msgType` (`msgType_id`, `msgType_name`) VALUES
(1, 'Alert'),
(2, 'Update'),
(3, 'Cancel');

DROP TABLE IF EXISTS `cap_types_responseType`;
CREATE TABLE IF NOT EXISTS `cap_types_responseType` (
  `responseType_id` int(11) NOT NULL AUTO_INCREMENT,
  `responseType_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`responseType_id`),
  UNIQUE KEY `responseType_name` (`responseType_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_responseType` (`responseType_id`, `responseType_name`) VALUES
(1, 'Prepare'),
(2, 'AllClear'),
(3, 'None'),
(4, 'Monitor');

DROP TABLE IF EXISTS `cap_types_scope`;
CREATE TABLE IF NOT EXISTS `cap_types_scope` (
  `scope_id` int(11) NOT NULL AUTO_INCREMENT,
  `scope_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`scope_id`),
  UNIQUE KEY `scope_name` (`scope_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_scope` (`scope_id`, `scope_name`) VALUES
(1, 'Public');

DROP TABLE IF EXISTS `cap_types_severity`;
CREATE TABLE IF NOT EXISTS `cap_types_severity` (
  `severity_id` int(11) NOT NULL AUTO_INCREMENT,
  `severity_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`severity_id`),
  UNIQUE KEY `severity_name` (`severity_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_severity` (`severity_id`, `severity_name`) VALUES
(1, 'Minor'),
(2, 'Moderate'),
(3, 'Severe'),
(4, 'Extreme');

DROP TABLE IF EXISTS `cap_types_source`;
CREATE TABLE IF NOT EXISTS `cap_types_source` (
  `source_id` int(11) NOT NULL AUTO_INCREMENT,
  `source_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`source_id`),
  UNIQUE KEY `source_name` (`source_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_source` (`source_id`, `source_name`) VALUES
(1, 'PVW');

DROP TABLE IF EXISTS `cap_types_status`;
CREATE TABLE IF NOT EXISTS `cap_types_status` (
  `status_id` int(11) NOT NULL AUTO_INCREMENT,
  `status_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`status_id`),
  UNIQUE KEY `status_name` (`status_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_status` (`status_id`, `status_name`) VALUES
(1, 'Actual'),
(2, 'Test');

DROP TABLE IF EXISTS `cap_types_urgency`;
CREATE TABLE IF NOT EXISTS `cap_types_urgency` (
  `urgency_id` int(11) NOT NULL AUTO_INCREMENT,
  `urgency_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `urgency_description` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`urgency_id`),
  UNIQUE KEY `urgency_name` (`urgency_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_types_urgency` (`urgency_id`, `urgency_name`, `urgency_description`) VALUES
(1, 'Immediate', 'Warnung'),
(2, 'Future', 'Vorabinformation');

DROP TABLE IF EXISTS `cap_warncell_types`;
CREATE TABLE IF NOT EXISTS `cap_warncell_types` (
  `warncell_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `digit` int(11) NOT NULL,
  `keyword` varchar(255) COLLATE utf8_bin NOT NULL,
  `description` varchar(255) COLLATE utf8_bin NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`warncell_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `cap_warncell_types` (`warncell_type_id`, `digit`, `keyword`, `description`) VALUES
(1, 1, 'LAND', 'Landkreise'),
(2, 2, 'LAKE', 'Seen'),
(3, 2, 'LAKE-SUM', 'Seenzusammenfassungen'),
(4, 4, 'SEA', 'Seegebiete '),
(5, 5, 'COAST', 'Küstengebiete '),
(6, 6, 'OBJECT', 'Kundenspezifische Objekte'),
(7, 7, 'QUARTER', 'Stadt-Unterteilungen'),
(8, 8, 'COMMUNE', 'Gemeinden'),
(9, 9, 'STATE', 'Bundesländer'),
(10, 9, 'AGGREGATION', 'Bundesl.-Zusammenfassungen'),
(11, 9, 'DISTRICTAREA', 'Gemeinde-Zusammenfassungen'),
(12, 9, 'DISTRICTAREA', 'Landkreis-Zusammenfassungen'),
(13, 9, 'REGION', 'Landkreis-Unterteilungen'),
(14, 9, 'FIRECONTROLDISTRICTS', 'Brandschutzbereiche '),
(15, 9, 'MEDIADISTRICT', 'WDR-Studios');


ALTER TABLE `cap_data`
  ADD CONSTRAINT `cap_data-areaColor_id` FOREIGN KEY (`areaColor_id`) REFERENCES `cap_data_areaColors` (`areaColor_id`),
  ADD CONSTRAINT `cap_data-category_id` FOREIGN KEY (`category_id`) REFERENCES `cap_types_category` (`category_id`),
  ADD CONSTRAINT `cap_data-certainty_id` FOREIGN KEY (`certainty_id`) REFERENCES `cap_types_certainty` (`certainty_id`),
  ADD CONSTRAINT `cap_data-code_id` FOREIGN KEY (`code_id`) REFERENCES `cap_types_code` (`code_id`),
  ADD CONSTRAINT `cap_data-event_id` FOREIGN KEY (`event_id`) REFERENCES `cap_types_event` (`event_id`),
  ADD CONSTRAINT `cap_data-license_id` FOREIGN KEY (`license_id`) REFERENCES `cap_data_licenses` (`license_id`),
  ADD CONSTRAINT `cap_data-msgType_id` FOREIGN KEY (`msgType_id`) REFERENCES `cap_types_msgType` (`msgType_id`),
  ADD CONSTRAINT `cap_data-profileVersion_id` FOREIGN KEY (`profileVersion_id`) REFERENCES `cap_data_profileVersions` (`cap_data_profileVersion_id`),
  ADD CONSTRAINT `cap_data-responseType_id` FOREIGN KEY (`responseType_id`) REFERENCES `cap_types_responseType` (`responseType_id`),
  ADD CONSTRAINT `cap_data-scope_id` FOREIGN KEY (`scope_id`) REFERENCES `cap_types_scope` (`scope_id`),
  ADD CONSTRAINT `cap_data-sender_id` FOREIGN KEY (`sender_id`) REFERENCES `cap_data_sender` (`sender_id`),
  ADD CONSTRAINT `cap_data-severity_id` FOREIGN KEY (`severity_id`) REFERENCES `cap_types_severity` (`severity_id`),
  ADD CONSTRAINT `cap_data-source_id` FOREIGN KEY (`source_id`) REFERENCES `cap_types_source` (`source_id`),
  ADD CONSTRAINT `cap_data-status_id` FOREIGN KEY (`status_id`) REFERENCES `cap_types_status` (`status_id`),
  ADD CONSTRAINT `cap_data-urgency_id` FOREIGN KEY (`urgency_id`) REFERENCES `cap_types_urgency` (`urgency_id`);

ALTER TABLE `cap_data_areas`
  ADD CONSTRAINT `cap_data_areas-cap_id` FOREIGN KEY (`cap_id`) REFERENCES `cap_data` (`cap_id`);

ALTER TABLE `cap_data_groups`
  ADD CONSTRAINT `cap_data_groups-cap_id` FOREIGN KEY (`cap_id`) REFERENCES `cap_data` (`cap_id`),
  ADD CONSTRAINT `cap_data_groups-group_id` FOREIGN KEY (`group_id`) REFERENCES `cap_types_group` (`group_id`);

ALTER TABLE `cap_data_info`
  ADD CONSTRAINT `cap_data_info-cap_id` FOREIGN KEY (`cap_id`) REFERENCES `cap_data` (`cap_id`);

ALTER TABLE `cap_data_parameters`
  ADD CONSTRAINT `cap_data_parameters-cap_id` FOREIGN KEY (`cap_id`) REFERENCES `cap_data` (`cap_id`),
  ADD CONSTRAINT `cap_data_parameters-name` FOREIGN KEY (`type_id`) REFERENCES `cap_data_parameters_type` (`type_id`),
  ADD CONSTRAINT `cap_data_parameters-unit` FOREIGN KEY (`unit_id`) REFERENCES `cap_data_parameters_units` (`unit_id`);

ALTER TABLE `cap_data_polygons`
  ADD CONSTRAINT `cap_info_polygons-cap_id` FOREIGN KEY (`cap_id`) REFERENCES `cap_data` (`cap_id`);

ALTER TABLE `cap_data_references`
  ADD CONSTRAINT `cap_data_references_cap_id` FOREIGN KEY (`cap_id`) REFERENCES `cap_data` (`cap_id`);

ALTER TABLE `cap_types_event`
  ADD CONSTRAINT `cap_types_event_cap_urgency_type_id` FOREIGN KEY (`event_urgency`) REFERENCES `cap_types_urgency` (`urgency_id`);
COMMIT;
