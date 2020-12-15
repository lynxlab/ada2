-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Dic 01, 2020 alle 10:33
-- Versione del server: 10.5.8-MariaDB-1:10.5.8+maria~focal
-- Versione PHP: 7.2.34-8+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_collaboraacl_files`
--

CREATE TABLE `module_collaboraacl_files` (
  `id` int(10) UNSIGNED NOT NULL,
  `filepath` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'path relative to ada ROOT_DIR',
  `id_corso` int(10) UNSIGNED NOT NULL,
  `id_istanza` int(10) UNSIGNED NOT NULL,
  `id_nodo` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id_owner` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_collaboraacl_files_utente`
--

CREATE TABLE `module_collaboraacl_files_utente` (
  `file_id` int(10) UNSIGNED NOT NULL,
  `utente_id` int(10) UNSIGNED NOT NULL,
  `permissions` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_collaboraacl_files`
--
ALTER TABLE `module_collaboraacl_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `filepath` (`filepath`,`id_nodo`) USING HASH,
  ADD KEY `fk_collaboraacl_files_nodo` (`id_nodo`),
  ADD KEY `fk_collaboraacl_files_corso` (`id_corso`),
  ADD KEY `fk_collaboraacl_files_istanza` (`id_istanza`),
  ADD KEY `fk_collaboraacl_files_owner` (`id_owner`);

--
-- Indici per le tabelle `module_collaboraacl_files_utente`
--
ALTER TABLE `module_collaboraacl_files_utente`
  ADD UNIQUE KEY `file_id` (`file_id`,`utente_id`),
  ADD KEY `fk_collaboraacl_users` (`utente_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_collaboraacl_files`
--
ALTER TABLE `module_collaboraacl_files`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `module_collaboraacl_files`
--
ALTER TABLE `module_collaboraacl_files`
  ADD CONSTRAINT `fk_collaboraacl_files_corso` FOREIGN KEY (`id_corso`) REFERENCES `modello_corso` (`id_corso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_collaboraacl_files_istanza` FOREIGN KEY (`id_istanza`) REFERENCES `istanza_corso` (`id_istanza_corso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_collaboraacl_files_nodo` FOREIGN KEY (`id_nodo`) REFERENCES `nodo` (`id_nodo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_collaboraacl_files_owner` FOREIGN KEY (`id_owner`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE;

--
-- Limiti per la tabella `module_collaboraacl_files_utente`
--
ALTER TABLE `module_collaboraacl_files_utente`
  ADD CONSTRAINT `fk_collaboraacl_files` FOREIGN KEY (`file_id`) REFERENCES `module_collaboraacl_files` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_collaboraacl_users` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
