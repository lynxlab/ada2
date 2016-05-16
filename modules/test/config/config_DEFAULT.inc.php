<?php
/*
 * Test and Survey constants (tables: test, history_test, history_answer )
 */
define('ADA_CUSTOM_EXERCISE_TEST',				9); //MUST MATCH ADA_PERSONAL_EXERCISE_TYPE!!!!!!!

//test node type (first character of column "tipo")
define('ADA_TYPE_TEST',							1); //applies only to test and survey
define('ADA_TYPE_SURVEY',						2); //applies only to test and survey
define('ADA_GROUP_TOPIC',						3); //(deve avere un padre di tipo test o survey)
define('ADA_GROUP_QUESTION',					4); //(deve avere un padre di tipo Topic)
define('ADA_LEAF_ANSWER',						5); // risposta

//return flag (second character of column "tipo") applies to ADA_TYPE_TEST/ADA_TYPE_SURVEY node
define('ADA_NO_TEST_RETURN',					0); // no link
define('ADA_NEXT_NODE_TEST_RETURN',				1); // display a link that points to next course node
define('ADA_INDEX_TEST_RETURN',					2); // display a link that points to course's index
define('ADA_COURSE_INDEX_TEST_RETURN',			3); // display a link that points to user's home page
define('ADA_COURSE_FIRSTNODE_TEST_RETURN',		4); // display a link that points to course's zero node

//interaction flag (third character of column "tipo") applies to ADA_TYPE_TEST/ADA_TYPE_SURVEY node
define('ADA_RATING_TEST_INTERACTION',			0); // with feedback, show rating and points for each answer
define('ADA_FEEDBACK_TEST_INTERACTION',			1); // with feedback
define('ADA_BLIND_TEST_INTERACTION',			2); // no feedback
define('ADA_CORRECT_TEST_INTERACTION',			3); // with feedback, show only most correct answer

//presentation flag (fourth character of column "tipo") applies to ADA_TYPE_TEST/ADA_TYPE_SURVEY node
define('ADA_ONEPAGE_TEST_MODE',					0); // All Answer of the group test in one page
define('ADA_SEQUENCE_TEST_MODE',				1); // next topic group will be shown in the next page (order)

//barrier flag (fifth character of column "tipo") applies to ADA_TYPE_TEST node
define('ADA_NO_TEST_BARRIER',					0);
define('ADA_YES_TEST_BARRIER',					1);

//repeatable flag (sixth character of column "tipo") applies to ADA_TYPE_TEST/ADA_TYPE_SURVEY node
define('ADA_NO_TEST_REPETEABLE',				0); // Applicare all'intero gruppo test/sondaggio
define('ADA_YES_TEST_REPETEABLE',				1);

//random questions flag (second character of column "tipo") applies to ADA_GROUP_TOPIC node
define('ADA_PICK_QUESTIONS_NORMAL',				0); // non applicabile
define('ADA_PICK_QUESTIONS_RANDOM',				1); // preleva le domande in modo casuale

//question type (second character of column "tipo") applies to ADA_GROUP_QUESTION node
define('ADA_NO_QUESTION_TEST_TYPE',				0); // non applicabile
define('ADA_MULTIPLE_CHECK_TEST_TYPE',			1); // risposta multipla (checkbox)
define('ADA_STANDARD_TEST_TYPE',				2); // risposta singola (radio button)
define('ADA_LIKERT_TEST_TYPE',					3); // rappresentazione in scala likert
define('ADA_OPEN_MANUAL_TEST_TYPE',				4); // risposta aperta con correzione manuale
define('ADA_OPEN_AUTOMATIC_TEST_TYPE',			5); // risposta aperta con correzione automatica
define('ADA_CLOZE_TEST_TYPE',					6);	// cloze (vari tipi)
define('ADA_OPEN_UPLOAD_TEST_TYPE',				7); // risposta aperta + upload

//open comment (third character of column "tipo") applies to ADA_GROUP_QUESTION node
define('ADA_NO_TEST_COMMENT',					0);
define('ADA_YES_TEST_COMMENT',					1);

//test variations (fourth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_STANDARD_TEST_TYPE | ADA_MULTIPLE_CHECK_TEST_TYPE node
define('ADA_NORMAL_TEST_VARIATION',				0); // esercizi normali senza variante
define('ADA_ERASE_TEST_VARIATION',				1); // esercizio con variante di visualizzazione (cancellazione)
define('ADA_HIGHLIGHT_TEST_VARIATION',			2); // esercizio con variante di visualizzazione (evidenziazione)

//test semplification (fourth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_CLOZE_TEST_TYPE node
define('ADA_NORMAL_TEST_SIMPLICITY',			0); // scrittura in spazi vuoti
define('ADA_MEDIUM_TEST_SIMPLICITY',			1); // scrittura in spazi vuoti con caratteri limitati
define('ADA_SELECT_TEST_SIMPLICITY',			2); // menù a tendina
define('ADA_DRAGDROP_TEST_SIMPLICITY',			3); // drag'n'drop
define('ADA_ERASE_TEST_SIMPLICITY',				4); // cancellazione/evidenziazione di parole
define('ADA_SLOT_TEST_SIMPLICITY',				6); // inserimento di parole
define('ADA_MULTIPLE_TEST_SIMPLICITY',			7); // classificazione di parole

//test semplification mode (fifth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_CLOZE_TEST_TYPE -> ADA_SELECT_TEST_SIMPLICITY node
define('ADA_NORMAL_SELECT_TEST',				0); //cloze a tendina con prima opzione vuota
define('ADA_SYNONYM_SELECT_TEST',				1); //cloze a tendina con prima opzione piena e colorata in modo diverso

//Drag'n'Drop box position (fifth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_CLOZE_TEST_TYPE -> ADA_DRAGDROP_TEST_SIMPLICITY | ADA_SLOT_TEST_SIMPLICITY node
define('ADA_TOP_TEST_DRAGDROP',					0); //Show box on the top of exercise
define('ADA_RIGHT_TEST_DRAGDROP',				1); //Show box on the right of exercise
define('ADA_BOTTOM_TEST_DRAGDROP',				2); //Show box on the bottom of exercise
define('ADA_LEFT_TEST_DRAGDROP',				3); //Show box on the left of exercise

//Erase Question mode (fifth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_CLOZE_TEST_TYPE -> ADA_ERASE_TEST_SIMPLICITY node
define('ADA_ERASE_TEST_ERASE',					0); //Erase mode
define('ADA_HIGHLIGHT_TEST_ERASE',				1); //Highlight mode

//Erase Question mode (sixth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_CLOZE_TEST_TYPE -> ADA_ERASE_TEST_SIMPLICITY node
//Multiple Cloze Question mode (sixth character of column "tipo") applies to ADA_GROUP_QUESTION -> ADA_CLOZE_TEST_TYPE -> ADA_MULTIPLE_TEST_SIMPLICITY node
define('ADA_NO_APOSTROPHE_TEST_MULTIPLE',		0); //Multiple Cloze mode
define('ADA_APOSTROPHE_TEST_MULTIPLE',			1); //Multiple Cloze mode (consider apostrophe)

//Extra blank answer (second character of column "tipo") applies to ADA_LEAF_ANSWER
define('ADA_NO_OPEN_TEST_ANSWER',				0); //don't show the extra answer
define('ADA_OPEN_TEST_ANSWER',					1); //show an extra blank answer that user needs to fill in

//Determines what compare function must be used (third character of column "tipo") applies to ADA_LEAF_ANSWER
define('ADA_CASE_SENSITIVE_TEST',				0); //correct the answer with case sensitive function
define('ADA_CASE_INSENSITIVE_TEST',				1); //correct the answer with case insensitive function

//if true the system allows the redirect to modules/test/index.php
define('ADA_REDIRECT_TO_TEST',TRUE);

require_once(MODULES_TEST_PATH.'/include/AMATestDataHandler.inc.php');
require_once(MODULES_TEST_PATH.'/include/nodeTest.class.inc.php');
require_once(MODULES_TEST_PATH.'/include/root.class.inc.php');
require_once(MODULES_TEST_PATH.'/include/forms/formTest.inc.php');