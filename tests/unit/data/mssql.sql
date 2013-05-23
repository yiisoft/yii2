IF OBJECT_ID('[dbo].[tbl_order_item]', 'U') IS NOT NULL DROP TABLE [dbo].[tbl_order_item];
IF OBJECT_ID('[dbo].[tbl_item]', 'U') IS NOT NULL DROP TABLE [dbo].[tbl_item];
IF OBJECT_ID('[dbo].[tbl_order]', 'U') IS NOT NULL DROP TABLE [dbo].[tbl_order];
IF OBJECT_ID('[dbo].[tbl_category]', 'U') IS NOT NULL DROP TABLE [dbo].[tbl_category];
IF OBJECT_ID('[dbo].[tbl_customer]', 'U') IS NOT NULL DROP TABLE [dbo].[tbl_customer];
IF OBJECT_ID('[dbo].[tbl_type]', 'U') IS NOT NULL DROP TABLE [dbo].[tbl_type];

CREATE TABLE [dbo].[tbl_customer] (
	[id] [int] IDENTITY(1,1) NOT NULL,
	[email] [varchar](128) NOT NULL,
	[name] [varchar](128) NOT NULL,
	[address] [text],
	[status] [int] DEFAULT 0,
	CONSTRAINT [PK_customer] PRIMARY KEY CLUSTERED (
		[id] ASC
	) ON [PRIMARY]
);

CREATE TABLE [dbo].[tbl_category] (
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](128) NOT NULL,
	CONSTRAINT [PK_category] PRIMARY KEY CLUSTERED (
		[id] ASC
	) ON [PRIMARY]
);

CREATE TABLE [dbo].[tbl_item] (
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](128) NOT NULL,
	[category_id] [int] NOT NULL,
	CONSTRAINT [PK_item] PRIMARY KEY CLUSTERED (
		[id] ASC
	) ON [PRIMARY]
);

CREATE TABLE [dbo].[tbl_order] (
	[id] [int] IDENTITY(1,1) NOT NULL,
	[customer_id] [int] NOT NULL,
	[create_time] [int] NOT NULL,
	[total] [decimal](10,0) NOT NULL,
	CONSTRAINT [PK_order] PRIMARY KEY CLUSTERED (
		[id] ASC
	) ON [PRIMARY]
);

CREATE TABLE [dbo].[tbl_order_item] (
	[order_id] [int] NOT NULL,
	[item_id] [int] NOT NULL,
	[quantity] [int] NOT NULL,
	[subtotal] [decimal](10,0) NOT NULL,
	CONSTRAINT [PK_order_item] PRIMARY KEY CLUSTERED (
		[order_id] ASC,
		[item_id] ASC
	) ON [PRIMARY]
);

CREATE TABLE [dbo].[tbl_type] (
	[int_col] [int] NOT NULL,
	[int_col2] [int] DEFAULT '1',
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

INSERT INTO [dbo].[tbl_customer] ([email], [name], [address], [status]) VALUES ('user1@example.com', 'user1', 'address1', 1);
INSERT INTO [dbo].[tbl_customer] ([email], [name], [address], [status]) VALUES ('user2@example.com', 'user2', 'address2', 1);
INSERT INTO [dbo].[tbl_customer] ([email], [name], [address], [status]) VALUES ('user3@example.com', 'user3', 'address3', 2);

INSERT INTO [dbo].[tbl_category] ([name]) VALUES ('Books');
INSERT INTO [dbo].[tbl_category] ([name]) VALUES ('Movies');

INSERT INTO [dbo].[tbl_item] ([name], [category_id]) VALUES ('Agile Web Application Development with Yii1.1 and PHP5', 1);
INSERT INTO [dbo].[tbl_item] ([name], [category_id]) VALUES ('Yii 1.1 Application Development Cookbook', 1);
INSERT INTO [dbo].[tbl_item] ([name], [category_id]) VALUES ('Ice Age', 2);
INSERT INTO [dbo].[tbl_item] ([name], [category_id]) VALUES ('Toy Story', 2);
INSERT INTO [dbo].[tbl_item] ([name], [category_id]) VALUES ('Cars', 2);

INSERT INTO [dbo].[tbl_order] ([customer_id], [create_time], [total]) VALUES (1, 1325282384, 110.0);
INSERT INTO [dbo].[tbl_order] ([customer_id], [create_time], [total]) VALUES (2, 1325334482, 33.0);
INSERT INTO [dbo].[tbl_order] ([customer_id], [create_time], [total]) VALUES (2, 1325502201, 40.0);

INSERT INTO [dbo].[tbl_order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (1, 1, 1, 30.0);
INSERT INTO [dbo].[tbl_order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (1, 2, 2, 40.0);
INSERT INTO [dbo].[tbl_order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 4, 1, 10.0);
INSERT INTO [dbo].[tbl_order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 5, 1, 15.0);
INSERT INTO [dbo].[tbl_order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (2, 3, 1, 8.0);
INSERT INTO [dbo].[tbl_order_item] ([order_id], [item_id], [quantity], [subtotal]) VALUES (3, 2, 1, 40.0);
