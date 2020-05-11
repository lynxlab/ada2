-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Apr 28, 2020 alle 11:43
-- Versione del server: 10.3.22-MariaDB-1:10.3.22+maria~bionic-log
-- Versione PHP: 7.2.30-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ada_provider1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_studentsgroups_groups`
--

CREATE TABLE `module_studentsgroups_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `customField0` int(10) UNSIGNED DEFAULT NULL COMMENT 'application managed',
  `customField1` int(10) UNSIGNED DEFAULT NULL COMMENT 'application managed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_studentsgroups_groups_utente`
--

CREATE TABLE `module_studentsgroups_groups_utente` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `utente_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_studentsgroups_groups`
--
ALTER TABLE `module_studentsgroups_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `module_studentsgroups_groups_utente`
--
ALTER TABLE `module_studentsgroups_groups_utente`
  ADD UNIQUE KEY `group_utente_idx` (`group_id`,`utente_id`),
  ADD KEY `fk_studentsgroups_utente` (`utente_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_studentsgroups_groups`
--
ALTER TABLE `module_studentsgroups_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `module_studentsgroups_groups_utente`
--
ALTER TABLE `module_studentsgroups_groups_utente`
  ADD CONSTRAINT `fk_studentsgroups_groups` FOREIGN KEY (`group_id`) REFERENCES `module_studentsgroups_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_studentsgroups_utente` FOREIGN KEY (`utente_id`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
