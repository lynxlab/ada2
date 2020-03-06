SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_budget_instance`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_budget_instance` (
  `budget_instance_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL,
  `budget` decimal(8,2) UNSIGNED DEFAULT NULL,
  `references` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`budget_instance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_cost_classroom`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_cost_classroom` (
  `cost_classroom_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_classroom` int(10) UNSIGNED NOT NULL,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL,
  `hourly_rate` decimal(6,2) DEFAULT NULL,
  PRIMARY KEY (`cost_classroom_id`),
  UNIQUE KEY `id_classroom_instance` (`id_classroom`,`id_istanza_corso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_cost_item`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_cost_item` (
  `cost_item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `applied_to` int(2) DEFAULT NULL,
  PRIMARY KEY (`cost_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_cost_tutor`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_cost_tutor` (
  `cost_tutor_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_tutor` int(10) UNSIGNED NOT NULL,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL,
  `hourly_rate` decimal(7,2) DEFAULT NULL,
  PRIMARY KEY (`cost_tutor_id`),
  UNIQUE KEY `id_classroom_instance` (`id_tutor`,`id_istanza_corso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;
