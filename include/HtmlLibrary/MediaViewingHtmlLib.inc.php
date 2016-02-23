<?php
/**
 * MediaViewingHtmlLib.inc.php
 *
 * @package        MediaViewingHtmlLib.inc
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           MediaViewingHtmlLib.inc
 * @version		   0.1
 */

class MediaViewingHtmlLib {

	private static $count=0;

	private static function getNextDivID() {
		return (isset($_REQUEST['isAjax']) ? 'ajax_' : '') . ++self::$count;
	}

	private static function jplayerCommonJS($format, $divID, $url, $title=null, $width=null, $height=null) {
		$jplayerCode = '<script type="text/javascript">
						var jplayerNoConflict;

						$j(document).ready(function(){
				    		jplayerNoConflict = {
    							jQuery: $j
							};

							$j("#jquery_jplayer_'.$divID.'").jPlayer({
								ready: function () {
									$j(this).jPlayer("setMedia", {';
		if (isset($title) && strlen ($title)>0)  $jplayerCode .= 'title: "'.$title.'",';
		/**
		 * format is checked against string 'never' to never display a poster
		 * the following line is here just in case you need to enable a poster
		 */
		if ($format=='never') $jplayerCode .= 'poster: "'.HTTP_ROOT_DIR.'/layout/"+ADA_TEMPLATE_FAMILY+"/img/header-logo.png",';
		$jplayerCode .= $format.': "'.$url.'"
									});
								},
								play: function() {
									$j(this).jPlayer("pauseOthers");
								},
				    			size: {
                         			width: "'.$width.'px",
                         			height: "'.$height.'px"
                    			},';
		if ($format=='m4v') $jplayerCode .= 'solution: "html, flash",';
		$jplayerCode .='		noConflict: "jplayerNoConflict.jQuery",
								swfPath: "'.HTTP_ROOT_DIR.'/js/include/jquery/jplayer",
								supplied: "'.$format.'",
								cssSelectorAncestor: "#jp_container_'.$divID.'",
								wmode: "window",
								smoothPlayBar: true,
								keyEnabled: true,
								remainingDuration: true,
								toggleDuration: true,
								useStateClassSkin: true
							});
						});
					</script>';

		return $jplayerCode;
	}

	public static function jplayerMp4Viewer($url, $title=null, $width=null, $height=null) {

		$divID = self::getNextDivID();
		$isAudio = false;

		$jpContainer  = self::buildJPElement('container',$isAudio, $divID);
		$jpContainer->setAttribute('style', 'width :'.$width.'px');

		$jpTypeSingle = self::buildJPElement('single');
		$jpTypeSingle->addChild(self::buildJPElement('jplayer',  $isAudio, $divID));

		$jpGUI = self::buildJPElement('gui',$isAudio);
		$jpInterface = self::buildJPElement('interface', $isAudio);

		$jpInterface->addChild(self::buildJPElement('progress'));
		$jpInterface->addChild(self::buildJPElement('current-time'));
		$jpInterface->addChild(self::buildJPElement('duration'));
		$jpInterface->addChild(self::buildJPElement('controls-holder', $isAudio));
		$jpInterface->addChild(self::buildJPElement('details'));

		$jpGUI->addChild($jpInterface);
		$jpTypeSingle->addChild($jpGUI);
		$jpTypeSingle->addChild(self::buildJPElement('nosolution'));

 		$jpContainer->addChild($jpTypeSingle);

		return self::jplayerCommonJS('m4v', $divID, $url, $title, $width, $height) . $jpContainer->getHtml();
	}

	public static function jplayerMp3Viewer ($url, $title=null) {

		$divID = self::getNextDivID();
		$isAudio = true;

		$jpPlayer     = self::buildJPElement('jplayer',  $isAudio, $divID);
		$jpContainer  = self::buildJPElement('container',$isAudio, $divID);
		$jpTypeSingle = self::buildJPElement('single');
		$jpInterface  = self::buildJPElement('interface');

		$jpInterface->addChild(self::buildJPElement('controls'));
		$jpInterface->addChild(self::buildJPElement('progress'));
		$jpInterface->addChild(self::buildJPElement('volume'));
		$jpInterface->addChild(self::buildJPElement('time'));

		$jpTypeSingle->addChild($jpInterface);
		$jpTypeSingle->addChild(self::buildJPElement('details'));
		$jpTypeSingle->addChild(self::buildJPElement('nosolution'));

		$jpContainer->addChild($jpTypeSingle);

		return self::jplayerCommonJS('mp3', $divID, $url, $title) . $jpPlayer->getHtml()  . $jpContainer->getHtml();
	}

	private static function buildJPElement ($type, $isAudio=true, $divID=null) {
		switch ($type) {
			case 'jplayer':
					return CDOMElement::create('div','id:jquery_jplayer_'.$divID.',class:jp-jplayer');
				break;
			case 'container':
					$jpContainer = CDOMElement::create('div','id:jp_container_'.$divID.',class:jp-'.(($isAudio) ? 'audio' : 'video'));
					$jpContainer->setAttribute('role', 'application');
					$jpContainer->setAttribute('aria-label', 'media player');

					return $jpContainer;
				break;
			case 'single':
				return CDOMElement::create('div','class:jp-type-single');
				break;
			case 'gui':
				if ($isAudio) return null;
				else {
					$outer = CDOMElement::create('div','id:outer-video-play-icon,class:jp-video-play');
					$middle = CDOMElement::create('div','id:middle-video-play-icon');
					$playButton = CDOMElement::create('button','class:jp-video-play-icon');
					$playButton->setAttribute('role', 'button');
					$playButton->setAttribute('tabindex', '0');
					$playButton->addChild(new CText(translateFN('Avvia')));
					$middle->addChild($playButton);
					$outer->addChild($middle);
					$gui = CDOMElement::create('div','class:jp-gui');
					$gui->addChild($outer);

					return $gui;
				}
				break;
			case 'interface':
				return CDOMElement::create('div','class:'.(($isAudio) ? 'jp-gui ' : '').'jp-interface');
				break;
			case 'controls-holder':
					$jpControlsHolder = CDOMElement::create('div','class:jp-controls-holder');
					$jpControlsHolder->addChild(self::buildJPElement('controls'));
					$jpControlsHolder->addChild(self::buildJPElement('volume'));
					$jpControlsHolder->addChild(self::buildJPElement('toggles', $isAudio));

					return $jpControlsHolder;
				break;
			case 'controls':
					$jpControls = CDOMElement::create('div','class:jp-controls');

					$playButton = CDOMElement::create('button','class:jp-play');
					$playButton->setAttribute('role', 'button');
					$playButton->setAttribute('tabindex', '0');
					$playButton->addChild(new CText(translateFN('Avvia')));
					$jpControls->addChild($playButton);

					$stopButton = CDOMElement::create('button','class:jp-stop');
					$stopButton->setAttribute('role', 'button');
					$stopButton->setAttribute('tabindex', '0');
					$stopButton->addChild(new CText(translateFN('Arresta')));
					$jpControls->addChild($stopButton);

					return $jpControls;
				break;
			case 'progress':
					$jpProgress = CDOMElement::create('div','class:jp-progress');
					$jpSeekBar = CDOMElement::create('div','class:jp-seek-bar');
					$jpSeekBar->addChild(CDOMElement::create('div','class:jp-play-bar'));
					$jpProgress->addChild($jpSeekBar);

					return $jpProgress;
				break;
			case 'volume':
					$jpVolumeControls = CDOMElement::create('div','class:jp-volume-controls');
					$muteButton = CDOMElement::create('button','class:jp-mute');
					$muteButton->setAttribute('role', 'button');
					$muteButton->setAttribute('tabindex', '0');
					$muteButton->addChild(new CText(translateFN('Silenzia')));
					$jpVolumeControls->addChild($muteButton);

					$maxButton = CDOMElement::create('button','class:jp-volume-max');
					$maxButton->setAttribute('role', 'button');
					$maxButton->setAttribute('tabindex', '0');
					$maxButton->addChild(new CText(translateFN('Massimo')));
					$jpVolumeControls->addChild($maxButton);

					$jpVolumeBar = CDOMElement::create('div','class:jp-volume-bar');
					$jpVolumeBar->addChild(CDOMElement::create('div','class:jp-volume-bar-value'));
					$jpVolumeControls->addChild($jpVolumeBar);

					return $jpVolumeControls;
				break;
			case 'current-time' :
					$jpCurrentTime = CDOMElement::create('div','class:jp-current-time');
					$jpCurrentTime->setAttribute('role', 'timer');
					$jpCurrentTime->setAttribute('aria-label', 'time');
					$jpCurrentTime->addChild(new CText('&nbsp;'));
					return $jpCurrentTime;
				break;
			case 'duration':
					$jpDuration = CDOMElement::create('div','class:jp-duration');
					$jpDuration->setAttribute('role', 'timer');
					$jpDuration->setAttribute('aria-label', 'duration');
					$jpDuration->addChild(new CText('&nbsp;'));
					return $jpDuration;
				break;
			case 'toggles':
					$jpToggles = CDOMElement::create('div','class:jp-toggles');
					$repeatButton = CDOMElement::create('button','class:jp-repeat');
					$repeatButton->setAttribute('role', 'button');
					$repeatButton->setAttribute('tabindex', '0');
					$repeatButton->addChild(new CText(translateFN('Ripeti')));
					$jpToggles->addChild($repeatButton);
					if (!$isAudio) {
						$fullscreenButton = CDOMElement::create('button','class:jp-full-screen');
						$fullscreenButton->setAttribute('role', 'button');
						$fullscreenButton->setAttribute('tabindex', '0');
						$fullscreenButton->addChild(new CText(translateFN('Schermo Intero')));
						$jpToggles->addChild($fullscreenButton);
					}

					return $jpToggles;
				break;
			case 'time':
					$jpTimeHolder = CDOMElement::create('div','class:jp-time-holder');
					$jpTimeHolder->addChild(self::buildJPElement('current-time'));
					$jpTimeHolder->addChild(self::buildJPElement('duration'));
					$jpTimeHolder->addChild(self::buildJPElement('toggles'));

					return $jpTimeHolder;
				break;
			case 'details':
					$jpDetails = CDOMElement::create('div','class:jp-details');
					$jpTitle = CDOMElement::create('div','class:jp-title');
					$jpTitle->setAttribute('aria-label','title');
					$jpTitle->addChild(new CText('&nbsp;'));
					$jpDetails->addChild($jpTitle);

					return $jpDetails;
				break;
			case 'nosolution':
					$jpNosolution = CDOMElement::create('div','class:jp-no-solution');
					$jpNoSolSpan = CDOMElement::create('span');
					$jpNoSolSpan->addChild(new CText(translateFN('Aggiornamento Richiesto')));
					$jpNosolution->addChild($jpNoSolSpan);
					$jpNosolution->addChild(new CText(translateFN('Per riprodurre il media devi aggiornare alla '.
							'versione più recente il tuo borwser oppure il '.
							'<a href="http://get.adobe.com/flashplayer/" target="_blank">plugin Flash</a>.')));

					return $jpNosolution;
				break;
		}
	}
}