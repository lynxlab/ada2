-- --------------------------------------------------------
--   SQL TO GENERATE TABLES FOR ADA MENU SYSTEM
--   PLS RUN THIS IS SCRIPT IN COMMON DB AND IN EACH PROVIDER DB
--   WHERE PROVIDER's OWN MENUS ARE NEEDED
-- --------------------------------------------------------
--
-- Struttura della tabella `menu_items`
--

CREATE TABLE IF NOT EXISTS `menu_items` (
`item_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extraHTML` text COLLATE utf8_unicode_ci,
  `icon` text COLLATE utf8_unicode_ci,
  `icon_size` text COLLATE utf8_unicode_ci,
  `href_properties` text COLLATE utf8_unicode_ci,
  `href_prefix` text COLLATE utf8_unicode_ci,
  `href_path` text COLLATE utf8_unicode_ci,
  `href_paramlist` text COLLATE utf8_unicode_ci,
  `extraClass` text COLLATE utf8_unicode_ci,
  `groupRight` int(1) NOT NULL DEFAULT '0',
  `specialItem` int(1) NOT NULL DEFAULT '0',
  `order` int(3) unsigned NOT NULL DEFAULT '0',
  `enabled` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `menu_page`
--

CREATE TABLE IF NOT EXISTS `menu_page` (
`tree_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the menu tree for the given module, script, user_type and self instruction',
  `module` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `script` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user_type` int(2) NOT NULL,
  `self_instruction` int(1) NOT NULL DEFAULT '0' COMMENT 'nonzero if course is in self instruction mode',
  `isVertical` int(1) NOT NULL DEFAULT '0' COMMENT 'nonzero if it''s a vertical menu',
  `linked_tree_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`tree_id`),
  UNIQUE KEY `module` (`module`,`script`,`user_type`,`self_instruction`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `menu_tree`
--

CREATE TABLE IF NOT EXISTS `menu_tree` (
  `tree_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `extraClass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tree_id`,`parent_id`,`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
