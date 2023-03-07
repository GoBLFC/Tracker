CREATE TABLE `claims` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `claim` varchar(64) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kiosks` (
  `session` varchar(256) NOT NULL,
  `authorized` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `session` (`session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `type` varchar(32) NOT NULL,
  `reward` int NOT NULL DEFAULT '0',
  `message` mediumtext NOT NULL,
  `hasread` tinyint(1) NOT NULL DEFAULT '0',
  `ack` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `rewards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `desc` mediumtext NOT NULL,
  `hours` int NOT NULL,
  `type` varchar(32) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `settings` (
  `profile` int NOT NULL AUTO_INCREMENT,
  `check_start` datetime NOT NULL,
  `check_end` datetime NOT NULL,
  `max_unique` int NOT NULL,
  `site_status` int NOT NULL,
  `devmode` int NOT NULL,
  UNIQUE KEY `id` (`profile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `time_mod` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `stop` datetime NOT NULL,
  `dept` varchar(256) NOT NULL,
  `modifier` double NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tracker` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `checkin` datetime NOT NULL,
  `checkout` datetime DEFAULT NULL,
  `dept` int NOT NULL,
  `notes` mediumtext NOT NULL,
  `addedby` int NOT NULL DEFAULT '0',
  `auto` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `nickname` varchar(64) NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `manager` tinyint(1) NOT NULL DEFAULT '0',
  `lead` tinyint NOT NULL DEFAULT '0',
  `banned` tinyint NOT NULL DEFAULT '0',
  `last_session` varchar(1024) DEFAULT NULL,
  `last_ip` varchar(128) DEFAULT NULL,
  `last_login` datetime DEFAULT CURRENT_TIMESTAMP,
  `registered` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reg_ua` mediumtext,
  `tg_uid` varchar(64) DEFAULT NULL,
  `tg_chatid` int DEFAULT NULL,
  `tg_quickcode` int DEFAULT NULL,
  `tg_quickcodetime` datetime DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
