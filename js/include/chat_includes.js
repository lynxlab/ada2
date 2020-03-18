/**
 * variables identifying chat dom elements.
 */
var TOP_CHAT_DIV         = 'top';
var READ_MESSAGES_DIV    = 'messages';
var SEND_MESSAGE_DIV     = 'sendmessage';
var SEND_MESSAGE_INPUT   = 'chatmessage';
var CONTROL_CHAT_DIV     = 'controlchat';
var EXIT_CHAT_DIV        = 'exitchat';
var USER_STATUS_DIV      = 'user_status';
var USER_ACTIONS_DIV     = 'user_actions';
var USER_ACTIONS_SELECT  = 'user_actions_select';
var USERS_LIST_DIV       = 'users_list';
var USERS_LIST_SELECT    = 'users_list_select';
var INVITED_USERS_LIST_UL  = 'invited_users_list';
var CHATROOM_INFO_DIV    = 'chatroom_info';
var AUTOSCROLL_CHECKBOX  = 'autoscroll';
var REFRESH_CHAT_BUTTON  = 'refresh_chat';
var DEBUG_DIV            = 'debug';
var DEBUG_LOG_ENABLED    = false;

/**
 * Actions to perform after a successfull exit from the chat
 */
var CLOSE_CHAT_WINDOW = 0;
var REDIRECT_TO_PRACTITIONER_EXIT_CHAT_URL = 1;

/**
 * variables used to store a reference to the two periodical
 * executers on which this chat is based.
 * these two variables are used in exitChat function to stop
 * the periodical executers before exiting the chat.
 */
var READ_MESSAGES_PERIODICAL_EXECUTER = null;
var CONTROL_CHAT_PERIODICAL_EXECUTER  = null;

/**
 * variables used to mantain the periodical executers periods,
 * in seconds.
 */

// test periodical executer
var READ_MESSAGES_PERIODICAL_EXECUTER_TIME_INTERVAL = 1;
// test periodical executer
var SECONDS_SINCE_LAST_READ_MESSAGE = 0;

var CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE = 1;
var CONTROL_CHAT_TIME_INTERVAL  = 60;//20;

///**
// Non sono utilizzate, l'idea era di incrementare l'intervallo di tempo tra le letture dei messaggi
// ogni volta che si esegue un controllo dei messaggi ricevuti e non ce ne sono.
// Se invece si ricevono dei messaggi, l'intervallo di tempo tra le due letture deve diminuire.
var MINIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE = 2;
var MAXIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE = 10;
var SECONDS_TO_ADD = 2;
//*/

var LAST_READ_MESSAGE_ID    = 0;
var USER_ACTIONS_FILLED     = false;

/**
 * URLs of the PHP scripts onto which perform AJAX requests.
 */
/*
 * var HTTP_ROOT_DIR is imported from ada_config
 */
var READ_CHAT_URL      = HTTP_ROOT_DIR + '/comunica/readChat.php';
var CONTROL_CHAT_URL   = HTTP_ROOT_DIR + '/comunica/controlChat.php';
var SEND_MESSAGE_URL   = HTTP_ROOT_DIR + '/comunica/sendChatMessage.php';
var EXIT_CHAT_URL      = HTTP_ROOT_DIR + '/comunica/quitChatroom.php';
var CONTROL_ACTION_URL = HTTP_ROOT_DIR + '/comunica/controlChatAction.php';

var PRACTITIONER_EXIT_CHAT_URL = HTTP_ROOT_DIR + '/tutor/eguidance_tutor_form.php';

/**
 *
 */
var GET_AJAX_REQUEST_EXECUTION_TIME = false;

var ARGUMENTS      = null;
var HOW_MANY_READS = 0;

/**
 * function startChat()
 * called on window load, starts the two periodical executers used to
 * obtain chat messages and chatroom control panel.
 */
function startChat()
{
	ARGUMENTS = getArguments();
	if (DEBUG_LOG_ENABLED) $(DEBUG_DIV).insert('ARGUMENTS: ' + JSON.stringify(ARGUMENTS) + '<br />');

// test periodical executer
//	READ_MESSAGES_PERIODICAL_EXECUTER = new PeriodicalExecuter(readMessages, CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE);
// test periodical executer
	// Start periodical executers for reading chat messages
	READ_MESSAGES_PERIODICAL_EXECUTER = new PeriodicalExecuter(shouldReadMessages, READ_MESSAGES_PERIODICAL_EXECUTER_TIME_INTERVAL);
	CONTROL_CHAT_PERIODICAL_EXECUTER  = new PeriodicalExecuter(controlChat, CONTROL_CHAT_TIME_INTERVAL);


	readMessages();
	controlChat();

}

/**
 * function loadTopChat()
 * called by function startChat(), obtains the data to display into
 * the chat header by performin an AJAX request.
 *
 */
function loadTopChat()
{
	$(TOP_CHAT_DIV).insert('TOP CHAT');
}

/**
 * function readMessages()
 * periodically executed by READ_MESSAGES_PERIODICAL_EXECUTER, with
 * period CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE, obtains chat messages by performing
 * an AJAX request and updates READ_MESSAGES_DIV content.
 */
function readMessages() {

	if (GET_AJAX_REQUEST_EXECUTION_TIME) {
		var request_time = Date.now();
	}

	var prevLast = LAST_READ_MESSAGE_ID;
	$j.ajax({
		method: 'POST',
		data: {
			chatroom: ARGUMENTS.chatroomId,
			ownerId : ARGUMENTS.ownerId,
			studentId: ARGUMENTS.studentId,
			lastMsgId: LAST_READ_MESSAGE_ID
		},
		url: READ_CHAT_URL,
		beforeSend: function() {
			if (DEBUG_LOG_ENABLED) console.groupCollapsed('readMessages async')
		}
	})
	.done(function(ajaxresp){
		if (DEBUG_LOG_ENABLED) console.log(ajaxresp);

		if (GET_AJAX_REQUEST_EXECUTION_TIME) {
			 var response_time = Date.now();
		}
		if (ajaxresp.error == 0) {
			if (ajaxresp.data.length > 0) {
				logMessageOnScreen('Letti ' + ajaxresp.data.length + ' messaggi');
				displayMessages(ajaxresp.data);
				if (prevLast > 0) {
					$j('#msg_'+LAST_READ_MESSAGE_ID).hide().fadeIn('slow');
				}
			}
		}
		else {
			logMessageOnScreen('readMessages json error: ' + ajaxresp.error);
			handleError('readMessages', ajaxresp);
			// displayErrorMessage('readMessages', json);
		}
	})
	.fail(function(response) {
		if (DEBUG_LOG_ENABLED) console.log('FAIL', response);
		displayErrorMessage('readMessages', null);

	})
	.always(function() {
		if (DEBUG_LOG_ENABLED) console.groupEnd();
	});
}

/**
 * function controlChat()
 * periodically executed by CONTROL_CHAT_PERIODICAL_EXECUTER, with
 * period CONTROL_CHAT_TIME_INTERVAL, obtains chat messages by performing
 * an AJAX request and updates CONTROL_CHAT_DIV content.
 */
function controlChat()
{
	$j.ajax({
		method: 'POST',
		data: { chatroom: ARGUMENTS.chatroomId },
		url: CONTROL_CHAT_URL
	})
		.done(function (json) {
			if (json.error == 0) {
				updateControlChatData(json);
			}
			else {
				//logMessageOnScreen('controlChat json error: ' + json.error);
				handleError('controlChat', json);
				//displayErrorMessage('controlChat', json);
			}
		})
		.fail(function () {
			displayErrorMessage('controlChat', null);
		})
		.always(function () {
			if ($j('#' + REFRESH_CHAT_BUTTON).hasClass('disabled')) {
				$j('#' + REFRESH_CHAT_BUTTON).removeClass('disabled');
				$j('.refresh.icon').removeClass('loading');
			}
		});
}

/**
 * function sendMessage()
 * executed when the user sends a message: performs ana AJAX request.
 */
function sendMessage()
{
	/*
	 * In order to be sent, a message must not be empty.
	 */
	if ($F(SEND_MESSAGE_INPUT).blank())
	{
//		var div_error = new Element('div', {'class':'javascript_error'});
//		div_error.insert('Devi inserire del testo, per inviare un messaggio.');
//		$(READ_MESSAGES_DIV).insert(div_error);
		return;
	}
	//var message_to_send = '&message_to_send='+encodeURIComponent($F(SEND_MESSAGE_INPUT));
	var message_to_send = $F(SEND_MESSAGE_INPUT);

//DISABILITIAMO L'INVIO DI ULTERIORI MESSAGGI FINO A CONCLUSIONE DELL'OPERAZIONE DI INVIO.
	$(SEND_MESSAGE_INPUT).disable();
	$j('#'+SEND_MESSAGE_DIV).siblings().find('.sendmessage.button').addClass('disabled');

	if (GET_AJAX_REQUEST_EXECUTION_TIME)
	{
		var request_time = Date.now();
	}

	new Ajax.Request(SEND_MESSAGE_URL, {
		method: 'Post',
		parameters: {chatroom:ARGUMENTS.chatroomId, message_to_send:message_to_send},
		onComplete: function(transport) {
			var json = transport.responseText.evalJSON(true);

			if (GET_AJAX_REQUEST_EXECUTION_TIME)
			{
				var response_time = Date.now();
			}

		    if ( json.error == 0 )
		    {
				logMessageOnScreen('messaggio inviato');
				$(SEND_MESSAGE_INPUT).clear();
				// Quando invio un messaggio, faccio ripartire il tempo di lettura messaggi dal minimo.
				CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE = MINIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE;

				readMessages();
				$(SEND_MESSAGE_INPUT).enable();
				$j('#'+SEND_MESSAGE_DIV).siblings().find('.sendmessage.button').removeClass('disabled');
				$(SEND_MESSAGE_INPUT).focus();
			}
			else
			{
				logMessageOnScreen('sendMessages json error: ' + json.error);
				handleError('sendMessages', json);
				//displayErrorMessage('sendMessages', json);
				$(SEND_MESSAGE_INPUT).enable();
				$j('#'+SEND_MESSAGE_DIV).siblings().find('.sendmessage.button').removeClass('disabled');
				$(SEND_MESSAGE_INPUT).focus();
			}

		},
		onFailure: function() {
			displayErrorMessage('sendMessages', null);
				$(SEND_MESSAGE_INPUT).enable();
				$j('#'+SEND_MESSAGE_DIV).siblings().find('.sendmessage.button').removeClass('disabled');
				$(SEND_MESSAGE_INPUT).focus();
		}
	});

}

/**
 * function exitChat()
 * executed when the user quits the chat, by closing the window or by
 * clicking Exit Chat button. It stops the two periodical executers:
 * READ_MESSAGES_PERIODICAL_EXECUTER and CONTROL_CHAT_PERIODICAL_EXECUTER,
 * performs an AJAX request.
 */
function exitChat(action, action_arguments)
{
// stoppare i periodical executers e poi fare una richiesta AJAX per aggiornare
// lo stato dell'utente all'interno del database
	READ_MESSAGES_PERIODICAL_EXECUTER.stop();
	CONTROL_CHAT_PERIODICAL_EXECUTER.stop();

	if (GET_AJAX_REQUEST_EXECUTION_TIME)
	{
		var request_time = Date.now();
	}

	new Ajax.Request(EXIT_CHAT_URL, {
		method: 'Post',
		parameters: {chatroom:ARGUMENTS.chatroomId,exit_reason:0},
		onComplete: function(transport) {
			var json = transport.responseText.evalJSON(true);

			if (GET_AJAX_REQUEST_EXECUTION_TIME)
			{
				var response_time = Date.now();
			}
		    if ( json.error == 0 )
		    {
		    	// vito, 20 mar 2009.
		    	//displayMessages(json.data);
		    	//alert(HOW_MANY_READS);
		    	if(action == REDIRECT_TO_PRACTITIONER_EXIT_CHAT_URL) {
		    		self.location = PRACTITIONER_EXIT_CHAT_URL+'?'+action_arguments;
		    	}
		    	else {
		    		self.close();
		    	}
			}
			else
			{
				logMessageOnScreen('exitChat json error: ' + json.error);
				handleError('exitChat', json);
				//displayErrorMessage('exitChat', json);
			}
		},
		onFailure: function() {
			displayErrorMessage('exitChat', null);
		}
	});
}

/**
 * function displayMessages(messages)
 * used to display messages and perform some additional tasks related
 * to message displaying such as autoscrolling
 */
function displayMessages(messages)
{
	/*
	 * Display each received message
	 */
	messages.each(displayMessage);
	/*
	 * Adjust scrolling, if autoscroll is on
	 */
//	if ($F(AUTOSCROLL_CHECKBOX) == 'on')
//	{
		$(READ_MESSAGES_DIV).scrollTop=$(READ_MESSAGES_DIV).scrollHeight;
//	}
	/*
	 * Update the time interval at which chat messages are retrieved
	 */
	updateReadChatInterval(messages.size());
}

/**
 * function displayMessage(message)
 * displays the message passed as argument in the chat
 */
function displayMessage(message)
{
	var this_message_class = 0;
	var this_message_text  = null;
	var result             = null;

  if(parseInt(message.id) > parseInt(LAST_READ_MESSAGE_ID)) {
  	LAST_READ_MESSAGE_ID = message.id;
  }

	// a volte legge due volte lo stesso messaggio, (problema di timing?)
	// con le righe seguenti evitiamo di mostrare dei doppioni
	var message_exists = 'msg_'+message.id;

	if ($(message_exists))
	{
		return;
	}
	// fine

	/*
	 * Check if current message contains a mood message:
	 * e.g. APPLAUSE, DISAGREE...
	 */
	if ((result = checkForMoodMessage(message.text)) != null)
	{
		this_message_class = getClassForMoodMessageType(result[0]);
		// result[1] contiene l'id dello studente a cui l'azione del mittente e' rivolta(eventualmente=0)
		// result[2] contiene l'id del messaggio a cui l'azione del mittente e' rivolta(eventualmente=0)

		this_message_text  = result[3];
	}
	/*
	 * Check if current message contains an operator message:
	 * e.g. USER KICKED, USER BANNED...
	 */
	else if((result = checkForOperatorMessage(message.text)) != null)
	{
		this_message_class = getClassForOperatorMessageType(result[0]);
		// result[1] contiene l'id dello studente a cui e' rivolta l'azione dell'operatore
		this_message_text  = result[2];
	}
	else if(checkForAdminMessage(message.sender))
	{
		this_message_class = 'admin_message';
		this_message_text  = message.text;
		controlChat();
	}
	/*
	 * Current message is a simple chat message sent by one of
	 * the users in the chatroom.
	 */
	else
	{
		this_message_class = getClassForChatMessage(message);
		this_message_text  = message.text;
	}

	/*
	 * Display current message.
	 */
	// div message dovrebbe avere id=id messaggio
	var div_message       = new Element('div',  {'id': 'msg_'+message.id, 'class': 'item '+this_message_class});
	var span_message_time = new Element('span', {'class':'message_time'});
	var span_user_name    = new Element('span', {'class':'user_name'});
	var span_message_text = new Element('span', {'class': 'message_text'});
	var item_header = new Element('div', {'class':'header'});

	span_message_time.insert(message.time);
	span_user_name.insert(message.sender);
	span_message_text.insert(this_message_text);

	item_header.insert(span_user_name);
	item_header.insert(span_message_time);
	div_message.insert(item_header);
	div_message.insert(span_message_text);

	$(READ_MESSAGES_DIV).insert(div_message);
}

/*
 * Check if message_text contains the ADA custom tag defined
 * to mark an user mood action like 'applause': if it is found,
 * return an array with:
 * 0. mood message type (integer value, defined in ada_config.php)
 * 1. the user id this message is addressed to (in case there is no target user, this value is 0)
 * 2. the message id this message refers to (in case there is no target message, this value is 0)
 * 3. the text this message carries
 *
 * If this message is not a mood message, return null
 */
function checkForMoodMessage(message_text)
{
	var regexp = /<mood type='([0-9]+)' touser='([0-9]+)' tomsg='([0-9]+)'>(.*)<\/mood>/;
	var result = regexp.exec(message_text);

	if (result != null)
	{
		return new Array(result[1], result[2],result[3],result[4]);
	}

	return null;
}

/*
 * Check if message_text contains the ADA custom tag defined
 * to mark an operator action like 'ban user': if it is found,
 * return an array with:
 * 0. action type (integer value, defined in ada_config.php)
 * 1. the user id this action is addressed to (in case there is no target user, this value is 0)
 * 2. the text this message carries
 *
 * If this message is not a mood message, return null
 */
function checkForOperatorMessage(message_text)
{
	var regexp = /<operator action='([0-9]+)' user='([0-9]+)'>(.*)<\/operator>/;
	var result = regexp.exec(message_text);
	if (result != null)
	{
		return new Array(result[1], result[2], result[3]);
	}

	return null;
}

/*
 * Check if message sender is ADA admin user and return true.
 *
 * If it is not, return false.
 */
function checkForAdminMessage(message_sender)
{
	if (message_sender == 'admin')
	{
		return true;
	}

	return false;
}

/*
 * This function shall return the appropriate CSS classname for
 * the mood message type passed as argument.
 *
 */
function getClassForMoodMessageType(message_type)
{
	// ci dovra' essere uno switch sul tipo di messaggio (intero)
	return 'mood_message';
}

/*
 * This function shall return the appropriate CSS classname for
 * the operator message type passed as argument.
 *
 */
function getClassForOperatorMessageType(message_type)
{
	// ci dovra' essere uno switch sul tipo di messaggio (intero)
	return 'operator_message';
}

/*
 * This function shall return the appropriate CSS classname for
 * the chat message passed as argument.
 *
 */
function getClassForChatMessage(message)
{
	if(message.tipo == 'P')
	{
		return 'private_message';
	}
	else if (message.tipo == 'C')
	{
		return 'message';
	}
}

function displayErrorMessage(functionName, json)
{
//	if (json == null)
//	{
//		$(READ_MESSAGES_DIV).insert('Failure performing AJAX request in: '+functionName+'().<br />');
//	}
//	else
//	{
//		//$(READ_MESSAGES_DIV).insert('Error '+json.error+' while performing AJAX request in: '+functionName+'().<br />');
//		var div_error = new Element('div', {'class':'php_error'});
//		div_error.insert(functionName+' returned this error: ' +json.message);
//		$(READ_MESSAGES_DIV).insert(div_error);
//	}
}

function logMessageOnScreen(text)
{
	if (DEBUG_LOG_ENABLED)
	{
		$(DEBUG_DIV).insert(text + '<br />');
	}
}
function updateControlChatData(data)
{
	/*
	 * Display user status in the chatroom.
	 */
	$(USER_STATUS_DIV).update();
	var div_user_status_label = new Element('div', {'class': 'user_status_label'});
	div_user_status_label.insert(data.user_status_label);
	$(USER_STATUS_DIV).insert(div_user_status_label);
	$(USER_STATUS_DIV).insert(data.user_status);

	/*
	 * Display chatroom control actions for the current user.
	 */
/*	if (!USER_ACTIONS_FILLED)
	{
		var div_user_options_label = new Element('div', {'class': 'options_list_label'});
		div_user_options_label.insert(data.options_list_label);
		$(USER_ACTIONS_DIV).insert(div_user_options_label);

		var actions_select = new Element('select', {'id':USER_ACTIONS_SELECT});
		$(USER_ACTIONS_DIV).insert(actions_select);

		data.options_list.each(addActionToUserActionSelect);

		USER_ACTIONS_FILLED = true;
	}
*/
	$(USERS_LIST_DIV).update();
//	$(INVITED_USERS_LIST_UL).update();

	var div_users_list_label = new Element('div', {'class': 'users_list_label ui small header'});
	div_users_list_label.insert(data.users_list_label);
	$(USERS_LIST_DIV).insert(div_users_list_label);

	var users_list_select = new Element('select', {'id': USERS_LIST_SELECT, 'size':'8'});
	$(USERS_LIST_DIV).insert(users_list_select);

	data.users_list.each(addUserToUserSelect);

//	data.invited_users_list.each(addUserToInvitedUsers);
}

function addActionToUserActionSelect(action)
{
	var opt = new Element('option', {'value': action.value});
	opt.insert(action.text);
	$(USER_ACTIONS_SELECT).insert(opt);
	//$(USER_ACTIONS_SELECT).insert('<option value="'+action.value+'">'+action.text+'</option>');
}

function addUserToUserSelect(user)
{
	var opt = new Element('option', {'value': user.id});
	opt.insert(user.nome + ' '+ user.cognome);
	$(USERS_LIST_SELECT).insert(opt);
//	$(USERS_LIST_SELECT).insert('<option value="'+user.id+'">'+user.username+'</option>');
}

function addUserToInvitedUsers(user)
{
	var li = new Element('li');
	li.insert(user.username);
	$(INVITED_USERS_LIST_UL).insert(li);
}

function getArguments()
{
	var passed_args = $('data').innerHTML.unescapeHTML();
	var retobj = { chatroomId: 0, ownerId: null, studentId: null };
	var passedObj = passed_args.length > 0 ? JSON.parse(passed_args) : {};
	return $j.extend({}, retobj, passedObj);
}

function executeControlAction()
{

	//alert('esegui azione di controllo: ' +$F(USER_ACTIONS_SELECT) + 'utente selezionato: '+$F(USERS_LIST_DIV));
	if (GET_AJAX_REQUEST_EXECUTION_TIME)
	{
		var request_time = Date.now();
	}

	var control_action = $F(USER_ACTIONS_SELECT);
	var user_id        = $F(USERS_LIST_SELECT);
//	alert(CONTROL_ACTION_URL+'?'+ARGUMENTS+'&action='+control_action+'&target_user='+user_id);
	var controlActionParameters = '&action='+control_action+'&target_user='+user_id;
//	alert(CONTROL_ACTION_URL+'?'+ARGUMENTS+controlActionParameters);

	new Ajax.Request(CONTROL_ACTION_URL+'?'+ARGUMENTS.chatroomId+controlActionParameters, {
		method: 'get',
		onComplete: function(transport) {
			var json = transport.responseText.evalJSON(true);

			if (GET_AJAX_REQUEST_EXECUTION_TIME)
			{
				var response_time = Date.now();
			}
		    if ( json.error == 0 )
		    {
		    	readMessages();
		    	//displayMessages(json.data);
		    	//alert('Azione eseguita: ' + controlActionParameters);
			}
			else
			{
				handleError('executeControlAction', json);
				//displayErrorMessage('executeControlAction', json);
			}
		},
		onFailure: function() {
			displayErrorMessage('executeControlAction', null);
		}
	});
}

// test periodical executer
function updateReadChatInterval(read_messages_number)
{
	if(read_messages_number == 0)
	{
		CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE += SECONDS_TO_ADD;
		if (CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE > MAXIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE)
		{
			CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE = MAXIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE;
		}
	}
	else
	{
		//var mps = Math.round(read_messages_number/CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE);
		//CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE -= mps;
		CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE -= read_messages_number;

		if(CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE < MINIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE)
		{
			CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE = MINIMUM_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE;
		}
	}
	// Debug only
	//$('debug').innerHTML = CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE;
}

// test periodical executer
function shouldReadMessages()
{
	if (SECONDS_SINCE_LAST_READ_MESSAGE < CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE)
	{
		SECONDS_SINCE_LAST_READ_MESSAGE++;
	}
	else if(SECONDS_SINCE_LAST_READ_MESSAGE >= CURRENT_TIME_INTERVAL_BETWEEN_TWO_READ_MESSAGE)
	{
		SECONDS_SINCE_LAST_READ_MESSAGE = 0;
		readMessages();
		// Debug only
		HOW_MANY_READS++;
	}
}

function handleError(function_name, error_object)
{
	if(error_object.error == 1)
	{
		displayErrorMessage(function_name, error_object);
	}
	else if(error_object.error == 2)
	{
		// stop periodical executer, exit chat
		READ_MESSAGES_PERIODICAL_EXECUTER.stop();
		CONTROL_CHAT_PERIODICAL_EXECUTER.stop();

		var div_message       = new Element('div',  {'class': 'php_error'});
		var span_message_time = new Element('span', {'class': 'message_time'});
		var span_user_name    = new Element('span', {'class': 'user_name'});
		var span_message_text = new Element('span', {'class': 'message_text'});

		span_message_time.insert();
		span_user_name.insert('ada chat');
		span_message_text.insert(error_object.message);

		div_message.insert(span_message_time);
		div_message.insert(span_user_name);
		div_message.insert(span_message_text);

		$(READ_MESSAGES_DIV).insert(div_message);
	}
}

function catchEnter(event)
{
	// Se l'utente ha premuto il tasto Enter, invia il messaggio
	if (event.keyCode==13)
	{
		sendMessage();
	}
}


/**
 * function refreshChat()
 *
 */
function refreshChat()
{
	if (!$j('#'+REFRESH_CHAT_BUTTON).hasClass('disabled')) {
		$j('#'+REFRESH_CHAT_BUTTON).addClass('disabled');
		$j('.refresh.icon').addClass('loading');
		readMessages();
		controlChat();
	}
}
