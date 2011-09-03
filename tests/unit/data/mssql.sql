SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[categories]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[categories](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](128) NOT NULL,
	[parent_id] [int] NULL,
 CONSTRAINT [PK_categories] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[orders]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[orders](
	[key1] [int] NOT NULL,
	[key2] [int] NOT NULL,
	[name] [varchar](128) NOT NULL,
 CONSTRAINT [PK_orders] PRIMARY KEY CLUSTERED 
(
	[key1] ASC,
	[key2] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[types]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[types](
	[int_col] [int] NOT NULL,
	[int_col2] [int] NULL CONSTRAINT [DF_types_int_col2]  DEFAULT (1),
	[char_col] [char](100) NOT NULL,
	[char_col2] [varchar](100) NULL CONSTRAINT [DF_types_char_col2]  DEFAULT ('something'),
	[char_col3] [text] NULL,
	[float_col] [real] NOT NULL,
	[float_col2] [float] NULL CONSTRAINT [DF_types_float_col2]  DEFAULT (1.23),
	[blob_col] [image] NULL,
	[numeric_col] [numeric](5, 2) NULL CONSTRAINT [DF_types_numeric_col]  DEFAULT (33.22),
	[time] [datetime] NULL CONSTRAINT [DF_types_time]  DEFAULT ('2002-01-01 00:00:00'),
	[bool_col] [bit] NOT NULL,
	[bool_col2] [bit] NOT NULL CONSTRAINT [DF_types_bool_col2]  DEFAULT (1)
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[users]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[username] [varchar](128) NOT NULL,
	[password] [varchar](128) NOT NULL,
	[email] [varchar](128) NOT NULL,
 CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[post_category]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[post_category](
	[category_id] [int] NOT NULL,
	[post_id] [int] NOT NULL,
 CONSTRAINT [PK_post_category] PRIMARY KEY CLUSTERED 
(
	[category_id] ASC,
	[post_id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[items]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[items](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](128) NULL,
	[col1] [int] NOT NULL,
	[col2] [int] NOT NULL,
 CONSTRAINT [PK_items] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[comments]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[comments](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[content] [text] NOT NULL,
	[post_id] [int] NOT NULL,
	[author_id] [int] NOT NULL,
 CONSTRAINT [PK_comments] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[posts]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[posts](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[title] [varchar](128) NOT NULL,
	[create_time] [datetime] NOT NULL,
	[author_id] [int] NOT NULL,
	[content] [text] NULL,
 CONSTRAINT [PK_posts] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[profiles]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
BEGIN
CREATE TABLE [dbo].[profiles](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[first_name] [varchar](128) NOT NULL,
	[last_name] [varchar](128) NOT NULL,
	[user_id] [int] NOT NULL,
 CONSTRAINT [PK_profiles] PRIMARY KEY CLUSTERED 
(
	[id] ASC
) ON [PRIMARY]
) ON [PRIMARY]
END
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_categories_categories]') AND type = 'F')
ALTER TABLE [dbo].[categories]  WITH CHECK ADD  CONSTRAINT [FK_categories_categories] FOREIGN KEY([parent_id])
REFERENCES [dbo].[categories] ([id])
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_post_category_categories]') AND type = 'F')
ALTER TABLE [dbo].[post_category]  WITH CHECK ADD  CONSTRAINT [FK_post_category_categories] FOREIGN KEY([category_id])
REFERENCES [dbo].[categories] ([id])
ON DELETE CASCADE
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_post_category_posts]') AND type = 'F')
ALTER TABLE [dbo].[post_category]  WITH NOCHECK ADD  CONSTRAINT [FK_post_category_posts] FOREIGN KEY([post_id])
REFERENCES [dbo].[posts] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[post_category] CHECK CONSTRAINT [FK_post_category_posts]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_items_orders]') AND type = 'F')
ALTER TABLE [dbo].[items]  WITH CHECK ADD  CONSTRAINT [FK_items_orders] FOREIGN KEY([col1], [col2])
REFERENCES [dbo].[orders] ([key1], [key2])
ON DELETE CASCADE
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_comments_users]') AND type = 'F')
ALTER TABLE [dbo].[comments]  WITH NOCHECK ADD  CONSTRAINT [FK_comments_users] FOREIGN KEY([author_id])
REFERENCES [dbo].[users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[comments] CHECK CONSTRAINT [FK_comments_users]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_post_comment]') AND type = 'F')
ALTER TABLE [dbo].[comments]  WITH NOCHECK ADD  CONSTRAINT [FK_post_comment] FOREIGN KEY([post_id])
REFERENCES [dbo].[posts] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[comments] CHECK CONSTRAINT [FK_post_comment]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_posts_users]') AND type = 'F')
ALTER TABLE [dbo].[posts]  WITH NOCHECK ADD  CONSTRAINT [FK_posts_users] FOREIGN KEY([author_id])
REFERENCES [dbo].[users] ([id])
GO
ALTER TABLE [dbo].[posts] CHECK CONSTRAINT [FK_posts_users]
GO
IF NOT EXISTS (SELECT * FROM dbo.sysobjects WHERE id = OBJECT_ID(N'[dbo].[FK_profile_user]') AND type = 'F')
ALTER TABLE [dbo].[profiles]  WITH NOCHECK ADD  CONSTRAINT [FK_profile_user] FOREIGN KEY([user_id])
REFERENCES [dbo].[users] ([id])
ON DELETE CASCADE
GO
ALTER TABLE [dbo].[profiles] CHECK CONSTRAINT [FK_profile_user]

INSERT INTO users (username, password, email) VALUES ('user1','pass1','email1')
GO
INSERT INTO users (username, password, email) VALUES ('user2','pass2','email2')
GO
INSERT INTO users (username, password, email) VALUES ('user3','pass3','email3')
GO

INSERT INTO profiles (first_name, last_name, user_id) VALUES ('first 1','last 1',1)
GO
INSERT INTO profiles (first_name, last_name, user_id) VALUES ('first 2','last 2',2)
GO

INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 1','2000-01-01',1,'content 1')
GO
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 2','2000-01-02',2,'content 2')
GO
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 3','2000-01-03',2,'content 3')
GO
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 4','2000-01-04',2,'content 4')
GO
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 5','2000-01-05',3,'content 5')
GO

INSERT INTO comments (content, post_id, author_id) VALUES ('comment 1',1, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 2',1, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 3',1, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 4',2, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 5',2, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 6',3, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 7',3, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 8',3, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 9',3, 2)
GO
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 10',5, 3)
GO

INSERT INTO categories (name, parent_id) VALUES ('cat 1',NULL)
GO
INSERT INTO categories (name, parent_id) VALUES ('cat 2',NULL)
GO
INSERT INTO categories (name, parent_id) VALUES ('cat 3',NULL)
GO
INSERT INTO categories (name, parent_id) VALUES ('cat 4',1)
GO
INSERT INTO categories (name, parent_id) VALUES ('cat 5',1)
GO
INSERT INTO categories (name, parent_id) VALUES ('cat 6',5)
GO
INSERT INTO categories (name, parent_id) VALUES ('cat 7',5)
GO

INSERT INTO post_category (category_id, post_id) VALUES (1,1)
GO
INSERT INTO post_category (category_id, post_id) VALUES (2,1)
GO
INSERT INTO post_category (category_id, post_id) VALUES (3,1)
GO
INSERT INTO post_category (category_id, post_id) VALUES (4,2)
GO
INSERT INTO post_category (category_id, post_id) VALUES (1,2)
GO
INSERT INTO post_category (category_id, post_id) VALUES (1,3)
GO


INSERT INTO orders (key1,key2,name) VALUES (1,2,'order 12')
GO
INSERT INTO orders (key1,key2,name) VALUES (1,3,'order 13')
GO
INSERT INTO orders (key1,key2,name) VALUES (2,1,'order 21')
GO
INSERT INTO orders (key1,key2,name) VALUES (2,2,'order 22')
GO


INSERT INTO items (name,col1,col2) VALUES ('item 1',1,2)
GO
INSERT INTO items (name,col1,col2) VALUES ('item 2',1,2)
GO
INSERT INTO items (name,col1,col2) VALUES ('item 3',1,3)
GO
INSERT INTO items (name,col1,col2) VALUES ('item 4',2,2)
GO
INSERT INTO items (name,col1,col2) VALUES ('item 5',2,2)
GO
