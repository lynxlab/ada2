-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Set 12, 2018 alle 15:42
-- Versione del server: 5.7.23
-- Versione PHP: 5.6.37-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ada_common`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_secretquestion_qa`
--

CREATE TABLE `module_secretquestion_qa` (
  `id_utente` int(10) UNSIGNED NOT NULL,
  `question` text COLLATE utf8_unicode_ci NOT NULL,
  `answerhash` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_secretquestion_qa`
--
ALTER TABLE `module_secretquestion_qa`
  ADD PRIMARY KEY (`id_utente`);
