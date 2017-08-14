/**
 * This is the database schema for testing MySQL support of Yii DAO and Active Record.
 * The database setup in config.php is required to perform then relevant tests:
 */

DROP TABLE IF EXISTS `composite_fk` CASCADE;
DROP TABLE IF EXISTS `order_item` CASCADE;
DROP TABLE IF EXISTS `order_item_with_null_fk` CASCADE;
DROP TABLE IF EXISTS `item` CASCADE;
DROP TABLE IF EXISTS `order` CASCADE;
DROP TABLE IF EXISTS `order_with_null_fk` CASCADE;
DROP TABLE IF EXISTS `category` CASCADE;
DROP TABLE IF EXISTS `customer` CASCADE;
DROP TABLE IF EXISTS `profile` CASCADE;
DROP TABLE IF EXISTS `null_values` CASCADE;
DROP TABLE IF EXISTS `negative_default_values` CASCADE;
DROP TABLE IF EXISTS `type` CASCADE;
DROP TABLE IF EXISTS `constraints` CASCADE;
DROP TABLE IF EXISTS `animal` CASCADE;
DROP TABLE IF EXISTS `default_pk` CASCADE;
DROP TABLE IF EXISTS `document` CASCADE;
DROP TABLE IF EXISTS `comment` CASCADE;
DROP VIEW IF EXISTS `animal_view`;
DROP TABLE IF EXISTS `T_constraints_4` CASCADE;
DROP TABLE IF EXISTS `T_constraints_3` CASCADE;
DROP TABLE IF EXISTS `T_constraints_2` CASCADE;
DROP TABLE IF EXISTS `T_constraints_1` CASCADE;

CREATE TABLE `constraints`
(
  `id` integer not null,
  `field1` varchar(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `name` varchar(128),
  `address` text,
  `status` int (11) DEFAULT 0,
  `profile_id` int(11),
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_customer_profile_id` FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_category_id` (`category_id`),
  CONSTRAINT `FK_item_category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `created_at` int(11) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_order_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `order_with_null_fk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11),
  `created_at` int(11) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `order_item` (
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL,
  PRIMARY KEY (`order_id`,`item_id`),
  KEY `FK_order_item_item_id` (`item_id`),
  CONSTRAINT `FK_order_item_order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_order_item_item_id` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `order_item_with_null_fk` (
  `order_id` int(11),
  `item_id` int(11),
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `composite_fk` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_composite_fk_order_item` FOREIGN KEY (`order_id`,`item_id`) REFERENCES `order_item` (`order_id`,`item_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE null_values (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `var1` INT UNSIGNED NULL,
  `var2` INT NULL,
  `var3` INT DEFAULT NULL,
  `stringcol` VARCHAR (32) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `negative_default_values` (
  `smallint_col` smallint default '-123',
  `int_col` integer default '-123',
  `bigint_col` bigint default '-123',
  `float_col` double default '-12345.6789',
  `numeric_col` decimal(5,2) default '-33.22'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `type` (
  `int_col` integer NOT NULL,
  `int_col2` integer DEFAULT '1',
  `smallint_col` smallint(1) DEFAULT '1',
  `char_col` char(100) NOT NULL,
  `char_col2` varchar(100) DEFAULT 'something',
  `char_col3` text,
  `enum_col` enum('a', 'B', 'c,D'),
  `float_col` double(4,3) NOT NULL,
  `float_col2` double DEFAULT '1.23',
  `blob_col` blob,
  `numeric_col` decimal(5,2) DEFAULT '33.22',
  `time` timestamp NOT NULL DEFAULT '2002-01-01 00:00:00',
  `bool_col` tinyint(1) NOT NULL,
  `bool_col2` tinyint(1) DEFAULT '1',
  `ts_default` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `bit_col` BIT(8) NOT NULL DEFAULT b'10000010'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `animal` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `default_pk` (
  `id` INT NOT NULL DEFAULT 5,
  `type` VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `document` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT,
  `version` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `comment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `add_comment` VARCHAR(255) NOT NULL,
  `replace_comment` VARCHAR(255) COMMENT 'comment',
  `delete_comment` VARCHAR(128) NOT NULL COMMENT 'comment',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE VIEW `animal_view` AS SELECT * FROM `animal`;

INSERT INTO `animal` (`type`) VALUES ('yiiunit\data\ar\Cat');
INSERT INTO `animal` (`type`) VALUES ('yiiunit\data\ar\Dog');

INSERT INTO `profile` (description) VALUES ('profile customer 1');
INSERT INTO `profile` (description) VALUES ('profile customer 3');

INSERT INTO `customer` (email, name, address, status, profile_id) VALUES ('user1@example.com', 'user1', 'address1', 1, 1);
INSERT INTO `customer` (email, name, address, status) VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO `customer` (email, name, address, status, profile_id) VALUES ('user3@example.com', 'user3', 'address3', 2, 2);

INSERT INTO `category` (name) VALUES ('Books');
INSERT INTO `category` (name) VALUES ('Movies');

INSERT INTO `item` (name, category_id) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO `item` (name, category_id) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO `item` (name, category_id) VALUES ('Ice Age', 2);
INSERT INTO `item` (name, category_id) VALUES ('Toy Story', 2);
INSERT INTO `item` (name, category_id) VALUES ('Cars', 2);

INSERT INTO `order` (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO `order` (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO `order` (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO `order_with_null_fk` (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO `order_with_null_fk` (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO `order_with_null_fk` (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO `order_item` (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO `order_item` (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO `order_item` (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO `order_item` (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO `order_item` (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO `order_item` (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

INSERT INTO `order_item_with_null_fk` (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO `order_item_with_null_fk` (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO `order_item_with_null_fk` (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO `order_item_with_null_fk` (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO `order_item_with_null_fk` (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO `order_item_with_null_fk` (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

INSERT INTO `document` (title, content, version) VALUES ('Yii 2.0 guide', 'This is Yii 2.0 guide', 0);


/**
 * (MySQL-)Database Schema for validator tests
 */

DROP TABLE IF EXISTS `validator_main` CASCADE;
DROP TABLE IF EXISTS `validator_ref` CASCADE;

CREATE TABLE `validator_main` (
  `id`     INT(11) NOT NULL AUTO_INCREMENT,
  `field1` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE =InnoDB  DEFAULT CHARSET =utf8;

CREATE TABLE `validator_ref` (
  `id`      INT(11) NOT NULL AUTO_INCREMENT,
  `a_field` VARCHAR(255),
  `ref`     INT(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `validator_main` (id, field1) VALUES (1, 'just a string1');
INSERT INTO `validator_main` (id, field1) VALUES (2, 'just a string2');
INSERT INTO `validator_main` (id, field1) VALUES (3, 'just a string3');
INSERT INTO `validator_main` (id, field1) VALUES (4, 'just a string4');
INSERT INTO `validator_ref` (a_field, ref) VALUES ('ref_to_2', 2);
INSERT INTO `validator_ref` (a_field, ref) VALUES ('ref_to_2', 2);
INSERT INTO `validator_ref` (a_field, ref) VALUES ('ref_to_3', 3);
INSERT INTO `validator_ref` (a_field, ref) VALUES ('ref_to_4', 4);
INSERT INTO `validator_ref` (a_field, ref) VALUES ('ref_to_4', 4);
INSERT INTO `validator_ref` (a_field, ref) VALUES ('ref_to_5', 5);

/* bit test, see https://github.com/yiisoft/yii2/issues/9006 */

DROP TABLE IF EXISTS `bit_values` CASCADE;

CREATE TABLE `bit_values` (
  `id`      INT(11) NOT NULL AUTO_INCREMENT,
  `val` bit(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `bit_values` (id, val) VALUES (1, b'0'), (2, b'1');

CREATE TABLE `T_constraints_1`
(
    `C_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `C_not_null` INT NOT NULL,
    `C_check` VARCHAR(255) NULL CHECK (`C_check` <> ''),
    `C_unique` INT NOT NULL,
    `C_default` INT NOT NULL DEFAULT 0,
    CONSTRAINT `CN_unique` UNIQUE (`C_unique`)
)
ENGINE = 'InnoDB' DEFAULT CHARSET = 'utf8';

CREATE TABLE `T_constraints_2`
(
    `C_id_1` INT NOT NULL,
    `C_id_2` INT NOT NULL,
    `C_index_1` INT NULL,
    `C_index_2_1` INT NULL,
    `C_index_2_2` INT NULL,
    CONSTRAINT `CN_constraints_2_multi` UNIQUE (`C_index_2_1`, `C_index_2_2`),
    CONSTRAINT `CN_pk` PRIMARY KEY (`C_id_1`, `C_id_2`)
)
ENGINE = 'InnoDB' DEFAULT CHARSET = 'utf8';

CREATE INDEX `CN_constraints_2_single` ON `T_constraints_2` (`C_index_1`);

CREATE TABLE `T_constraints_3`
(
    `C_id` INT NOT NULL,
    `C_fk_id_1` INT NOT NULL,
    `C_fk_id_2` INT NOT NULL,
    CONSTRAINT `CN_constraints_3` FOREIGN KEY (`C_fk_id_1`, `C_fk_id_2`) REFERENCES `T_constraints_2` (`C_id_1`, `C_id_2`) ON DELETE CASCADE ON UPDATE CASCADE
)
ENGINE = 'InnoDB' DEFAULT CHARSET = 'utf8';

CREATE TABLE `T_constraints_4`
(
    `C_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `C_col_1` INT NULL,
    `C_col_2` INT NOT NULL,
    CONSTRAINT `CN_constraints_4` UNIQUE (`C_col_1`, `C_col_2`)
)
ENGINE = 'InnoDB' DEFAULT CHARSET = 'utf8';
