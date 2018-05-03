-- phpMyAdmin SQL Dump
-- version 3.3.7deb7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 18 feb, 2013 at 06:32 PM
-- Versione MySQL: 5.1.66
-- Versione PHP: 5.3.3-7+squeeze14

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `ada_provider1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_test_course_survey`
--

CREATE TABLE IF NOT EXISTS `module_test_course_survey` (
  `id_corso` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `id_nodo` varchar(64) NOT NULL,
  PRIMARY KEY (`id_corso`,`id_test`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_test_history_answer`
--

CREATE TABLE IF NOT EXISTS `module_test_history_answer` (
  `id_answer` int(10) NOT NULL AUTO_INCREMENT,
  `id_history_test` int(10) unsigned NOT NULL,
  `id_utente` int(10) NOT NULL DEFAULT '0',
  `id_topic` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'id of relative topic test node',
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'id of relative question test node',
  `id_corso` int(10) unsigned NOT NULL,
  `id_istanza_corso` int(10) DEFAULT NULL COMMENT 'id of relative course instance',
  `risposta` text COLLATE utf8_unicode_ci COMMENT 'student''s answer (a serialized array)',
  `commento` text COLLATE utf8_unicode_ci COMMENT 'tutor''s comment',
  `punteggio` smallint(4) DEFAULT NULL,
  `correzione_risposta` text COLLATE utf8_unicode_ci,
  `allegato` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `data` int(10) NOT NULL,
  PRIMARY KEY (`id_answer`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_test_history_test`
--

CREATE TABLE IF NOT EXISTS `module_test_history_test` (
  `id_history_test` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_corso` int(10) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned DEFAULT NULL,
  `id_nodo` int(10) NOT NULL,
  `data_inizio` int(10) NOT NULL,
  `data_fine` int(10) NOT NULL,
  `punteggio_realizzato` int(10) unsigned NOT NULL DEFAULT '0',
  `ripetibile` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `punteggio_minimo_barriera` int(10) unsigned NOT NULL DEFAULT '0',
  `livello_raggiunto` int(10) unsigned DEFAULT NULL,
  `consegnato` tinyint(1) NOT NULL DEFAULT '0',
  `tempo_scaduto` tinyint(1) NOT NULL DEFAULT '0',
  `domande` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_history_test`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_test_nodes`
--

CREATE TABLE IF NOT EXISTS `module_test_nodes` (
  `id_nodo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `id_posizione` int(10) unsigned NOT NULL DEFAULT '0',
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titolo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `consegna` text COLLATE utf8_unicode_ci,
  `testo` text COLLATE utf8_unicode_ci,
  `tipo` mediumint(8) unsigned DEFAULT NULL,
  `data_creazione` int(10) DEFAULT NULL,
  `ordine` int(10) DEFAULT NULL,
  `id_nodo_parent` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_nodo_radice` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_nodo_riferimento` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `livello` int(10) unsigned DEFAULT '0',
  `versione` int(10) unsigned NOT NULL DEFAULT '0',
  `n_contatti` int(10) unsigned NOT NULL DEFAULT '0',
  `icona` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colore_didascalia` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colore_sfondo` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correttezza` tinyint(3) unsigned DEFAULT NULL,
  `copyright` tinyint(3) unsigned DEFAULT NULL,
  `didascalia` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `durata` int(10) DEFAULT NULL,
  `titolo_dragdrop` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id_nodo`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;