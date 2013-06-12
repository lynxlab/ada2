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

class TemplateEditorControlTest extends FormControl {
	protected $label;
	protected $id;
	protected $files = array();

	/**
	 * Template Editor control Constructor
	 *
	 * @param string $id html tag id
	 * @param string $label field label
	 */
	public function __construct($id,$label) {
		$this->id = $id;
		$this->label = $label;

		$dir = MODULES_TEST_PATH.'/template/';
		$toBeReplaced = array($dir,'.html','.HTML','.htm','.HTM');
		$files = glob($dir.'*.html');
		if (!empty($files)) {
			foreach($files as $k=>$v) {
				$key = str_replace($toBeReplaced,'',$v);
				$this->files[$key] = file_get_contents($v);
			}
		}
    }

	/**
	 * Control rendering
	 *
	 * @return string
	 */
    public function render() {
		$select = CDOMElement::create('select','id:'.$this->id.',class:form');

		if (empty($this->files)) {
			$option = CDOMElement::create('option','value:template_clear');
			$option->addChild(new CText(translateFN('Nessun template trovato')));
			$select->addChild($option);
		}
		else {
			$option = CDOMElement::create('option');
			$option->setAttribute('value','');
			$option->addChild(new CText(translateFN('Vuoto')));
			$select->addChild($option);

			$i=0;
			foreach($this->files as $k=>$v) {
				$option = CDOMElement::create('option');
				$option->setAttribute('value', htmlspecialchars($v));
				$option->addChild(new CText($k));
				$select->addChild($option);
				$i++;
			}
		}

		$button = CDOMElement::create('input_button');
		$button->setAttribute('id', $this->id.'_button');
		$button->setAttribute('value', translateFN('Inserisci template'));
		$button->setAttribute('style', 'float:right;');

		$html = '<label id="l_'.$this->id.'" for="'.$this->id.'" class="'.self::DEFAULT_CLASS.'">'.$this->label.'</label> '.$button->getHtml().' '.$select->getHtml();

        return $html;
    }
}