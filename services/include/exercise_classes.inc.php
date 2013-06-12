<?php
include_once "$root_dir/include/HtmlLibrary/BaseHtmlLib.inc.php";

/**
 * @name ExerciseDAO
 * This is a Data Acces Object use to get ADA Exercises Object from database,
 * or to store an ADA Exercise Object in the database.
 *
 */
class ExerciseDAO {
    /**
     * @method getExercise
     * Used to retrieve an exercise from database.
     * It gets all the needed data to build an exercise and returns the exercise object.
     * @return ADA_Exercise object on success, AMA_PEAR_Error on failure.
     */

    function getExercise( $id_node, $id_answer=null ) {
        $dh = $GLOBALS['dh'];
        $exercise_nodes = $dh->get_exercise($id_node);
        if ( AMA_DataHandler::isError( $exercise_nodes ) ) {
            $errObj = new ADA_Error($exercise_nodes,'Error while loading exercise');
        }

        $nodes = array();
        $exercise_text;
        foreach( $exercise_nodes as $exercise ) {
            if ( $exercise['id_nodo'] == $id_node ) {
                $exercise_text = $exercise;
            }
            else {
                $nodes[$exercise['id_nodo']] = $exercise;
            }
        }

        $student_answer = null;

        if ( $id_answer != null ) {
            $student_answer = $dh->get_student_answer($id_answer);
            if ( AMA_DataHandler::isError( $student_answer ) ) {
                //return $student_answer;
                $errObj = new ADA_Error($student_answer, 'Error while loading student answer');
            }
        }
        return new ADA_Esercizio( $id_node, $exercise_text, $nodes, $student_answer );
    }

    /**
     * @method getNextExerciseId
     * Used to get the id for the next exercise, if this exercise requires the next in sequence to
     * or a random one to be shown.
     * @param object $exercise - ADA Exercise object
     * @param int $id_student  -
     * @return mixed - a string representing next exercise id in case it finds an exercise to show or null.
     */
    function getNextExerciseId ( $exercise, $id_student ) {
        $dh = $GLOBALS['dh'];

        $next_exercise_id = null;
        switch ( $exercise->getExerciseMode() ) {
            case ADA_SINGLE_EXERCISE_MODE:
            default:
                return $next_exercise_id;
                break;
            case ADA_SEQUENCE_EXERCISE_MODE:
                $exercises = $dh->get_other_exercises($exercise->getParentId(), $exercise->getOrder(), $id_student);
                if ( AMA_DataHandler::isError($exercises) ) {
                    return $exercises;
                }
                break;
            case ADA_RANDOM_EXERCISE_MODE:
            // get all of the exercises for parent_id node and shuffle them.
                $exercises = $dh->get_other_exercises($exercise->getParentId(), 0, $id_student);
                if ( AMA_DataHandler::isError($exercises) ) {
                    return $exercises;
                }
                shuffle($exercises);
                break;
        }

        foreach( $exercises as $ex ) {
            if (( $ex['ripetibile'] == null ) || ( $ex['ripetibile'] == 1 )) {
                $next_exercise_id = $ex['id_nodo'];
                return $next_exercise_id;
            }
        }
        // There aren't exercises for the user
        return $next_exercise_id; // null
    }

    /**
     * @method save
     * Used to save or update an exercise in the database.
     * @param object $exercise - the exercise object we want to save.
     * @return mixed - true or AMA_PEAR Error.
     */
    function save( $exercise ) {
        $dh = $GLOBALS['dh'];
        // AL MOMENTO NON  VIENE USATO PER CREARE L'ESERCIZIO

        switch( $exercise->saveOrUpdateEsercizio() ) {
            case 0:
            default:
                break;
            case 1:
            // save
                break;
            case 2:
            // update
                $nodes = array();
                $nodes = $exercise->getUpdatedDataIds();

                foreach($nodes as $updated_node => $operation_on_node) {
                    if ($updated_node == $exercise->getId()) {
                        $data = array();
                        $data['id']        = $exercise->getId();
                        $data['name']      = $exercise->getTitle();
                        $data['text']      = $exercise->getText();
                        $data['type']      = $exercise->getType();
                        $data['parent_id'] = $exercise->getParentId();
                        $data['order']     = $exercise->getOrder();

                        $result = $dh->_edit_node($data);
                        if (AMA_DataHandler::isError($result)) {
                            return FALSE;
                        }
                    }
                    else {
                        if ($operation_on_node == ADA_EXERCISE_MODIFIED_ITEM) {
                            $data['id']        = $updated_node;//$ex_data['id_nodo'];
                            $data['name']      = $exercise->getExerciseDataAnswerForItem($updated_node);//$ex_data['nome'];
                            $data['text']      = $exercise->getExerciseDataAuthorCommentForItem($updated_node);
                            $data['type']      = $exercise->getExerciseDataTypeForItem($updated_node);
                            $data['parent_id'] = $exercise->getId();
                            $data['order']     = $exercise->getExerciseDataOrderForItem($updated_node);
                            $data['correctness'] = $exercise->getExerciseDataCorrectnessForItem($updated_node);

                            $result = $dh->_edit_node($data);
                            if (AMA_DataHandler::isError($result)) {
                                return FALSE;
                            }
                        }
                        else if($operation_on_node == ADA_EXERCISE_DELETED_ITEM) {
                            $result = $dh->remove_node($updated_node);
                            if (AMA_DataHandler::isError($result)) {
                                return FALSE;
                            }
                        }
                    }
                }


//                $data = array();
//    			$data['id']        = $exercise->getId();
//    			$data['name']      = $exercise->getTitle();
//    			$data['text']      = $exercise->getText();
//    			$data['type']      = $exercise->getType();
//    			$data['parent_id'] = $exercise->getParentId();
//    			$data['order']     = $exercise->getOrder();
//
//    			$result = $dh->_edit_node($data);
//                if (AMA_DataHandler::isError($result)) {
//                  return FALSE;
//                }
//
//                $exercise_data = $exercise->getExerciseData();
//                foreach ($exercise_data as $ex_data) {
//                  $data['id']        = $ex_data['id_nodo'];
//    			  $data['name']      = $ex_data['nome'];
//    			  $data['text']      = $ex_data['testo'];
//    			  $data['type']      = $ex_data['tipo'];
//    			  $data['parent_id'] = $exercise->getId();
//    			  $data['order']     = $ex_data['ordine'];
//    			  $data['correctness'] = $ex_data['correttezza'];
//
//                  $result = $dh->_edit_node($data);
//                  if (AMA_DataHandler::isError($result)) {
//                    return FALSE;
//                  }
//                }

                break;

        }

        switch( $exercise->saveOrUpdateRisposta() ) {
            case 0:
            default:
                break;
            case 1:
            // save
                $result = $dh->add_ex_history($exercise->getStudentId(),
                        $exercise->getCourseInstanceId(),
                        $exercise->getId(),
                        $exercise->getStudentAnswer(),
                        "-",
                        $exercise->getRating(),
                        "-",
                        $ripetibile=0,
                        $exercise->getAttachment()
                );
                //print_r($result);
                if ( AMA_DataHandler::isError($result) ) {
                    return FALSE;
                }
                return TRUE;
                break;
            case 2:
            // update
                $data = array ( 'commento' => $exercise->getTutorComment(),
                        'da_ripetere' => $exercise->getRepeatable(),
                        'punteggio' => $exercise->getRating() );
                $result = $dh->set_ex_history( $exercise->getStudentAnswerId(), $data );
                if ( AMA_DataHandler::isError($result) ) {
                    return FALSE;
                }
                return TRUE;
                break;
        }
        return TRUE;
    }

    function delete($exercise_id) {
        $dh = $GLOBALS['dh'];

        $exercise = self::getExercise($exercise_id);
        $exercise_data = $exercise->getExerciseData();
        foreach($exercise_data as $node_id => $node_data) {
            $result = $dh->remove_node($node_id);
//            print_r($result);
        }

        $result = $dh->remove_node($exercise_id);

    }

    function canEditExercise($exercise_id) {
        $dh = $GLOBALS['dh'];

        $tokens = explode('_',$exercise_id);
        $course_id = $tokens[0];

        $result = $dh->course_instance_get_list(NULL,$course_id);
        if (AMA_DataHandler::isError($result)) {
            return FALSE;
        }
        /*
       * There aren't active course instances, the exercise can be edited.
        */
        if(is_array($result) && sizeof($result) == 0) {
            return TRUE;
        }

        /*
       * There is at least an active course instance.
       * This exercise can be edited only if no one has executed it.
        */
        foreach ($result as $course_instance_data) {
            $course_instance_id = $course_instance_data[0];
            $ex_history = $dh->find_exercise_history_for_course_instance($exercise_id, $course_instance_id);

            if (AMA_DataHandler::isError($ex_history)) {
                return FALSE;
            }

            if (is_array($ex_history) && sizeof($ex_history) > 0) {
                return FALSE;
            }
        }
        return TRUE;
    }

    function addAnswer($exercise, $answer_data=array()) {
        $dh = $GLOBALS['dh'];

        $tmpAr = array();
        $tmpAr = explode ('_', $exercise->getId());
        $id_course = $tmpAr[0];
        $last_node = get_max_idFN($id_course);

        $tmpAr = array();
        $tempAr = explode ('_', $last_node);
        $new_id =$tempAr[1] + 1;
        $new_node = $id_course . '_' . $new_id;

        $node_to_add = array(
                'id'             => $new_node,
                'parent_id'      => $exercise->getId(),
                'id_node_author' => $exercise->getAuthorId(),
                'level'          => $exercise->getExerciseLevel(),
                'order'          => $answer_data['position'],
                'version'        => 0,
                'creation_date'  => $ymdhms,
                'icon'           => '',
                'type'           => ADA_LEAF_TYPE,
                'pos_x0'       => 100,
                'pos_y0'       => 100,
                'pos_x1'       => 200,
                'pos_y1'       => 200,
                'name'	         => $answer_data['answer'],              // titolo
                'title'          => '', // keywords
                'text'           => $answer_data['comment'],
                'bg_color'       => '#FFFFFF',
                'color'			 => '',
                'correctness'    => $answer_data['correctness'],
                'copyright'      => ''
        );
        $result = $dh->add_node($node_to_add);
        if (AMA_DataHandler::isError($result)) {
            $errObj = new ADA_Error($result,'Error while adding a new answer');
        }
        return TRUE;
    }

    function getExerciseInfo($exerciseObj, $id_course_instance) {
        $dh = $GLOBALS['dh'];
        /*
       * qui inserire uno switch che in base al tipo di esercizio
       * richiama un metodo opportuno di dh
        */
        $result = $dh->get_ex_report($exerciseObj->getId(), $id_course_instance);
        return $result;
    }
}


/**
 * @name ADA_Esercizio
 * This has just getters and setters for its attributes.
 *
 */
class ADA_Esercizio {
    var $id;
    var $testo;
    var $dati;
    var $risposta;
    var $flag_ex;
    var $flag_risp;
    var $updated_data_ids;

    function __construct( $id_node, $testo, $dati, $student_answer=null ) {
        $this->id        = $id_node;
        $this->testo     = $testo;
        $this->dati      = $dati;
        if ($tudent_answer == null) {
            $this->risposta['ripetibile'] = true; 
        } else {
            $this->risposta  = $student_answer;
        }
        $this->flag_ex   = FALSE;
        $this->flag_risp = FALSE;
        $this->updated_data_ids = array();
    }

    /*
     * Getters
    */
    function getUpdatedDataIds() {
        return $this->updated_data_ids;
    }
    function getId() {
        return $this->id;
    }

    function getText() {
        return $this->testo['testo'];
    }

    function getExerciseData () {
        return $this->dati;
    }

    function getAuthorId() {
        return $this->testo['id_utente'];
    }

    function getTitle() {
        return $this->testo['nome'];
    }

    function getType() {
        return $this->testo['tipo'];
    }

    function getExerciseFamily() {
        $type = $this->testo['tipo'];
        return $type[0];
    }

    function getExerciseInteraction() {
        $type = $this->testo['tipo'];
        return isset($type[1]) ? $type[1] : 0;
    }

    function getExerciseMode() {
        $type = $this->testo['tipo'];
        return isset($type[2]) ? $type[2] : 0;
    }

    function getExerciseSimplification() {
        $type = $this->testo['tipo'];
        return isset($type[3]) ? $type[3] : 0;
    }

    function getExerciseBarrier() {
        $type = $this->testo['tipo'];
        return isset($type[4]) ? $type[4] : 0;
    }

    function getStudentAnswer() {
        return $this->risposta['risposta_libera'];
        //return $this->risposta[6];
    }

    function getAnswerText($id_answer=null) {
        if ( $id_answer == null ) {
            $id_answer = $this->getStudentAnswer();
        }
        return $this->dati[$id_answer]['nome'];
    }

    function getRating() {
        return $this->risposta['punteggio'];
        //return $this->risposta[8];
    }

    function getExecutionDate() {
        if(is_array($this->risposta)) {
            return ts2dFN($this->risposta['data_visita']);
        }
    }
    function getCorrectness( $id_node ) {
        return $this->dati[$id_node]['correttezza'];
    }

    function getAuthorComment( $id_node=NULL ) {
        /*
       * Se $id_node è NULL, sto considerando il testo della risposta data
       * dallo studente, che è in $this->dati['nome']
        */
        if ($id_node == NULL) {
            $student_answer = $this->getStudentAnswer();

            foreach($this->dati as $answer) {
                if ($answer['nome'] == $student_answer) {
                    return $answer['testo'];
                }
            }
        }
        return $this->dati[$id_node]['testo'];
    }

    function getExerciseLevel () {
        return $this->testo['livello'];
    }

    function getParentId() {
        return $this->testo['id_nodo_parent'];
    }

    function getOrder() {
        return $this->testo['ordine'];
    }

    function getStudentId() {
        return $this->risposta['id_utente_studente'];
        //return $this->risposta[1];
    }

    function getCourseInstanceId() {
        return $this->risposta['id_istanza_corso'];
        //return $this->risposta[3];
    }

    function getStudentAnswerId() {
        return $this->risposta['id_history_ex'];
    }

    function getRepeatable() {
        return $this->risposta['ripetibile'];
    }

    function getTutorComment() {
        return $this->risposta['commento'];
    }

    function getAttachment() {
        if (isset($this->risposta['allegato'])) {
            return $this->risposta['allegato'];
        }
        else {   // dovrebbe restituire null o false, ma il metodo $dh->add_ex_history
            //si aspetta una cosa del genere.
            return " ";
        }
    }
    /*
     * Setters
    */
    function setTitle($title) {
        $this->testo['nome'] = $title;
        if(!$this->flag_ex) {
            $this->flag_ex = TRUE;
        }
    }

    function setText($text) {
        $this->testo['testo'] = $text;
        if(!$this->flag_ex) {
            $this->flag_ex = TRUE;
        }
    }

    function setStudentAnswer( $answer ) {
        $this->risposta['risposta_libera'] = $answer;
        if (!$this->flag_risp) {
            $this->flag_risp=true;
        }
    }

    function setRating ( $rating ) {
        $this->risposta['punteggio'] = $rating;
        if (!$this->flag_risp) {
            $this->flag_risp=true;
        }
    }

    function setTutorComment( $comment ) {
        $this->risposta['commento'] = $comment;
        if (!$this->flag_risp) {
            $this->flag_risp=true;
        }
    }

    function setRepeatable ( $flag ) {
        $this->risposta['ripetibile'] = $flag;
        if (!$this->flag_risp) {
            $this->flag_risp=true;
        }
    }

    function setStudentId( $id ) {
        $this->risposta['id_utente_studente'] = $id;
    }

    function setCourseInstanceId( $id ) {
        $this->risposta['id_istanza_corso'] = $id;
    }

    function setAttachment( $file ) {
        $this->risposta['allegato'] = $file;
        if (!$this->flag_risp) {
            $this->flag_risp=true;
        }
    }

    function setExerciseDataAnswerForItem($id, $value) {
        if (!isset($this->dati[$id])) {
            return FALSE;
        }

        $this->dati[$id]['nome'] = $value;
        if (!$this->flag_ex) {
            $this->flag_ex = TRUE;
        }
        return TRUE;
    }

    function setExerciseDataAuthorCommentForItem($id, $value) {
        if (!isset($this->dati[$id])) {
            return FALSE;
        }

        $this->dati[$id]['testo'] = $value;
        if (!$this->flag_ex) {
            $this->flag_ex = TRUE;
        }
        return TRUE;
    }

    function setExerciseDataCorrectnessForItem($id, $value) {
        if (!isset($this->dati[$id])) {
            return FALSE;
        }

        $this->dati[$id]['correttezza'] = $value;
        if (!$this->flag_ex) {
            $this->flag_ex = TRUE;
        }
        return TRUE;
    }

    function getExerciseDataAnswerForItem($id) {
        if (isset($this->dati[$id])) {
            return $this->dati[$id]['nome'];
        }
    }

    function getExerciseDataAuthorCommentForItem($id) {
        if (isset($this->dati[$id])) {
            return $this->dati[$id]['testo'];
        }
    }

    function getExerciseDataCorrectnessForItem($id) {
        if (isset($this->dati[$id])) {
            return $this->dati[$id]['correttezza'];
        }
    }

    function getExerciseDataTypeForItem($id) {
        if (isset($this->dati[$id])) {
            return $this->dati[$id]['tipo'];
        }
    }

    function getExerciseDataOrderForItem($id) {
        if (isset($this->dati[$id])) {
            return $this->dati[$id]['ordine'];
        }
    }


    /**
     * @method saveOrUpdateEsercizio
     * Used to check if this exercise needs to be saved (in case it doesn't exists in db)
     * or it needs to get updated (in case it exists in db and some change has been made)
     * @return 1 if this exercise needs to be saved
     * @return 2 if this exercise needs to be updated
     * @return 0 otherwise
     */
    function saveOrUpdateEsercizio() {
        if ($this->testo['id_nodo'] == null ) return 1;
        else if($this->flag_ex) return 2;

        return 0;
    }

    /**
     * @method saveOrUpdateRisposta
     * Used to check if the content of $this->risposta needs to be saved or updated.
     *
     * @return 1 if it needs to be saved
     * @return 2 if it needs to be updated
     * @return 0 otherwise
     */
    function saveOrUpdateRisposta () {
        if ($this->flag_ex) {
            if ($this->risposta['id_nodo'] == NULL) {
                return 0;
            }
        }

        if ($this->risposta['id_nodo'] == NULL ) return 1;
        else if($this->flag_risp) return 2;

        return 0;
    }

    function deleteDataItem($id) {
        if (isset($this->dati[$id])) {
            $this->updated_data_ids[$id] = ADA_EXERCISE_DELETED_ITEM;
            unset($this->dati[$id]);
            if(!$this->flag_ex) {
                $this->flag_ex = TRUE;
            }
        }
    }

    function updateExercise($data=array()) {
        unset($data['edit_exercise']);

        if (isset($data['exercise_title']) && !empty($data['exercise_title'])) {
            $this->setTitle($data['exercise_title']);

            if (!isset($this->updated_data_ids[$this->id])) {
                $this->updated_data_ids[$this->id] = TRUE;
            }
            unset($data['exercise_title']);
        }

        if (isset($data['exercise_text']) && !empty($data['exercise_text'])) {
            $this->setText($data['exercise_text']);

            if (!isset($this->updated_data_ids[$this->id])) {
                $this->updated_data_ids[$this->id] = TRUE;
            }
            unset($data['exercise_text']);
        }

        foreach($data as $exercise_data_id => $value) {
            $data = explode('_', $exercise_data_id);
            $node_id = $data[0].'_'.$data[1];
            $key = $data[2];

            if (!isset($this->updated_data_ids[$node_id])) {
                $this->updated_data_ids[$node_id] = ADA_EXERCISE_MODIFIED_ITEM;
            }

//        print_r($this->dati[$node_id]);
//        echo '<br />';

            switch ($key) {
                case 'answer':
                //$field = 'nome';
                    $result = $this->setExerciseDataAnswerForItem($node_id, $value);
                    break;
                case 'comment':
                //$field = 'testo';
                    $result = $this->setExerciseDataAuthorCommentForItem($node_id, $value);
                    break;
                case 'correctness':
                    $result = $this->setExerciseDataCorrectnessForItem($node_id, $value);
                    break;
                default:
                    return FALSE;
            }
//        if (!isset($this->dati[$node_id])) {
//          //sto aggiungendo dei dati, da gestire
//        }
//        else {
//          $this->dati[$node_id][$field] = $value;
//          if (!$this->flag_risp) {
//            $this->flag_risp = TRUE;
//          }
//        }
        }
    }
}

/*
interface iExerciseViewer
{
     function getTutorForm();
     function getAuthorForm();
     function getStudentForm();
}

abstract class AbsExerciseViewer implements iExerciseViewer
{
     function getTutorForm();
     function getAuthorForm();
     function getStudentForm();
}
*/ //       print_r($node_exAr);

/**
 * @name ExerciseViewer
 * This class (and its subclasses) manages the html form generation for each one of ExerciseFamily
 * in ADA.
 */
class ExerciseViewer //extends AbsExerciseViewer
{
    /**
     * @method fill_field_with_data
     * It simply checks if array position $data[$field_name] and returns its content.
     * Otherwise it returns an empty string.
     *
     * @param string $field_name - a key for the associative array $data
     * @param array  $data       - an associative array
     * @return string
     */
    function fill_field_with_data( $field_name, $data = array() ) {
        //return ( isset($data[$field_name]) ) ? $data[$field_name] : "";
        $field_data = '';
        if (isset($data[$field_name])) {
            $field_data = $data[$field_name];
        }
        if ($field_data != '' && get_magic_quotes_gpc()) {
            return stripslashes($field_data);
        }
        return $field_data;
    }

    /**
     * @method shuffleList
     * It shuffles an associative array, preserving $key=>$value association.
     *
     * @param array $a - the original array
     * @return array $shuffled - the shuffled array
     */
    function shuffleList ( $a = array() ) {
        if (count($a) == 1) {
            return $a;
        }

		$shuffled = array();
		while(!empty($a)) {
			$key = array_rand($a, 1);
			$shuffled[$key] = $a[$key];
			unset($a[$key]);
		}
        
        return $shuffled;
    }

    function getAddAnswerForm($edit_form_base_action, $exercise, $field) {
        return NULL;
    }

    /**
     *
     * @param $userObj     - the user object
     * @param $exerciseObj - the exercise object
     * @param $action      - the action for the form
     * @return String      - the form
     */
    function getViewingForm($userObj, $exerciseObj, $id_course_instance, $action) {
        if($userObj->tipo == AMA_TYPE_TUTOR) {
            return $this->getExerciseReport($exerciseObj, $id_course_instance);
        }

        return $this->getStudentForm($action, $exerciseObj);
    }
}

/**
 * @name Standard_ExerciseViewer
 * This class contains all of the methods needed to display an ADA Standard Exercise based on the user
 * that is seeing this exercise.
 * An ADA Standard Exercise is a multiple choice exercise...
 */
class Standard_ExerciseViewer extends ExerciseViewer {
    function getStudentForm( $form_action, $exercise ) {
        $answers = $exercise->getExerciseData();

        $div = CDOMElement::create('div');
        /*
        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:')));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);
        */
        $div_text = CDOMElement::create('div','id:exercise_text');
        $div_text->addChild(new CText($exercise->getText()));
        $div->addChild($div_text);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);
        foreach( $answers as $answer ) {
            $div_choice = CDOMElement::create('div', 'class:possible_answer');
            //$label = CDOMElement::create('label', 'for:useranswer');
            //$label->addChild(new CText($answer['nome']));
            $radio = CDOMElement::create('radio',"id:useranswer,name:useranswer,value:{$answer['id_nodo']}");
            //$div_choice->addChild($label);
            $div_choice->addChild($radio);
            $possible_answer = CDOMElement::create('span');
            $possible_answer->addChild(new CText($answer['nome']));
            $div_choice->addChild($possible_answer);
            $form->addChild($div_choice);

        }

        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    private function _getExercise( $exercise ) {

        $div = CDOMElement::create('div');

        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:')));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);

        $div_date = CDOMElement::create('div','id:exercise_date');
        $div_date->addChild(new CText(translateFN('Data di svolgimento:').' '));
        $div_date->addChild(new CText($exercise->getExecutionDate()));
        $div->addChild($div_date);

        $div_question = CDOMElement::create('div','id:exercise_question');
        $div_question->addChild(new CText(translateFN('Domanda:').' '));
        $div_question->addChild(new CText($exercise->getText()));
        $div->addChild($div_question);

        $div_answer = CDOMElement::create('div','id:exercise_answer');
        $div_answer->addChild(new CText(translateFN('Risposta:').' '));
        $div_answer->addChild(new CText($exercise->getAnswerText()));
        $div->addChild($div_answer);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $div_rating->addChild(new CText(translateFN('Punteggio:').' '));
        $div_rating->addChild(new CText($exercise->getRating()));
        $div->addChild($div_rating);

        return $div;
    }

    function getExerciseHtml( $exercise ) {
        $div = $this->_getExercise($exercise);
        return $div->getHtml();
    }

    function getTutorForm( $form_action, $exercise ) {

        $div = $this->_getExercise($exercise);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_textarea = CDOMElement::create('div','id:tutor_comment');
        $div_textarea->addChild(CDOMElement::create('textarea','id:comment, name:comment'));
        $form->addChild($div_textarea);

        $div_checkbox1 = CDOMElement::create('div','id:exercise_repeatable');
        $label1 = CDOMElement::create('label','for:ripetibile');
        $label1->addChild(new CText(translateFN('Ripetibile:')));
        $div_checkbox1->addChild($label1);
        $div_checkbox1->addChild(CDOMElement::create('checkbox','id:ripetibile, name:ripetibile'));
        $form->addChild($div_checkbox1);

        $div_checkbox2 = CDOMElement::create('div','id:exercise_sendmessage');
        $label2 = CDOMElement::create('label','for:messaggio');
        $label2->addChild(new CText(translateFN('Invia messaggio:')));
        $div_checkbox2->addChild($label2);
        $div_checkbox2->addChild(CDOMElement::create('checkbox','id:messaggio, name:messaggio'));
        $form->addChild($div_checkbox2);

        $form->addChild(CDOMElement::create('hidden',"name:student_id,value:{$exercise->getStudentId()}"));
        $form->addChild(CDOMElement::create('hidden',"name:course_instance,value:{$exercise->getCourseInstanceId()}"));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Salva');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);

        $div->addChild($form);

        return $div->getHtml();
    }

    function getAuthorForm ( $form_action, $data = array() ) {
        $error_msg = "";
        if ( isset($data['empty_field']) && $data['empty_field'] == true ) {
            $error_msg   = translateFN("Attenzione: campo non compilato!").'<br />';
            $answer      = parent::fill_field_with_data('last_answer', $data);
            $comment     = parent::fill_field_with_data('last_comment', $data);
            $correctness = parent::fill_field_with_data('last_correctness', $data);
        }
        $question    = parent::fill_field_with_data('question', $data);

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_error_message = CDOMElement::create('div','class:error_msg');
        $div_error_message->addChild(new CText($error_msg));
        $form->addChild($div_error_message);

        $div_textarea1 = CDOMElement::create('div','id:exercise_question');
        $label1 = CDOMElement::create('label','for:question');
        $label1->addChild(new CText(translateFN('Frase completa:')));
        $div_textarea1->addChild($label1);
        $textarea1 = CDOMElement::create('textarea','id:question,name:question');
        $textarea1->addChild(new CText($question));
        $div_textarea1->addChild($textarea1);
        $form->addChild($div_textarea1);

        $div_textarea2 = CDOMElement::create('div','id:exercise_answer');
        $label2 = CDOMElement::create('label','for:answer');
        $label2->addChild(new CText(translateFN('Testo risposta')));
        $div_textarea2->addChild($label2);
        $textarea2 = CDOMElement::create('textarea','id:answer,name:answer');
        $textarea2->addChild(new CText($answer));
        $div_textarea2->addChild($textarea2);
        $form->addChild($div_textarea2);

        $div_textarea3 = CDOMElement::create('div','id:exercise_comment');
        $label3 = CDOMElement::create('label','for:comment');
        $label3->addChild(new CText(translateFN('Commento alla risposta')));
        $div_textarea3->addChild($label3);
        $textarea3 = CDOMElement::create('textarea','id:comment,name:comment');
        $textarea3->addChild(new CText($comment));
        $div_textarea3->addChild($textarea3);
        $form->addChild($div_textarea3);

        $div_correctness = CDOMElement::create('div','id:exercise_correctness');
        $label4 = CDOMElement::create('label','for:hide');
        $label4->addChild(new CText(translateFN('Correttezza:')));
        $div_correctness->addChild($label4);
        $div_correctness->addChild(CDOMElement::create('text',"id:correctness,name:correctness,value:$correctness"));
        $form->addChild($div_correctness);

        $div_stop = CDOMElement::create('div','id:exercise_ended');
        $div_text = CDOMElement::create('div');
        $div_text->addChild(new CText(translateFN('Finito?')));
        $div_stop->addChild($div_text);
        $label5 = CDOMElement::create('label','for:finito');
        $label5->addChild(new CText('Si'));
        $div_stop->addChild($label5);
        $radio1 = CDOMElement::create('radio','name:finito,value:1,checked:true');
        $div_stop->addChild($radio1);
        $label6 = CDOMElement::create('label','for:finito');
        $label6->addChild(new CText('No'));
        $div_stop->addChild($label6);
        $radio2 = CDOMElement::create('radio','name:finito,value:0,checked:false');

        $div_stop->addChild($radio2);
        $form->addChild($div_stop);

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();

    }

    function getEditForm($form_action, $exercise) {

        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');
        $form->setAttribute('action',"$form_action&save=1");
        /*
       * Exercise title
        */
        $exercise_title = CDOMElement::create('div','id:title');
        $label = CDOMElement::create('label','for:exercise_title');
        $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
        $title = CDOMElement::create('div','id:exercise_title');
        $title->addChild(new CText($exercise->getTitle()));
        $mod_title = CDOMElement::create('a', "href:$form_action&edit=title");
        $mod_title->addChild(new CText(translateFN('[Modifica]')));
        $exercise_title->addChild($label);
        $exercise_title->addChild($title);
        $exercise_title->addChild($mod_title);
        /*
       * Exercise question
        */
        $exercise_question = CDOMElement::create('div','id:text');
        $label = CDOMElement::create('label','for:exercise_text');
        $label->addChild(new CText(translateFN("Testo dell'esercizio")));
        $text = CDOMElement::create('div','id:exercise_text');
        $text->addChild(new CText($exercise->getText()));
        $mod_text = CDOMElement::create('a', "href:$form_action&edit=text");
        $mod_text->addChild(new CText(translateFN('[Modifica]')));
        $exercise_question->addChild($label);
        $exercise_question->addChild($text);
        $exercise_question->addChild($mod_text);
        /*
       * Exercise data
        */
        $exercise_data  = $exercise->getExerciseData();

        //$answers = CDOMElement::create('div','id:answers');
        $table = CDOMElement::create('table');
        $thead  = CDOMElement::create('thead');
        $col1  = CDOMElement::create('tr');
        $col1->addChild(new CText(translateFN('Possibile risposta')));
        $col2  = CDOMElement::create('tr');
        $col2->addChild(new CText(translateFN('Commento')));
        $col3  = CDOMElement::create('tr');
        $col3->addChild(new CText(translateFN('Correttezza')));
        $thead->addChild($col1);
        $thead->addChild($col2);
        $thead->addChild($col3);
        $table->addChild($thead);
        $answers = CDOMElement::create('tbody');
        foreach($exercise_data as $answer_id => $answer_data) {
            //$exercise_answer = CDOMElement::create('div');
            $exercise_answer = CDOMElement::create('tr');

            //$answer = CDOMElement::create('div');
            $answer = CDOMElement::create('td');

            //   $label1 = CDOMElement::create('label',"for:{$answer_id}_answer");
            //   $label1->addChild(new CText(translateFN("Possibile risposta")));
            $answer_text = CDOMElement::create('div', 'id:answer_text');
            $answer_text->addChild(new CText($answer_data['nome']));
            //    $answer->addChild($label1);
            $answer->addChild($answer_text);

            //$comment = CDOMElement::create('div');
            $comment = CDOMElement::create('td');
            //   $label2 = CDOMElement::create('label',"for:{$answer_id}_comment");
            //   $label2->addChild(new CText(translateFN("Commento")));
            //$textarea2 = CDOMElement::create('textarea',"id:{$answer_id}_comment, name:{$answer_id}_comment");
            //$textarea2->addChild(new CText($answer_data['testo']));
            $answer_comment = CDOMElement::create('div', 'id:answer_comment');
            $answer_comment->addChild(new CText($answer_data['testo']));
            //  $comment->addChild($label2);
            $comment->addChild($answer_comment);

            //$correctness = CDOMElement::create('div');
            $correctness = CDOMElement::create('td');

            //  $label3 = CDOMElement::create('label',"for:{$answer_id}_correctness");
            //  $label3->addChild(new CText(translateFN("Correttezza")));
            //$textarea3 = CDOMElement::create('textarea',"id:{$answer_id}_correctness, name:{$answer_id}_correctness");
            //$textarea3->addChild(new CText($answer_data['correttezza']));
            $answer_correctness = CDOMElement::create('div', 'id:answer_correctness');
            $answer_correctness->addChild(new CText($answer_data['correttezza']));
            //  $correctness->addChild($label3);
            $correctness->addChild($answer_correctness);

            $actions = CDOMElement::create('td');
            $modify = CDOMElement::create('a', "href:$form_action&edit={$answer_id}");
            $modify->addChild(new CText(translateFN('[Modifica]')));
            $delete = CDOMElement::create('a', "href:$form_action&delete={$answer_id}");
            $delete->addChild(new CText(translateFN('[Elimina]')));
            $actions->addChild($modify);
            $actions->addChild($delete);

            $exercise_answer->addChild($answer);
            $exercise_answer->addChild($comment);
            $exercise_answer->addChild($correctness);
            $exercise_answer->addChild($actions);

            $answers->addChild($exercise_answer);
        }
        $table->addChild($answers);

        $add_answer = CDOMElement::create('div');
        $link = CDOMElement::create('a',"href:$form_action&edit=0&add=1");
        $link->addChild(new CText(translateFN('Aggiungi risposta')));
        $add_answer->addChild($link);


        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Modifica esercizio'));
        $buttons->addChild($input_submit);

        $form->addChild($exercise_title);
        $form->addChild($exercise_question);
        $form->addChild($table);
        $form->addChild($add_answer);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }

    function getEditFieldForm($form_action, $exercise, $exercise_field=NULL) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');

        /*
       * Exercise title
        */
        if ($exercise_field == 'title') {
            $form->setAttribute('action',"$form_action&update=title");

            $exercise_title = CDOMElement::create('div','id:title');
            $label = CDOMElement::create('label','for:exercise_title');
            $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
            $input_title = CDOMElement::create('text','id:exercise_title, name:exercise_title');
            $input_title->setAttribute('value', $exercise->getTitle());
            $exercise_title->addChild($label);
            $exercise_title->addChild($input_title);
            $form->addChild($exercise_title);
        }
        /*
      * Exercise question
        */
        else if ($exercise_field == 'text') {
            $form->setAttribute('action',"$form_action&update=text");

            $exercise_question = CDOMElement::create('div','id:text');
            $label = CDOMElement::create('label','for:exercise_text');
            $label->addChild(new CText(translateFN("Testo dell'esercizio")));
            $exercise_text = CDOMElement::create('textarea','id:exercise_text, name:exercise_text');
            $exercise_text->addChild(new CText($exercise->getText()));
            $exercise_question->addChild($label);
            $exercise_question->addChild($exercise_text);
            $form->addChild($exercise_question);
        }
        else if(count($exercise_field) > 0) {
            $node = $exercise_field;

            $form->setAttribute('action',"$form_action&update=$node");

            $possible_answer = CDOMElement::create('div','id:possible_answer');
            $label = CDOMElement::create('label', "for:{$node}_answer");
            $label->addChild(new CText(translateFN('Risposta possibile: ')));
            $answer = CDOMElement::create('text',"id:{$node}_answer,name:{$node}_answer");
            $answer->setAttribute('value',$exercise->getExerciseDataAnswerForItem($node));
            $possible_answer->addChild($label);
            $possible_answer->addChild($answer);
            $form->addChild($possible_answer);

            $answer_rating = CDOMElement::create('div','id:answer_rating');
            $label = CDOMElement::create('label', "for:{$node}_correctness");
            $label->addChild(new CText(translateFN('Punteggio associato: ')));
            $rating = CDOMElement::create('text',"id:{$node}_correctness,name:{$node}_correctness");
            $rating->setAttribute('value', $exercise->getExerciseDataCorrectnessForItem($node));
            $answer_rating->addChild($label);
            $answer_rating->addChild($rating);
            $form->addChild($answer_rating);

            $author_comment = CDOMElement::create('div','id:author_comment');
            $label = CDOMElement::create('label', "for:{$node}_comment");
            $label->addChild(new CText(translateFN('Commento associato: ')));
            $comment = CDOMElement::create('text',"id:{$node}_comment,name:{$node}_comment");
            $comment->setAttribute('value', $exercise->getExerciseDataAuthorCommentForItem($node));
            $author_comment->addChild($label);
            $author_comment->addChild($comment);
            $form->addChild($author_comment);


//        $data = explode('_', $exercise_field);
//        $node = $data[0].'_'.$data[1];
//        $field = $data[2];
//        //echo $node .'<br />' .$field;
//
//        $textarea = CDOMElement::create('textarea',"id:{$node}_{$field}, name:{$node}_{$field}");
//
//        switch($field) {
//          case 'answer':
//            $value = $exercise->getExerciseDataAnswerForItem($node);
//            break;
//          case 'comment':
//            $value = $exercise->getExerciseDataAuthorCommentForItem($node);
//            break;
//          case 'correctness':
//            $value = $exercise->getExerciseDataCorrectnessForItem($node);
//            break;
//          default:
//            $value = '';
//        }
//        $textarea->addChild(new CText($value));
//        $form->addChild($textarea);
        }
        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;

    }

    function getAddAnswerForm($form_action, $exercise, $field) {
        $add_answer = CDOMElement::create('div');
        $form = CDOMElement::create('form', 'id:added_answer, name:added_answer, method:post');
        $form->setAttribute('action',"$form_action&add_answer_to={$exercise->getId()}");

        $new_answer = CDOMElement::create('div', 'class:possible_answer');

        $answer      = CDOMElement::create('text','id:answer, name:answer');
        $answer->setAttribute('value', translateFN('Testo della risposta'));
        $new_answer->addChild($answer);

        $comment     = CDOMElement::create('text','id:comment, name:comment');
        $comment->setAttribute('value',translateFN('Commento alla risposta'));
        $new_answer->addChild($comment);

        $correctness = CDOMElement::create('text','id:correctness, name:correctness');
        $correctness->setAttribute('value',translateFN('Correttezza della risposta'));
        $new_answer->addChild($correctness);

        $position = CDOMElement::create('hidden', 'id:position, name:position');
        $position->setAttribute('value',$field);
        $new_answer->addChild($position);

        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:add_answer, name:add_answer');
        $input_submit->setAttribute('value',translateFN('Aggiungi questa risposta'));
        $buttons->addChild($input_submit);

        $form->addChild($new_answer);
        $form->addChild($buttons);

        $add_answer->addChild($form);
        return $add_answer;
    }

    function getExerciseReport($exerciseObj, $id_course_instance) {
        /*
       * ottiene i dati relativi alla risposte fornite dagli utenti nella classe
       * e alle risposte possibile ammesse dall'esercizio
        */
        $exercise_data    = ExerciseDAO::getExerciseInfo($exerciseObj, $id_course_instance);
        $possible_answers = $exerciseObj->getExerciseData();

        $div = CDOMElement::create('div');
        $div->addChild(new CText($exerciseObj->getText()));
        $data = array();

        $exercise_data_count = count($exercise_data);
        $thead_data = array(
                translateFN('Testo della risposta'),
                translateFN('Punteggio'),
                translateFN('Numero di risposte')
        );

        /*
       * scorre le risposte fornite dalla classe
        */
        for($i = 0; $i < $exercise_data_count; $i++) {
            $href = 'view.php?id_node='.$exercise_data[$i]['risposta_libera'];
            $answer = CDOMElement::create('a', "href:$href");

            $answer_id = $exercise_data[$i]['risposta_libera'];
            $answer->addChild(new CText($exerciseObj->getExerciseDataAnswerForItem($answer_id)));

            $tbody_data[$i] = array(
                    $answer->getHtml(),
                    $exercise_data[$i]['punteggio'],
                    $exercise_data[$i]['risposte']
            );

            if(isset($possible_answers[$answer_id])) {
                unset($possible_answers[$answer_id]);
            }
        }

        /*
       * considera eventuali risposte all'esercizio che non sono state date
       * da nessuno studente
        */
        foreach($possible_answers as $answer_id => $answer_data) {
            $href = 'view.php?id_node='.$answer_id;
            $answer = CDOMElement::create('a', "href:$href");
            $answer->addChild(new CText($exerciseObj->getExerciseDataAnswerForItem($answer_id)));

            $tbody_data[$i] = array(
                    $answer->getHtml(),
                    $exerciseObj->getExerciseDataCorrectnessForItem($answer_id),
                    0
            );
            $i++;
        }
        $div->addChild(BaseHtmlLib::tableElement('', $thead_data, $tbody_data));
        return $div->getHtml();
    }

    function checkAuthorInput ( $post_data = array(), &$data=array() ) {
        $empty_field = false;
        $i = (isset($data['answers'])) ? count($data['answers']) + 1 : 1;
        if (!isset($data['last_index'])) $data['last_index'] = 1;
        $last_index = $data['last_index'];

        if ( !isset($data['empty_field']) ) {   // DOMANDA
            if ( isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else {
                $empty_field = true;
            }
            // RISPOSTA
            if ( isset($post_data['answer']) && $post_data['answer'] !== "" ) {
                $data['answers'][$i]['answer'] = $post_data['answer'];
                $data['last_answer'] = $post_data['answer'];
            }
            else {
                $empty_field = true;
            }
            // CORRETTEZZA
            if ( isset($post_data['correctness']) && $post_data['correctness'] !== "" ) {
                $data['answers'][$i]['correctness'] = $post_data['correctness'];
                $data['last_correctness'] = $post_data['correctness'];
            }
            else {
                $empty_field = true;
            }
            // COMMENTO
            if ( isset($post_data['comment']) && $post_data['comment'] !== "" ) {
                $data['answers'][$i]['comment'] = $post_data['comment'];
                $data['last_comment'] = $post_data['comment'];
            }

        }
        else if ( isset($data['empty_field']) && $data['empty_field'] ) {
            // DOMANDA
            if ( !isset($data['question']) && isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else if ( !isset($post_data['question']) || $post_data['question'] == "") {
                $empty_field = true;
            }
            // RISPOSTA
            if ( !isset($data['answers'][$last_index]['answer']) && isset($post_data['answer']) && $post_data['answer'] !== ""  ) {
                $data['answers'][$last_index]['answer'] = $post_data['answer'];
                $data['last_answer'] = $post_data['answer'];
            }
            else if ( !isset($post_data['answer']) || $post_data['answer'] == "") {
                $empty_field = true;
            }
            // CORRETTEZZA
            if ( !isset($data['answers'][$last_index]['correctness']) && isset($post_data['correctness']) && $post_data['correctness'] !== ""  ) {
                $data['answers'][$last_index]['correctness'] = $post_data['correctness'];
                $data['last_correctness'] = $post_data['correctness'];
            }
            else if ( !isset($post_data['correctness']) || $post_data['correctness'] == "") {
                $empty_field = true;
            }
            // COMMENTO
            if ( !isset($data['answers'][$last_index]['comment']) && isset($post_data['comment']) && $post_data['comment'] !== "" ) {
                $data['answers'][$last_index]['comment'] = $post_data['comment'];
                $data['last_comment'] = $post_data['comment'];
            }
        }

        if ( $empty_field ) {
            $data['empty_field'] = true;
        }
        else {
            unset ( $data['empty_field']);
            $data['last_index']++;
        }

        return !$empty_field;
    }
}

/**
 * @name OpenManual_ExerciseViewer
 * This class contains all of the methods needed to display an ADA OpenManual Exercise based on the user
 * that is seeing this exercise.
 * An ADA OpenManual Exercise is ...
 */
class OpenManual_ExerciseViewer extends ExerciseViewer {
    function getStudentForm( $form_action, $exercise ) {

        $div = CDOMElement::create('div');
        $div_text = CDOMElement::create('div','id:exercise_text');
        $div_text->addChild(new CText($exercise->getText()));
        $div->addChild($div_text);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_textarea = CDOMElement::create('div','id:answer');
        $label = CDOMElement::create('label','for:useranswer');
        $label->addChild(new CText(translateFN('Scrivi la tua risposta')));
        $div_textarea->addChild($label);
        $div_textarea->addChild(CDOMElement::create('textarea','id:useranswer, name:useranswer'));
        $form->addChild($div_textarea);

        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();

    }

    private function _getExercise( $exercise ) {

        $div = CDOMElement::create('div');

        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:').' '));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);

        $div_date = CDOMElement::create('div','id:exercise_date');
        $div_date->addChild(new CText(translateFN('Data di svolgimento:').' '));
        $div_date->addChild(new CText($exercise->getExecutionDate()));
        $div->addChild($div_date);

        $div_question = CDOMElement::create('div','id:exercise_question');
        $div_question->addChild(new CText(translateFN('Domanda:').' '));
        $div_question->addChild(new CText($exercise->getText()));
        $div->addChild($div_question);

        $div_answer = CDOMElement::create('div','id:exercise_answer');
        $div_answer->addChild(new CText(translateFN('Risposta:').' '));
        $div_answer->addChild(new CText($exercise->getStudentAnswer()));
        $div->addChild($div_answer);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $div_rating->addChild(new CText(translateFN('Punteggio:').' '));
        $div_rating->addChild(new CText($exercise->getRating()));
        $div->addChild($div_rating);

        return $div;
    }

    function getExerciseHtml( $exercise ) {
        $div = $this->_getExercise($exercise);
        return $div->getHtml();
    }

    function getTutorForm( $form_action, $exercise ) {
        $div = $this->_getExercise($exercise);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $label = CDOMElement::create('label','for:punteggio');
        $label->addChild(new CText('Punteggio:').' ');
        $div_rating->addChild($label);
        $div_rating->addChild(CDOMElement::create('text',"id:punteggio,name:punteggio,value:{$exercise->getRating()}"));
        $form->addChild($div_rating);

        $div_textarea = CDOMElement::create('div','id:tutor_comment');
        $div_textarea->addChild(CDOMElement::create('textarea','id:comment, name:comment'));
        $form->addChild($div_textarea);

        $div_checkbox1 = CDOMElement::create('div','id:exercise_repeatable');
        $label1 = CDOMElement::create('label','for:ripetibile');
        $label1->addChild(new CText(translateFN('Ripetibile:').' '));
        $div_checkbox1->addChild($label1);
        $div_checkbox1->addChild(CDOMElement::create('checkbox','id:ripetibile, name:ripetibile'));
        $form->addChild($div_checkbox1);

        $div_checkbox2 = CDOMElement::create('div','id:exercise_sendmessage');
        $label2 = CDOMElement::create('label','for:messaggio');
        $label2->addChild(new CText(translateFN('Invia messaggio:').' '));
        $div_checkbox2->addChild($label2);
        $div_checkbox2->addChild(CDOMElement::create('checkbox','id:messaggio, name:messaggio'));
        $form->addChild($div_checkbox2);

        $form->addChild(CDOMElement::create('hidden',"name:student_id,value:{$exercise->getStudentId()}"));
        $form->addChild(CDOMElement::create('hidden',"name:course_instance,value:{$exercise->getCourseInstanceId()}"));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Salva');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    function getAuthorForm ( $form_action, $data = array() ) {
        $error_msg = "";
        if ( isset($data['empty_field']) && $data['empty_field'] == true ) {
            $error_msg = translateFN("Attenzione: campo non compilato!").'<br />';
        }

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_error_message = CDOMElement::create('div','class:error_msg');
        $div_error_message->addChild(new CText($error_msg));
        $form->addChild($div_error_message);

        $div_textarea1 = CDOMElement::create('div','id:exercise_question');
        $label1 = CDOMElement::create('label','for:question');
        $label1->addChild(new CText(translateFN('Testo domanda')));
        $div_textarea1->addChild($label1);
        $textarea1 = CDOMElement::create('textarea','id:question,name:question');
        $textarea1->addChild(new CText($question));
        $div_textarea1->addChild($textarea1);
        $form->addChild($div_textarea1);

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    function getEditForm($form_action, $exercise) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');
        $form->setAttribute('action',"$form_action&save=1");      /*
       * Exercise title
        */
        $exercise_title = CDOMElement::create('div','id:title');
        $label = CDOMElement::create('label','for:exercise_title');
        $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
        $input_title = CDOMElement::create('div','id:exercise_title');
        $input_title->addChild(new CText($exercise->getTitle()));
        $mod_title = CDOMElement::create('a',"href:$form_action&edit=title");
        $mod_title->addChild(new CText(translateFN("Modifica")));
        $exercise_title->addChild($label);
        $exercise_title->addChild($input_title);
        $exercise_title->addChild($mod_title);

        /*
       * Exercise question
        */
        $exercise_question = CDOMElement::create('div','id:text');
        $label = CDOMElement::create('label','for:exercise_text');
        $label->addChild(new CText(translateFN("Testo dell'esercizio")));
        $exercise_text = CDOMElement::create('div','id:exercise_text');
        $exercise_text->addChild(new CText($exercise->getText()));
        $mod_text = CDOMElement::create('a',"href:$form_action&edit=text");
        $mod_text->addChild(new CText(translateFN("Modifica")));
        $exercise_question->addChild($label);
        $exercise_question->addChild($exercise_text);
        $exercise_question->addChild($mod_text);

        /*
       * Form buttons
        */
        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);

        $form->addChild($exercise_title);
        $form->addChild($exercise_question);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);

        return $edit_exercise;
    }

    function getEditFieldForm($form_action, $exercise, $exercise_field=NULL) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');

        /*
       * Exercise title
        */
        if ($exercise_field == 'title') {
            $form->setAttribute('action',"$form_action&update=title");

            $exercise_title = CDOMElement::create('div','id:title');
            $label = CDOMElement::create('label','for:exercise_title');
            $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
            $input_title = CDOMElement::create('text','id:exercise_title, name:exercise_title');
            $input_title->setAttribute('value', $exercise->getTitle());
            $exercise_title->addChild($label);
            $exercise_title->addChild($input_title);
            $form->addChild($exercise_title);
        }
        /*
      * Exercise question
        */
        else if ($exercise_field == 'text') {
            $form->setAttribute('action',"$form_action&update=text");

            $exercise_question = CDOMElement::create('div','id:text');
            $label = CDOMElement::create('label','for:exercise_text');
            $label->addChild(new CText(translateFN("Testo dell'esercizio")));
            $exercise_text = CDOMElement::create('textarea','id:exercise_text, name:exercise_text');
            $exercise_text->addChild(new CText($exercise->getText()));
            $exercise_question->addChild($label);
            $exercise_question->addChild($exercise_text);
            $form->addChild($exercise_question);
        }

        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }


    function getExerciseReport($exerciseObj, $id_course_instance) {
        return $this->getStudentForm('exercise.php', $exerciseObj);
    }

    function checkAuthorInput ( $post_data = array(), &$data = array() ) {
        $empty_field = false;
        $i = (isset($data['answers'])) ? count($data['answers']) + 1 : 1;
        if (!isset($data['last_index'])) $data['last_index'] = 1;
        $last_index = $data['last_index'];

        if ( !isset($data['empty_field']) ) {   // DOMANDA
            if ( isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else {
                $empty_field = true;
            }
        }
        else if ( isset($data['empty_field']) && $data['empty_field'] ) {
            // DOMANDA
            if ( !isset($data['question']) && isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else if ( !isset($post_data['question']) || $post_data['question'] == "") {
                $empty_field = true;
            }
        }

        if ( $empty_field ) {
            $data['empty_field'] = true;
        }
        else {
            unset ( $data['empty_field']);
            $data['last_index']++;
        }

        return !$empty_field;
    }

}

/**
 * @name OpenAutomatic_ExerciseViewer
 * This class contains all of the methods needed to display an ADA OpenAutomatic Exercise based on the user
 * that is seeing this exercise.
 * An ADA OpenAutomatic Exercise is ...
 */
class OpenAutomatic_ExerciseViewer extends ExerciseViewer {
    function getStudentForm( $form_action, $exercise ) {
        $div = CDOMElement::create('div');
        /*
        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:')));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);
        */
        $div_text = CDOMElement::create('div','id:exercise_text');
        $div_text->addChild(new CText($exercise->getText()));
        $div->addChild($div_text);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_textarea = CDOMElement::create('div','id:answer');
        $label = CDOMElement::create('label','for:useranswer');
        $label->addChild(new CText(translateFN('Scrivi la tua risposta')));
        $div_textarea->addChild($label);
        $div_textarea->addChild(CDOMElement::create('textarea','id:useranswer, name:useranswer'));
        $form->addChild($div_textarea);

        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    private function _getExercise( $exercise ) {
        $div = CDOMElement::create('div');

        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:').' '));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);

        $div_date = CDOMElement::create('div','id:exercise_date');
        $div_date->addChild(new CText(translateFN('Data di svolgimento:').' '));
        $div_date->addChild(new CText($exercise->getExecutionDate()));
        $div->addChild($div_date);

        $div_question = CDOMElement::create('div','id:exercise_question');
        $div_question->addChild(new CText(translateFN('Domanda:').' '));
        $div_question->addChild(new CText($exercise->getText()));
        $div->addChild($div_question);

        $div_answer = CDOMElement::create('div','id:exercise_answer');
        $div_answer->addChild(new CText(translateFN('Risposta:').' '));
        $div_answer->addChild(new CText($exercise->getStudentAnswer()));
        $div->addChild($div_answer);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $div_rating->addChild(new CText(translateFN('Punteggio:').' '));
        $div_rating->addChild(new CText($exercise->getRating()));
        $div->addChild($div_rating);

        return $div;
    }

    function getExerciseHtml( $exercise ) {
        $div = $this->_getExercise($exercise);
        return $div->getHtml();
    }

    function getTutorForm( $form_action, $exercise ) {
        $div = $this->_getExercise( $exercise );

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_textarea = CDOMElement::create('div','id:tutor_comment');
        $div_textarea->addChild(CDOMElement::create('textarea','id:comment, name:comment'));
        $form->addChild($div_textarea);

        $div_checkbox1 = CDOMElement::create('div','id:exercise_repeatable');
        $label1 = CDOMElement::create('label','for:ripetibile');
        $label1->addChild(new CText(translateFN('Ripetibile:')));
        $div_checkbox1->addChild($label1);
        $div_checkbox1->addChild(CDOMElement::create('checkbox','id:ripetibile, name:ripetibile'));
        $form->addChild($div_checkbox1);

        $div_checkbox2 = CDOMElement::create('div','id:exercise_sendmessage');
        $label2 = CDOMElement::create('label','for:messaggio');
        $label2->addChild(new CText(translateFN('Invia messaggio:')));
        $div_checkbox2->addChild($label2);
        $div_checkbox2->addChild(CDOMElement::create('checkbox','id:messaggio, name:messaggio'));
        $form->addChild($div_checkbox2);

        $form->addChild(CDOMElement::create('hidden',"name:student_id,value:{$exercise->getStudentId()}"));
        $form->addChild(CDOMElement::create('hidden',"name:course_instance,value:{$exercise->getCourseInstanceId()}"));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Salva');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    function getAuthorForm ( $form_action, $data = array() ) {
        $error_msg = "";
        if ( isset($data['empty_field']) && $data['empty_field'] == true ) {
            $error_msg = translateFN("Attenzione: campo non compilato!");
            $question  = parent::fill_field_with_data('question', $data);
            $answer    = parent::fill_field_with_data('last_answer', $data);
            $comment   = parent::fill_field_with_data('last_comment', $data);
        }

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_error_message = CDOMElement::create('div','class:error_msg');
        $div_error_message->addChild(new CText($error_msg));
        $form->addChild($div_error_message);

        $div_textarea1 = CDOMElement::create('div','id:exercise_question');
        $label1 = CDOMElement::create('label','for:question');
        $label1->addChild(new CText(translateFN('Testo domanda')));
        $div_textarea1->addChild($label1);
        $textarea1 = CDOMElement::create('textarea','id:question,name:question');
        $textarea1->addChild(new CText($question));
        $div_textarea1->addChild($textarea1);
        $form->addChild($div_textarea1);

        $div_textarea2 = CDOMElement::create('div','id:exercise_answer');
        $label2 = CDOMElement::create('label','for:answer');
        $label2->addChild(new CText(translateFN('Testo risposta corretta:')));
        $div_textarea2->addChild($label2);
        $textarea2 = CDOMElement::create('textarea','id:answer,name:answer');
        $textarea2->addChild(new CText($answer));
        $div_textarea2->addChild($textarea2);
        $form->addChild($div_textarea2);

        $div_textarea3 = CDOMElement::create('div','id:exercise_comment');
        $label3 = CDOMElement::create('label','for:comment');
        $label3->addChild(new CText(translateFN('Commento alla risposta')));
        $div_textarea3->addChild($label3);
        $textarea3 = CDOMElement::create('textarea','id:comment,name:comment');
        $textarea3->addChild(new CText($comment));
        $div_textarea3->addChild($textarea3);
        $form->addChild($div_textarea3);

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    function getEditForm($form_action, $exercise) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');
        $form->setAttribute('action',"$form_action&save=1");
        /*
       * Exercise title
        */
        $exercise_title = CDOMElement::create('div','id:title');
        $label = CDOMElement::create('label','for:exercise_title');
        $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
        $input_title = CDOMElement::create('div','id:exercise_title');
        $input_title->addChild(new CText($exercise->getTitle()));
        $mod_title = CDOMElement::create('a',"href:$form_action&edit=title");
        $mod_title->addChild(new CText(translateFN("Modifica")));
        $exercise_title->addChild($label);
        $exercise_title->addChild($input_title);
        $exercise_title->addChild($mod_title);

        /*
       * Exercise question
        */
        $exercise_question = CDOMElement::create('div','id:text');
        $label = CDOMElement::create('label','for:exercise_text');
        $label->addChild(new CText(translateFN("Testo dell'esercizio")));
        $exercise_text = CDOMElement::create('div','id:exercise_text');
        $exercise_text->addChild(new CText($exercise->getText()));
        $mod_text = CDOMElement::create('a',"href:$form_action&edit=text");
        $mod_text->addChild(new CText(translateFN("Modifica")));
        $exercise_question->addChild($label);
        $exercise_question->addChild($exercise_text);
        $exercise_question->addChild($mod_text);

        /*
       * OpenAutomatic exercise has only one node in $exercise_data,
       * so it is safe to pop out this data.
        */
        $answer_data  = array_pop($exercise->getExerciseData());

        $answer_id      = $answer_data['id_nodo'];
        //$right_answer   = $exercise_data['nome'];
        //$answer_comment = $exercise_data['testo'];

        $exercise_answer = CDOMElement::create('div','id:answer');

//      $answer = CDOMElement::create('div');
//      $label1 = CDOMElement::create('label',"for:{$answer_id}_answer");
//      $label1->addChild(new CText(translateFN("Risposta corretta all'esercizio")));
//      //$textarea1 = CDOMElement::create('textarea',"id:{$answer_id}_answer, name:{$answer_id}_answer");
//      //$textarea1->addChild(new CText($right_answer));
//      $answer->addChild($label1);
//      $answer->addChild($textarea1);

        $answer = CDOMElement::create('div');
        $label1 = CDOMElement::create('label',"for:{$answer_id}_answer");
        $label1->addChild(new CText(translateFN("Possibile risposta")));
        $answer_text = CDOMElement::create('div', 'id:answer_text');
        $answer_text->addChild(new CText($answer_data['nome']));
        $mod_text1 = CDOMElement::create('a', "href:$form_action&edit={$answer_id}_answer");
        $mod_text1->addChild(new CText(translateFN('[Modifica]')));
        $answer->addChild($label1);
        $answer->addChild($answer_text);
        $answer->addChild($mod_text1);

        $comment = CDOMElement::create('div');
        $label2 = CDOMElement::create('label',"for:{$answer_id}_comment");
        $label2->addChild(new CText(translateFN("Commento")));
        //$textarea2 = CDOMElement::create('textarea',"id:{$answer_id}_comment, name:{$answer_id}_comment");
        //$textarea2->addChild(new CText($answer_data['testo']));
        $answer_comment = CDOMElement::create('div', 'id:answer_comment');
        $answer_comment->addChild(new CText($answer_data['testo']));
        $mod_text2 = CDOMElement::create('a', "href:$form_action&edit={$answer_id}_comment");
        $mod_text2->addChild(new CText(translateFN('[Modifica]')));
        $comment->addChild($label2);
        $comment->addChild($answer_comment);
        $comment->addChild($mod_text2);





//
//      $comment = CDOMElement::create('div');
//      $label2 = CDOMElement::create('label',"for:{$answer_id}_comment");
//      $label2->addChild(new CText(translateFN("Commento alla risposta corretta")));
//      //$textarea2 = CDOMElement::create('textarea',"id:{$answer_id}_comment, name:{$answer_id}_comment");
//      //$textarea2->addChild(new CText($answer_comment));
//      $comment->addChild($label2);
//      $comment->addChild($textarea2);

        $exercise_answer->addChild($answer);
        $exercise_answer->addChild($comment);


        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Modifica esercizio'));
        $buttons->addChild($input_submit);

        $form->addChild($exercise_title);
        $form->addChild($exercise_question);
        $form->addChild($exercise_answer);

        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }

    function getEditFieldForm($form_action, $exercise, $exercise_field=NULL) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');

        /*
       * Exercise title
        */
        if ($exercise_field == 'title') {
            $form->setAttribute('action',"$form_action&update=title");

            $exercise_title = CDOMElement::create('div','id:title');
            $label = CDOMElement::create('label','for:exercise_title');
            $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
            $input_title = CDOMElement::create('text','id:exercise_title, name:exercise_title');
            $input_title->setAttribute('value', $exercise->getTitle());
            $exercise_title->addChild($label);
            $exercise_title->addChild($input_title);
            $form->addChild($exercise_title);
        }
        /*
      * Exercise question
        */
        else if ($exercise_field == 'text') {
            $form->setAttribute('action',"$form_action&update=text");

            $exercise_question = CDOMElement::create('div','id:text');
            $label = CDOMElement::create('label','for:exercise_text');
            $label->addChild(new CText(translateFN("Testo dell'esercizio")));
            $exercise_text = CDOMElement::create('textarea','id:exercise_text, name:exercise_text');
            $exercise_text->addChild(new CText($exercise->getText()));
            $exercise_question->addChild($label);
            $exercise_question->addChild($exercise_text);
            $form->addChild($exercise_question);
        }
        else if(count($exercise_field) > 0) {
            $form->setAttribute('action',"$form_action&update=$exercise_field");

            $data = explode('_', $exercise_field);
            $node = $data[0].'_'.$data[1];
            $field = $data[2];
            //echo $node .'<br />' .$field;


            $textarea = CDOMElement::create('textarea',"id:{$node}_{$field}, name:{$node}_{$field}");

            switch($field) {
                case 'answer':
                    $value = $exercise->getExerciseDataAnswerForItem($node);
                    break;

                case 'comment':
                    $value = $exercise->getExerciseDataAuthorCommentForItem($node);
                    break;

                default:
                    $value = '';
            }

            $textarea->addChild(new CText($value));
            $form->addChild($textarea);
        }

        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }


    function getExerciseReport($exerciseObj, $id_course_instance) {
        return $this->getStudentForm('exercise.php', $exerciseObj);
    }

    function checkAuthorInput ( $post_data = array(), &$data = array() ) {
        $empty_field = false;
        $i = (isset($data['answers'])) ? count($data['answers']) + 1 : 1;
        if (!isset($data['last_index'])) $data['last_index'] = 1;
        $last_index = $data['last_index'];

        if ( !isset($data['empty_field']) ) {   // DOMANDA
            if ( isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else {
                $empty_field = true;
            }
            // RISPOSTA
            if ( isset($post_data['answer']) && $post_data['answer'] !== "" ) {
                $data['answers'][$i]['answer'] = $post_data['answer'];
                $data['last_answer'] = $post_data['answer'];
            }
            else {
                $empty_field = true;
            }
            // COMMENTO
            if ( isset($post_data['comment']) && $post_data['comment'] !== "" ) {
                $data['answers'][$i]['comment'] = $post_data['comment'];
                $data['last_comment'] = $post_data['comment'];
            }

        }
        else if ( isset($data['empty_field']) && $data['empty_field'] ) {
            // DOMANDA
            if ( !isset($data['question']) && isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else if ( !isset($post_data['question']) || $post_data['question'] == "") {
                $empty_field = true;
            }
            // RISPOSTA
            if ( !isset($data['answers'][$last_index]['answer']) && isset($post_data['answer']) && $post_data['answer'] !== ""  ) {
                $data['answers'][$last_index]['answer'] = $post_data['answer'];
                $data['last_answer'] = $post_data['answer'];
            }
            else if ( !isset($post_data['answer']) || $post_data['answer'] == "") {
                $empty_field = true;
            }
            // COMMENTO
            if ( !isset($data['answers'][$last_index]['comment']) && isset($post_data['comment']) && $post_data['comment'] !== "" ) {
                $data['answers'][$last_index]['comment'] = $post_data['comment'];
                $data['last_comment'] = $post_data['comment'];
            }
        }

        if ( $empty_field ) {
            $data['empty_field'] = true;
        }
        else {
            unset ( $data['empty_field']);
            $data['last_index']++;
        }

        return !$empty_field;
    }
}

/**
 * @name Cloze_ExerciseViewer
 * This class contains all of the methods needed to display an ADA Cloze Exercise based on the user
 * that is seeing this exercise.
 * An ADA Cloze Exercise is ...
 */
class Cloze_ExerciseViewer extends ExerciseViewer {
    function getStudentForm( $form_action, $exercise ) {

        $data = ExerciseUtils::tokenizeString($exercise->getText());

        $hidden_words = $exercise->getExerciseData();

        foreach ( $hidden_words as $answer ) {
            if ( $answer['ordine'] != 0 ) {
                $lista['nascoste'][] = array('id_nodo'     => $answer['id_nodo'],
                        'posizione'   => $answer['ordine'],
                        'parola'      => $answer['nome'],
                        'correttezza' => $answer['correttezza']);
            }
            else {
                $lista['altre'][] = $answer['nome'];
            }
        }

        switch ( $exercise->getExerciseSimplification() ) {
            case ADA_SIMPLIFY_EXERCISE_SIMPLICITY:
                return $this->viewSimplifiedExercise( $lista, $data, $form_action );

            case ADA_MEDIUM_EXERCISE_SIMPLICITY:
                return $this->viewMediumExercise( $lista, $data, $form_action  );

            case ADA_NORMAL_EXERCISE_SIMPLICITY:
            default:
                return $this->viewNormalExercise( $lista, $data, $form_action  );
        }
    }

    private function _getExercise( $exercise ) {
        $div = CDOMElement::create('div');

        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);

        $div_date = CDOMElement::create('div','id:exercise_date');
        $div_date->addChild(new CText(translateFN('Data di svolgimento').' '));
        $div_date->addChild(new CText($exercise->getExecutionDate()));
        $div->addChild($div_date);

        $div_question = CDOMElement::create('div','id:exercise_text');
        //$div_question->addChild(new CText(translateFN('Domanda')));
        $div_question->addChild($this->formatExerciseText($exercise));
        $div->addChild($div_question);

        $div_answer = CDOMElement::create('div','id:student_answer');
        $div_answer->addChild(new CText(translateFN('Risposta').' '));
        $div_answer2 = CDOMElement::create('div','id:answer');
        $div_answer2->addChild($this->formatStudentAnswer($exercise));
        $div_answer->addChild($div_answer2);
        $div->addChild($div_answer);

        $div_choices = CDOMElement::create('div','id:exercise_choices');
        $div_choices->addChild(new CText(translateFN('Scelte possibili:').' '));
        $div_choices->addChild($this->getHiddenWords($exercise));
        $div->addChild($div_choices);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $div_rating->addChild(new CText(translateFN('Punteggio:').' '));
        $div_rating->addChild(new CText($exercise->getRating()));
        $div->addChild($div_rating);
        
        return $div;
    }

    function getExerciseHtml( $exercise ) {
        $div = $this->_getExercise($exercise);
        return $div->getHtml();
    }

    function getTutorForm( $form_action, $exercise ) {
        $div = $this->_getExercise( $exercise );

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_textarea = CDOMElement::create('div','id:tutor_comment');
        $div_textarea->addChild(CDOMElement::create('textarea','id:comment, name:comment'));
        $form->addChild($div_textarea);

        $div_checkbox1 = CDOMElement::create('div','id:exercise_repeatable');
        $label1 = CDOMElement::create('label','for:ripetibile');
        $label1->addChild(new CText(translateFN('Ripetibile:').' '));
        $div_checkbox1->addChild($label1);
        $div_checkbox1->addChild(CDOMElement::create('checkbox','id:ripetibile, name:ripetibile'));
        $form->addChild($div_checkbox1);

        $div_checkbox2 = CDOMElement::create('div','id:exercise_sendmessage');
        $label2 = CDOMElement::create('label','for:messaggio');
        $label2->addChild(new CText(translateFN('Invia messaggio:').' '));
        $div_checkbox2->addChild($label2);
        $div_checkbox2->addChild(CDOMElement::create('checkbox','id:messaggio, name:messaggio'));
        $form->addChild($div_checkbox2);

        $form->addChild(CDOMElement::create('hidden',"name:student_id,value:{$exercise->getStudentId()}"));
        $form->addChild(CDOMElement::create('hidden',"name:course_instance,value:{$exercise->getCourseInstanceId()}"));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Salva');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();

    }

    function getAuthorForm ( $form_action, $data = array() ) {
        $error_msg = "";
        if ( isset($data['empty_field']) && $data['empty_field'] == true ) {
            $error_msg   = translateFN("Attenzione: campo non compilato!")."<br />";
            $answer      = parent::fill_field_with_data('last_answer', $data);
            $comment     = parent::fill_field_with_data('last_comment', $data);
            $hide        = parent::fill_field_with_data('last_hide', $data);
            $correctness = parent::fill_field_with_data('last_correctness', $data);
        }
        $question    = parent::fill_field_with_data('question', $data);

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_error_message = CDOMElement::create('div','class:error_msg');
        $div_error_message->addChild(new CText($error_msg));
        $form->addChild($div_error_message);

        $div_textarea1 = CDOMElement::create('div','id:exercise_question');
        $label1 = CDOMElement::create('label','for:question');
        $label1->addChild(new CText(translateFN('Frase completa:')));
        $div_textarea1->addChild($label1);
        $textarea1 = CDOMElement::create('textarea','id:question,name:question');
        $textarea1->addChild(new CText($question));
        $div_textarea1->addChild($textarea1);
        $form->addChild($div_textarea1);

        $div_textarea2 = CDOMElement::create('div','id:exercise_answer');
        $label2 = CDOMElement::create('label','for:answer');
        $label2->addChild(new CText(translateFN('Parola da nascondere:')));
        $div_textarea2->addChild($label2);
        $textarea2 = CDOMElement::create('textarea','id:answer,name:answer');
        $textarea2->addChild(new CText($answer));
        $div_textarea2->addChild($textarea2);
        $form->addChild($div_textarea2);

        $div_textarea3 = CDOMElement::create('div','id:exercise_comment');
        $label3 = CDOMElement::create('label','for:comment');
        $label3->addChild(new CText(translateFN('Commento alla risposta')));
        $div_textarea3->addChild($label3);
        $textarea3 = CDOMElement::create('textarea','id:comment,name:comment');
        $textarea3->addChild(new CText($comment));
        $div_textarea3->addChild($textarea3);
        $form->addChild($div_textarea3);

        $div_position = CDOMElement::create('div','id:exercise_position');
        $label4 = CDOMElement::create('label','for:hide');
        $label4->addChild(new CText(translateFN('Posizione:')));
        $div_position->addChild($label4);
        $div_position->addChild(CDOMElement::create('text',"id:hide,name:hide,value:$hide"));
        $form->addChild($div_position);

        $div_correctness = CDOMElement::create('div','id:exercise_correctness');
        $label4 = CDOMElement::create('label','for:hide');
        $label4->addChild(new CText(translateFN('Correttezza:')));
        $div_correctness->addChild($label4);
        $div_correctness->addChild(CDOMElement::create('text',"id:correctness,name:correctness,value:$correctness"));
        $form->addChild($div_correctness);

        $div_stop = CDOMElement::create('div','id:exercise_ended');
        $div_text = CDOMElement::create('div');
        $div_text->addChild(new CText(translateFN('Finito?')));
        $div_stop->addChild($div_text);
        $label5 = CDOMElement::create('label','for:finito');
        $label5->addChild(new CText('Si'));
        $div_stop->addChild($label5);
        $radio1 = CDOMElement::create('radio','name:finito,value:1,checked:true');
        $div_stop->addChild($radio1);
        $label6 = CDOMElement::create('label','for:finito');
        $label6->addChild(new CText('No'));
        $div_stop->addChild($label6);
        $radio2 = CDOMElement::create('radio','name:finito,value:0,checked:false');

        $div_stop->addChild($radio2);
        $form->addChild($div_stop);

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }


    function getEditForm($form_action, $exercise) {

        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');
        $form->setAttribute('action',"$form_action&save=1");      /*
       * Exercise title
        */
        $exercise_title = CDOMElement::create('div','id:title');
        $label = CDOMElement::create('label','for:exercise_title');
        $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
        $title = CDOMElement::create('div','id:exercise_title');
        $title->addChild(new CText($exercise->getTitle()));
        $mod_title = CDOMElement::create('a', "href:$form_action&edit=title");
        $mod_title->addChild(new CText(translateFN('[Modifica]')));
        $exercise_title->addChild($label);
        $exercise_title->addChild($title);
        $exercise_title->addChild($mod_title);

        /*
       * Exercise text
        */
        $text = array();

        $tokenized_text = ExerciseUtils::tokenizeString($exercise->getText());
        $text = array();
        foreach($tokenized_text as $t) {
            $text[] = new CText($t[0].$t[1]);
        }

        $hidden_words = $exercise->getExerciseData();
        $hidden_word_position = array();

        foreach ( $hidden_words as $hidden_word ) {
            $position = $hidden_word['ordine'] - 1;
            if ( !isset($hidden_word_position[$position]) ) {
                $hidden_word_position[$position] = $position;
                $span = CDOMElement::create('div');
                $link = CDOMElement::create('a', "href:$form_action&edit=$position");
                $link->addChild(new CText(translateFN('[Modifica]')));
                $span->addChild($text[$position]);
                $span->addChild($link);
                $text[$position] = $span;
            }
        }

        $exercise_question = CDOMElement::create('div','id:exercise_text');

        foreach ( $text as $parola ) {
            //$string .= $parola . ' ';
            $exercise_question->addChild($parola);
        }





        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Modifica esercizio'));
        $buttons->addChild($input_submit);

        $form->addChild($exercise_title);
        $form->addChild($exercise_question);
        //$form->addChild($answers);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }

    function getEditFieldForm($form_action, $exercise, $exercise_field=NULL) {
        $edit_exercise = CDOMElement::create('div');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');

        /*
       * Exercise title
        */
        if ($exercise_field == 'title') {
            $form->setAttribute('action',"$form_action&update=title");

            $exercise_title = CDOMElement::create('div','id:title');
            $label = CDOMElement::create('label','for:exercise_title');
            $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
            $input_title = CDOMElement::create('text','id:exercise_title, name:exercise_title');
            $input_title->setAttribute('value', $exercise->getTitle());
            $exercise_title->addChild($label);
            $exercise_title->addChild($input_title);
            $form->addChild($exercise_title);
        }
        /*
      * Exercise question
        */
        else if ($exercise_field == 'text') {
            $form->setAttribute('action',"$form_action&update=text");

            $exercise_question = CDOMElement::create('div','id:text');
            $label = CDOMElement::create('label','for:question');
            $label->addChild(new CText(translateFN("Testo dell'esercizio")));
            $exercise_text = CDOMElement::create('textarea','id:exercise_text, name:exercise_text');
            $exercise_text->addChild(new CText($exercise->getText()));
            $exercise_question->addChild($label);
            $exercise_question->addChild($exercise_text);
            $form->addChild($exercise_question);
        }
        else if(count($exercise_field) > 0 && is_numeric($exercise_field)) {
            $form->setAttribute('action',"$form_action&update=$exercise_field");
            $position = $exercise_field+1;
            $exercise_data = $exercise->getExerciseData();
            $hidden_words_in_this_place = array();

            foreach($exercise_data as $hidden_word) {
                if ($hidden_word['ordine'] == $position && $hidden_word['correttezza'] < ADA_MAX_SCORE) {
                    $node = $hidden_word['id_nodo'];

                    $possible_answer = CDOMElement::create('div', 'class:possible_answer');

                    $answer      = CDOMElement::create('text',"id:{$node}_answer, name:{$node}_answer");
                    $answer->setAttribute('value', $hidden_word['nome']);
                    $possible_answer->addChild($answer);

                    $comment     = CDOMElement::create('text',"id:{$node}_comment, name:{$node}_comment");
                    $comment->setAttribute('value',$hidden_word['testo']);
                    $possible_answer->addChild($comment);

                    $correctness = CDOMElement::create('text',"id:{$node}_correctness, name:{$node}_correctness");
                    $correctness->setAttribute('value',$hidden_word['correttezza']);
                    $possible_answer->addChild($correctness);

                    $delete = CDOMElement::create('a', "href:$form_action&delete={$node}");
                    $delete->addChild(new CText(translateFN('[Elimina]')));
                    $possible_answer->addChild($delete);

                    $form->addChild($possible_answer);
                }
            }
        }

        $add_answer = CDOMElement::create('div');
        $link = CDOMElement::create('a',"href:$form_action&edit=$position&add=1");
        $link->addChild(new CText(translateFN('Aggiungi risposta')));
        $add_answer->addChild($link);
        $form->addChild($add_answer);

        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }

    function getAddAnswerForm($form_action, $exercise, $field) {
        $add_answer = CDOMElement::create('div');
        $form = CDOMElement::create('form', 'id:added_answer, name:added_answer, method:post');
        $form->setAttribute('action',"$form_action&add_answer_to={$exercise->getId()}");

        $new_answer = CDOMElement::create('div', 'class:possible_answer');

        $answer      = CDOMElement::create('text','id:answer, name:answer');
        $answer->setAttribute('value', translateFN('Testo della risposta'));
        $new_answer->addChild($answer);

        $comment     = CDOMElement::create('text','id:comment, name:comment');
        $comment->setAttribute('value',translateFN('Commento alla risposta'));
        $new_answer->addChild($comment);

        $correctness = CDOMElement::create('text','id:correctness, name:correctness');
        $correctness->setAttribute('value',translateFN('Correttezza della risposta'));
        $new_answer->addChild($correctness);

        $position = CDOMElement::create('hidden', 'id:position, name:position');
        $position->setAttribute('value',$field);
        $new_answer->addChild($position);

        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:add_answer, name:add_answer');
        $input_submit->setAttribute('value',translateFN('Aggiungi questa risposta'));
        $buttons->addChild($input_submit);

        $form->addChild($new_answer);
        $form->addChild($buttons);

        $add_answer->addChild($form);
        return $add_answer;
    }

    function getExerciseReport($exerciseObj, $id_course_instance) {
        return $this->getStudentForm('exercise.php', $exerciseObj);
    }

    function checkAuthorInput ( $post_data = array(), &$data = array() ) {
        $empty_field = false;
        $i = (isset($data['answers'])) ? count($data['answers']) + 1 : 1;
        if (!isset($data['last_index'])) $data['last_index'] = 1;
        $last_index = $data['last_index'];

        if ( !isset($data['empty_field']) ) {   // DOMANDA
            if ( isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else {
                $empty_field = true;
            }
            // RISPOSTA
            if ( isset($post_data['answer']) && $post_data['answer'] !== "" ) {
                $data['answers'][$i]['answer'] = $post_data['answer'];
                $data['last_answer'] = $post_data['answer'];
            }
            else {
                $empty_field = true;
            }
            // NASCONDI PAROLA
            if ( isset($post_data['hide']) && $post_data['hide'] !== "" ) {
                $data['answers'][$i]['hide'] = $post_data['hide'];
                $data['last_hide'] = $post_data['hide'];
            }
            else {
                $empty_field = true;
            }

            // CORRETTEZZA
            if ( isset($post_data['correctness']) && $post_data['correctness'] !== "" ) {
                $data['answers'][$i]['correctness'] = $post_data['correctness'];
                $data['last_correctness'] = $post_data['correctness'];
            }
            else {
                $empty_field = true;
            }
            // COMMENTO
            if ( isset($post_data['comment']) && $post_data['comment'] !== "" ) {
                $data['answers'][$i]['comment'] = $post_data['comment'];
                $data['last_comment'] = $post_data['comment'];
            }

        }
        else if ( isset($data['empty_field']) && $data['empty_field'] ) {
            // DOMANDA
            if ( !isset($data['question']) && isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else if ( !isset($post_data['question']) || $post_data['question'] == "") {
                $empty_field = true;
            }
            // RISPOSTA
            if ( !isset($data['answers'][$last_index]['answer']) && isset($post_data['answer']) && $post_data['answer'] !== ""  ) {
                $data['answers'][$last_index]['answer'] = $post_data['answer'];
                $data['last_answer'] = $post_data['answer'];
            }
            else if ( !isset($post_data['answer']) || $post_data['answer'] == "") {
                $empty_field = true;
            }
            // NASCONDI PAROLA
            if ( !isset($data['answers'][$last_index]['hide']) && isset($post_data['hide']) && $post_data['hide'] !== ""  ) {
                $data['answers'][$last_index]['hide'] = $post_data['hide'];
                $data['last_hide'] = $post_data['hide'];
            }
            else if ( !isset($post_data['hide']) || $post_data['hide'] == "") {
                $empty_field = true;
            }
            // CORRETTEZZA
            if ( !isset($data['answers'][$last_index]['correctness']) && isset($post_data['correctness']) && $post_data['correctness'] !== ""  ) {
                $data['answers'][$last_index]['correctness'] = $post_data['correctness'];
                $data['last_correctness'] = $post_data['correctness'];
            }
            else if ( !isset($post_data['correctness']) || $post_data['correctness'] == "") {
                $empty_field = true;
            }
            // COMMENTO
            if ( !isset($data['answers'][$last_index]['comment']) && isset($post_data['comment']) && $post_data['comment'] !== "" ) {
                $data['answers'][$last_index]['comment'] = $post_data['comment'];
                $data['last_comment'] = $post_data['comment'];
            }
        }

        if ( $empty_field ) {
            $data['empty_field'] = true;
        }
        else {
            unset ( $data['empty_field']);
            $data['last_index']++;
        }

        return !$empty_field;
    }

    /*
     * Private methods
    */

    // Used by getStudentForm
    function viewSimplifiedExercise( $lista, $exercisetext, $form_action  ) {

		$posizione = array();
        foreach ( $lista['nascoste'] as $item ) {
            $posizione[$item['posizione']][$item['parola']] = $item['parola'];
        }

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);
        $cloze_text = CDOMElement::create('div','id:cloze_exercise_text');

        $words_count = count($exercisetext);
        for ($i=0; $i<$words_count; $i++) {
            if(isset($posizione[$i+1])) {
                $p = $i+1;
                $div_select = CDOMElement::create('div');

				$empty_option = array('---'=>'---');
				$options = array_merge($empty_option,parent::shuffleList($posizione[$p]));

                $select = BaseHtmlLib::selectElement("id:useranswer[$p],name:useranswer[$p], size:0",$options);
                $div_select->addChild($select);
                $div_select->addChild(new CText($exercisetext[$i][1]));
                $cloze_text->addChild($div_select);
            }
            else {
                $word = new CText($exercisetext[$i][0].$exercisetext[$i][1]);
                $cloze_text->addChild($word);
            }
        }

        $form->addChild($cloze_text);

        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));
        $form->addChild($div_buttons);

        $div->addChild($form);
        return $div->getHtml();
    }

    function viewMediumExercise( $lista, $exercisetext, $form_action  ) {
        $posizione = array();

        foreach ($lista['nascoste'] as $parola ) {
            if ($parola['correttezza'] == ADA_MAX_SCORE ) {
                $posizione[$parola['posizione']] = strlen($parola['parola']);

            }
        }
        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $words_count = count($exercisetext);
        for ($i=0; $i<$words_count; $i++) {
            if(isset($posizione[$i+1])) {
                $p = $i+1;
                $div_text = CDOMElement::create('div');
                $text_input = CDOMElement::create('text',"id:useranswer[$p],name:useranswer[$p], maxlength:{$posizione[$p]}, size:{$posizione[$p]}");
                $div_text->addChild($text_input);
                $div_text->addChild(new CText($exercisetext[$i][1]));
                $form->addChild($div_text);
            }
            else {
                $word = new CText($exercisetext[$i][0].$exercisetext[$i][1]);
                $form->addChild($word);
            }
        }

        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));
        $form->addChild($div_buttons);

        $div->addChild($form);
        return $div->getHtml();
    }

    function viewNormalExercise( $lista, $exercisetext, $form_action  ) {
        // tokenizzare il testo dell'esercizio e stampare l'esercizio
        // con le posizioni delle parole nascoste contenenti degli input text senza
        // dimensione massima fissata
        $posizione = array();
        foreach ($lista['nascoste'] as $parola ) {
            $posizione[$parola['posizione']] = 1;
        }

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);
        $text = CDOMElement::create('div', 'id:exercise_text');
        $form->addChild($text);
        $words_count = count($exercisetext);
        for ($i=0; $i<$words_count; $i++) {
            if(isset($posizione[$i+1])) {
                $p = $i+1;
                $div_text = CDOMElement::create('div', 'class:hidden_word');
                $text_input = CDOMElement::create('text',"id:useranswer[$p],name:useranswer[$p]");
                $div_text->addChild($text_input);
                $div_text->addChild(new CText($exercisetext[$i][1]));
                $text->addChild($div_text);
            }
            else {
                $word = new CText($exercisetext[$i][0].$exercisetext[$i][1]);
                $text->addChild($word);
            }
        }

        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));
        $form->addChild($div_buttons);

        $div->addChild($form);
        return $div->getHtml();
    }

    // Used by getTutorForm

    function formatExerciseText( $exercise ) {
        $text = array();
        $text = ExerciseUtils::tokenizeString($exercise->getText());

        $hidden_words = $exercise->getExerciseData();
        $hidden_word_position = array();

        foreach ( $hidden_words as $hidden_word ) {
            $position = $hidden_word['ordine'] - 1;
            if ( !isset($hidden_word_position[$position]) ) {
                $hidden_word_position[$position] = $position;
                //8gennaio
                //$text[$position] = '<span class="RIGHT_ANSWER">*'.$text[$position].'*</span>';
                // vito 4 feb 2009
                //$text[$position][0] = '<span class="RIGHT_ANSWER">*'.$text[$position][0].'*</span>';
                $span = CDOMElement::create('span','class:RIGHT_ANSWER');
                $span->addChild(new CText($text[$position][0]));
                $text[$position][0] = $span->getHtml();
            }
        }

        $string = '';
        $span = CDOMElement::create('span');
        foreach ( $text as $parola ) {
            //8gennaio2009
            //    $string .= $parola . ' ';
            //vito 4 feb 2009
            //$string .= $parola[0].$parola[1];
            $span->addChild(new CText($parola[0].$parola[1]));
        }
        //vito 4 feb 2009
        //return $string;
        return $span;
    }



    function formatStudentAnswer( $exercise ) {
        $text = array();
        $text = ExerciseUtils::tokenizeString($exercise->getText());

        $student_answer = array();
        $student_answer = ExerciseUtils::tokenizeString($exercise->getStudentAnswer());

        $hidden_words = $exercise->getExerciseData();
        $hidden_word_position = array();

        $posizione = 0;
        foreach ( $hidden_words as $hidden_word ) {
            $position = $hidden_word['ordine'] - 1;
            if ( !isset($hidden_word_position[$position]) ) {
                $hidden_word_position[$position] = $position;
            }
        }

        foreach ( $hidden_word_position as $position ) {
            if ( $student_answer[$position][0] == $text[$position][0] ) {
                $class_name = 'right_answer';
                //vito 4 feb 2009
                //$delimiter  = '*';
            }
            else {
                $class_name = 'wrong_answer';
                //vito 4 feb 2009
                //$delimiter  = '#';
            }
            //vito 4 feb 2009
            //$student_answer[$position][0] = '<span class="'.$class_name.'">'.$delimiter.$student_answer[$position][0].$delimiter.'</span>';
            $span = CDOMElement::create('span',"class:$class_name");
            $span->addChild(new CText($student_answer[$position][0]));
            $student_answer[$position][0] = $span->getHtml();
        }
        //vito 4 feb 2009
        //$string = '';
        $span = CDOMElement::create('span');
        foreach ($student_answer as $a ) {
            //vito 4 feb 2009
            //    $string .= $a[0].$a[1];
            $span->addChild(new CText($a[0].$a[1]));
        }
        //vito 4 feb 2009
        //return $string;
        return $span;
    }

    function getHiddenWords( $exercise ) {
        $text = array();
        $text = ExerciseUtils::tokenizeString($exercise->getText());

        $hidden_words = $exercise->getExerciseData();

        $parole_nascoste = array();
        foreach ( $hidden_words as $word ) {
            $posizione = $word['ordine'];
            $parole_nascoste[$posizione][] = $word['nome'];
        }


        $output = CDOMElement::create('div');
        foreach ( $parole_nascoste as $posizione => $array ) {
            $line = CDOMElement::create('div');
            $line->addChild(new CText($text[$posizione-1][0].': '));
            foreach ( $array as $word ) {
                if ( $word != $text[$posizione-1][0] ) {
                    $line->addChild(new CText($word.' '));
                }
            }
            $output->addChild($line);
        }
        //vito 4 feb 2009
        //return $output->getHtml();
        return $output;
    }
}

/**
 * @name OpenUpload_ExerciseViewer
 * This class contains all of the methods needed to display an ADA OpenUpload Exercise based on the user
 * that is seeing this exercise.
 * An ADA OpenUpload Exercise is ...
 */
class OpenUpload_ExerciseViewer extends ExerciseViewer {
    function getStudentForm( $form_action, $exercise ) {
        $div = CDOMElement::create('div');
        /*
        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:')));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);
        */
        $div_text = CDOMElement::create('div','id:exercise_text');
        $div_text->addChild(new CText($exercise->getText()));
        $div->addChild($div_text);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);
        $form->setAttribute('enctype','multipart/form-data');

        $div_textarea = CDOMElement::create('div','id:answer');
        $label1 = CDOMElement::create('label','for:useranswer');
        $label1->addChild(new CText(translateFN('Risposta')));
        $div_textarea->addChild($label1);
        $div_textarea->addChild(CDOMElement::create('textarea','id:useranswer, name:useranswer'));
        $form->addChild($div_textarea);
        $div_file = CDOMElement::create('div','id:file_upload');
        $label2 = CDOMElement::create('label','for:file_up');
        $label2->addChild(new CText(translateFN('File:')));
        $div_file->addChild($label2);
        $div_file->addChild(CDOMElement::create('file','id:file_up, name:file_up'));
        $form->addChild($div_file);
        $form->addChild(CDOMElement::create('hidden','id:op, name:op, value:answer'));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    private function _getExercise( $exercise ) {
$http_root_dir = $GLOBALS['http_root_dir'];
        $root_dir      = $GLOBALS['root_dir'];
        /*
        $f = new Form_html();
		$f->form_name = 'esercizio';
		$f->method = "POST";
		$f->action = $form_action;
	    $form  = $f->write_form();
		$form .= "<b>".translateFN("Esercizio").":</b>" . $exercise->getTitle() . "<br />";
		$form .= "<b>".translateFN("Domanda").":</b>" . $exercise->getText() . "<br />";
		$form .= "<b>".translateFN("Risposta").":</b>" . $exercise->getStudentAnswer() . "<br />";

		if ( is_file($root_dir.$exercise->getAttachment()))
		{
		    $form .= "<a href=\"".$http_root_dir.$exercise->getAttachment() . "\">".translateFN("File allegato dallo studente") ."</a><br />";
		}
		else
		{
		    $form .= translateFN("Non ci sono allegati")."<br />";
		}

		$form .= $f->html_input_text(translateFN("Punteggio"), 'punteggio', $exercise->getRating(),20,20,false);
		$form .= $f->html_textarea(translateFN("Commento"), 'comment');
		$form .= $f->html_input_checkbox(translateFN("Ripetibile"), 'ripetibile','');
		$form .= $f->html_input_checkbox(translateFN("Invia messaggio"), 'messaggio','');
		$form .= $f->html_input_hidden('student_id', $exercise->getStudentId());
		$form .= $f->html_input_hidden('course_instance', $exercise->getCourseInstanceId());
		$form .= $f->html_input_submit("submit","button",translateFN("Salva"));
		$form .= $f->html_input_reset(translateFN("Reset"));
		$form .= $f->close_form();
        return $form;
        */

        $div = CDOMElement::create('div');

        $div_title = CDOMElement::create('div','id:exercise_title');
        $div_title->addChild(new CText(translateFN('Esercizio:').' '));
        $div_title->addChild(new CText($exercise->getTitle()));
        $div->addChild($div_title);

        $div_date = CDOMElement::create('div','id:exercise_date');
        $div_date->addChild(new CText(translateFN('Data di svolgimento:').' '));
        $div_date->addChild(new CText($exercise->getExecutionDate()));
        $div->addChild($div_date);

        $div_question = CDOMElement::create('div','id:exercise_question');
        $div_question->addChild(new CText(translateFN('Domanda:').' '));
        $div_question->addChild(new CText($exercise->getText()));
        $div->addChild($div_question);

        $div_answer = CDOMElement::create('div','id:exercise_answer');
        $div_answer->addChild(new CText(translateFN('Risposta:').' '));
        $div_answer->addChild(new CText($exercise->getStudentAnswer()));
        $div->addChild($div_answer);

        $div_attachment = CDOMElement::create('div');
        $div_attachment->addChild(new CText(translateFN('File allegato:').' '));
        $path_to_file = $root_dir.$exercise->getAttachment();
        if(is_file($path_to_file)) {
            //vito 6 feb 2009, modificato il link in href
            $link = CDOMElement::create('a',"href:$http_root_dir{$exercise->getAttachment()}");
            $filename = basename($exercise->getAttachment());
            $link->addChild(new CText($filename));
        }
        else {
            $link = new CText('Nessuno');
        }
        $div_attachment->addChild($link);
        $div->addChild($div_attachment);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $div_rating->addChild(new CText(translateFN('Punteggio:').' '));
        $div_rating->addChild(new CText($exercise->getRating()));
        $div->addChild($div_rating);

        return $div;
    }

    function getExerciseHtml( $exercise ) {
        $div = $this->_getExercise($exercise);
        return $div->getHtml();
    }

    function getTutorForm( $form_action, $exercise ) {

        $div = $this->_getExercise($exercise);

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_rating = CDOMElement::create('div','id:exercise_rating');
        $label = CDOMElement::create('label','for:punteggio');
        $label->addChild(new CText('Punteggio:').' ');
        $div_rating->addChild($label);
        $div_rating->addChild(CDOMElement::create('text',"id:punteggio,name:punteggio,value:{$exercise->getRating()}"));
        $form->addChild($div_rating);

        $div_textarea = CDOMElement::create('div','id:tutor_comment');
        $div_textarea->addChild(CDOMElement::create('textarea','id:comment, name:comment'));
        $form->addChild($div_textarea);

        $div_checkbox1 = CDOMElement::create('div','id:exercise_repeatable');
        $label1 = CDOMElement::create('label','for:ripetibile');
        $label1->addChild(new CText(translateFN('Ripetibile:').' '));
        $div_checkbox1->addChild($label1);
        $div_checkbox1->addChild(CDOMElement::create('checkbox','id:ripetibile, name:ripetibile'));
        $form->addChild($div_checkbox1);

        $div_checkbox2 = CDOMElement::create('div','id:exercise_sendmessage');
        $label2 = CDOMElement::create('label','for:messaggio');
        $label2->addChild(new CText(translateFN('Invia messaggio:').' '));
        $div_checkbox2->addChild($label2);
        $div_checkbox2->addChild(CDOMElement::create('checkbox','id:messaggio, name:messaggio'));
        $form->addChild($div_checkbox2);

        $form->addChild(CDOMElement::create('hidden',"name:student_id,value:{$exercise->getStudentId()}"));
        $form->addChild(CDOMElement::create('hidden',"name:course_instance,value:{$exercise->getCourseInstanceId()}"));

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Salva');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);

        $div->addChild($form);
        
        return $div->getHtml();
    }

    function getAuthorForm ( $form_action, $data = array() ) {
        if ( isset($data['empty_field']) && $data['empty_field'] == true ) {
            $error_msg = translateFN("Attenzione: campo non compilato!")."<br />";
            $question  = parent::fill_field_with_data('question', $data);
        }

        $div = CDOMElement::create('div');

        $form = CDOMElement::create('form','id:esercizio, name:esercizio, method:POST');
        $form->setAttribute('action', $form_action);

        $div_error_message = CDOMElement::create('div','class:error_msg');
        $div_error_message->addChild(new CText($error_msg));
        $form->addChild($div_error_message);

        $div_textarea1 = CDOMElement::create('div','id:exercise_question');
        $label1 = CDOMElement::create('label','for:question');
        $label1->addChild(new CText(translateFN('Testo domanda')));
        $div_textarea1->addChild($label1);
        $textarea1 = CDOMElement::create('textarea','id:question,name:question');
        $textarea1->addChild(new CText($question));
        $div_textarea1->addChild($textarea1);
        $form->addChild($div_textarea1);

        $div_buttons = CDOMElement::create('div','id:buttons');
        $button_text = translateFN('Procedi');
        $div_buttons->addChild(CDOMElement::create('submit',"id:button,name:button,value:$button_text"));
        $div_buttons->addChild(CDOMElement::create('reset'));

        $form->addChild($div_buttons);
        $div->addChild($form);
        return $div->getHtml();
    }

    function getEditForm($form_action, $exercise) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');
        $form->setAttribute('action',"$form_action&save=1");
        /*
       * Exercise title
        */
        $exercise_title = CDOMElement::create('div','id:title');
        $label = CDOMElement::create('label','for:exercise_title');
        $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
        $input_title = CDOMElement::create('div','id:exercise_title');
        $input_title->addChild(new CText($exercise->getTitle()));
        $mod_title = CDOMElement::create('a',"href:$form_action&edit=title");
        $mod_title->addChild(new CText(translateFN("Modifica")));
        $exercise_title->addChild($label);
        $exercise_title->addChild($input_title);
        $exercise_title->addChild($mod_title);

        /*
       * Exercise question
        */
        $exercise_question = CDOMElement::create('div','id:text');
        $label = CDOMElement::create('label','for:exercise_text');
        $label->addChild(new CText(translateFN("Testo dell'esercizio")));
        $exercise_text = CDOMElement::create('div','id:exercise_text');
        $exercise_text->addChild(new CText($exercise->getText()));
        $mod_text = CDOMElement::create('a',"href:$form_action&edit=text");
        $mod_text->addChild(new CText(translateFN("Modifica")));
        $exercise_question->addChild($label);
        $exercise_question->addChild($exercise_text);
        $exercise_question->addChild($mod_text);

        /*
       * Form buttons
        */
        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);

        $form->addChild($exercise_title);
        $form->addChild($exercise_question);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);

        return $edit_exercise;
    }

    function getEditFieldForm($form_action, $exercise, $exercise_field=NULL) {
        $edit_exercise = CDOMElement::create('div','id:edit_exercise');
        $form = CDOMElement::create('form', 'id:edited_exercise, name:edited_exercise, method:post');

        /*
       * Exercise title
        */
        if ($exercise_field == 'title') {
            $form->setAttribute('action',"$form_action&update=title");

            $exercise_title = CDOMElement::create('div','id:title');
            $label = CDOMElement::create('label','for:exercise_title');
            $label->addChild(new CText(translateFN("Titolo dell'esercizio")));
            $input_title = CDOMElement::create('text','id:exercise_title, name:exercise_title');
            $input_title->setAttribute('value', $exercise->getTitle());
            $exercise_title->addChild($label);
            $exercise_title->addChild($input_title);
            $form->addChild($exercise_title);
        }
        /*
      * Exercise question
        */
        else if ($exercise_field == 'text') {
            $form->setAttribute('action',"$form_action&update=text");

            $exercise_question = CDOMElement::create('div','id:text');
            $label = CDOMElement::create('label','for:question');
            $label->addChild(new CText(translateFN("Testo dell'esercizio")));
            $exercise_text = CDOMElement::create('textarea','id:exercise_text, name:exercise_text');
            $exercise_text->addChild(new CText($exercise->getText()));
            $exercise_question->addChild($label);
            $exercise_question->addChild($exercise_text);
            $form->addChild($exercise_question);
        }

        $buttons = CDOMElement::create('div');
        $input_submit = CDOMElement::create('submit','id:edit_exercise, name:edit_exercise');
        $input_submit->setAttribute('value',translateFN('Salva modifiche'));
        $buttons->addChild($input_submit);
        $form->addChild($buttons);

        $edit_exercise->addChild($form);
        return $edit_exercise;
    }


    function getExerciseReport($exerciseObj, $id_course_instance) {
        return $this->getStudentForm('exercise.php', $exerciseObj);
    }

    function checkAuthorInput ( $post_data = array(), &$data = array() ) {
        $empty_field = false;
        $i = (isset($data['answers'])) ? count($data['answers']) + 1 : 1;
        if (!isset($data['last_index'])) $data['last_index'] = 1;
        $last_index = $data['last_index'];

        if ( !isset($data['empty_field']) ) {   // DOMANDA
            if ( isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else {
                $empty_field = true;
            }
        }
        else if ( isset($data['empty_field']) && $data['empty_field'] ) {
            // DOMANDA
            if ( !isset($data['question']) && isset($post_data['question']) && $post_data['question'] !== "" ) {
                $data['question'] = $post_data['question'];
            }
            else if ( !isset($post_data['question']) || $post_data['question'] == "") {
                $empty_field = true;
            }
        }

        if ( $empty_field ) {
            $data['empty_field'] = true;
        }
        else {
            unset ( $data['empty_field']);
            $data['last_index']++;
        }

        return !$empty_field;
    }

}

class ExerciseViewerFactory {
    function create( $exercise_type ) {
        switch ( $exercise_type ) {
            case ADA_STANDARD_EXERCISE_TYPE:
            default:
                return new Standard_ExerciseViewer();

            case ADA_OPEN_MANUAL_EXERCISE_TYPE:
                return new OpenManual_ExerciseViewer();

            case ADA_OPEN_AUTOMATIC_EXERCISE_TYPE:
                return new OpenAutomatic_ExerciseViewer();

            case ADA_CLOZE_EXERCISE_TYPE:
                return new Cloze_ExerciseViewer();

            case ADA_OPEN_UPLOAD_EXERCISE_TYPE:
                return new OpenUpload_ExerciseViewer();
        }
    }
}

class ExerciseCorrectionFactory {
    function create( $exercise_type ) {
        switch ( $exercise_type ) {
            case ADA_STANDARD_EXERCISE_TYPE:
            default:
                return new Standard_ExerciseCorrection();

            case ADA_OPEN_MANUAL_EXERCISE_TYPE:
                return new OpenManual_ExerciseCorrection();

            case ADA_OPEN_AUTOMATIC_EXERCISE_TYPE:
                return new OpenAutomatic_ExerciseCorrection();

            case ADA_CLOZE_EXERCISE_TYPE:
                return new Cloze_ExerciseCorrection();

            case ADA_OPEN_UPLOAD_EXERCISE_TYPE:
                return new OpenUpload_ExerciseCorrection();
        }
    }
}
/*
interface iExerciseCorrection
{
     function rateStudentAnswer();
}
*/
class ExerciseCorrection //implements iExerciseCorrection
{
    function raiseUserLevel( $exercise, $user_level ) {
        return ($user_level == $exercise->getExerciseLevel() && $exercise->getExerciseBarrier() && $exercise->getRating() == ADA_MAX_SCORE);
    }

    function getMessageForStudent( $username, $exercise ) {
        $msg  = "Punteggio ottenuto: " . $exercise->getRating() . "<BR>";
        $msg .= "La tua risposta " . $exercise->getStudentAnswer() . "<BR>";
        $msg .= "Il commento dell'autore: " . $exercise->getAuthorComment() . "<BR>";
        return $msg;
    }

    function getMessageForTutor ( $username, $exercise ) {
        /*
         * OLD EXERCISE.PHP CODE
        */

        $node_title = $exercise->getTitle();
        $node_exAr = explode('_',$exercise->getId());
        $node_ex_id =  $node_exAr[1];
        $testo = translateFN("Esercizio: ").$node_title ." <link type=internal value=\"$node_ex_id\"><br />\n";

//        $useranswer = $exercise->getStudentAnswer();
//        $testo .= "Qui la risposta dello studente<BR>";

        $testo .= $exercise->getStudentAnswer();
        $testo .= "Valutazione: " . $exercise->getRating() . "<BR>";
        return $testo;
//        if (is_array($useranswer)){
//            $userAnswerStr = "<ul>\n";
//            foreach ($useranswer as $ua){
//                $node_ansHa = $dh->get_node_info($ua);
//                $answerStr = $node_ansHa ['text'];
//                $node_ansAr = explode('_',$ua);
//                $node_ans_id =  $node_ansAr[1];
//                $userAnswerStr .= "<li><link type=internal value=\"$node_ans_id\"> $answerStr</li>\n";
//            }
//            $userAnswerStr = "</ul>\n";
//            $testo .= translateFN("Risposta: ").$userAnswerStr;
//        }  else {
//            if  ($exercise_type==3){
//                $node_ansHa = $dh->get_node_info($useranswer);
//                $answerStr = $node_ansHa ['text'];
//                $node_ansAr = explode('_',$useranswer);
//                $node_ans_id =  $node_ansAr[1];
//                $userAnswer = "<link type=internal value=\"$node_ans_id\">$answerStr\n";
//                $testo .= translateFN("Risposta: ").$useranswer;
//            } else {
//                $testo .= translateFN("Risposta: ").$useranswer;
//            }
//
//        }
//        $testo .= translateFN("Valutazione: ") . $this->rating;
//        return $testo;
    }

    function setStudentData ( $exercise, $id_student, $id_course_instance ) {
        $exercise->setStudentId($id_student);
        $exercise->setCourseInstanceId($id_course_instance);
    }
}

class Standard_ExerciseCorrection extends ExerciseCorrection {
    function rateStudentAnswer( $exercise, $student_answer, $id_student, $id_course_instance ) {
        $correctness   = $exercise->getCorrectness($student_answer);

        $exercise->setStudentAnswer($student_answer);
        $exercise->setRating($correctness);
        parent::setStudentData($exercise, $id_student, $id_course_instance);
    }

    function getMessageForStudent( $username, $exercise ) {
        $interaction = $exercise->getExerciseInteraction();

//        $msg  = translateFN('Esercizio inviato') . '<br />';
//        $msg .= translateFN("Titolo dell'esercizio: ") . $exercise->getTitle() . '<br />';
//        $msg .= translateFN('Domanda: ') . $exercise->getText() . '<br />';
//        $msg .= translateFN('La tua risposta: ') . $exercise->getAnswerText($exercise->getStudentAnswer()) . '<br />';

        $message_for_student = CDOMElement::create('div','id:message_for_student');

        $exercise_submitted = CDOMElement::create('div','id:exercise_submitted');
        $exercise_submitted->addChild(new CText(translateFN('Esercizio inviato.')));
        $message_for_student->addChild($exercise_submitted);

        $exercise_title = CDOMElement::create('div','id:exercise_title');
        //$label = CDOMElement::create('div','class:page_label');
        //$label->addChild(new CText(translateFN("Titolo dell'esercizio:")));
        $title = CDOMElement::create('div','id:title');
        $title->addChild(new CText($exercise->getTitle()));
        //$exercise_title->addChild($label);
        $exercise_title->addChild($title);
        $message_for_student->addChild($exercise_title);

        $exercise_question = CDOMElement::create('div','id:exercise_question');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('Domanda')));
        $question = CDOMElement::create('div','id:question');
        $question->addChild(new CText($exercise->getText()));
        $exercise_question->addChild($label);
        $exercise_question->addChild($question);
        $message_for_student->addChild($exercise_question);

        $student_answer = CDOMElement::create('div','id:student_answer');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('La tua risposta')));
        $answer = CDOMElement::create('div','id:answer');
        $answer->addChild(new CText($exercise->getAnswerText($exercise->getStudentAnswer())));
        $student_answer->addChild($label);
        $student_answer->addChild($answer);
        $message_for_student->addChild($student_answer);

        switch ($interaction) {
            case ADA_FEEDBACK_EXERCISE_INTERACTION: // with feedback
//            $msg .= translateFN("Il commento dell'autore: ") . $exercise->getAuthorComment($exercise->getStudentAnswer()) . '<br />';
                $comment = CDOMElement::create('div','id:author_comment');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN("Il commento dell'autore:")));
                $comment->addChild($label);
                $comment->addChild(new CText($exercise->getAuthorComment($exercise->getStudentAnswer())));
                $message_for_student->addChild($comment);
                break;

            case ADA_RATING_EXERCISE_INTERACTION: // with feedback and rating
//            $msg .= translateFN('Punteggio ottenuto: ') . $exercise->getRating() . '<br />';
//            $msg .= translateFN("Il commento dell'autore: ") . $exercise->getAuthorComment($exercise->getStudentAnswer()) . '<br />';
                $exercise_rating = CDOMElement::create('div','id:exercise_rating');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN('Punteggio ottenuto:')));
                $rating = CDOMElement::create('div','id:rating');
                $rating->addChild(new CText($exercise->getRating()));
                $exercise_rating->addChild($label);
                $exercise_rating->addChild($rating);
                $message_for_student->addChild($exercise_rating);

                $comment = CDOMElement::create('div','id:author_comment');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN("Il commento dell'autore:")));
                $comment->addChild($label);
                $comment->addChild(new CText($exercise->getAuthorComment($exercise->getStudentAnswer())));
                $message_for_student->addChild($comment);
                break;

            case ADA_BLIND_EXERCISE_INTERACTION: // no feedback
            default:
                break;
        }
//        return $msg;
        return $message_for_student;
    }
}

class OpenManual_ExerciseCorrection extends ExerciseCorrection {
    function rateStudentAnswer( $exercise, $student_answer, $id_student, $id_course_instance ) {
        $exercise->setStudentAnswer($student_answer);
        $exercise->setRating(0);
        parent::setStudentData($exercise, $id_student, $id_course_instance);
    }

    function getMessageForStudent( $username, $exercise ) {
//        $msg  = translateFN('Esercizio inviato') . '<br />';
//        $msg .= translateFN("Titolo dell'esercizio: ") . $exercise->getTitle() . '<br />';
//        $msg .= translateFN('Domanda: ') . $exercise->getText() . '<br />';
//        $msg .= translateFN('La tua risposta è: ') . $exercise->getStudentAnswer() . '<br />';
//        return $msg;
        $message_for_student = CDOMElement::create('div','id:message_for_student');

        $exercise_submitted = CDOMElement::create('div','id:exercise_submitted');
        $exercise_submitted->addChild(new CText(translateFN('Hai inviato il seguente esercizio')));
        $message_for_student->addChild($exercise_submitted);

        $exercise_title = CDOMElement::create('div','id:exercise_title');
        //$label = CDOMElement::create('div','class:page_label');
        //$label->addChild(new CText(translateFN("Titolo dell'esercizio:")));
        $title = CDOMElement::create('div','id:title');
        $title->addChild(new CText($exercise->getTitle()));
        //$exercise_title->addChild($label);
        $exercise_title->addChild($title);
        $message_for_student->addChild($exercise_title);

        $exercise_question = CDOMElement::create('div','id:exercise_question');
        //$label = CDOMElement::create('div','class:page_label');
        //$label->addChild(new CText(translateFN("Testo dell'esercizio:")));
        $question = CDOMElement::create('div','id:question');
        $question->addChild(new CText($exercise->getText()));
        //$exercise_question->addChild($label);
        $exercise_question->addChild($question);
        $message_for_student->addChild($exercise_question);

        $student_answer = CDOMElement::create('div','id:student_answer');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('La tua risposta')));
        $answer = CDOMElement::create('div','id:answer');
        $answer->addChild(new CText($exercise->getStudentAnswer()));
        $student_answer->addChild($label);
        $student_answer->addChild($answer);
        $message_for_student->addChild($student_answer);

        return $message_for_student;
    }
}

class OpenAutomatic_ExerciseCorrection extends ExerciseCorrection {
    function rateStudentAnswer( $exercise, $student_answer, $id_student, $id_course_instance ) {
        $right_answer = array_pop($exercise->getExerciseData());
        $rating = 0;

        if ( strcmp(strtolower($student_answer), strtolower($right_answer['nome'])) == 0 ) {
            $rating = ADA_MAX_SCORE;
        }
        $exercise->setStudentAnswer($student_answer);
        $exercise->setRating($rating);
        parent::setStudentData($exercise, $id_student, $id_course_instance);
    }

    function getMessageForStudent( $username, $exercise ) {
        $interaction = $exercise->getExerciseInteraction();
        $message_for_student = CDOMElement::create('div','id:message_for_student');

        $exercise_submitted = CDOMElement::create('div','id:exercise_submitted');
        $exercise_submitted->addChild(new CText(translateFN('Esercizio inviato.')));
        $message_for_student->addChild($exercise_submitted);

        $exercise_title = CDOMElement::create('div','id:exercise_title');
        $title = CDOMElement::create('div','id:title');
        $title->addChild(new CText($exercise->getTitle()));
        $exercise_title->addChild($title);
        $message_for_student->addChild($exercise_title);

        $exercise_question = CDOMElement::create('div','id:exercise_question');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('Domanda')));
        $question = CDOMElement::create('div','id:question');
        $question->addChild(new CText($exercise->getText()));
        $exercise_question->addChild($label);
        $exercise_question->addChild($question);
        $message_for_student->addChild($exercise_question);

        $student_answer = CDOMElement::create('div','id:student_answer');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('La tua risposta')));
        $answer = CDOMElement::create('div','id:answer');
        $answer->addChild(new CText($exercise->getStudentAnswer()));
        $student_answer->addChild($label);
        $student_answer->addChild($answer);
        $message_for_student->addChild($student_answer);

        switch ($interaction) {
            case ADA_FEEDBACK_EXERCISE_INTERACTION: // with feedback
                $comment = CDOMElement::create('div','id:author_comment');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN("Il commento dell'autore:")));
                $comment->addChild($label);
                $comment->addChild(new CText($exercise->getAuthorComment()));
                $message_for_student->addChild($comment);
                break;

            case ADA_RATING_EXERCISE_INTERACTION: // with feedback and rating
                $exercise_rating = CDOMElement::create('div','id:exercise_rating');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN('Punteggio ottenuto:')));
                $rating = CDOMElement::create('div','id:rating');
                $rating->addChild(new CText($exercise->getRating()));
                $exercise_rating->addChild($label);
                $exercise_rating->addChild($rating);
                $message_for_student->addChild($exercise_rating);

                $comment = CDOMElement::create('div','id:author_comment');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN("Il commento dell'autore:")));
                $comment->addChild($label);
                $comment->addChild(new CText($exercise->getAuthorComment()));
                $message_for_student->addChild($comment);
                break;

            case ADA_BLIND_EXERCISE_INTERACTION: // no feedback
            default:
                break;
        }
        return $message_for_student;
    }
}

class Cloze_ExerciseCorrection extends ExerciseCorrection {
    var $author_comment;

    function rateStudentAnswer( $exercise, $student_answer, $id_student, $id_course_instance ) {
        $exercise_data  = $exercise->getExerciseData();

        $comment = CDOMElement::create('div');

        $tokenized_exercise_text = array();
        $tokenized_exercise_text = ExerciseUtils::tokenizeString($exercise->getText());

        $rating = 0;
        foreach( $exercise_data as $a ) {
            $posizione = $a['ordine'];
            if ( $posizione > 0 ) {
                if (strcmp( $a['nome'], $student_answer[$posizione] ) == 0) {
                    $rating += $a['correttezza'];

                    if ($a['correttezza'] == 0) {
                        $css_classname = 'wrong_answer';
                    }
                    else {
                        $css_classname = 'right_answer';
                    }
                    $comment_for_answer = CDOMElement::create('div',"class:$css_classname");
                    $comment_for_answer->addChild(new CText("{$a['nome']}: {$a['testo']}"));
                    $comment->addChild($comment_for_answer);
                }

                if (empty($student_answer[$posizione])) {
                    $student_answer[$posizione] = NO_ANSWER;
                }
                $tokenized_exercise_text[$posizione-1][0] = $student_answer[$posizione];
            }
        }
        /*
         * Set user answer.
        */
        $string = '';
        foreach ( $tokenized_exercise_text as $token ) {
            $string .= $token[0].$token[1];
        }

        $rating /= count($student_answer);

        $this->author_comment = $comment;

        $exercise->setStudentAnswer($string);
        $exercise->setRating($rating);
        parent::setStudentData($exercise, $id_student, $id_course_instance);
    }

    function getMessageForStudent( $username, $exercise ) {
        $interaction = $exercise->getExerciseInteraction();
        $message_for_student = CDOMElement::create('div','id:message_for_student');

        $exercise_submitted = CDOMElement::create('div','id:exercise_submitted');
        $exercise_submitted->addChild(new CText(translateFN('Esercizio inviato.')));
        $message_for_student->addChild($exercise_submitted);
        $exercise_question = CDOMElement::create('div','id:exercise_question');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('Domanda')));
        $question = CDOMElement::create('div','id:question');
        $question->addChild(Cloze_ExerciseViewer::formatExerciseText($exercise));
        $exercise_question->addChild($label);
        $exercise_question->addChild($question);
        $message_for_student->addChild($exercise_question);

        $student_answer = CDOMElement::create('div','id:student_answer');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('La tua risposta')));
        $answer = CDOMElement::create('div','id:answer');
        $answer->addChild(Cloze_ExerciseViewer::formatStudentAnswer($exercise));
        $student_answer->addChild($label);
        $student_answer->addChild($answer);
        $message_for_student->addChild($student_answer);

        switch ($interaction) {
            case ADA_FEEDBACK_EXERCISE_INTERACTION: // with feedback
                $comment = CDOMElement::create('div','id:author_comment');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN("Il commento dell'autore:")));
                $comment->addChild($label);
                $comment->addChild($this->author_comment);
                $message_for_student->addChild($comment);
                break;

            case ADA_RATING_EXERCISE_INTERACTION: // with feedback and rating
                $exercise_rating = CDOMElement::create('div','id:exercise_rating');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN('Punteggio ottenuto:')));
                $rating = CDOMElement::create('div','id:rating');
                $rating->addChild(new CText($exercise->getRating()));
                $exercise_rating->addChild($label);
                $exercise_rating->addChild($rating);
                $message_for_student->addChild($exercise_rating);

                $comment = CDOMElement::create('div','id:author_comment');
                $label = CDOMElement::create('div','class:page_label');
                $label->addChild(new CText(translateFN("Il commento dell'autore:")));
                $comment->addChild($label);
                $comment->addChild($this->author_comment);
                $message_for_student->addChild($comment);
                break;

            case ADA_BLIND_EXERCISE_INTERACTION: // no feedback
            default:
                break;
        }

        return $message_for_student;
    }
}

class OpenUpload_ExerciseCorrection extends ExerciseCorrection {
    function rateStudentAnswer( $exercise, $student_answer, $id_student, $id_course_instance ) {
        /*
         * upload del file
        */
        require_once ROOT_DIR . '/include/upload_funcs.inc.php';

        $file_uploaded = false;

        if ( $_FILES['file_up']['error'] == UPLOAD_ERR_OK ) {
            $filename          = $_FILES['file_up']['name'];
            $source            = $_FILES['file_up']['tmp_name'];

            $file_destination  = MEDIA_PATH_DEFAULT . $exercise->getAuthorId() . DIRECTORY_SEPARATOR;
            $file_destination .= $id_course_instance . "_" . $id_student . "_" . $exercise->getId() . "_";
            $file_destination .= $filename;

            $file_move = upload_file($_FILES, $source, ROOT_DIR . $file_destination);

            if ($file_move[0] == "ok") {
                $file_uploaded = true;
            }
        }

        /*
         * salvataggio della risposta studente
        */
        $exercise->setStudentAnswer($student_answer);
        if ( $file_uploaded ) {
            $replace = array(" " => "_","\'" => "_");
            $file_destination = strtr($file_destination, $replace);

            $exercise->setAttachment($file_destination);
        }
        $exercise->setRating(0);
        parent::setStudentData($exercise, $id_student, $id_course_instance);
    }

    function getMessageForStudent( $username, $exercise ) {
        $message_for_student = CDOMElement::create('div','id:message_for_student');

        $exercise_submitted = CDOMElement::create('div','id:exercise_submitted');
        $exercise_submitted->addChild(new CText(translateFN('Esercizio inviato.')));
        $message_for_student->addChild($exercise_submitted);

        $exercise_title = CDOMElement::create('div','id:exercise_title');
        $title = CDOMElement::create('div','id:title');
        $title->addChild(new CText($exercise->getTitle()));
        $exercise_title->addChild($title);
        $message_for_student->addChild($exercise_title);

        $exercise_question = CDOMElement::create('div','id:exercise_question');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('Domanda')));
        $question = CDOMElement::create('div','id:question');
        $question->addChild(new CText($exercise->getText()));
        $exercise_question->addChild($label);
        $exercise_question->addChild($question);
        $message_for_student->addChild($exercise_question);

        $student_answer = CDOMElement::create('div','id:student_answer');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('La tua risposta')));
        $answer = CDOMElement::create('div','id:answer');
        $answer->addChild(new CText($exercise->getStudentAnswer()));
        $student_answer->addChild($label);
        $student_answer->addChild($answer);
        $message_for_student->addChild($student_answer);

        $attached_file = CDOMElement::create('div','id:attached_file');
        $label = CDOMElement::create('div','class:page_label');
        $label->addChild(new CText(translateFN('Il file inviato')));
        $attachment = CDOMElement::create('div','id:attachment');
        $attachment->addChild(new CText($exercise->getAttachment()));
        $attached_file->addChild($label);
        $attached_file->addChild($attachment);
        $message_for_student->addChild($attached_file);

        return $message_for_student;
    }
}

class ExerciseUtils {
    function tokenizeString($string) {

        $data = array();
        $length = strlen($string);

        $current_char = NULL;
        $current_word = NULL;
        $current_stop = NULL;
        $word_count = 0;

        for ($i = 0; $i < $length; $i++) {

            $current_char = $string[$i];
            /* state 1 */
            if($current_char == ' ' || $current_char == ',' || $current_char == '.' || $current_char == ':'
				|| $current_char == ';'  || $current_char == '!'  || $current_char == '?' ) {
                if($i == 0) {
                    $state = 1;
                }
                if ($state == 0) {
                    if(!isset($data[$word_count])) {
                        $data[$word_count] = array(NULL,NULL);
                    }
                    $data[$word_count][0] = $current_word;
                    if($data[$word_count][1] !== NULL) {
                        $word_count++;
                    }
                    $current_word = NULL;

                    $state = 1;
                }
                $current_stop .= $current_char;
            }
            /*state 0*/
            else {
                if($i == 0) {
                    $state = 0;
                }
                if ($state == 1) {
                    if(!isset($data[$word_count])) {
                        $data[$word_count] = array(NULL,NULL);
                    }
                    $data[$word_count][1] = $current_stop;
                    $current_stop = NULL;
                    if($data[$word_count][0] !== NULL) {
                        $word_count++;
                    }

                    $state = 0;
                }
                $current_word .= $current_char;
            }
        }
        if ($i==$length) {
            if($current_word !== NULL) {
                $data[$word_count][0] = $current_word;
            }
            if($current_stop !== NULL) {
                $data[$word_count][1] = $current_stop;
            }
        }
        return $data;
    }
}
?>
