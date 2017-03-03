CREATE TABLE `acl_acl` (
  `aro_id` INT NOT NULL ,
  `aco_id` INT NOT NULL ,
  `action` VARCHAR( 255 ) NOT NULL ,
  `permission` ENUM( 'allow', 'deny' ) NOT NULL ,
  PRIMARY KEY ( `aro_id` , `aco_id` , `action` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
 
CREATE TABLE `acl_aro` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `parent_id` INT NULL DEFAULT NULL,
  `alias` VARCHAR( 255 ) NOT NULL ,
  `lineage` VARCHAR( 255) NOT NULL DEFAULT '/',
  INDEX ( `parent_id`),
  UNIQUE ( `alias` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
 
CREATE TABLE `acl_aco` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `alias` VARCHAR( 255 ) NOT NULL ,
  UNIQUE ( `alias` )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(64) collate utf8_unicode_ci NOT NULL,
  `expire` datetime NOT NULL,
  `value` text collate utf8_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `users` (
  `username` VARCHAR(32) NOT NULL,
  `password` TEXT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `vars` TEXT NOT NULL,
  PRIMARY KEY (`username`),
  UNIQUE (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
