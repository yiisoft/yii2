-- phpMyAdmin SQL Dump
-- version 3.5.0
-- http://www.phpmyadmin.net
--
-- Host: 10.10.50.201
-- Generation Time: Nov 01, 2013 at 04:38 PM
-- Server version: 5.1.69-log
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `yiiexample`
--

-- --------------------------------------------------------

--
-- Table structure for table `yii2_test_article`
--

CREATE TABLE IF NOT EXISTS `yii2_test_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `create_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `yii2_test_article`
--

INSERT INTO `yii2_test_article` (`id`, `title`, `content`, `author_id`, `create_date`) VALUES
(1, 'About cats', 'This article is about cats', 1, '2013-10-23 00:00:00'),
(2, 'About dogs', 'This article is about dogs', 2, '2013-11-15 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `yii2_test_item`
--

CREATE TABLE IF NOT EXISTS `yii2_test_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) NOT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `yii2_test_item`
--

INSERT INTO `yii2_test_item` (`id`, `name`, `description`, `category_id`, `price`) VALUES
(1, 'pencil', 'Simple pencil', 1, 2.5),
(2, 'table', 'Wooden table', 2, 100);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
