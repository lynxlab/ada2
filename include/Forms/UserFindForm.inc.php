<?php
/**
 * UserLoginForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
require_once 'lib/classes/FForm.inc.php';
/**
 * Description of UserFindForm
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class UserFindForm extends FForm
{
    public function  __construct() {
        parent::__construct();

        $this->addTextInput('username', translateFN('Username'))
             ->setRequired()
             ->setValidator(FormValidator::USERNAME_VALIDATOR);

        $this->addHidden('findByUsername');
        $this->addHidden('id_course_instance');
    }
}
