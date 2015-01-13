<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define ('CONFIG_LOG_REPORT', true);

$GLOBALS['LogReport_Array'] = array ( 
    'provider'=>array('label'=>'provider' ,'show'=>true),
    'final_users'=>array('label'=>'utenti registrati' ,'show'=>true),
    'user_subscribed'=>array('label'=>'utenti iscritti' ,'show'=>true),
    'course'=>array('label'=>'tot. corsi' ,'show'=>true),
    'service_level' =>array('label'=>'service_level' ,'show'=>true),  //Warning: dont' change this key, because it fix course type.
    'sessions_started'=>array('label'=>'ed. iniziate' ,'show'=>true),
    'student_CompletedStatus_sessStarted'=>array('label'=>'std. status complt.' ,'show'=>true),
    'student_subscribedStatus_sessStarted'=>array('label'=>'std. status subscribed' ,'show'=>false),
    'student_CompletedStatus_sessStarted_Rate'=>array('label'=>'% complt.' ,'show'=>true),
    'sessions_closed'=>array('label'=>'ed. chiuse' ,'show'=>true),
    'student_CompletedStatus_sessionEnd'=>array('label'=>'std. status complt.' ,'show'=>true),
    'student_subscribedStatus_sessEnd'=>array('label'=>'std. status subscribed' ,'show'=>false),
    'student_CompletedStatus_sessionEnd_Rate'=>array('label'=>'% complt.' ,'show'=>true),
    'tot_Session'=>array('label'=>'tot. edizioni' ,'show'=>true),
    'tot_student_CompletedStatus'=>array('label'=>'tot. std. status complt.' ,'show'=>true),
    'tot_student_subscribedStatus'=>array('label'=>'tot.std. status subscribed' ,'show'=>false),
    'tot_student_CompletedStatus_Rate'=>array('label'=>'tot. %' ,'show'=>true),
    'visits'=>array('label'=>'pagine visitate' ,'show'=>true),
    'system_messages'=>array('label'=>'messaggi' ,'show'=>true),
    'chatrooms'=>array('label'=>'chat' ,'show'=>true),
    'videochatrooms'=>array('label'=>'video chat' ,'show'=>true)
    
);



