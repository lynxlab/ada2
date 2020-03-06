-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Giu 18, 2019 alle 16:01
-- Versione del server: 10.3.16-MariaDB-1:10.3.16+maria~bionic-log
-- Versione PHP: 7.2.19-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_forkedpaths_history`
--

CREATE TABLE IF NOT EXISTS `module_forkedpaths_history` (
  `module_forkedpaths_history_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userId` int(10) UNSIGNED NOT NULL,
  `courseInstanceId` int(10) UNSIGNED NOT NULL,
  `nodeFrom` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `nodeTo` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `userLevelFrom` tinyint(3) UNSIGNED NOT NULL,
  `userLevelTo` tinyint(3) UNSIGNED NOT NULL,
  `saveTS` int(10) UNSIGNED NOT NULL,
  `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`module_forkedpaths_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;
