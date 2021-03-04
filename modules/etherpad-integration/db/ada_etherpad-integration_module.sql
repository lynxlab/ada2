-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mar 02, 2021 alle 17:48
-- Versione del server: 10.5.9-MariaDB-1:10.5.9+maria~focal
-- Versione PHP: 7.2.34-18+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_etherpad_authors`
--

CREATE TABLE `module_etherpad_authors` (
  `userId` int(10) UNSIGNED NOT NULL,
  `authorId` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_etherpad_groups`
--

CREATE TABLE `module_etherpad_groups` (
  `instanceId` int(10) UNSIGNED NOT NULL,
  `groupId` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_etherpad_hashkey`
--

CREATE TABLE `module_etherpad_hashkey` (
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `isActive` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_etherpad_pads`
--

CREATE TABLE `module_etherpad_pads` (
  `padId` int(11) NOT NULL,
  `groupId` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `nodeId` varchar(10) COLLATE utf8_unicode_ci NOT NULL COMMENT '''all'' means an instance pad not linked to any node',
  `padName` text COLLATE utf8_unicode_ci NOT NULL,
  `realPadName` text COLLATE utf8_unicode_ci NOT NULL,
  `isActive` tinyint(1) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_etherpad_sessions`
--

CREATE TABLE `module_etherpad_sessions` (
  `groupId` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `authorId` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sessionId` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `validUntil` int(10) NOT NULL,
  `creationDate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_etherpad_authors`
--
ALTER TABLE `module_etherpad_authors`
  ADD PRIMARY KEY (`userId`) USING BTREE,
  ADD UNIQUE KEY `authorId` (`authorId`);

--
-- Indici per le tabelle `module_etherpad_groups`
--
ALTER TABLE `module_etherpad_groups`
  ADD PRIMARY KEY (`instanceId`) USING BTREE,
  ADD UNIQUE KEY `groupId` (`groupId`);

--
-- Indici per le tabelle `module_etherpad_hashkey`
--
ALTER TABLE `module_etherpad_hashkey`
  ADD PRIMARY KEY (`uuid`,`isActive`);

--
-- Indici per le tabelle `module_etherpad_pads`
--
ALTER TABLE `module_etherpad_pads`
  ADD PRIMARY KEY (`padId`),
  ADD KEY `etherpad_pads_groups` (`groupId`);

--
-- Indici per le tabelle `module_etherpad_sessions`
--
ALTER TABLE `module_etherpad_sessions`
  ADD KEY `etherpad_session_group` (`groupId`),
  ADD KEY `etherpad_session_author` (`authorId`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_etherpad_pads`
--
ALTER TABLE `module_etherpad_pads`
  MODIFY `padId` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `module_etherpad_authors`
--
ALTER TABLE `module_etherpad_authors`
  ADD CONSTRAINT `etherpad_author_user` FOREIGN KEY (`userId`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `module_etherpad_groups`
--
ALTER TABLE `module_etherpad_groups`
  ADD CONSTRAINT `etherpad_group_instance` FOREIGN KEY (`instanceId`) REFERENCES `istanza_corso` (`id_istanza_corso`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `module_etherpad_pads`
--
ALTER TABLE `module_etherpad_pads`
  ADD CONSTRAINT `etherpad_pads_groups` FOREIGN KEY (`groupId`) REFERENCES `module_etherpad_groups` (`groupId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `module_etherpad_sessions`
--
ALTER TABLE `module_etherpad_sessions`
  ADD CONSTRAINT `etherpad_session_author` FOREIGN KEY (`authorId`) REFERENCES `module_etherpad_authors` (`authorId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `etherpad_session_group` FOREIGN KEY (`groupId`) REFERENCES `module_etherpad_groups` (`groupId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
