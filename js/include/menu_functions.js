var DROPDOWN_MENU_OPEN_ANIMATION     = true;
var DROPDOWN_MENU_CLOSE_ANIMATION    = false;
var NAVIGATION_PANEL_OPEN_ANIMATION  = false;
var NAVIGATION_PANEL_CLOSE_ANIMATION = false;
var NAVIGATION_PANEL_IDENTIFIER      = 'menuright';
var NODE_TEXT_CONTAINER_IDENTIFIER   = 'content_view';
var MAIN_INDEX_CONTAINER_IDENTIFIER   = 'contentcontent';
var EFFECT_BLIND_DURATION_IN_SECONDS = 0.3;
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