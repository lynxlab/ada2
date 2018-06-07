-- phpMyAdmin SQL Dump
-- version 4.6.6deb1+deb.cihar.com~xenial.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Mag 02, 2018 alle 10:44
-- Versione del server: 5.7.22
-- Versione PHP: 5.6.35-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ada_common`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_gdpr_policy_content`
--

CREATE TABLE `module_gdpr_policy_content` (
  `policy_content_id` int(10) UNSIGNED NOT NULL,
  `title` text COLLATE utf8_unicode_ci,
  `content` text COLLATE utf8_unicode_ci,
  `tester_pointer` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `mandatory` tinyint(3) UNSIGNED DEFAULT '0',
  `isPublished` tinyint(3) UNSIGNED DEFAULT '0' NOT NULL,
  `lastEditTS` int(11) NOT NULL,
  `version` int(11) UNSIGNED DEFAULT'1' NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Struttura della tabella `module_gdpr_policy_utente`
--

CREATE TABLE `module_gdpr_policy_utente` (
  `id_utente` int(10) UNSIGNED NOT NULL,
  `id_policy` int(10) UNSIGNED NOT NULL,
  `acceptedVersion` int(11) UNSIGNED NOT NULL,
  `lastmodTS` int(11) UNSIGNED NOT NULL,
  `isAccepted` tinyint(3) UNSIGNED DEFAULT '0' NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_gdpr_policy_content`
--
ALTER TABLE `module_gdpr_policy_content`
  ADD PRIMARY KEY (`policy_content_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_gdpr_policy_content`
--
ALTER TABLE `module_gdpr_policy_content`
  MODIFY `policy_content_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
  
--
-- Indici per le tabelle `module_gdpr_policy_utente`
--
ALTER TABLE `module_gdpr_policy_utente`
  ADD PRIMARY KEY (`id_utente`,`id_policy`,`acceptedVersion`);
