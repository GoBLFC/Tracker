CREATE TABLE `users` (
  `id` int NOT NULL, -- User ID from ConCat, no AUTO_INCREMENT here
  `username` varchar(64) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `badge_name` varchar(64),
  `role` tinyint NOT NULL DEFAULT 0,
  `tg_setup_code` varchar(32) NOT NULL,
  `tg_uid` int,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`tg_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL,
  `hidden` boolean NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tracker` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime,
  `dept` int NOT NULL,
  `notes` mediumtext,
  `added_by` int NOT NULL,
  `auto` boolean NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uid`) REFERENCES `users`(`id`),
  FOREIGN KEY (`dept`) REFERENCES `departments`(`id`),
  FOREIGN KEY (`added_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `kiosks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `rewards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `desc` mediumtext NOT NULL,
  `hours` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `claims` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `claim` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uid`) REFERENCES `users`(`id`),
  FOREIGN KEY (`claim`) REFERENCES `rewards`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `bonuses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start` datetime NOT NULL,
  `stop` datetime NOT NULL,
  `dept` varchar(256) NOT NULL,
  `modifier` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `reward` int,
  `message` mediumtext NOT NULL,
  `has_read` boolean NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uid`) REFERENCES `users`(`id`),
  FOREIGN KEY (`reward`) REFERENCES `rewards`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `telegram` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(4) NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `uid` int NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uid`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`uid`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `settings` (
  `profile` int NOT NULL AUTO_INCREMENT,
  `site_status` boolean NOT NULL,
  `dev_mode` boolean NOT NULL,
  PRIMARY KEY (`profile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
