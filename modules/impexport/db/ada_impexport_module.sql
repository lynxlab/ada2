-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Dic 18, 2019 alle 15:23
-- Versione del server: 10.3.21-MariaDB-1:10.3.21+maria~bionic-log
-- Versione PHP: 7.2.25-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_impexport_repository`
--

CREATE TABLE IF NOT EXISTS `module_impexport_repository` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_course` int(10) UNSIGNED NOT NULL,
  `exporter_userid` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `id_tester` int(10) UNSIGNED NOT NULL,
  `exportTS` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `repository_idcourse` (`id_course`),
  KEY `repository_idtester` (`id_tester`),
  KEY `repository_exporterid` (`exporter_userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `module_impexport_repository`
--
ALTER TABLE `module_impexport_repository`
  ADD CONSTRAINT `repository_exporterid` FOREIGN KEY (`exporter_userid`) REFERENCES `utente` (`id_utente`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `repository_idcourse` FOREIGN KEY (`id_course`) REFERENCES `servizio_tester` (`id_corso`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `repository_idtester` FOREIGN KEY (`id_tester`) REFERENCES `tester` (`id_tester`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
