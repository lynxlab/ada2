ADA FormMail Module
-----------------------

This module implements a **FormMail** feature for ADA.


## Installation Notes

To enable the module, please:
* Make sure that the `module_formmail_helptype` and `module_formmail_history` tables exist in the
providers' databases. These tables should have been created during the ADA installation process, but if they've been not, please run the provided `ada_formmail_module.sql` script on every providers' databases and add at least one row to each `module_formmail_helptype` table.
* Edit the ``recipient`` column in the provided sample row to match your real support email address.
* Copy or rename the module config/config_DEFAULT.inc.php to config/config.inc.php.

To enable the FormMail feature and help menu items for user types other than `AMA_TYPE_SWITCHER`:
* grab the SQL from the `ada_formmail_module-menu.sql` file and before executing, make sure to replace the **6** at row 13 (``SET @usertype := 6;``) with the number defined in the `/config/config_main.inc.php` file which corresponds to the desired user type.
