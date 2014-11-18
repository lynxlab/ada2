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

var hasVenues = false;

function initDoc() {
	
	if ($j('#classcalendar').length>0) {
	    var fullcal = $j('#classcalendar').fullCalendar({
	        // put your options and callbacks here
	    	theme 	 : true,	// enables jQuery UI theme
	    	firstDay : 1,		// monday is the first day
	    	minTime  : "08:00",	// events starts at 08AM ,
	    	maxTime  : "20:00",	// events ends at 08PM
	    	weekends : false,	// hide weekends
	    	defaultEventMinutes: 60,
	    	height : 590,
	    	editable : true,
	    	selectable : true,
	    	selectHelper : true,
	    	defaultView : 'agendaWeek',
//	    	select :	function( startDate, endDate, allDay, jsEvent, view ) {
//	    					appointments.addAppointment ( startDate, endDate, allDay );
//	    	},
//	    	eventClick :function( event, jsEvent, view ) { 
//	    					appointments.eventClick ( event );
//	    	},
//	    	eventDrop:  function( event, dayDelta, minuteDelta, allDay, revertFunc ) {
//	    					appointments.moveAppointment ( event, dayDelta, minuteDelta, allDay, revertFunc ); 
//	    	},
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			events: [
{
	title: 'Classe SUPER-ABILE',
	start: '2014-11-21T09:30:00',
	end: '2014-11-21T13:30:00'
},
{
	title: 'Classe SUPER-ABILE',
	start: '2014-11-20T09:30:00',
	end: '2014-11-20T13:30:00'
},
{
	title: 'Classe SUPER-ABILE',
	start: '2014-11-19T09:30:00',
	end: '2014-11-19T13:30:00'
},
{
	title: 'Classe SUPER-ABILE',
	start: '2014-11-18T09:30:00',
	end: '2014-11-18T13:30:00'
},


{
	title: 'Classe SUPER-ABILE',
	start: '2014-11-11T09:30:00',
	end: '2014-11-11T13:30:00'
},
{
	title: 'Classe SUPER-ABILE',
	start: '2014-11-10T09:30:00',
	end: '2014-11-10T13:30:00'
},
						{
							title: 'Classe SUPER-ABILE',
							start: '2014-11-12T09:30:00',
							end: '2014-11-12T13:30:00'
						},
						{
							title: 'Classe SUPER-ABILE',
							start: '2014-11-12T15:00:00',
							end: '2014-11-12T18:00:00'
						},
						{
							title: 'Classe SUPER-ABILE',
							start: '2014-11-13T9:30:00',
							end: '2014-11-13T13:30:00'
						},
						{
							title: 'Classe SUPER-ABILE',
							start: '2014-11-13T15:00:00',
							end: '2014-11-13T18:00:00'
						},
						{
							title: 'Classe del corso di prova',
							start: '2014-11-10T14:00:00',
							end: '2014-11-10T18:00:00'
						},
						{
							title: 'Classe del corso di prova',
							start: '2014-11-11T14:00:00',
							end: '2014-11-11T18:00:00'
						},
						{
							title: 'Classe del corso di prova',
							start: '2014-11-12T12:00:00'
						},
					]
//			eventSources : [  {
//			                	url : GCAL_HOLIDAYS_FEED,
//								className: 'holiday'
//							  },
//							  {
//				                url : HTTP_ROOT_DIR + "/comunica/ajax/getProposals.php",
//				                // WARNING: js code is based on these classnmes, do not change them!
//				                className : 'loadedEvents proposal',
//				                editable  : 	false,
//				                allDayDefault : false			                
//							  },
//							  {
//				                url : HTTP_ROOT_DIR + "/comunica/ajax/getProposals.php?type=C",
//				                // WARNING: js code is based on these classnmes, do not change them!			                
//				                className : 'loadedEvents confirmed',
//				                editable  : 	false,
//				                allDayDefault : false			                
//							  }	
//			                ]
	    });
	    
	    $j('#saveCalendar').button();
	    $j('#saveCalendar').on('click', function ( event ) {
    		event.preventDefault();
	    });
	}
	
	// hook select change to update number of students
	updateStudentCountOnSelectChange();
	// trigger onchange event to update number of students when page loads
	if ($j('#instancesList').length>0) $j('#instancesList').trigger('change');
	
	hasVenues = $j('#venuesList').length>0;
	if (hasVenues) {
		updateClassroomsOnVenueChange();
		$j('#venuesList').trigger('change');
	} else {
		$j('#classcalendar').css('width','100%');
	}
	
}

function updateClassroomsOnVenueChange() {
	if (hasVenues) {
		$j('#venuesList').on('change', function(){
			$j.ajax({
				type	:	'GET',
				url		:	'ajax/getClassrooms.php',
				data	:	{ venueID: $j(this).val() },
				dataType:	'html'
			}).done (function(htmlcode){
				$j('#classroomlist').html(htmlcode);
			});
		});
	}
}

/**
 * set instancesList select dropdown to update studentcount
 */
function updateStudentCountOnSelectChange() {
	if ($j('#instancesList').length>0 && $j('#studentcount').length>0) {
		$j('#instancesList').on('change', function(){
			$j.ajax({
				type	:	'GET',
				url		:	'ajax/getStudentsCount.php',
				data	:	{ instanceID: $j(this).val() },
				dataType:	'json'
			}).done (function(JSONObj){
				if (JSONObj) {
					$j('#studentcount').html(JSONObj.value);
				}
			});
		});
	}
}
