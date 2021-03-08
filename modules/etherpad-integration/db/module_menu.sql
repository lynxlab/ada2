SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET @moduledefine = 'MODULES_ETHERPAD';
SET @modulepath = '%MODULES_ETHERPAD_HTTP%';

/* get the studentTree */
SELECT @studentTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=0 AND `user_type`=3 AND `linked_tree_id` IS NULL;
/* get the studentSelfInstructionTree */
SELECT @studentSelfInstructionTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=1 AND `user_type`=3 AND `linked_tree_id` IS NULL;

/* get the tutorTree */
SELECT @tutorTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `user_type`=4 AND `self_instruction`=0 AND `linked_tree_id` IS NULL;
/* get the tutorSelfInstructionTree */
SELECT @tutorSelfInstructionTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=1 AND `user_type`=4 AND `linked_tree_id` IS NULL;

/* get 'agisci' item */
SELECT @agisciitem := `item_id` FROM  `menu_items` WHERE `label` LIKE '%agisci%';

/* DELETE ALL FROM MENU TREE */
DELETE FROM `menu_tree` WHERE `tree_id` IN (SELECT `tree_id` FROM `menu_page` WHERE `module` = 'modules/etherpad-integration' );
DELETE FROM `menu_tree` WHERE `item_id` IN (SELECT `item_id` FROM `menu_items` WHERE `href_prefix` LIKE @modulepath );
ALTER TABLE `menu_tree` auto_increment = 1;

/* DELETE ALL FROM MENU PAGE */
DELETE FROM `menu_page` WHERE `module` = 'modules/etherpad-integration';
ALTER TABLE `menu_page` auto_increment = 1;

/* DELETE ALL FROM MENU ITEMS */
DELETE FROM `menu_items` WHERE `href_prefix` LIKE @modulepath;
ALTER TABLE `menu_items` auto_increment = 1;

/* insert instance document item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Documento Condiviso di Classe', NULL, 'text file', NULL, NULL, @modulepath, 'index.php?id_node=all', NULL, NULL, 0, 0, 50, '{"func": ["\\\\Lynxlab\\\\ADA\\\\Module\\\\EtherpadIntegration\\\\Utils","enableSharedDocMenuItem"], "params": { "nodeId" : "all" }}');
SET @instanceDoc = LAST_INSERT_ID();

/* insert node docuemnt item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Documento Condiviso di Nodo', NULL, 'text file outline', NULL, NULL, @modulepath, 'index.php', 'id_node', NULL, 0, 0, 55, '{"func": ["\\\\Lynxlab\\\\ADA\\\\Module\\\\EtherpadIntegration\\\\Utils","enableSharedDocMenuItem"]}');
SET @nodeDoc = LAST_INSERT_ID();

INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@studentTree, @agisciitem, @instanceDoc, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@studentTree, @agisciitem, @nodeDoc, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@tutorTree, @agisciitem, @instanceDoc, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@tutorTree, @agisciitem, @nodeDoc, '');

INSERT INTO `menu_page` (`tree_id`, `module`, `script`, `user_type`, `self_instruction`, `isVertical`, `linked_tree_id`) VALUES (NULL, 'modules/etherpad-integration', 'default', 3, 0, 0, NULL);
SET @studentPageId = LAST_INSERT_ID();
INSERT INTO `menu_page` (`tree_id`, `module`, `script`, `user_type`, `self_instruction`, `isVertical`, `linked_tree_id`) VALUES (NULL, 'modules/etherpad-integration', 'default', 4, 0, 0, NULL);
SET @tutorPageId = LAST_INSERT_ID();

INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`)  VALUES
(@studentPageId, 0, 1, ''),
(@studentPageId, 0, 9, ''),
(@studentPageId, 0, 16, ''),
(@studentPageId, 0, 24, ''),
(@studentPageId, 0, 37, ''),
(@studentPageId, 16, 17, ''),
(@studentPageId, 16, 18, ''),
(@studentPageId, 16, 152, ''),
(@tutorPageId, 0, 1, ''),
(@tutorPageId, 0, 9, ''),
(@tutorPageId, 0, 16, ''),
(@tutorPageId, 0, 24, ''),
(@tutorPageId, 0, 37, ''),
(@tutorPageId, 16, 17, ''),
(@tutorPageId, 16, 18, ''),
(@tutorPageId, 16, 152, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
COMMIT;