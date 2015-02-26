-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_budget_instance`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_budget_instance` (
  `budget_instance_id` int(11) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL,
  `budget` decimal(8,2) unsigned DEFAULT NULL,
  `references` text COLLATE utf8_unicode_ci,
  `notes` text COLLATE utf8_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classbudget_budget_instance`
--
ALTER TABLE `module_classbudget_budget_instance`
 ADD PRIMARY KEY (`module_classbudget_budget_instance_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classbudget_budget_instance`
--
ALTER TABLE `module_classbudget_budget_instance`
MODIFY `module_classbudget_budget_instance_id` int(11) unsigned NOT NULL AUTO_INCREMENT;