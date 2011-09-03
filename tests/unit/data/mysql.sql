/**
 * This is the database schema for testing MySQL support of yii Active Record.
 * To test this feature, you need to create a database named 'yii' on 'localhost'
 * and create an account 'test/test' which owns this test database.
 */

CREATE TABLE users
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
) TYPE=INNODB;

INSERT INTO users (username, password, email) VALUES ('user1','pass1','email1');
INSERT INTO users (username, password, email) VALUES ('user2','pass2','email2');
INSERT INTO users (username, password, email) VALUES ('user3','pass3','email3');

CREATE TABLE profiles
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	first_name VARCHAR(128) NOT NULL,
	last_name VARCHAR(128) NOT NULL,
	user_id INTEGER NOT NULL,
	CONSTRAINT FK_profile_user FOREIGN KEY (user_id)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
) TYPE=INNODB;

INSERT INTO profiles (first_name, last_name, user_id) VALUES ('first 1','last 1',1);
INSERT INTO profiles (first_name, last_name, user_id) VALUES ('first 2','last 2',2);

CREATE TABLE posts
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	title VARCHAR(128) NOT NULL,
	create_time TIMESTAMP NOT NULL,
	author_id INTEGER NOT NULL,
	content TEXT,
	CONSTRAINT FK_post_author FOREIGN KEY (author_id)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
) TYPE=INNODB;

INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 1','2000-01-01',1,'content 1');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 2','2000-01-02',2,'content 2');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 3','2000-01-03',2,'content 3');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 4','2000-01-04',2,'content 4');
INSERT INTO posts (title, create_time, author_id, content) VALUES ('post 5','2000-01-05',3,'content 5');

CREATE TABLE comments
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	content TEXT NOT NULL,
	post_id INTEGER NOT NULL,
	author_id INTEGER NOT NULL,
	CONSTRAINT FK_post_comment FOREIGN KEY (post_id)
		REFERENCES posts (id) ON DELETE CASCADE ON UPDATE RESTRICT,
	CONSTRAINT FK_user_comment FOREIGN KEY (author_id)
		REFERENCES users (id) ON DELETE CASCADE ON UPDATE RESTRICT
) TYPE=INNODB;

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
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(128) NOT NULL,
	parent_id INTEGER,
	CONSTRAINT FK_category_category FOREIGN KEY (parent_id)
		REFERENCES categories (id) ON DELETE CASCADE ON UPDATE RESTRICT
) TYPE=INNODB;

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
) TYPE=INNODB;

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
) TYPE=INNODB;

INSERT INTO orders (key1,key2,name) VALUES (1,2,'order 12');
INSERT INTO orders (key1,key2,name) VALUES (1,3,'order 13');
INSERT INTO orders (key1,key2,name) VALUES (2,1,'order 21');
INSERT INTO orders (key1,key2,name) VALUES (2,2,'order 22');

CREATE TABLE items
(
	id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(128),
	col1 INTEGER NOT NULL,
	col2 INTEGER NOT NULL,
	CONSTRAINT FK_order_item FOREIGN KEY (col1,col2)
		REFERENCES orders (key1,key2) ON DELETE CASCADE ON UPDATE RESTRICT
) TYPE=INNODB;

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
	time TIMESTAMP DEFAULT '2002-01-01',
	bool_col BOOL NOT NULL,
	bool_col2 BOOLEAN DEFAULT 1
) TYPE=INNODB;