--
-- Struttura della tabella `module_classagenda_calendars`
--

CREATE TABLE IF NOT EXISTS `module_classagenda_calendars` (
`module_classagenda_calendars_id` int(10) unsigned NOT NULL,
  `start` int(11) unsigned NOT NULL,
  `end` int(11) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL,
  `id_classroom` int(10) unsigned DEFAULT NULL,
  `id_utente_tutor` int(10) unsigned NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classagenda_calendars`
--
ALTER TABLE `module_classagenda_calendars`
 ADD PRIMARY KEY (`module_classagenda_calendars_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classagenda_calendars`
--
ALTER TABLE `module_classagenda_calendars`
MODIFY `module_classagenda_calendars_id` int(10) unsigned NOT NULL AUTO_INCREMENT;