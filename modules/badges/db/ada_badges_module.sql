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

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_badges_badges`
--
ALTER TABLE `module_badges_badges`
  ADD PRIMARY KEY (`uuid_bin`);
COMMIT;
