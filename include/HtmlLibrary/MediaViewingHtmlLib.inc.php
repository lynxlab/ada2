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
		return ++self::$count;
	}
	
	private function jplayerCommonJS($format, $divID, $url, $title=null, $width=null, $height=null) {
		$jplayerCode = '<script type="text/javascript">
					//<![CDATA[
    					var jplayerNoConflict = {
    						jQuery: $j
						};
    
    					var windowWidth = $j(window).width() + "px";
      					var windowHeight = $j(window).height() + "px";
    
						$j(document).ready(function(){
							$j("#jquery_jplayer_'.$divID.'").jPlayer({
								ready: function () {
									$j(this).jPlayer("setMedia", {';
		if (isset($title) && strlen ($title)>0) {
			$jplayerCode .= 'title: "'.$title.'",';
		}
		$jplayerCode .= $format.': "'.$url.'"
									});
								},
								play: function() { // To avoid multiple jPlayers playing together.
									$j(this).jPlayer("pauseOthers");
								},
				    			size: {
                         			width: "'.$width.'px",
                         			height: "'.$height.'px"
                    			},
//                        			sizeFull: {
//                 					width: windowWidth,
//                 					height: windowHeight
//             					},
    							solution: "flash, html",
    							noConflict: "jplayerNoConflict.jQuery",
								swfPath: "'.HTTP_ROOT_DIR.'/js/include/jquery/jplayer",
								supplied: "'.$format.'",
								cssSelectorAncestor: "#jp_container_'.$divID.'",
								wmode: "window",
								smoothPlayBar: true,
								keyEnabled: true,
								remainingDuration: true,
								toggleDuration: true
							});
						});
					//]]>
					</script>';
		
		return $jplayerCode;
	}
	
	public static function jplayerMp4Viewer($url, $title=null, $width=null, $height=null) {
		
		$divID = self::getNextDivID();
		
		$jplayerCode = self::jplayerCommonJS('m4v', $divID, $url, $title, $width, $height);
		
		$jplayerCode .= '<div id="jp_container_'.$divID.'" class="jp-video">
			<div class="jp-type-single">
				<div id="jquery_jplayer_'.$divID.'" class="jp-jplayer"></div>
				<div class="jp-gui">
					<div class="jp-video-play">
						<a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
					</div>
					<div class="jp-interface">
						<div class="jp-progress">
							<div class="jp-seek-bar">
								<div class="jp-play-bar"></div>
							</div>
						</div>
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
						<div class="jp-controls-holder">
							<ul class="jp-controls">
								<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
								<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
								<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
								<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
								<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
								<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
							</ul>
							<div class="jp-volume-bar">
								<div class="jp-volume-bar-value"></div>
							</div>
							<ul class="jp-toggles">
								<li><a href="javascript:;" class="jp-full-screen" tabindex="1" title="full screen">full screen</a></li>
								<li><a href="javascript:;" class="jp-restore-screen" tabindex="1" title="restore screen">restore screen</a></li>
							</ul>
						</div>
						<div class="jp-details">
							<ul>
								<li><span class="jp-title"></span></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>';
		
		return $jplayerCode;
	}
	
	public static function jplayerMp3Viewer ($url, $title=null) {
		
		$divID = self::getNextDivID();
		
		$jplayerCode = self::jplayerCommonJS('mp3', $divID, $url, $title);
		
		$jplayerCode .= '<div id="jquery_jplayer_'.$divID.'" class="jp-jplayer"></div>		
		<div id="jp_container_'.$divID.'" class="jp-audio">
			<div class="jp-type-single">
				<div class="jp-gui jp-interface">
					<ul class="jp-controls">
						<li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
						<li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
						<li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
						<li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
						<li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
						<li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
					</ul>
					<div class="jp-progress">
						<div class="jp-seek-bar">
							<div class="jp-play-bar"></div>
						</div>
					</div>
					<div class="jp-volume-bar">
						<div class="jp-volume-bar-value"></div>
					</div>
					<div class="jp-time-holder">
						<div class="jp-current-time"></div>
						<div class="jp-duration"></div>
					</div>
				</div>
				<div class="jp-details">
					<ul>
						<li><span class="jp-title"></span></li>
					</ul>
				</div>
				<div class="jp-no-solution">
					<span>Update Required</span>
					To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
				</div>
			</div>
		</div>';
		
		return $jplayerCode;
	}
	
}