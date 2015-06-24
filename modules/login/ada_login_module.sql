-- phpMyAdmin SQL Dump
-- version 4.2.3deb1.trusty~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Giu 24, 2015 alle 11:34
-- Versione del server: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_login_history_login`
--

CREATE TABLE IF NOT EXISTS `module_login_history_login` (
  `id_utente` int(10) unsigned NOT NULL,
  `date` int(11) NOT NULL,
  `module_login_providers_id` int(5) unsigned NOT NULL,
  `successfulOptionsID` int(5) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_login_options`
--

CREATE TABLE IF NOT EXISTS `module_login_options` (
  `module_login_providers_options_id` int(5) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Struttura della tabella `module_login_providers_options`
--

CREATE TABLE IF NOT EXISTS `module_login_providers_options` (
  `module_login_providers_options_id` int(5) unsigned NOT NULL,
  `module_login_providers_id` int(5) unsigned NOT NULL,
  `order` int(5) unsigned NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_login_providers`
--

CREATE TABLE IF NOT EXISTS `module_login_providers` (
`module_login_providers_id` int(5) unsigned NOT NULL,
  `className` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `buttonLabel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayOrder` int(4) unsigned NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_login_history_login`
--
ALTER TABLE `module_login_history_login`
 ADD PRIMARY KEY (`id_utente`,`date`,`module_login_providers_id`);

--
-- Indexes for table `module_login_options`
--
ALTER TABLE `module_login_options`
 ADD PRIMARY KEY (`key`,`module_login_providers_options_id`);

--
-- Indexes for table `module_login_providers`
--
ALTER TABLE `module_login_providers`
 ADD PRIMARY KEY (`module_login_providers_id`);

--
-- Indexes for table `module_login_providers_options`
--
ALTER TABLE `module_login_providers_options`
 ADD PRIMARY KEY (`module_login_providers_options_id`);
 
--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_login_providers`
--
ALTER TABLE `module_login_providers`
MODIFY `module_login_providers_id` int(5) unsigned NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `module_login_providers_options`
--
ALTER TABLE `module_login_providers_options`
MODIFY `module_login_providers_options_id` int(5) unsigned NOT NULL AUTO_INCREMENT;
