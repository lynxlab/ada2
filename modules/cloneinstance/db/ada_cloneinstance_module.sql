-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Set 15, 2022 alle 17:25
-- Versione del server: 10.5.17-MariaDB-1:10.5.17+maria~ubu2004
-- Versione PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_cloneinstance_history`
--

CREATE TABLE `module_cloneinstance_history` (
  `module_cloneinstance_history_id` int(10) UNSIGNED NOT NULL,
  `instanceId` int(10) UNSIGNED NOT NULL,
  `clonedInCourse` int(10) UNSIGNED NOT NULL,
  `clonedInstanceId` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `cloneTimestamp` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_cloneinstance_history`
--
ALTER TABLE `module_cloneinstance_history`
  ADD PRIMARY KEY (`module_cloneinstance_history_id`),
  ADD KEY `instanceId` (`instanceId`),
  ADD KEY `clonedInCourse` (`clonedInCourse`),
  ADD KEY `clonedInstanceId` (`clonedInstanceId`),
  ADD KEY `userId` (`userId`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_cloneinstance_history`
--
ALTER TABLE `module_cloneinstance_history`
  MODIFY `module_cloneinstance_history_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `module_cloneinstance_history`
--
ALTER TABLE `module_cloneinstance_history`
  ADD CONSTRAINT `module_cloneinstance_history_ibfk_1` FOREIGN KEY (`instanceId`) REFERENCES `istanza_corso` (`id_istanza_corso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `module_cloneinstance_history_ibfk_2` FOREIGN KEY (`clonedInCourse`) REFERENCES `modello_corso` (`id_corso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `module_cloneinstance_history_ibfk_3` FOREIGN KEY (`clonedInstanceId`) REFERENCES `istanza_corso` (`id_istanza_corso`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `module_cloneinstance_history_ibfk_4` FOREIGN KEY (`userId`) REFERENCES `utente` (`id_utente`);
COMMIT;
