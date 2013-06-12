<?php
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
require_once ROOT_DIR.'/include/HtmlLibrary/FormElementCreator.inc.php';

class ServicesModuleHtmlLib {
    static public function getAddExerciseForm($form_dataAr = array(), $errorsAr = array()) {
        $form = CDOMElement::create('form','id:add_exercise, name:add_exercise, class:fec, method:post');
        $form->setAttribute('action', 'add_exercise.php?verify=1');
        //$form->setAttribute('onsubmit',"return checkNec();");
        $form->setAttribute('enctype','multipart/form-data');
        $parent_node = FormElementCreator::addTextInput('parent_node','Nodo parent',$form_dataAr, $errorsAr,'',true);
        $form->addChild($parent_node);

        $exercise_title = FormElementCreator::addTextInput('exercise_title','Titolo esercizio',$form_dataAr, $errorsAr,'',true);
        $form->addChild($exercise_title);

        $exercise_familyAr = array (
                ADA_STANDARD_EXERCISE_TYPE       => translateFN('Multiple Choice'),
                ADA_OPEN_MANUAL_EXERCISE_TYPE    => translateFN('Open with Manual Correction'),
                ADA_OPEN_AUTOMATIC_EXERCISE_TYPE => translateFN('Open with Automatic Correction'),
                ADA_OPEN_UPLOAD_EXERCISE_TYPE    => translateFN('Open Manual + Upload'),
                ADA_CLOZE_EXERCISE_TYPE          => translateFN('CLOZE')
        );
        $exercise_family = FormElementCreator::addSelect('exercise_family','Tipo di esercizio',$exercise_familyAr,$form_dataAr,$errorsAr,'',true);
        $form->addChild($exercise_family);

        $exercise_interactionAr = array (
                ADA_BLIND_EXERCISE_INTERACTION    => translateFN('No Feedback'),
                ADA_FEEDBACK_EXERCISE_INTERACTION => translateFN('With Feedback'),
                ADA_RATING_EXERCISE_INTERACTION   => translateFN('With Feedback and Rating')
        );
        $exercise_interaction = FormElementCreator::addSelect('exercise_interaction','Tipo di interazione',$exercise_interactionAr,$form_dataAr,$errorsAr,'',true);
        $form->addChild($exercise_interaction);

        $test_modeAr = array (
                ADA_SINGLE_EXERCISE_MODE   => translateFN('Only One Exercise'),
                ADA_SEQUENCE_EXERCISE_MODE => translateFN('Next Exercise will be Shown'),
                ADA_RANDOM_EXERCISE_MODE   => translateFN('A Random Picked Exercise will be Shown')
        );
        $test_mode = FormElementCreator::addSelect('test_mode','Modalità di esecuzione',$test_modeAr,$form_dataAr,$errorsAr,'',true);
        $form->addChild($test_mode);

        $test_simplificationAr = array (
                ADA_NORMAL_EXERCISE_SIMPLICITY   => translateFN('Normal Exercise'),
                ADA_MEDIUM_EXERCISE_SIMPLICITY   => translateFN('Medium Exercise'),
                ADA_SIMPLIFY_EXERCISE_SIMPLICITY => translateFN('Simplified Exercise')
        );
        $test_simplification = FormElementCreator::addSelect('test_simplification',"Semplicità dell'esercizio",$test_simplificationAr,$form_dataAr,$errorsAr,'',true);
        $form->addChild($test_simplification);

        $test_barrierAr = array (
                ADA_NO_EXERCISE_BARRIER  => translateFN('No barrier'),
                ADA_YES_EXERCISE_BARRIER => translateFN('With barrier')
        );
        $test_barrier = FormElementCreator::addSelect('test_barrier','Con sbarramento',$test_barrierAr,$form_dataAr,$errorsAr,'',true);
        $form->addChild($test_barrier);

        $buttons = FormElementCreator::addSubmitAndResetButtons();
        $form->addChild($buttons);

        return $form;
    }
}
?>