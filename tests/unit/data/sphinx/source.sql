/**
 * This is the MySQL database schema for creation of the test Sphinx index sources.
 */

DROP TABLE IF EXISTS yii2_test_article;
DROP TABLE IF EXISTS yii2_test_item;
DROP TABLE IF EXISTS yii2_test_tag;
DROP TABLE IF EXISTS yii2_test_article_tag;

CREATE TABLE IF NOT EXISTS `yii2_test_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `yii2_test_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3;

CREATE TABLE IF NOT EXISTS `yii2_test_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5;

CREATE TABLE IF NOT EXISTS `yii2_test_article_tag` (
  `article_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `yii2_test_article` (`id`, `title`, `content`, `author_id`, `create_date`) VALUES
(1, 'About cats', 'This article is about cats', 1, '2013-10-23 00:00:00'),
(2, 'About dogs', 'This article is about dogs', 2, '2013-11-15 00:00:00');

INSERT INTO `yii2_test_item` (`id`, `name`, `description`, `category_id`, `price`) VALUES
(1, 'pencil', 'Simple pencil', 1, 2.5),
(2, 'table', 'Wooden table', 2, 100);

INSERT INTO `yii2_test_tag` (`id`, `name`) VALUES
(1, 'tag1'),
(2, 'tag2'),
(3, 'tag3'),
(4, 'tag4');

INSERT INTO `yii2_test_article_tag` (`article_id`, `tag_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 3),
(2, 4);