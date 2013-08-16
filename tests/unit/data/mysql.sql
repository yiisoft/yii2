/**
 * This is the database schema for testing MySQL support of Yii DAO and Active Record.
 * The database setup in config.php is required to perform then relevant tests:
 */

DROP TABLE IF EXISTS tbl_order_item CASCADE;
DROP TABLE IF EXISTS tbl_item CASCADE;
DROP TABLE IF EXISTS tbl_order CASCADE;
DROP TABLE IF EXISTS tbl_category CASCADE;
DROP TABLE IF EXISTS tbl_customer CASCADE;
DROP TABLE IF EXISTS tbl_type CASCADE;
DROP TABLE IF EXISTS tbl_constraints CASCADE;

CREATE TABLE `tbl_constraints`
(
  `id` integer not null,
  `field1` varchar(255)
);


CREATE TABLE `tbl_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `name` varchar(128) NOT NULL,
  `address` text,
  `status` int (11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbl_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbl_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_item_category_id` (`category_id`),
  CONSTRAINT `FK_item_category_id` FOREIGN KEY (`category_id`) REFERENCES `tbl_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbl_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_order_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbl_order_item` (
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,0) NOT NULL,
  PRIMARY KEY (`order_id`,`item_id`),
  KEY `FK_order_item_item_id` (`item_id`),
  CONSTRAINT `FK_order_item_order_id` FOREIGN KEY (`order_id`) REFERENCES `tbl_order` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_order_item_item_id` FOREIGN KEY (`item_id`) REFERENCES `tbl_item` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tbl_type` (
  `int_col` int(11) NOT NULL,
  `int_col2` int(11) DEFAULT '1',
  `char_col` char(100) NOT NULL,
  `char_col2` varchar(100) DEFAULT 'something',
  `char_col3` text,
  `float_col` double(4,3) NOT NULL,
  `float_col2` double DEFAULT '1.23',
  `blob_col` blob,
  `numeric_col` decimal(5,2) DEFAULT '33.22',
  `time` timestamp NOT NULL DEFAULT '2002-01-01 00:00:00',
  `bool_col` tinyint(1) NOT NULL,
  `bool_col2` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO tbl_customer (email, name, address, status) VALUES ('user1@example.com', 'user1', 'address1', 1);
INSERT INTO tbl_customer (email, name, address, status) VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO tbl_customer (email, name, address, status) VALUES ('user3@example.com', 'user3', 'address3', 2);

INSERT INTO tbl_category (name) VALUES ('Books');
INSERT INTO tbl_category (name) VALUES ('Movies');

INSERT INTO tbl_item (name, category_id) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO tbl_item (name, category_id) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO tbl_item (name, category_id) VALUES ('Ice Age', 2);
INSERT INTO tbl_item (name, category_id) VALUES ('Toy Story', 2);
INSERT INTO tbl_item (name, category_id) VALUES ('Cars', 2);

INSERT INTO tbl_order (customer_id, create_time, total) VALUES (1, 1325282384, 110.0);
INSERT INTO tbl_order (customer_id, create_time, total) VALUES (2, 1325334482, 33.0);
INSERT INTO tbl_order (customer_id, create_time, total) VALUES (2, 1325502201, 40.0);

INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);


/**
 * (MySQL-)Database Schema for ExistValidatorTest
 */

DROP TABLE IF EXISTS tbl_validator_exist_main CASCADE;
DROP TABLE IF EXISTS tbl_validator_exist_ref CASCADE;

CREATE TABLE `tbl_validator_exist_main` (
  `id`     INT(11) NOT NULL AUTO_INCREMENT,
  `field1` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE =InnoDB  DEFAULT CHARSET =utf8;

CREATE TABLE `tbl_validator_exist_ref` (
  `id`      INT(11) NOT NULL AUTO_INCREMENT,
  `a_field` VARCHAR(255),
  `ref`     INT(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO tbl_validator_exist_main (id, field1) VALUES (1, 'just a string1');
INSERT INTO tbl_validator_exist_main (id, field1) VALUES (2, 'just a string2');
INSERT INTO tbl_validator_exist_main (id, field1) VALUES (3, 'just a string3');
INSERT INTO tbl_validator_exist_main (id, field1) VALUES (4, 'just a string4');
INSERT INTO tbl_validator_exist_ref (a_field, ref) VALUES ('ref_to_2', 2);
INSERT INTO tbl_validator_exist_ref (a_field, ref) VALUES ('ref_to_2', 2);
INSERT INTO tbl_validator_exist_ref (a_field, ref) VALUES ('ref_to_3', 3);
INSERT INTO tbl_validator_exist_ref (a_field, ref) VALUES ('ref_to_4', 4);
INSERT INTO tbl_validator_exist_ref (a_field, ref) VALUES ('ref_to_4', 4);
INSERT INTO tbl_validator_exist_ref (a_field, ref) VALUES ('ref_to_5', 5);
