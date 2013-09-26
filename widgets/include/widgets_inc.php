<?php
/*
 * widgets_inc.php Copyright 2013 Lynx This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */
define ( "ADA_WIDGET_ASYNC_MODE", 1 );
define ( "ADA_WIDGET_SYNC_MODE", 0 );

define ( "ADA_WIDGET_AJAX_ROOTDIR", ROOT_DIR . '/widgets/ajax' );
define ( "ADA_WIDGET_AJAX_HTTPDIR", HTTP_ROOT_DIR . '/widgets/ajax' );

define ( 'JQUERY_SUPPORT', true );

/**
 * class representation of a widget
 *
 * @package widget
 * @author Stefano Penge <steve@lynxlab.com>
 * @author giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2013, Lynx s.r.l.
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link widget
 * @version 0.1
 *         
 */
class Widget {
	
	/**
	 * template field name where widget contest must appear on the page.
	 * Must mactch one of the template_fields name in the .tpl file, or
	 * else nothing will be rendered
	 *
	 * @var string
	 */
	var $templateField;
	
	/**
	 * id of the generated div holding the content, for prototype or jquery
	 * handling and css styling.
	 *
	 * @var string
	 */
	var $generatedDIVId;
	
	/**
	 * filename of the script (relative to ROOT_DIR/widgets/ajax) that is
	 * responsible for generating widget's content
	 *
	 * @var string
	 */
	var $ajaxModule;
	
	/**
	 * array of parameters to be passed as a GET query string to the ajaxModule
	 *
	 * @var string
	 */
	var $optionsArr;
	
	/**
	 * true if widget is active, else false.
	 * Defaults to true, can be set in the XML
	 *
	 * @var boolean
	 */
	var $isActive;
	
	/**
	 * tells if widget is to be called in sync or async mode.
	 * Defaults to ADA_WIDGET_ASYNC_MODE, can be set in the XML
	 *
	 * @var numeric
	 */
	var $asyncMode;
	
	function __construct($widget) {
		$this->templateField = $widget ['field'];
		$this->generatedDIVId = $widget ['id'];
		$this->isActive = isset ( $widget ['active'] ) ? $widget ['active'] : 1;
		$this->asyncMode = isset ( $widget ['async'] ) ? $widget ['async'] : ADA_WIDGET_ASYNC_MODE;
		$this->optionsArr = array ();
		
		if (is_file ( ADA_WIDGET_AJAX_ROOTDIR . '/' . $widget ['module'] )) {
			if ($this->asyncMode == ADA_WIDGET_ASYNC_MODE)
				$this->ajaxModule = ADA_WIDGET_AJAX_HTTPDIR . '/' . $widget ['module'];
			else if ($this->asyncMode == ADA_WIDGET_SYNC_MODE)
				$this->ajaxModule = ADA_WIDGET_AJAX_ROOTDIR . '/' . $widget ['module'];
		} else
			$this->ajaxModule = false;
		
		if (isset ( $widget ['param'] ) && ! empty ( $widget ['param'] )) {
			foreach ( $widget ['param'] as $paramElement ) {
				if (isset ( $paramElement [ArrayToXML::attr_arr_string] ))
					$curEl = $paramElement [ArrayToXML::attr_arr_string];
				else
					$curEl = $paramElement;
				$this->setParam ( $curEl ['name'], $curEl ['value'] );
			}
		}
	}
	
	/**
	 * sets a param for the widget
	 * 
	 * @param string $name	name of the param to be set
	 * @param string $value	value of the param to be set
	 */
	public function setParam($name, $value) {
		$this->optionsArr [$name] = $value;
	}
	
	/**
	 * gets (aka render) the widget
	 *
	 * @return string
	 */
	public function getWidget() {
		if (! $this->isActive) return '';
		
		$this->setParam ( "widgetMode", $this->asyncMode );
		
		switch ($this->asyncMode) {
			case ADA_WIDGET_ASYNC_MODE :
			default :
				$widget_async_obj = new AjaxRemoteContent ( $this );
				$html_content = $widget_async_obj->getContent ();
				break;
			case ADA_WIDGET_SYNC_MODE :
				extract ( $this->optionsArr );
				$widget_sync_content = include $this->ajaxModule;
				$html_content = $widget_sync_content;
				break;
		}
		return $html_content;
	}
}
?>
