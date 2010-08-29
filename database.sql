-- phpMyAdmin SQL Dump
-- version 3.3.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Czas wygenerowania: 29 Sie 2010, 21:44
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
(1, 'news', 'news.tpl', 'assault-example-news', 'example'),
(3, 'copyright', 'copyright.tpl', 'thanks-and-copyrights', 'example'),
(7, 'contact', 'contact.tpl', 'contact-us', 'example'),
(4, 'how-to-connect', 'how-to-connect.tpl', 'how-to-connect', 'example'),
(5, 'game-modes', 'game-modes.tpl', 'game-modes', 'example'),
(9, 'admin', 'admin-login.tpl', 'login-as-admin', 'example');
