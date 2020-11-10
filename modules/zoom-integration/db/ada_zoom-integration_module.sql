-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Set 17, 2020 alle 16:05
-- Versione del server: 10.5.5-MariaDB-1:10.5.5+maria~focal
-- Versione PHP: 7.2.33-1+ubuntu20.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ada_provider1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_zoomconf_meeting`
--

CREATE TABLE `module_zoomconf_meeting` (
  `openmeetings_room_id` int(11) UNSIGNED NOT NULL,
  `meetingID` bigint(20) UNSIGNED NOT NULL,
  `meetingPWD` varchar(10) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_zoomconf_meeting`
--
ALTER TABLE `module_zoomconf_meeting`
  ADD PRIMARY KEY (`openmeetings_room_id`);
COMMIT;
