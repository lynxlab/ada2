-- phpMyAdmin SQL Dump
-- version 3.5.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Gen 03, 2014 alle 18:22
-- Versione del server: 5.5.34-0ubuntu0.13.04.1
-- Versione PHP: 5.4.9-4ubuntu2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ada_graf_provider0`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `amministratore_corsi`
--

CREATE TABLE IF NOT EXISTS `amministratore_corsi` (
  `id_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `id_utente_amministratore` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_corso`,`id_utente_amministratore`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `amministratore_sistema`
--

CREATE TABLE IF NOT EXISTS `amministratore_sistema` (
  `id_utente_amministratore_sist` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_amministratore_sist`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `amministratore_sistema`
--

INSERT INTO `amministratore_sistema` (`id_utente_amministratore_sist`) VALUES
(1);

-- --------------------------------------------------------

--
-- Struttura della tabella `autore`
--

CREATE TABLE IF NOT EXISTS `autore` (
  `id_utente_autore` int(10) unsigned NOT NULL DEFAULT '0',
  `profilo` text COLLATE utf8_unicode_ci,
  `tariffa` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_autore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `autore`
--

INSERT INTO `autore` (`id_utente_autore`, `profilo`, `tariffa`) VALUES
(3, 'NULL', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `banner`
--

CREATE TABLE IF NOT EXISTS `banner` (
  `id_banner` int(10) NOT NULL AUTO_INCREMENT,
  `address` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_client` int(10) NOT NULL DEFAULT '0',
  `id_course` int(10) NOT NULL DEFAULT '0',
  `module` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `impressions` int(11) NOT NULL DEFAULT '0',
  `a_impressions` int(11) NOT NULL DEFAULT '0',
  `date_from` int(11) DEFAULT NULL,
  `date_to` int(11) DEFAULT NULL,
  KEY `id_banner` (`id_banner`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `bookmark`
--

CREATE TABLE IF NOT EXISTS `bookmark` (
  `id_bookmark` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_utente_studente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `data` int(11) NOT NULL DEFAULT '0',
  `descrizione` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordering` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_bookmark`),
  KEY `bookmark_date` (`data`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `chatroom`
--

CREATE TABLE IF NOT EXISTS `chatroom` (
  `id_chatroom` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `tipo_chat` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titolo_chat` text COLLATE utf8_unicode_ci NOT NULL,
  `argomento_chat` text COLLATE utf8_unicode_ci NOT NULL,
  `id_proprietario_chat` int(10) unsigned NOT NULL DEFAULT '0',
  `tempo_avvio` int(11) NOT NULL DEFAULT '0',
  `tempo_fine` int(11) NOT NULL DEFAULT '0',
  `msg_benvenuto` text COLLATE utf8_unicode_ci NOT NULL,
  `max_utenti` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_chatroom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `clienti`
--

CREATE TABLE IF NOT EXISTS `clienti` (
  `id_client` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `clienti_id` (`id_client`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `destinatari_messaggi`
--

CREATE TABLE IF NOT EXISTS `destinatari_messaggi` (
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_messaggio` int(10) unsigned NOT NULL DEFAULT '0',
  `read_timestamp` int(11) NOT NULL DEFAULT '0',
  `deleted` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`id_utente`,`id_messaggio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `extended_node`
--

CREATE TABLE IF NOT EXISTS `extended_node` (
  `id_node` varchar(64) NOT NULL,
  `hyphenation` varchar(255) NOT NULL,
  `grammar` text NOT NULL,
  `semantic` text NOT NULL,
  `notes` text NOT NULL,
  `examples` text NOT NULL,
  `language` tinyint(3) NOT NULL,
  PRIMARY KEY (`id_node`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `history_esercizi`
--

CREATE TABLE IF NOT EXISTS `history_esercizi` (
  `ID_HISTORY_EX` int(10) NOT NULL AUTO_INCREMENT,
  `ID_UTENTE_STUDENTE` int(10) NOT NULL DEFAULT '0',
  `ID_NODO` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `ID_ISTANZA_CORSO` int(10) NOT NULL DEFAULT '0',
  `DATA_VISITA` int(11) NOT NULL DEFAULT '0',
  `DATA_USCITA` int(11) DEFAULT NULL,
  `RISPOSTA_LIBERA` text COLLATE utf8_unicode_ci,
  `COMMENTO` text COLLATE utf8_unicode_ci,
  `PUNTEGGIO` smallint(4) DEFAULT NULL,
  `CORREZIONE_RISPOSTA_LIBERA` text COLLATE utf8_unicode_ci,
  `RIPETIBILE` smallint(1) NOT NULL DEFAULT '0',
  `ALLEGATO` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID_HISTORY_EX`),
  KEY `ID_HISTORY_EX` (`ID_HISTORY_EX`,`ID_UTENTE_STUDENTE`,`ID_ISTANZA_CORSO`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `history_nodi`
--

CREATE TABLE IF NOT EXISTS `history_nodi` (
  `id_history` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_utente_studente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `data_visita` int(11) NOT NULL DEFAULT '0',
  `data_uscita` int(11) NOT NULL DEFAULT '0',
  `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `remote_address` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `installation_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_from` smallint(5) unsigned DEFAULT '0',
  PRIMARY KEY (`id_history`),
  KEY `id_history` (`id_history`,`id_utente_studente`,`id_istanza_corso`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Dump dei dati per la tabella `history_nodi`
--

INSERT INTO `history_nodi` (`id_history`, `id_utente_studente`, `id_istanza_corso`, `id_nodo`, `data_visita`, `data_uscita`, `session_id`, `remote_address`, `installation_path`, `access_from`) VALUES
(1, 0, 0, '1_0', 1388418708, 1388418720, 'ldrqf1fdk3qf2r44odrfdkk1d4', '127.0.0.1', 'http://localhost/adaInstall', 0),
(2, 0, 0, '1_0', 1388418720, 1388418720, 'ldrqf1fdk3qf2r44odrfdkk1d4', '127.0.0.1', 'http://localhost/adaInstall', 0),
(3, 0, 0, '1_153', 1388768901, 1388768909, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(4, 0, 0, '1_153', 1388768909, 1388768927, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(5, 0, 0, '1_30', 1388768927, 1388768964, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(6, 0, 0, '1_153', 1388768964, 1388768994, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(7, 0, 0, '1_164', 1388768994, 1388769005, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(8, 0, 0, '1_32', 1388769005, 1388769026, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(9, 0, 0, '1_81', 1388769026, 1388769064, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(10, 0, 0, '1_141', 1388769064, 1388769176, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(11, 0, 0, '1_32', 1388769176, 1388769194, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(12, 0, 0, '1_33', 1388769194, 1388769272, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(13, 0, 0, '1_80', 1388769272, 1388769288, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0),
(14, 0, 0, '1_154', 1388769288, 1388769288, 'jiur53hdqug560ue1k3ajhsp33', '127.0.0.1', 'http://localhost/adaInstall', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `iscrizioni`
--

CREATE TABLE IF NOT EXISTS `iscrizioni` (
  `id_utente_studente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `livello` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_utente_studente`,`id_istanza_corso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `istanza_corso`
--

CREATE TABLE IF NOT EXISTS `istanza_corso` (
  `id_istanza_corso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `data_inizio` int(11) NOT NULL DEFAULT '0',
  `durata` int(10) unsigned DEFAULT NULL,
  `data_inizio_previsto` int(11) NOT NULL DEFAULT '0',
  `id_layout` int(10) unsigned NOT NULL DEFAULT '0',
  `data_fine` int(11) NOT NULL DEFAULT '0',
  `status` int(10) unsigned NOT NULL DEFAULT '0',
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `self_instruction` tinyint(1) NOT NULL,
  `self_registration` tinyint(1) NOT NULL,
  `start_level_student` int(2) NOT NULL,
  `duration_subscription` int(3) NOT NULL,
  `open_subscription` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_istanza_corso`),
  KEY `id_istanza_corso` (`id_istanza_corso`,`id_corso`),
  KEY `id_corso` (`id_corso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `link`
--

CREATE TABLE IF NOT EXISTS `link` (
  `id_link` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_nodo_to` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_posizione` int(10) unsigned NOT NULL DEFAULT '0',
  `tipo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `data_creazione` int(11) DEFAULT NULL,
  `stile` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `significato` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `azione` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_link`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=46 ;

--
-- Dump dei dati per la tabella `link`
--

INSERT INTO `link` (`id_link`, `id_utente`, `id_nodo`, `id_nodo_to`, `id_posizione`, `tipo`, `data_creazione`, `stile`, `significato`, `azione`) VALUES
(1, 3, '1_2', '1_24', 1, 0, 1388703600, 0, '', 0),
(2, 3, '1_2', '1_3', 1, 0, 1388703600, 0, '', 0),
(3, 3, '1_2', '1_14', 1, 0, 1388703600, 0, '', 0),
(4, 3, '1_2', '1_23', 1, 0, 1388703600, 0, '', 0),
(5, 3, '1_2', '1_30', 1, 0, 1388703600, 0, '', 0),
(6, 3, '1_3', '1_20', 1, 0, 1388703600, 0, '', 0),
(7, 3, '1_3', '1_14', 1, 0, 1388703600, 0, '', 0),
(8, 3, '1_3', '1_26', 1, 0, 1388703600, 0, '', 0),
(9, 3, '1_4', '1_5', 1, 0, 1388703600, 0, '', 0),
(10, 3, '1_5', '1_7', 1, 0, 1388703600, 0, '', 0),
(11, 3, '1_5', '1_4', 1, 0, 1388703600, 0, '', 0),
(12, 3, '1_6', '1_22', 1, 0, 1388703600, 0, '', 0),
(13, 3, '1_6', '1_7', 1, 0, 1388703600, 0, '', 0),
(14, 3, '1_7', '1_5', 1, 0, 1388703600, 0, '', 0),
(15, 3, '1_7', '1_14', 1, 0, 1388703600, 0, '', 0),
(16, 3, '1_14', '1_23', 1, 0, 1388703600, 0, '', 0),
(17, 3, '1_15', '1_16', 1, 0, 1388703600, 0, '', 0),
(18, 3, '1_16', '1_22', 1, 0, 1388703600, 0, '', 0),
(19, 3, '1_16', '1_15', 1, 0, 1388703600, 0, '', 0),
(20, 3, '1_17', '1_18', 1, 0, 1388703600, 0, '', 0),
(21, 3, '1_18', '1_19', 1, 0, 1388703600, 0, '', 0),
(22, 3, '1_18', '1_17', 1, 0, 1388703600, 0, '', 0),
(23, 3, '1_19', '1_20', 1, 0, 1388703600, 0, '', 0),
(24, 3, '1_19', '1_18', 1, 0, 1388703600, 0, '', 0),
(25, 3, '1_20', '1_21', 1, 0, 1388703600, 0, '', 0),
(26, 3, '1_20', '1_18', 1, 0, 1388703600, 0, '', 0),
(27, 3, '1_21', '1_20', 1, 0, 1388703600, 0, '', 0),
(28, 3, '1_21', '1_22', 1, 0, 1388703600, 0, '', 0),
(29, 3, '1_22', '1_23', 1, 0, 1388703600, 0, '', 0),
(30, 3, '1_22', '1_21', 1, 0, 1388703600, 0, '', 0),
(31, 3, '1_24', '1_25', 1, 0, 1388703600, 0, '', 0),
(32, 3, '1_25', '1_26', 1, 0, 1388703600, 0, '', 0),
(33, 3, '1_25', '1_24', 1, 0, 1388703600, 0, '', 0),
(34, 3, '1_26', '1_27', 1, 0, 1388703600, 0, '', 0),
(35, 3, '1_26', '1_25', 1, 0, 1388703600, 0, '', 0),
(36, 3, '1_27', '1_28', 1, 0, 1388703600, 0, '', 0),
(37, 3, '1_27', '1_26', 1, 0, 1388703600, 0, '', 0),
(38, 3, '1_28', '1_27', 1, 0, 1388703600, 0, '', 0),
(39, 3, '1_33', '1_141', 10, 0, 1388703600, 0, '', 0),
(40, 3, '1_33', '1_116', 10, 0, 1388703600, 0, '', 0),
(41, 3, '1_33', '1_80', 10, 0, 1388703600, 0, '', 0),
(42, 3, '1_33', '1_49', 10, 0, 1388703600, 0, '', 0),
(43, 3, '1_33', '1_36', 10, 0, 1388703600, 0, '', 0),
(44, 3, '1_80', '1_26', 1, 0, 1388703600, 0, '', 0),
(45, 3, '1_116', '1_25', 1, 0, 1388703600, 0, '', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `log_classi`
--

CREATE TABLE IF NOT EXISTS `log_classi` (
  `id_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `id_corso` int(10) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL,
  `data` int(11) NOT NULL,
  `visite` int(10) unsigned NOT NULL DEFAULT '0',
  `punti` int(10) unsigned NOT NULL DEFAULT '0',
  `esercizi` int(10) unsigned NOT NULL DEFAULT '0',
  `msg_out` int(10) unsigned NOT NULL DEFAULT '0',
  `msg_in` int(10) unsigned NOT NULL DEFAULT '0',
  `notes_in` int(10) unsigned NOT NULL DEFAULT '0',
  `notes_out` int(10) unsigned NOT NULL DEFAULT '0',
  `chat` int(10) unsigned NOT NULL DEFAULT '0',
  `bookmarks` int(10) unsigned NOT NULL DEFAULT '0',
  `indice_att` int(10) unsigned NOT NULL DEFAULT '0',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_log`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `messaggi`
--

CREATE TABLE IF NOT EXISTS `messaggi` (
  `id_messaggio` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_group` int(10) unsigned NOT NULL DEFAULT '0',
  `data_ora` int(11) NOT NULL DEFAULT '0',
  `tipo` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titolo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_mittente` int(10) unsigned DEFAULT NULL,
  `priorita` tinyint(3) unsigned DEFAULT NULL,
  `testo` text COLLATE utf8_unicode_ci,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_messaggio`),
  KEY `id_mittente` (`id_mittente`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `modello_corso`
--

CREATE TABLE IF NOT EXISTS `modello_corso` (
  `id_corso` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_utente_autore` int(10) unsigned NOT NULL,
  `id_layout` int(10) unsigned DEFAULT '0',
  `nome` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titolo` text COLLATE utf8_unicode_ci NOT NULL,
  `data_creazione` int(11) DEFAULT NULL,
  `data_pubblicazione` int(11) DEFAULT NULL,
  `descrizione` text COLLATE utf8_unicode_ci,
  `id_nodo_iniziale` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_nodo_toc` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `media_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `static_mode` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_lingua` tinyint(3) unsigned NOT NULL,
  `crediti` tinyint(3) NOT NULL DEFAULT '1',
  `id_servizio` int(10) NOT NULL,
  PRIMARY KEY (`id_corso`),
  UNIQUE KEY `modello_corso_nome` (`nome`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `modello_corso`
--

INSERT INTO `modello_corso` (`id_corso`, `id_utente_autore`, `id_layout`, `nome`, `titolo`, `data_creazione`, `data_pubblicazione`, `descrizione`, `id_nodo_iniziale`, `id_nodo_toc`, `media_path`, `static_mode`, `id_lingua`, `crediti`, `id_servizio`) VALUES
(1, 3, 0, 'pub-01', 'Notizie da ADA', 1388358000, NULL, NULL, '0', '0', NULL, 0, 1, 1, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `module_test_course_survey`
--

CREATE TABLE IF NOT EXISTS `module_test_course_survey` (
  `id_corso` int(11) NOT NULL,
  `id_test` int(11) NOT NULL,
  `id_nodo` varchar(64) NOT NULL,
  PRIMARY KEY (`id_corso`,`id_test`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=107 ;

--
-- Dump dei dati per la tabella `module_test_nodes`
--

INSERT INTO `module_test_nodes` (`id_nodo`, `id_corso`, `id_posizione`, `id_utente`, `id_istanza`, `nome`, `titolo`, `consegna`, `testo`, `tipo`, `data_creazione`, `ordine`, `id_nodo_parent`, `id_nodo_radice`, `id_nodo_riferimento`, `livello`, `versione`, `n_contatti`, `icona`, `colore_didascalia`, `colore_sfondo`, `correttezza`, `copyright`, `didascalia`, `durata`, `titolo_dragdrop`) VALUES
(1, 1, 0, 3, 0, 'test 1', 'Prova per UEMS', '', '', 100101, 1388768870, 0, NULL, NULL, '1_29', 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(2, 1, 0, 3, 0, 'Sessione 1', 'Esercizi classici', '', '<p>In questa sessione mostriamo esempi di esercizi classici:&#160;scelta singola, scelta multipla.</p>', 300000, 1388768870, 1, '1', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(3, 1, 0, 3, 0, 'Domanda 1', 'Scelta singola', '<p>Seleziona la risposta corretta</p>', 'Data l''equazione <img src="http://upload.wikimedia.org/math/3/f/c/3fc507748a44ba50c768adedba1c5fca.png" alt="2x+3=4" class="tex" />, stabilire il valore di <i>x</i>.<br />', 420000, 1388768870, 1, '2', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(4, 1, 0, 3, 0, '4', '', '', '4', 500000, 1388768870, 1, '3', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(5, 1, 0, 3, 0, '10', '', '', '10', 500000, 1388768870, 2, '3', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(6, 1, 0, 3, 0, '0.5', '', '', '0.5', 500000, 1388768870, 3, '3', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(7, 1, 0, 3, 0, '1.5', '', '', '1.5', 500000, 1388768870, 4, '3', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(8, 1, 0, 3, 0, '8', '', '', '8', 500000, 1388768870, 5, '3', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(9, 1, 0, 3, 0, 'Domanda 2', '', '<p>Ascolta l''audio e rispondi alla domanda.</p>\n<p><MEDIA TYPE="2" VALUE="/ada20/services/media/2/A1_M1_U1_S3_E1.mp3" TITLE="Esempio audio"></p>', 'Da quale paese provengono le studentesse?<br />', 410000, 1388768870, 2, '2', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(10, 1, 0, 3, 0, 'Siena', '', '', 'Siena', 500000, 1388768870, 1, '9', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(11, 1, 0, 3, 0, 'New York', '', '', 'New York', 500000, 1388768870, 2, '9', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(12, 1, 0, 3, 0, 'Tokyo', '', '', 'Tokyo', 500000, 1388768870, 3, '9', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(13, 1, 0, 3, 0, 'Berlino', '', '', 'Berlino', 500000, 1388768870, 4, '9', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(14, 1, 0, 3, 0, 'Domanda 3', 'Scelta singola con variante cancellazione', '<p>Cancella la parola intrusa cliccandoci sopra.</p>', 'Quale di queste parole non è un verbo?<br />', 410100, 1388768870, 3, '2', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(15, 1, 0, 3, 0, 'Bare', '', '', 'Bare', 500000, 1388768870, 1, '14', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(16, 1, 0, 3, 0, 'Dare', '', '', 'Dare', 500000, 1388768870, 2, '14', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(17, 1, 0, 3, 0, 'Fare', '', '', 'Fare', 500000, 1388768870, 3, '14', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(18, 1, 0, 3, 0, 'Domanda 4', 'Scelta multipla con variante evidenziazione', '<p>Evidenzia i sinonimi</p>', 'Evidenzia le parole che sono sinonimi di Dolciastro<br />', 410200, 1388768870, 4, '2', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(19, 1, 0, 3, 0, 'Dolce', '', '', 'Dolce', 500000, 1388768870, 1, '18', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(20, 1, 0, 3, 0, 'Aspro', '', '', 'Aspro', 500000, 1388768870, 2, '18', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(21, 1, 0, 3, 0, 'Salato', '', '', 'Salato', 500000, 1388768870, 3, '18', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(22, 1, 0, 3, 0, 'Zuccherato', '', '', 'Zuccherato', 500000, 1388768870, 4, '18', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(23, 1, 0, 3, 0, 'Salato', '', '', 'Salato', 500000, 1388768870, 5, '18', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(24, 1, 0, 3, 0, 'Sessione 2', 'Esercizi a risposta aperta', '', '<p>In questa sessione vedremo esempi di domande a risposta aperta</p>', 300000, 1388768870, 2, '1', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(25, 1, 0, 3, 0, 'Domanda 5', 'Domanda a risposta aperta con correzione automatica', '<p>Digita la risposta corretta</p>', 'Chi è l''attuale presidente della repubblica italiana?<br />', 450001, 1388768870, 1, '24', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(26, 1, 0, 3, 0, 'napolitano', '', '', 'napolitano', 501000, 1388768870, 1, '25', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(27, 1, 0, 3, 0, 'giorgio napolitano', '', '', 'giorgio napolitano', 501000, 1388768870, 2, '25', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(28, 1, 0, 3, 0, 'napolitano giorgio', '', '', 'napolitano giorgio', 501000, 1388768870, 3, '25', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(29, 1, 0, 3, 0, 'Domanda 6', 'Domanda a risposta aperta con correzione manuale', '<p>Digita la risposta alla seguente domanda</p>', 'Racconta brevemente la storia di Pinocchio<br />', 440000, 1388768870, 2, '24', '1', NULL, 0, 0, 0, '', '', '', 5, 0, '', 0, ''),
(30, 1, 0, 3, 0, 'Domanda 7', 'Domanda a risposta aperta con upload di immagine e correzione manuale', '<p>Invia una immagine e, se vuoi, digita una spiegazione</p>', 'Invia una immagine di Pinocchio<br />', 470000, 1388768870, 3, '24', '1', NULL, 0, 0, 0, '', '', '', 5, 0, '', 0, ''),
(31, 1, 0, 3, 0, 'Sessione 3', 'Esercizi avanzati: Cloze', '', '<p>In questa sessione vedremo esempi di esercizi CLOZE</p>', 300000, 1388768870, 3, '1', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(32, 1, 0, 3, 0, 'Domanda 8', 'Esercizio CLOZE a inserimento libero', '<p>Riempi gli spazi vuoti nel testo</p>', '<div>Nel <cloze title="1">mezzo</cloze> del cammin di nostra vita</div>\n<div>mi ritrovai per una <cloze title="2">selva</cloze> oscura</div>\n<div>ché la diritta <cloze title="3">via</cloze> era smarrita.</div>\n<div>Ahi <cloze title="4">quanto</cloze> a dir qual era è cosa dura</div>\n<div>esta selva <cloze title="5">selvaggia</cloze> e aspra e forte</div>\n<div>che nel pensier rinova la <cloze title="6">paura</cloze>!</div>\n<br />', 460000, 1388768870, 1, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(33, 1, 0, 3, 0, 'mezzo', '', '', 'mezzo', 500000, 1388768870, 1, '32', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(34, 1, 0, 3, 0, 'selva', '', '', 'selva', 500000, 1388768870, 2, '32', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(35, 1, 0, 3, 0, 'via', '', '', 'via', 500000, 1388768870, 3, '32', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(36, 1, 0, 3, 0, 'quanto', '', '', 'quanto', 500000, 1388768870, 4, '32', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(37, 1, 0, 3, 0, 'selvaggia', '', '', 'selvaggia', 500000, 1388768870, 5, '32', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(38, 1, 0, 3, 0, 'paura', '', '', 'paura', 500000, 1388768870, 6, '32', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(39, 1, 0, 3, 0, 'Domanda 9', 'Esercizio CLOZE a inserimento libero facilitato', '<p>Riempi gli spazi vuoti nel testo</p>', '<div>Nel <cloze title="1">mezzo</cloze> del cammin di nostra vita</div>\n<div>mi ritrovai per una <cloze title="2">selva</cloze> oscura</div>\n<div>ché la diritta <cloze title="3">via</cloze> era smarrita.</div>\n<div>Ahi <cloze title="4">quanto</cloze> a dir qual era è cosa dura</div>\n<div>esta selva <cloze title="5">selvaggia</cloze> e aspra e forte</div>\n<div>che nel pensier rinova la <cloze title="6">paura</cloze>!</div>\n<br />', 460101, 1388768870, 2, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(40, 1, 0, 3, 0, 'mezzo', '', '', 'mezzo', 500001, 1388768870, 1, '39', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(41, 1, 0, 3, 0, 'selva', '', '', 'selva', 500001, 1388768870, 2, '39', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(42, 1, 0, 3, 0, 'via', '', '', 'via', 500001, 1388768870, 3, '39', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(43, 1, 0, 3, 0, 'quanto', '', '', 'quanto', 500001, 1388768870, 4, '39', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(44, 1, 0, 3, 0, 'selvaggia', '', '', 'selvaggia', 500001, 1388768870, 5, '39', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(45, 1, 0, 3, 0, 'paura', '', '', 'paura', 500001, 1388768870, 6, '39', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(46, 1, 0, 3, 0, 'Domanda 10', 'Esercizio CLOZE con tendina', '<p>Seleziona le voci corrette per completare il testo</p>', '<div>Nel <cloze title="1">mezzo</cloze> del cammin di nostra vita</div>\n<div>mi ritrovai per una <cloze title="2">selva</cloze> oscura</div>\n<div>ché la diritta <cloze title="3">via</cloze> era smarrita.</div>\n<div>Ahi <cloze title="4">quanto</cloze> a dir qual era è cosa dura</div>\n<div>esta selva <cloze title="5">selvaggia</cloze> e aspra e forte</div>\n<div>che nel pensier rinova la <cloze title="6">paura</cloze>!</div>', 460200, 1388768870, 3, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(47, 1, 0, 3, 0, 'mezzo', '', '', 'mezzo', 500000, 1388768870, 1, '46', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(48, 1, 0, 3, 0, 'mentre', '', '', 'mentre', 500000, 1388768870, 1, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(49, 1, 0, 3, 0, 'giorno', '', '', 'giorno', 500000, 1388768870, 1, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(50, 1, 0, 3, 0, 'selva', '', '', 'selva', 500000, 1388768870, 2, '46', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(51, 1, 0, 3, 0, 'foresta', '', '', 'foresta', 500000, 1388768870, 2, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(52, 1, 0, 3, 0, 'città', '', '', 'città', 500000, 1388768870, 2, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(53, 1, 0, 3, 0, 'via', '', '', 'via', 500000, 1388768870, 3, '46', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(54, 1, 0, 3, 0, 'strada', '', '', 'strada', 500000, 1388768870, 3, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(55, 1, 0, 3, 0, 'direzione', '', '', 'direzione', 500000, 1388768870, 3, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(56, 1, 0, 3, 0, 'quanto', '', '', 'quanto', 500000, 1388768870, 4, '46', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(57, 1, 0, 3, 0, 'tanto', '', '', 'tanto', 500000, 1388768870, 4, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(58, 1, 0, 3, 0, 'manco', '', '', 'manco', 500000, 1388768870, 4, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(59, 1, 0, 3, 0, 'selvaggia', '', '', 'selvaggia', 500000, 1388768870, 5, '46', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(60, 1, 0, 3, 0, 'oscura', '', '', 'oscura', 500000, 1388768870, 5, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(61, 1, 0, 3, 0, 'tetra', '', '', 'tetra', 500000, 1388768870, 5, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(62, 1, 0, 3, 0, 'paura', '', '', 'paura', 500000, 1388768870, 6, '46', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(63, 1, 0, 3, 0, 'fifa', '', '', 'fifa', 500000, 1388768870, 6, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(64, 1, 0, 3, 0, 'gioia', '', '', 'gioia', 500000, 1388768870, 6, '46', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(65, 1, 0, 3, 0, 'Domanda 11', 'Esercizio CLOZE con drag''n''drop', '<p>Trascina le parole nel testo, alle corrette posizioni.</p>', '<div>Nel <cloze title="1">mezzo</cloze> del cammin di nostra vita</div>\n<div>mi ritrovai per una <cloze title="2">selva</cloze> oscura</div>\n<div>ché la diritta <cloze title="3">via</cloze> era smarrita.</div>\n<div>Ahi <cloze title="4">quanto</cloze> a dir qual era è cosa dura</div>\n<div>esta selva <cloze title="5">selvaggia</cloze> e aspra e forte</div>\n<div>che nel pensier rinova la <cloze title="6">paura</cloze>!</div>\n<br />', 460310, 1388768870, 4, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(66, 1, 0, 3, 0, 'mezzo', '', '', 'mezzo', 500000, 1388768870, 1, '65', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(67, 1, 0, 3, 0, 'mazzo', '', '', 'mazzo', 500000, 1388768870, 1, '65', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(68, 1, 0, 3, 0, 'selva', '', '', 'selva', 500000, 1388768870, 2, '65', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(69, 1, 0, 3, 0, 'via', '', '', 'via', 500000, 1388768870, 3, '65', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(70, 1, 0, 3, 0, 'quanto', '', '', 'quanto', 500000, 1388768870, 4, '65', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(71, 1, 0, 3, 0, 'selvaggia', '', '', 'selvaggia', 500000, 1388768870, 5, '65', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(72, 1, 0, 3, 0, 'paura', '', '', 'paura', 500000, 1388768870, 6, '65', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(73, 1, 0, 3, 0, 'Domanda 12', 'Esercizio CLOZE a eliminazione di parole', '<p>Elimina le parole "intruse" cliccando con il mouse</p>', '<div>Nel mezzo del cammin di nostra vita</div>\n<div>mi ritrovai per <cloze title="11">sbaglio</cloze> <cloze title="12">in</cloze> una selva oscura</div>\n<div>ché la diritta via era smarrita.</div>\n<div>Ahi quanto a dir qual era è cosa dura</div>\n<div>esta selva selvaggia <cloze title="34">nera</cloze> e aspra e forte</div>\n<div>che nel <cloze title="41">mio</cloze> pensier rinova la paura!</div>', 460400, 1388768870, 5, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(74, 1, 0, 3, 0, 'sbaglio', '', '', 'sbaglio', 500000, 1388768870, 11, '73', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(75, 1, 0, 3, 0, 'in', '', '', 'in', 500000, 1388768870, 12, '73', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(76, 1, 0, 3, 0, 'nera', '', '', 'nera', 500000, 1388768870, 34, '73', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(77, 1, 0, 3, 0, 'mio', '', '', 'mio', 500000, 1388768870, 41, '73', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(78, 1, 0, 3, 0, 'Domanda 13', 'Esercizio CLOZE a evidenziazione di elementi', '<p>Seleziona la raffigurazione classica di Dante Alighieri</p>', '<div><cloze title="1"><img width="128" height="128" src="/adaInstall/services/media/3/dante.jpg" alt="" /></cloze> <img width="128" height="128" src="/adaInstall/services/media/3/Dantes-Inferno.png" alt="" /> <img width="128" height="128" src="/adaInstall/services/media/3/euro.jpg" alt="" /></div>\n<div></div>\n<br />', 460420, 1388768870, 6, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(79, 1, 0, 3, 0, '<img width="128" height="128" src="/ada20/services/media/3/dante.jpg" alt="" />', '', '', '<img width="128" height="128" src="/adaInstall/services/media/3/dante.jpg" alt="" />', 500000, 1388768870, 1, '78', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(80, 1, 0, 3, 0, 'Domanda 14', 'Esercizio CLOZE a incastro di parole', '<p>Collocare nella posizione corretta le parole presenti nella colonna</p>', '<div>Nel <cloze title="1">mezzo</cloze> del cammin di nostra vita</div>\n<div>mi ritrovai per una <cloze title="9">selva</cloze> oscura</div>\n<div>ché la diritta <cloze title="12">via</cloze> era smarrita.</div>\n<div>Ahi <cloze title="14">quanto</cloze> a dir qual era è cosa dura</div>\n<div>esta selva <cloze title="22">selvaggia</cloze> e aspra e forte</div>\n<div>che nel pensier rinova la <cloze title="30">paura</cloze>!</div>', 460610, 1388768870, 7, '31', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(81, 1, 0, 3, 0, 'mezzo', '', '', 'mezzo', 500000, 1388768870, 1, '80', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(82, 1, 0, 3, 0, 'selva', '', '', 'selva', 500000, 1388768870, 9, '80', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(83, 1, 0, 3, 0, 'via', '', '', 'via', 500000, 1388768870, 12, '80', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(84, 1, 0, 3, 0, 'quanto', '', '', 'quanto', 500000, 1388768870, 14, '80', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(85, 1, 0, 3, 0, 'selvaggia', '', '', 'selvaggia', 500000, 1388768870, 22, '80', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(86, 1, 0, 3, 0, 'paura', '', '', 'paura', 500000, 1388768870, 30, '80', '1', NULL, 0, 0, 0, '', '', '', 1, 0, '', 0, ''),
(87, 1, 0, 3, 0, 'sessione 4', 'Funzionalità per Sondaggi', '', '', 300000, 1388768870, 4, '1', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(88, 1, 0, 3, 0, 'Domanda 15', 'Quanti anni hai?', '', '', 420000, 1388768870, 1, '87', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(89, 1, 0, 3, 0, '0 - 17', '', '', '0 - 17', 500000, 1388768870, 1, '88', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(90, 1, 0, 3, 0, '18 - 22', '', '', '18 - 22', 500000, 1388768870, 2, '88', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(91, 1, 0, 3, 0, '23 - 28', '', '', '23 - 28', 500000, 1388768870, 3, '88', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(92, 1, 0, 3, 0, '28 - 35', '', '', '28 - 35', 500000, 1388768870, 4, '88', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(93, 1, 0, 3, 0, 'oltre 35', '', '', 'oltre 35', 500000, 1388768870, 5, '88', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(94, 1, 0, 3, 0, 'Domanda 16', 'Di quale periferica tecnologica disponi?', '', '', 411000, 1388768870, 2, '87', '1', NULL, 0, 0, 0, '', '', '', 0, 0, 'commento', 0, ''),
(95, 1, 0, 3, 0, 'Laptop', '', '', 'Laptop', 500000, 1388768870, 1, '94', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(96, 1, 0, 3, 0, 'Smartphone', '', '', 'Smartphone', 500000, 1388768870, 2, '94', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(97, 1, 0, 3, 0, 'Tablet', '', '', 'Tablet', 500000, 1388768870, 3, '94', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(98, 1, 0, 3, 0, 'Altro (specificare)', '', '', 'Altro (specificare)', 510000, 1388768870, 4, '94', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(99, 1, 0, 3, 0, 'Domanda 17', 'Soddisfazione utente', '<p>Esprimi un giudizio sul corso che hai conseguito</p>', '', 431000, 1388768870, 3, '87', '1', NULL, 0, 0, 0, '', '', '', 0, 0, 'Hai qualche suggerimento?', 0, ''),
(100, 1, 0, 3, 0, 'Affatto', '', '', 'Affatto', 500000, 1388768870, 1, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(101, 1, 0, 3, 0, '', '', '', '', 500000, 1388768870, 2, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(102, 1, 0, 3, 0, '', '', '', '', 500000, 1388768870, 3, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(103, 1, 0, 3, 0, 'neutrale', '', '', 'neutrale', 500000, 1388768870, 4, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(104, 1, 0, 3, 0, '', '', '', '', 500000, 1388768870, 5, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(105, 1, 0, 3, 0, '', '', '', '', 500000, 1388768870, 6, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, ''),
(106, 1, 0, 3, 0, 'Totalmente', '', '', 'Totalmente', 500000, 1388768870, 7, '99', '1', NULL, 0, 0, 0, '', '', '', 0, 0, '', 0, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `nodo`
--

CREATE TABLE IF NOT EXISTS `nodo` (
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_posizione` int(10) unsigned NOT NULL DEFAULT '0',
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza` int(10) unsigned DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titolo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `testo` text COLLATE utf8_unicode_ci,
  `tipo` mediumint(8) unsigned DEFAULT NULL,
  `data_creazione` int(11) DEFAULT NULL,
  `ordine` int(11) DEFAULT NULL,
  `id_nodo_parent` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `livello` tinyint(3) unsigned DEFAULT NULL,
  `versione` tinyint(3) unsigned DEFAULT NULL,
  `n_contatti` int(10) unsigned DEFAULT NULL,
  `icona` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colore_didascalia` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colore_sfondo` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correttezza` tinyint(3) unsigned DEFAULT NULL,
  `copyright` tinyint(3) unsigned DEFAULT NULL,
  `lingua` tinyint(3) NOT NULL,
  `pubblicato` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_nodo`),
  KEY `parent` (`id_nodo_parent`,`ordine`),
  KEY `id_istanza` (`id_istanza`,`id_utente`,`id_nodo_parent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `nodo`
--

INSERT INTO `nodo` (`id_nodo`, `id_posizione`, `id_utente`, `id_istanza`, `nome`, `titolo`, `testo`, `tipo`, `data_creazione`, `ordine`, `id_nodo_parent`, `livello`, `versione`, `n_contatti`, `icona`, `colore_didascalia`, `colore_sfondo`, `correttezza`, `copyright`, `lingua`, `pubblicato`) VALUES
('1_0', 1, 3, 0, 'Notizie da ADA', 'NULL', '<p>Queste sono le notizie dal mondo di ADA!!</p><p>a presto</p>', 1, 1388417641, 0, 'NULL', 0, 0, 2, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_1', 1, 3, 0, 'Inizia', 'ADA 2.0', '<p>Da qui potete scegliere di:</p><ol><li>visionare un <strong>corso demo</strong> su ADA che ne illustra le caratterische più interessanti;</li><li>visionare i <strong>manuali online </strong>della piattaforma, con la descrizione dettagliata di tutte le funzionalità di ADA.</li></ol><p>Per navigare tra i contenuti basta aprire il pannello  su " <img src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" alt="" /> <strong>naviga</strong>"&#160; nella barra dei menù oppure l''<strong>indice </strong>(clic sul titolo del corso nel beadcrump).</p><p><strong>Buona navigazione!</strong></p>', 1, 1388768868, 0, '1_0', 0, 0, 0, '/var/www/html/adaInstall/services/media/3/logo_ada1.gif', 'NULL', 'NULL', 0, 0, 0, 0),
('1_2', 2, 3, 0, 'Il demo', 'piattaforma', '<p><span class="alignleft"><img width="130" vspace="15" hspace="15" height="89" align="left" alt="" src="/adaInstall/services/media/3/ManualeAda/logo_ada1.gif" /><br /></span></p><p>Benvenuti nel corso demo di <strong>ADA - Ambiente Digitale per l''Apprendimento</strong>.</p><p></p><p>Questo corso è stato realizzato allo scopo di illustrare le potenzialità della piattaforma e le funzioni più interessanti di cui è corredata.</p><p></p><p>Nelle prossime pagine affronteremo tre aspetti principali di una piattaforma e-learning:</p><ul><li><LINK TYPE="INTERNAL" VALUE="3"></li><li><LINK TYPE="INTERNAL" VALUE="14"></li><li><LINK TYPE="INTERNAL" VALUE="23"></li></ul><p>Ogn''una di queste "brevi lezioni" continene degli approfondimenti.<br />Potete vedere gli <em>approfondimenti</em> elencati nel pannello di navigazione a destra (se il pannello è chiuso apritelo cliccando su " <a href="#" onclick="toggleElementVisibility(''menuright'', ''right'');"><img alt="" src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" /> naviga</a>" nella barra dei menù in alto).</p><p>Potete iniziare dall''argomento che vi interessa e saltare da una pagina all''altra oppure seguire il percorso lineare cliccando sui tati "continua" presenti al termine delle pagine.</p><p>Per avere un''idea di tutti contenuti del corso&#160; l''<strong>indice</strong> del corso, cliccando sul link omonimo nel pannello di navigazione. L''indice si può consultare anche cliccando sul nome del corso in alto a destra.</p>    <p>I contenuti di questo corso demo sono tutti immediatamente visibili ma ADA consente di filtrare i contenuti da far vedere al corsista, un corso infatti può essere strutturato per <em>livelli di accesso</em>, solo se il corsista (<LINK TYPE="INTERNAL" VALUE="24">) è stato abilitato a quel specifico livello può vederne il contenuto. L''abilitazione può essere gestita in maniera automatica con gli esercizi (il corsista passa di livello solo se risponde correttamente ad un questionario) oppure dal tutor che alza di livello il corsista dopo averne verificate le competenze raggiunte e/o le attività realizzate.</p><p>Per ulteriori approfondimenti sull''uso e sugli aspetti tecnici rimandiamo a <LINK TYPE="INTERNAL" VALUE="30"></p>', 1, 1388768868, 0, '1_1', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_3', 3, 3, 0, '1. I contenuti', 'contenuti', '<p style="text-align: justify;"><img width="200" vspace="15" hspace="15" height="133" align="right" alt="" src="/adaInstall/services/media/3/ManualeAda/Multimedia.jpg" />ADA permette all''autore (<LINK TYPE="INTERNAL" VALUE="26">) di organizzare i contenuti in maniera estremamente strutturata.</p><p style="text-align: justify;">Un corso è costituito da un insieme di gruppi, nodi ed esercitazioni. Questi elementi possono essere ulteriormente arricchiti da un corredo multimediale:</p><ul><li style="text-align: justify;">immagini,</li><li style="text-align: justify;">video,</li><li style="text-align: justify;">suoni,</li><li style="text-align: justify;">file di qualsiasi tipo,</li><li style="text-align: justify;">link esterni.</li></ul><p style="text-align: justify;"><img width="250" vspace="15" hspace="15" height="291" border="1" align="right" src="/adaInstall/services/media/3/ManualeAda/struttura_corso.jpg" alt="" />In ADA ogni <strong>nodo</strong> rappresenta un contenuto significativo, definito da un proprio <em>nome</em> (titolo del nodo), una propria <em>posizione</em> (all''interno dell''indice) ed una propria <em>pagina</em> che mostra tutti i materiali didattici relativi.&#160;</p><p style="text-align: justify;">Ogni nodo è inserito in una <strong>gerarchia</strong>: è padre o figlio cioè di un altro elemento ed è poi legato agli altri nodi tramite dei <em>collegamenti</em>, dei link, che creano una rete di rimandi e legami ipertestuali.</p><p style="text-align: justify;">Il corso quindi può assumere una molteplice varietà di forme e strutture: non solo un elenco <strong>lineare</strong> ma anche una complessa <strong>rete</strong> di conoscenze diffuse. Proprio per questo il corso può essere navigato sia in <strong>maniera gerarchica</strong> (scendendo di livello in livello, in modo lineare) o in <strong>maniera reticolare</strong> (seguendo i link inseriti dall''autore).</p><p style="text-align: justify;">Rispetto ad altre piattaforme inoltre, questi contenuti sono perfettamente <strong>integrati</strong> con la piattaforma e con gli strumenti disponibili, in particolare con il <strong>forum</strong>: le discussioni possono essere attivate dal tutor e dagli studenti in qualsiasi punto del corso. Questo rende possibile un uso del forum estremamente raffinato: è un vero luogo di discussione e un laboratorio di idee.</p><p style="text-align: justify;">Grazie a questa stretta integrazione tra contenuti e forum abbiamo inoltre messo a punto, ispirandoci ai principi della didattica costruttivista, un meccanismo di promozione delle note forum (visibili solo alla classe) a nodi (visibili a tutte le classi del corso) che vedremo meglio più avanti <LINK TYPE="INTERNAL" VALUE="20">.</p><p style="text-align: justify;">Aprendo il pannello <a href="#"><img src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" alt="" />naviga</a> , oltre a visualizzare l''elenco degli approfondimenti, potete consultare la mappa grafica degli stess: basta cliccare sul link <strong>mappa</strong> e continuare la navigazione in questa modalità.</p><p style="text-align: justify;">Ora potete decidere se saltare direttamente all''argomento/lezione successiva [<LINK TYPE="INTERNAL" VALUE="14">] oppure se proseguire la lettura a cominciare dal primo approfondimento elencato nel pannello a destra. Per tornare ai livelli superiori basta cliccare sul percorso in corrispondenza del "dove sei:"&#160;in alto a destra.</p>', 1, 1388768868, 0, '1_2', 0, 0, 0, '/var/www/html/adaInstall/services/media/3/find-an-editor1.gif', 'NULL', 'NULL', 0, 0, 0, 0),
('1_4', 4, 3, 0, '1.1 Il testo', 'contenuti', '<p>I contenuti testuali del corso si inseriscono attraverso un comune editor html ti tipologia WYSIWYG (what you see is what you get). <br />Il testo può essere quindi formattato come si vuole, applicando gli stili, inserendo elenchi, tabelle e quanto altro.</p><p><MEDIA TYPE="1" VALUE="barra_editing.jpg"></p><p>L''immagine qua sopra mostra gli strumenti di editing a disposizione dell''editor di contenuti online. E'' possibile formattare il testo, inserire link, immagini e video.</p><p>E'' possibile anche copiare e incollare il testo da documenti di testo (Word tipicamente) mantenendo gli stili e le formattazioni.</p><p>Avanti <LINK TYPE="INTERNAL" VALUE="5"></p>', 0, 1388768868, 0, '1_3', 0, 0, 0, '/var/www/html/adaInstall/services/media/3/barra_editing.jpg', 'NULL', 'NULL', 0, 0, 0, 0),
('1_5', 5, 3, 0, '1.2 I media', 'contenuti', '<p><img width="200" vspace="15" hspace="15" height="133" align="right" src="/adaInstall/services/media/3/ManualeAda/Multimedia.jpg" alt="" />ADA è in grado di supportare vari tipi di contenuto multimediale: immagini, video, audio, documenti di vari formati.</p><p>Tutti i media sono visualizzati come delle icone, tranne le immagini che vengono visualizzate a schermo, per i video si può decidere se visualizzarli tramite icona (in questo caso cliccando si aprirà una finestra e il video partirà), oppure tramite anteprima (il corsista deve cliccare per far partire ilv eideo). I media vengono elencati anche nella lista media del pannello di navigazione:</p><ul><li>i file Audio possono essere file in formato Wav, Midi e Mp3;</li><li>i file Immagine possono esser file in formato Gif, Jpg e Png;</li><li>i file Documenti possono essere nei principali formati Office e Open Office, txt, Rtf, Html, Pdf (Acrobat Reader), Zip (WinZip);</li><li>i file Video possono essere file in formato Avi, Dcr (Schokwave), Swf (Flash Movies), Flv e Mpg).</li></ul><p>Questi sono i formati di default, è possibile aggiungerne altri, così come si può gestire la grandezza massima dei file che un autore può inserire in un corso.</p><p>Prima di proseguire nella lettura, ecco alcuni esempi di media.</p><p>Un esempio di <strong>audio </strong><MEDIA TYPE="2" VALUE="Amanda.mp3">[formato MP3].</p><p>La <strong>foto</strong> di un gatto [formato JPG].</p><p><MEDIA TYPE="1" VALUE="esempio_immagine.jpg"></p><p>La nostra <strong>presentazione</strong> <MEDIA TYPE="5" VALUE="presentazione_Lynx_2010.pdf"> [formato PDF].</p><p>Un esempio di <strong>animazione</strong> realizzata per il progetto DEAL TOI [formato SWF], l''animazione è priva di audio.</p><p><MEDIA TYPE="3" VALUE="01_INGLESE.swf"></p><p>Questi file sono elencati anche nel pannello naviga e dal pannello naviga possono essere aperti. Provate a farlo con il file SWF:&#160;cliccate su <a href="#" onclick="toggleElementVisibility(''menuright'', ''right'');"><img alt="" src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" />naviga</a> e poi sul nome del file, si aprirà in una finestra più grande.</p><p></p><p>Vedi l''approfondimento nel pannello naviga prima di andare avanti <LINK TYPE="INTERNAL" VALUE="7"><br />Indietro <LINK TYPE="INTERNAL" VALUE="4"></p>', 1, 1388768868, 0, '1_3', 0, 0, 0, '/var/www/html/adaInstall/services/media/3/Multimedia.jpg', 'NULL', 'NULL', 0, 0, 0, 0),
('1_6', 6, 3, 0, '1.2.1 Pacchetti SCORM e Videolezioni', 'contenuti', '<p>ADA può interagire con pacchetti SCORM e videolezioni realizzate in Flash. Questo è un esempio di videolezione:</p><p><object width="953" height="619" id="presentation" type="application/x-shockwave-flash" data="http://ada.lynxlab.com/demo/STEP_A/STEP_A.swf">  <param name="allowScriptAccess" value="sameDomain" />  <param name="movie" value="http://ada.lynxlab.com/demo/STEP_A/STEP_A.swf" />  <param name="quality" value="high" />  <param name="bgcolor" value="#ffffff" />  <param name="allowFullScreen" value="true" />  </object></p><p>In questo corso l''interazione con lo SCORM non è attivato perché ADA incorpora già tutte le funzioni di tracciamento dello studente utile per la valutazione del percorso di studio del corsista (vedi <LINK TYPE="INTERNAL" VALUE="22">). </p><p>Prosegui con la lettura ora <LINK TYPE="INTERNAL" VALUE="7"></p>', 0, 1388768868, 0, '1_5', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_7', 7, 3, 0, '1.3 Gli esercizi', 'contenuti', '<p>Gli esercizi dentro ADA possono essere di vari tipi:</p><ul><li>domande a risposta multipla,</li><li>domande a risposta aperta,</li><li>domande a risposta esatta,</li><li>cloze (esercizi di completamento).</li></ul><p>Anche gli esercizi possono avere al loro interno dei media: file audio o video o documenti da scaricare.</p><p>Se un gruppo contiene un esercizio viene visualizzato nel pannello <a onclick="toggleElementVisibility(''menuright'', ''right'');" href="#"><img src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" alt="" />naviga</a>, come in questo caso, provate a rispondere alla prima domanda.</p><p>Per tornare agli altri argomenti puoi utilizzare i link del percorso o l''indice, oppure seguire il link per l''argomento successivo <LINK TYPE="INTERNAL" VALUE="14"><br />Indietro <LINK TYPE="INTERNAL" VALUE="5"></p>', 1, 1388768868, 0, '1_3', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_8', 8, 3, 0, 'Esercizio 1', 'NULL', 'Questa è una domanda a risposta multipla, il corsista dopo aver letto la domanda sceglie una delle possibili risposte. La correzione è automatica, in questo caso scegliendo una delle risposte potrete leggere il commento e visualizzare il commento dell''autore. <br>\nIn questa installazione di ADA per ripetere l''esercizio occorre essere autorizzati dal tutor (che può inviare al corsista ulteriori commenti e indicazioni), ma si può impostare il sistema in modo da rendere le domande sempre attive. Facciamo una prova.<br><br>\n<b>Cosa significa l''acronimo ADA?</b>', 30100, 1388768868, 1, '1_7', 0, 0, 0, 'NULL', 'NULL', 'NULL', 0, 0, 0, 0),
('1_9', 8, 3, 0, 'Ambiente Digitale di Apprendimento', 'NULL', 'Ci siamo quasi, non hai ottenuto nessun punteggio ma passa pure alla domanda successiva.', 1, 1388768868, 0, '1_8', 0, 0, 0, 'NULL', 'NULL', 'NULL', 50, 0, 0, 0),
('1_10', 8, 3, 0, 'Ambiente Digitale per l''Apprendimento.', 'NULL', 'Ottima risposta, hai ottenuto il punteggio massimo, passa pure alla domanda successiva', 1, 1388768868, 0, '1_8', 0, 0, 0, 'NULL', 'NULL', 'NULL', 100, 0, 0, 0),
('1_11', 8, 3, 0, 'Ambiente Digitale degli Apprendimenti', 'NULL', 'Risposta sbagliata, non hai ottenuto nessun punteggio ma passa pure alla domanda successiva.', 1, 1388768868, 0, '1_8', 0, 0, 0, 'NULL', 'NULL', 'NULL', 0, 0, 0, 0),
('1_12', 8, 3, 0, 'Esercizio 2', 'NULL', 'In questo caso abbiamo una domanda a risposta aperta, significa che il corsista deve inviare la propria risposta al tutor, il quale provvederà a correggerla assegnando un punteggio e a commentarla. <br>In questo caso specifico il corsista può inviare anche un file al tutor.', 52000, 1388768868, 2, '1_7', 0, 0, 0, 'NULL', 'NULL', 'NULL', 0, 0, 0, 0),
('1_13', 8, 3, 0, 'Benissimo, appena il tutor correggerà l''esercizio ti invierà un messaggio.', 'NULL', 'Passa agli altri argomenti del corso.', 1, 1388768868, 0, '1_12', 0, 0, 0, 'NULL', 'NULL', 'NULL', 0, 0, 0, 0),
('1_14', 9, 3, 0, '2. Gli strumenti', 'strumenti', '<p>Chi segue un corso con ADA ha a sua disposizione un set di strumenti molto variegato. Sostanzialmente si dividono in</p><ul><li>strumenti di navigazione,</li><li>strumenti di comunicazione e interazione,</li><li>strumenti di analisi.</li></ul><p><MEDIA TYPE="1" VALUE="home.jpg"></p><p>Questi strumenti consentono al corsista un controllo completo sui contenuti del corso e sulle attività che deve realizzare al suo interno; di partecipare alla creazione di un gruppo di apprendimento formato dal corsista stesso, dal tutor e dagli altri corsisti; di controllare il proprio percorso consultando i dati registrati dalla piattaforma.</p><p>Puoi andare direttamente all''argomento/lezione successiva <LINK TYPE="INTERNAL" VALUE="23"> oppure proseguire la lettura a cominciare dal primo approfondimento elencato nel pannello <a href="#" onclick="toggleElementVisibility(''menuright'', ''right'');"><img alt="" src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" />naviga</a>.</p>', 1, 1388768868, 0, '1_2', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_15', 10, 3, 0, '2.1 Navigare', 'strumenti', '<p>Gli strumenti di navigazione aiutano il corsista nella navigazione del corso, questi strumenti sono:</p><ul><li>il pannello di navigazione (si apre cliccando su Naviga nella barra dei menù),</li><li>l''indice (si apre tramite il pannello di navigazione o cliccando sul titolo del corso riportato nel percorso),</li><li>la mappa grafica (si apre tramite il pannello di navigazione ma solo se si è all''interno di un gruppo),</li><li>la funzione di ricerca (si apre cliccando sul menù Strumenti).</li></ul><p>Abbiamo già visto a cosa serve il <strong>pannello di navigazione</strong>, vediamo brevemente gli altri.</p><p>L''<strong>indice</strong> consente di avere una visione completa del corso, i contenuti si possono filtrare in base alla profondità, ordinare in modo diverso e si possono verificare le visite fatte: in questo modo è facile verificare quale parte del corso non si è ancora letta.</p><p><MEDIA TYPE="1" VALUE="pag_indice.jpg"></p><p>La <strong>mappa</strong> grafica visualizza graficamente la posizione e i legami tra i vari contenuti di un gruppo.</p><p><MEDIA TYPE="1" VALUE="pag_mappa.jpg"></p><p>Anche la <strong>ricerca</strong> è semplice da utilizzare, basta inserire la parola e ADA restituirà la lista dei nodi e gruppi del corso in cui è contenuta.</p><p><MEDIA TYPE="1" VALUE="pag_cerca.jpg"></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="16"></p><p>&#160;</p><p>&#160;</p>', 0, 1388768868, 0, '1_14', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_16', 11, 3, 0, '2.2. Comunicare', 'strumenti', '<p>Gli strumenti di comunicazione e di interazione consentono al corsista di comunicare e interagire con il tutor e gli altri corsisti all''interno del corso.</p><p>Possono essere divisi in due categorie:</p><ul><li>quelli strumentali all''organizzazione del corso come la <strong>messaggeria</strong> e l''<strong>agenda</strong>,</li><li>quelli di interazione vera e propria come la <strong>chat</strong>, la <strong>videochat</strong> il <strong>forum</strong> e l''area <strong>collabora</strong>.</li></ul><p>Questi ultimi consentono di trasformare un corso online in un percorso di apprendimento completo, in cui i corsisti non si limitano a studiare i contenuti e a realizzare gli esercizi, ma entrano a far parte di un gruppo di apprendimento in cui si confrontano, discutono e approfondiscono le tematiche affrontate con l''aiuto del tutor.</p><p>Ecco uan tabella di massima che consente di esemplificare le caratteristiche di questi strumenti.</p><table width="600" cellspacing="1" cellpadding="1" border="2"><tbody><tr><td>Caratteristiche strumento</td><td>Sincrono (richiede la presenza di tutti gli attori)</td><td>Asincrono (non è necessaria la presenza degli attori nello stesso momento)</td></tr><tr><td>da uno a uno</td><td><strong>chat<br />videochat</strong></td><td><strong>messagggeria<br />agenda</strong></td></tr><tr><td>da uno a tutti e viceversa</td><td><strong>chat<br />videochat</strong></td><td><p><strong>messaggeria<br />agenda<br />forum<br />area collabora</strong></p></td></tr></tbody></table><p></p><p>Prima di proseguire <LINK TYPE="INTERNAL" VALUE="22">, consulta gli approfondimenti nel pannello <a onclick="toggleElementVisibility(''menuright'', ''right'');" href="#"><img src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" alt="" />naviga</a>.<br />Indietro <LINK TYPE="INTERNAL" VALUE="15"></p>', 1, 1388768868, 0, '1_14', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_17', 12, 3, 0, '2.2.1 La messaggeria', 'strumenti', '<p>La <strong>messaggeria</strong> di ADA consente di inviare un messaggio sia all''interno della piattaforma che sulla casella di posta elettronica del destinatario.&nbsp;</p><p><MEDIA TYPE="1" VALUE="260712_150944_messaggeria_OK.jpg"></p><p>Un corsista pu&ograve; inviare un <strong>messaggio</strong> agli amministratori della piattaforma, all''autore del corso, al tutor della classe, agli altri corsisti e riceverli a sua volta.</p><p><MEDIA TYPE="1" VALUE="messaggio_OK.jpg"></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="18"></p>', 0, 1388768868, 0, '1_16', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_18', 13, 3, 0, '2.2.2 L''agenda', 'strumenti', '<p>L''<strong>agenda</strong> consente al tutor di segnare gli appuntamenti di chat o le scadenze di consegna a tutti gli studenti.</p><p><MEDIA TYPE="1" VALUE="agenda_OK.jpg"></p><p>Ma pu&ograve; anche essere usata per segnare <strong>appuntamenti o scadenze personali </strong>dagli stessi corsisti.</p><p><MEDIA TYPE="1" VALUE="appuntamento_OK.jpg"></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="19"><br />Indietro <LINK TYPE="INTERNAL" VALUE="17"></p>', 0, 1388768868, 0, '1_16', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_19', 14, 3, 0, '2.2.3 La chat e la videochat', 'strumenti', '<p>La <strong>chat</strong> di ADA &egrave; di tipo testuale e piuttosto intuitiva. La chat &egrave; sempre attiva, il corsista pu&ograve; utilizzarla in qualsiasi momento,di solito &egrave; il tutor che cura l''agenda delle chat e comunica giorni e orari, ma nulla vieta che i corsisti si accordino tra di loro e si prendano un appuntamento. Tutte le conversazioni vengono memorizzate e il tutor pu&ograve; accedere al report della chat in qualsiasi momento, potr&agrave; poi decidere se condividerle con la classe tramite il forum o meno. Il tutor pu&ograve; anche creare delle stanze di chat riservate consentendone l''accesso solo ad un numero ristretto di persone in una data e orario definito.</p><p><MEDIA TYPE="1" VALUE="chat_OK.jpg"></p><p>Oltre alla chat testuale &egrave; possibile attivare la <strong>videochat</strong>: un sistema di videoconferenza vero e proprio che consente l''interazione audio e/o video e consente l''utilizzo di una lavagna condivisa.</p><p><MEDIA TYPE="1" VALUE="videochat.jpg"></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="20"><br />Indietro <LINK TYPE="INTERNAL" VALUE="18"></p>', 1, 1388768868, 0, '1_16', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_20', 15, 3, 0, '2.2.4 Il forum', 'strumenti', '<p>Come abbiamo già detto il forum dentro ADA è completamente integrato nei contenuti.</p><p>Un corsista (o un tutor) può aprire una discussione in qualsiasi punto del corso, basta cliccare sul menù agisci e poi su aggiungi nota. Le note possono essere pubbliche, cioè visibili a tutta la classe, oppure personali, visibili solo a chi le ha inserite. Queste note personali possono restare private oppure essere promosse a pubbliche in qualsiasi momento. Le note possono essere eliminate da chi le ha inserite (ma solo se nessuno ha risposto), il testo può esser formattato e arrichito di link e media.</p><p>Per avere una visione d''insieme del forum si può aprire l''<strong>indice forum</strong> tramite il menù Comunica, tutte le note presenti nel corso vengono visualizzate in base al thread o alla data di inserimento e si possono leggere comodamente.</p><p><MEDIA TYPE="1" VALUE="forum_indice_OK.jpg"></p><p>Il forum è lo spazio di discussione e di approfondimento per antonomasia all''interno di un corso, in molti corsi parecchie attività didattiche vengono realizzate con l''ausilio del forum. ADA facilita particolarmente questo uso del forum come strumento di collaborazione e di crescita in una ottica costruttivista, nel caso di un contributo forum particolarmente significativo e ben fatto da parte di un corsista, il tutor può proporne la promozione a nodo del corso, se l''autore lo giudica valido diventerà parte del corso, visibile a tutti corsisti e non solo a quelli appartenenti alla stessa classe.</p><p>Avanti <LINK TYPE="INTERNAL" VALUE="21"><br />Indietro <LINK TYPE="INTERNAL" VALUE="18"></p>', 0, 1388768868, 0, '1_16', 0, 0, 0, '/var/www/html/adaInstall/services/media/3/forum_indice_OK.jpg', 'NULL', 'NULL', 0, 0, 0, 0),
('1_21', 16, 3, 0, '2.2.5 L''area collabora', 'strumenti', '<p>Cliccando su <strong>area collabora</strong> dal men&ugrave; Comunica si apre l''elenco dei file inviati dalla classe.</p><p><MEDIA TYPE="1" VALUE="collabora_OK.jpg"></p><p>Per inviare un file occorre trovarsi all''interno di un nodo, di un gruppo o di una nota. Qui vengono solo elencati in base al nome del file (ADA aggiunge il numero di ID del nodo al nome del file originale), il nome utente di chi lo ha inviato e il nodo a cui &egrave; stato collegato.</p><p>Questa funzione consente ai corsisti e al tutor di condivere direttamente i file all''interno della piattaforma e commentarli tramite il forum.</p><p>Avanti <LINK TYPE="INTERNAL" VALUE="22"><br />Indietro <LINK TYPE="INTERNAL" VALUE="20"></p>', 0, 1388768868, 0, '1_16', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_22', 17, 3, 0, '2.3 Analizzare', 'strumenti', '<p>Ci sono infine una serie di strumenti che riguardano solo il corsista:</p><ul><li>il report navigazione,</li><li>lo storico degli esercizi</li><li>e il diario.</li></ul><p>Se un corsista lo desidera può stampare il contenuto di un nodo su carta.</p><p>Grazie al <strong>report</strong> il corsista può consultare in ogni momento la propria cronologia di navigazione, questa cronologia è visibile anche al tutor. Anche gli <strong>esercizi</strong> realizzati sono visibili, insieme agli eventuali commenti del tutor.</p><p><MEDIA TYPE="1" VALUE="cronologia_OK.jpg"></p><p>Il <strong>diario</strong> infine è una sorta di blocco appunti personali, cliccando sulla voce diario dal menù strumenti lo studente può entrare nel proprio diario personale e scrivere i propri commenti, ADA salva automaticamente il testo che può essere consultato durante gli accessi successivi, è completamente privato e può essere letto solo dal corsista.</p><p><MEDIA TYPE="1" VALUE="diario_OK.jpg"></p><p>Usa i link del percorso per cambiare tematica oppure vai direttamente all''argomento successivo <LINK TYPE="INTERNAL" VALUE="23"><br />Indietro <LINK TYPE="INTERNAL" VALUE="21"></p>', 0, 1388768868, 0, '1_14', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_23', 18, 3, 0, '3. I ruoli', 'ruoli', '<p>In una piattaforma di e-learning sono normalmente disponibili vari ruoli utente per gestire in maniera differenziata le funzionalità esistenti. Esiste di solito un <em>docente</em>, che a volte è responsabile sia dei contenuti che della didattica. In ADA invece la distinzione tra chi inserisce i contenuti (<strong>autore</strong>) e chi gestisce il gruppo di apprendimento (<strong>tutor</strong>) è nettamente distinta.</p><p>La particolarità di ADA sta poi nel distinguere <strong>due livelli amministrativi</strong>: l''amministratore della piattaforma e l''amministratore dei provider dei corsi.</p><p>Leggi gli approfondimenti aprendo il pannello <a onclick="toggleElementVisibility(''menuright'', ''right'');" href="#"><img src="http://localhost/adaInstall/layout/ada_blu/img/naviga.png" alt="" />naviga</a>.</p>', 1, 1388768868, 0, '1_2', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_24', 19, 3, 0, '3.1 Il corsista', 'ruoli', '<p><span class="alignleft"><MEDIA TYPE="1" VALUE="corsista.jpg"></span> Il corsista può registrarsi ad ADA in maniera autonoma ad ADA e, sempre in maniera autonoma, può iscriversi ad un corso. Sarà poi l''amministratore del provider ad accettare la sua richiesta e ad autorizzarlo ad accedere al corso.</p><p>Con ADA però è possibile automatizzare completamente questo processo: le iscrizioni possono essere gestite in maniera automatica tramite dei <strong>codici di accesso</strong>, il pagamento della quota d''iscrizione può essere fatto tramite <strong>paypal</strong>, l''invio dell''attestato di partecipazione viene fatto in automatico dal sistema una volta che il corsista ha completato il percorso di studio.</p><p>Una volta che un corsista è registrato e iscritto ad un corso potrà interagire con i contenuti, il tutor e gli altri corsisti fino a completare il corso.</p><p><font color="#FFFFFF">.</font></p><p></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="25"></p>', 0, 1388768868, 0, '1_23', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_25', 20, 3, 0, '3.2 Il tutor', 'ruoli', '<p><span class="alignright"><MEDIA TYPE="1" VALUE="tutor.jpg"></span></p><p>Il tutor all''interno di ADA ha un ruolo centrale: è il fulcro del corso, la persona che trasforma un insieme di corsisti in un gruppo di apprendimento.</p><p>Ha quindi a disposizione degl<strong>i strumenti di analisi e di interazione</strong> molto raffinati. Può navigare nel corso come il corsista ma ha allo stesso tempo una visione d''insieme; ad esempio di una domanda a riposta multipla non solo può leggere il testo e le risposte con le relative indicazioni di correttezza, ma anche visualizzare il numero di studenti che le hanno scelte.</p><p>Può accedere al report globale delle attività delle classi e a quello personale di ogni corsista. Può correggere e commentare gli esercizi, abilitare i corsisti, gestire i livelli, proporre la promozione delle note forum... insomma ha la possibilità di monitorare costantamente l''andamento del corso e può organizzarne il lavorodandogli tutti gli input necessari.</p><p><strong>L''ampiezza del suo ruolo dipende in buona parte dal modo in cui il corso è stato organizzato dall''autore ma tendenzialmente ADA facilita una didattica di tipo costruttivista e collaborativo.</strong></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="26"><br />Indietro <LINK TYPE="INTERNAL" VALUE="24"></p>', 0, 1388768868, 0, '1_23', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_26', 21, 3, 0, '3.3 L''autore', 'NULL', '<p><span class="alignright"><MEDIA TYPE="1" VALUE="autore.jpg"></span> L''autore (o team di autori) possono aggiungere contenuti di ogni tipo al corso che gli è stato assegnato e strutturarlo come meglio desiderano stabilendone le modalità e i tempi di interazione.</p><p>Creare un corso dentro ADA non significa semplicemente <strong>organizzare dei contenuti,</strong> magari già pronti e pensati per altri contesti, ma ripensarli completamente organizzandone la <strong>fruizione</strong> in modo da facilitare la realizzazione di un <strong>percorso di apprendimento.</strong></p><p>L''autore deve anche stabilire tempi e modalità delle verifiche di apprendimento e indicare al tutor le modalità con cui organizzare l''interazione con i corsisti.</p><p>Ad uno stesso corso inoltre, possono lavorare <strong>autori diversi</strong>, ognuno con il proprio account di accesso personale.</p><p>Avanti <LINK TYPE="INTERNAL" VALUE="27"><br />Indietro&#160;<LINK TYPE="INTERNAL" VALUE="25"></p>', 0, 1388768868, 0, '1_23', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_27', 22, 3, 0, '3.4 L''amministratore del provider', 'NULL', '<p><span class="alignleft"><MEDIA TYPE="1" VALUE="manager_provider.jpg"></span> ADA consente&#160; di <strong>gestire più fornitori di corsi</strong> (<em>provider</em>) all''interno di una sola installazione, con evidenti risparmi e facilità di gestione in situazioni complesse, come più dipartimenti indipendenti dello stesso ente, oppure portali tematici con fornitori indipendenti.</p><p>L''amministratore del provider può: creare i corsi e assegnarli agli autori, creare le istanze di corso e assegnarle ai tutor, gestire le registrazioni ad ADA e le iscrizioni alle istanze.</p><p>Ha inoltre a disposizione una serie di <strong>strumenti di reportistica</strong> sugli utenti registrati e sui corsi presenti all''interno della piattaforma.</p><p><font color="#FFFFFF">.</font></p><p></p><p>Avanti <LINK TYPE="INTERNAL" VALUE="28"><br />Indietro <LINK TYPE="INTERNAL" VALUE="26"></p>', 0, 1388768868, 0, '1_23', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_28', 23, 3, 0, '3.5 L''amministratore', 'NULL', '<p><span class="alignleft"><MEDIA TYPE="1" VALUE="amministratore_piattaforma.jpg"></span> L''amministratore della piattaforma può <strong>abilitare i provider</strong> alla gestione dei corsi e stabilirne caratteristiche e poteri.</p><p>E'' inoltre l''unico utente che, grazie ad una serie di<strong> strumenti di reportistica</strong>, può consultare e visualizzare tutti i dati relativi agli  utenti registrati e ai corsi presenti all''interno della piattaforma.</p><p>Il nostro corso è terminato. Grazie per averci seguito fin qui, per qualsiasi richiesta di informazioni puoi scrivere a <strong>info@lynxlab.com</strong></p><p><font color="#FFFFFF">.</font></p><p><font color="#FFFFFF">.</font></p><p></p><p></p><p></p><p></p><p></p><p>Indietro <LINK TYPE="INTERNAL" VALUE="27"></p>', 0, 1388768868, 0, '1_23', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_29', 8, 3, 0, 'Esempio di Test Completo', 'NULL', '<p><a href="http://localhost/adaInstall/modules/test/index.php?id_test=1">http://localhost/adaInstall/modules/test/index.php?id_test=1</a></p>', 0, 1388768868, 1, '1_2', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_30', 24, 3, 0, 'I manuali', 'ADA 2.0, manuale', '<p><span style="font-family: ''Lucida Sans'', Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: small;">Benvenuti nella versione online del manuale utenti di</span><span style="font-family: ''Lucida Sans'', Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: small;">&#160;</span><strong style="font-family: ''Lucida Sans'', Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: small;">ADA 2.0</strong><span style="font-family: ''Lucida Sans'', Verdana, Geneva, Arial, Helvetica, sans-serif; font-size: small;">.</span></p><div class="firstnode" style="margin: 0px; padding: 0px; border: 0px; font-family: ''Lucida Sans'', Verdana, Geneva, Arial, Helvetica, sans-serif; vertical-align: baseline; font-size: small;"><p>Ad ogni tipologia di utente è dedicato un capitolo specifico del manuale:</p><ul style="font-style: inherit; border: 0px; font-weight: inherit; font-family: inherit; vertical-align: baseline;"><li style="font-style: inherit; border: 0px; font-weight: inherit; font-family: inherit; text-align: left; vertical-align: baseline;">amministratore</li><li style="font-style: inherit; border: 0px; font-weight: inherit; font-family: inherit; text-align: left; vertical-align: baseline;">switcher</li><li style="font-style: inherit; border: 0px; font-weight: inherit; font-family: inherit; text-align: left; vertical-align: baseline;">autore</li><li style="font-style: inherit; border: 0px; font-weight: inherit; font-family: inherit; text-align: left; vertical-align: baseline;">tutor</li><li style="font-style: inherit; border: 0px; font-weight: inherit; font-family: inherit; text-align: left; vertical-align: baseline;">studente</li></ul><p>Le indicazioni di uso riguardo l''agenda e la messaggeria sono presentate in un capitolo a parte, poiché sono valide per tutte (o quasi) le tipologie di utente.</p><p>Potete scegliere l''argomento che vi interessa cliccando sui gruppi elencati nel pannello di sinistra (per aprire e chiudere il pannello cliccare sul menù naviga); aprendo l''indice; aprendo la mappa grafica, seguendo i link tra i vari nodi.</p><p><strong>Buona lettura!</strong></p></div><div id="go_next" style="margin: 0px; padding: 0px; border: 0px; font-family: ''Lucida Sans'', Verdana, Geneva, Arial, Helvetica, sans-serif; vertical-align: baseline; font-size: small;"></div><p></p>', 1, 1388768868, 0, '1_1', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_31', 1, 3, 0, '1. Premessa', 'premessa', '<p><strong>ADA</strong> - <strong>Ambiente Digitale per l''Apprendimento</strong> - è un sistema software che consente di creare, amministrare e fruire corsi a distanza via Internet o Intranet.</p><p>Non si tratta di corsi per e-mail o di lezioni video pre-registrate, ma di un vero ambiente di apprendimento collaborativo integrato. Come altri sistemi di questo tipo permette agli studenti, suddivisi in classi, di:</p><ul><li>consultare materiali didattici strutturati (testi, immagini, audio, video, animazioni),</li><li>eseguire esercitazioni,</li><li>interagire con il tutor e con gli altri corsisti con degli strumenti di comunicazione integrati (messaggeria, agenda, forum e chat),</li><li>condividere documenti di qualsiasi tipo grazie ad un repository integrato.</li></ul><p>Tuttavia <strong>ADA</strong> è assolutamente unica. I principali punti di forza, che lo distinguono da tutti gli altri prodotti, sono questi.</p><ul><li>E'' stato ideato e prodotto integralmente in Italia: pertanto documentazione e assistenza sono in italiano, mentre la lingua usata per la messaggistica agli utenti può essere scelta.</li><li>E'' completamente web-based: può quindi essere gestito e usato completamente online utilizzando un qualsiasi web browser (Explorer, Netscape, Opera), da qualsiasi macchina, con qualsiasi sistema operativo, senza bisogno di ulteriori software o plugin da scaricare e installare.</li><li>Contiene un modulo autore per la creazione dei corsi estremamente semplice ma insieme molto potente.</li><li>La struttura ipertestuale del corso è visibile sia in forma di indice che in forma di mappa grafica, permettendo una navigazione intuitiva.</li><li>L''interfaccia è indipendente dal sistema e completamente configurabile dall''amministratore attraverso programmi standard.</li><li>E'' multipiattaforma, perché poggia su database SQL ed è scritto in PHP, disponibili sia sotto Windows che sotto Unix (Linux).</li><li>I corsi sono riusabili con altre piattaforme: il formato principale di ADA è l''XML</li></ul>', 1, 1388768868, 1, '1_30', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_32', 1, 3, 0, '1.1 Le caratteristiche innovative di ADA', 'premessa', '<p><strong>ADA</strong> è un''applicazione multistrato, in cui cioè l''interfaccia utente, la logica e i dati sono del tutto indipendenti. Questa caratteristica da un lato permette la personalizzazione da parte dell''utente, dall''altro garantisce la possibilità di aggiornamento futuro.<br /><br /><strong>ADA</strong> è scritto esclusivamente in PHP, un linguaggio di scripting dal lato server paragonabile all''ASP. PHP è sempre più diffuso nel mondo, sia per la sua potenza e velocità, sia per la disponibilità per sistemi operativi diversi (Unix, Windows, Solaris), sia perché rilasciato secondo la licenza GPL. ADA non fa uso di altri linguaggi (Java etc) che richiedono plugin o servlet particolari; non richiede configurazioni speciali del server Web su cui gira né versioni particolari dei browser dell''utente. E'' stato testato su tutti i browser disponibili attualmente.<br /><br /><strong>ADA</strong> può appoggiarsi a qualsiasi database SQL preesistente sulla macchina in cui gira o anche su una macchina remota. Il suo livello di astrazione dai dati è tale da permettere di interfacciarsi a Oracle come a Microsoft SQLserver, a MySql come a Postgres. Il numero massimo di utenti e le dimensioni complessive dei dati che può gestire dipendono solo dal DataBase utilizzato e dalla banda disponibile. Inoltre ADA gestisce internamente le transazioni in modo da garantire la robustezza del sistema indipendentemente da quella del Database.<br /><br /><strong>ADA</strong> permette di configurare completamente l''interfaccia di fruizione dei corsi, attraverso una serie di template HTML che possono essere modificati o ricreati da capo dall''amministratore ADA semplicemente usando un qualsiasi editor HTML. E'' possibile quindi personalizzare completamente le pagine per renderle omogenee con uno stile. L''uso di Cascaded Style Sheets (CSS) permette poi ulteriori livelli di gestione omogenea dell''interfaccia delle vari parti del sistema.</p><p>La versione base di <strong>ADA</strong> produce dinamicamente pagine HTML validate secondo lo standard W3C. Ma il formato interno dei dati è ancora più standard: si tratta di XML 1.1, che permette quindi di creare interfacce specializzare per qualsiasi dispositivo di navigazione su web, da WAP a GPRS.</p>', 0, 1388768868, 1, '1_31', 0, 0, 2, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_33', 1, 3, 0, '1.2 I moduli di gestione', 'premessa', '<p>Il sistema è strutturato modularmente. Ciascun modulo assolve ad una serie di funzioni omogenee. Alcuni moduli sono di servizio e svolgono funzioni trasparenti all''utente.<br /><u><br />Modulo Studente</u> <LINK TYPE="INTERNAL" VALUE="141"><br />Il modulo consente la generazione dinamica delle pagine web, che avviene dopo aver "filtrato" e personalizzato i contenuti in funzione di una serie di parametri, tra i quali il livello di apprendimento a cui è giunto il corsista, gli esercizi eseguiti correttamente, la percentuale di unità didattiche visitata, ecc.<br /><br />Questo modulo permette agli studenti di:</p><ul><li>fruire i contenuti del corso (navigazione negli indici, nelle mappe grafiche, nelle unità didattiche, esecuzione di esercizi, ecc.);</li><li>assegnare tag specifiche ai contenuti del corso, visualizzarle, cancellarle</li><li>effettuare ricerche nei contenuti del corso in modalità full-text o per parole chiave</li><li>effettuare ricerche nel corredo multimediali del corso;</li><li>accedere alle chat di classe e alle chat riservate del corso specifico che si sta seguendo;</li><li>inviare messaggi agli altri studenti della classe;</li><li>segnare appuntamenti privati o pubblici;</li><li>accedere ai forum di discussione;</li><li>inviare documenti all''area repository del corso per condividerli con il tutor e con il resto della classe;</li><li>accedere alle chat pubbliche della piattaforma;</li><li>formulare richieste ai tutor, agli autori o all''amministrazione relativamente ai servizi di ADA.</li></ul><p><u>Modulo Tutor</u>&#160; <LINK TYPE="INTERNAL" VALUE="116"><br />Per quanto riguarda la navigazione dei contenuti del corso e l''utilizzo degli strumenti di comunicazione e interazione il tutor ha a disposizione gli stessi strumenti degli studenti, anche se ha dei permessi in più.<br /><br />Il modulo permette al tutor della classe di:</p><ul><li>navigare nel corso per visualizzare gli accessi degli studenti per nodo;</li><li>accedere ai dati statistici globali relativi alla classe;</li><li>monitorare l''andamento dell''attività didattica dei singoli studenti;</li><li>visualizzare e correggere gli esercizi svolti;</li><li>integrare la correzione dei test con commenti, suggerimenti, ecc</li><li>inviare messaggi interni agli studenti, agli altri tutor, agli autori e all''amministrazione;</li><li>segnare appuntamenti privati o pubblici;</li><li>accedere ai forum di discussione e proporre all''autore la la promozione delle note forum più interessanti a nodi;</li><li>inviare documenti nell''area repository;</li><li>partecipare alla chat di classe e impostare le chat riservate, accedere al log delle chat.</li></ul><p><u>Modulo Autore</u> <LINK TYPE="INTERNAL" VALUE="80"><br /><br />Questo modulo online, accessibile solamente all''autore del corso, permette di:</p><ul><li>inviare al sistema ADA il corso realizzato precedentemente con il modulo Autore offline per la sua pubblicazione;</li><li>creare un corso direttamente online;</li><li>modificare e aggiornare i contenuti del corso;</li><li>navigare nel corso per verificarne l''accessibilità e visualizzare il numero di accessi per nodo;</li><li>visualizzare un report sintetico dell''accesso a tutti i propri corsi;</li><li>promuovere a nodo una delle note forum proposte dal tutor;</li><li>accedere alla chat riservate;</li><li>inviare messaggi interni agli studenti, ai tutor, agli altri autori e all''amministrazione.</li></ul><p><u>Modulo Switcher</u> <LINK TYPE="INTERNAL" VALUE="49"></p><p>Questo modulo online permette agli switcher di:</p><ul><li>controllare gli accessi alle diverse aree;</li><li>consultare i report generali (accessi, nodi, corsi, utenti);</li><li>gestire i corsisti, i tutor, gli autori;</li><li>creare i corsi e assegnarli agli autori;</li><li>creare le istanze dei corsi e assegnarli ai tutor</li><li>gestire le iscrizioni dei corsisti.</li></ul><p><u>Modulo Amministratore</u> <LINK TYPE="INTERNAL" VALUE="36"><br />Questo modulo online permette agli amministratori di:</p><ul><li>controllare gli accessi alle diverse aree;</li><li>gestire i corsisti, i tutor, gli autori, gli switcher e gli amministratori;</li><li>creare i provider dei servizi;</li><li>creare i servizi e assegnarli ai provider;</li><li>editare i testi della home page;</li><li>importare una nuova lingua per l''interfaccia.</li></ul><p></p>', 0, 1388768869, 2, '1_31', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_34', 1, 3, 0, '1.3 Le caratteristiche tecniche', 'premessa', '<p>Se avete intenzione di installare e utilizzare ADA in maniera autonoma, i requisiti necessari al funzionamento del sistema sono flessibili. E'' necessario che siano installati nel sistema:</p><ul><li>un qualsiasi server web (Apache, Microsoft IIS, Xitami);</li><li>Php versione 4.06 o successive (consigliata: 4.1.2);</li><li>un Data Base SQL (MSSql, MySQL, Postgres, Oracle, ecc.).</li></ul><p>Per ulteriori informazioni vi rimandiamo a questa pagina:&#160;<MEDIA TYPE="4" VALUE="http://ada.lynxlab.com"></p><p>I&#160;requisiti per la navigazione in ADA (per studenti, tutor, switcher e amministratori) sono estremamente semplici:</p><ul><li>collegamento Internet;</li><li>browser Internet vers. 4.0 o superiore. (Opera, Firefox, Explorer, Chrome).</li></ul><p>Naturalmente se si prevede di utilizzare la videochat è necessaria una webcam e un microfono, per il resto delle funzioni un normale PC o un normale notebook sono più che sufficienti.</p>', 0, 1388768869, 3, '1_31', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_35', 1, 3, 0, '2. La home page di ADA', 'ADA 2.0', '<p>La<strong> Home Page</strong> (<em>index.php</em>) di <strong>ADA</strong> è l''indirizzo da cui è possibile entrare nei corsi. I template HTML di cui è composta sono personalizzabili a piacimento, fermo restando le operazioni base che elenchiamo qui.</p><p>Il presente manuale riguarda unicamente la gestione di <strong>ADA</strong> tramite i moduli online e gli strumenti di comunicazione previsti all''interno della piattaforma, non riguarda quindi la gestione del database e l''installazione della piattaforma per cui rimandiamo al manuale d''installazione.</p><p>Per ragioni pratiche le funzioni sono descritte a partire dall’interfaccia standard di <strong>ADA</strong> (una installazione aperta e visibile è raggiungibile seguendo questo indirizzo <MEDIA TYPE="4" VALUE="http://localhost/adaInstall"> ), in realtà questa interfaccia è completamente personalizzabile.</p><p>Per entrare nei corsi basta digitare il proprio username e la propria password e poi cliccare su Accedi. Se l''installazione ha più lingue attive, si può scegliere la lingua dell''interfaccia. A seconda del tipo di utente che corrisponde allo username digitato il sistema invia direttamente nel modulo corrispondente: amministratore, switcher, autore, tutor o studente.</p><p>Se non si è un utente registrato ci si può registrare cliccando sul menù <strong>Registrati</strong>, corrispondete al form in PHP <em>registration.php</em>. Con questo form ci si può registrare solo come studente. Si compilano i campi e poi si clicca su Invia richiesta per confermare. A questo punto il sistema invierà una mail all''indirizzo indicato, questa mail contiene un link per la conferma della registrazione, cliccando sul link si aprirà una finestra in cui inserire la propria password, a questo punto la procedura di registrazione è completata e si aprirà immediatamente la propria Home Page di studente.</p><p>La registrazione come <strong>amministratore, switcher, autore e tutor </strong>può essere fatta solo dall’amministratore del sistema.</p><p>Nel caso un utente dimentichi la propria password si puà cliccare su link<strong> Hai dimenticato la tua password? </strong>indicare il proprio indirizzo mail e seguire la procedura indicata nella mail che il sistema invierà automaticamente.</p><p>Per visualizzare la lista dei corsi attivi basta cliccare sul menù <strong>informazioni</strong> (<em>info.php</em>).</p><p>I corsi sono elencati per <strong>Nome</strong> e <strong>Descrizione</strong>, cliccando sul link <u>more info</u> in corrispondenza della colonna <strong>Informazioni</strong> si apre l''elenco delle istanze di corso attive, cioè delle edizioni del corso cui è possibile iscriversi.</p><p>I testi contenuti nella home (<em>index.php</em>) possono essere modificati dall''amministratore.</p>', 0, 1388768869, 2, '1_30', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_36', 1, 3, 0, '3. Il modulo Amministratore', 'amministratore', '<p>Il modulo permette all’<strong>amministratore</strong> del sistema di:</p><ul><li>gestire i corsisti, i tutor, gli autori, gli switcher e gli amministratori;</li><li>creare i provider dei servizi;</li><li>creare i servizi e assegnarli ai provider;</li><li>editare i testi della home page;</li><li>importare una nuova lingua per l''interfaccia.</li></ul><p>Per entrare in ADA come amministratore occorre essere abilitati da un altro amministratore del sistema, il quale fornisce un nome utente e una password personale. Dopo aver digitato questi dati e aver cliccato su <strong>Entra</strong> l’amministratore accede alla propria home page.</p>', 1, 1388768869, 3, '1_30', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0);
INSERT INTO `nodo` (`id_nodo`, `id_posizione`, `id_utente`, `id_istanza`, `nome`, `titolo`, `testo`, `tipo`, `data_creazione`, `ordine`, `id_nodo_parent`, `livello`, `versione`, `n_contatti`, `icona`, `colore_didascalia`, `colore_sfondo`, `correttezza`, `copyright`, `lingua`, `pubblicato`) VALUES
('1_37', 1, 3, 0, '3.1 La home page del provider', 'amministratore', '<p>Un amministratore in ADA può gestire tutti gli utenti, crearne di nuovi, assegnare i servizi ai provider. E'' l''unico che può abilitare un utente come <em>switcher</em>. Inoltre può aggiungere nuove lingue.<br /><br />Il sistema visualizzare sempre (in alto a sinistra) il punto in cui ci si trova e (in alto a destra) in nome <strong>utente</strong> con cui ci si è collegati, il <strong>tipo</strong> (se amministratore, switcher, autore, tutor o studente), lo <strong>status</strong>.<br /><br />Le voci di menù in alto alla pagina sono sempre presenti, contrassegnate da delle icone sono:</p><ul><li><strong>home</strong> (per tornare alla home in ogni momento);</li><li><strong>agisci</strong> (i contenuti di questo menù si modificano a seconda del punto in cui ci si trova);</li><li><strong>help</strong> (per aprire la pagina delle informazioni e dei credits della piattaforma);</li><li><strong>esci</strong> (per chiudere la sessione di lavoro).</li></ul><p>Nella propria home page l''amminstratore può consultare l''elenco di tutti i provider abilitati. I provider sono elencati per <strong>nome</strong> ed è possibile aprirne il <strong>profilo</strong> (cliccando sul link omonimo in corrispondenza della colonna <em>azioni</em>) e visualizzare il numero di <em>utenti</em> e di sessioni attive.<br /><br />Può inoltre compiere una serie di operazioni grazie al menù <strong>agisci</strong>:</p><ul><li><strong>aggiungi provider</strong> (per aggiungere un nuovo provider);</li><li><strong>aggiungi servizio</strong> (per aggiungere un nuovo servizio);</li><li><strong>aggiungi utente </strong>(per registrare un utente e assegnargli il ruolo di amministratore, switcher, autore, tutor o corsista);</li><li><strong>edit home page </strong>(per modificare e personalizzare il testo della home page);</li><li><strong>importa una lingua</strong> (per aggiungere una nuova lingua dell''interfaccia oltre a quelle già presenti).</li></ul><p>In basso sono presenti le etichette <strong>messaggi per te</strong> e<strong> i tuoi appuntamenti</strong>, anche se l''amministratore non ha a disposizione la messaggeria e neanche l''agenda.<br /><br />In basso si trova la freccia per tornare all''inizio della pagina e le informazioni sul sistema e sugli sviluppatori di ADA.</p>', 0, 1388768869, 1, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_38', 1, 3, 0, '3.2 Il profilo del provider', 'amministratore', '<p>Nell''installazione standard di ADA ci sono due profili di provider: il <strong>provider 0</strong> e il <strong>provider 1</strong>.</p><p>Il <strong>provider 0 </strong>gestisce la tipologia del corso aperto, un corso cioè che si può navigare senza essere necessariamente registrati sulla piattaforma. Se volete creare un corso aperto dovete quindi crearlo entrando come questo tipo di provider e assegnarlo ad un autore gestito da quello stesso provider. &#160;.</p><p>Il <strong>provider 1 </strong>gestisce la tipologia del corso normale, sono corsi per cui è necessario essere registrati e per cui occorre creare una istanza (una classe) ed affidarla ad un tutor.</p><p>Aprendo il profilo del provider se ne visualizzano tutte le informazioni: ID, nome, ragione, sociale, indirizzo, provincia, città, nazione, telefono, e-mail, descrizione, responsabile, client del database a cui è abbinato.<br /><br />E'' possibile aggiornare questi dati cliccando sul link <strong>modifica</strong> in fondo alla scheda, oppure tramite il menù <strong>agisci</strong> cliccando su <strong>modifica il profilo del provider</strong>.<br /><br />In basso sono elencati i corsi che quel provider gestisce, il link per associare o dissociare un servizio, il numero di utenti assegnati a quel provider e il link per aprire la lista. Queste azioni (gestire l''associazione dei corsi e aprire la lista degli utenti) sono possibile anche tramite il menù <strong>agisci</strong>.</p>', 1, 1388768869, 2, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_39', 1, 3, 0, '3.2.1 Modificare il profilo del provider', 'amministratore', '<p>Per modificare il profilo di un provider basta cliccare sul link <strong>modifica</strong> in fondo alla sua scheda oppure cliccare sul menù<strong> agisci &gt; modifica il profilo del provider.</strong></p><p>Una volta completate le modifiche per confermarle è sufficiente cliccare sul pulsante <strong>Invia</strong>.</p><p>Se non si vuole procedere nelle modifiche basta cliccare su Profilo del provider che appare nel percorso in alto a sinistra, appena sotto la barra dei menù.</p>', 0, 1388768869, 1, '1_38', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_40', 1, 3, 0, '3.2.2 Gestire le associazioni dei corsi', 'amministratore', '<p>Per cambiare o aggiornare la tipologia di servizi assegnati ad un provider. I servizi sono delle ????</p><p>Per cambiare quindi i servizi basta cliccare sul link <strong>associa/disassocia un servizio </strong>in fondo alla scheda oppure cliccare sul menù <strong>agisci &gt; gestisci associazioni corsi</strong>.</p><p>Una volta aperto basta selezionare o deselezionare...</p><p>Se non si vuole procedere nelle modifiche basta cliccare su Profilo del provider che appare nel percorso in alto a sinistra, appena sotto la barra dei menù.</p>', 0, 1388768869, 2, '1_38', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_41', 1, 3, 0, '3.2.3 La lista degli utenti', 'amministratore', '<p>Quando ci si trova dentro la scheda di un provider è possibile effettuare alcune operazioni sugli utenti che gestisce. Per prima cosa occorre aprirne la lista cliccando su link <strong>Lista utenti </strong>oppure cliccare sul menù<strong> agisci &gt; lista utenti.</strong></p><p>Comparirà in questo modo la lista degli utenti gestiti da quel provider, possono essere ordinati in base al numero di ID, al nome, al cognome, all''indirizzo e-mail, allo username e al ruolo che hanno all''interno del sistema (amministratore, switcher, autore, tutor, studente).</p><p>Se non si vuole procedere nelle modifiche basta cliccare su Profilo del provider che appare nel percorso in alto a sinistra, appena sotto la barra dei menù.</p>', 1, 1388768869, 3, '1_38', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_42', 1, 3, 0, '3.2.3.1 Filtrare la lista degli utenti', 'amministratore', '<p>Se gli utenti sono molti possono esser filtrati in base al ruolo cliccando sul menù <strong>agisci</strong> e poi cliccare sulla lista che interessa: amministratori, switcher, autori, tutor, studenti.</p><p>In questo modo si visualizzeranno solo gli utenti con quel determinato ruolo.</p>', 0, 1388768869, 0, '1_41', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_43', 1, 3, 0, '3.2.3.2 Modificare i dati di un utente', 'amministratore', '<p>Per modificare i dati di un utente basta cliccare sul link <strong>Modifica</strong> corrispondente sotto la colonna <strong>Azioni</strong>.</p><p>Una volta completate le modifiche per confermarle è sufficiente cliccare sul pulsante <strong>Invia</strong>.</p><p>Se non si vuole procedere nelle modifiche basta cliccare su Lista utenti che appare nel percorso in alto a sinistra, appena sotto la barra dei menù.</p>', 0, 1388768869, 0, '1_41', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_44', 1, 3, 0, '3.3 Aggiungere un provider', 'amministratore', '<p>Per aggiungere un nuovo provider dentro ADA, dalla home page, basta cliccare  sul menù <strong>agisci &gt; aggiungi provider</strong>.</p><p>Una volta compilati tutti i campi per confermare l''inserimento è sufficiente cliccare sul pulsante <strong>Invia</strong>.</p><p>Per annullare l''inserimento basta clicare sul menù <strong>Home</strong> e sul link <strong>Home dell''amministratore</strong> presente nel percorso in alto a sinistra, appena sotto la barrà dei menù.</p>', 0, 1388768869, 3, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_45', 1, 3, 0, '3.4 Aggiungere un servizio', 'amministratore', '<p>Per aggiungere un nuovo servizio dentro ADA, dalla home page, basta cliccare  sul menù <strong>agisci &gt; aggiungi servizio</strong>.</p><p>Una volta compilati tutti i campi per confermare l''inserimento è sufficiente cliccare sul pulsante <strong>Invia</strong>.</p><p>Per annullare l''inserimento basta clicare sul menù <strong>Home</strong> e sul link <strong>Home dell''amministratore</strong> presente nel percorso in alto a sinistra, appena sotto la barrà dei menù.</p>', 0, 1388768869, 4, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_46', 1, 3, 0, '3.5 Aggiungere un utente', 'amministratore', '<p>Per aggiungere un nuovo utente dentro ADA, dalla home page, basta cliccare  sul menù <strong>agisci &gt; aggiungi utente.</strong></p><p>Una volta compilati tutti i campi per confermare l''inserimento è sufficiente cliccare sul pulsante <strong>Invia</strong>.</p><p>Per annullare l''inserimento basta clicare sul menù <strong>Home</strong> e sul link <strong>Home dell''amministratore </strong>nel percorso in alto a sinistra, appena sotto la barrà dei menù.</p>', 0, 1388768869, 5, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_47', 1, 3, 0, '3.6 Editare la home page', 'amministratore', '<p>Per modificare i testi della home page di ADA, dalla home page, basta cliccare sul menù <strong>agisci &gt; edit home page news</strong>.</p><p>Comparirà la lista delle news nelle varie lingue attive nel sistema, per ognuna è possibile leggere la data dell''ultimo aggiornamento. Se una news non è mai stata editata comparirà la dicitura "no file" al posto della data.</p><p>Per editare una qualsiasi delle news basta cliccare sul link <strong>edit news in [lingua prescelta]</strong>.</p><p>L''amministratore ha a disposizione un vero e proprio editor di testo, è possibile inserire immagini, link e modificare il testo come si vuole, sia per quanto riguarda i contenuti che per quanto riguarda il grafico.</p><p>Una volta compilati tutti i campi per confermare l''inserimento è sufficiente cliccare sul pulsante <strong>Invia</strong>.</p><p>Per vedere l''effetto delle nuove modifiche, vi consigliamo di aprire la home page di ADA con un altro browser e di aggiornarla dopo aver completato i cambiamenti.  Per annullare le modifiche basta cliccare sul menù <strong>Home</strong>.</p>', 0, 1388768869, 6, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_48', 1, 3, 0, '3.7 Importare una lingua', 'amministratore', '<p>Per importare una nuova lingua dentro ADA, dalla home page, basta cliccare sul menù <strong>agisci &gt; importa una lingua.</strong></p>', 0, 1388768869, 7, '1_36', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_49', 1, 3, 0, '4. Il modulo Switcher', 'switcher', '<p>Il modulo permette allo <strong>switcher</strong> di:</p><ul><li>controllare gli accessi alle diverse aree;</li><li>consultare i report generali (accessi, nodi, corsi, utenti);</li><li>gestire i corsisti, i tutor, gli autori e gli amministratori;</li><li>pubblicare i corsi (formazione delle classi, attivazione dei corsi).</li></ul><p>Per entrare in ADA come switcher occorre essere abilitati dall''amministratore del sistema, il quale fornisce un nome utente e una password personale. Dopo aver digitato questi dati e aver cliccato su <strong>Accedi</strong> lo switcher accede alla propria home page.</p>', 1, 1388768869, 4, '1_30', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_50', 10, 3, 0, '4.3 La gestione degli utenti', 'switcher', '', 1, 1388768869, 0, '1_49', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_51', 10, 3, 0, '4.3.1 Modificare il profilo dello switcher', 'switcher', '', 0, 1388768869, 0, '1_50', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_52', 10, 3, 0, '4.3.2 Le liste degli utenti', 'switcher', '', 1, 1388768869, 0, '1_50', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_53', 10, 3, 0, '4.3.2.1 Modificare i dati di un utente', 'switcher', '', 0, 1388768869, 0, '1_52', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_54', 10, 3, 0, '4.3.2.2 Aprire la scheda di un utente', 'switcher', '', 0, 1388768869, 0, '1_52', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_55', 10, 3, 0, '4.3.2.3 Eliminare il profilo di un utente', 'switcher', '', 0, 1388768869, 0, '1_52', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_56', 10, 3, 0, '4.3.3 Aggiungere un utente', 'switcher', '', 0, 1388768869, 0, '1_50', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_57', 10, 3, 0, '4.4 La traduzione dei messaggi', 'switcher', '', 0, 1388768869, 0, '1_49', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_58', 10, 3, 0, '4.5 La lista delle chatrooms', 'switcher', '', 1, 1388768869, 0, '1_49', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_59', 10, 3, 0, '4.5.1 Modificare una chat', 'switcher', '', 0, 1388768869, 0, '1_58', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_60', 10, 3, 0, '4.5.2 Eliminare una chat', 'switcher', '', 0, 1388768869, 0, '1_58', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_61', 10, 3, 0, '4.5.3 Creare una nuova chat', 'switcher', '', 0, 1388768869, 0, '1_58', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_62', 1, 3, 0, '4.1 La home page dello switcher', 'switcher', '<p>Uno switcher in ADA può creare o eliminare utenti (studenti, tutor, autori), corsi e istanze di corso.<br /><br />E'' anche possibile gestire le traduzioni dei messaggi di sistema tramite l''apposito modulo.<br /><br />Il sistema visualizza sempre (in alto a sinistra) il punto in cui ci si trova e (in alto a destra) il nome <strong>utente</strong> con cui ci si è collegati, il <strong>tipo</strong> (se amministratore, switcher, autore, tutor o studente), lo <strong>status</strong>.<br /><br />Le voci di menù in alto alla pagina sono sempre presenti, contrassegnate da delle icone sono:</p><ul><li><strong>home</strong> (per tornare alla home in ogni momento);</li><li><strong>comunica</strong> (per aprire la messaggeria integrata e la lista delle chat);</li><li><strong>agisci</strong> (i contenuti di questo menù si modificano a seconda del punto in cui ci si trova);</li><li><strong>naviga</strong> (per accedere al pannello di navigazione e accedere così ai moduli di configurazione, monitoraggio, gestione utenti, gestione corsi, modifica informazioni e traduzioni, gestione template);</li><li><strong>aiuto</strong> (per aprire il manuale utente online);</li><li><strong>esci</strong> (per chiudere la sessione di lavoro).</li></ul><p>Nella propria home page lo switcher può consultare l''elenco di tutti i corsi attivi. I corsi sono elencati per: I<strong>D, Codice, Titolo e Descrizione</strong>. Per ogni corso sono possibili una serie di azioni:</p><ul><li>modificare il corso;</li><li>aprire la scheda del corso;</li><li>aprire la lista delle istanze del corso;</li><li>aprire la lista dei sondaggi abbinati al corso, oppure assegnarli;</li><li>aggiungere una istanza di corso;</li><li>eliminare il corso.</li></ul><p>Può inoltre compiere una serie di operazioni grazie al menù <strong>agisci</strong>:</p><ul><li><strong>cambia profilo</strong> (per modificare o aggiornare i propri dati personali);</li><li><strong>lista autori</strong> (l''elenco di tutti gli autori registrati sulla piattaforma);</li><li><strong>lista tutor</strong> (l''elenco di tutti i tutor registrati sulla piattaforma);</li><li><strong>lista studenti</strong> (l''elenco di tutti i corsisti registrati sulla piattaforma);</li><li><strong>aggiungi utente </strong>(per registrare un utente e assegnargli il ruolo di autore, tutor o corsista);</li><li><strong>lista corsi </strong>(l''elenco dei corsi presenti in piattaforma);</li><li><strong>aggiungi corso </strong>(per creare un nuovo corso);</li><li><strong>traduci messaggi</strong> (per gestire la traduzione dei messaggi di sistema).</li></ul><p>In basso sono elencati, se presenti i messaggi ricevuti (<strong>messaggi per te</strong>) e gli appuntamenti presenti in agenda (<strong>i tuoi appuntamenti</strong>).<br /><br />ADA mantiene messaggi e appuntamenti fino al momento in cui vengono letti dal destinatario, una volta letti se il destinatario non li elimina lo fa automaticamente il sistema dopo un certo periodo di tempo. In questo modo il database non viene sovraccaricato e la consultazione dei nuovi messaggi o dei nuovi appuntamenti è più semplice.<br /><br />In basso si trova la freccia per tornare all''inizio della pagina e le informazioni sul sistema e sugli sviluppatori di ADA.</p>', 0, 1388768869, 1, '1_49', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_63', 1, 3, 0, '4.2 La gestione dei corsi', 'switcher', '<p>L''attivazione di un corso all''interno di ADA richiede sostanzialmente due passaggi:</p><ol><li>la <strong>creazione del corso </strong>e l''assegnazione del corso ad un autore;</li><li>la <strong>creazione di una o più istanze di corso </strong>e la loro assegnazione ad un tutor.</li></ol><p>Una volta il corso è stato creato l''autore può editarlo. Una volta che le istanze sono state create gli studenti possono iscriversi e i tutor possono visualizzare il corso. Un autore può modificare un corso solo fino a quando non ci sono istanze attive, cioè solo quando non ci sono studenti che lo stanno seguendo. Può modificarlo fino al giorno prima che l''istanza inizi e tornare a farlo il giorno dopo la chiusura dell''istanza.</p><p>Per ogni corso ci possono essere più istanze attive, con date di inizio e di fine diverse e con diversi tutor assegnati. Ai corsi possono anche essere abbinati dei sondaggi, ad esempio dei sondaggi di soddisazione per valutarne l''efficacia e l''impatto sul corsista, o per ottenere informazioni aggiuntive sulle abitudini di navigazione ed osservazioni riguardo il corso stesso.</p>', 1, 1388768869, 2, '1_49', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_64', 1, 3, 0, '4.2.1 Creare un nuovo corso', 'switcher', '<p>Dalla home page cliccando su aggiungi corso dal menù agisci è possibile creare un nuovo corso. Per ogni corso va indicato:</p><ul><li>l’<strong>autore</strong> (selezionandolo tra la lista di quelli disponibili);</li><li>la <strong>lingua</strong> (selezionandola tra la lista di quelle disponibili);</li><li>il <strong>codice</strong> (una sigla);</li><li>il <strong>titolo</strong> (la denominazione del corso estesa);</li><li>la <strong>descrizione</strong> del corso (verrà visualizzato dall''utente quando aprirà il corso);</li><li>i <strong>crediti</strong> a cui il corso da diritto (segnare almeno 1).</li></ul><p>Tutti questi campi sono obbligatori. Per confermare l''inserimento cliccare su <strong>Invia</strong>.  Per annullare l''inserimento del corso cliccare sul menù <strong>home</strong>, in questo modo si torna all''elenco dei corsi.</p><p>Vediamo ora nel dettaglio le operazioni possibili dalla lista dei corsi.</p>', 0, 1388768869, 1, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_65', 1, 3, 0, '4.2.2 Modificare un corso', 'switcher', '<p>Dalla home page cliccando su <strong>modifica</strong> lo switcher può modificare i dati di un corso.</p><p>Una volta modificato uno dei campi è sufficiente cliccare su <strong>Invia</strong> per confermare le modifiche.</p><p>Per annullare le modifiche alla scheda di un corso cliccare sul menù <strong>home</strong>, in questo modo si torna all''elenco dei corsi.</p>', 0, 1388768869, 2, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_66', 1, 3, 0, '4.2.3 Aprire la scheda di un corso', 'switcher', '<p>Dalla home page cliccando sull’icona <strong>zoom</strong> si possono visualizzare i dati del corso: Id corso; Autore; Lingua; Codice; Titolo; Descrizione; Id nodo iniziale; Id nodo toc; media path, data di creazione, data di pubblicazione, crediti.</p><p>Non tutti questi dati possono essere modificati dallo switcher, perché in realtà vengono assegnati automaticamente da ADA (o non assegnati come nel caso della data di creazione e di pubblicazione) ma possono essere modificati entrando nel database: un''operazione di cui però non si occupa lo switcher.</p><p>Per tornare alla lista dei corsi basta cliccare sul menù <strong>home</strong>.</p>', 0, 1388768869, 3, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_67', 1, 3, 0, '4.2.4 Creare una nuova istanza', 'switcher', '<p>Affinché un corso posso essere frequentato occorre creare una <strong>istanza</strong>, cioè una classe: in questo modo si stabilisce una data d''inizio, una durata, si assegna un tutor e si possono gestire iscrizioni e preiscrizioni.</p><p>Dalla home page per creare una nuova istanza basta cliccare sul link <strong>add instance.</strong> Per ogni istanza va indicato:</p><ul><li>il <strong>titolo</strong> (può essere un nome identificativo che facilita il tutor nel caso gli siano state assegnate più istanze per uno stesso corso);</li><li>il <strong>prezzo</strong> (per i decimali va usato il punto);</li><li>la <strong>data</strong> <strong>d''inizio</strong> prevista (nel formato gg/mm/aaaa);</li><li><strong>iscrizioni aperte </strong>si o no (se si cecca la casella si chiunque sia registrato alla piattaforma può iscriversi);</li><li><strong>iniziato</strong> si o no (se si cecca si il corso parte automaticamente alla data d''inizio indicata, se si cecca no il corso non parte);</li><li><strong>durata</strong> (va espressa in giorni, una volta trascorso i corsisti non potranno più accedere al corso);</li><li>modo autoistruzione si o no (un corso in autoistruzione è un corso in cui non è presente il tutor);</li><li><strong>iscrizione autonoma </strong>dell''utente si o no (se si cecca si il corsista si iscrive automaticamente, senza dover aspettare che lo switcher lo abiliti, serve per i corsi in autoistruzione);</li><li><strong>durata iscrizione</strong> (cioè il tempo massimo entro cui il corsista dovrà seguire il corso, serve per i corsi in autoistruzione e non va confuso con la durata del corso);</li><li><strong>livello assegnato </strong>(di default il livello è 0).</li></ul><p>Una volta riempiti i vari campi è sufficiente cliccare su <strong>Invia</strong> per confermare le modifiche.&#160;Solo dopo aver creato una istanza è popssibile assegnare il tutor e in caso iscrivere subito gli studenti.</p><p>Per annullare la creazione dell''istanza cliccare sul menù <strong>home</strong>, in questo modo si torna all''elenco dei corsi.</p>', 0, 1388768869, 4, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_68', 1, 3, 0, '4.2.5 Aprire la lista delle istanze di un corso', 'switcher', '<p>Dalla home page cliccando sull''icona <strong>classi</strong> lo switcher può aprire l''elenco delle istanze di un determinato corso, gestirne e inizio, assegnarre i tutor, iscrivere, sospendere o disiscrivere i corsisti.</p><p>Se nel corso sono presenti classi queste vengono elencate in base all’<strong>ID</strong>, alla <strong>classe</strong> (cioè al titolo assegnato all''istanza), alla <strong>durata</strong>, alla data di <strong>inizio</strong> e alla data di <strong>fine</strong> (calcolata automaticamente in base alla durata indicata).</p><p>Per ogni istanza è possibile:</p><ul><li>assegnare il tutor;</li><li>aprire la lista degli studenti;</li><li>modificarla;</li><li>eliminarla.</li></ul><p>Se nella lista delle istanze non è presente nessuna classe se ne crea una tornando alla <strong>home</strong> (vedi sopra).</p><p>Per tornare alla lista dei corsi basta cliccare sul menù <strong>home</strong>.</p>', 1, 1388768869, 5, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_69', 1, 3, 0, '4.2.5.1 Assegnare un tutor alla istanza', 'switcher', '<p>Se ad una istanza è stato assegnato un tutor se ne legge il nome sotto la colonna omonima, altrimenti si legge nessun tutor.&#160;Cliccando sul link sensibile sotto la colonna <strong>tutor</strong> si apre l''elenco dei tutor abilitati.</p><p>A questo punto è sufficiente selezionare il tutor dalla lista di quelli disponibili e cliccare su <strong>Invia</strong> per confermare l’assegnazione.</p><p>Per l’operazione contraria, cioè per togliere il tutor ad una classe una classe basta ceccare su <strong>nessun tutor.</strong></p><p>Per tornare alla lista delle classi basta cliccare sul menù <strong>home</strong>.</p>', 0, 1388768869, 1, '1_68', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_70', 1, 3, 0, '4.2.5.2 Aprire la lista degli iscritti', 'switcher', '<p>Cliccando su lista studenti in corrispondenza della colonna <strong>Iscritti</strong> é possibile gestire le preiscrizioni e le iscrizioni alla istanza.</p><p>Gli studenti eventualmente presenti nella classe sono elencati. In ADA uno studente, rispetto ad un corso, può risultare pre-iscritto, regolarmente iscritto, rimosso o in visita. In base allo status assegnato lo studente ha delle limitazioni all’interno del corso.</p><p>Lo studente <strong>pre-iscritto </strong>non può navigare il corso, in pratica è una richiesta ed è compito dello switcher soddisfarla o meno.</p><p>Quando è <strong>iscritto</strong> può navigare nel corso, realizzare gli esercizi, consultare la propria cronologia, inviare messaggi al tutor, all’autore e al resto della classe, inserire note forum e partecipare alla chat.</p><p>Lo studente <strong>rimosso</strong> non può entrare nel corso ma la sua eventuale cronologia viene mantenuta intatta fino al momento in cui viene reiscritto.</p><p>Lo studente<strong> in visita</strong> può navigare nel corso ma non può realizzare gli esercizi, inviare messaggi, inserire note forum ed entrare in chat. Non ha inoltre nessuna cronologia della navigazione.</p><p>Lo switcher può di volta in volta decidere lo status dello studente aprendo il menù a tendina in corrispondenza del nome e selezionare uno degli status disponibili, poi cliccando su Invia per confermare il nuovo status.</p><p>Lo switcher può iscrivere direttamente uno studente ad una istanza: importando una lista di studenti, oppure iscrivendoli uno per uno.</p><p>Se i corsisti da iscrivere sono molti lo switcher può uploadare un file con i loro dati e iscriverli automaticamente. Il file deve avere estensione <strong>txt</strong> e deve contenere in ogni riga i seguenti dati: nome, cognome, email.</p><p>Dalla lista degli iscritti si clicca sul link <strong>upload file</strong>, poi si seleziona il file dal proprio computer e si clicca sul pulsante <strong>Invia</strong>.</p><p>Se gli studenti non sono già registrati dentro ADA vengono registrati e gli viene inviata una mail in cui gli si chiede di confermare la registrazione e la conseguente iscrizione.  Se invece sono già presenti ADA li iscrive e basta.</p><p>Se si deve iscrivere un solo corsista basta cliccare sul link <strong>Iscrivi studente</strong>, scrivere lo username nel campo di testo e poi cliccare su <strong>Invia</strong>.</p><p>Per tornare alla lista delle classi basta cliccare sul menù <strong>home</strong>.</p>', 1, 1388768869, 2, '1_68', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_71', 1, 3, 0, '4.2.5.2.1 Cambiare lo status di uno studente', 'switcher', '', 0, 1388768869, 1, '1_70', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_72', 1, 3, 0, '4.2.5.2.2 Iscrivere uno studente', 'switcher', '', 0, 1388768869, 2, '1_70', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_73', 1, 3, 0, '4.2.5.3 Modificare una istanza', 'switcher', '', 0, 1388768869, 3, '1_68', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_74', 1, 3, 0, '4.2.5.4 Eliminare una istanza', 'switcher', '', 0, 1388768869, 4, '1_68', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_75', 1, 3, 0, '4.2.6 Gestire i sondaggi di un corso', 'switcher', '', 0, 1388768869, 6, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_76', 1, 3, 0, '4.2.7 Importare un corso', 'switcher', '', 0, 1388768869, 7, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_77', 1, 3, 0, '4.2.8 Esportare un corso', 'switcher', '', 0, 1388768869, 8, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_78', 1, 3, 0, '4.2.9 Eliminare un corso', 'switcher', '', 0, 1388768869, 9, '1_63', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_79', 1, 3, 0, '4.6 La newsletter', 'switcher', '', 0, 1388768869, 6, '1_49', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_80', 1, 3, 0, '5. Il modulo Autore', 'autore', '<p><img width="100" vspace="15" hspace="15" height="100" align="left" alt="" src="/adaInstall/services/media/3/ManualeAda/Edit_Green.png" /><br />&#160;L''autore è il <strong>responsabile dei contenuti</strong>: struttura i materiali del corso e li organizza per l''interazione all''interno di ADA. <br />Ha anche la responsabilità di costruire le prove di valutazione.</p><p><LINK TYPE="INTERNAL" VALUE="26"></p><p><a href="http://localhost/adaInstall/browsing/view.php?id_node=110_160&amp;id_course=110"><img width="200" height="36" alt="" src="/adaInstall/services/media/3/ManualeAda/maggiori_spiegazioni.png" /></a></p>', 1, 1388768869, 5, '1_30', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_81', 10, 3, 0, '5.1 La home page dell''autore', 'autore', '', 0, 1388768869, 0, '1_80', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_82', 10, 3, 0, '5.2 Editare un corso', 'autore', '', 1, 1388768869, 0, '1_80', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_83', 10, 3, 0, '5.2.1 Aggiungere un nodo o un gruppo', 'autore', '', 1, 1388768869, 0, '1_82', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_84', 10, 3, 0, '5.2.1.1 Inserire un media', 'autore', '', 0, 1388768869, 0, '1_83', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_85', 10, 3, 0, '5.2.1.2 Inserire un link interno', 'autore', '', 0, 1388768869, 0, '1_83', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_86', 10, 3, 0, '5.2.1.3 Inserire un link esterno', 'autore', '', 0, 1388768869, 0, '1_83', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_87', 10, 3, 0, '5.2.2 Aggiungere un termine', 'autore', '', 0, 1388768869, 0, '1_82', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_88', 10, 3, 0, '5.2.3 Modificare un nodo, un gruppo o un termine', 'autore', '', 0, 1388768869, 0, '1_82', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_89', 10, 3, 0, '5.2.4 Eliminare un nodo, un gruppo o un termine', 'autore', '', 0, 1388768869, 0, '1_82', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_90', 10, 3, 0, '5.2.5 Gestire la mappa grafica', 'autore', '', 0, 1388768869, 0, '1_82', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_91', 10, 3, 0, '5.2.6 Creare un esercizio, un test o un sondaggio', 'autore', '', 1, 1388768869, 0, '1_82', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_92', 10, 3, 0, '5.2.6.1 Aggiungere un esercizio', 'autore', '', 0, 1388768869, 0, '1_91', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_93', 10, 3, 0, '5.2.6.2 Modificare un esercizio', 'autore', '', 0, 1388768869, 0, '1_91', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_94', 10, 3, 0, '5.2.6.3 Eliminare un esercizio', 'autore', '', 0, 1388768869, 0, '1_91', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_95', 10, 3, 0, '5.2.6.4 Aggiungere un test', 'autore', '', 1, 1388768869, 0, '1_91', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_96', 10, 3, 0, '5.2.6.4.1 Aggiungere un argomento', 'autore', '', 0, 1388768869, 0, '1_95', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_97', 10, 3, 0, '5.2.6.4.2 Modificare un argomento', 'autore', '', 0, 1388768869, 0, '1_95', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_98', 10, 3, 0, '5.2.6.4.3 Eliminare un argomento', 'autore', '', 0, 1388768869, 0, '1_95', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_99', 10, 3, 0, '5.2.6.4.4 Aggiungere una domanda', 'autore', '', 0, 1388768869, 0, '1_95', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_100', 10, 3, 0, '5.2.6.4.5 Modificare una domanda', 'autore', '', 0, 1388768869, 0, '1_95', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_101', 10, 3, 0, '5.2.6.4.6 Eliminare una domanda', 'autore', '', 0, 1388768869, 0, '1_95', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_102', 10, 3, 0, '5.2.6.5 Aggiungere un sondaggio', 'autore', '', 1, 1388768869, 0, '1_91', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_103', 10, 3, 0, '5.2.6.5.1 Aggiungere una sessione ', 'autore', '', 0, 1388768869, 0, '1_102', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_104', 10, 3, 0, '5.2.6.5.2 Modificare una sessione', 'autore', '', 0, 1388768869, 0, '1_102', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_105', 10, 3, 0, '5.2.6.5.3 Eliminare una sessione', 'autore', '', 0, 1388768869, 0, '1_102', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_106', 10, 3, 0, '5.2.6.5.4 Aggiungere una domanda', 'autore', '', 0, 1388768869, 0, '1_102', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_107', 10, 3, 0, '5.2.6.5.5 Modificare una domanda', 'autore', '', 0, 1388768869, 0, '1_102', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_108', 10, 3, 0, '5.2.6.5.6 Eliminare una domanda', 'autore', '', 0, 1388768869, 0, '1_102', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_109', 10, 3, 0, '5.3 Navigare un corso', 'autore', '', 1, 1388768869, 0, '1_80', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_110', 10, 3, 0, '5.3.1 Il pannello di navigazione', 'autore', '', 0, 1388768869, 0, '1_109', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_111', 10, 3, 0, '5.3.2 L''indice del corso', 'autore', '', 0, 1388768869, 0, '1_109', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_112', 10, 3, 0, '5.3.3 La mappa grafica', 'autore', '', 0, 1388768869, 0, '1_109', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_113', 10, 3, 0, '5.3.4 La ricerca', 'autore', '', 0, 1388768869, 0, '1_109', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_114', 10, 3, 0, '5.4 Il report di un corso', 'autore', '', 0, 1388768869, 0, '1_80', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_115', 10, 3, 0, '5.5 Modificare il profilo dell''autore', 'autore', '', 0, 1388768869, 0, '1_80', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_116', 1, 3, 0, '6. Il modulo Tutor', 'tutor', '<p>&#160;<img width="150" vspace="15" hspace="15" height="167" align="left" src="/adaInstall/services/media/3/ManualeAda/Red_Tator.png" alt="" />Il tutor ha il compito di <strong>gestire la classe</strong>: fornendo assistenza in caso di difficoltà, aiutando i corsisti a oganizzare il proprio calendario di lavoro, promuovendo l''interazione e la comunicazione del gruppo e mediando con gli autori e gli esperti per chiarimenti riguardo i contenuti.</p><p><LINK TYPE="INTERNAL" VALUE="25"></p><p><a href="http://localhost/adaInstall/browsing/view.php?id_node=110_159&amp;id_course=110"><img width="200" height="36" src="/adaInstall/services/media/3/ManualeAda/maggiori_spiegazioni.png" alt="" /></a></p>', 1, 1388768869, 6, '1_30', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_117', 10, 3, 0, '6.4 Modificare il profilo del tutor', 'tutor', '', 0, 1388768869, 0, '1_116', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_118', 10, 3, 0, '6.1 La home page del tutor', 'tutor', '', 1, 1388768869, 0, '1_116', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_119', 10, 3, 0, '6.2 Navigare un corso', 'tutor', '', 1, 1388768869, 0, '1_116', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_120', 10, 3, 0, '6.2.1 Il pannello di navigazione', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_121', 10, 3, 0, '6.2.2 L''indice del corso', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_122', 10, 3, 0, '6.2.3 La mappa grafica', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_123', 10, 3, 0, '6.2.4 L''area collabora', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_124', 10, 3, 0, '6.2.5 Il forum', 'tutor', '', 1, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_125', 10, 3, 0, '6.2.5.1 Inserire una nota forum', 'tutor', '', 0, 1388768869, 0, '1_124', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_126', 10, 3, 0, '6.2.5.2 Le note forum personali', 'tutor', '', 0, 1388768869, 0, '1_124', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_127', 10, 3, 0, '6.2.6 La chat', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_128', 1, 3, 0, '6.2.7 La videochat', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_129', 1, 3, 0, '6.2.8 La ricerca', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_130', 10, 3, 0, '6.2.9 Gli esercizi', 'tutor', '', 0, 1388768869, 0, '1_119', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_131', 10, 3, 0, '6.3 Valutare la classe', 'tutor', '', 1, 1388768869, 0, '1_116', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_132', 10, 3, 0, '6.3.3 L''elenco degli esercizi svolti', 'tutor', '', 1, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_133', 10, 3, 0, '6.3.3.1 Correggere o far ripetere un esercizio', 'tutor', '', 0, 1388768869, 0, '1_132', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_134', 10, 3, 0, '6.3.4 Consultare la lista delle note inserite', 'tutor', '', 0, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_135', 10, 3, 0, '6.3.5 Alzare o abbassare di livello', 'tutor', '', 0, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_136', 10, 3, 0, '6.3.6 Il report delle chat', 'tutor', '', 0, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_137', 10, 3, 0, '6.3.7 La gestione delle chat', 'tutor', '', 1, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_138', 10, 3, 0, '6.3.7.1 Creare una chat privata', 'tutor', '', 0, 1388768869, 0, '1_137', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_139', 10, 3, 0, '6.3.1 La scheda dello studente', 'tutor', '', 0, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_140', 10, 3, 0, '6.3.2 Consultare la cronologia', 'tutor', '', 0, 1388768869, 0, '1_131', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_141', 1, 3, 0, '7. Il modulo Studente', 'studente', '<p><img width="150" vspace="15" hspace="15" height="148" align="left" src="/adaInstall/services/media/3/ManualeAda/Matri_Cola.png" alt="" />Chi segue un corso con ADA ha a disposizione strumenti avanzati di interazione, navigazione e comunicazione.</p><p>&#160;Può studiare in maniera autonoma oppure avvalersi dell''aiuto del tutor o, ancora, interagire con un gruppo di pari durante l''apprendimento.</p><p><a href="http://localhost/adaInstall/browsing/view.php?id_node=110_158&amp;id_course=110"><img width="200" height="36" src="/adaInstall/services/media/3/ManualeAda/maggiori_spiegazioni.png" alt="" /></a></p>', 1, 1388768869, 7, '1_30', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_142', 10, 3, 0, '7.1 La home page dello studente', 'studente', '', 0, 1388768869, 0, '1_141', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_143', 10, 3, 0, '7.2 Navigare un corso', 'studente', '', 1, 1388768869, 0, '1_141', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_144', 10, 3, 0, '7.2.1 Il pannello di navigazione', 'studente', '', 0, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_145', 10, 3, 0, '7.2.9 La stampa', 'studente', '', 0, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_146', 10, 3, 0, '7.2.8 La ricerca', 'studente', '', 0, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_147', 10, 3, 0, '7.2.7 La videochat', 'studente', '', 0, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_148', 10, 3, 0, '7.2.6 La chat', 'studente', '', 0, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_149', 10, 3, 0, '7.2.5 Il forum', 'studente', '', 1, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_150', 10, 3, 0, '7.2.5.1 Inserire una nota forum', 'studente', '', 0, 1388768869, 0, '1_149', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_151', 10, 3, 0, '7.2.5.2 Le note forum personali', 'studente', '', 0, 1388768869, 0, '1_149', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_152', 10, 3, 0, '7.2.4 L''area collabora', 'studente', '', 0, 1388768869, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_153', 10, 3, 0, '7.2.3 La mappa grafica', 'studente', '', 0, 1388768870, 0, '1_143', 0, 0, 3, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_154', 10, 3, 0, '7.2.2 L''indice del corso', 'studente', '', 1, 1388768870, 0, '1_143', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_155', 10, 3, 0, '7.2.10 Realizzare un esercizio, un test o un sondaggio', 'studente', '', 0, 1388768870, 0, '1_143', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_156', 10, 3, 0, '7.3 Valutare il proprio lavoro', 'studente', '', 1, 1388768870, 0, '1_141', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_157', 10, 3, 0, '7.3.1 Consultare la cronologia', 'studente', '', 0, 1388768870, 0, '1_156', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_158', 10, 3, 0, '7.3.2 Consultare lo storico degli esercizi', 'studente', '', 0, 1388768870, 0, '1_156', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_159', 10, 3, 0, '7.3.3 Consultare lo storico dei test', 'studente', '', 0, 1388768870, 0, '1_156', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_160', 10, 3, 0, '7.3.4 Consultare lo storico dei sondaggi', 'studente', '', 0, 1388768870, 0, '1_156', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_161', 10, 3, 0, '7.4 Il diario', 'studente', '', 0, 1388768870, 0, '1_141', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_162', 10, 3, 0, '7.5 Iscriversi ad un corso', 'studente', '', 0, 1388768870, 0, '1_141', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_163', 10, 3, 0, '7.6 Modificare il profilo dello studente', 'studente', '', 0, 1388768870, 0, '1_141', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_164', 1, 3, 0, '8. L''agenda', 'ADA 2.0', '', 0, 1388768870, 8, '1_30', 0, 0, 1, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0),
('1_165', 1, 3, 0, '9. La messaggeria', 'ADA 2.0', '', 0, 1388768870, 9, '1_30', 0, 0, 0, 'nodo.png', 'NULL', 'NULL', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `openmeetings_room`
--

CREATE TABLE IF NOT EXISTS `openmeetings_room` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_room` int(11) NOT NULL,
  `id_istanza_corso` int(10) NOT NULL,
  `id_tutor` int(10) NOT NULL,
  `tipo_videochat` varchar(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `descrizione_videochat` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `tempo_avvio` int(11) NOT NULL,
  `tempo_fine` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_room` (`id_room`),
  KEY `id_istanza_corso` (`id_istanza_corso`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `posizione`
--

CREATE TABLE IF NOT EXISTS `posizione` (
  `id_posizione` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `x0` int(11) NOT NULL DEFAULT '0',
  `y0` int(11) NOT NULL DEFAULT '0',
  `x1` int(11) NOT NULL DEFAULT '0',
  `y1` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_posizione`),
  UNIQUE KEY `posizione_coords` (`x0`,`y0`,`x1`,`y1`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=25 ;

--
-- Dump dei dati per la tabella `posizione`
--

INSERT INTO `posizione` (`id_posizione`, `x0`, `y0`, `x1`, `y1`) VALUES
(1, 100, 100, 200, 200),
(2, 131, 48, 100, 0),
(3, 466, 35, 100, 0),
(4, 487, 7, 100, 0),
(5, 273, 139, 100, 0),
(6, 489, 54, 100, 0),
(7, 472, 267, 100, 0),
(8, 0, 0, 0, 0),
(9, 211, 167, 100, 0),
(10, 113, 137, 100, 0),
(11, 372, 136, 100, 0),
(12, 54, 83, 100, 0),
(13, 268, 85, 100, 0),
(14, 349, 256, 100, 0),
(15, 581, 185, 100, 0),
(16, 773, 183, 100, 0),
(17, 646, 136, 100, 0),
(18, 653, 168, 100, 0),
(19, 158, 76, 100, 0),
(20, 28, 229, 100, 0),
(21, 275, 229, 100, 0),
(22, 512, 91, 100, 0),
(23, 726, 0, 100, 0),
(24, 281, 128, 100, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `risorsa_esterna`
--

CREATE TABLE IF NOT EXISTS `risorsa_esterna` (
  `id_risorsa_ext` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nome_file` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tipo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `copyright` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `id_utente` int(10) unsigned NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `titolo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descrizione` text COLLATE utf8_unicode_ci NOT NULL,
  `pubblicato` tinyint(1) NOT NULL,
  `lingua` tinyint(3) NOT NULL,
  PRIMARY KEY (`id_risorsa_ext`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=27 ;

--
-- Dump dei dati per la tabella `risorsa_esterna`
--

INSERT INTO `risorsa_esterna` (`id_risorsa_ext`, `nome_file`, `tipo`, `copyright`, `id_utente`, `keywords`, `titolo`, `descrizione`, `pubblicato`, `lingua`) VALUES
(1, 'barra_editing.jpg', 1, 0, 3, '', '', '', 0, 0),
(2, 'esempio_immagine.jpg', 1, 0, 3, '', '', '', 0, 0),
(3, '01_INGLESE.swf', 3, 0, 3, '', '', '', 0, 0),
(4, 'presentazione_Lynx_2010.pdf', 5, 0, 3, '', '', '', 0, 0),
(5, 'Amanda.mp3', 2, 0, 3, '', '', '', 0, 0),
(6, 'home.jpg', 1, 0, 3, '', '', '', 0, 0),
(7, 'pag_indice.jpg', 1, 0, 3, '', '', '', 0, 0),
(8, 'pag_mappa.jpg', 1, 0, 3, '', '', '', 0, 0),
(9, 'pag_cerca.jpg', 1, 0, 3, '', '', '', 0, 0),
(10, '260712_150944_messaggeria_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(11, 'messaggio_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(12, 'agenda_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(13, 'appuntamento_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(14, 'chat_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(15, 'videochat.jpg', 1, 0, 3, '', '', '', 0, 0),
(16, 'forum_indice_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(17, 'collabora_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(18, 'cronologia_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(19, 'diario_OK.jpg', 1, 0, 3, '', '', '', 0, 0),
(20, 'corsista.jpg', 1, 0, 3, '', '', '', 0, 0),
(21, 'tutor.jpg', 1, 0, 3, '', '', '', 0, 0),
(22, 'autore.jpg', 1, 0, 3, '', '', '', 0, 0),
(23, 'manager_provider.jpg', 1, 0, 3, '', '', '', 0, 0),
(24, 'amministratore_piattaforma.jpg', 1, 0, 3, '', '', '', 0, 0),
(25, 'http://ada.lynxlab.com', 4, 0, 3, '', '', '', 0, 0),
(26, 'http://ada.lynxlab.com/ada20', 4, 0, 3, '', '', '', 0, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `risorse_nodi`
--

CREATE TABLE IF NOT EXISTS `risorse_nodi` (
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_risorsa_ext` int(10) unsigned NOT NULL DEFAULT '0',
  `peso` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_nodo`,`id_risorsa_ext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `risorse_nodi`
--

INSERT INTO `risorse_nodi` (`id_nodo`, `id_risorsa_ext`, `peso`) VALUES
('1_4', 1, 1),
('1_5', 2, 1),
('1_5', 3, 1),
('1_5', 4, 1),
('1_5', 5, 1),
('1_14', 6, 1),
('1_15', 7, 1),
('1_15', 8, 1),
('1_15', 9, 1),
('1_17', 10, 1),
('1_17', 11, 1),
('1_18', 12, 1),
('1_18', 13, 1),
('1_19', 14, 1),
('1_19', 15, 1),
('1_20', 16, 1),
('1_21', 17, 1),
('1_22', 18, 1),
('1_22', 19, 1),
('1_24', 20, 1),
('1_25', 21, 1),
('1_26', 22, 1),
('1_27', 23, 1),
('1_28', 24, 1),
('1_34', 25, 1),
('1_35', 26, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `sessione_eguidance`
--

CREATE TABLE IF NOT EXISTS `sessione_eguidance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_tutor` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `event_token` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_ora` int(11) unsigned NOT NULL DEFAULT '0',
  `tipo_eguidance` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ud_1` tinyint(3) unsigned DEFAULT '0',
  `ud_2` tinyint(3) unsigned DEFAULT '0',
  `ud_3` tinyint(3) unsigned DEFAULT '0',
  `ud_comments` text COLLATE utf8_unicode_ci,
  `pc_1` tinyint(3) unsigned DEFAULT '0',
  `pc_2` tinyint(3) unsigned DEFAULT '0',
  `pc_3` tinyint(3) unsigned DEFAULT '0',
  `pc_4` tinyint(3) unsigned DEFAULT '0',
  `pc_5` tinyint(3) unsigned DEFAULT '0',
  `pc_6` tinyint(3) unsigned DEFAULT '0',
  `pc_comments` text COLLATE utf8_unicode_ci,
  `ba_1` tinyint(3) unsigned DEFAULT '0',
  `ba_2` tinyint(3) unsigned DEFAULT '0',
  `ba_3` tinyint(3) unsigned DEFAULT '0',
  `ba_4` tinyint(3) unsigned DEFAULT '0',
  `ba_comments` text COLLATE utf8_unicode_ci,
  `t_1` tinyint(3) unsigned DEFAULT '0',
  `t_2` tinyint(3) unsigned DEFAULT '0',
  `t_3` tinyint(3) unsigned DEFAULT '0',
  `t_4` tinyint(3) unsigned DEFAULT '0',
  `t_comments` text COLLATE utf8_unicode_ci,
  `pe_1` tinyint(3) unsigned DEFAULT '0',
  `pe_2` tinyint(3) unsigned DEFAULT '0',
  `pe_3` tinyint(3) unsigned DEFAULT '0',
  `pe_comments` text COLLATE utf8_unicode_ci,
  `ci_1` tinyint(3) unsigned DEFAULT '0',
  `ci_2` tinyint(3) unsigned DEFAULT '0',
  `ci_3` tinyint(3) unsigned DEFAULT '0',
  `ci_4` tinyint(3) unsigned DEFAULT '0',
  `ci_comments` text COLLATE utf8_unicode_ci,
  `m_1` tinyint(3) unsigned DEFAULT '0',
  `m_2` tinyint(3) unsigned DEFAULT '0',
  `m_comments` text COLLATE utf8_unicode_ci,
  `other_comments` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `studente`
--

CREATE TABLE IF NOT EXISTS `studente` (
  `id_utente_studente` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_studente`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `studente`
--

INSERT INTO `studente` (`id_utente_studente`) VALUES
(5);

-- --------------------------------------------------------

--
-- Struttura della tabella `tutor`
--

CREATE TABLE IF NOT EXISTS `tutor` (
  `id_utente_tutor` int(10) unsigned NOT NULL DEFAULT '0',
  `profilo` text COLLATE utf8_unicode_ci,
  `tariffa` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_tutor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `tutor`
--

INSERT INTO `tutor` (`id_utente_tutor`, `profilo`, `tariffa`) VALUES
(4, 'NULL', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `tutor_studenti`
--

CREATE TABLE IF NOT EXISTS `tutor_studenti` (
  `id_utente_tutor` int(10) unsigned NOT NULL DEFAULT '0',
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_tutor`,`id_istanza_corso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `lingua` tinyint(3) DEFAULT '0',
  `timezone` int(11) DEFAULT '0',
  `cap` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `matricola` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id_utente`, `nome`, `cognome`, `tipo`, `e_mail`, `username`, `password`, `layout`, `indirizzo`, `citta`, `provincia`, `nazione`, `codice_fiscale`, `birthdate`, `sesso`, `telefono`, `stato`, `lingua`, `timezone`, `cap`, `matricola`, `avatar`) VALUES
(1, 'admin', 'ada', '2', 'steve@lynxlab.com', 'adminAda', '28dcaeb4eb65eba987aeeb37d228d4526eb37791', NULL, '', '', '', '', '', 0, '', '0123456789', 0, 0, 0, '', '', ''),
(2, 'Switcher', 'Ada 0', '6', 'switcherAda0@lynxlab.com', 'switcherAda0', '079e75c342c92ec665cd32c45488f6ff5bdf8faa', '', 'NULL', 'NULL', 'NULL', 'NULL', 'codfis', 0, 'M', 'NULL', 0, NULL, NULL, '', '', ''),
(3, 'Autore', 'Ada 0', '1', 'autoreAda0@lynxlab.com', 'autoreAda0', '49dad85d588462d9307f4234ba1b75ca934fdea5', '', 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', 0, 'M', 'NULL', 0, NULL, NULL, '', '', ''),
(4, 'Tutor', 'Ada 0', '4', 'tutorAda0@lynxlab.com', 'tutorAda0', '948f343e3f0fc8dae45bf4eac09d6740f82fccc3', '', 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', 0, 'M', 'NULL', 0, NULL, NULL, '', '', ''),
(5, 'studente', 'Ada0', '3', 'studente@ada0.com', 'studenteAda0', '039647c2f69d0402f72ea21346b2e033b6b317dd', '', 'NULL', 'NULL', 'NULL', 'NULL', 'NULL', 0, 'M', 'NULL', 0, NULL, NULL, '', '', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_chatroom`
--

CREATE TABLE IF NOT EXISTS `utente_chatroom` (
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `id_chatroom` int(10) unsigned NOT NULL DEFAULT '0',
  `stato_utente` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tempo_entrata` int(11) NOT NULL DEFAULT '0',
  `tempo_ultimo_evento` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente`,`id_chatroom`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_chatroom_log`
--

CREATE TABLE IF NOT EXISTS `utente_chatroom_log` (
  `tempo` int(11) NOT NULL DEFAULT '0',
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `azione` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_operatore` int(10) unsigned NOT NULL DEFAULT '0',
  `id_chatroom` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tempo`,`id_utente`,`azione`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_log`
--

CREATE TABLE IF NOT EXISTS `utente_log` (
  `id` int(10) unsigned NOT NULL DEFAULT '0',
  `id_utente` int(10) unsigned NOT NULL DEFAULT '0',
  `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `testo` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `utente_log_id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_messaggio_log`
--

CREATE TABLE IF NOT EXISTS `utente_messaggio_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tempo` int(11) NOT NULL DEFAULT '0',
  `id_mittente` int(10) unsigned NOT NULL DEFAULT '0',
  `testo` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tipo` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `titolo` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `id_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `lingua` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'it',
  `id_riceventi` int(10) unsigned DEFAULT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tempo`,`id_mittente`,`testo`),
  UNIQUE KEY `utente_messaggio_log_id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
