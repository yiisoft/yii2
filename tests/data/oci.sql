/**
 * This is the database schema for testing Oracle support of Yii Active Record.
 */

BEGIN EXECUTE IMMEDIATE 'DROP TABLE "composite_fk"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "order_item"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "item"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "order_item_with_null_fk"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "order"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "order_with_null_fk"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "category"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "customer"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "profile"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "type"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "null_values"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "negative_default_values"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "constraints"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "bool_values"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "animal"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "default_pk"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "document"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "dossier"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "employee"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "department"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP VIEW "animal_view"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "validator_main"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "validator_ref"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "bit_values"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END; --
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "T_constraints_4"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "T_constraints_3"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "T_constraints_2"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "T_constraints_1"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP TABLE "T_upsert"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;--

BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "profile_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "customer_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "category_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "item_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "order_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "order_with_null_fk_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "null_values_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "bool_values_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "animal_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "document_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "T_upsert_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "department_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--
BEGIN EXECUTE IMMEDIATE 'DROP SEQUENCE "employee_SEQ"'; EXCEPTION WHEN OTHERS THEN IF SQLCODE != -2289 THEN RAISE; END IF; END;--

/* STATEMENTS */

CREATE TABLE "constraints"
(
  "id" integer not null,
  "field1" varchar2(255)
);

CREATE TABLE "profile" (
  "id" integer not null,
  "description" varchar2(128) NOT NULL,
  CONSTRAINT "profile_PK" PRIMARY KEY ("id") ENABLE
);

CREATE SEQUENCE "profile_SEQ";

CREATE TABLE "customer" (
  "id" integer not null,
  "email" varchar2(128) NOT NULL UNIQUE,
  "name" varchar2(128),
  "address" varchar(4000),
  "status" integer DEFAULT 0,
  "bool_status" char DEFAULT 0 check ("bool_status" in (0,1)),
  "profile_id" integer,
  CONSTRAINT "customer_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "customer_SEQ";

comment on column "customer"."email" is 'someone@example.com';

CREATE TABLE "category" (
  "id" integer not null,
  "name" varchar2(128) NOT NULL,
  CONSTRAINT "category_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "category_SEQ";

CREATE TABLE "item" (
  "id" integer not null,
  "name" varchar2(128) NOT NULL,
  "category_id" integer NOT NULL references "category"("id") on DELETE CASCADE,
  CONSTRAINT "item_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "item_SEQ";

CREATE TABLE "order" (
  "id" integer not null,
  "customer_id" integer NOT NULL references "customer"("id") on DELETE CASCADE,
  "created_at" integer NOT NULL,
  "total" decimal(10,0) NOT NULL,
  CONSTRAINT "order_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "order_SEQ";

CREATE TABLE "order_with_null_fk" (
  "id" integer not null,
  "customer_id" integer,
  "created_at" integer NOT NULL,
  "total" decimal(10,0) NOT NULL,
  CONSTRAINT "order_with_null_fk_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "order_with_null_fk_SEQ";

CREATE TABLE "order_item" (
  "order_id" integer NOT NULL references "order"("id") on DELETE CASCADE,
  "item_id" integer NOT NULL references "item"("id") on DELETE CASCADE,
  "quantity" integer NOT NULL,
  "subtotal" decimal(10,0) NOT NULL,
  CONSTRAINT "order_item_PK" PRIMARY KEY ("order_id", "item_id") ENABLE
);

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
  CONSTRAINT "composite_fk_PK" PRIMARY KEY ("id") ENABLE,
  CONSTRAINT FK_composite_fk_order_item FOREIGN KEY ("order_id", "item_id")
    REFERENCES "order_item" ("order_id", "item_id") ON DELETE CASCADE
);

CREATE TABLE "null_values" (
  "id" INT NOT NULL,
  "var1" INT NULL,
  "var2" INT NULL,
  "var3" INT DEFAULT NULL,
  "stringcol" varchar2(32) DEFAULT NULL,
  CONSTRAINT "null_values_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "null_values_SEQ";

CREATE TABLE "negative_default_values" (
    "tinyint_col" number(3) default -123,
    "smallint_col" smallint default -123,
    "int_col" integer default -123,
    "bigint_col" integer default -123,
    "float_col" double precision default -12345.6789,
    "numeric_col" decimal(5,2) default -33.22
);

CREATE TABLE "type" (
  "int_col" integer NOT NULL,
  "int_col2" integer DEFAULT 1,
  "tinyint_col" number(3) DEFAULT 1,
  "smallint_col" smallint DEFAULT 1,
  "char_col" char(100) NOT NULL,
  "char_col2" varchar2(100) DEFAULT 'something',
  "char_col3" varchar2(4000),
  "float_col" double precision NOT NULL,
  "float_col2" double precision DEFAULT 1.23,
  "blob_col" blob,
  "numeric_col" decimal(5,2) DEFAULT 33.22,
  "time" timestamp DEFAULT to_timestamp('2002-01-01 00:00:00', 'yyyy-mm-dd hh24:mi:ss') NOT NULL,
  "bool_col" char NOT NULL check ("bool_col" in (0,1)),
  "bool_col2" char DEFAULT 1 check("bool_col2" in (0,1)),
  "ts_default" TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  "bit_col" char(3) DEFAULT 130 NOT NULL
);

CREATE TABLE "bool_values" (
  "id" integer not null,
  "bool_col" char check ("bool_col" in (0,1)),
  "default_true" char default 1 not null check ("default_true" in (0,1)),
  "default_false" char default 0 not null check ("default_false" in (0,1)),
  CONSTRAINT "bool_values_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "bool_values_SEQ";


CREATE TABLE "animal" (
  "id" integer,
  "type" varchar2(255) not null,
  CONSTRAINT "animal_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "animal_SEQ";

CREATE TABLE "default_pk" (
  "id" integer default 5 not null,
  "type" varchar2(255) not null,
  CONSTRAINT "default_pk_PK" PRIMARY KEY ("id") ENABLE
);

CREATE TABLE "document" (
  "id" integer,
  "title" varchar2(255) not null,
  "content" varchar(4000),
  "version" integer default 0 not null,
  CONSTRAINT "document_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "document_SEQ";

CREATE TABLE "department" (
  "id" INTEGER NOT NULL,
  "title" varchar2(255) not null,
  CONSTRAINT "department_PK" PRIMARY KEY ("id") ENABLE
);
CREATE SEQUENCE "department_SEQ";

CREATE TABLE "employee" (
  "id" INTEGER NOT NULL,
  "department_id" INTEGER NOT NULL,
  "first_name" varchar2(255) not null,
  "last_name" varchar2(255) not null,
  CONSTRAINT "employee_PK" PRIMARY KEY ("id", "department_id") ENABLE
);
CREATE SEQUENCE "employee_SEQ";

CREATE TABLE "dossier" (
  "id" INTEGER NOT NULL,
  "department_id" INTEGER NOT NULL,
  "employee_id" INTEGER NOT NULL,
  "summary" varchar2(255) not null,
  CONSTRAINT "dossier_PK" PRIMARY KEY ("id", "department_id") ENABLE
);

CREATE VIEW "animal_view" AS SELECT * FROM "animal";

CREATE TABLE "bit_values" (
  "id" integer not null,
  "val" char(1) NOT NULL,
  CONSTRAINT "bit_values_PK" PRIMARY KEY ("id") ENABLE,
  CONSTRAINT "bit_values_val" CHECK ("val" IN ('1','0'))
);

CREATE TABLE "T_constraints_1"
(
    "C_id" INT NOT NULL PRIMARY KEY,
    "C_not_null" INT NOT NULL,
    "C_check" VARCHAR(255) NULL CHECK ("C_check" <> ''),
    "C_unique" INT NOT NULL,
    "C_default" INT DEFAULT 0 NOT NULL,
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
    CONSTRAINT "CN_constraints_3" FOREIGN KEY ("C_fk_id_1", "C_fk_id_2") REFERENCES "T_constraints_2" ("C_id_1", "C_id_2") ON DELETE CASCADE
);

CREATE TABLE "T_constraints_4"
(
    "C_id" INT NOT NULL PRIMARY KEY,
    "C_col_1" INT NULL,
    "C_col_2" INT NOT NULL,
    CONSTRAINT "CN_constraints_4" UNIQUE ("C_col_1", "C_col_2")
);

CREATE TABLE "T_upsert"
(
    "id" INT NOT NULL PRIMARY KEY,
    "ts" INT NULL,
    "email" VARCHAR(128) NOT NULL UNIQUE,
    "recovery_email" VARCHAR(128) NULL,
    "address" CLOB NULL,
    "status" NUMBER(5,0) DEFAULT 0 NOT NULL,
    "orders" INT DEFAULT 0 NOT NULL,
    "profile_id" INT NULL,
    CONSTRAINT "CN_T_upsert_multi" UNIQUE ("email", "recovery_email")
);
CREATE SEQUENCE "T_upsert_SEQ";

/**
 * (Postgres-)Database Schema for validator tests
 */

CREATE TABLE "validator_main" (
  "id" integer not null,
  "field1" varchar2(255),
  CONSTRAINT "validator_main_PK" PRIMARY KEY ("id") ENABLE
);

CREATE TABLE "validator_ref" (
  "id" integer not null,
  "a_field" varchar2(255),
  "ref"     integer,
  CONSTRAINT "validator_ref_PK" PRIMARY KEY ("id") ENABLE
);

/* TRIGGERS */

CREATE TRIGGER "profile_TRG" BEFORE INSERT ON "profile" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "profile_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "customer_TRG" BEFORE INSERT ON "customer" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "customer_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "category_TRG" BEFORE INSERT ON "category" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "category_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "item_TRG" BEFORE INSERT ON "item" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "item_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "order_TRG" BEFORE INSERT ON "order" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "order_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "order_with_null_fk_TRG" BEFORE INSERT ON "order_with_null_fk" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "order_with_null_fk_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "null_values_TRG" BEFORE INSERT ON "null_values" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "null_values_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "bool_values_TRG" BEFORE INSERT ON "bool_values" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "bool_values_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "animal_TRG" BEFORE INSERT ON "animal" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "animal_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "document_TRG" BEFORE INSERT ON "document" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
  IF INSERTING AND :NEW."id" IS NULL THEN SELECT "document_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/
CREATE TRIGGER "T_upsert_TRG" BEFORE INSERT ON "T_upsert" FOR EACH ROW BEGIN <<COLUMN_SEQUENCES>> BEGIN
    IF INSERTING AND :NEW."id" IS NULL THEN SELECT "T_upsert_SEQ".NEXTVAL INTO :NEW."id" FROM SYS.DUAL; END IF;
END COLUMN_SEQUENCES;
END;
/

/* TRIGGERS */

INSERT INTO "animal" ("type") VALUES ('yiiunit\data\ar\Cat');
INSERT INTO "animal" ("type") VALUES ('yiiunit\data\ar\Dog');


INSERT INTO "profile" ("description") VALUES ('profile customer 1');
INSERT INTO "profile" ("description") VALUES ('profile customer 3');

INSERT INTO "customer" ("email", "name", "address", "status", "bool_status", "profile_id") VALUES ('user1@example.com', 'user1', 'address1', 1, 1, 1);
INSERT INTO "customer" ("email", "name", "address", "status", "bool_status") VALUES ('user2@example.com', 'user2', 'address2', 1, 1);
INSERT INTO "customer" ("email", "name", "address", "status", "bool_status", "profile_id") VALUES ('user3@example.com', 'user3', 'address3', 2, 0, 2);

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

INSERT INTO "department" ("id", "title") VALUES (1, 'IT');
INSERT INTO "department" ("id", "title") VALUES (2, 'accounting');

INSERT INTO "employee" ("id", "department_id", "first_name", "last_name") VALUES (1, 1, 'John', 'Doe');
INSERT INTO "employee" ("id", "department_id", "first_name", "last_name") VALUES (1, 2, 'Ann', 'Smith');
INSERT INTO "employee" ("id", "department_id", "first_name", "last_name") VALUES (2, 2, 'Will', 'Smith');

INSERT INTO "dossier" ("id", "department_id", "employee_id", "summary") VALUES (1, 1, 1, 'Excellent employee.');
INSERT INTO "dossier" ("id", "department_id", "employee_id", "summary") VALUES (2, 2, 1, 'Brilliant employee.');
INSERT INTO "dossier" ("id", "department_id", "employee_id", "summary") VALUES (3, 2, 2, 'Good employee.');

INSERT INTO "validator_main" ("id", "field1") VALUES (1, 'just a string1');
INSERT INTO "validator_main" ("id", "field1") VALUES (2, 'just a string2');
INSERT INTO "validator_main" ("id", "field1") VALUES (3, 'just a string3');
INSERT INTO "validator_main" ("id", "field1") VALUES (4, 'just a string4');
INSERT INTO "validator_ref" ("id", "a_field", "ref") VALUES (1, 'ref_to_2', 2);
INSERT INTO "validator_ref" ("id", "a_field", "ref") VALUES (2, 'ref_to_2', 2);
INSERT INTO "validator_ref" ("id", "a_field", "ref") VALUES (3, 'ref_to_3', 3);
INSERT INTO "validator_ref" ("id", "a_field", "ref") VALUES (4, 'ref_to_4', 4);
INSERT INTO "validator_ref" ("id", "a_field", "ref") VALUES (5, 'ref_to_4', 4);
INSERT INTO "validator_ref" ("id", "a_field", "ref") VALUES (6, 'ref_to_5', 5);

INSERT INTO "bit_values" ("id", "val")
  SELECT 1, '0' FROM SYS.DUAL
  UNION ALL SELECT 2, '1' FROM SYS.DUAL;
