<?php
/**
 * Course file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 * Description of Course
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/*
 * @FIXME
 * La classe che viene estesa (Course_Old) non viene mai usata direttamente nel codice
 * Si potrebbe quindi fondere le due classi (Course e Course_Old) ed eliminare la classe
 * padre (Course_Old)
 */
class Course extends Course_Old
{
    var $publicCourse;
        
    public function __construct($courseId) {
        parent::__construct($courseId);
        $this->publicCourse = false;
        if ($this->id == PUBLIC_COURSE_ID_FOR_NEWS) {
            $this->publicCourse = true;
        }
    }
    public function getId() {
        return parent::getId();
    }
    public function getAuthorId() {
        return $this->id_autore;
    }
    public function getLayoutId() {
        return $this->id_layout;
    }
    public function getCode() {
        return $this->nome;
    }
    public function getTitle() {
        return $this->titolo;
    }
    public function getCreationDate() {
        return $this->d_create;
    }
    public function getPublicationDate() {
        return $this->d_publish;
    }
    public function getDescription() {
        return $this->descr;
    }
    public function getRootNodeId() {
        return $this->id_nodo_iniziale;
    }
    public function getTableOfContentsNodeId() {
        return $this->id_nodo_toc;
    }
    public function getMediaPath() {
        return $this->media_path;
    }
    public function getLanguageId() {
        return $this->id_lingua;
    }
    public function getStaticMode() {
        return $this->static_mode;
    }
    public function getTemplateFamily() {
        return $this->template_family;
    }
    public function getCredits() {
        return $this->crediti;
    }
    public function isFull() {
        return $this->full == true;
    }
    public function getIsPublic() {
        return $this->publicCourse;
    }
    public function getDurationHours() {
    	return $this->duration_hours;
    }
    public function getServiceLevel() {
    	return $this->service_level;
    }
}