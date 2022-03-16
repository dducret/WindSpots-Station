CREATE DATABASE IF NOT EXISTS windspots CHARACTER SET utf8 COLLATE utf8_general_ci;
USE windspots;
CREATE TABLE IF NOT EXISTS `forecast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `station_name` varchar(6) NOT NULL,
  `reference_time` datetime NOT NULL,
  `speed` varchar(11) DEFAULT NULL,
  `direction` varchar(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `station_name` (`station_name`),
  KEY `reference_time` (`station_name`,`reference_time`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `sensor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `station_name` varchar(6) NOT NULL,
  `sensor_name` varchar(10) DEFAULT NULL,
  `channel` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `station_name` (`station_name`),
  KEY `sensor_name` (`station_name`,`sensor_name`,`channel`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `sensor_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id` int(11) NOT NULL,
  `sensor_time` datetime NOT NULL,
  `battery` varchar(5) DEFAULT NULL,
  `temperature` float DEFAULT NULL,
  `humidity` smallint(5) DEFAULT NULL,
  `barometer` smallint(5) DEFAULT NULL,
  `direction` smallint(5) DEFAULT NULL,
  `speed` float DEFAULT NULL,
  `average` float DEFAULT NULL,
  `gust` float DEFAULT NULL,
  `uv` varchar(2) DEFAULT NULL,
  `rain_rate` smallint(5) DEFAULT NULL,
  `rain_total` smallint(5) DEFAULT NULL,
  `ten` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sensor_id` (`sensor_id`),
  KEY `sendor_time` (`sensor_id`,`sensor_time`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS `station` (
  `station_name` varchar(6) NOT NULL DEFAULT '',
  `display_name` varchar(255) DEFAULT NULL,
  `short_name` varchar(80) DEFAULT NULL,
  `ms_name` varchar(8) DEFAULT NULL,
  `information` varchar(255) DEFAULT NULL,
  `altitude` smallint(5) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `spot_type` tinyint(4) DEFAULT NULL DEFAULT '0',
  `wind_id` smallint(5) NOT NULL DEFAULT '0',
  `barometer_id` smallint(5) NOT NULL DEFAULT '0',
  `temperature_id` smallint(5) NOT NULL DEFAULT '0',
  `water_id` smallint(5) NOT NULL DEFAULT '0',
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `maintenance` tinyint(1) NOT NULL DEFAULT '0',
  `reason` varchar(255) DEFAULT '',
  `GMT` tinyint(2) NOT NULL DEFAULT '0',
  `data_time` datetime,
  `image_time` datetime,
  PRIMARY KEY (`station_name`),
  KEY `ms_name` (`ms_name`)
) CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'windspots'@localhost IDENTIFIED BY 'WS2022org!';
GRANT ALL privileges ON `windspots`.* TO 'windspotsr'@localhost;
FLUSH PRIVILEGES;