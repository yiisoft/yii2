/**
 * This is the database schema for testing PostgreSQL support of yii Active Record.
 * To test this feature, you need to create a database named 'yii' on 'localhost'
 * and create an account 'test/test' which owns this test database.
 */
CREATE SCHEMA test;

CREATE TABLE test.users
(
	id SERIAL NOT NULL PRIMARY KEY,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);

INSERT INTO test.users (username, password, email) VALUES ('user1','pass1','email1');
INSERT INTO test.users (username, password, email) VALUES ('user2','pass2','email2');
INSERT INTO test.users (username, password, email) VALUES ('user3','pass3','email3');

CREATE TABLE test.user_friends
(
	id INTEGER NOT NULL,
	friend INTEGER NOT NULL,
	PRIMARY KEY (id, friend),
	CONSTRAINT FK_user_id FOREIGN KEY (id)
		REFERENCES test.users (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_friend_id FOREIGN KEY (friend)
		REFERENCES test.users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.user_friends VALUES (1,2);
INSERT INTO test.user_friends VALUES (1,3);
INSERT INTO test.user_friends VALUES (2,3);

CREATE TABLE test.profiles
(
	id SERIAL NOT NULL PRIMARY KEY,
	first_name VARCHAR(128) NOT NULL,
	last_name VARCHAR(128) NOT NULL,
	user_id INTEGER NOT NULL,
	CONSTRAINT FK_profile_user FOREIGN KEY (user_id)
		REFERENCES test.users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.profiles (first_name, last_name, user_id) VALUES ('first 1','last 1',1);
INSERT INTO test.profiles (first_name, last_name, user_id) VALUES ('first 2','last 2',2);

CREATE TABLE test.posts
(
	id SERIAL NOT NULL PRIMARY KEY,
	title VARCHAR(128) NOT NULL,
	create_time TIMESTAMP NOT NULL,
	author_id INTEGER NOT NULL,
	content TEXT,
	CONSTRAINT FK_post_author FOREIGN KEY (author_id)
		REFERENCES test.users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.posts (title, create_time, author_id, content) VALUES ('post 1',TIMESTAMP '2004-10-19 10:23:54',1,'content 1');
INSERT INTO test.posts (title, create_time, author_id, content) VALUES ('post 2',TIMESTAMP '2004-10-19 10:23:54',2,'content 2');
INSERT INTO test.posts (title, create_time, author_id, content) VALUES ('post 3',TIMESTAMP '2004-10-19 10:23:54',2,'content 3');
INSERT INTO test.posts (title, create_time, author_id, content) VALUES ('post 4',TIMESTAMP '2004-10-19 10:23:54',2,'content 4');
INSERT INTO test.posts (title, create_time, author_id, content) VALUES ('post 5',TIMESTAMP '2004-10-19 10:23:54',3,'content 5');

CREATE TABLE test.comments
(
	id SERIAL NOT NULL PRIMARY KEY,
	content TEXT NOT NULL,
	post_id INTEGER NOT NULL,
	author_id INTEGER NOT NULL,
	CONSTRAINT FK_post_comment FOREIGN KEY (post_id)
		REFERENCES test.posts (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_user_comment FOREIGN KEY (author_id)
		REFERENCES test.users (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 1',1, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 2',1, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 3',1, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 4',2, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 5',2, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 6',3, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 7',3, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 8',3, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 9',3, 2);
INSERT INTO test.comments (content, post_id, author_id) VALUES ('comment 10',5, 3);

CREATE TABLE test.categories
(
	id SERIAL NOT NULL PRIMARY KEY,
	name VARCHAR(128) NOT NULL,
	parent_id INTEGER,
	CONSTRAINT FK_category_category FOREIGN KEY (parent_id)
		REFERENCES test.categories (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.categories (name, parent_id) VALUES ('cat 1',NULL);
INSERT INTO test.categories (name, parent_id) VALUES ('cat 2',NULL);
INSERT INTO test.categories (name, parent_id) VALUES ('cat 3',NULL);
INSERT INTO test.categories (name, parent_id) VALUES ('cat 4',1);
INSERT INTO test.categories (name, parent_id) VALUES ('cat 5',1);
INSERT INTO test.categories (name, parent_id) VALUES ('cat 6',5);
INSERT INTO test.categories (name, parent_id) VALUES ('cat 7',5);

CREATE TABLE test.post_category
(
	category_id INTEGER NOT NULL,
	post_id INTEGER NOT NULL,
	PRIMARY KEY (category_id, post_id),
	CONSTRAINT FK_post_category_post FOREIGN KEY (post_id)
		REFERENCES test.posts (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_post_category_category FOREIGN KEY (category_id)
		REFERENCES test.categories (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.post_category (category_id, post_id) VALUES (1,1);
INSERT INTO test.post_category (category_id, post_id) VALUES (2,1);
INSERT INTO test.post_category (category_id, post_id) VALUES (3,1);
INSERT INTO test.post_category (category_id, post_id) VALUES (4,2);
INSERT INTO test.post_category (category_id, post_id) VALUES (1,2);
INSERT INTO test.post_category (category_id, post_id) VALUES (1,3);

CREATE TABLE test.orders
(
	key1 INTEGER NOT NULL,
	key2 INTEGER NOT NULL,
	name VARCHAR(128),
	PRIMARY KEY (key1, key2)
);

INSERT INTO test.orders (key1,key2,name) VALUES (1,2,'order 12');
INSERT INTO test.orders (key1,key2,name) VALUES (1,3,'order 13');
INSERT INTO test.orders (key1,key2,name) VALUES (2,1,'order 21');
INSERT INTO test.orders (key1,key2,name) VALUES (2,2,'order 22');

CREATE TABLE test.items
(
	id SERIAL NOT NULL PRIMARY KEY,
	name VARCHAR(128),
	col1 INTEGER NOT NULL,
	col2 INTEGER NOT NULL,
	CONSTRAINT FK_order_item FOREIGN KEY (col1,col2)
		REFERENCES test.orders (key1,key2) ON DELETE CASCADE ON UPDATE RESTRICT
);

INSERT INTO test.items (name,col1,col2) VALUES ('item 1',1,2);
INSERT INTO test.items (name,col1,col2) VALUES ('item 2',1,2);
INSERT INTO test.items (name,col1,col2) VALUES ('item 3',1,3);
INSERT INTO test.items (name,col1,col2) VALUES ('item 4',2,2);
INSERT INTO test.items (name,col1,col2) VALUES ('item 5',2,2);

CREATE TABLE public.yii_types
(
	int_col INT NOT NULL,
	int_col2 INTEGER DEFAULT 1,
	char_col CHAR(100) NOT NULL,
	char_col2 VARCHAR(100) DEFAULT 'something',
	char_col3 TEXT,
	numeric_col NUMERIC(4,3) NOT NULL,
	real_col REAL DEFAULT 1.23,
	blob_col BYTEA,
	time TIMESTAMP,
	bool_col BOOL NOT NULL,
	bool_col2 BOOLEAN DEFAULT TRUE
);