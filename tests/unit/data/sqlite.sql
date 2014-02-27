/**
 * This is the database schema for testing Sqlite support of Yii DAO and Active Record.
 * The database setup in config.php is required to perform then relevant tests:
 */

DROP TABLE IF EXISTS tbl_composite_fk;
DROP TABLE IF EXISTS tbl_order_item;
DROP TABLE IF EXISTS tbl_item;
DROP TABLE IF EXISTS tbl_order;
DROP TABLE IF EXISTS tbl_category;
DROP TABLE IF EXISTS tbl_customer;
DROP TABLE IF EXISTS tbl_profile;
DROP TABLE IF EXISTS tbl_type;
DROP TABLE IF EXISTS tbl_null_values;

CREATE TABLE tbl_profile (
  id INTEGER NOT NULL,
  description varchar(128) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE tbl_customer (
  id INTEGER NOT NULL,
  email varchar(128) NOT NULL,
  name varchar(128),
  address text,
  status INTEGER DEFAULT 0,
  profile_id INTEGER,
  PRIMARY KEY (id)
);

CREATE TABLE tbl_category (
  id INTEGER NOT NULL,
  name varchar(128) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE tbl_item (
  id INTEGER NOT NULL,
  name varchar(128) NOT NULL,
  category_id INTEGER NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE tbl_order (
  id INTEGER NOT NULL,
  customer_id INTEGER NOT NULL,
  created_at INTEGER NOT NULL,
  total decimal(10,0) NOT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE tbl_order_item (
  order_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL,
  quantity INTEGER NOT NULL,
  subtotal decimal(10,0) NOT NULL,
  PRIMARY KEY (order_id, item_id)
);

CREATE TABLE `tbl_composite_fk` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_composite_fk_order_item` FOREIGN KEY (`order_id`,`item_id`) REFERENCES `tbl_order_item` (`order_id`,`item_id`) ON DELETE CASCADE
);

CREATE TABLE tbl_null_values (
  id INTEGER UNSIGNED PRIMARY KEY NOT NULL,
  var1 INTEGER UNSIGNED,
  var2 INTEGER,
  var3 INTEGER DEFAULT NULL,
  stringcol VARCHAR(32) DEFAULT NULL
);

CREATE TABLE tbl_type (
  int_col INTEGER NOT NULL,
  int_col2 INTEGER DEFAULT '1',
  char_col char(100) NOT NULL,
  char_col2 varchar(100) DEFAULT 'something',
  char_col3 text,
  float_col double(4,3) NOT NULL,
  float_col2 double DEFAULT '1.23',
  blob_col blob,
  numeric_col decimal(5,2) DEFAULT '33.22',
  time timestamp NOT NULL DEFAULT '2002-01-01 00:00:00',
  bool_col tinyint(1) NOT NULL,
  bool_col2 tinyint(1) DEFAULT '1'
);

INSERT INTO tbl_profile (description) VALUES ('profile customer 1');
INSERT INTO tbl_profile (description) VALUES ('profile customer 3');

INSERT INTO tbl_customer (email, name, address, status, profile_id) VALUES ('user1@example.com', 'user1', 'address1', 1, 1);
INSERT INTO tbl_customer (email, name, address, status) VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO tbl_customer (email, name, address, status, profile_id) VALUES ('user3@example.com', 'user3', 'address3', 2, 2);

INSERT INTO tbl_category (name) VALUES ('Books');
INSERT INTO tbl_category (name) VALUES ('Movies');

INSERT INTO tbl_item (name, category_id) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO tbl_item (name, category_id) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO tbl_item (name, category_id) VALUES ('Ice Age', 2);
INSERT INTO tbl_item (name, category_id) VALUES ('Toy Story', 2);
INSERT INTO tbl_item (name, category_id) VALUES ('Cars', 2);

INSERT INTO tbl_order (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO tbl_order (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO tbl_order (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO tbl_order_item (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

/**
 * (SqLite-)Database Schema for validator tests
 */

DROP TABLE IF EXISTS tbl_validator_main;
DROP TABLE IF EXISTS tbl_validator_ref;

CREATE TABLE tbl_validator_main (
  id     INTEGER PRIMARY KEY ,
  field1 VARCHAR(255)
);

CREATE TABLE tbl_validator_ref (
  id      INTEGER PRIMARY KEY ,
  a_field VARCHAR(255),
  ref     INT(11)
);

INSERT INTO tbl_validator_main (id, field1) VALUES (1, 'just a string1');
INSERT INTO tbl_validator_main (id, field1) VALUES (2, 'just a string2');
INSERT INTO tbl_validator_main (id, field1) VALUES (3, 'just a string3');
INSERT INTO tbl_validator_main (id, field1) VALUES (4, 'just a string4');
INSERT INTO tbl_validator_ref (id, a_field, ref) VALUES (1, 'ref_to_2', 2);
INSERT INTO tbl_validator_ref (id, a_field, ref) VALUES (2, 'ref_to_2', 2);
INSERT INTO tbl_validator_ref (id, a_field, ref) VALUES (3, 'ref_to_3', 3);
INSERT INTO tbl_validator_ref (id, a_field, ref) VALUES (4, 'ref_to_4', 4);
INSERT INTO tbl_validator_ref (id, a_field, ref) VALUES (5, 'ref_to_4', 4);
INSERT INTO tbl_validator_ref (id, a_field, ref) VALUES (6, 'ref_to_5', 5);
