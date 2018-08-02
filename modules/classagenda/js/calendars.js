/**
 * CLASSAGENDA MODULE.
 *
 * @package        classagenda module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           classagenda
 * @version		   0.1
 */

/**
 * include files
 */
document.write("<script type='text/javascript' src='"+HTTP_ROOT_DIR+"/external/fckeditor/fckeditor.js'></script>");

/**
 * global vars
 */
var hasVenues = false;
var calendar  = undefined;
var mustSave = false;
var canDelete = false;
var reminderDialog = null;
var oFCKeditor = null;
var isCheckingReminder = false;
var userType = null;

/**
 * events updated in the UI only,
 * used to check for tutor overlap
 */
var UIEvents = [];

/**
 * events deleted from the UI only,
 * used to check for tutor overlap
 */
var UIDeletedEvents = [];

function initDoc(passedUserType) {
	// save passed user type in its own global
	userType = passedUserType;
	// hook instance select change to update number of students
	updateStudentCountOnInstanceChange();
	// hook instance select change to update service (aka course) type
	updateServiceTypeOnInstanceChange();	
	// ask a save confirmation before changing course instance
	askChangeInstanceOrVenueConfirm();
	// hook instance select change to update tutors list
	updateTutorsListOnInstanceChange();
	// init buttons
	initSaveButton();
	initCancelButton();
	
	hasVenues = $j('#venuesList').length>0;
	if (hasVenues) {
		// hook instance select change to update venues list
		updateVenuesListOnInstanceChange();
		// hook venues select change to update classroom list
		updateClassroomsOnVenueChange();
		// trigger onchange event to update classroom list when page loads
		// $j('#venuesList').trigger('change');
	}
	
	if ($j('#filterInstanceState').length>0 || $j('#onlySelectedInstance').length>0) {
		$j('#filterInstanceState, #onlySelectedInstance, #onlySelectedVenue').on('change',function() {
			/**
			 * reload course instances list only when #filterInstanceState changes 
			 */
			if ($j(this).attr('id')=='filterInstanceState') {
				$j.when(loadCourseInstances()).done(function() {
					if (getSelectedCourseInstance()!=0) calendar.fullCalendar( 'refetchEvents' );
				});
			} else calendar.fullCalendar( 'refetchEvents' );
			
		});
		
		$j('#filterInstanceState, label[for="filterInstanceState"],'+
		   '#onlySelectedInstance, label[for="onlySelectedInstance"],'+
		   '#onlySelectedVenue, label[for="onlySelectedVenue"]').on('mousedown',function() {
			if (mustSave) {
				event.preventDefault();
				jQueryConfirm('#confirmDialog', '#filterInstanceStatequestion',
						function() { saveClassRoomEvents(); },
						function() { event.preventDefault(); });
			}
		});
	}
	
	// handle export menu items
	if ($j('li.calendarexportmenuitem a').length>0) {
		$j('li.calendarexportmenuitem a').on ('click',function(){
			aHref = $j(this).attr('href') + '&id_course='+$j('#courseID').text()+'&id_course_instance='+getSelectedCourseInstance();
			
			if ('undefined' != typeof $j(this).data('type') && $j(this).data('type')=='pdf') {
				openNewWindow(aHref,0,0,'',true,true);
			} else {
				location.href = aHref;
			}
			return false;
		});
	}
	
	/**
	 * to initialize, call loadCourseInstances() that will fire
	 * an #instancesList change event causing the execution of:
	 * 1. updateStudentCountOnInstanceChange();
	 * 2. updateServiceTypeOnInstanceChange();
	 * 3. updateTutorsListOnInstanceChange();
	 * 
	 * the update tutor will cause fullcalendar events to be
	 * loaded if the global calendar variable is not undefined
	 * or the fullcalendar to be initialized.
	 */
	loadCourseInstances();
}

/**
 * init facilities tooltip on classroom
 * radio button mouserover
 */
function initFacilitiesTooltips() {
	if ($j('.classroomradio').length>0) {
		$j(document).tooltip({
			items : '#classroomlist label',
			content: function() {
				var classroomID = parseInt($j(this).attr('for').match(/(\d+)$/)[0]);
				return ($j('#facilities'+classroomID).length>0) ? $j('#facilities'+classroomID).html() : null;
			}
		});
	}
}

function initCalendar() {
	if ($j('#classcalendar').length>0) {
		calendar = $j('#classcalendar').fullCalendar({
			// put your options and callbacks here
			theme 	 : true,	// enables jQuery UI theme
			firstDay : 1,		// monday is the first day
			minTime  : "08:00",	// events starts at 08AM ,
			maxTime  : "20:00",	// events ends at 08PM
			weekends : false,	// hide weekends
			defaultEventMinutes: 60,
			height : 564,
			editable : (userType == AMA_TYPE_SWITCHER) || (userType == AMA_TYPE_TUTOR),
			eventStartEditable : (userType == AMA_TYPE_SWITCHER),
			eventDurationEditable : (userType == AMA_TYPE_SWITCHER),
			selectable : (userType == AMA_TYPE_SWITCHER),
			selectHelper : (userType == AMA_TYPE_SWITCHER),
			allDaySlot : false,
			slotEventOverlap : false,
			defaultView : 'agendaWeek',
			/**
			 * selected or deselect the clicked event
			 */
			eventClick: function(calEvent, jsEvent, view) {
				
				if (!calEvent.editable || isCheckingReminder) return;
				
				var doSelection = true;
				
				// get previously selected event
				var selEvent = getSelectedEvent();
				
				// unselect previously selected event
				if (selEvent!=null) {
					doSelection = selEvent._id != calEvent._id;
					unselectSelectedEvent(!doSelection);
				}
				
				// select clicked element if needed
				if (doSelection) {
					setSelectedEvent(calEvent);
				} else {
					if (canDelete) setCanDelete(false);
				}
			},
			/**
			 * creates a new event
			 */
			select :
				function( startDate, endDate, jsEvent, view ) {
				if (startDate.hasTime() && endDate.hasTime()) {
					
					newEvent = {
						start: startDate.format(),
						end: endDate.format(),
						isSelected : false,
						editable : true,
						instanceID : getSelectedCourseInstance(),
						classroomID : getSelectedClassroom(),
						tutorID : getSelectedTutor()
					};
					
					var placeEvent = function() {
						newEvent.title = buildEventTitle(newEvent);
						if (newEvent.tutorID>0) newEvent.eventID = addToUIEvents(newEvent);
						calendar.fullCalendar('renderEvent', newEvent, true);
						calendar.fullCalendar('unselect');
						if(!mustSave) setMustSave(true);
						updateAllocatedHours(moment.duration(endDate.subtract(startDate)).asMilliseconds(), 1);
					}
					
					/**
					 * do the checkTutorOverlap only if a tutor is there
					 */
					if (newEvent.tutorID>0) {
						$j.when(checkTutorOverlap(newEvent)).done(function(overlaps) {
							if ('undefined' != typeof overlaps.isOverlap && !overlaps.isOverlap) {
								placeEvent();
							} else {
								jQueryConfirm('#confirmDialog', '#tutorOverlapquestion',
										function() { placeEvent(); },
										function() { calendar.fullCalendar('unselect'); });
							}						
						}).fail(function() {
							console.log ('error while checking overlapping events in select');
							calendar.fullCalendar('unselect');
						});
					} else placeEvent();
				}
			},
			eventRender: function(event, element, view) {
				// allows html to be used inside the title
				var htmlEl = ('month' != view.name) ? 'div' : 'span';
				element.find(htmlEl+'.fc-title').html(element.find(htmlEl+'.fc-title').text());
			},
			eventDrop: function ( event, delta, revertFunc) {
				
				var data = prepareDataForCheckTutorOverlap(event);
				
				var placeEvent = function() {
					if (parseInt(delta.as('minutes'))!=0 && !mustSave) setMustSave(true);
					if (data.tutorID>0) addToUIEvents(data);
				}
				/**
				 * do the checkTutorOverlap only if a tutor is there
				 */
				if (data.tutorID>0) {
					$j.when(checkTutorOverlap(data)).done(function(overlaps) {
						if ('undefined' != typeof overlaps.isOverlap && !overlaps.isOverlap) {
							placeEvent();
						} else {
							jQueryConfirm('#confirmDialog', '#tutorOverlapquestion',
									function() { placeEvent(); },
									function() { revertFunc(); });
						}						
					}).fail(function() {
						console.log ('error while checking overlapping events in eventDrop');
						revertFunc();
					});
				} else placeEvent();
				
			},
			eventResize: function( event, delta, revertFunc, jsEvent, ui, view ) {
				
				data = prepareDataForCheckTutorOverlap(event);
				
				var placeEvent = function() {
					if (parseInt(delta.as('minutes'))!=0 && !mustSave) setMustSave(true);
					updateAllocatedHours(delta.asMilliseconds(), 0);
					if (data.tutorID>0) addToUIEvents(data);
				}
				
				/**
				 * do the checkTutorOverlap only if a tutor is there
				 */
				if (data.tutorID>0) {
					$j.when(checkTutorOverlap(data)).done(function(overlaps) {
						if ('undefined' != typeof overlaps.isOverlap && !overlaps.isOverlap) {
							placeEvent();
						} else {
							jQueryConfirm('#confirmDialog', '#tutorOverlapquestion',
									function() { placeEvent(); },
									function() { revertFunc(); });
						}						
					}).fail(function() {
						console.log ('error while checking overlapping events in eventResize');
						revertFunc();
					});
				} else placeEvent();
				
			},
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			events: function(start, end, timezone, callback) {
				$j.when(reloadClassRoomEvents()).done(function(events) {
					callback(events);
				});
			},
			loading: function (isLoading, view) {
				if (isLoading) showLoading();
				else hideLoading();
			}
		});
		
		// initialize save button and set must save to false
		setMustSave(false);
		// initialize delete button and set can delete to false
		setCanDelete(false);
		
		moveInsideCalendarHeader('onlySelectedInstance');
	}
}

function initSaveButton() {
	$j('#saveCalendar').on('click', function ( event ) {
		saveClassRoomEvents();
		event.preventDefault();
	});	
}

function initCancelButton() {
	// initialize cancel button
	$j('#cancelCalendar').button().on('click', function ( event ) {
		event.preventDefault();
		
		var doReset = function() {
			// refetch calendar events
			if ('undefined' != typeof calendar) calendar.fullCalendar('refetchEvents');
			setMustSave(false);
		};
		
		if (mustSave) {
			jQueryConfirm('#confirmDialog', '#'+$j(this).attr('id')+'question',
					function() { doReset(); }, 
					function() { event.preventDefault(); });
		} else doReset();
	});	
}

/**
 * checks the selected classroom radio button
 * 
 * @param classroomid the id of the classroom to be checked
 */
function setSelectedClassroom(classroomid) {
	if ($j('input[name="classroomradio"]').length>0) $j('input[name="classroomradio"][value='+classroomid+']').prop('checked',true);
}

/**
 * checks the selected tutor radio button
 * 
 * @param tutorid the id of the tutor to be checked
 */
function setSelectedTutor(tutorid) {
	if ($j('#tutorSelect').length>0) $j('#tutorSelect option[value="'+tutorid+'"]').attr('selected','selected');
}

/**
 * gets the selected venue option value
 * 
 * @returns selected venue id or null
 */
function getSelectedVenue() {
	return ($j('#venuesList').length>0 && $j('input[name="classroomradio"]').length>0
			&& $j('#onlySelectedVenue').is(':checked')) ? $j('#venuesList').val() : null;
}

/**
 * gets the selected course instance option
 * 
 * @returns selected course instance id or null
 */
function getSelectedCourseInstance() {
	return ($j('#instancesList').length>0) ? $j('#instancesList').val() : null;
}

/**
 * gets the selected course instance 'data-idcourse' property if set
 * 
 * @returns selected course id or null
 */
function getSelectedCourseFromInstance() {
	return ($j('#instancesList').find(':selected').data('idcourse')>0) ? $j('#instancesList').find(':selected').data('idcourse') : null;
}

/**
 * gets the selected classroom radio button
 * 
 * @returns selected classroom id or null
 */
function getSelectedClassroom() {
	return ($j('input[name="classroomradio"]').length>0) ? $j('input[name="classroomradio"]:checked').val() : null;
}

/**
 * gets the selected tutor ID from select element
 * 
 * @returns selected tutor id or null
 */
function getSelectedTutor() {
	return ($j('#tutorSelect').length>0) ? $j('#tutorSelect').val() : null;
}

/**
 * gets the text of the select option associated to the passed tutor id
 * 
 * @param tutorid
 * 
 * @returns string the retreived label or null
 */
function getTutorFromSelect(tutorid)  {
	return ($j('#tutorSelect').length>0) ? $j('#tutorSelect option[value="'+tutorid+'"]').text() : null;
}

/**
 * gets the label of the radio button associated to the passed classroomid
 * 
 * @param classroomid
 * 
 * @returns string the retreived label
 */
function getClassroomRadioLabel(classroomid) {
	return $j('input[name="classroomradio"][value='+classroomid+']+ label').text();
}

/**
 * gets the selected event
 * 
 * @returns event if one is found, or null
 */
function getSelectedEvent() {
	// get selected event
	var selEvent = calendar.fullCalendar('clientEvents', function (clEvent) {
		return (typeof clEvent.isSelected != "undefined" && clEvent.isSelected==true);
		});
	return (selEvent.length > 0) ? selEvent[0] : null;
}

function getFilterInstanceState() {
	return ($j('#filterInstanceState').length>0) ? $j('#filterInstanceState').val() : null;
}

function getShowSelectedInstance() {
	if (($j('#onlySelectedInstance-cloned').length>0)) return $j('#onlySelectedInstance-cloned').is(':checked');
	else if (($j('#onlySelectedInstance').length>0)) return $j('#onlySelectedInstance').is(':checked');
	else return false;
}

/**
 * update the selected calendar event data
 * 
 * @param event the clicked event to be set as selected
 */
function updateSelectedEvent(event) {
	// get selected event
	var selEvent = calendar.fullCalendar('clientEvents', function (clEvent) {
		return (typeof clEvent.isSelected != "undefined" && clEvent.isSelected==true);
		});
	if (selEvent.length > 0) {
		selEvent[0] = event;
		calendar.fullCalendar('updateEvent', event);
	}
}

function unselectSelectedEvent(checkReminder) {
	// get selected event
	var selEvent = calendar.fullCalendar('clientEvents', function (clEvent) {
		return (typeof clEvent.isSelected != "undefined" && clEvent.isSelected==true);
		});
	if (selEvent.length > 0) {
		selEvent[0].isSelected = false;
		selEvent[0].className = '';
		calendar.fullCalendar('updateEvent', selEvent[0]);
	}
	
	if (checkReminder) checkReminderSent();
}

/**
 * sets the selected event to the passed event
 * @param event
 */
function setSelectedEvent(event) {
	// get event to be selected
	var selEvent = calendar.fullCalendar('clientEvents', function (clEvent) {
			if ('undefined' != typeof event.id) {
				return clEvent.id==event.id;
			} else {
				/**
				 * it's a new (non saved) event, must
				 * compare _id since id would be undefined
				 */
				return clEvent._id==event._id;
			}		
		});
	if (selEvent.length > 0) {
		selEvent[0].className = 'selectedClassroomEvent';
		selEvent[0].isSelected = true;
		setSelectedClassroom(selEvent[0].classroomID);
		setSelectedTutor(selEvent[0].tutorID);
		if (!canDelete) setCanDelete(true);
		calendar.fullCalendar('updateEvent', selEvent[0]);
		/**
		 * check if the selected event has a reminder
		 */
		$j.when(checkReminderSent()).always(function() {
			$j('#reminderButton').button({ disabled: !canDelete });
		});
	}
}

/**
 * builds the event title to be rendered
 * 
 * @param event to build the title
 * 
 * @returns string, the event title
 */
function buildEventTitle(event) {
	var title = '';
	title += ($j('#instancesList').length>0) ? ($j('#instancesList option[value='+event.instanceID+']').text()) : '';
	
	// add room and tutor name to the event title
	
	if ($j('input[name="classroomradio"]').length>0) {
		// remove (xx seats) from radiobutton label and use it
		var roomName = getClassroomRadioLabel(event.classroomID).replace(/ \(.*\)/,'');
		title += '<span class="roomnameInEvent">' + roomName + '</span>';
	}
	
	if ($j('#tutorSelect').length>0) {
		title += '<span class="tutornameInEvent">'+
				 getTutorFromSelect(event.tutorID)+'</span>';
	}
	
	return title;
}

function rerenderAllEvents() {
	// get event to be selected
	var allEvents = calendar.fullCalendar('clientEvents');
	
	if (allEvents.length > 0) {
		var selectedInstanceID = getSelectedCourseInstance();
		for (var i=0; i<allEvents.length; i++) {
			allEvents[i].title = buildEventTitle(allEvents[i]);
			// set as editable only events of the selected course instance
			if ((userType == AMA_TYPE_SWITCHER) || (userType == AMA_TYPE_TUTOR)) {
				allEvents[i].editable = (allEvents[i].instanceID==selectedInstanceID);
			} else allEvents[i].editable = false;
			allEvents[i].className = (!allEvents[i].editable) ? 'noteditableClassroomEvent' : 'editableClassroomEvent';
			if (allEvents[i].isSelected) allEvents[i].className = 'selectedClassroomEvent';
		}
		calendar.fullCalendar( 'rerenderEvents' );
	}
	
}

/**
 * updates classroom radio buttons on venue change
 */
function updateClassroomsOnVenueChange() {
	if (hasVenues) {
		$j('#venuesList').on('change', function(){
			$j.ajax({
				type	:	'GET',
				url		:	'ajax/getClassrooms.php',
				data	:	{ venueID: $j(this).val(),
							  courseID: getSelectedCourseFromInstance() },
				dataType:	'html'
			}).done (function(htmlcode){
				if (htmlcode && htmlcode.length>0) {
					$j('#classroomlist').html(htmlcode);
					initFacilitiesTooltips();
					if (getSelectedVenue()==null) rerenderAllEvents();
					else calendar.fullCalendar( 'refetchEvents' );
					// select the first radio button
					if ($j('input[name="classroomradio"]').length>0) {
						$j('input[name="classroomradio"]').first().prop('checked',true);
						$j('input[name="classroomradio"]').on('change', function(userEvent) {
							updateEventOnClassRoomChange();
						});
					}
				}
			});
		});
	}
}

/**
 * set instancesList select dropdown to
 * update service (aka course) type
 */
function updateServiceTypeOnInstanceChange() {
	if ($j('#instancesList').length>0) {
		$j('#instancesList').on('change', function(){
			
			if ($j('#headerInstanceTitle').length >0) {
				$j('#headerInstanceTitle').text($j(this).find('option:selected').text());
			}
			
			if ($j('#servicetype').length>0) {
				$j.ajax({
					type	:	'GET',
					url		:	'ajax/getServiceDetails.php',
					data	:	{ instanceID: $j(this).val(), 
								  courseID: getSelectedCourseFromInstance() },
					dataType:	'json'
				}).done (function(JSONObj){
					if (JSONObj) {
						$j('#servicetype').html(JSONObj.serviceTypeString);
						
						if ('undefined' != typeof JSONObj.courseID) $j('#courseID').text(JSONObj.courseID);
						else $j('#courseID').text('0');
						
						if('undefined' != typeof JSONObj.isOnline && JSONObj.isOnline===true) {
							$j('#classroomlist').html('');
							$j('#classrooms').hide();
							$j('#serviceduration').hide();
						} else if('undefined' != typeof JSONObj.isPresence && JSONObj.isPresence===true) {
							// show classrooms div
							if ($j('#classrooms').length>0 && !$j('#classrooms').is(':visible')) {
								if (userType==AMA_TYPE_SWITCHER) $j('#classrooms').show();
								// trigger venues change to update classroom list
								if (hasVenues) $j('#venuesList').trigger('change');
							}
							if ($j('#serviceduration').length>0 && 'undefined' != typeof JSONObj.duration_hours) {
								$j('#serviceduration').show();
								$j('#duration_hours').text(JSONObj.duration_hours);
								
								if ($j('#allocated_hours').length>0 && 'undefined' != typeof JSONObj.allocated_hours)
									$j('#allocated_hours').text(millisecondsToHourMin(JSONObj.allocated_hours));
								
								if ($j('#lessons_count').length>0 && 'undefined' != typeof JSONObj.lessons_count)
									$j('#lessons_count').text(JSONObj.lessons_count);
							}
						}
					}
				});
			}
		});
	}
}

/**
 * set instancesList select dropdown to
 * update studentcount and calendar events
 */
function updateStudentCountOnInstanceChange() {
	if ($j('#instancesList').length>0) {
		$j('#instancesList').on('change', function(){
			if ($j('#studentcount').length>0) {
				$j.ajax({
					type	:	'GET',
					url		:	'ajax/getStudentsCount.php',
					data	:	{ instanceID: $j(this).val(),
								  courseID: getSelectedCourseFromInstance() },
					dataType:	'json'
				}).done (function(JSONObj){
					if (JSONObj) {
						$j('#studentcount').html(JSONObj.value);
					}
				});
			}
		});
	}
}

/**
 * updates selected event on classroom radio button change
 */
function updateEventOnClassRoomChange() {
	var event = getSelectedEvent(), classroomid = getSelectedClassroom();
	if (event!=null && classroomid>0) {
		event.classroomID = classroomid;
		event.title = buildEventTitle(event);
		updateSelectedEvent(event);
		if(!mustSave) setMustSave(true);
	}
}

/**
 * updates selected event on tutor radio button change
 */
function updateEventOnTutorChange() {
	var event = getSelectedEvent(), tutorid = getSelectedTutor();
	var setTutor = function() {
		event.tutorID = tutorid;
		event.title = buildEventTitle(event);
		updateSelectedEvent(event);
		if(!mustSave) setMustSave(true);
	};
	
	if (event!=null && tutorid>0) {
		event.tutorID = tutorid;
		data = prepareDataForCheckTutorOverlap(event);
		$j.when(checkTutorOverlap(data)).done(function(overlaps) {
			if ('undefined' != typeof overlaps.isOverlap && !overlaps.isOverlap) {
				 setTutor();
			} else {
				jQueryConfirm('#confirmDialog', '#tutorOverlapquestion',
						function() { setTutor(); },
						function() { return null; });
			}
		}).fail(function() {
			console.log ('error while checking overlapping events in updateEventOnTutorChange');
		});
	}	
}

/**
 * if something needs to be saved and the user
 * wants to change course instance or venue, ask to save
 */
function askChangeInstanceOrVenueConfirm() {
	var selector = '';
	if ($j('#instancesList').length>0) selector += '#instancesList';
	if ($j('#venuesList').length>0) selector += ',#venuesList';
	
	if (selector.length>0) {
		$j(selector).on('mousedown', function(event){
			// check if there's something to save here
			if (mustSave) {
				event.preventDefault();
				jQueryConfirm('#confirmDialog', '#'+$j(this).attr('id')+'question',
						function() { saveClassRoomEvents(); }, 
						function() { event.preventDefault(); });
			}
		});
	}
}

/**
 * set instancesList select dropdown to update venues list
 */
function updateVenuesListOnInstanceChange() {
	if (hasVenues) {
		$j('#instancesList').on('change', function(){
			$j.ajax({
				type	:	'GET',
				url		:	'ajax/getVenues.php',
				data	:	{ instanceID: $j(this).val(),
							  courseID: getSelectedCourseFromInstance() },
				dataType:	'html'
			}).done (function(htmlcode){
				if (htmlcode && htmlcode.length>0) {
					$j('#venuesList').html($j(htmlcode).html()).trigger('change');
				}
			});
		});
	}	
}

/**
 * set instancesList select dropdown to update tutors list
 */
function updateTutorsListOnInstanceChange() {
	if ($j('#instancesList').length>0 && $j('#tutorsListContainer').length>0) {
		$j('#instancesList').on('change', function(){
			$j.ajax({
				type	:	'GET',
				url		:	'ajax/getTutors.php',
				data	:	{ instanceID: $j(this).val(),
							  courseID: getSelectedCourseFromInstance() },
				dataType:	'html'
			}).done (function(htmlcode){
				if (htmlcode && htmlcode.length>0) {
					$j('#tutorslist').html(htmlcode);
					// reload classroom events
					if (typeof calendar != 'undefined'){
						if (getShowSelectedInstance()) {
							calendar.fullCalendar( 'refetchEvents' );
						} else {
							rerenderAllEvents();
						}
					} else initCalendar();
					// select the first radio button
					if ($j('#tutorSelect').length>0) {
						$j("#tutorSelect option:first").attr('selected','selected');
						$j('#tutorSelect').on('change', function() {
							updateEventOnTutorChange();
						});
					}
				}
			});
		});
	}
}

/**
 * saves calendar
 */
function saveClassRoomEvents() {
	
	var calEvents = calendar.fullCalendar('clientEvents');
	var eventsToPass = [];
	var selectedCourseInstance = getSelectedCourseInstance();
	
	for (var i=0, j=0; i<calEvents.length; i++) {
		if (calEvents[i].instanceID==selectedCourseInstance) {
			eventsToPass[j++] = {
					id : (typeof calEvents[i].id == 'undefined' || null == calEvents[i].id) ? null : calEvents[i].id,
					wasSelected : (calEvents[i].isSelected ? 1 : 0),
					start : calEvents[i].start.format(),
					end : calEvents[i].end.format(),
					classroomID : calEvents[i].classroomID,
					tutorID : calEvents[i].tutorID
			};
		}
	}
	
	return $j.ajax({
				type	:	'POST',
				url		:	'ajax/saveClassroomEvents.php',
				data	:	{
					venueID : getSelectedVenue(),
					instanceID : selectedCourseInstance,
					events : eventsToPass
				},
				dataType:	'json'
			}).done (function(JSONObj){
				var popupMsg = 'Unknown Error';
				var popupErr = false;
				if (JSONObj && JSONObj.status.length>0) {
					if ('undefined' != typeof JSONObj.newSelectedID) {
						// must look for the event having id==newSelectedID
						// and set its isSelected to true, so it will be re-selected
						var selEvent = getSelectedEvent();
						if (selEvent!=null) {
							selEvent.id = JSONObj.newSelectedID;
							updateSelectedEvent(selEvent);
						}
					}
					popupMsg = JSONObj.msg;
					popupErr = JSONObj.status==='OK';
				}
				
				/**
				 * disable save button
				 */
				setMustSave(false);
				/**
				 * display popup message and reload calendar data
				 */
				$j.when(showHideDiv('',popupMsg, popupErr)).then(function() {
					rerenderAllEvents();
				});
			});
}

function loadCourseInstances() {
	if ($j('#instancesList').length>0) {
		
		var oldSelectedInstance = getSelectedCourseInstance();
		
		$j('#instancesList').prop('disabled','disabled');
		return $j.ajax({
			type	:	'GET',
			url		:	'ajax/getInstances.php',
			data	:	{ filterInstanceState : getFilterInstanceState() },
			dataType:	'html'
		}).done (function(htmlcode){
			if (htmlcode.length>0){
				$j('#instancesList').html(htmlcode);
				/**
				 * if oldSelectedInstance is still in the returned <select> element,
				 * select id. Else select the first returned <option>
				 */
				if (typeof $j('#instancesList option[value="'+oldSelectedInstance+'"]').val() == 'undefined') {
					// mark the first option as selected
					$j("#instancesList option:first").attr('selected','selected');
				} else {
					$j('#instancesList option[value="'+oldSelectedInstance+'"]').attr('selected','selected');
				}
				if (getSelectedCourseInstance()!=0) {
					// trigger onchange event to update number of students when page loads
					$j('#instancesList').trigger('change');
				} else {
					$j('#servicetype').text('--');
					$j('#studentcount').text('--');
					$j('#tutorslist').text('--');
					$j('#headerInstanceTitle').text('--');
					$j('#classroomlist').html('');
					$j('#classrooms').hide();
					$j('#serviceduration').hide();
					calendar.fullCalendar('removeEvents');
				}
			}
		}).always(function() {
			$j('#instancesList').prop('disabled',false);
		});
	}
}

/**
 * reloads calendar events for select instance id
 */
function reloadClassRoomEvents() {
	var data = {
			filterInstanceState : getFilterInstanceState() 
		};
	var venueID = getSelectedVenue();
	var selectedInstanceID = getSelectedCourseInstance();
	
	if (getShowSelectedInstance()) {
		$j.extend (data, {
			instanceID: selectedInstanceID,
			courseID: getSelectedCourseFromInstance()
		});
	}
	
	/**
	 * ajax-load events
	 */
	var deferred = $j.Deferred();
	
	$j.ajax({
		type	:	'GET',
		url		:	'ajax/getCalendarForInstance.php',
		data	:	data,
		dataType:	'json'
	}).done (function(JSONObj){
		/**
		 * store the selected event in a var
		 */
		var selectedEvent = getSelectedEvent();
		if (null != selectedEvent) unselectSelectedEvent(false);
		
		if (!mustSave) calendar.fullCalendar('removeEvents');
		
		if (JSONObj) {
			/**
			 * add all loaded events to the calendar
			 */
			var selectedIndex = -1;
			var eventsToDraw = [];
			for (var i=0; i<JSONObj.length; i++) {
				if (venueID==null || (venueID!=null && JSONObj[i].venueID==venueID)) {
					if (null != selectedEvent && JSONObj[i].id == selectedEvent.id) selectedIndex = i;
					JSONObj[i].title = buildEventTitle(JSONObj[i]);
					// set as editable only events of the selected course instance
					if ((userType == AMA_TYPE_SWITCHER) || (userType == AMA_TYPE_TUTOR)) {
						JSONObj[i].editable = (JSONObj[i].instanceID==selectedInstanceID);
					} else JSONObj[i].eidtable = false;
					JSONObj[i].className = (!JSONObj[i].editable) ? 'noteditableClassroomEvent' : 'editableClassroomEvent';
					
					calendar.fullCalendar('unselect');
					eventsToDraw.push(JSONObj[i]);
				}
			}
			
			if (selectedIndex>-1) {
				if (JSONObj[selectedIndex].editable) setSelectedEvent(JSONObj[selectedIndex]);
			}
			
			$j('a.noteditableClassroomEvent').on ('click', function(event){
				event.preventDefault();
			});
			
			/**
			 * resolve the promise returning the events to be rendered
			 */
			deferred.resolve(eventsToDraw);
			
		}
	}).fail(deferred.reject).always(function() {
		if (getSelectedEvent()==null) setCanDelete(false);
		UIEvents = [];
		UIDeletedEvents = [];
	});
	
	return deferred.promise();
}

function millisecondsToHourMin(millis) {
	var minutes = Math.floor (millis/(1000*60));
	var hours = (Math.floor (minutes / 60)) + ''; // converted to string by +''
	var realminutes = (minutes % 60)+ ''; // converted to string by +''
	// zero-pad strings to a fixed length of 2
	hours = (hours.length < 2) ? '0'+hours : hours;
	realminutes = (realminutes.length < 2) ? '0'+realminutes : realminutes;
	
	return hours+':'+realminutes;
}

function updateAllocatedHours(durationDelta, lessonsDelta) {
	if ($j('#allocated_hours').length>0 && $j('#allocated_hours').text().length>0) {
		var allocatedDuration = moment.duration($j('#allocated_hours').text());
		if (lessonsDelta>=0) {
			allocatedDuration.add(durationDelta);
		} else if (lessonsDelta<0) {
			allocatedDuration.subtract(durationDelta);
		}
		$j('#allocated_hours').text(millisecondsToHourMin(allocatedDuration.asMilliseconds()));
	}
	
	if ($j('#lessons_count').length>0) {
		$j('#lessons_count').text(parseInt($j('#lessons_count').text())+lessonsDelta);
	}
}

/**
 * check if the passed events overlaps with some other event of the same tutor
 * by looping the local UIEvents array
 * 
 * @param event
 * @return boolean
 */
function checkTutorOverlapOnUIEvents(event) {
	if (objectSize(UIEvents)>0) {
		
		newStart = moment(event.start);
		newEnd = moment(event.end);
		
		for (var prop in UIEvents) {
			if (UIEvents.hasOwnProperty(prop)) {
				var currentEvent = UIEvents[prop];
			} else continue;
			
			loadedStart = moment(currentEvent.start);
			loadedEnd = moment(currentEvent.end);
			
			if (event.tutorID==currentEvent.tutorID && event.eventID != currentEvent.eventID &&
				   ( loadedStart.isSame(newStart) || loadedEnd.isSame(newEnd)   ||
					 loadedStart.isSame(newEnd)   || loadedEnd.isSame(newStart) ||
					(newStart.isBefore(loadedStart) && newEnd.isAfter(loadedStart)) ||
					(newStart.isAfter(loadedStart)  && newStart.isBefore(loadedEnd))
			   )) {
				
				prepareTutorOverlapDialog({
					instanceName : ($j('#instancesList').length>0) ? ($j('#instancesList option[value='+event.instanceID+']').text()) : '',
					id_utente_tutor : event.tutorID,
					date: newStart.format('L'),
					start: loadedStart.format('HH:mm'),
					end: loadedEnd.format('HH:mm')
				});
				
				return true;
			}
		}
	}
	return false;
}

/**
 * check if the passed events overlaps with some other event of the same tutor
 * by making an ajax call to the server and returing its promise
 * 
 * @param event
 * @return jQuery promise
 */
function checkTutorOverlapOnServer(event) {
	return $j.ajax({
		type	:	'GET',
		url		:	'ajax/checkTutorOverlap.php',
		data	:	event,
		dataType:	'json'
	}).done (function(JSONObj){
		if ('undefined' != typeof JSONObj.isOverlap) {
			if (JSONObj.isOverlap==true) {
				if (UIDeletedEvents.indexOf(JSONObj.data.module_classagenda_calendars_id)!=-1) {
					/**
					 * If the server reports an overlap on an event that has been
					 * deleted but not saved yet, then force it not to be an overlap
					 */
					JSONObj.isOverlap = false;
				} else if ('undefined' != typeof UIEvents[JSONObj.data.module_classagenda_calendars_id]) {
					/**
					 * If the server reports an overlap on an event that has been
					 * moved or resized but not saved yet, then check it against the UIEvents array
					 */
					JSONObj.isOverlap = checkTutorOverlapOnUIEvents(event);
				} else {
					prepareTutorOverlapDialog(JSONObj.data);
				}
				
			}
		}
	}).fail(function() {});
}

/**
 * Set dialog text with overlapping found event data
 * 
 * @param event
 */
function prepareTutorOverlapDialog(event) {
	$j('#overlapTutorName').text(getTutorFromSelect(event.id_utente_tutor));
	if ('undefined' != typeof event.instanceName) {
		$j('#overlapInstanceName').html(($j('#instancesList').length>0) ? event.instanceName : '');
	}
	if ('undefined' != event.date)  {
		$j('#overlapDate').text(event.date);
	}
	if ('undefined' != event.start)  {
		$j('#overlapStartTime').text(event.start);
	}
	if ('undefined' != event.end)  {
		$j('#overlapEndTime').text(event.end);
	}
}

/**
 * prepares the object needed by checkTutorOverlap
 * 
 * @param event
 */
function prepareDataForCheckTutorOverlap(event) {
	var theEventID = null;
	if ('undefined' != typeof event.eventID) theEventID = event.eventID;
	else if ('undefind' != typeof event.id) theEventID = event.id;
	
	return ({
			start: event.start.format(),
			end  : event.end.format(),
			tutorID: parseInt(event.tutorID),
			instanceID : getSelectedCourseInstance(),
			eventID : theEventID
	});
}

/**
 * check if the passed events overlaps with some other event of the same tutor
 * in 2 steps:
 * 1. first check on local UIEvents array and if no overlaps are found
 * 2. ask the server to check against the events stored in the DB with an ajax call
 * 
 * @param event
 * @returns jQuery promise
 */
function checkTutorOverlap(event) {
	if (checkTutorOverlapOnUIEvents(event)) {
		var aDeferred = $j.Deferred();
		aDeferred.resolve({
			isOverlap : true
		});
		return aDeferred.promise();
	} else {
		return checkTutorOverlapOnServer(event);
	}
}

/**
 * deletes the select calendar event
 */
function deleteSelectedEvent() {
	var selectedDuration = null;
	var eventID = null;
	calendar.fullCalendar('removeEvents', function (clEvent) {
		if (typeof clEvent.isSelected != "undefined" && clEvent.isSelected==true) {
			if (selectedDuration==null) {
				selectedDuration = moment.duration();
				selectedDuration.add(clEvent.end.subtract(clEvent.start));
			}
			if (eventID == null) {
				if ('undefined' != typeof clEvent.eventID) eventID = clEvent.eventID; 
				else if ('undefined' != typeof clEvent.id) eventID = clEvent.id+'';
			}
			return true;
		} else return false;
	});
	
	setCanDelete(false);
	if (!mustSave) setMustSave(true);
	if (selectedDuration != null) updateAllocatedHours(selectedDuration, -1);
	if (eventID != null) {
		/**
		 * add event to UIDeletedEvents array only if
		 * it was not a new event and has not been previously added
		 */
		if (eventID.search('tmp_')==-1 && UIDeletedEvents.indexOf(eventID)==-1) UIDeletedEvents.push(eventID);
		if ('undefined' != typeof UIEvents[eventID]) delete UIEvents[eventID];
	}
}

/**
 * opens up the dialog to set and send the email
 * reminder to subscribed students
 */
function reminderSelectedEvent() {
	
	var selectedEvent = getSelectedEvent();
	
	if (userType==AMA_TYPE_SWITCHER && ('undefined' == typeof selectedEvent.id || mustSave)) {
		// ask to save events if needed
		jQueryConfirm('#confirmDialog', '#reminderNonSavedEventquestion',
				function() {
					$j.when(saveClassRoomEvents()).then(function() {
						displayReminderDialog(getSelectedEvent());
					});
				},
				function() { return null; }
		);
	} else displayReminderDialog(selectedEvent);
}

function displayReminderDialog(selectedEvent) {

	if (reminderDialog == null) reminderDialog = prepareReminderDialog('#reminderDialog');
	
	$j.ajax({
				type	:	'GET',
				url		:	'ajax/getEventReminderForm.php',
				dataType:	'json',
				beforeSend : function() { $j('#reminderDialogContent').html(''); },
				data : { eventID: selectedEvent.id }
	}).
	done (function(JSONObj){
		if ('undefined' != typeof JSONObj) {
			if (JSONObj.status=='OK') {
				$j('#reminderDialogContent').html(JSONObj.html);
				if (oFCKeditor==null) {
					oFCKeditor = new FCKeditor( 'reminderEventHTML' );
					oFCKeditor.BasePath = HTTP_ROOT_DIR+'/external/fckeditor/';
					oFCKeditor.Width = '100%';
					oFCKeditor.Height = '350';
					oFCKeditor.ToolbarSet = 'Basic';
				}
				oFCKeditor.ReplaceTextarea();
				setTimeout(function () {
					if ('undefined' != typeof FCKeditorAPI) {
						FCKeditorAPI.GetInstance(oFCKeditor.InstanceName).Focus();
					}
				},350);
				
				$j('#reminderOkButton').show();
				reminderDialog.dialog('open');
				
			} else {
				showHideDiv('',JSONObj.html, false);
			}
		} else showHideDiv('','Unknow error', false); 
	});	
}
/**
 * prepares the reminder dialog to show the form
 */
function prepareReminderDialog(id) {
	var okLbl = $j(id + ' .confirmOKLbl').html();
	var cancelLbl = $j(id + ' .confirmCancelLbl').html();
	
	if ($j(id + ' li.tooltip').length>0) {
		/**
		 * set the tooltip on the legend
		 */
		$j(id + ' li.tooltip').tooltip();
		/**
		 * set the double click handler for the legend
		 */
		$j(id + ' li.tooltip').on('dblclick', function() {
			if ('undefined' != typeof FCKeditorAPI && oFCKeditor!=null) {
				FCKeditorAPI.GetInstance(oFCKeditor.InstanceName).InsertHtml($j(this).text());
			}
		});
	}
	
	return $j(id).dialog({
		resizable : false,
		autoOpen : false,
		width: "80%",
		modal : true,
		show: {
			effect: "fade",
			easing: "easeInSine", 
			duration: 250
		},
		hide: {
			effect: "fade",
			easing: "easeOutSine", 
			duration: 250
		},
		buttons : [ {
			text : okLbl,
			id   : 'reminderOkButton',
			click : function() {
				/**
				 * if there's a form inside the passed div id submit it
				 */
				if ($j(id + ' form').length>0) {
					/**
					 * run required fields validation before submit
					 */
					if ($j(id + ' form input[type="submit"]').length>0) {
						// get form submit button onclick code
						var onClickDefaultAction = $j(id + ' form input[type="submit"]').attr('onclick');
						// execute it, to hava ADA's own form validator
						var okToSubmit = (onClickDefaultAction.length > 0) ? new Function(onClickDefaultAction)() : false;
						// and if ok submit the form
						if (okToSubmit) {
							var that = this;
							$j('#reminderEventHTML').val(FCKeditorAPI.GetInstance(oFCKeditor.InstanceName).GetHTML(true));
							$j.when(saveAndSendReminder($j(id + ' form').serialize())).
								done (function(JSONObj) {
									if (JSONObj && JSONObj.status=='OK') {
										$j(that).dialog("close");
										/**
										 * check if the selected event has a reminder
										 */
										$j.when(checkReminderSent()).always(function() {
											$j('#reminderButton').button({ disabled: !canDelete });
										});
									}
								});
						}
					}
				}
			}
		}, {
			text : cancelLbl,
			id   : 'reminderCancelButton',
			click : function() {
				$j(this).dialog("close");
			}
		} ]
	});	
}

/**
 * handles reminder saving and sending with an ajax call
 */
function saveAndSendReminder(data) {
	return $j.ajax({
				type	:	'POST',
				url		:	'ajax/saveReminder.php',
				data	:	data,
				dataType:	'json'
	}).done (function(JSONObj){
		if (JSONObj) {
			showHideDiv('',JSONObj.msg,JSONObj.status=='OK');
			if ('undefined' != typeof JSONObj.reminderID && parseInt(JSONObj.reminderID)>0) {
				$j.ajax({
					type	: 'POST',
					url		: 'ajax/sendReminder.php',
					data	: { reminderID : JSONObj.reminderID },
					dataType: 'html'
				}).done(function (html) {});
			}
		} else showHideDiv('','Unknown error', false);
	});
}

/**
 * checks (and possibly gets content) if a
 * reminder for the selected event exists in the DB
 */
function checkReminderSent() {
	var animDuration = 200;
	
	$j.when($j('.reminderDetailsContainer').slideUp(animDuration/2)).then(
		function() {
			var selectedEvent = getSelectedEvent();
			if (!isCheckingReminder && selectedEvent != null) {
				$j('.reminderDetailsContainer').remove();
				return $j.ajax({
					type	:	'GET',
					url		:	'ajax/checkReminderSent.php',
					data	:	{ reminderEventID: selectedEvent.id },
					dataType:	'json',
					beforeSend : function () { isCheckingReminder = true; }
				}).done (function(JSONObj){			
					if ('undefined' != typeof JSONObj && 'undefined' != typeof JSONObj.html) {
						if ($j('#reminderButton').is(':visible')) $j('#reminderButton').slideUp(animDuration/2);
						$j('#reminderButtonContainer').prepend(JSONObj.html);
						$j('.reminderDetailsContainer').find('button').button();
						$j('.reminderDetailsContainer').slideDown(animDuration, function() { isCheckingReminder = false; });
						$j('.reminderDetailsContainer').append(JSONObj.content);
					} else {
						if (!$j('#reminderButton').is(':visible')) $j('#reminderButton').slideDown(animDuration);
						isCheckingReminder = false;
					}
				}).fail(function() { isCheckingReminder = false; });
			} else {
				if (!$j('#reminderButton').is(':visible')) return $j('#reminderButton').slideDown(animDuration);
			}
		}
	);
}

/**
 * shows the reminder content loaded by checkReminderSent
 */
function openReminder(id) {	
	if (reminderDialog == null) reminderDialog = prepareReminderDialog('#reminderDialog');	
	$j('#reminderDialogContent').html($j(id).html());
	$j('#reminderOkButton').hide();
	reminderDialog.dialog('open');
}

/**
 * sets the mustSave variable
 * 
 * @param status boolean, true to enable save button
 */
function setMustSave (status) {
	mustSave = status;
	$j('#saveCalendar').button({ disabled: !mustSave });	
}

/**
 * sets the canDelete variable, used for enable and disable
 * the #deleteButton and #reminderButton
 * 
 * @param status boolean, true to enable delete button
 */
function setCanDelete (status) {
	canDelete = status;
	$j('#deleteButton').button({ disabled: !canDelete });
	$j('#reminderButton').button({ disabled: !canDelete });
}

/**
 * sets the text of the modal dialogù
 * 
 * @param windowId id of the div containing the dialog
 * @param spanId id of the span containing the text to set
 */
function setModalDialogText (windowId, spanId) {
	// hides all spans that do not contain a variable
	$j(windowId + ' span').not('[id^="var"]').hide();
	// shows the passed span that holds the message to be shown
	$j(spanId).show();
	$j(spanId).children().show();
}

function moveInsideCalendarHeader (elementID) {
	var targetElement = '#classcalendar > .fc-toolbar';
	var customClass = 'fc-custom';
	
	var moveElement = false;
	
	if ($j(targetElement).length>0 && $j('#'+elementID).length>0) {
		
		/**
		 * add a customClass div to the calendar toolbar before last children
		 */
		if ($j(targetElement + ' > .' + customClass).length<=0) {
			var childCount = $j(targetElement).children().length;
			
			$j(targetElement+' :nth-child('+childCount+')').before('<div class="'+customClass+'"></div>');
		}
		
		/**
		 * clone, inspect and remove passed element
		 */
		var cloned = $j('#'+elementID).clone(true);
		var clonedID = elementID+'-cloned';
		// change cloned id attr
		$j(cloned).attr('id',clonedID);
		
		if ($j('#'+elementID).is('input')) {
			// change the cloned name attr
			$j(cloned).attr('name',$j(cloned).attr('name')+'-cloned');
			// if passed ID is an input and has a label, clone and move the label as well
			if ($j('label[for="'+elementID+'"]').length>0) {
				var clonedLabel = $j('label[for="'+elementID+'"]').clone(true);
				// change the cloned label for attr
				$j(clonedLabel).attr('for',clonedID);
				 if (moveElement) $j('label[for="'+elementID+'"]').remove();
				 else $j('label[for="'+elementID+'"]').hide();
			}
		}
				
		if (moveElement) $j('#'+elementID).remove();
		else $j('#'+elementID).hide();
		
		targetElement += ' > .' + customClass;
		$j(targetElement).html(cloned);
		/**
		 * add the clonedLabel if it's there
		 */
		if ('undefined' != typeof clonedLabel) $j(targetElement).append(clonedLabel);
	}
}

function addToUIEvents(event) {
	if (event.eventID == null) {
		/**
		 * if it's a new event, generate a temporary ID
		 * using an UTC timestamp.
		 * Code is made compatibile with IE8 with the Date.now check
		 */
	    var timestamp = (!Date.now) ? new Date().getTime() : Date.now();
		event.eventID = 'tmp_'+timestamp;
	}
	
	UIEvents[event.eventID] = event;
	
	return event.eventID;
}

/**
 * shows a confirmation dialog and waits for user action
 * 
 * @param id id of the div containing the dialog
 * @param questionId id of the span containing the text (question)
 * @param OKcallback ok button callback
 * @param CancelCallBack cancel button callback
 */
function jQueryConfirm(id, questionId, OKcallback, CancelCallBack) {
	var okLbl = $j(id + ' .confirmOKLbl').html();
	var cancelLbl = $j(id + ' .confirmCancelLbl').html();

	setModalDialogText(id, questionId);

	$j(id).dialog({
		resizable : false,
		width: "auto",
		modal : true,
		show: {
			effect: "fade",
			easing: "easeInSine", 
			duration: 250
		},
		hide: {
			effect: "fade",
			easing: "easeOutSine", 
			duration: 250
		},
		buttons : [ {
			text : okLbl,
			click : function() {
				OKcallback();
				$j(this).dialog("close");
			}
		}, {
			text : cancelLbl,
			click : function() {
				CancelCallBack();
				$j(this).dialog("close");
			}
		} ]
	});
}

function objectSize (obj) {
	var size=0;
	for (key in obj){ if (obj.hasOwnProperty(key)) size++; }
	return size;
}
