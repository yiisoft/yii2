/**
 * This is the database schema for testing PostgreSQL support of yii Active Record.
 * To test this feature, you need to create a database named 'yiitest' on 'localhost'
 * and create an account 'postgres/postgres' which owns this test database.
 */

DROP TABLE IF EXISTS "order_item" CASCADE;
DROP TABLE IF EXISTS "item" CASCADE;
DROP TABLE IF EXISTS "order" CASCADE;
DROP TABLE IF EXISTS "category" CASCADE;
DROP TABLE IF EXISTS "customer" CASCADE;
DROP TABLE IF EXISTS "profile" CASCADE;
DROP TABLE IF EXISTS "type" CASCADE;
DROP TABLE IF EXISTS "null_values" CASCADE;
DROP TABLE IF EXISTS "constraints" CASCADE;

CREATE TABLE "constraints"
(
  id integer not null,
  field1 varchar(255)
);

CREATE TABLE "profile" (
  id serial not null primary key,
  description varchar(128) NOT NULL
);

CREATE TABLE "customer" (
  id serial not null primary key,
  email varchar(128) NOT NULL,
  name varchar(128),
  address text,
  status integer DEFAULT 0,
  profile_id integer
);

comment on column public.customer.email is 'someone@example.com';

CREATE TABLE "category" (
  id serial not null primary key,
  name varchar(128) NOT NULL
);

CREATE TABLE "item" (
  id serial not null primary key,
  name varchar(128) NOT NULL,
  category_id integer NOT NULL references "category"(id) on UPDATE CASCADE on DELETE CASCADE
);

CREATE TABLE "order" (
  id serial not null primary key,
  customer_id integer NOT NULL references "customer"(id) on UPDATE CASCADE on DELETE CASCADE,
  created_at integer NOT NULL,
  total decimal(10,0) NOT NULL
);

CREATE TABLE "order_item" (
  order_id integer NOT NULL references "order"(id) on UPDATE CASCADE on DELETE CASCADE,
  item_id integer NOT NULL references "item"(id) on UPDATE CASCADE on DELETE CASCADE,
  quantity integer NOT NULL,
  subtotal decimal(10,0) NOT NULL,
  PRIMARY KEY (order_id,item_id)
);

CREATE TABLE "null_values" (
  id INT NOT NULL,
  var1 INT NULL,
  var2 INT NULL,
  var3 INT DEFAULT NULL,
  stringcol VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE "type" (
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

INSERT INTO "profile" (description) VALUES ('profile customer 1');
INSERT INTO "profile" (description) VALUES ('profile customer 3');

INSERT INTO "customer" (email, name, address, status, profile_id) VALUES ('user1@example.com', 'user1', 'address1', 1, 1);
INSERT INTO "customer" (email, name, address, status) VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO "customer" (email, name, address, status, profile_id) VALUES ('user3@example.com', 'user3', 'address3', 2, 2);

INSERT INTO "category" (name) VALUES ('Books');
INSERT INTO "category" (name) VALUES ('Movies');

INSERT INTO "item" (name, category_id) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO "item" (name, category_id) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO "item" (name, category_id) VALUES ('Ice Age', 2);
INSERT INTO "item" (name, category_id) VALUES ('Toy Story', 2);
INSERT INTO "item" (name, category_id) VALUES ('Cars', 2);

INSERT INTO "order" (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO "order" (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO "order" (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

/**
 * (Postgres-)Database Schema for validator tests
 */

DROP TABLE IF EXISTS "validator_main" CASCADE;
DROP TABLE IF EXISTS "validator_ref" CASCADE;

CREATE TABLE "validator_main" (
  id integer not null primary key,
  field1 VARCHAR(255)
);

CREATE TABLE "validator_ref" (
  id integer not null primary key,
  a_field VARCHAR(255),
  ref     integer
);

INSERT INTO "validator_main" (id, field1) VALUES (1, 'just a string1');
INSERT INTO "validator_main" (id, field1) VALUES (2, 'just a string2');
INSERT INTO "validator_main" (id, field1) VALUES (3, 'just a string3');
INSERT INTO "validator_main" (id, field1) VALUES (4, 'just a string4');
INSERT INTO "validator_ref" (id, a_field, ref) VALUES (1, 'ref_to_2', 2);
INSERT INTO "validator_ref" (id, a_field, ref) VALUES (2, 'ref_to_2', 2);
INSERT INTO "validator_ref" (id, a_field, ref) VALUES (3, 'ref_to_3', 3);
INSERT INTO "validator_ref" (id, a_field, ref) VALUES (4, 'ref_to_4', 4);
INSERT INTO "validator_ref" (id, a_field, ref) VALUES (5, 'ref_to_4', 4);
INSERT INTO "validator_ref" (id, a_field, ref) VALUES (6, 'ref_to_5', 5);
