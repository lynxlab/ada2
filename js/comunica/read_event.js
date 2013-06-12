/**
 * Performs the steps required to enter the chatroom or the videochat
 * when the user click on the appropriate link in the appointment message.
 * 
 * @param id_course				the id of the course
 * @param id_course_instance    the id of the course instance
 * @param id_msg				the id of the appointment message
 * 
 * @return void
 */
function performEnterEventSteps(event, id_course, id_course_instance) {
		
	var windowOpenerLocationHref = HTTP_ROOT_DIR + '/browsing/view.php'
	                       + '?id_node=' + id_course + '_0'
	                       + '&id_course=' + id_course
	                       + '&id_course_instance=' + id_course_instance;
	var thisWindowLocationHref = HTTP_ROOT_DIR + '/comunica/enter_event.php'
				               + '?event=' + event
							   + '&id_course=' + id_course
 						       + '&id_course_instance=' + id_course_instance;
	                           
	window.opener.location.href = windowOpenerLocationHref;
	window.location = thisWindowLocationHref;
}
