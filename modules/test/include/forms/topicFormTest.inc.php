<?php
/**
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

class TopicFormTest extends FormTest {

	protected $id_test;
	protected $id_nodo_parent;

	public function __construct($id_test,$data=array(),$id_nodo_parent=null) {
		$this->id_test = $id_test;
		$this->id_nodo_parent = $id_nodo_parent;
		
		/**
		 * @author giorgio 09/ott/2013
		 * split testo into consegna(aka testo), didascalia and stimolo (if any)
		 */
		$splittedAr = self::extractFieldsFromTesto(Node::prepareInternalLinkMediaForEditor($data['testo']), array('stimolo-field','didascalia-field'));
		foreach ($splittedAr as $key=>$value) $data[preg_replace('/[-]\w+/', '', $key)]=$value;
		
		parent::__construct($data);
	}
	
	/**
	 * @author giorgio 09/ott/2013
	 * 
	 *  testo holds the html for itself, stimolo and didascalia all in one database field.
	 *  The html for stimolo has to be inside a div having a class='stimolo' attribute and
	 *  the html for didascalia has to be inside a div having a class='didascalia' attribute.
	 *  E.g.
	 *  <html>  <!-- or whatever tag is there -->
	 *  	This is testo div holding everything that's displayed in the topic description
	 *  	<div class='stimolo'>Hey! I'm the stimolo</div>
	 *  	<div class='didascalia'>Hallo there! Here's didascalia</div>
	 *  </html> <!-- or whatever tag was up above opening this one -->
	 *  
	 *  This (glueing the 3 textarea together) is done automatically when saving the topic
	 *  and therefore the html stored in the database field MUST be splitted when loading the form.
	 */
	public static function extractFieldsFromTesto ($testo,$whatElements)
	{
		$newtext = '';
		$retval = array();
	
		$source = new DOMDocument ( '1.0', ADA_CHARSET );
		/**
		 * suppress warnings, It's known that most probably the html shall be invalid
		 */
		@$source->loadHTML ( mb_convert_encoding($testo, 'HTML-ENTITIES', ADA_CHARSET) );
		$path = new DOMXpath ( $source );
		
		foreach ($whatElements as $what)
		{
			$dom = $path->query ( "*/div[@class='" . $what . "']" );
			if (intval ( $dom->length ) > 0) {
				$innerHTML = '';
				/**
				 * take the first (one and only, hopefully!) element found
				 * if by any chance there are more than one, it's a user mistake
				 * that inserted two div with same class (for instance 'stimolo')
				 */
				$divelement = $dom->item ( 0 );
				$children = $divelement->childNodes;
				// save the innerHTML of the div that's being extracted
				foreach ( $children as $child ) {
					$innerHTML .= $divelement->ownerDocument->saveHTML ( $child );
				}
				$retval [preg_replace('/[-]\w+/', '', $what)] = $innerHTML;
				// remove the extracted div from the passed html
				$divelement->parentNode->removeChild ( $divelement );
			}
		}
		
		if (is_object($source->getElementsByTagName ( 'body' )->item ( 0 )) && 
			$source->getElementsByTagName ( 'body' )->item ( 0 )->hasChildNodes()) {
			// generate the new html to be used as 'testo'
			foreach ( $source->getElementsByTagName ( 'body' )->item ( 0 )->childNodes as $child ) {
				$newtext .= $source->saveXML ( $child );
			}
		}
		$retval['testo'] = $newtext;
		return $retval;		 
	}
	

	protected function content() {
		$dh = $GLOBALS['dh'];

		$this->setName('topicForm');

		$random = 'random';
		$random_number = 'random_number';

		$js = 'var random_field = "'.$random.'";
			var field = "'.$random_number.'";
			var regexp = /^[0-9]+$/;
			var module_http = "'.MODULES_TEST_HTTP.'";
			document.write(\'<script type="text/javascript" src="'.MODULES_TEST_HTTP.'/js/topicForm.js"><\/script>\');';
		$this->setCustomJavascript($js);

		//parent
		$nodes = $dh->test_getNodesByParent($this->id_test,$this->id_test);
		$options = array(
			$this->id_test => $nodes[$this->id_test]['titolo'].' ('.$nodes[$this->id_test]['nome'].')',
		);
		foreach($nodes as $id_nodo=>$v) {
			if ($id_nodo != $this->id_test) {
				$options[$id_nodo] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; '.$v['titolo'].' ('.$v['nome'].')';
			}
		}
		if (isset($this->data['id_nodo_parent'])) {
			$defaultValue = $this->data['id_nodo_parent'];
		}
		else {
			if (is_null($this->id_nodo_parent)) {
				$defaultValue = $this->id_test;
			}
			else {
				$defaultValue = $this->id_nodo_parent;
			}
		}
        $this->addSelect('id_nodo_parent',translateFN('Aggancia a').':',$options,$defaultValue);

		//nome
		if (!empty($this->data['nome'])) {
			$defaultValue = $this->data['nome'];
		}
		else {
			if (is_null($this->id_nodo_parent)) {
				$defaultValue = translateFN('sessione').' ';
			}
			else {
				$defaultValue = translateFN('argomento').' ';
			}
			$res = $dh->test_getNodesByRadix($this->id_test);
			if ($dh->isError($res) || empty($res)) {
				$defaultValue.= 1;
			}
			else {
				foreach($res as $k=>$v) {
					if ($v['tipo']{0} != ADA_GROUP_TOPIC) {
						unset($res[$k]);
					}
				}
				$defaultValue.= count($res)+1;
			}
		}
        $this->addTextInput('nome', translateFN('Nome (per uso interno)').':')
             ->setRequired()
             ->setValidator(FormValidator::NOT_EMPTY_STRING_VALIDATOR)
             ->withData($defaultValue);

		//titolo
        $this->addTextInput('titolo', translateFN('Titolo').':')
             ->withData($this->data['titolo']);

		//descrizione
        $this->addTextarea('testo', translateFN('Consegna').':')
             ->withData(Node::prepareInternalLinkMediaForEditor($this->data['testo']));
        //didascalia
        $this->addTextarea('didascalia-field', translateFN('Didascalia').':')
             ->withData(Node::prepareInternalLinkMediaForEditor($this->data['didascalia']));
        //stimolo
        $this->addTextarea('stimolo-field', translateFN('Stimolo').':')
        	 ->withData(Node::prepareInternalLinkMediaForEditor($this->data['stimolo']));

		//durata
		if (false && !is_null($this->id_nodo_parent)) {
			$this->addHidden('durata')->withData(0);
		}
		else {
			if (isset($this->data['durata'])) {
				$defaultValue = $this->data['durata'];
			}
			else {
				$defaultValue = 0;
			}
			$this->addTextInput('durata', translateFN('Tempo limite (in minuti, 0 = senza limite)').': ')
				 ->setRequired()
				 ->setValidator(FormValidator::NON_NEGATIVE_NUMBER_VALIDATOR)
				 ->withData($defaultValue);
		}

		//random questions
		$radios = array(
			ADA_PICK_QUESTIONS_NORMAL => translateFN('No'),
			ADA_PICK_QUESTIONS_RANDOM => translateFN('Si'),
		);
		if (isset($this->data[$random])) {
			$defaultValue = $this->data[$random];
		}
		else {
			$defaultValue = ADA_PICK_QUESTIONS_NORMAL;
		}

		if (is_null($this->id_nodo_parent)) {
			$randomTranslation = translateFN('Scelta casuale degli argomenti');
		}
		else {
			$randomTranslation = translateFN('Scelta casuale delle domande');
		}
		$this->addRadios($random,$randomTranslation.':',$radios,$defaultValue);

		//how many random questions
		if (is_null($this->id_nodo_parent)) {
			$label = translateFN('Numero di argomenti da mostrare');
		}
		else {
			$label = translateFN('Numero di domande da mostrare');
		}
        $num = $this->addTextInput($random_number, $label.':')
					->setValidator(FormValidator::POSITIVE_NUMBER_VALIDATOR)
					->withData($this->data[$random_number]);
		if (isset($this->data[$random]) && $this->data[$random] == ADA_PICK_QUESTIONS_RANDOM) {
			$num->setRequired();
		}
    }
}
