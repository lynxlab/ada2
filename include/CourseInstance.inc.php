<?php
/**
 * Course_instance file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
/**
 * Description of Course_instance
 *
 * @package   Default
 * @author    vito <vito@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/*
 * @FIXME
 * La classe che viene estesa (Course_instance_Old) non viene mai usata direttamente nel codice
 * Si potrebbe quindi fondere le due classi (Course_instance e Course_instance_Old) ed eliminare la classe
 * padre (Course_instance_Old)
 */

class Course_instance extends Course_instance_Old
{
    public function __construct($courseInstanceId) {
        parent::__construct($courseInstanceId);
    }
    public function getId() {
        return $this->id;
    }
    public function getCourseId() {
        return $this->id_corso;
    }
    public function getStartDate() {
        if($this->data_inizio > 0) {
            return ts2dFN($this->data_inizio);
        }
        return '';
    }
    public function getDuration() {
        return $this->durata;
    }
    public function getScheduledStartDate() {
        if($this->data_inizio_previsto > 0) {
            return ts2dFN($this->data_inizio_previsto);
        }
        return '';
    }
    public function getLayoutId() {
        return $this->id_layout;
    }
    public function getEndDate() {
        if($this->data_fine > 0) {
            return ts2dFN($this->data_fine);
        }
        return '';
    }
    public function getStatus() {
        return $this->status;
    }
    public function isFull() {
        return $this->full == true;
    }

    public function isStarted() {
        return $this->data_inizio > 0;
    }

    public function getSelfInstruction() {
        return $this->self_instruction;
    }

    public function getSelfRegistration() {
        return $this->self_registration;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDurationSubscription() {
        return $this->duration_subscription;
    }

    public function getStartLevelStudent() {
        return $this->start_level_student;
    }

    public function getOpenSubscription() {
        return $this->open_subscription;
    }

}