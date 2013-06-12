<?php
/**
 * Layout.inc.php file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 *
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UILayout
{
    public function __construct()
    {
        $this->_pathToLayoutDir = ROOT_DIR . DIRECTORY_SEPARATOR . 'layout';

        if(isset($_GET['family']) && !empty($_GET['family'])) {
            $this->_layoutsPrecedence[] = $_GET['family'];
        }
        $this->_layoutsPrecedence[] = ADA_TEMPLATE_FAMILY;
        // $conf_base = basename(HTTP_ROOT_DIR));
    }

    private function createAvailableLayoutsList()
    {

        $handle = opendir($this->_pathToLayoutDir);
        while (false !== ($layout = readdir($handle))) {
            if ($this->isLayoutInstalled($layout)) {
                $this->_availableLayouts[$layout] = $layout;
            }
        }
        closedir($handle);
    }

    private function isLayoutInstalled($layout)
    {
        if ($layout !== '.' && $layout !== '..'
           && is_dir($this->_pathToLayoutDir . DIRECTORY_SEPARATOR . $layout)) {

            return true;
        }
        return false;
    }

    public function getAvailableLayouts()
    {
        $this->createAvailableLayoutsList();
        return $this->_availableLayouts;
    }

    private $_layoutsPrecedence = array();
    private $_availableLayouts = array();
    private $_pathToLayoutDir = '';
}