-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Apr 20, 2021 alle 18:44
-- Versione del server: 10.5.9-MariaDB-1:10.5.9+maria~focal
-- Versione PHP: 7.2.34-18+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_notifications_emailqueue`
--

CREATE TABLE `module_notifications_emailqueue` (
  `id` int(10) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `recipientEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `recipientFullName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `emailType` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(8) UNSIGNED NOT NULL,
  `sendResult` int(8) UNSIGNED DEFAULT NULL,
  `enqueueTS` int(11) NOT NULL,
  `processTS` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_notifications_notification`
--

CREATE TABLE `module_notifications_notification` (
  `notificationId` int(10) UNSIGNED NOT NULL,
  `notificationType` int(8) UNSIGNED NOT NULL,
  `userId` int(10) UNSIGNED NOT NULL,
  `nodeId` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `instanceId` int(10) DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL,
  `jsonField` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `creationTS` datetime NOT NULL,
  `lastEditTS` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_notifications_emailqueue`
--
ALTER TABLE `module_notifications_emailqueue`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `module_notifications_notification`
--
ALTER TABLE `module_notifications_notification`
  ADD PRIMARY KEY (`notificationId`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_notifications_emailqueue`
--
ALTER TABLE `module_notifications_emailqueue`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `module_notifications_notification`
--
ALTER TABLE `module_notifications_notification`
  MODIFY `notificationId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;
