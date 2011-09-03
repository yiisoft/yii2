CREATE TABLE users
(
	id INTEGER NOT NULL PRIMARY KEY,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);

INSERT INTO users(id,username,password,email) VALUES (1,'user1','pass1','email1');
INSERT INTO users(id,username,password,email) VALUES (2,'user2','pass2','email2');
INSERT INTO users(id,username,password,email) VALUES (3,'user3','pass3','email3');
INSERT INTO users(id,username,password,email) VALUES (4,'user4','pass4','email4');

CREATE TABLE groups
(
	id INTEGER NOT NULL PRIMARY KEY,
	name VARCHAR(128) NOT NULL
);

INSERT INTO groups(id,name) VALUES (1,'group1');
INSERT INTO groups(id,name) VALUES (2,'group2');
INSERT INTO groups(id,name) VALUES (3,'group3');
INSERT INTO groups(id,name) VALUES (4,'group4');
INSERT INTO groups(id,name) VALUES (5,'group5');
INSERT INTO groups(id,name) VALUES (6,'group6');

CREATE TABLE groups_descriptions
(
	group_id INTEGER NOT NULL PRIMARY KEY,
	name VARCHAR(128) NOT NULL
);

INSERT INTO groups_descriptions(group_id,name) VALUES (1,'room1');
INSERT INTO groups_descriptions(group_id,name) VALUES (2,'room2');
INSERT INTO groups_descriptions(group_id,name) VALUES (3,'room3');
INSERT INTO groups_descriptions(group_id,name) VALUES (4,'room4');

CREATE TABLE roles
(
	user_id INTEGER NOT NULL,
	group_id INTEGER NOT NULL,
	name VARCHAR(128) NOT NULL,
	PRIMARY KEY(user_id,group_id)
);

INSERT INTO roles(user_id,group_id,name) VALUES (1,1,'dev');
INSERT INTO roles(user_id,group_id,name) VALUES (1,2,'user');
INSERT INTO roles(user_id,group_id,name) VALUES (2,1,'dev');
INSERT INTO roles(user_id,group_id,name) VALUES (2,3,'user');

CREATE TABLE mentorships
(
	teacher_id INTEGER NOT NULL,
	student_id INTEGER NOT NULL,
	progress VARCHAR(128) NOT NULL,
	PRIMARY KEY(teacher_id,student_id)
);

INSERT INTO mentorships(teacher_id,student_id,progress) VALUES (1,3,'good');
INSERT INTO mentorships(teacher_id,student_id,progress) VALUES (2,4,'average');

CREATE TABLE profiles
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	first_name VARCHAR(128) NOT NULL,
	last_name VARCHAR(128) NOT NULL,
	user_id INTEGER NOT NULL,
	CONSTRAINT FK_profile_user FOREIGN KEY (user_id)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO profiles (first_name, last_name, user_id) VALUES ('first 1','last 1',1);
INSERT INTO profiles (first_name, last_name, user_id) VALUES ('first 2','last 2',2);

CREATE TABLE posts
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	create_time TIMESTAMP NOT NULL,
	author_id INTEGER NOT NULL,
	content TEXT,
	CONSTRAINT FK_post_author FOREIGN KEY (author_id)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 1',100000,1,'content 1');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 2',100001,2,'content 2');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 3',100002,2,'content 3');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 4',100003,2,'content 4');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 5',100004,3,'content 5');


CREATE TABLE posts_nofk
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	create_time TIMESTAMP NOT NULL,
	author_id INTEGER NOT NULL,
	content TEXT
);

INSERT INTO posts_nofk (title, create_time, author_id, content) VALUES ('post 1',100000,1,'content 1');
INSERT INTO posts_nofk (title, create_time, author_id, content) VALUES ('post 2',100001,2,'content 2');
INSERT INTO posts_nofk (title, create_time, author_id, content) VALUES ('post 3',100002,2,'content 3');
INSERT INTO posts_nofk (title, create_time, author_id, content) VALUES ('post 4',100003,2,'content 4');
INSERT INTO posts_nofk (title, create_time, author_id, content) VALUES ('post 5',100004,3,'content 5');


CREATE TABLE comments
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	content TEXT NOT NULL,
	post_id INTEGER NOT NULL,
	author_id INTEGER NOT NULL,
	CONSTRAINT FK_post_comment FOREIGN KEY (post_id)
		REFERENCES posts (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_user_comment FOREIGN KEY (author_id)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO comments (content, post_id, author_id) VALUES ('comment 1',1, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 2',1, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 3',1, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 4',2, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 5',2, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 6',3, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 7',3, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 8',3, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 9',3, 2);
INSERT INTO comments (content, post_id, author_id) VALUES ('comment 10',5, 3);

CREATE TABLE categories
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(128) NOT NULL,
	parent_id INTEGER,
	CONSTRAINT FK_category_category FOREIGN KEY (parent_id)
		REFERENCES categories (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO categories (name, parent_id) VALUES ('cat 1',NULL);
INSERT INTO categories (name, parent_id) VALUES ('cat 2',NULL);
INSERT INTO categories (name, parent_id) VALUES ('cat 3',NULL);
INSERT INTO categories (name, parent_id) VALUES ('cat 4',1);
INSERT INTO categories (name, parent_id) VALUES ('cat 5',1);
INSERT INTO categories (name, parent_id) VALUES ('cat 6',5);
INSERT INTO categories (name, parent_id) VALUES ('cat 7',5);

CREATE TABLE post_category
(
	category_id INTEGER NOT NULL,
	post_id INTEGER NOT NULL,
	PRIMARY KEY (category_id, post_id),
	CONSTRAINT FK_post_category_post FOREIGN KEY (post_id)
		REFERENCES posts (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_post_category_category FOREIGN KEY (category_id)
		REFERENCES categories (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO post_category (category_id, post_id) VALUES (1,1);
INSERT INTO post_category (category_id, post_id) VALUES (2,1);
INSERT INTO post_category (category_id, post_id) VALUES (3,1);
INSERT INTO post_category (category_id, post_id) VALUES (4,2);
INSERT INTO post_category (category_id, post_id) VALUES (1,2);
INSERT INTO post_category (category_id, post_id) VALUES (1,3);

CREATE TABLE orders
(
	key1 INTEGER NOT NULL,
	key2 INTEGER NOT NULL,
	name VARCHAR(128),
	PRIMARY KEY (key1, key2)
);

INSERT INTO orders (key1,key2,name) VALUES (1,2,'order 12');
INSERT INTO orders (key1,key2,name) VALUES (1,3,'order 13');
INSERT INTO orders (key1,key2,name) VALUES (2,1,'order 21');
INSERT INTO orders (key1,key2,name) VALUES (2,2,'order 22');

CREATE TABLE items
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	name VARCHAR(128),
	col1 INTEGER NOT NULL,
	col2 INTEGER NOT NULL,
	CONSTRAINT FK_order_item FOREIGN KEY (col1,col2)
		REFERENCES orders (key1,key2) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO items (name,col1,col2) VALUES ('item 1',1,2);
INSERT INTO items (name,col1,col2) VALUES ('item 2',1,2);
INSERT INTO items (name,col1,col2) VALUES ('item 3',1,3);
INSERT INTO items (name,col1,col2) VALUES ('item 4',2,2);
INSERT INTO items (name,col1,col2) VALUES ('item 5',2,2);

CREATE TABLE types
(
	int_col INT NOT NULL,
	int_col2 INTEGER DEFAULT 1,
	char_col CHAR(100) NOT NULL,
	char_col2 VARCHAR(100) DEFAULT 'something',
	char_col3 TEXT,
	float_col REAL(4,3) NOT NULL,
	float_col2 DOUBLE DEFAULT 1.23,
	blob_col BLOB,
	numeric_col NUMERIC(5,2) DEFAULT 33.22,
	time TIMESTAMP DEFAULT 123,
	bool_col BOOL NOT NULL,
	bool_col2 BOOLEAN DEFAULT 1,
	null_col INTEGER DEFAULT NULL
);

CREATE TABLE Content
(
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	class VARCHAR(128),
	parentID INTEGER NOT NULL,
	ownerID INTEGER NOT NULL,
	title VARCHAR(100),
	CONSTRAINT FK_content_user FOREIGN KEY (ownerID)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
	CONSTRAINT FK_content_parent FOREIGN KEY (parentID)
		REFERENCES Content (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO Content (class,parentID,ownerID,title) VALUES ('Article',-1,1,'article 1');
INSERT INTO Content (class,parentID,ownerID,title) VALUES ('Article',-1,2,'article 2');
INSERT INTO Content (class,parentID,ownerID,title) VALUES ('Comment',1,1,'comment 1');
INSERT INTO Content (class,parentID,ownerID,title) VALUES ('Article',-1,2,'article 3');
INSERT INTO Content (class,parentID,ownerID,title) VALUES ('Comment',4,2,'comment 2');
INSERT INTO Content (class,parentID,ownerID,title) VALUES ('Comment',4,1,'comment 3');

CREATE TABLE Article
(
	id INTEGER NOT NULL PRIMARY KEY,
	authorID INTEGER NOT NULL,
	body TEXT,
	CONSTRAINT FK_article_content FOREIGN KEY (id)
		REFERENCES Content (id) ON DELETE CASCADE ON UPDATE RESTRICT
	CONSTRAINT FK_article_author FOREIGN KEY (authorID)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO Article (id,authorID,body) VALUES (1,1,'content for article 1');
INSERT INTO Article (id,authorID,body) VALUES (2,2,'content for article 2');
INSERT INTO Article (id,authorID,body) VALUES (4,1,'content for article 3');

CREATE TABLE Comment
(
	id INTEGER NOT NULL PRIMARY KEY,
	authorID INTEGER NOT NULL,
	body TEXT,
	CONSTRAINT FK_comment_content FOREIGN KEY (id)
		REFERENCES Content (id) ON DELETE CASCADE ON UPDATE RESTRICT
	CONSTRAINT FK_article_author FOREIGN KEY (authorID)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO Comment (id,authorID,body) VALUES (3,1,'content for comment 1');
INSERT INTO Comment (id,authorID,body) VALUES (5,1,'content for comment 2');
INSERT INTO Comment (id,authorID,body) VALUES (6,1,'content for comment 3');

