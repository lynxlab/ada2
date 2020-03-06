SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";
--
-- Struttura della tabella `module_classagenda_calendars`
--

CREATE TABLE IF NOT EXISTS `module_classagenda_calendars` (
  `module_classagenda_calendars_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `start` int(11) UNSIGNED NOT NULL,
  `end` int(11) UNSIGNED NOT NULL,
  `id_istanza_corso` int(10) UNSIGNED NOT NULL,
  `id_classroom` int(10) UNSIGNED DEFAULT NULL,
  `id_utente_tutor` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`module_classagenda_calendars_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Struttura della tabella `module_classagenda_rollcall`
--

CREATE TABLE IF NOT EXISTS `module_classagenda_rollcall` (
  `module_classagenda_rollcall_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_utente_studente` int(10) NOT NULL,
  `module_classagenda_calendars_id` int(10) NOT NULL,
  `entertime` int(11) UNSIGNED NOT NULL,
  `exittime` int(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`module_classagenda_rollcall_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Struttura della tabella `module_classagenda_reminder_history`
--

CREATE TABLE IF NOT EXISTS `module_classagenda_reminder_history` (
  `module_classagenda_reminder_history_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `module_classagenda_calendars_id` int(10) UNSIGNED NOT NULL,
  `html` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(11) NOT NULL,
  PRIMARY KEY (`module_classagenda_reminder_history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;
