<?php
/*
 * widgets_inc.php
 * 
 * Copyright 2013 Lynx
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */
define ("ADA_WIDGET_ASYNC_MODE",0);
define ("ADA_WIDGET_SYNC_MODE",1);

define ("ADA_WIDGET_AJAX_ROOTDIR", ROOT_DIR . '/widgets/ajax');
define ("ADA_WIDGET_AJAX_HTTPDIR", HTTP_ROOT_DIR . '/widgets/ajax');


class Widget {
	
	var $templateField;
	var $generatedDIVId;
	var $ajaxModule;
	var $optionsArr;
	var $isActive;
	
	function __construct($widget) {
		
		$this->templateField = $widget ['field'];
		$this->generatedDIVId = $widget ['id'];
		$this->isActive = $widget['active'];
		$this->optionsArr = array();
		
		if (is_file ( ADA_WIDGET_AJAX_ROOTDIR . '/' . $widget ['module'] ))
			$this->ajaxModule = ADA_WIDGET_AJAX_HTTPDIR . '/' . $widget ['module'];
		else
			$this->ajaxModule = false;
		
		if (isset ($widget['param']) && !empty($widget['param'])){
			foreach ($widget['param'] as $paramElement)
			{
				$this->setParam($paramElement[ArrayToXML::attr_arr_string]['name'], $paramElement[ArrayToXML::attr_arr_string]['value']);
			}
		}
	}
	
	public function setParam ($name, $value)
	{
		$this->optionsArr[$name] = $value;
	}
	
	public function getWidget($mode) {

		switch ($mode) {
			case ADA_WIDGET_ASYNC_MODE :
			default :
				$widget_async_obj = new AjaxRemoteContent ( $this->generatedDIVId, $this->ajaxModule );
				$html_content = $widget_async_obj->getContent ();
				break;
			case ADA_WIDGET_SYNC_MODE :
				$widget_sync_content = include $this->ajaxModule;
				$html_content = $widget_sync_content;
				break;
		}
		return $html_content;
	}
}
?>
