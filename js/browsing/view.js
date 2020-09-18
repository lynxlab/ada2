/**
 * VIEW.JS
 *
 * @package		view
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Main view.php initializations, starts up tabbed content and nivo slider as
 * appropriate
 */
function initDoc() {
	const isAuthor = $j('body').hasClass('ada-autore');
	// run script after document is ready
	$j(function() {
		// install flowplayer to an element with CSS class "ADAflowplayer"
		// generated by the media_viewing_classes if it's needed
		if ($j(".ADAflowplayer").length > 0)
			$j(".ADAflowplayer").flowplayer();

		// if there are tabs on the page, initialize'em
		if ($j("#tabs").length > 0) {
			$j("#tabs").tabs({
				collapsible : true,
				active : false
			});
			showTabs = true;
		}

		// if there's a nivo slider, start it
		if ($j("#slider").length > 0) {
			$j('#slider').nivoSlider({
				// effect: 'fade', // Specify sets like: 'fold,fade,sliceDown'
				// effect: 'fold', // Specify sets like: 'fold,fade,sliceDown'
				effect : 'slideInRight', // Specify sets like: 'fold,fade,sliceDown'
				slices : 15, // For slice animations
				boxCols : 8, // For box animations
				boxRows : 4, // For box animations
				animSpeed : 500, // Slide transition speed
				pauseTime : 3000, // How long each slide will show
				startSlide : 0, // Set starting Slide (0 index)
				directionNav : true, // Next & Prev navigation
				controlNav : true, // 1,2,3... navigation
				controlNavThumbs : true, // Use thumbnails for Control Nav
				pauseOnHover : true, // Stop animation while hovering
				manualAdvance : false, // Force manual transitions
				prevText : 'Prev', // Prev directionNav text
				nextText : 'Next', // Next directionNav text
				randomStart : false, // Start on a random slide
				beforeChange : function() {
				}, // Triggers before a slide transition
				afterChange : function() {
				}, // Triggers after a slide transition
				slideshowEnd : function() {
				}, // Triggers after all slides have been shown
				lastSlide : function() {
				}, // Triggers when last slide is shown
				afterLoad : function() {
				} // Triggers when slider has loaded
			}); // end nivoSlider init
		}

		if ($j('.ui.menu','.semantic.tabs').length>0) {
			$j('a.item:not(:first-child), .ui.tab:not(:first-child)','.semantic.tabs').removeClass('active');
			$j('.ui.menu > a.item','.semantic.tabs').click( function(e) {
				e.preventDefault();
				$j(this).parents('.semantic.tabs').find('a.item').removeClass('active');
				$j(this).parents('.semantic.tabs').find('.ui.tab').removeClass('active');
				$j(this).addClass('active');
				$j($j(this).attr('href')).addClass('active');
			});
		}

		// add class to style keywords as labels
		$j('a','div.keywords.content').each(function() {
			// remove keywords equals to string 'null'
			if ($j(this).text().toLowerCase()=='null') $j(this).remove();
			$j(this).addClass('ui label');
		});

		// if no keywords, remove the divs
		if ($j('a','div.keywords.content').length<=0) $j('div.keywords').remove();

		// if accordion holding notes and keywords is empty, remove it
		if ($j('#notesandkeywords.ui.accordion','#content_view').last().children().length <= 0) {
			$j('#notesandkeywords.ui.accordion','#content_view').last().remove();
		}
		// show the accordion, if it's still there
		$j('#notesandkeywords.ui.accordion','#content_view').last().fadeIn();

		// close navigation right panel if its cookie is there
		var closeRightPanel = parseInt(readCookie("closeRightPanel"))==1;
		if ((!$j('#menuright').sidebar('is open') && !closeRightPanel) ||
			($j('#menuright').sidebar('is open') && closeRightPanel)) {
			$j('a[onclick*="navigationPanelToggle()"]').trigger('click');
			navigationPanelToggle();
		}

		var checkRepeater = [];
		for (i=0; i<window.frames.length; i++) {
		try{
			if ('Reveal' in window.frames[i].window) {
				setupRevealListeners(i, checkRepeater, {
					readyCallback: function() {
						// empty callback
					},
					endCallback: function(i){
						// empty callback, argument i is the iframe index that has just ended
					}
				});
			}
		}
		catch { }
		}
	}); // end $j function

	if ($j('#chatsidebar').length>0) {
		$j('#chatsidebar').first().sidebar({overlay:true}).sidebar('attach events', '#triggerchat');
	}

	if (!isAuthor) {
		const loaderHtml = '<div style="padding:1em;"><div class="ui active inverted dimmer"><div class="ui loader"></div></div></div>';
		if ($j('#jitsi-meet-placeholder').length>0) {
			$j('#jitsi-meet-placeholder').html(loaderHtml);
			$j.getScript('../js/comunica/ada-jitsi.js.php?isView=1&parentId=jitsi-meet-placeholder');
		} else if ($j('#bbb-placeholder').length>0) {
			$j('#bbb-placeholder').html(loaderHtml);
			$j('#bbb-placeholder').load('../modules/bbb-integration/nodeembed.php', function (response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$j('#bbb-placeholder').html(msg + xhr.status + " " + xhr.statusText);
				} else {
					$j.getScript('../js/comunica/videochat.js');
				}
			});
		} else if ($j('#zoom-placeholder').length>0) {
			$j('#zoom-placeholder').html(loaderHtml);
			$j('#zoom-placeholder').load('../modules/zoom-integration/nodeembed.php', function (response, status, xhr) {
				if (status == "error") {
					var msg = "Sorry but there was an error: ";
					$j('#zoom-placeholder').html(msg + xhr.status + " " + xhr.statusText);
				} else {
					$j.getScript('../js/comunica/videochat.js');
				}
			});
		}
	}
} // end initDoc

function setupRevealListeners(frameIdx, checkRepeater, callbacks) {
	var callbacks = callbacks || {};
	var revealObj = window.frames[i].window.Reveal;
	revealObj.addEventListener('ready', function( event ) {
		// remove unwanted footer
		$j(window.frames[frameIdx].window.document).contents().find('.embed-footer').remove();
		// do the callback if it's there
		if ('function' === typeof callbacks.readyCallback) callbacks.readyCallback();
	});
	revealObj.addEventListener('slidechanged', function( event ) {
		// if in the last slide, check every second if the navigate-right button
		// is disabled. when it is, the slide has actually reached the end
		if (revealObj.isLastSlide()) {
			checkRepeater[frameIdx] = window.setInterval(function(){
				// check if next button is still there
				if ($j(window.frames[frameIdx].window.document).contents().find('button.navigate-right[disabled="disabled"]').length>0) {
					window.clearInterval(checkRepeater[frameIdx]);
					if ('function' === typeof callbacks.endCallback) callbacks.endCallback(frameIdx);
				}
			},1000);
		}
	});
}