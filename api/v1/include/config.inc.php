<?php

/*
 * version of API
 */
define('VERSION','v1');

/*
 * URL of Semantic API
 */
define('URL_LAVORI5','http://openlabor.lynxlab.com/services/search/andrea/lavori5/');
define('URL_LAVORI4','http://openlabor.lynxlab.com/services/search/andrea/lavori4/');

/*
 * Number of professional code to treat
 */
define('NUMBER_CODE',4);

define('DIR_INFO_SERVICES','doc_news');
define('DISCOVERY_INFO',DIR_INFO_SERVICES.'/discovery.xml');
define('SERVICES_INFO',DIR_INFO_SERVICES.'/services.xml');

/*
 * PROVIDER IN WHICH SEARCH DATA
 */
define('DATA_PROVIDER','client0');

/*
 * utility for query in educational qualification
define('laurea','corso di laurea|laurea|diploma|');
define('diploma', 'ISTITUTO PROFESSIONALE|SCUOLA MAGISTRALE|ISTITUTO TECNICO|ISTITUTO MAGISTRALE|SCIENTIFICO|CLASSICO|LINGUISTICO|ISTITUTO D\'ARTE|LICEO ARTISTICO|ISTITUTO SUPERIORE');
define('media','media,');
 */
define('laurea','qualificationRequired like \'%laurea%\'');
define('diploma', '(qualificationRequired like \'%ISTITUTO PROFESSIONALE%\' OR 
    qualificationRequired like \'%SCUOLA MAGISTRALE%\' OR 
    qualificationRequired like \'%ISTITUTO TECNICO%\' OR
    qualificationRequired like \'%ISTITUTO MAGISTRALE%\' OR
    qualificationRequired like \'%SCIENTIFICO%\' OR
    qualificationRequired like \'%CLASSICO%\' OR 
    qualificationRequired like \'%LINGUISTICO%\' OR 
    qualificationRequired like \'%ISTITUTO D\'\'ARTE%\' OR 
    qualificationRequired like \'%LICEO ARTISTICO%\' OR 
    qualificationRequired like \'%ISTITUTO SUPERIORE%\')');
define('media','qualificationRequired like \'%media%\'');

/*
 * 
 */
define('SEARCH_AND',true);
define('LOGFILEREQUEST','log/openlaborRequest.txt');
