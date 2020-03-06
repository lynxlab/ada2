-- phpMyAdmin SQL Dump
-- version 4.0.3deb1.precise~ppa.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Lug 29, 2013 alle 11:22
-- Versione del server: 5.5.32-0ubuntu0.12.04.1
-- Versione PHP: 5.3.10-1ubuntu3.7
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_newsletter_history`
--

CREATE TABLE IF NOT EXISTS `module_newsletter_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_newsletter` int(10) unsigned DEFAULT NULL,
  `filter` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datesent` int(11) NOT NULL,
  `recipientscount` int(6) unsigned NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_newsletter_newsletters`
--

CREATE TABLE IF NOT EXISTS `module_newsletter_newsletters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` int(11) NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sender` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `htmltext` text COLLATE utf8_unicode_ci,
  `plaintext` text COLLATE utf8_unicode_ci,
  `draft` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
COMMIT;