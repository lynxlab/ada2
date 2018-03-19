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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

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

--
-- Struttura della tabella `module_classagenda_rollcall`
--

CREATE TABLE IF NOT EXISTS `module_classagenda_rollcall` (
`module_classagenda_rollcall_id` int(10) unsigned NOT NULL,
  `id_utente_studente` int(10) NOT NULL,
  `module_classagenda_calendars_id` int(10) NOT NULL,
  `entertime` int(11) unsigned NOT NULL,
  `exittime` int(11) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classagenda_rollcall`
--
ALTER TABLE `module_classagenda_rollcall`
 ADD PRIMARY KEY (`module_classagenda_rollcall_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classagenda_rollcall`
--
ALTER TABLE `module_classagenda_rollcall`
MODIFY `module_classagenda_rollcall_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

--
-- Struttura della tabella `module_classagenda_reminder_history`
--

CREATE TABLE IF NOT EXISTS `module_classagenda_reminder_history` (
  `module_classagenda_reminder_history_id` int(10) unsigned NOT NULL,
  `module_classagenda_calendars_id` int(10) unsigned NOT NULL,
  `html` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classagenda_reminder_history`
--
ALTER TABLE `module_classagenda_reminder_history`
 ADD PRIMARY KEY (`module_classagenda_reminder_history_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classagenda_reminder_history`
--
ALTER TABLE `module_classagenda_reminder_history`
MODIFY `module_classagenda_reminder_history_id` int(10) unsigned NOT NULL AUTO_INCREMENT;