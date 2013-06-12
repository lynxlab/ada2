<?php

/**
 * function exitWith_JSON_Error
 * 
 * Used to exit from script execution returning a json error object. 
 * @param string $error_msg - the text string to display
 * @return void 
 * @author vito
 */
function exitWith_JSON_Error($error_msg,$error_code=1)
{
    $json_string = '{"error":'.$error_code.',"message":"'.$error_msg.'"}';
    print $json_string;
    exit();
}

function thisChatMessageToJSON($chat_message)
{
    $json_string = '{
    	"id": '.$chat_message['id_messaggio'].',
    	"type":"'.$chat_message['tipo'].'",
    	"time":"'.ts2tmFN($chat_message['data_ora']).'",
    	"sender":"'.$chat_message['username'].'",
		"text":"'.$chat_message['testo'].'"    	
	},';  

    return $json_string;
}
?>