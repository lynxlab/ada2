-- phpMyAdmin SQL Dump
-- version 4.0.9deb1.saucy~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Dic 09, 2013 alle 13:54
-- Versione del server: 5.5.34-0ubuntu0.13.10.1
-- Versione PHP: 5.5.3-1ubuntu2

-- --------------------------------------------------------

--
-- Struttura della tabella `module_complete_conditionset`
--

CREATE TABLE IF NOT EXISTS `module_complete_conditionset` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `descrizione` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_complete_conditionset_course`
--

CREATE TABLE IF NOT EXISTS `module_complete_conditionset_course` (
  `id_conditionset` int(10) unsigned NOT NULL COMMENT 'id of the completeset rule',
  `id_course` int(10) unsigned NOT NULL COMMENT 'id of the course linked to the completeset rule',
  PRIMARY KEY (`id_conditionset`,`id_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_complete_operations`
--

CREATE TABLE IF NOT EXISTS `module_complete_operations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_conditionset` int(11) NOT NULL,
  `operator` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `operand1` text COLLATE utf8_unicode_ci NOT NULL,
  `operand2` text COLLATE utf8_unicode_ci,
  `priority` int(11) NOT NULL COMMENT 'this is called priority but it''s used to tell in which column of the UI is the conditionSet',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
