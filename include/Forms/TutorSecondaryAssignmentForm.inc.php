<?php
/**
 * TutorAssignmentForm file
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
 * Description of TutorAssignmentForm
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class TutorSecondaryAssignmentForm extends FForm
{
    public function  __construct($tutorsAr = array(), $checkedTutors)
    {
       parent::__construct();
       $this->addCheckboxes(
               'id_tutors_new[]',
               translateFN("Seleziona i tutors dall'elenco"),
               $tutorsAr,
               $checkedTutors);
       $this->addHidden('id_tutors_old');
       $this->addHidden('id_course_instance');
       $this->addHidden('id_course');
    }
}
