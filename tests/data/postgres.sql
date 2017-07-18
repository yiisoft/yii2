/**
 * This is the database schema for testing PostgreSQL support of yii Active Record.
 * To test this feature, you need to create a database named 'yiitest' on 'localhost'
 * and create an account 'postgres/postgres' which owns this test database.
 */

DROP TABLE IF EXISTS "composite_fk" CASCADE;
DROP TABLE IF EXISTS "order_item" CASCADE;
DROP TABLE IF EXISTS "item" CASCADE;
DROP TABLE IF EXISTS "order_item_with_null_fk" CASCADE;
DROP TABLE IF EXISTS "order" CASCADE;
DROP TABLE IF EXISTS "order_with_null_fk" CASCADE;
DROP TABLE IF EXISTS "category" CASCADE;
DROP TABLE IF EXISTS "customer" CASCADE;
DROP TABLE IF EXISTS "profile" CASCADE;
DROP TABLE IF EXISTS "type" CASCADE;
DROP TABLE IF EXISTS "null_values" CASCADE;
DROP TABLE IF EXISTS "negative_default_values" CASCADE;
DROP TABLE IF EXISTS "constraints" CASCADE;
DROP TABLE IF EXISTS "bool_values" CASCADE;
DROP TABLE IF EXISTS "animal" CASCADE;
DROP TABLE IF EXISTS "default_pk" CASCADE;
DROP TABLE IF EXISTS "document" CASCADE;
DROP TABLE IF EXISTS "comment" CASCADE;
DROP VIEW IF EXISTS "animal_view";
DROP TABLE IF EXISTS "T_constraints_4";
DROP TABLE IF EXISTS "T_constraints_3";
DROP TABLE IF EXISTS "T_constraints_2";
DROP TABLE IF EXISTS "T_constraints_1";

DROP SCHEMA IF EXISTS "schema1" CASCADE;
DROP SCHEMA IF EXISTS "schema2" CASCADE;

CREATE SCHEMA "schema1";
CREATE SCHEMA "schema2";

CREATE TABLE "constraints"
(
  id integer not null,
  field1 varchar(255)
);

CREATE TABLE "profile" (
  id serial not null primary key,
  description varchar(128) NOT NULL
);

CREATE TABLE "schema1"."profile" (
  id serial not null primary key,
  description varchar(128) NOT NULL
);

CREATE TABLE "customer" (
  id serial not null primary key,
  email varchar(128) NOT NULL,
  name varchar(128),
  address text,
  status integer DEFAULT 0,
  bool_status boolean DEFAULT FALSE,
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

CREATE TABLE "order_with_null_fk" (
  id serial not null primary key,
  customer_id integer,
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

CREATE TABLE "order_item_with_null_fk" (
  order_id integer,
  item_id integer,
  quantity integer NOT NULL,
  subtotal decimal(10,0) NOT NULL
);

CREATE TABLE "composite_fk" (
  id integer NOT NULL,
  order_id integer NOT NULL,
  item_id integer NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT FK_composite_fk_order_item FOREIGN KEY (order_id, item_id) REFERENCES "order_item" (order_id, item_id) ON DELETE CASCADE
);

CREATE TABLE "null_values" (
  id serial NOT NULL,
  var1 INT NULL,
  var2 INT NULL,
  var3 INT DEFAULT NULL,
  stringcol VARCHAR(32) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE "type" (
  int_col integer NOT NULL,
  int_col2 integer DEFAULT '1',
  smallint_col smallint DEFAULT '1',
  char_col char(100) NOT NULL,
  char_col2 varchar(100) DEFAULT 'something',
  char_col3 text,
  float_col double precision NOT NULL,
  float_col2 double precision DEFAULT '1.23',
  blob_col bytea,
  numeric_col decimal(5,2) DEFAULT '33.22',
  time timestamp NOT NULL DEFAULT '2002-01-01 00:00:00',
  bool_col boolean NOT NULL,
  bool_col2 boolean DEFAULT TRUE,
  ts_default TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  bit_col BIT(8) NOT NULL DEFAULT B'10000010',
  bigint_col BIGINT
);

CREATE TABLE "bool_values" (
  id serial not null primary key,
  bool_col bool,
  default_true bool not null default true,
  default_false boolean not null default false
);

CREATE TABLE "negative_default_values" (
  smallint_col smallint default '-123',
  int_col integer default '-123',
  bigint_col bigint default '-123',
  float_col double precision default '-12345.6789',
  numeric_col decimal(5,2) default '-33.22'
);

CREATE TABLE "animal" (
  id serial primary key,
  type varchar(255) not null
);

CREATE TABLE "default_pk" (
  id integer not null default 5 primary key,
  type varchar(255) not null
);

CREATE TABLE "document" (
  id serial primary key,
  title varchar(255) not null,
  content text not null,
  version integer not null default 0
);

CREATE TABLE "comment" (
  id serial primary key,
  name varchar(255) not null,
  message text not null
);

CREATE VIEW "animal_view" AS SELECT * FROM "animal";

INSERT INTO "animal" (type) VALUES ('yiiunit\data\ar\Cat');
INSERT INTO "animal" (type) VALUES ('yiiunit\data\ar\Dog');


INSERT INTO "profile" (description) VALUES ('profile customer 1');
INSERT INTO "profile" (description) VALUES ('profile customer 3');

INSERT INTO "schema1"."profile" (description) VALUES ('profile customer 1');
INSERT INTO "schema1"."profile" (description) VALUES ('profile customer 3');

INSERT INTO "customer" (email, name, address, status, bool_status, profile_id) VALUES ('user1@example.com', 'user1', 'address1', 1, true, 1);
INSERT INTO "customer" (email, name, address, status, bool_status) VALUES ('user2@example.com', 'user2', 'address2', 1, true);
INSERT INTO "customer" (email, name, address, status, bool_status, profile_id) VALUES ('user3@example.com', 'user3', 'address3', 2, false, 2);

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

INSERT INTO "order_with_null_fk" (customer_id, created_at, total) VALUES (1, 1325282384, 110.0);
INSERT INTO "order_with_null_fk" (customer_id, created_at, total) VALUES (2, 1325334482, 33.0);
INSERT INTO "order_with_null_fk" (customer_id, created_at, total) VALUES (2, 1325502201, 40.0);

INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO "order_item" (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

INSERT INTO "order_item_with_null_fk" (order_id, item_id, quantity, subtotal) VALUES (1, 1, 1, 30.0);
INSERT INTO "order_item_with_null_fk" (order_id, item_id, quantity, subtotal) VALUES (1, 2, 2, 40.0);
INSERT INTO "order_item_with_null_fk" (order_id, item_id, quantity, subtotal) VALUES (2, 4, 1, 10.0);
INSERT INTO "order_item_with_null_fk" (order_id, item_id, quantity, subtotal) VALUES (2, 5, 1, 15.0);
INSERT INTO "order_item_with_null_fk" (order_id, item_id, quantity, subtotal) VALUES (2, 3, 1, 8.0);
INSERT INTO "order_item_with_null_fk" (order_id, item_id, quantity, subtotal) VALUES (3, 2, 1, 40.0);

INSERT INTO "document" (title, content, version) VALUES ('Yii 2.0 guide', 'This is Yii 2.0 guide', 0);

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

/* bit test, see https://github.com/yiisoft/yii2/issues/9006 */

DROP TABLE IF EXISTS "bit_values" CASCADE;

CREATE TABLE "bit_values" (
  id serial not null primary key,
  val bit(1) not null
);

INSERT INTO "bit_values" (id, val) VALUES (1, '0'), (2, '1');

CREATE TABLE "T_constraints_1"
(
    "C_id" INT NOT NULL PRIMARY KEY,
    "C_not_null" INT NOT NULL,
    "C_check" VARCHAR(255) NULL CHECK ("C_check" <> ''),
    "C_unique" INT NOT NULL,
    "C_default" INT NOT NULL DEFAULT 0,
    CONSTRAINT "CN_unique" UNIQUE ("C_unique")
);

CREATE TABLE "T_constraints_2"
(
    "C_id_1" INT NOT NULL,
    "C_id_2" INT NOT NULL,
    "C_index_1" INT NULL,
    "C_index_2_1" INT NULL,
    "C_index_2_2" INT NULL,
    CONSTRAINT "CN_constraints_2_multi" UNIQUE ("C_index_2_1", "C_index_2_2"),
    CONSTRAINT "CN_pk" PRIMARY KEY ("C_id_1", "C_id_2")
);

CREATE INDEX "CN_constraints_2_single" ON "T_constraints_2" ("C_index_1");

CREATE TABLE "T_constraints_3"
(
    "C_id" INT NOT NULL,
    "C_fk_id_1" INT NOT NULL,
    "C_fk_id_2" INT NOT NULL,
    CONSTRAINT "CN_constraints_3" FOREIGN KEY ("C_fk_id_1", "C_fk_id_2") REFERENCES "T_constraints_2" ("C_id_1", "C_id_2") ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE "T_constraints_4"
(
    "C_id" INT NOT NULL PRIMARY KEY,
    "C_col_1" INT NULL,
    "C_col_2" INT NOT NULL,
    CONSTRAINT "CN_constraints_4" UNIQUE ("C_col_1", "C_col_2")
);
