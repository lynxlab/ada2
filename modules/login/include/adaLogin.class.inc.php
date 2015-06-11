<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * ADA login provider implementation
 */
require_once(ROOT_DIR.'/modules/login/include/abstractLogin.class.inc.php');

class adaLogin extends AbstractLogin
{
	/**
	 * performs user login using the ADA db
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass)
	{
		return MultiPort::loginUser($name, $pass);
	}
	
	protected function render($returnHtml)
	{
		if (strlen($this->buttonLabel)>0) {
			$button = CDOMElement::create('button','type:button');
			$button->setAttribute('class', get_class($this).' login');
			$button->setAttribute('onclick', 'javascript:'.
					'$j(\'#selectedLoginProvider\').val(\''.get_class($this).'\');'.
					'$j(this).parents(\'form\').first().submit();');
			$button->addChild (new CText($this->buttonLabel));
			
			return (($returnHtml) ? $button->getHtml() : $button);			
		} else return null;
	}
}
