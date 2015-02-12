ALTER TABLE `modello_corso` ADD  `tipo_servizio` tinyint(3) unsigned DEFAULT NULL;
ALTER TABLE `modello_corso` ADD  `duration_hours` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `istanza_corso` ADD  `duration_hours` int(10) unsigned NOT NULL DEFAULT '0';
