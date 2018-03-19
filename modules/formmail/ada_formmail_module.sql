--
-- RUN THIS SCRIPT ONCE PER EVERY PROVIDER
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Struttura della tabella `module_formmail_helptype`
--

CREATE TABLE `module_formmail_helptype` (
  `module_formmail_helptype_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_type` tinyint(2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_formmail_history`
--

CREATE TABLE `module_formmail_history` (
  `module_formmail_history` int(10) UNSIGNED NOT NULL,
  `id_utente` int(10) UNSIGNED NOT NULL,
  `module_formmail_helptype_id` int(10) UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `msgbody` text COLLATE utf8_unicode_ci NOT NULL,
  `attachments` text COLLATE utf8_unicode_ci,
  `selfSent` tinyint(1) UNSIGNED NOT NULL,
  `sentOK` tinyint(1) UNSIGNED NOT NULL,
  `sentTimestamp` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `module_formmail_helptype`
--
ALTER TABLE `module_formmail_helptype`
  ADD PRIMARY KEY (`module_formmail_helptype_id`);

--
-- Indici per le tabelle `module_formmail_history`
--
ALTER TABLE `module_formmail_history`
  ADD PRIMARY KEY (`module_formmail_history`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `module_formmail_helptype`
--
ALTER TABLE `module_formmail_helptype`
  MODIFY `module_formmail_helptype_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT per la tabella `module_formmail_history`
--
ALTER TABLE `module_formmail_history`
  MODIFY `module_formmail_history` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;