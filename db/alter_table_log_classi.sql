ALTER TABLE `log_classi`
    ADD `last_access` INT(11) NOT NULL DEFAULT '0' ,
    ADD `exercises_test` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
    ADD `score_test` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
    ADD `exercises_survey` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
    ADD `score_survey` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    ADD `subscription_status` tinyint(3) UNSIGNED NOT NULL DEFAULT '0';