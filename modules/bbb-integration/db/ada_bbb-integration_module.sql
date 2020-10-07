-- phpMyAdmin SQL Dump
-- version 4.9.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Creato il: Ago 13, 2020 alle 12:28
-- Versione del server: 10.3.24-MariaDB-1:10.3.24+maria~bionic-log
-- Versione PHP: 7.2.33-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ada_provider1`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `module_bigbluebutton_meeting`
--

CREATE TABLE `module_bigbluebutton_meeting` (
  `openmeetings_room_id` int(11) UNSIGNED NOT NULL,
  `meetingID` binary(16) NOT NULL,
  `attendeePW` binary(16) NOT NULL,
  `moderatorPW` binary(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_bigbluebutton_meeting`
--
ALTER TABLE `module_bigbluebutton_meeting`
  ADD PRIMARY KEY (`openmeetings_room_id`);
COMMIT;
