-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 25, 2021 alle 17:26
-- Versione del server: 10.5.8-MariaDB-1:10.5.8+maria~focal
-- Versione PHP: 7.2.34-9+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_impersonate_linkedusers`
--

CREATE TABLE `module_impersonate_linkedusers` (
  `source_id` int(10) UNSIGNED NOT NULL COMMENT 'source user id',
  `linked_id` int(10) UNSIGNED NOT NULL COMMENT 'linked user id',
  `source_type` tinyint(3) UNSIGNED NOT NULL COMMENT 'source user type',
  `linked_type` tinyint(3) UNSIGNED NOT NULL COMMENT 'linked user type',
  `is_active` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 if link is active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_impersonate_linkedusers`
--
ALTER TABLE `module_impersonate_linkedusers`
  ADD PRIMARY KEY (`source_id`,`linked_id`) USING BTREE,
  ADD KEY `fk_impersonate_linked_utente_id` (`linked_id`);

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `module_impersonate_linkedusers`
--
ALTER TABLE `module_impersonate_linkedusers`
  ADD CONSTRAINT `fk_impersonate_linked_utente_id` FOREIGN KEY (`linked_id`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_impersonate_source_utente_id` FOREIGN KEY (`source_id`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
