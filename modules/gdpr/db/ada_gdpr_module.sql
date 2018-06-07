-- phpMyAdmin SQL Dump
-- version 4.6.6deb1+deb.cihar.com~xenial.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Apr 06, 2018 alle 15:25
-- Versione del server: 5.7.21
-- Versione PHP: 5.6.35-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ada_provider1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_gdpr_requests`
--

CREATE TABLE `module_gdpr_requests` (
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `generatedBy` int(11) NOT NULL,
  `generatedTs` int(11) NOT NULL,
  `confirmedTs` int(11) DEFAULT NULL,
  `closedBy` int(11) DEFAULT NULL,
  `closedTs` int(11) DEFAULT NULL,
  `type` int(11) NOT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `selfOpened` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_gdpr_requestTypes`
--

CREATE TABLE `module_gdpr_requestTypes` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` int(10) UNSIGNED NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `extra` text COLLATE utf8_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_gdpr_users`
--

CREATE TABLE `module_gdpr_users` (
  `id_utente` int(10) UNSIGNED NOT NULL,
  `type` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_gdpr_userTypes`
--

CREATE TABLE `module_gdpr_userTypes` (
  `id` int(10) UNSIGNED NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_gdpr_requests`
--
ALTER TABLE `module_gdpr_requests`
  ADD PRIMARY KEY (`uuid`);

--
-- Indici per le tabelle `module_gdpr_requestTypes`
--
ALTER TABLE `module_gdpr_requestTypes`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `module_gdpr_users`
--
ALTER TABLE `module_gdpr_users`
  ADD PRIMARY KEY (`id_utente`,`type`) USING BTREE;

--
-- Indici per le tabelle `module_gdpr_userTypes`
--
ALTER TABLE `module_gdpr_userTypes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_gdpr_requestTypes`
--
ALTER TABLE `module_gdpr_requestTypes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `module_gdpr_userTypes`
--
ALTER TABLE `module_gdpr_userTypes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
--
-- Dump dei dati per la tabella `module_gdpr_requestTypes`
--

INSERT INTO `module_gdpr_requestTypes` (`id`, `type`, `description`, `extra`) VALUES
(1, 4, 'Cancellazione', '{\"confirmhandle\":true}'),
(2, 1, 'Accesso', NULL),
(3, 2, 'Modifica', NULL),
(4, 3, 'Limita', '{\"confirmhandle\":true}'),
(5, 5, 'Opposizione', NULL);

--
-- Dump dei dati per la tabella `module_gdpr_userTypes`
--

INSERT INTO `module_gdpr_userTypes` (`id`, `description`) VALUES
(1, 'Manager'),
(2, 'Nessuno');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
