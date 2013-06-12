<?php
/**
 * FileUploadForm.inc.php file
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
 * Description of FileUploadForm
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010-2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class FileUploadForm extends FForm
{
    public function  __construct() {
        parent::__construct();

        $this->setEncType('multipart/form-data');
        $this->setAccept('text/csv');
        $this->addFileInput(
                'uploaded_file',
                translateFN('Seleziona il file contenente gli studenti da iscrivere')
        );
        $this->addHidden('id_course');
        $this->addHidden('id_course_instance');
    }
}
