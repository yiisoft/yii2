IF OBJECT_ID('[dbo].[composite_fk]', 'U') IS NOT NULL DROP TABLE [dbo].[composite_fk];
IF OBJECT_ID('[dbo].[order_item]', 'U') IS NOT NULL DROP TABLE [dbo].[order_item];
IF OBJECT_ID('[dbo].[order_item_with_null_fk]', 'U') IS NOT NULL DROP TABLE [dbo].[order_item_with_null_fk];
IF OBJECT_ID('[dbo].[item]', 'U') IS NOT NULL DROP TABLE [dbo].[item];
IF OBJECT_ID('[dbo].[order]', 'U') IS NOT NULL DROP TABLE [dbo].[order];
IF OBJECT_ID('[dbo].[order_with_null_fk]', 'U') IS NOT NULL DROP TABLE [dbo].[order_with_null_fk];
IF OBJECT_ID('[dbo].[category]', 'U') IS NOT NULL DROP TABLE [dbo].[category];
IF OBJECT_ID('[dbo].[customer]', 'U') IS NOT NULL DROP TABLE [dbo].[customer];
IF OBJECT_ID('[dbo].[profile]', 'U') IS NOT NULL DROP TABLE [dbo].[profile];
IF OBJECT_ID('[dbo].[type]', 'U') IS NOT NULL DROP TABLE [dbo].[type];
IF OBJECT_ID('[dbo].[null_values]', 'U') IS NOT NULL DROP TABLE [dbo].[null_values];
IF OBJECT_ID('[dbo].[test_trigger]', 'U') IS NOT NULL DROP TABLE [dbo].[test_trigger];
IF OBJECT_ID('[dbo].[test_trigger_alert]', 'U') IS NOT NULL DROP TABLE [dbo].[test_trigger_alert];
IF OBJECT_ID('[dbo].[negative_default_values]', 'U') IS NOT NULL DROP TABLE [dbo].[negative_default_values];
IF OBJECT_ID('[dbo].[animal]', 'U') IS NOT NULL DROP TABLE [dbo].[animal];
IF OBJECT_ID('[dbo].[default_pk]', 'U') IS NOT NULL DROP TABLE [dbo].[default_pk];
IF OBJECT_ID('[dbo].[document]', 'U') IS NOT NULL DROP TABLE [dbo].[document];
IF OBJECT_ID('[dbo].[dossier]', 'U') IS NOT NULL DROP TABLE [dbo].[dossier];
IF OBJECT_ID('[dbo].[employee]', 'U') IS NOT NULL DROP TABLE [dbo].[employee];
IF OBJECT_ID('[dbo].[department]', 'U') IS NOT NULL DROP TABLE [dbo].[department];
IF OBJECT_ID('[dbo].[animal_view]', 'V') IS NOT NULL DROP VIEW [dbo].[animal_view];
IF OBJECT_ID('[T_constraints_4]', 'U') IS NOT NULL DROP TABLE [T_constraints_4];
IF OBJECT_ID('[T_constraints_3]', 'U') IS NOT NULL DROP TABLE [T_constraints_3];
IF OBJECT_ID('[T_constraints_2]', 'U') IS NOT NULL DROP TABLE [T_constraints_2];
IF OBJECT_ID('[T_constraints_1]', 'U') IS NOT NULL DROP TABLE [T_constraints_1];
IF OBJECT_ID('[T_upsert]', 'U') IS NOT NULL DROP TABLE [T_upsert];
IF OBJECT_ID('[T_upsert_1]', 'U') IS NOT NULL DROP TABLE [T_upsert_1];
IF OBJECT_ID('[T_upsert_varbinary]', 'U') IS NOT NULL DROP TABLE [T_upsert_varbinary];
IF OBJECT_ID('[table.with.special.characters]', 'U') IS NOT NULL DROP TABLE [table.with.special.characters];
IF OBJECT_ID('[stranger ''table]', 'U') IS NOT NULL DROP TABLE [stranger 'table];
IF OBJECT_ID('[foo1]', 'U') IS NOT NULL DROP TABLE [foo1];

CREATE TABLE [dbo].[profile] (
    [id] [int] IDENTITY NOT NULL,
    [description] [varchar](128) NOT NULL,
    CONSTRAINT [PK_profile] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[customer] (
    [id] [int] IDENTITY NOT NULL,
    [email] [varchar](128) NOT NULL,
    [name] [varchar](128),
    [address] [text],
    [status] [int] DEFAULT 0,
    [profile_id] [int],
    CONSTRAINT [PK_customer] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[category] (
    [id] [int] IDENTITY NOT NULL,
    [name] [varchar](128) NOT NULL,
    CONSTRAINT [PK_category] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[item] (
    [id] [int] IDENTITY NOT NULL,
    [name] [varchar](128) NOT NULL,
    [category_id] [int] NOT NULL,
    CONSTRAINT [PK_item] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[order] (
    [id] [int] IDENTITY NOT NULL,
    [customer_id] [int] NOT NULL,
    [created_at] [int] NOT NULL,
    [total] [decimal](10,0) NOT NULL,
    CONSTRAINT [PK_order] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[order_with_null_fk] (
    [id] [int] IDENTITY NOT NULL,
    [customer_id] [int] ,
    [created_at] [int] NOT NULL,
    [total] [decimal](10,0) NOT NULL,
    CONSTRAINT [PK_order_with_null_fk] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[order_item] (
    [order_id] [int] NOT NULL,
    [item_id] [int] NOT NULL,
    [quantity] [int] NOT NULL,
    [subtotal] [decimal](10,0) NOT NULL,
    CONSTRAINT [PK_order_item] PRIMARY KEY CLUSTERED (
        [order_id] ASC,
        [item_id] ASC
    ) ON [PRIMARY]

);

create table [dbo].[composite_fk]
(
    id int not null
        constraint composite_fk_pk
            primary key nonclustered,
    order_id int not null,
    item_id int not null,
    constraint FK_composite_fk_order_item
        foreign key (order_id, item_id) references order_item
            on delete cascade
);

CREATE TABLE [dbo].[order_item_with_null_fk] (
    [order_id] [int],
    [item_id] [int],
    [quantity] [int] NOT NULL,
    [subtotal] [decimal](10,0) NOT NULL
);

CREATE TABLE [dbo].[null_values] (
  [id] [int] IDENTITY NOT NULL,
  var1 [int] NULL,
  var2 [int] NULL,
  var3 [int] DEFAULT NULL,
  stringcol [varchar](32) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE [dbo].[negative_default_values] (
  [tinyint_col] [tinyint] DEFAULT '-123',
  [smallint_col] [tinyint] DEFAULT '-123',
  [int_col] [smallint] DEFAULT '-123',
  [bigint_col] [int] DEFAULT '-123',
  [float_col] [float] DEFAULT '-12345.6789',
  [numeric_col] [decimal](5,2) DEFAULT '-33.22'
);

CREATE TABLE [dbo].[type] (
    [int_col] [int] NOT NULL,
    [int_col2] [int] DEFAULT '1',
    [tinyint_col] [tinyint] DEFAULT '1',
    [smallint_col] [smallint] DEFAULT '1',
    [char_col] [char](100) NOT NULL,
    [char_col2] [varchar](100) DEFAULT 'something',
    [char_col3] [text],
    [float_col] [decimal](4,3) NOT NULL,
    [float_col2] [float] DEFAULT '1.23',
    [blob_col] [varbinary](MAX),
    [numeric_col] [decimal](5,2) DEFAULT '33.22',
    [time] [datetime] NOT NULL DEFAULT '2002-01-01 00:00:00',
    [bool_col] [tinyint] NOT NULL,
    [bool_col2] [tinyint] DEFAULT '1'
);

CREATE TABLE [dbo].[animal] (
    [id] [int] IDENTITY NOT NULL,
    [type] [varchar](255) NOT NULL,
    CONSTRAINT [PK_animal] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[default_pk] (
    [id] [int] NOT NULL DEFAULT 5,
    [type] [varchar](255) NOT NULL,
    CONSTRAINT [PK_default_pk] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[document] (
    [id] [int] IDENTITY NOT NULL,
    [title] [varchar](255) NOT NULL,
    [content] [text],
    [version] [int] NOT NULL DEFAULT 0,
    CONSTRAINT [PK_document_pk] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[department] (
    [id] [int] IDENTITY NOT NULL,
    [title] [varchar](255) NOT NULL,
    CONSTRAINT [PK_department_pk] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[employee] (
    [id] [int] NOT NULL,
    [department_id] [int] NOT NULL,
    [first_name] [varchar](255) NOT NULL,
    [last_name] [varchar](255) NOT NULL,
    CONSTRAINT [PK_employee_pk] PRIMARY KEY CLUSTERED (
        [id] ASC,
        [department_id] ASC
    ) ON [PRIMARY]
);

CREATE TABLE [dbo].[dossier] (
    [id] [int] IDENTITY NOT NULL,
    [department_id] [int] NOT NULL,
    [employee_id] [int] NOT NULL,
    [summary] [varchar](255) NOT NULL,
    CONSTRAINT [PK_dossier_pk] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

CREATE VIEW [dbo].[animal_view] AS SELECT * FROM [dbo].[animal];

INSERT INTO [dbo].[animal] (type) VALUES ('yiiunit\data\ar\Cat');
INSERT INTO [dbo].[animal] (type) VALUES ('yiiunit\data\ar\Dog');

INSERT INTO [dbo].[profile] ([description]) VALUES ('profile customer 1');
INSERT INTO [dbo].[profile] ([description]) VALUES ('profile customer 3');

INSERT INTO [dbo].[customer] ([email], [name], [address], [status], [profile_id]) VALUES ('user1@example.com', 'user1', 'address1', 1, 1);
INSERT INTO [dbo].[customer] ([email], [name], [address], [status]) VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO [dbo].[customer] ([email], [name], [address], [status], [profile_id]) VALUES ('user3@example.com', 'user3', 'address3', 2, 2);

INSERT INTO [dbo].[category] ([name]) VALUES ('Books');
INSERT INTO [dbo].[category] ([name]) VALUES ('Movies');

INSERT INTO [dbo].[item] ([name], [category_id]) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO [dbo].[item] ([name], [category_id]) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO [dbo].[item] ([name], [category_id]) VALUES ('Ice Age', 2);
INSERT INTO [dbo].[item] ([name], [category_id]) VALUES ('Toy Story', 2);
INSERT INTO [dbo].[item] ([name], [category_id]) VALUES ('Cars', 2);

INSERT INTO [dbo].[order] ([customer_id], [created_at], [total]) VALUES (1, 1325282384, 110.0);
INSERT INTO [dbo].[order] ([customer_id], [created_at], [total]) VALUES (2, 1325334482, 33.0);
INSERT INTO [dbo].[order] ([customer_id], [created_at], [total]) VALUES (2, 1325502201, 40.0);

INSERT INTO [dbo].[order_with_null_fk] ([customer_id], [created_at], [total]) VALUES (1, 1325282384, 110.0);
INSERT INTO [dbo].[order_with_null_fk] ([customer_id], [created_at], [total]) VALUES (2, 1325334482, 33.0);
INSERT INTO [dbo].[order_with_null_fk] ([customer_id], [created_at], [total]) VALUES (2, 1325502201, 40.0);

INSERT INTO [dbo].[order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (1, 1, 1, 30.0);
INSERT INTO [dbo].[order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (1, 2, 2, 40.0);
INSERT INTO [dbo].[order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 4, 1, 10.0);
INSERT INTO [dbo].[order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 5, 1, 15.0);
INSERT INTO [dbo].[order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 3, 1, 8.0);
INSERT INTO [dbo].[order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (3, 2, 1, 40.0);

INSERT INTO [dbo].[order_item_with_null_fk] ([order_id], [item_id], [quantity], [subtotal]) VALUES (1, 1, 1, 30.0);
INSERT INTO [dbo].[order_item_with_null_fk] ([order_id], [item_id], [quantity], [subtotal]) VALUES (1, 2, 2, 40.0);
INSERT INTO [dbo].[order_item_with_null_fk] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 4, 1, 10.0);
INSERT INTO [dbo].[order_item_with_null_fk] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 5, 1, 15.0);
INSERT INTO [dbo].[order_item_with_null_fk] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 5, 1, 8.0);
INSERT INTO [dbo].[order_item_with_null_fk] ([order_id], [item_id], [quantity], [subtotal]) VALUES (3, 2, 1, 40.0);

INSERT INTO [dbo].[document] ([title], [content], [version]) VALUES ('Yii 2.0 guide', 'This is Yii 2.0 guide', 0);

SET IDENTITY_INSERT [dbo].[department] ON;
INSERT INTO [dbo].[department] (id, title) VALUES (1, 'IT');
INSERT INTO [dbo].[department] (id, title) VALUES (2, 'accounting');
SET IDENTITY_INSERT [dbo].[department] OFF;

INSERT INTO [dbo].[employee] (id, department_id, first_name, last_name) VALUES (1, 1, 'John', 'Doe');
INSERT INTO [dbo].[employee] (id, department_id, first_name, last_name) VALUES (1, 2, 'Ann', 'Smith');
INSERT INTO [dbo].[employee] (id, department_id, first_name, last_name) VALUES (2, 2, 'Will', 'Smith');

SET IDENTITY_INSERT [dbo].[dossier] ON;
INSERT INTO [dbo].[dossier] (id, department_id, employee_id, summary) VALUES (1, 1, 1, 'Excellent employee.');
INSERT INTO [dbo].[dossier] (id, department_id, employee_id, summary) VALUES (2, 2, 1, 'Brilliant employee.');
INSERT INTO [dbo].[dossier] (id, department_id, employee_id, summary) VALUES (3, 2, 2, 'Good employee.');
SET IDENTITY_INSERT [dbo].[dossier] OFF;

/* bit test, see https://github.com/yiisoft/yii2/issues/9006 */

IF OBJECT_ID('[dbo].[bit_values]', 'U') IS NOT NULL DROP TABLE [dbo].[bit_values];

CREATE TABLE [dbo].[bit_values] (
    [id] [int] IDENTITY NOT NULL,
    [val] [bit] NOT NULL,
    CONSTRAINT [PK_bit_values] PRIMARY KEY CLUSTERED (
        [id] ASC
    ) ON [PRIMARY]
);

INSERT INTO [dbo].[bit_values] ([val]) VALUES (0), (1);

CREATE TABLE [T_constraints_1]
(
    [C_id] INT NOT NULL IDENTITY PRIMARY KEY,
    [C_not_null] INT NOT NULL,
    [C_check] VARCHAR(255) NULL CHECK ([C_check] <> ''),
    [C_unique] INT NOT NULL,
    [C_default] INT NOT NULL DEFAULT 0,
    CONSTRAINT [CN_unique] UNIQUE ([C_unique])
);

CREATE TABLE [T_constraints_2]
(
    [C_id_1] INT NOT NULL,
    [C_id_2] INT NOT NULL,
    [C_index_1] INT NULL,
    [C_index_2_1] INT NULL,
    [C_index_2_2] INT NULL,
    CONSTRAINT [CN_constraints_2_multi] UNIQUE ([C_index_2_1], [C_index_2_2]),
    CONSTRAINT [CN_pk] PRIMARY KEY ([C_id_1], [C_id_2])
);

CREATE INDEX [CN_constraints_2_single] ON [T_constraints_2] ([C_index_1]);

CREATE TABLE [T_constraints_3]
(
    [C_id] INT NOT NULL,
    [C_fk_id_1] INT NOT NULL,
    [C_fk_id_2] INT NOT NULL,
    CONSTRAINT [CN_constraints_3] FOREIGN KEY ([C_fk_id_1], [C_fk_id_2]) REFERENCES [T_constraints_2] ([C_id_1], [C_id_2]) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE [T_constraints_4]
(
    [C_id] INT NOT NULL IDENTITY  PRIMARY KEY,
    [C_col_1] INT NULL,
    [C_col_2] INT NOT NULL,
    CONSTRAINT [CN_constraints_4] UNIQUE ([C_col_1], [C_col_2])
);

CREATE TABLE [T_upsert]
(
    [id] INT NOT NULL IDENTITY PRIMARY KEY,
    [ts] INT NULL,
    [email] VARCHAR(128) NOT NULL UNIQUE,
    [recovery_email] VARCHAR(128) NULL,
    [address] TEXT NULL,
    [status] TINYINT NOT NULL DEFAULT 0,
    [orders] INT NOT NULL DEFAULT 0,
    [profile_id] INT NULL,
    UNIQUE ([email], [recovery_email])
);

CREATE TABLE [T_upsert_1]
(
    [a] INT NOT NULL,
    UNIQUE ([a])
);

CREATE TABLE [dbo].[table.with.special.characters] (
    [id] [int]
);

IF OBJECT_ID('[validator_main]', 'U') IS NOT NULL DROP TABLE [dbo].[validator_main];
IF OBJECT_ID('[validator_ref]', 'U') IS NOT NULL DROP TABLE [dbo].[validator_ref];

create table [dbo].[validator_main]
(
    id     int identity
        constraint validator_main_pk
            primary key nonclustered,
    field1 nvarchar(255)
);

create table [dbo].[validator_ref]
(
    id      int identity
        constraint validator_ref_pk
            primary key nonclustered,
    a_field nvarchar(255),
    ref     int
);

INSERT INTO [validator_main] (field1) VALUES ('just a string1');
INSERT INTO [validator_main] (field1) VALUES ('just a string2');
INSERT INTO [validator_main] (field1) VALUES ('just a string3');
INSERT INTO [validator_main] (field1) VALUES ('just a string4');
INSERT INTO [validator_ref] (a_field, ref) VALUES ('ref_to_2', 2);
INSERT INTO [validator_ref] (a_field, ref) VALUES ('ref_to_2', 2);
INSERT INTO [validator_ref] (a_field, ref) VALUES ('ref_to_3', 3);
INSERT INTO [validator_ref] (a_field, ref) VALUES ('ref_to_4', 4);
INSERT INTO [validator_ref] (a_field, ref) VALUES ('ref_to_4', 4);
INSERT INTO [validator_ref] (a_field, ref) VALUES ('ref_to_5', 5);

CREATE TABLE [dbo].[stranger 'table] (
    [id] [int],
    [stranger 'field] [varchar] (32)
);

CREATE TABLE [dbo].[test_trigger] (
  [id] [int] IDENTITY NOT NULL,
  [stringcol] [varchar](32) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE [dbo].[test_trigger_alert] (
  [id] [int] IDENTITY NOT NULL,
  [stringcol] [varchar](32) DEFAULT NULL,
  PRIMARY KEY (id)
);

CREATE TABLE [dbo].[foo1] (
  [id] [int] IDENTITY NOT NULL,
  [bar] [varchar](32),
  PRIMARY KEY (id)
);

CREATE TABLE [T_upsert_varbinary]
(
    [id] INT NOT NULL,
    [blob_col] [varbinary](MAX),
    UNIQUE ([id])
);
