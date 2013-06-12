<?php
/**
 * InstancePaypalForm file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */
require_once 'lib/classes/FForm.inc.php';
/**
 * 
 */
class InstanceTransferForm extends FForm {
    public function  __construct() {
        parent::__construct();

        //$action = PAYPAL_ACTION;
        $action = HTTP_ROOT_DIR. "/browsing/student_course_instance_transfer.php";
        //?instance=$instanceId&student=$studentId&provider=$providerId&course=$courseId";
        $this->setAction($action);
        $submitValue = translateFN('Paga con bonifico');
        $this->setSubmitValue($submitValue);

        $this->addHidden('instance');
        $this->addHidden('student');
        $this->addHidden('provider');
        $this->addHidden('course');
        

    }
}