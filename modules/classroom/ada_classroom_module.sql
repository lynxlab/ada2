SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
-- phpMyAdmin SQL Dump
-- version 4.2.3deb1.trusty~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Set 17, 2014 alle 18:19
-- Versione del server: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classroom_classrooms`
--

CREATE TABLE IF NOT EXISTS `module_classroom_classrooms` (
  `id_classroom` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_venue` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seats` int(4) UNSIGNED DEFAULT NULL,
  `computers` tinyint(4) UNSIGNED DEFAULT NULL,
  `internet` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `wifi` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `projector` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `mobility_impaired` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `hourly_rate` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`id_classroom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classroom_venues`
--

CREATE TABLE IF NOT EXISTS `module_classroom_venues` (
  `id_venue` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressline1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addressline2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `map_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_venue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;
