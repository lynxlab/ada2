ALTER TABLE `iscrizioni` ADD  `data_iscrizione` INT( 11 ) NOT NULL;
ALTER TABLE `iscrizioni` ADD `laststatusupdate` INT(11) NULL DEFAULT NULL AFTER `data_iscrizione`;
