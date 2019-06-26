-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Giu 20, 2019 alle 15:18
-- Versione del server: 10.3.16-MariaDB-1:10.3.16+maria~bionic-log
-- Versione PHP: 7.2.19-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
--
-- Struttura della tabella `module_badges_badges`
--

CREATE TABLE `module_badges_badges` (
  `uuid_bin` binary(16) NOT NULL,
  `uuid` varchar(36) CHARACTER SET utf8 GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`uuid_bin`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
  `name` varchar(36) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `criteria` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
--
-- Struttura della tabella `module_badges_course_badges`
--

CREATE TABLE `module_badges_course_badges` (
  `badge_uuid_bin` binary(16) NOT NULL,
  `badge_uuid` varchar(36) CHARACTER SET utf8 GENERATED ALWAYS AS (insert(insert(insert(insert(hex(`badge_uuid_bin`),9,0,'-'),14,0,'-'),19,0,'-'),24,0,'-')) VIRTUAL,
  `id_corso` int(10) UNSIGNED NOT NULL,
  `id_conditionset` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle `module_badges_badges`
--
ALTER TABLE `module_badges_badges`
  ADD PRIMARY KEY (`uuid_bin`);

--
-- Indici per le tabelle `module_badges_course_badges`
--
ALTER TABLE `module_badges_course_badges`
  ADD UNIQUE KEY `course_badges_idx` (`badge_uuid_bin`,`id_corso`,`id_conditionset`) USING BTREE,
  ADD KEY `fk_badge_uuid` (`badge_uuid_bin`),
  ADD KEY `fk_id_conditionset` (`id_conditionset`);

--
-- Limiti per la tabella `module_badges_course_badges`
--
ALTER TABLE `module_badges_course_badges`
  ADD CONSTRAINT `fk_badge_uuid` FOREIGN KEY (`badge_uuid_bin`) REFERENCES `module_badges_badges` (`uuid_bin`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_id_conditionset` FOREIGN KEY (`id_conditionset`) REFERENCES `module_complete_conditionset` (`id`) ON DELETE CASCADE;

COMMIT;
