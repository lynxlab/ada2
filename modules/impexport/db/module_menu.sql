SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

SET @moduledefine = 'MODULES_IMPEXPORT';
SET @modulepath = '%MODULES_IMPEXPORT_HTTP%';

/* get the switcherTree */
SELECT @switcherTree := `tree_id` FROM `menu_page` WHERE `module` = 'switcher' AND `script` = 'default' AND `user_type`=6;
/* get the authorTree */
SELECT @authorTree := `tree_id` FROM `menu_page` WHERE `module` = 'services' AND `script` = 'author.php' AND `user_type`=1;
/* get the authorViewTree */
SELECT @authorViewTree := `tree_id` FROM `menu_page` WHERE `module` = 'browsing' AND `script` = 'view.php' AND `self_instruction`=0 AND `user_type`=1;
/* get 'agisci' item */
SELECT @agisciitem := `item_id` FROM  `menu_items` WHERE `label` LIKE '%agisci%';

/* DELETE ALL FROM MENU TREE */
DELETE FROM `menu_tree` WHERE `item_id` IN (SELECT `item_id` FROM  `menu_items` WHERE `href_prefix` = @modulepath);
ALTER TABLE `menu_tree` auto_increment = 1;

/* DELETE ALL FROM MENU ITEMS */
DELETE FROM `menu_items` WHERE `enabledON` LIKE CONCAT("%", @moduledefine, "%") OR `href_prefix` LIKE @modulepath;
ALTER TABLE `menu_items` auto_increment = 1;

/* DELETE ALL FROM MENU PAGE */
DELETE FROM `menu_page` WHERE `module` LIKE 'modules/impexport';
ALTER TABLE `menu_page` auto_increment = 1;

/* insert import course item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'importa corso', NULL, 'download disk', NULL, NULL, @modulepath, 'import.php', NULL, NULL, 0, 0, 45, CONCAT("%", @moduledefine, "%"));
SET @importitem = LAST_INSERT_ID();

/* insert export course item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'esporta corso', NULL, 'upload disk', NULL, NULL, @modulepath, 'export.php', NULL, NULL, 0, 0, 50, CONCAT("%", @moduledefine, "%"));
SET @exportitem = LAST_INSERT_ID();

/* insert repository item */
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'repository corsi', NULL, 'basic book', NULL, NULL, @modulepath, 'repository.php', NULL, NULL, 0, 0, 55, CONCAT("%", @moduledefine, "%"));
SET @repoitem = LAST_INSERT_ID();

/* insert repository item with params*/
INSERT INTO `menu_items` (`item_id`, `label`, `extraHTML`, `icon`, `icon_size`, `href_properties`, `href_prefix`, `href_path`, `href_paramlist`, `extraClass`, `groupRight`, `specialItem`, `order`, `enabledON`) VALUES
(NULL, 'Importa da repository', NULL, 'basic book', NULL, NULL, @modulepath, 'repository.php', 'id_course,id_node', NULL, 0, 0, 55, CONCAT("%", @moduledefine, "%"));
SET @repoparamsitem = LAST_INSERT_ID();

INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@switcherTree,  @agisciitem, @importitem, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@switcherTree,  @agisciitem, @exportitem, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@switcherTree,  @agisciitem, @repoitem, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@authorTree,    @agisciitem, @repoitem, '');
INSERT INTO `menu_tree` (`tree_id`, `parent_id`, `item_id`, `extraClass`) VALUES (@authorViewTree,@agisciitem, @repoparamsitem, '');

INSERT INTO `menu_page` (`tree_id`, `module`, `script`, `user_type`, `self_instruction`, `isVertical`, `linked_tree_id`) VALUES
(NULL, 'modules/impexport', 'default', '1', '0', '0', 124);
INSERT INTO `menu_page` (`tree_id`, `module`, `script`, `user_type`, `self_instruction`, `isVertical`, `linked_tree_id`) VALUES
(NULL, 'modules/impexport', 'default', '6', '0', '0', 124);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
COMMIT;
