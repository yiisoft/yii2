/**
 * This is the database schema for testing DB2 support of Yii DAO and Active Record.
 * The database setup in config.php is required to perform then relevant tests:
 */

BEGIN
  DECLARE CONTINUE HANDLER FOR SQLSTATE '42704' BEGIN END;
  EXECUTE IMMEDIATE 'DROP TABLE "composite_fk"';
  EXECUTE IMMEDIATE 'DROP TABLE "order_item"';
  EXECUTE IMMEDIATE 'DROP TABLE "order_item_with_null_fk"';
  EXECUTE IMMEDIATE 'DROP TABLE "item"';
  EXECUTE IMMEDIATE 'DROP TABLE "order"';
  EXECUTE IMMEDIATE 'DROP TABLE "order_with_null_fk"';
  EXECUTE IMMEDIATE 'DROP TABLE "category"';
  EXECUTE IMMEDIATE 'DROP TABLE "customer"';
  EXECUTE IMMEDIATE 'DROP TABLE "profile"';
  EXECUTE IMMEDIATE 'DROP TABLE "null_values"';
  EXECUTE IMMEDIATE 'DROP TABLE "type"';
  EXECUTE IMMEDIATE 'DROP TABLE "constraints"';
  EXECUTE IMMEDIATE 'DROP TABLE "animal"';
  EXECUTE IMMEDIATE 'DROP TABLE "default_pk"';
  EXECUTE IMMEDIATE 'DROP TABLE "document"';
  EXECUTE IMMEDIATE 'DROP TABLE "validator_main"';
  EXECUTE IMMEDIATE 'DROP TABLE "validator_ref"';
  EXECUTE IMMEDIATE 'DROP TABLE "bit_values"';
  EXECUTE IMMEDIATE 'DROP VIEW "animal_view"';
END;--

/* STATEMENTS */

CREATE TABLE "constraints" (
  "id" integer not null,
  "field1" varchar(255)
);

CREATE TABLE "profile" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "description" varchar(128) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "customer" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "email" varchar(128) NOT NULL,
  "name" varchar(128),
  "address" clob,
  "status" integer DEFAULT 0,
  "profile_id" integer,
  PRIMARY KEY ("id")
);

CREATE TABLE "category" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "name" varchar(128) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "item" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "name" varchar(128) NOT NULL,
  "category_id" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "FK_item_category_id" FOREIGN KEY ("category_id") REFERENCES "category" ("id") ON DELETE CASCADE
);
CREATE INDEX "FK_item_category_id" ON "item" ("category_id");

CREATE TABLE "order" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "customer_id" integer NOT NULL,
  "created_at" integer NOT NULL,
  "total" decimal(10,0) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "FK_order_customer_id" FOREIGN KEY ("customer_id") REFERENCES "customer" ("id") ON DELETE CASCADE
);

CREATE TABLE "order_with_null_fk" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "customer_id" integer,
  "created_at" integer NOT NULL,
  "total" decimal(10,0) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "order_item" (
  "order_id" integer NOT NULL,
  "item_id" integer NOT NULL,
  "quantity" integer NOT NULL,
  "subtotal" decimal(10,0) NOT NULL,
  PRIMARY KEY ("order_id", "item_id"),
  CONSTRAINT "FK_order_item_order_id" FOREIGN KEY ("order_id") REFERENCES "order" ("id") ON DELETE CASCADE,
  CONSTRAINT "FK_order_item_item_id" FOREIGN KEY ("item_id") REFERENCES "item" ("id") ON DELETE CASCADE
);
CREATE INDEX "FK_order_item_item_id" ON "order_item" ("item_id");

CREATE TABLE "order_item_with_null_fk" (
  "order_id" integer,
  "item_id" integer,
  "quantity" integer NOT NULL,
  "subtotal" decimal(10,0) NOT NULL
);

CREATE TABLE "composite_fk" (
  "id" integer NOT NULL,
  "order_id" integer NOT NULL,
  "item_id" integer NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "FK_composite_fk_order_item" FOREIGN KEY ("order_id", "item_id") REFERENCES "order_item" ("order_id", "item_id") ON DELETE CASCADE
);

CREATE TABLE "null_values" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "var1" integer,
  "var2" integer,
  "var3" integer DEFAULT NULL,
  "stringcol" varchar(32) DEFAULT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "type" (
  "int_col" integer NOT NULL,
  "int_col2" integer DEFAULT 1,
  "smallint_col" smallint DEFAULT 1,
  "char_col" char(100) NOT NULL,
  "char_col2" varchar(100) DEFAULT 'something',
  "char_col3" clob,
  "float_col" double NOT NULL,
  "float_col2" double DEFAULT 1.23,
  "blob_col" blob,
  "numeric_col" decimal(5,2) DEFAULT 33.22,
  "time" timestamp NOT NULL DEFAULT '2002-01-01 00:00:00',
  "bool_col" smallint NOT NULL,
  "bool_col2" smallint DEFAULT 1,
  "ts_default" timestamp NOT NULL DEFAULT CURRENT TIMESTAMP,
  "bit_col" smallint NOT NULL DEFAULT 130
);

CREATE TABLE "animal" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "type" varchar(255) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "default_pk" (
  "id" integer NOT NULL DEFAULT 5,
  "type" varchar(255) NOT NULL,
  PRIMARY KEY ("id")
);

CREATE TABLE "document" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "title" varchar(255) NOT NULL,
  "content" clob,
  "version" integer NOT NULL DEFAULT 0,
  PRIMARY KEY ("id")
);

CREATE TABLE "validator_main" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "field1" varchar(255),
  PRIMARY KEY ("id")
);

CREATE TABLE "validator_ref" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "a_field" varchar(255),
  "ref" integer,
  PRIMARY KEY ("id")
);

CREATE TABLE "bit_values" (
  "id" integer NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1),
  "val" smallint NOT NULL,
  PRIMARY KEY ("id")
);

CREATE VIEW "animal_view" AS SELECT * FROM "animal";

INSERT INTO "animal" ("type") VALUES ('yiiunit\data\ar\Cat');
INSERT INTO "animal" ("type") VALUES ('yiiunit\data\ar\Dog');

INSERT INTO "profile" ("description") VALUES ('profile customer 1');
INSERT INTO "profile" ("description") VALUES ('profile customer 3');

INSERT INTO "customer" ("email", "name", "address", "status", "profile_id") VALUES ('user1@example.com', 'user1', 'address1', 1, 1);
INSERT INTO "customer" ("email", "name", "address", "status") VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO "customer" ("email", "name", "address", "status", "profile_id") VALUES ('user3@example.com', 'user3', 'address3', 2, 2);

INSERT INTO "category" ("name") VALUES ('Books');
INSERT INTO "category" ("name") VALUES ('Movies');

INSERT INTO "item" ("name", "category_id") VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO "item" ("name", "category_id") VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO "item" ("name", "category_id") VALUES ('Ice Age', 2);
INSERT INTO "item" ("name", "category_id") VALUES ('Toy Story', 2);
INSERT INTO "item" ("name", "category_id") VALUES ('Cars', 2);

INSERT INTO "order" ("customer_id", "created_at", "total") VALUES (1, 1325282384, 110.0);
INSERT INTO "order" ("customer_id", "created_at", "total") VALUES (2, 1325334482, 33.0);
INSERT INTO "order" ("customer_id", "created_at", "total") VALUES (2, 1325502201, 40.0);

INSERT INTO "order_with_null_fk" ("customer_id", "created_at", "total") VALUES (1, 1325282384, 110.0);
INSERT INTO "order_with_null_fk" ("customer_id", "created_at", "total") VALUES (2, 1325334482, 33.0);
INSERT INTO "order_with_null_fk" ("customer_id", "created_at", "total") VALUES (2, 1325502201, 40.0);

INSERT INTO "order_item" ("order_id", "item_id", "quantity", "subtotal") VALUES (1, 1, 1, 30.0);
INSERT INTO "order_item" ("order_id", "item_id", "quantity", "subtotal") VALUES (1, 2, 2, 40.0);
INSERT INTO "order_item" ("order_id", "item_id", "quantity", "subtotal") VALUES (2, 4, 1, 10.0);
INSERT INTO "order_item" ("order_id", "item_id", "quantity", "subtotal") VALUES (2, 5, 1, 15.0);
INSERT INTO "order_item" ("order_id", "item_id", "quantity", "subtotal") VALUES (2, 3, 1, 8.0);
INSERT INTO "order_item" ("order_id", "item_id", "quantity", "subtotal") VALUES (3, 2, 1, 40.0);

INSERT INTO "order_item_with_null_fk" ("order_id", "item_id", "quantity", "subtotal") VALUES (1, 1, 1, 30.0);
INSERT INTO "order_item_with_null_fk" ("order_id", "item_id", "quantity", "subtotal") VALUES (1, 2, 2, 40.0);
INSERT INTO "order_item_with_null_fk" ("order_id", "item_id", "quantity", "subtotal") VALUES (2, 4, 1, 10.0);
INSERT INTO "order_item_with_null_fk" ("order_id", "item_id", "quantity", "subtotal") VALUES (2, 5, 1, 15.0);
INSERT INTO "order_item_with_null_fk" ("order_id", "item_id", "quantity", "subtotal") VALUES (2, 3, 1, 8.0);
INSERT INTO "order_item_with_null_fk" ("order_id", "item_id", "quantity", "subtotal") VALUES (3, 2, 1, 40.0);

INSERT INTO "document" ("title", "content", "version") VALUES ('Yii 2.0 guide', 'This is Yii 2.0 guide', 0);

INSERT INTO "validator_main" ("field1") VALUES ('just a string1');
INSERT INTO "validator_main" ("field1") VALUES ('just a string2');
INSERT INTO "validator_main" ("field1") VALUES ('just a string3');
INSERT INTO "validator_main" ("field1") VALUES ('just a string4');
INSERT INTO "validator_ref" ("a_field", "ref") VALUES ('ref_to_2', 2);
INSERT INTO "validator_ref" ("a_field", "ref") VALUES ('ref_to_2', 2);
INSERT INTO "validator_ref" ("a_field", "ref") VALUES ('ref_to_3', 3);
INSERT INTO "validator_ref" ("a_field", "ref") VALUES ('ref_to_4', 4);
INSERT INTO "validator_ref" ("a_field", "ref") VALUES ('ref_to_4', 4);
INSERT INTO "validator_ref" ("a_field", "ref") VALUES ('ref_to_5', 5);

INSERT INTO "bit_values" ("val") VALUES (0), (1);