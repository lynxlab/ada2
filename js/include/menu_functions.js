var DROPDOWN_MENU_OPEN_ANIMATION     = true;
var DROPDOWN_MENU_CLOSE_ANIMATION    = false;
var NAVIGATION_PANEL_OPEN_ANIMATION  = false;
var NAVIGATION_PANEL_CLOSE_ANIMATION = false;
var NAVIGATION_PANEL_IDENTIFIER      = 'menuright';
var NODE_TEXT_CONTAINER_IDENTIFIER   = 'content_view';
var MAIN_INDEX_CONTAINER_IDENTIFIER   = 'contentcontent';
var EFFECT_BLIND_DURATION_IN_SECONDS = 0.3;

/**
 * display the unread messages badge if it's needed
 */
setUnreadMessagesBadge();

/**
 * hides the sidebar (aka menuright) from an
 * href onclick event generated inside the sidebar
 */
function hideSideBarFromSideBar() {
	if (IE_version==false || IE_version>8) {
		$j('#menuright').sidebar('hide');
		$j('li.item.active').removeClass('active');
	} else {
		if ($j('#menuright').is(':visible')) $j('#menuright').hide();
	}

}

document.observe('dom:loaded', function() {

	/**
	 * add trim method to String object
	 */

	if(typeof String.prototype.trim !== 'function') {
		  String.prototype.trim = function() {
		    return this.replace(/^\s+|\s+$/g, '');
		  }
		}
	/**
	 * sets the dropdown menu to appear on hover
	 * and the menuitem onclick handler for proper css class switching
	 *
	 * WARNING: I'm using $j inside a function called by prototype
	 * document observer. One day all shall be handled by jQuery...
	 * This is not going to harm anybody, but you've been warned
	 */

	/**
	 * If it's not internet explorer or is IE>8
	 * use semantic-ui components
	 */
	if (IE_version==false || IE_version>8) {
		/**
		 * Copy the .computer.menu HTML code, make the
		 * needed changes and use it as a .mobile.menu
		 */
		if ($j('.ui.mobile.ada.menu').length>0) {
			var menuHTML = $j('.ui.computer.ada.menu').clone();
			$j(menuHTML).find('ul.left.menu').toggleClass('left menu sm sm-ada');
			var rightMenu = $j(menuHTML).find('.right.menu');
			if (rightMenu.length >0) {
				rightMenu.remove();
				$j(menuHTML).find('ul').first().append(rightMenu.html());
			}

			$j(menuHTML).find('.ui.dropdown.item').toggleClass('item').toggleClass('fluid').
			wrap('<li class="item"><ul></ul></li>');
			// use the generated html as the mobile menu
			$j('.ui.mobile.ada.menu').html(menuHTML.html());
			// hook the menubutton onclick to mobile menu display
		    $j('.ui.mobile.ada.menubutton .ui.button').on('click', function() {
		    	$j('#mobilesidebar').sidebar('toggle');
		    });
		}


		// mobile dropdown on click
    	/**
    	 * @author giorgio 16/set/2014
    	 * commented line to have a non-js working
    	 * dropdown as a workaround to some bug causing
    	 * firefox crash on xp and vista.
    	 * Should you wish to revert to a js dropdown,
    	 * remove the simple class from menu_functions.inc.php
    	 * and uncomment the following line
    	 */
		// $j('.mobile.menu .dropdown').dropdown({ on: 'click' });

		// enable menu items (non dropdown) active class
		var menuItem = $j('.computer.ada > .menu > li.item').not('.closepanel');
		menuItem.on('click', function() {
		    if(!$j(this).hasClass('dropdown')) {
		          $j(this).toggleClass('active').closest('.ui.menu')
		          .find('.item').not($j(this)).removeClass('active');
		    }
		});

		// enable userpopup, if found
		if ($j('li.item.userpopup').length>0 && $j('#status_bar').length>0) {
			$j('#status_bar').hide();
			$j('li.item.userpopup').popup({
				variation: 'large',
			    position: 'bottom center',
			    html: $j('#status_bar').html(),
			    on: 'click',
			    onHide: function() {
		    		$j('.ada.menu .item.active').removeClass('active');
			    }
			  });
		}

		// enable com_tools popup
		if ($j('div#com_tools').length>0) {
			$j('.ui.menu .item','#com_tools').each (function() {
				var popupContent = $j(this).children('span').first();
				var totalRows = 0;
				if ($j(this).hasClass('whosonline')) {
					totalRows = popupContent.find("ul>li").length;
				} else if ($j(this).hasClass('messages') || $j(this).hasClass('appointments')){
					// maxium number of rows to display
					var maxRows = 5;
					totalRows = popupContent.find("table>tbody>tr").length;
					if (totalRows > maxRows) {
						popupContent.find("table>tbody>tr").each(function() {
							if (totalRows > maxRows) {
								$j(this).remove();
								totalRows--;
							}
						});
					}
				}
				// if the resulting popup have no rows, disable
				// the respective link/button in the com_tools bar
				if (totalRows>0) {
					$j(this).popup({
						position: 'top left',
						html: popupContent.html(),
						on: 'click',
						target: $j(this),
						offset: -50,
						inline: true,
						onShow: function() { $j(this).toggleClass('active'); },
						onHide: function() { $j(this).toggleClass('active'); }
					});
				} else {
					$j(this).addClass('disabled');
				}
			});
		}

	    // perform search on search icon click
    	if ($j('.search.link.icon').length>0) {
    		$j('.search.link.icon').on('click',function(){
    			var text = $j(this).siblings('input[type="text"]').val().trim();
    			if (text.length>0) {
    				document.location.href='search.php?s_UnicNode_text='+text+'&l_search=l_search&submit=cerca';
    			}
    		});
    	}

	    // init and set resize for mobile sidebar if needed
	    if ($j('#mobilesidebar').length>0) {
	        $j('#mobilesidebar').sidebar();
	        $j(window).resize(function() {
	        	var w = $j(window).width();
	        	if (w>768 && $j('#mobilesidebar').sidebar('is open')) {
	        		$j('#mobilesidebar').sidebar('toggle');
	        	}
	        });
	    }

	    $j('.ui.accordion').accordion();

	} else {
		/**
		 * it's internet explorer v.8 or less, use smartmenus
		 */
		$j('.ui.mobile.ada.menubutton ,#mobilesidebar').remove();

		$j('ul.left.menu, ul.right.menu').smartmenus({
			subMenusSubOffsetX: 1,
			subMenusSubOffsetY: -8,
			subIndicators: false
		});

		if ($j('#menuright').length>0) {
			$j('li.item.rightpaneltoggle').removeClass('disabled');
		}

		// disable click event on disabled items
		if ($j('.ui.ada.menu  ul.menu > li.item.disabled > a').length>0) {
			$j('.ui.ada.menu  ul.menu > li.item.disabled > a').prop('onclick',null).off('click');
		}

		// enable show/hide userpopup, if found
		if ($j('li.item.userpopup').length>0 && $j('#status_bar').length>0) {
			$j('#status_bar').hide();
			$j('li.item.userpopup').on('click',function() {
				if($j('#status_bar').is(':visible')) {
					$j('#status_bar').fadeOut();
				} else {
					$j('#status_bar').fadeIn();
				}
			});
		}
	}

    // if there's the searchbox, make it work
    if($j('.item.searchItem input[type="text"]').length>0) {
		// perform search on searchmenutext enter key press
		$j('.item.searchItem input[type="text"]').on('keyup', function(event){
			if(event.which == 13) {
				var text = $j(this).val().trim();
    			if (text.length>0) {
    				document.location.href='search.php?s_UnicNode_text='+text+'&l_search=l_search&submit=cerca';
    			}
			}
		});
    }


});

function navigationPanelToggle() {
	if (IE_version==false || IE_version>8) {
		$j('#menuright').sidebar({overlay:true}).sidebar('toggle');
	} else {
		$j('#menuright').toggle('fade');
		if (!index_loaded) showIndex();
	}
}

/*
 * Per mostrare e nascondere elementi
 */
 Effect.BlindLeft = function(elem) {
	var element = $(elem);
	element.makeClipping();

	return new Effect.Scale(element, 0,
		Object.extend({scaleContent: false,
			scaleY: false,
			scaleMode: 'box',
			restoreAfterFinish:true,
			afterSetup: function (effect) {
				effect.element.makeClipping().setStyle({
					height: effect.dims[0] + 'px'
				}).show();
			},
			afterFinishInternal: function(effect) {
				effect.element.hide().undoClipping();
			}
		}, arguments[1] || {})
	);
};

Effect.BlindRight = function(elem) {
	var element = $(elem);
	var elementDimensions = $(element).getDimensions();

	return new Effect.Scale(element, 100, Object.extend({
		scaleContent: false,
		scaleY: false,
		scaleFrom: 0,
		scaleMode: {originalHeight: elementDimensions.height, originalWidth: elementDimensions.width},
		restoreAfterFinish: true,
		afterSetup: function(effect) {
			effect.element.makeClipping().setStyle({
				width: '0px',
				height: effect.dims[0]+'px'
			}).show();
		},
		afterFinishInternal: function(effect) {
			effect.element.undoClipping();
		}
	}, arguments[1] || {})
	);
};


function toggleElementVisibility(element, direction)
{
	if($(element).hasClassName('sottomenu_off')){
		$(element).removeClassName('sottomenu_off');
		$(element).hide();
	}

	/*
	 * Handle navigation panel visibility.
	 */
	if ($(element).identify() == NAVIGATION_PANEL_IDENTIFIER) {
		if($(element).visible()) {
			navigationPanelHide(direction);
		}
		else {
			navigationPanelShow(direction);
		}
		return;
	}

	/*
	 * Handle dropdown menu visibility
	 */
	var elements = $('dropdownmenu').childElements();
	var elements_count = elements.size();

	for (i=0; i < elements_count; i++) {

		var e = elements[i];

		var menu_link = e.identify().sub('submenu_','',1);

		var selected_classname   = 'selected'   + menu_link;
		var unselected_classname = 'unselected' + menu_link;

		if(e.identify() == element) {

			if(e.visible()) {

				$(menu_link).removeClassName(selected_classname);
				$(menu_link).addClassName(unselected_classname);

				dropDownMenuHide(e, direction);
			}
			else {

				$(menu_link).removeClassName(unselected_classname);
				$(menu_link).addClassName(selected_classname);

				dropDownMenuShow(e, direction);
			}
		}
		else {
			if(e.visible()) {

				$(menu_link).removeClassName(selected_classname);
				$(menu_link).addClassName(unselected_classname);

				dropDownMenuHide(e, direction);
			}
		}
	}
}

function showElement(element, direction) {

	switch(direction) {
		case 'left':
			Effect.BlindRight(element);

		case 'right':
			Effect.BlindRight(element);

		case 'up':
			Effect.BlindDown(element, {duration: EFFECT_BLIND_DURATION_IN_SECONDS});

		default:
	}
}

function hideElement(element, direction) {

	switch(direction) {
		case 'left':
			Effect.BlindLeft(element);

		case 'right':
			Effect.BlindLeft(element);

		case 'up':
			Effect.BlindUp(element, {duration: EFFECT_BLIND_DURATION_IN_SECONDS});

		default:
	}
}

function navigationPanelHide(direction) {
	if(NAVIGATION_PANEL_CLOSE_ANIMATION) {
		hideElement(NAVIGATION_PANEL_IDENTIFIER, direction);
	}
	else {
		if($(NODE_TEXT_CONTAINER_IDENTIFIER)
		   && $(NODE_TEXT_CONTAINER_IDENTIFIER).hasClassName('content_small')){

			$(NODE_TEXT_CONTAINER_IDENTIFIER).removeClassName('content_small');
		}
                else {
                    if ($(MAIN_INDEX_CONTAINER_IDENTIFIER)
                        && $(MAIN_INDEX_CONTAINER_IDENTIFIER).hasClassName('content_small')){
                            $(MAIN_INDEX_CONTAINER_IDENTIFIER).removeClassName('content_small');
                    }
                }
		$(NAVIGATION_PANEL_IDENTIFIER).hide();
	}
}

function navigationPanelShow(direction) {
	if(NAVIGATION_PANEL_OPEN_ANIMATION) {
		showElement(NAVIGATION_PANEL_IDENTIFIER, direction);
	}
	else {
		if($(NODE_TEXT_CONTAINER_IDENTIFIER)
		   && !$(NODE_TEXT_CONTAINER_IDENTIFIER).hasClassName('content_small')){

			$(NODE_TEXT_CONTAINER_IDENTIFIER).addClassName('content_small');
		}
                else {
                    if($(MAIN_INDEX_CONTAINER_IDENTIFIER)
                        && !$(MAIN_INDEX_CONTAINER_IDENTIFIER).hasClassName('content_small')){
                            $(MAIN_INDEX_CONTAINER_IDENTIFIER).addClassName('content_small');
                    }
                }
		$(NAVIGATION_PANEL_IDENTIFIER).show();
	}
}

function dropDownMenuShow(element, direction) {

	var targetElement = $(element).identify().sub('submenu_', '');
	$(element).clonePosition(targetElement, {
		setLeft: true,
		setTop: false,
		setWidth: false,
		setHeight: false
	});

	if(DROPDOWN_MENU_OPEN_ANIMATION) {
		showElement(element, direction);
	}
	else {
		$(element).show();
	}
}

function dropDownMenuHide(element, direction) {
	if(DROPDOWN_MENU_CLOSE_ANIMATION) {
		hideElement(element, direction);
	}
	else {
		$(element).hide();
	}
}

function setUnreadMessagesBadge () {
	document.observe('dom:loaded', function() {
		// do something only if there is the 'comunica' menu item
		/**
		 * @author giorgio 21/ago/2014
		 * FIXME:
		 * when all templates menu are turned into semantic-ui
		 * it is safe to remove all the $('com') related stuff
		 */
		if ($('com')!=undefined || $('unreadmsgbadge')!=undefined) {
			new Ajax.Request( HTTP_ROOT_DIR+ '/comunica/ajax/getUnreadMessagesCount.php', {
				method: 'get',
				onComplete: function(transport) {
					var json = transport.responseText.evalJSON(true);
					var value = parseInt (json.value);
					if (!isNaN(value) && value>0) {
						if ($('com')!=undefined) {
							var msgCounter = new Element('span',{
								id:'newMsgCount'
							});
							msgCounter.style.display = 'none';
							msgCounter.update("<span class='arrow'></span>"+value);
							$('com').insert(msgCounter);
							$('com').style.paddingRight = '0';
							Effect.Appear('newMsgCount',{ duration: 0.4 });
						} else if($('unreadmsgbadge')!=undefined) { // update span id
							$('msglabel').show();
							$('unreadmsgbadge').update(value);
						}
					}
				}
			});
//			PLEASE FIND BELOW A MOCK-UP CODE TO BE USED WITH jQuery/NOCONFLICT
//			$j.ajax({
//				type	: 'GET',
//				url		: HTTP_ROOT_DIR+ '/comunica/ajax/getUnreadMessagesCount.php',
//				dataType:'json'
//				})
//				.done   (function( JSONObj ) {
//					if (JSONObj)
//						{
//							console.log (JSONObj);
//						}
//				});
			// decrease some padding for nice rendering
//			$j('#com').css('padding-right','10px');
			// generate the new message counter to the 'comunica' menu iten
//			var msgCounter = $j('<span>').attr('id','newMsgCount').html("<span class='arrow'></span>"+messageCount).hide();
			// show the counter with effect
//			$j('#com').append(msgCounter);
//			msgCounter.fadeIn();
		}
	});
}

var index_loaded=false;
function showIndex() {
    if(!index_loaded) {
	    $j.ajax({
		type	: 'GET',
		url     : HTTP_ROOT_DIR + '/browsing/ajax/index_menu.php',
        dataType:'html',
		async	: true,
        success: function(data) {
        		$j('#show_index').slideUp(function(){
        			$j('#show_index').html(data).slideDown();
        		});
            	index_loaded=true;
        	}
	    });
    }
}