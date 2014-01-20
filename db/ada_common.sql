-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Gen 20, 2014 alle 10:18
-- Versione del server: 5.5.34-0ubuntu0.13.04.1
-- Versione PHP: 5.4.9-4ubuntu2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ada_install_common`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE IF NOT EXISTS `utente` (
  `id_utente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `cognome` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `e_mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `layout` varchar(30) COLLATE utf8_unicode_ci DEFAULT '',
  `indirizzo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `citta` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `provincia` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nazione` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `codice_fiscale` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthdate` int(11) unsigned DEFAULT NULL,
  `sesso` enum('F','M') COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stato` tinyint(3) unsigned NOT NULL,
  `lingua` tinyint(3) unsigned DEFAULT '0',
  `timezone` int(11) DEFAULT '0',
  `cap` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `matricola` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id_utente`, `nome`, `cognome`, `tipo`, `e_mail`, `username`, `password`, `layout`, `indirizzo`, `citta`, `provincia`, `nazione`, `codice_fiscale`, `birthdate`, `sesso`, `telefono`, `stato`, `lingua`, `timezone`, `cap`, `matricola`, `avatar`) VALUES
(1, 'admin', 'ada', '2', 'admin@lynxlab.com', 'adminAda', '28dcaeb4eb65eba987aeeb37d228d4526eb37791', NULL, '', '', '', '', '', 0, '', '0123456789', 0, 0, 0, '', '', ''),
(2, 'nomeAutoreAda1', 'cognomeAutoreAda1', '1', 'autore@lynxlab.com', 'autoreAda1', '078139c99e73077fb7db87e49df8fe43cf7ca1b3', 'ada_blu', 'via o piazza', 'roma', 'RM', 'ZW', 'NULL', 0, 'M', '12341234', 0, 0, NULL, '', '', ''),
(3, 'nomeTutorAda1', 'cognomeTutorAda1', '4', 'tutor@lynxlab.com', 'tutorAda1', 'e74e0b5df5cf02436a1c33509c42f76eb36bcabf', 'ada_blu', 'via ostiense 60/d', 'roma', 'roma', 'ZW', 'NULL', 1386716400, 'M', '', 0, 1, 0, '', '', ''),
(4, 'Segreteria', 'ADA', '6', 'segreteria@lynxlab.com', 'switcherAda1', '64baf2b0844f66cd95c50e716f089052f1682572', '', '', 'roma', 'roma', 'ZW', 'NULL', 1386630000, 'M', '', 0, 1, NULL, '', '', ''),
(5, 'nomeStudenteAda1', 'cognomeStudenteAda1', '3', 'studente@lynxlab.com', 'studenteAda1', '7d4ee672efb7827f7be8f2dffb471353f4b88d3a', 'ada_blu', '', '', 'RM', 'ZW', 'NULL', 0, 'M', '', 0, 1, 0, '', '', ''),
(7, 'nomeAutoreAda0', 'cognomeAutoreAda0', '1', 'autore@lynxlab.com', 'autoreAda0', '49dad85d588462d9307f4234ba1b75ca934fdea5', '', 'via o piazza', 'roma', 'RM', 'ZW', 'NULL', 0, 'M', '12341234', 0, 0, NULL, '', '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
