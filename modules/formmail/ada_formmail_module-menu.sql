--
-- RUN THIS SCRIPT WHEREVER YOU HAVE MENUS
--
-- Typically it's going to be on the common database only,
-- unless you have a per-provider menu structure. In that
-- case run the script once per every provider as well.

---
--- Replace the 6 below with the usertype number you
--- wish to enable the formmail module
---

SET @usertype := 6;
--
-- Create a menu page
--
INSERT INTO `menu_page` (`module`,`script`,`user_type`,`linked_tree_id`) 
SELECT
'modules/formmail',
'default',@usertype,
(SELECT `tree_id` FROM `menu_page` WHERE `module`='abstract' AND script='home_help_esc_back' LIMIT 1) as linked_tree;

--
-- update all switcher's help submenus
--
INSERT INTO `menu_tree` (tree_id, parent_id, item_id, extraClass)
SELECT `tree_id`,
(SELECT `item_id` FROM `menu_items` WHERE `label` = 'help' LIMIT 1) as parent,
(SELECT `item_id` FROM `menu_items` WHERE `enabledON` = '%MODULES_FORMMAIL%' LIMIT 1) as item,
'' as class FROM `menu_page` WHERE `user_type`=@usertype;
