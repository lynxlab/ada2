-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Nov 13, 2019 alle 15:59
-- Versione del server: 10.3.20-MariaDB-1:10.3.20+maria~bionic-log
-- Versione PHP: 7.2.24-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `ADA_moreUserFields`
--

CREATE TABLE IF NOT EXISTS `ADA_moreUserFields` (
  `idMoreUserFields` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `studente_id_utente_studente` int(11) DEFAULT NULL,
  PRIMARY KEY (`idMoreUserFields`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `ADA_someMoreUserFields`
--

CREATE TABLE IF NOT EXISTS `ADA_someMoreUserFields` (
  `idSomeMoreUserFields` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `foreign_key` int(11) DEFAULT NULL,
  PRIMARY KEY (`idSomeMoreUserFields`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `amministratore_corsi`
--

CREATE TABLE IF NOT EXISTS `amministratore_corsi` (
  `id_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_utente_amministratore` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_corso`,`id_utente_amministratore`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `amministratore_sistema`
--

CREATE TABLE IF NOT EXISTS `amministratore_sistema` (
  `id_utente_amministratore_sist` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_utente_amministratore_sist`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `autore`
--

CREATE TABLE IF NOT EXISTS `autore` (
  `id_utente_autore` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `profilo` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `tariffa` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_utente_autore`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `banner`
--

CREATE TABLE IF NOT EXISTS `banner` (
  `id_banner` int(10) NOT NULL AUTO_INCREMENT,
  `address` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(80) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_client` int(10) NOT NULL DEFAULT 0,
  `id_course` int(10) NOT NULL DEFAULT 0,
  `module` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `impressions` int(11) NOT NULL DEFAULT 0,
  `a_impressions` int(11) NOT NULL DEFAULT 0,
  `date_from` int(11) DEFAULT NULL,
  `date_to` int(11) DEFAULT NULL,
  KEY `id_banner` (`id_banner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `bookmark`
--

CREATE TABLE IF NOT EXISTS `bookmark` (
  `id_bookmark` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_utente_studente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` int(11) NOT NULL DEFAULT 0,
  `descrizione` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ordering` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_bookmark`),
  KEY `bookmark_date` (`data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `chatroom`
--

CREATE TABLE IF NOT EXISTS `chatroom` (
  `id_chatroom` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tipo_chat` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titolo_chat` text COLLATE utf8_unicode_ci NOT NULL,
  `argomento_chat` text COLLATE utf8_unicode_ci NOT NULL,
  `id_proprietario_chat` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tempo_avvio` int(11) NOT NULL DEFAULT 0,
  `tempo_fine` int(11) NOT NULL DEFAULT 0,
  `msg_benvenuto` text COLLATE utf8_unicode_ci NOT NULL,
  `max_utenti` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_chatroom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `clienti`
--

CREATE TABLE IF NOT EXISTS `clienti` (
  `id_client` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `address` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci DEFAULT NULL,
  UNIQUE KEY `clienti_id` (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `destinatari_messaggi`
--

CREATE TABLE IF NOT EXISTS `destinatari_messaggi` (
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_messaggio` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `read_timestamp` int(11) NOT NULL DEFAULT 0,
  `deleted` char(1) COLLATE utf8_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`id_utente`,`id_messaggio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `history_esercizi`
--

CREATE TABLE IF NOT EXISTS `history_esercizi` (
  `ID_HISTORY_EX` int(10) NOT NULL AUTO_INCREMENT,
  `ID_UTENTE_STUDENTE` int(10) NOT NULL DEFAULT 0,
  `ID_NODO` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `ID_ISTANZA_CORSO` int(10) NOT NULL DEFAULT 0,
  `DATA_VISITA` int(11) NOT NULL DEFAULT 0,
  `DATA_USCITA` int(11) DEFAULT NULL,
  `RISPOSTA_LIBERA` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `COMMENTO` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `PUNTEGGIO` smallint(4) DEFAULT NULL,
  `CORREZIONE_RISPOSTA_LIBERA` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `RIPETIBILE` smallint(1) NOT NULL DEFAULT 0,
  `ALLEGATO` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`ID_HISTORY_EX`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `history_nodi`
--

CREATE TABLE IF NOT EXISTS `history_nodi` (
  `id_history` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_utente_studente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `data_visita` int(11) NOT NULL DEFAULT 0,
  `data_uscita` int(11) NOT NULL DEFAULT 0,
  `session_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `remote_address` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `installation_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `access_from` smallint(5) UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id_history`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `iscrizioni`
--

CREATE TABLE IF NOT EXISTS `iscrizioni` (
  `id_utente_studente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `livello` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED DEFAULT NULL,
  `codice` varchar(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_iscrizione` int(11) DEFAULT NULL,
  `laststatusupdate` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_utente_studente`,`id_istanza_corso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `istanza_corso`
--

CREATE TABLE IF NOT EXISTS `istanza_corso` (
  `id_istanza_corso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data_inizio` int(11) NOT NULL DEFAULT 0,
  `durata` int(10) UNSIGNED DEFAULT NULL,
  `data_inizio_previsto` int(11) NOT NULL DEFAULT 0,
  `id_layout` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data_fine` int(11) NOT NULL DEFAULT 0,
  `status` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(7,2) NOT NULL,
  `self_instruction` tinyint(1) NOT NULL,
  `self_registration` tinyint(1) NOT NULL,
  `start_level_student` int(2) NOT NULL,
  `duration_subscription` int(3) NOT NULL,
  `open_subscription` tinyint(1) NOT NULL,
  `duration_hours` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tipo_servizio` tinyint(3) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_istanza_corso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `link`
--

CREATE TABLE IF NOT EXISTS `link` (
  `id_link` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_nodo_to` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_posizione` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tipo` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `data_creazione` int(11) DEFAULT NULL,
  `stile` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `significato` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `azione` tinyint(3) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_link`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `log_classi`
--

CREATE TABLE IF NOT EXISTS `log_classi` (
  `id_log` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_corso` int(10) UNSIGNED NOT NULL,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL,
  `data` int(11) NOT NULL,
  `visite` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `punti` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `esercizi` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `msg_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `msg_in` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `notes_in` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `notes_out` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `chat` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bookmarks` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `indice_att` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `last_access` int(11) NOT NULL DEFAULT 0,
  `exercises_test` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `score_test` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `exercises_survey` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `score_survey` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `subscription_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `messaggi`
--

CREATE TABLE IF NOT EXISTS `messaggi` (
  `id_messaggio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_group` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data_ora` int(11) NOT NULL DEFAULT 0,
  `tipo` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `titolo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_mittente` int(10) UNSIGNED DEFAULT NULL,
  `priorita` tinyint(3) UNSIGNED DEFAULT NULL,
  `testo` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `flags` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_messaggio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `modello_corso`
--

CREATE TABLE IF NOT EXISTS `modello_corso` (
  `id_corso` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_utente_autore` int(10) UNSIGNED NOT NULL,
  `id_layout` int(10) UNSIGNED DEFAULT 0,
  `nome` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titolo` text COLLATE utf8_unicode_ci NOT NULL,
  `data_creazione` int(11) DEFAULT NULL,
  `data_pubblicazione` int(11) DEFAULT NULL,
  `descrizione` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_nodo_iniziale` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_nodo_toc` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `media_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `static_mode` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `id_lingua` tinyint(3) UNSIGNED NOT NULL,
  `crediti` tinyint(3) NOT NULL DEFAULT 1,
  `duration_hours` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `tipo_servizio` tinyint(3) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id_corso`),
  UNIQUE KEY `modello_corso_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `nodo`
--

CREATE TABLE IF NOT EXISTS `nodo` (
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_posizione` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_istanza` int(10) UNSIGNED DEFAULT NULL,
  `nome` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `titolo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `testo` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `tipo` mediumint(8) UNSIGNED DEFAULT NULL,
  `data_creazione` int(11) DEFAULT NULL,
  `ordine` int(11) DEFAULT NULL,
  `id_nodo_parent` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `livello` tinyint(3) UNSIGNED DEFAULT NULL,
  `versione` tinyint(3) UNSIGNED DEFAULT NULL,
  `n_contatti` int(10) UNSIGNED DEFAULT NULL,
  `icona` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colore_didascalia` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `colore_sfondo` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `correttezza` tinyint(3) UNSIGNED DEFAULT NULL,
  `copyright` tinyint(3) UNSIGNED DEFAULT NULL,
  `lingua` tinyint(3) NOT NULL,
  `pubblicato` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_nodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `posizione`
--

CREATE TABLE IF NOT EXISTS `posizione` (
  `id_posizione` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `x0` int(11) NOT NULL DEFAULT 0,
  `y0` int(11) NOT NULL DEFAULT 0,
  `x1` int(11) NOT NULL DEFAULT 0,
  `y1` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_posizione`),
  UNIQUE KEY `posizione_coords` (`x0`,`y0`,`x1`,`y1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `risorsa_esterna`
--

CREATE TABLE IF NOT EXISTS `risorsa_esterna` (
  `id_risorsa_ext` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome_file` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tipo` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `copyright` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `id_utente` int(10) UNSIGNED NOT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `titolo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descrizione` text COLLATE utf8_unicode_ci NOT NULL,
  `pubblicato` tinyint(1) NOT NULL,
  `lingua` tinyint(3) NOT NULL,
  PRIMARY KEY (`id_risorsa_ext`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `risorse_nodi`
--

CREATE TABLE IF NOT EXISTS `risorse_nodi` (
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT '',
  `id_risorsa_ext` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `peso` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_nodo`,`id_risorsa_ext`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sessione_eguidance`
--

CREATE TABLE IF NOT EXISTS `sessione_eguidance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_tutor` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `event_token` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `data_ora` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `tipo_eguidance` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `ud_1` tinyint(3) UNSIGNED DEFAULT 0,
  `ud_2` tinyint(3) UNSIGNED DEFAULT 0,
  `ud_3` tinyint(3) UNSIGNED DEFAULT 0,
  `ud_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `pc_1` tinyint(3) UNSIGNED DEFAULT 0,
  `pc_2` tinyint(3) UNSIGNED DEFAULT 0,
  `pc_3` tinyint(3) UNSIGNED DEFAULT 0,
  `pc_4` tinyint(3) UNSIGNED DEFAULT 0,
  `pc_5` tinyint(3) UNSIGNED DEFAULT 0,
  `pc_6` tinyint(3) UNSIGNED DEFAULT 0,
  `pc_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ba_1` tinyint(3) UNSIGNED DEFAULT 0,
  `ba_2` tinyint(3) UNSIGNED DEFAULT 0,
  `ba_3` tinyint(3) UNSIGNED DEFAULT 0,
  `ba_4` tinyint(3) UNSIGNED DEFAULT 0,
  `ba_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `t_1` tinyint(3) UNSIGNED DEFAULT 0,
  `t_2` tinyint(3) UNSIGNED DEFAULT 0,
  `t_3` tinyint(3) UNSIGNED DEFAULT 0,
  `t_4` tinyint(3) UNSIGNED DEFAULT 0,
  `t_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `pe_1` tinyint(3) UNSIGNED DEFAULT 0,
  `pe_2` tinyint(3) UNSIGNED DEFAULT 0,
  `pe_3` tinyint(3) UNSIGNED DEFAULT 0,
  `pe_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `ci_1` tinyint(3) UNSIGNED DEFAULT 0,
  `ci_2` tinyint(3) UNSIGNED DEFAULT 0,
  `ci_3` tinyint(3) UNSIGNED DEFAULT 0,
  `ci_4` tinyint(3) UNSIGNED DEFAULT 0,
  `ci_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `m_1` tinyint(3) UNSIGNED DEFAULT 0,
  `m_2` tinyint(3) UNSIGNED DEFAULT 0,
  `m_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_comments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `studente`
--

CREATE TABLE IF NOT EXISTS `studente` (
  `id_utente_studente` int(11) NOT NULL,
  `samplefield` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id_utente_studente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `tutor`
--

CREATE TABLE IF NOT EXISTS `tutor` (
  `id_utente_tutor` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `profilo` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `tariffa` decimal(7,2) UNSIGNED NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_utente_tutor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `tutor_studenti`
--

CREATE TABLE IF NOT EXISTS `tutor_studenti` (
  `id_utente_tutor` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_utente_tutor`,`id_istanza_corso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE IF NOT EXISTS `utente` (
  `id_utente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
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
  `stato` tinyint(3) UNSIGNED NOT NULL,
  `lingua` tinyint(3) DEFAULT 0,
  `timezone` int(11) DEFAULT 0,
  `cap` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `matricola` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `avatar` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `birthcity` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `birthprovince` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_utente`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_chatroom`
--

CREATE TABLE IF NOT EXISTS `utente_chatroom` (
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_chatroom` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `stato_utente` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tempo_entrata` int(11) NOT NULL DEFAULT 0,
  `tempo_ultimo_evento` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_utente`,`id_chatroom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_chatroom_log`
--

CREATE TABLE IF NOT EXISTS `utente_chatroom_log` (
  `tempo` int(11) NOT NULL DEFAULT 0,
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `azione` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `id_operatore` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_chatroom` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`tempo`,`id_utente`,`azione`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_log`
--

CREATE TABLE IF NOT EXISTS `utente_log` (
  `id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_utente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `testo` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY `utente_log_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente_messaggio_log`
--

CREATE TABLE IF NOT EXISTS `utente_messaggio_log` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tempo` int(11) NOT NULL DEFAULT 0,
  `id_mittente` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `testo` varchar(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `tipo` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `titolo` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `id_corso` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `lingua` char(2) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'it',
  `id_riceventi` int(10) UNSIGNED DEFAULT NULL,
  `flags` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`tempo`,`id_mittente`,`testo`),
  UNIQUE KEY `utente_messaggio_log_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `nodo`
--
ALTER TABLE `nodo` ADD FULLTEXT KEY `testo` (`testo`,`titolo`,`nome`);

COMMIT;
