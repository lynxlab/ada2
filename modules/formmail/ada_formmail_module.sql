--
-- RUN THIS SCRIPT ONCE PER EVERY PROVIDER
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_formmail_helptype`
--

CREATE TABLE IF NOT EXISTS `module_formmail_helptype` (
  `module_formmail_helptype_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_type` tinyint(2) UNSIGNED NOT NULL,
  PRIMARY KEY (`module_formmail_helptype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_formmail_history`
--

CREATE TABLE IF NOT EXISTS `module_formmail_history` (
  `module_formmail_history` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_utente` int(10) UNSIGNED NOT NULL,
  `module_formmail_helptype_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `msgbody` text COLLATE utf8_unicode_ci NOT NULL,
  `attachments` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `selfSent` tinyint(1) UNSIGNED NOT NULL,
  `sentOK` tinyint(1) UNSIGNED NOT NULL,
  `sentTimestamp` int(10) NOT NULL,
  PRIMARY KEY (`module_formmail_history`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;