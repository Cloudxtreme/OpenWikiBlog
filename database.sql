-- phpMyAdmin SQL Dump
-- version 3.3.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 31 Sie 2010, 20:13
-- Wersja serwera: 5.1.50
-- Wersja PHP: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Baza danych: `wikiblog`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `prefix_admin`
--

CREATE TABLE IF NOT EXISTS `prefix_admin` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `passwd` varchar(180) NOT NULL,
  `privigle` int(2) NOT NULL,
  `restrictip` varchar(200) NOT NULL COMMENT 'Here is a place for PHP serialized array with ip numbers',
  `disabled` int(1) NOT NULL COMMENT 'Is admin account enabled?',
  `unblock_time` int(20) NOT NULL COMMENT 'When the account will become avaible? - Unix timestamp format only',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Zrzut danych tabeli `prefix_admin`
--

INSERT INTO `prefix_admin` (`id`, `name`, `passwd`, `privigle`, `restrictip`, `disabled`, `unblock_time`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 10, '', 0, 0);

-- --------------------------------------------------------

--
-- Struktura tabeli dla  `prefix_libmypage`
--

CREATE TABLE IF NOT EXISTS `prefix_libmypage` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `include` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `template` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `seo_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `site` varchar(8) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Zrzut danych tabeli `prefix_libmypage`
--

INSERT INTO `prefix_libmypage` (`id`, `include`, `template`, `seo_name`, `site`) VALUES
(1, 'news', 'news.tpl', 'assault-cube-news', 'cube'),
(3, 'copyright', 'copyright.tpl', 'thanks-and-copyrights', 'cube'),
(7, 'contact', 'contact.tpl', 'contact-us', 'cube'),
(4, 'how-to-connect', 'how-to-connect.tpl', 'how-to-connect', 'cube'),
(5, 'game-modes', 'game-modes.tpl', 'game-modes', 'cube'),
(9, 'admin', 'admin-login.tpl', 'login-as-admin', 'cube');
