<?php
/**
 * @package test
 * @author	Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */

class QuestionOpenUploadTest extends QuestionTest
{
	/**
	 * used to configure object with database's data options
	 *
	 * @access protected
	 *
	 */
	protected function configureProperties() {
		if (!parent::configureProperties()) {
			return false;
		}
		return true;
	}

	/**
	 * return necessaries html objects that represent the object
	 *
	 * @access protected
	 *
	 * @param $ref reference to the object that will contain this rendered object
	 * @param $feedback "show feedback" flag on rendering
	 * @param $rating "show rating" flag on rendering
	 * @param $rating_answer "show correct answer" on rendering
	 *
	 * @return an object of CDOMElement
	 */
	protected function renderingHtml(&$ref = null,$feedback=false,$rating=false,$rating_answer=false) {
		if (!$this->display) return new CText(''); //if we don't have to display this question, let's return an empty item
		$out = parent::renderingHtml($ref,$feedback,$rating,$rating_answer);

		$name = $this->getPostFieldName();
		$post_data = $this->getPostData();

		$li = CDOMElement::create('li','class:answer_open_test');

        $div_file = CDOMElement::create('div');
		$div_file->setAttribute('class','file_upload_test');
		$li->addChild($div_file);

		if ($feedback) {
			$file_url = str_replace(ROOT_DIR,HTTP_ROOT_DIR,$this->givenAnswer['allegato']);
			$a_img = CDOMElement::create('a');
			$a_img->setAttribute('href',$file_url);
			$a_img->setAttribute('target','_blank');

			$img = CDOMElement::create('img');
			$img->setAttribute('src',$file_url);
			$img->setAttribute('class','open_answer_test_image');
			$a_img->addChild($img);
			$div_file->addChild($a_img);
		}
		else {
			$label_file = CDOMElement::create('label');
			$label_file->setAttribute('for','file_answer_test');
			$label_file->addChild(new CText(translateFN('File').':'));
			$div_file->addChild($label_file);

			$file = CDOMElement::create('file');
			$file->setAttribute('id', 'file_answer_test');
			$file->setAttribute('name', $name.'['.self::POST_ATTACHMENT_VAR.']');
			$div_file->addChild($file);			
		}

		$textArea = CDOMElement::create('textarea');
		$textArea->setAttribute('name',$name.'['.self::POST_ANSWER_VAR.']');
		$textArea->setAttribute('class','open_answer_test');
		$li->addChild($textArea);
		
		if ($feedback) {
			$textArea->addChild(new CText($this->givenAnswer['risposta'][self::POST_ANSWER_VAR]));
			$textArea->setAttribute('disabled','');

			if ($_SESSION['sess_id_user_type'] == AMA_TYPE_TUTOR) {
				$textAreaCorrect = CDOMElement::create('textarea','id:open_answer_test_point_textarea_'.$this->givenAnswer['id_answer']);
				$textAreaCorrect->setAttribute('class', 'open_answer_test fright');
				$textAreaCorrect->addChild(new CText($this->givenAnswer['correzione_risposta']));
				$li->addChild($textAreaCorrect);

				$button = CDOMElement::create('input_button');
				$button->setAttribute('class', 'test_button fright');
				$button->setAttribute('onclick','saveCorrectOpenAnswer('.$this->givenAnswer['id_answer'].');');
				$button->setAttribute('value',translateFN('Salva correzione risposta'));
				$li->addChild($button);

				$punti = $this->givenAnswer['punteggio'];

				$input = CDOMElement::create('text','id:open_answer_test_point_input_'.$this->givenAnswer['id_answer']);
				$input->setAttribute('size',4);
				$input->setAttribute('maxlength',4);
				$input->setAttribute('value',$punti);

				$button = CDOMElement::create('input_button','class:test_button');
				$button->setAttribute('onclick','saveOpenAnswerPoints('.$this->givenAnswer['id_answer'].');');
				$button->setAttribute('value',translateFN('Assegna punti'));

				$span = CDOMElement::create('span','id:open_answer_test_point_span_'.$this->givenAnswer['id_answer']);

				if (is_null($punti)) {
					$punti = translateFN('Nessun punteggio assegnato');
				}
				$span->addChild(new CText($punti));

				$div = CDOMElement::create('div','class:open_answer_test_point');
				$div->addChild($input);
				$div->addChild($button);
				$div->addChild(new CText('&nbsp;&nbsp;&nbsp;'.translateFN('Punti già assegnati').': '));
				$div->addChild($span);
				$li->addChild($div);
			}
			else if (!empty($this->givenAnswer['correzione_risposta'])) {
				$divCorrectAnswer = CDOMElement::create('div','id:open_answer_test_point_textarea_'.$this->givenAnswer['id_answer']);
				$divCorrectAnswer->setAttribute('class', 'open_answer_test fright');
				$divCorrectAnswer->addChild(new CText('<b>'.translateFN('Risposta corretta:').'</b> '.$this->givenAnswer['correzione_risposta']));
				$li->addChild($divCorrectAnswer);
			}
		}

		if (!empty($post_data[self::POST_ANSWER_VAR])) {
			$textArea->addChild(new CText($post_data[self::POST_ANSWER_VAR]));
		}		

		$ref->addChild($li);

		return $out;
	}

	/**
	 * implementation of exerciseCorrection for OpenUpload question type
	 *
	 * @access public
	 *
	 * @return a value representing the points earned or an array containing points and attachment elements
	 */
	public function exerciseCorrection($data) {
		$topic_id = $this->_parent->id_nodo;
		$question_id = $this->id_nodo;
        /*
         * upload del file
        */
        require_once ROOT_DIR . '/include/upload_funcs.inc.php';

        $file_uploaded = false;

        if ( $_FILES[self::POST_TOPIC_VAR]['error'][$topic_id][$question_id] == UPLOAD_ERR_OK ) {
            $filename          = $_FILES[self::POST_TOPIC_VAR]['name'][$topic_id][$question_id];
            $source            = $_FILES[self::POST_TOPIC_VAR]['tmp_name'][$topic_id][$question_id];

			$file_destination = $this->getFilePath($this->id_utente, $this->id_istanza, $_SESSION['sess_id_user'], $this->id_nodo, $filename);

            $file_move = upload_file($_FILES, $source, ROOT_DIR . $file_destination);

            if ($file_move[0] == "ok") {
				$replace = array(" " => "_","\'" => "_");
				$file_destination = strtr($file_destination, $replace);
            }
			else {
				$file_destination = null;
			}
        }

		//manual correction: no points gained
		return array('points'=>null, 'attachment'=>$file_destination);
	}

	/**
	 * return exercise max score
	 *
	 * @access public
	 *
	 * @return a value representing the max score
	 */
	public function getMaxScore() {
		return $this->correttezza;
	}

	/**
	 * generate uploaded file path
	 *
	 * @param int $id_author author id
	 * @param int $id_instance course instance id
	 * @param int $id_student student id
	 * @param int $id_nodo node id
	 * @param string $filename filename
	 * @param boolean $http if true returns http file path, otherwise hard disk file path
	 *
	 * @return string
	 */
	private function getFilePath($id_author, $id_instance, $id_student, $id_nodo, $filename, $http = false) {
		if ($http) {
			$base = HTTP_ROOT_DIR;
		}
		else {
			$base = ROOT_DIR;
		}

		return $base.MEDIA_PATH_DEFAULT.$id_author.DIRECTORY_SEPARATOR.$id_instance.'_'.$id_student.'_'.$id_nodo.'_'.$filename;
	}
}
