ALTER TABLE  `utente` ADD  `cap` VARCHAR( 10 ) NOT NULL ,
ADD  `matricola` VARCHAR( 20 ) NOT NULL ,
ADD  `avatar` VARCHAR( 90 ) NOT NULL,
CHANGE  `eta`  `birthdate` INT( 12 ) NULL DEFAULT NULL ;