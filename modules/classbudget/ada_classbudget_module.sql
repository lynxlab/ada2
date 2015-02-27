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

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_cost_classroom`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_cost_classroom` (
  `cost_classroom_id` int(10) unsigned NOT NULL,
  `id_classroom` int(10) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL,
  `hourly_rate` decimal(6,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classbudget_cost_classroom`
--
ALTER TABLE `module_classbudget_cost_classroom`
 ADD PRIMARY KEY (`cost_classroom_id`), ADD UNIQUE KEY `id_classroom_instance` (`id_classroom`,`id_istanza_corso`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classbudget_cost_classroom`
--
ALTER TABLE `module_classbudget_cost_classroom`
MODIFY `cost_classroom_id` int(10) unsigned NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_cost_item`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_cost_item` (
  `cost_item_id` int(11) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `applied_to` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classbudget_cost_item`
--
ALTER TABLE `module_classbudget_cost_item`
 ADD PRIMARY KEY (`cost_item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classbudget_cost_item`
--
ALTER TABLE `module_classbudget_cost_item`
MODIFY `cost_item_id` int(11) unsigned NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Struttura della tabella `module_classbudget_cost_tutor`
--

CREATE TABLE IF NOT EXISTS `module_classbudget_cost_tutor` (
`cost_tutor_id` int(10) unsigned NOT NULL,
  `id_tutor` int(10) unsigned NOT NULL,
  `id_istanza_corso` int(10) unsigned NOT NULL,
  `hourly_rate` decimal(7,2) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `module_classbudget_cost_tutor`
--
ALTER TABLE `module_classbudget_cost_tutor`
 ADD PRIMARY KEY (`cost_tutor_id`), ADD UNIQUE KEY `id_classroom_instance` (`id_tutor`,`id_istanza_corso`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `module_classbudget_cost_tutor`
--
ALTER TABLE `module_classbudget_cost_tutor`
MODIFY `cost_tutor_id` int(10) unsigned NOT NULL AUTO_INCREMENT;