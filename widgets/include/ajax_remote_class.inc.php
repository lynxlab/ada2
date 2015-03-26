<?php
/**
 * helper class for the widget: gets a remote content when
 * the widget is rendered in asynchronous mode
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
class AjaxRemoteContent {
	
	/**
	 * the generated content
	 *
	 * @var string
	 */
	private $content;
	
	/**
	 * placeholder for the content inside the generated div element
	 *
	 * @var string
	 */
	private static $placeholder = '[CONTENTPLACEHOLDER]';
	function __construct(Widget $widgetObj) {
		$content = "<div id='$widgetObj->generatedDIVId' class='ADAwidget loading'>" . self::$placeholder . "</div>";
		
		if ($widgetObj->ajaxModule) {
			$replacement = translateFN ( 'Loading' ) . '...';
			
			if (JQUERY_SUPPORT) {
				$ajax_content = "<script type='text/javascript'>\$j.get('$widgetObj->ajaxModule'";				
				if (!empty( $widgetObj->optionsArr )) $ajax_content .= ' ,' . json_encode ( $widgetObj->optionsArr );				
				$ajax_content .= ").done( function(html){ 				
				\$j('#$widgetObj->generatedDIVId').removeClass('loading');
				\$j('#$widgetObj->generatedDIVId').html(html);  } );</script>";
			} else {
				// prototype 1.6 version
				$ajax_content = "<script type='text/javascript'>
						new Ajax.Request('" . $widgetObj->ajaxModule . "', {
								method: 'get',";				
				if (! empty ( $widgetObj->optionsArr )) $ajax_content .= 'parameters: ' . json_encode ( $widgetObj->optionsArr ) . ',';				
				$ajax_content .= "  onComplete: function(response) {
						$('" . $widgetObj->generatedDIVId . "').removeClassName('loading');
						$('" . $widgetObj->generatedDIVId . "').update (response.responseText);
			  }
			});
			</script>";
			}
		} else {
			$replacement = translateFN ( 'widget content generator not found' );
			$ajax_content = '';
		}		
		$this->content = str_replace ( self::$placeholder, $replacement, $content ) . $ajax_content;
	}
	
	/**
	 * content getter
	 *
	 * @return string
	 */
	function getContent() {
		return $this->content;
	}
}
?>
