-- phpMyAdmin SQL Dump
-- version 4.2.3deb1.trusty~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Set 17, 2014 alle 18:19
-- Versione del server: 5.5.38-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.4

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classroom_classrooms`
--

CREATE TABLE IF NOT EXISTS `module_classroom_classrooms` (
`id_classroom` int(10) unsigned NOT NULL,
  `id_venue` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seats` tinyint(4) unsigned DEFAULT NULL,
  `computers` tinyint(4) unsigned DEFAULT NULL,
  `internet` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `wifi` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `projector` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `mobility_impaired` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hourly_rate` decimal(6,2) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classroom_venues`
--

CREATE TABLE IF NOT EXISTS `module_classroom_venues` (
`id_venue` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `addressline1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addressline2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `map_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classroom_classrooms`
--
ALTER TABLE `module_classroom_classrooms`
 ADD PRIMARY KEY (`id_classroom`);

--
-- Indexes for table `module_classroom_venues`
--
ALTER TABLE `module_classroom_venues`
 ADD PRIMARY KEY (`id_venue`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classroom_classrooms`
--
ALTER TABLE `module_classroom_classrooms`
MODIFY `id_classroom` int(10) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `module_classroom_venues`
--
ALTER TABLE `module_classroom_venues`
MODIFY `id_venue` int(10) unsigned NOT NULL AUTO_INCREMENT;
