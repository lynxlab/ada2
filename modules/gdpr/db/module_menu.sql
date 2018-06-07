SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET @moduledefine = 'MODULES_GDPR';
SET @modulepath = '%MODULES_GDPR_HTTP%';

/* get the authorTree */
SELECT @authorTree := `tree_id` FROM `menu_page` WHERE `module` = 'services' AND `script` = 'author.php' AND `user_type`=1;
/* get the studentTree */
SELECT @studentTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=0 AND `user_type`=3;
/* get the studentSelfInstructionTree */
SELECT @studentSelfInstructionTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=1 AND `user_type`=3;
/* get the tutorTree */
SELECT @tutorTree := `tree_id` FROM `menu_page` WHERE `module` = 'tutor' AND `script` = 'default' AND `user_type`=4;
/* get the visitorTree (aka home page, not logged user) */
SELECT @visitorTree := `tree_id` FROM `menu_page` WHERE `module` = 'main' AND `script` = 'index.php' AND `user_type`=5;
/* get the switcherTree */
SELECT @switcherTree := `tree_id` FROM `menu_page` WHERE `module` = 'switcher' AND `script` = 'default' AND `user_type`=6;

/* DELETE ALL FROM MENU ITEMS */
DELETE FROM `menu_items` WHERE `enabledON` LIKE CONCAT("%", @moduledefine, "%") OR `href_prefix` LIKE @modulepath;
ALTER TABLE `menu_items` auto_increment = 1;

/* module tree */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Privacy', NULL, 'id basic', 'large', NULL, NULL, NULL, NULL, NULL, 1, 0, 5, CONCAT("%", @moduledefine, "%"));
SET @moduletree = LAST_INSERT_ID();
/* new request item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Nuova richiesta', NULL, 'file outline', NULL, NULL, @modulepath, NULL, NULL, NULL, 0, 0, 5, CONCAT("%", @moduledefine, "%"));
SET @newrequest = LAST_INSERT_ID();
/* my requests item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Le mie richieste', NULL, 'tasks', NULL, NULL, @modulepath, 'list.php', NULL, NULL, 0, 0, 10, CONCAT("%", @moduledefine, "%"));
SET @myrequests = LAST_INSERT_ID();
/* all requests item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Tutte le richieste', NULL, 'text file outline', NULL, NULL, @modulepath, 'list.php?showall=1', NULL, NULL, 0, 0, 15, '{"func": ["\\\\Lynxlab\\\\ADA\\\\Module\\\\GDPR\\\\GdprActions","canDo"],"params":{ "value1": {"func": ["\\\\Lynxlab\\\\ADA\\\\Module\\\\GDPR\\\\GdprActions","getConstantFromString"],"params": "ACCESS_ALL_REQUESTS"}}}');
SET @allrequests = LAST_INSERT_ID();
/* all policies item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Gestione politiche', NULL, 'legal', NULL, NULL, @modulepath, 'listPolicies.php', NULL, NULL, 0, 0, 15, '{"func": ["\\\\Lynxlab\\\\ADA\\\\Module\\\\GDPR\\\\GdprActions","canDo"],"params":{ "value1": {"func": ["\\\\Lynxlab\\\\ADA\\\\Module\\\\GDPR\\\\GdprActions","getConstantFromString"],"params": "LIST_POLICIES"}}}');
SET @allpolicies = LAST_INSERT_ID();
/* request lookup item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Cerca pratica', NULL, 'basic search', NULL, NULL, @modulepath, 'lookuprequest.php', NULL, NULL, 0, 0, 20, '%ALWAYS%');
SET @requestlookup = LAST_INSERT_ID();
/* accept policies item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Accettazione politiche', NULL, 'checkmark sign', NULL, NULL, @modulepath, 'acceptPolicies.php', NULL, NULL, 0, 0, 25, CONCAT("%", @moduledefine, "%"));
SET @acceptpolicies = LAST_INSERT_ID();

/* DELETE ALL FROM MENU TREE */
DELETE FROM `menu_tree` WHERE `item_id` IN (@moduletree) OR `parent_id` IN (@moduleTree);
ALTER TABLE `menu_tree` auto_increment = 1;

INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES
	(@authorTree, 0, @moduletree, ''),
		(@authorTree, @moduletree, @newrequest, ''),
		(@authorTree, @moduletree, @myrequests, ''),
		(@authorTree, @moduletree, @allrequests, ''),
		(@authorTree, @moduletree, @allpolicies, ''),
		(@authorTree, @moduletree, @acceptpolicies, ''),
	(@studentTree, 0, @moduletree, ''),
		(@studentTree, @moduletree, @newrequest, ''),
		(@studentTree, @moduletree, @myrequests, ''),
		(@studentTree, @moduletree, @acceptpolicies, ''),
	(@studentSelfInstructionTree, 0, @moduletree, ''),
		(@studentSelfInstructionTree, @moduletree, @newrequest, ''),
		(@studentSelfInstructionTree, @moduletree, @myrequests, ''),
		(@studentSelfInstructionTree, @moduletree, @acceptpolicies, ''),
	(@tutorTree, 0, @moduletree, ''),
		(@tutorTree, @moduletree, @newrequest, ''),
		(@tutorTree, @moduletree, @myrequests, ''),
		(@tutorTree, @moduletree, @allrequests, ''),
		(@tutorTree, @moduletree, @allpolicies, ''),
		(@tutorTree, @moduletree, @acceptpolicies, ''),
	(@visitorTree, 0, @moduletree, ''),
		(@visitorTree, @moduletree, @requestlookup, ''),
	(@switcherTree, 0, @moduletree, ''),
		(@switcherTree, @moduletree, @newrequest, ''),
		(@switcherTree, @moduletree, @myrequests, ''),
		(@switcherTree, @moduletree, @allrequests, ''),
		(@switcherTree, @moduletree, @allpolicies, ''),
		(@switcherTree, @moduletree, @acceptpolicies, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
