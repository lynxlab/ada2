-- phpMyAdmin SQL Dump
-- version 4.0.6deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Apr 28, 2014 alle 09:06
-- Versione del server: 5.5.35-0ubuntu0.13.10.2
-- Versione PHP: 5.5.3-1ubuntu2.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ada_provider1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `amministratore_corsi`
--

CREATE TABLE IF NOT EXISTS `amministratore_corsi` (
  `id_corso` int(10) unsigned NOT NULL DEFAULT '0',
  `id_utente_amministratore` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_corso`,`id_utente_amministratore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `tariffa` decimal(7,2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_autore`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `autore`
--

INSERT INTO `autore` (`id_utente_autore`, `profilo`, `tariffa`) VALUES
(2, NULL, 0);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `language` tinyint(3) NOT NULL
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
  PRIMARY KEY (`ID_HISTORY_EX`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  PRIMARY KEY (`id_history`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

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
  `duration_hours` int unsigned NOT NULL DEFAULT '0',
  `tipo_servizio` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id_istanza_corso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  PRIMARY KEY (`id_link`),
  UNIQUE KEY `unique-link` (`id_nodo`,`id_nodo_to`) COMMENT 'prevents link duplication'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  PRIMARY KEY (`id_messaggio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `duration_hours` int unsigned NOT NULL DEFAULT '0',
  `tipo_servizio` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_corso`),
  UNIQUE KEY `modello_corso_nome` (`nome`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `tariffa` decimal(7,2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_utente_tutor`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `tutor`
--

INSERT INTO `tutor` (`id_utente_tutor`, `profilo`, `tariffa`) VALUES
(3, 'NULL', 0);

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
  `birthdate` int(12) DEFAULT NULL,
  `sesso` enum('F','M') COLLATE utf8_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stato` tinyint(3) unsigned NOT NULL,
  `lingua` tinyint(3) DEFAULT '0',
  `timezone` int(11) DEFAULT '0',
  `cap` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `matricola` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `birthcity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `birthprovince` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id_utente`, `nome`, `cognome`, `tipo`, `e_mail`, `username`, `password`, `layout`, `indirizzo`, `citta`, `provincia`, `nazione`, `codice_fiscale`, `birthdate`, `sesso`, `telefono`, `stato`, `lingua`, `timezone`, `cap`, `matricola`, `avatar`, `birthcity`, `birthprovince`) VALUES
(1, 'admin', 'ada', '2', 'admin@lynxlab.com', 'adminAda', '28dcaeb4eb65eba987aeeb37d228d4526eb37791', NULL, '', '', '', '', '', 0, '', '0123456789', 0, 0, 0, '', '', '', '', NULL),
(2, 'nomeAutoreAda1', 'cognomeAutoreAda1', '1', 'autore@lynxlab.com', 'autoreAda1', '078139c99e73077fb7db87e49df8fe43cf7ca1b3', 'silver_gray', 'via o piazza', 'roma', 'RM', 'ZW', 'NULL', 33, 'M', '12341234', 0, NULL, NULL, '', '', '', '', NULL),
(3, 'nomeTutorAda1', 'cognomeTutorAda1', '4', 'tutor@lynxlab.com', 'tutorAda1', 'e74e0b5df5cf02436a1c33509c42f76eb36bcabf', 'ada_blu', 'via ostiense 60/d', 'roma', 'roma', 'ZW', 'NULL', 0, 'M', NULL, 0, 1, 0, '', '', '', '', NULL),
(4, 'nomeSwitcherAda1', 'cognomeSwitcherAda1', '6', 'coordinator@lynxlab.com', 'switcherAda1', '64baf2b0844f66cd95c50e716f089052f1682572', NULL, 'via ostiense 60/d', 'roma', 'roma', 'ZW', 'NULL', 0, 'M', NULL, 0, 2, NULL, '', '', '', '', NULL),
(5, 'nomeStudenteAda1', 'cognomeStudenteAda1', '3', 'studente@lynxlab.com', 'studenteAda1', '7d4ee672efb7827f7be8f2dffb471353f4b88d3a', '', NULL, NULL, 'RM', 'ZW', 'NULL', 28, 'M', NULL, 0, 1, 0, '', '', '', '', NULL);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
