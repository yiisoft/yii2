/**
 * This is the database schema for testing PostgreSQL support of yii Active Record.
 * To test this feature, you need to create a database named 'yiitest' on 'localhost'
 * and create an account 'postgres/postgres' which owns this test database.
 */

DROP TABLE IF EXISTS tbl_order_item CASCADE;
DROP TABLE IF EXISTS tbl_item CASCADE;
DROP TABLE IF EXISTS tbl_order CASCADE;
DROP TABLE IF EXISTS tbl_category CASCADE;
DROP TABLE IF EXISTS tbl_customer CASCADE;
DROP TABLE IF EXISTS tbl_type CASCADE;

CREATE TABLE tbl_customer (
  id serial not null primary key,
  email varchar(128) NOT NULL,
  name varchar(128) NOT NULL,
  address text,
  status integer DEFAULT 0 
);

CREATE TABLE tbl_category (
  id serial not null primary key,
  name varchar(128) NOT NULL
);

CREATE TABLE tbl_item (
  id serial not null primary key,
  name varchar(128) NOT NULL,
  category_id integer NOT NULL references tbl_category(id) on UPDATE CASCADE on DELETE CASCADE
);

CREATE TABLE tbl_order (
  id serial not null primary key,
  customer_id integer NOT NULL references tbl_customer(id) on UPDATE CASCADE on DELETE CASCADE,
  create_time integer NOT NULL,
  total decimal(10,0) NOT NULL
);

CREATE TABLE tbl_order_item (
  order_id integer NOT NULL references tbl_order(id) on UPDATE CASCADE on DELETE CASCADE,
  item_id integer NOT NULL references tbl_item(id) on UPDATE CASCADE on DELETE CASCADE,
  quantity integer NOT NULL,
  subtotal decimal(10,0) NOT NULL,
  PRIMARY KEY (order_id,item_id)
);

CREATE TABLE tbl_type (
  int_col integer NOT NULL,
  int_col2 integer DEFAULT '1',
  char_col char(100) NOT NULL,
  char_col2 varchar(100) DEFAULT 'something',
  char_col3 text,
  float_col double precision NOT NULL,
  float_col2 double precision DEFAULT '1.23',
  blob_col bytea,
  numeric_col decimal(5,2) DEFAULT '33.22',
  time timestamp NOT NULL DEFAULT '2002-01-01 00:00:00',
  bool_col smallint NOT NULL,
  bool_col2 smallint DEFAULT '1'
);

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
