SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET @moduledefine = 'MODULES_STUDENTSGROUPS';
SET @modulepath = '%MODULES_STUDENTSGROUPS_HTTP%';

/* get the studentTree */
SELECT @studentTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'user.php' AND `user_type`=3;
/* get the studentSelfInstructionTree */
SELECT @studentSelfInstructionTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=1 AND `user_type`=3;
/* get the switcherTree */
SELECT @switcherTree := `tree_id` FROM `menu_page` WHERE `module` = 'switcher' AND `script` = 'default' AND `user_type`=6;
/* get 'agisci' item */
SELECT @agisciitem := `item_id` FROM  `menu_items` WHERE `label` LIKE '%agisci%';
/* get 'strumenti' item */
SELECT @strumentiitem := `item_id` FROM  `menu_items` WHERE `label` LIKE '%strumenti%';

/*
 * menu page for listgroups.php script
 * done by hard copying the id of the 'home_help_esc' abstract page
 */
SELECT @oldpageid := `tree_id` FROM `menu_page` WHERE `module`='modules/studentsgroups' AND `script`='listgroups.php' AND `user_type`=6;

/* DELETE ALL FROM MENU ITEMS */
DELETE FROM `menu_items` WHERE `enabledON` LIKE CONCAT("%", @moduledefine, "%") OR `href_prefix` LIKE @modulepath;
ALTER TABLE `menu_items` auto_increment = 1;

/* DELETE ALL FROM MENU PAGE */
DELETE FROM `menu_page` WHERE `module` = 'modules/studentsgroups';
DELETE FROM `menu_page` WHERE `tree_id`=@oldpageid;
ALTER TABLE `menu_page` auto_increment = 1;

/* DELETE ALL FROM MENU TREE */
DELETE FROM `menu_tree` WHERE `tree_id`=@oldpageid;
ALTER TABLE `menu_tree` auto_increment = 1;

/* insert list groups item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Gruppi di studenti', NULL, 'basic users', NULL, NULL, @modulepath, 'listgroups.php', NULL, NULL, 0, 0, 18, CONCAT("%", @moduledefine, "%"));
SET @listgroups = LAST_INSERT_ID();

INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@switcherTree, @agisciitem, @listgroups, '');

/* link home_help_esc_back abstract menu to the module menu page */
INSERT INTO `menu_page` (`tree_id`, `module`, `script`, `user_type`, `self_instruction`, `isVertical`, `linked_tree_id`) VALUES (NULL, 'modules/studentsgroups', 'default', 6, 0, 0, 124);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
