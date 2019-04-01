<?php
use Lynxlab\ADA\Module\GDPR\GdprAPI;
use Lynxlab\ADA\Module\GDPR\GdprPolicy;

/**
 * User classes
 *
 *
 * @package		model
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		user_classes
 * @version		0.1
 */

/**
 *
 *
 */
abstract class ADAGenericUser {
    /*
   * Data stored in table Utente
    */
    public $id_user;
    public $nome;
    public $cognome;
    public $tipo;
    public $email;
    public $telefono;
    public $username;
    public $template_family;   // layout
    // ADA specific
    protected $indirizzo;
    protected $citta;
    protected $provincia;
    protected $nazione;
    protected $codice_fiscale;
    protected $birthdate;
    protected $birthcity;
    protected $birthprovince;
    protected $sesso;
    protected $stato;
    protected $lingua;
    protected $timezone;
    protected $cap;
    protected $SerialNumber;
    protected $avatar;

    // we do not store user's password ???
    protected $password;
    // END of ADA specific

// ATTENZIONE A QUESTI QUI SOTTO
    public $livello = 1;
    public $history='';
    public $exercise='';
    public $address;
    public $status;
    public $full=0; //  user exists
    public $error_msg;

    /*
   * Data stored in table Utente_Tester
    */
    protected $testers;
    /*
   * Path to user's home page
    */
    protected $homepage;

    /**
     * path to user's edit profile page
     */
    protected $editprofilepage;

    /*
   * getters
    */

    public function getId() {
        return $this->id_user;
    }

    public function getFirstName() {
        return $this->nome;
    }

    public function getLastName() {
        return $this->cognome;
    }

    public function getFullName() {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getType() {
        return $this->tipo;
    }

    public function getTypeAsString() {
        switch($this->tipo) {
            case AMA_TYPE_ADMIN:
                return translateFN('Super amministratore');
            case AMA_TYPE_SWITCHER:
                return translateFN('Amministratore del provider');
            case AMA_TYPE_AUTHOR:
                return translateFN('Autore');
            case AMA_TYPE_TUTOR:
                return ($this->isSuper) ? translateFN('Super Tutor') : translateFN('Tutor');
            case AMA_TYPE_STUDENT:
                return translateFN('Studente');
            default:
                return translateFN('Ospite');
        }
    }
    public function getEmail() {
        return $this->email;
    }

    public function getAddress() {
        if($this->indirizzo != 'NULL') {
            return $this->indirizzo;
        }
        return '';
    }

    public function getCity() {
        if($this->citta != 'NULL') {
            return $this->citta;
        }
        return '';
    }

    public function getProvincia() {
        if($this->provincia != 'NULL') {
            return $this->provincia;
        }
        return '';
    }

    public function getProvince() {
        if($this->provincia != 'NULL') {
            return $this->provincia;
        }
        return '';
    }

    public function getCountry() {
        if($this->nazione != 'NULL') {
            return $this->nazione;
        }
        return '';
    }

    public function getFiscalCode() {
        return $this->codice_fiscale;
    }

    public function getBirthDate() {
        return $this->birthdate;
    }

    public function getBirthCity() {
    	return $this->birthcity;
    }

    public function getBirthProvince() {
    	 return $this->birthprovince;
    }

    public function getGender() {
        return $this->sesso;
    }

    public function getPhoneNumber() {
        if($this->telefono != 'NULL') {
            return $this->telefono;
        }
        return '';
    }

    public function getStatus() {
        return $this->stato;
    }

    public function getLanguage() {
        return $this->lingua;
    }

    public function getTimezone() {
        return $this->timezone;
    }

    public function getUserName() {
        return $this->username;
    }

    public function getCap() {
        return $this->cap;
    }

    public function getSerialNumber() {
        return $this->SerialNumber;
    }

    public function getAvatar() {
        if ($this->avatar != '' && file_exists(ADA_UPLOAD_PATH.$this->id_user.'/'.$this->avatar)) {
            $imgAvatar = HTTP_UPLOAD_PATH.$this->id_user.'/'.$this->avatar;
        } else {
            $imgAvatar = HTTP_UPLOAD_PATH.ADA_DEFAULT_AVATAR;
        }
        return $imgAvatar;
    }

    public function getTesters() {
        if(is_array($this->testers)) {
            return $this->testers;
        }
        return array();
    }

    public function getDefaultTester() {
        if(is_array($this->testers) && sizeof($this->testers) > 0) {
            return $this->testers[0];
        }
        return NULL;
    }

    public function getHomePage($msg = null) {
        if ($msg!=null) {
            return $this->homepage."?message=$msg";
        }
        return $this->homepage;
    }

    public function getEditProfilePage()
    {
    	return  HTTP_ROOT_DIR . $this->editprofilepage;
    }

    public function getUnreadMessagesCount() {
    	$msg_simple_count = 0;
    	// passing true means get unread message
    	$msg_simpleAr =  MultiPort::getUserMessages($this, true);
    	foreach ($msg_simpleAr as $msg_simple_provider) {
    		$msg_simple_count += count($msg_simple_provider);
    	}
    	return intval($msg_simple_count);
    }

    /*
   * setters
    */
    public function setFirstName($firstname) {
        $this->nome = $firstname;
    }
    public function setLastName($lastname) {
        $this->cognome = $lastname;
    }

    public function setType($type) {
        $this->tipo = $type;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhoneNumber($phone_number) {
        $this->telefono = $phone_number;
    }

//  public function setUserName($username) {
//    // NON SI PUO' MODIFICARE LO USERNAME
//  }

    public function setLayout($layout) {
        if ($layout == 'none' || $layout == 'null' || $layout == 'NULL') {
            $this->template_family = '';
        } else {
        $this->template_family = $layout;
        }
    }
    public function setAddress($address) {
        $this->indirizzo = $address;
    }

    public function setCity($city) {
        $this->citta = $city;
    }

    public function setProvince($province) {
        $this->provincia = $province;
    }

    public function setCountry($country) {
        $this->nazione = $country;
    }

    public function setFiscalCode($fiscal_code) {
        $this->codice_fiscale = $fiscal_code;
    }

    public function setBirthDate($birthdate) {
        $this->birthdate = $birthdate;
    }

    public function setBirthCity($birthcity) {
    	$this->birthcity = $birthcity;
    }

    public function setBirthProvince($birthprovince) {
    	$this->birthprovince = $birthprovince;
    }

    public function setGender($gender) {
        $this->sesso = $gender;
    }



    /**
     *
     * @param $user_id
     * @return unknown_type
     */
    // FIXME: controllare se servono questi controlli
    public function setUserId($id_user) {
        if(!is_numeric($id_user)) {
            return;
        }
        $this->id_user = (int)$id_user;
    }



    protected function setHomePage($home_page) {
        $this->homepage = $home_page;
    }

    protected function setEditProfilePage ($relativeUrl) {
    	if (isset($relativeUrl) && strlen($relativeUrl)>0) {
    		// make it leading slash-agnostic
    		if ($relativeUrl{0}!== DIRECTORY_SEPARATOR) $relativeUrl = DIRECTORY_SEPARATOR .$relativeUrl;

    		if (is_file(ROOT_DIR . $relativeUrl)) $this->editprofilepage = $relativeUrl;
    		else $this->editprofilepage = '';
    	} else $this->editprofilepage = '';
    }

    public function setTesters($testersAr = array()) {
        // testersAr is an array containing tester ids.
        $this->testers = $testersAr;
    }

    public function setStatus($status) {
        $this->stato = $status;
    }

    public function setLanguage($language) {
        $this->lingua = $language;
    }

    public function setTimezone($timezone) {
        $this->timezone = $timezone;
    }

    public function setPassword($password) {
        if (DataValidator::validate_password($password, $password) != FALSE) {
            $this->password = sha1($password);
        }
    }

    public function setCap($cap) {
        $this->cap = $cap;
    }

    public function setSerialNumber($matricola) {
        $this->SerialNumber = $matricola;
    }

    public function setAvatar($avatar) {
        $this->avatar = $avatar;
    }


    public function addTester($tester) {
        $tester = DataValidator::validate_testername($tester,MULTIPROVIDER);
        if($tester !== FALSE) {
        	$this->setTesters($this->getTesters());
            array_push($this->testers, $tester);
            return TRUE;
        }
        return FALSE;
    }

    /**
     *
     * @return array
     */
    public function toArray() {
        $user_dataAr = array(
                'id_utente'              => $this->id_user,
                'nome'                   => $this->nome,
                'cognome'                => $this->cognome,
                'tipo'                   => $this->tipo,
                'e_mail'                 => $this->email,
                'username'               => $this->username,
                'password'               => $this->password, // <--- fare attenzione qui
                'layout'                 => $this->template_family,
                'indirizzo'              => ($this->indirizzo != 'NULL') ? $this->indirizzo : '',
                'citta'                  => ($this->citta != 'NULL') ? $this->citta : '',
                'provincia'              => ($this->provincia != 'NULL') ? $this->provincia : '',
                'nazione'                => $this->nazione,
                'codice_fiscale'         => $this->codice_fiscale,
                'birthdate'              => $this->birthdate,
        		'birthcity'				 => ($this->birthcity != NULL) ? $this->birthcity : '',
        		'birthprovince'			 => $this->birthprovince,
                'sesso'                  => $this->sesso,
                'telefono'               => ($this->telefono != 'NULL') ? $this->telefono : '',
                'stato'                  => $this->stato,
                'lingua'                 => $this->lingua,
                'timezone'               => $this->timezone,
                'cap'                    => ($this->cap != NULL) ? $this->cap : '',
                'matricola'              => ($this->SerialNumber != NULL) ? $this->SerialNumber : '',
                'avatar'                 => ($this->avatar != NULL) ? $this->avatar :''

        );


        if ($this instanceof ADAPractitioner && $this->isSuper) $user_dataAr['tipo'] = AMA_TYPE_SUPERTUTOR;

        return $user_dataAr;
    }

    // MARK: existing methods

    public function get_messagesFN($id_user) {
    }


    // FIXME: sarebbe statico, ma viene usato come metodo non statico.
    public static function convertUserTypeFN($id_profile) {
        switch  ($id_profile) {
            case 0: // reserved
                $user_type = translateFN('utente ada');
                break;

            case AMA_TYPE_AUTHOR:
                $user_type = translateFN('autore');
                break;

            case AMA_TYPE_ADMIN:
                $user_type = translateFN('amministratore');
                break;

            case AMA_TYPE_TUTOR:
                $user_type = translateFN('tutor');
                break;
            case AMA_TYPE_SWITCHER:
                $user_type = translateFN('switcher');
                break;

            case AMA_TYPE_STUDENT:
            default:
            // FIXME: trovare dove controlliamo $user_type == 'studente' e sostituire con $user_type == 'utente'
                $user_type = translateFN('utente');
        }
        return $user_type;
    }

    public function get_agendaFN($id_user) {
    }

    public static function get_online_usersFN($id_course_instance, $mode) {
    }

    private static function _online_usersFN($id_course_instance, $mode=0) {
    }

    public static function is_someone_thereFN($id_course_instance, $id_node) {
    }

    public function get_last_accessFN($id_course_instance="") {
    }

    public static function is_visited_by_userFN($node_id, $course_instance_id, $user_id) {
    }

    public static function is_visited_by_classFN($node_id, $course_instance_id, $course_id) {
    }

    public static function is_visitedFN($node_id) {
    }
}

/**
 *
 *
 */
class ADAGuest extends ADAGenericUser {
    public function __construct($user_dataHa=array()) {
        $this->id_user         = 0;
        $this->nome            = 'guest';
        $this->cognome         = 'guest';
        $this->tipo            = AMA_TYPE_VISITOR;
        $this->email           = 'vito@lynxlab.com';
        $this->telefono        = 0;
        $this->username        = 'guest';
        $this->template_family =  ADA_TEMPLATE_FAMILY;
        $this->indirizzo       = NULL;
        $this->citta           = NULL;
        $this->provincia       = NULL;
        $this->nazione         = NULL;
        $this->codice_fiscale  = NULL;
        $this->birthdate       = NULL;
        $this->birthcity	   = NULL;
        $this->birthprovince   = NULL;
        $this->sesso           = NULL;
        $this->telefono               = NULL;
        $this->stato                  = NULL;
        $this->lingua = 0;
        $this->timezone = 0;
        $this->cap             = NULL;
        $this->SerialNumber    = NULL;
        $this->avatar          = NULL;
        $this->testers = (!MULTIPROVIDER && isset ($GLOBALS['user_provider'])) ? array($GLOBALS['user_provider']) : array(ADA_PUBLIC_TESTER);

        $this->setHomePage(HTTP_ROOT_DIR);
        $this->setEditProfilePage('');
    }
}

/**
 *
 *
 */
abstract class ADALoggableUser extends ADAGenericUser {
    public function __construct($user_dataHa=array()) {
        /*
   * $user_dataHa is an associative array with the following keys:
   * nome, cognome, tipo, e_mail, telefono, username, layout, indirizzo, citta,
   * provincia, nazione, codice_fiscale, sesso,
   * telefono, stato
        */
        if(isset($user_dataHa['id']) && DataValidator::is_uinteger($user_dataHa['id'])) {
            $this->id_user = $user_dataHa['id'];
        }
        else {
            $this->id_user = 0;
        }
        $this->nome                   = isset($user_dataHa['nome']) ? $user_dataHa['nome'] : null;
        $this->cognome                = isset($user_dataHa['cognome']) ? $user_dataHa['cognome'] : null;
        $this->tipo                   = isset($user_dataHa['tipo']) ? $user_dataHa['tipo'] : null;
        $this->email                  = isset($user_dataHa['email']) ? $user_dataHa['email'] : null;
        $this->username               = isset($user_dataHa['username']) ? $user_dataHa['username'] : null;
        $this->template_family        = isset($user_dataHa['layout']) ? $user_dataHa['layout'] : null;
        $this->indirizzo              = isset($user_dataHa['indirizzo']) ? $user_dataHa['indirizzo'] : null;
        $this->citta                  = isset($user_dataHa['citta']) ? $user_dataHa['citta'] : null;
        $this->provincia              = isset($user_dataHa['provincia']) ? $user_dataHa['provincia'] : null;
        $this->nazione                = isset($user_dataHa['nazione']) ? $user_dataHa['nazione'] : null;
        $this->codice_fiscale         = isset($user_dataHa['codice_fiscale']) ? $user_dataHa['codice_fiscale'] : null;
        $this->birthdate              = isset($user_dataHa['birthdate']) ? $user_dataHa['birthdate'] : null;
        $this->sesso                  = isset($user_dataHa['sesso']) ? $user_dataHa['sesso'] :null;

        $this->telefono               = isset($user_dataHa['telefono']) ? $user_dataHa['telefono'] : null;

        $this->stato                  = isset($user_dataHa['stato']) ? $user_dataHa['stato'] : null;
        $this->lingua                 = isset($user_dataHa['lingua']) ? $user_dataHa['lingua'] : null;
        $this->timezone               = isset($user_dataHa['timezone']) ? $user_dataHa['timezone'] : null;

        $this->cap                    = isset($user_dataHa['cap']) ? $user_dataHa['cap'] : null;
        $this->SerialNumber           = isset($user_dataHa['matricola']) ? $user_dataHa['matricola'] : null;
        $this->avatar                 = isset($user_dataHa['avatar']) ? $user_dataHa['avatar'] : null;

        $this->birthcity			  = isset($user_dataHa['birthcity']) ? $user_dataHa['birthcity'] : null;
        $this->birthprovince		  = isset($user_dataHa['birthprovince']) ? $user_dataHa['birthprovince'] : null;


    }

    public function fillWithArrayData ($dataArr = null)
    {
    	if (!is_null($dataArr))
    	{
    		$this->setFirstName($dataArr['nome']);
    		$this->setLastName($dataArr['cognome']);
    		$this->setFiscalCode($dataArr['codice_fiscale']);
    		$this->setEmail($dataArr['email']);
    		if (trim($dataArr['password']) != '') {
    			$this->setPassword($dataArr['password']);
    		}
    		$this->setSerialNumber(isset($dataArr['matricola']) ? : null);
    		$this->setLayout($dataArr['layout']);
    		$this->setAddress($dataArr['indirizzo']);
    		$this->setCity($dataArr['citta']);
    		$this->setProvince($dataArr['provincia']);
    		$this->setCountry($dataArr['nazione']);
    		$this->setBirthDate($dataArr['birthdate']);
    		$this->setGender(isset($dataArr['sesso']) ? $dataArr['sesso'] : null);
    		$this->setPhoneNumber($dataArr['telefono']);
    		$this->setLanguage(isset($dataArr['lingua']) ? $dataArr['lingua'] : null);
    		//        $this->setAvatar($dataArr['avatar']);
    		if (isset($_SESSION['uploadHelper']['fileNameWithoutPath'])) $this->setAvatar($_SESSION['uploadHelper']['fileNameWithoutPath']);
    		$this->setCap($dataArr['cap']);
    		if (isset($dataArr['stato'])) $this->setStatus($dataArr['stato']);
    		$this->setBirthCity(isset($dataArr['birthcity']) ? $dataArr['birthcity'] : null);
    		$this->setBirthProvince(isset($dataArr['birthprovince']) ? $dataArr['birthprovince'] : null);
    	}
    }

    /**
     * Anonymize user data by replacing the data passed in the keys of $dataArr
     * with random strings.
     * Default anonymized values are: 'nome', 'cognome', 'codice_fiscale',
     * 'email', 'username', 'password', 'matricola'
     *
     * NOTE: this method will just DIE if MODULES_GDPR is not installed
     *
     * @param array $dataArr
     * @return ADALoggableUser
     */
    public function anonymize($dataArr = array('nome', 'cognome', 'codice_fiscale', 'email', 'username', 'password', 'matricola')) {
    	if (defined('MODULES_GDPR') && MODULES_GDPR===true) {
			try {
	    		$userArr = $this->toArray();
	    		foreach ($dataArr as $key) {
	    			$value = bin2hex(random_bytes(random_int(8, 16)));
	    			if (strcmp($key, 'username') === 0) $this->username = $value;
	    			else $userArr[$key] = $value;
	    		}
				$this->fillWithArrayData($userArr);
				$this->setStatus(ADA_STATUS_ANONYMIZED);
	    		return $this;
			} catch (TypeError $e) {
			    die("An unexpected error has occurred");
			} catch (Error $e) {
			    die("An unexpected error has occurred");
			} catch (Exception $e) {
			    // If you get this message, the CSPRNG failed hard.
			    die("Could not generate a random string. Is our OS secure?");
			}
    	} else die("anonymize method cannot be called when MODULES_GDPR is not installed");
    }

// MARK: USARE MultiPort::getUserMessages
    public function get_messagesFN($id_user) {
        return '';
    }

// MARK: usare MultiPort::getUserAgenda
    public function get_agendaFN($id_user) {
        return '';
    }

    public static function get_online_usersFN($id_course_instance,$mode) {
    $data =  self::_online_usersFN($id_course_instance,$mode);
        if (gettype($data)== 'string' || $data == 'null'){
            return $data;
        } else {
            $user_list = BaseHtmlLib::plainListElement('class:user_online', $data, FALSE);
            $user_list_html = $user_list->getHtml();
            /*
             *
            $t = new Table();
            $t->initTable('0','center','0','0','100%','','','','','','1');
            $t->setTable($data,$caption="",$summary="Utenti online");
            $tabled_data = $t->getTable();
             */
//            return $tabled_data;
            return $user_list_html;
        }
    }

    private static function _online_usersFN($id_course_instance,$mode=0) {
        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;
        $sess_id_node = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;
        $sess_id_course = isset($_SESSION['sess_id_course']) ? $_SESSION['sess_id_course'] : null;
        $sess_id_user = $_SESSION['sess_id_user'];

        /*
     Viene passato $id_course_instance per filtrare l'istanza di corso
     su cui si sta lavorando.
        */

        /**
         * @author giorgio 28/giu/2013
         * fixed bug: if neither course instance nor session course instance is set, then return null
         */
        if (!isset($id_course_instance) && (
        		!isset($sess_id_course_instance) || is_null($sess_id_course_instance))) return null;

        if (!isset($id_course_instance))
            $id_course_instance = $sess_id_course_instance;
        $now = time();
        // $mode=0;  forcing mode to increase speed
        $tolerance = 240; // 4 minuti
        $limit = $now-$tolerance;
        $out_fields_ar = array('data_visita','id_utente_studente','session_id');
        $clause = "data_visita > $limit and id_istanza_corso ='$id_course_instance'";
        $dataHa = $dh->_find_nodes_history_list($out_fields_ar, $clause);
        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            if (gettype($dataHa)=="object") {
                $msg = $dataHa->getMessage();
                return $msg;
            }
            // header("Location: $error?err_msg=$msg");
        } else {
            switch ($mode) {
                case 3:   // username, link to chat
                // should read from chat table...
                    break;
                case 2:  // username, link to msg & tutor
                    if (count($dataHa)) {
                        //$online_usersAr = array();
                        $online_users_idAr = array();
                        foreach ($dataHa as $user) {
                            $user_id = $user[2];
                            if (!in_array($user_id,$online_users_idAr)) {
                                if ($sess_id_user==$user_id) {
                                    // ora bisogna controllare che la sessione sia la stessa
                                    $user_session_id = $user[3];
                                    if ($user_session_id == session_id()) {


                                    	/**
                                    	 * @author giorgio 17/feb/2016
                                    	 * added continue; to remove 'io'
                                    	 * from the online users list
                                    	 */
                                    	continue;

                                        $online_users_idAr[] = $user_id;
                                        //$online_usersAr[$user_id]['user']= "<img src=\"img/_student.png\" border=\"0\"> ".translateFN("io");
                                        $online_usersAr[]= translateFN("io");
                                        // if we don't want to show this user:
                                        //$online_usersAr[$user_id]['user']= "";
                                    } else {
                                        $online_users_idAr[] = $user_id;
                                        //$online_usersAr[$user_id]['user']= "<img src=\"img/_student.png\" border=\"0\"> ".translateFN("Un utente con i tuoi dati &egrave; gi&agrave; connesso!");
                                        $online_usersAr[]= translateFN("Un utente con i tuoi dati &egrave; gi&agrave; connesso!");
                                    }
                                    $currentUserObj = $_SESSION['sess_userObj'];
                                    $current_profile = $currentUserObj->getType();
                                    if ($current_profile == AMA_TYPE_STUDENT) {


                                    }
                                } else {
                                    $userObj = MultiPort::findUser($user_id);
                                    if(gettype($userObj) == 'object') { //instanceof ADAUser) { // && $userObj->getStatus() == ADA_STATUS_REGISTERED) {
//                                    $userObj = new User($user_id);
                                        $online_users_idAr[] = $user_id;
                                        $id_profile = $userObj->getType(); //$userObj->tipo;
                                        if ($id_profile == AMA_TYPE_TUTOR) {
                                            $online_usersAr[]= $userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |"
                                                . " <a href=\"view.php?id_node=$sess_id_node&guide_user_id=".$userObj->getId()."\"> ".translateFN("segui")."</a> |";
                                            //$online_usersAr[$user_id]['user']= "<img src=\"img/_tutor.png\" border=\"0\"> ".$userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |";
                                            //$online_usersAr[$user_id]['user'].= " <a href=\"view.php?id_node=$sess_id_node&guide_user_id=".$userObj->id."\"> ".translateFN("segui")."</a> |";
                                        } else {    // STUDENT
                                            // $online_usersAr[$user_id]['user']= "<a href=\"student.php?op=list_students&id_course_instance=$sess_id_course_instance&id_course=$sess_id_course\"><img src=\"img/_student.png\" border=0></a> ";
                                            $online_usersAr[]= $userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |";
//                                            $online_usersAr[$user_id]['user']= "<img src=\"img/_student.png\" border=\"0\"> ";
//                                            $online_usersAr[$user_id]['user'].= $userObj->username. " |<a href=\"$http_root_dir/comunica/send_message.php?destinatari=". $userObj->username."\"  target=\"_blank\">".translateFN("scrivi un messaggio")."</a> |";
                                        }
                                    }
                                }
                            }
                        }

                        return  (isset($online_usersAr) ? $online_usersAr : null);
                    } else {
                        return  translate("Nessuno");
                    }
                    break;
                case 1: // username, mail and timestemp // @FIXME
                    if (count($dataHa)) {
                        //$online_usersAr = array();
                        $online_users_idAr = array();
                        foreach ($dataHa as $user) {
                            $user_id = $user[2];
                            if (!in_array($user_id,$online_users_idAr)) {
                                $userObj = MultiPort::findUser($user_id);
                                $time = date("H:i:s",$user[1]);
                                $online_users_idAr[] = $user_id;
                                $online_usersAr[$user_id]['user'] = $userObj->username;
                                $online_usersAr[$user_id]['email'] = $userObj->email;
                                $online_usersAr[$user_id]['time'] = $time;
                            }
                        }
                        return  $online_usersAr;
                    } else {
                        return  translateFN("Nessuno");
                    }
                    break;
                case 0:
                default;
                    if (count($dataHa)) {
                        $online_users_idAr = array();
                        foreach ($dataHa as $user) {
                            $user_id = $user[2];
                            if (!in_array($user_id,$online_users_idAr)) {
                                $online_users_idAr[] = $user_id;
                            }
                        }
                        return count($online_users_idAr)." ".translateFN("studente/i"); // only number of users online
                    } else {
                        return translateFN("Nessuno");
                    }
            }
        }
    }

    public static function is_someone_there_courseFN($id_course_instance) {
        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = isset($_SESSION['sess_id_course_instance']) ? $_SESSION['sess_id_course_instance'] : null;
        $sess_id_node = isset($_SESSION['sess_id_node']) ? $_SESSION['sess_id_node'] : null;

        if (!isset($id_course_instance))
            $id_course_instance = $sess_id_course_instance;
        if (!isset($id_node))
            $id_node = $sess_id_node;

        $now = time();
        // $mode=0;  forcing mode to increase speed
        $tolerance = 600; // dieci minuti
        $limit = $now-$tolerance;
        $out_fields_ar = array('id_nodo','data_uscita','id_utente_studente');
        $clause = "data_uscita > $limit and id_istanza_corso ='$id_course_instance'";
        $dataHa = $dh->_find_nodes_history_list($out_fields_ar, $clause, true);
        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            if (gettype($dataHa)=="object") {
                $msg = $dataHa->getMessage();
                return $msg;
            }
            // header("Location: $error?err_msg=$msg");
        } else {
            return $dataHa;
        }
    }

    public static function is_someone_thereFN($id_course_instance,$id_node) {
        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];

        if (!isset($id_course_instance))
            $id_course_instance = $sess_id_course_instance;
        if (!isset($id_node))
            $id_node = $sess_id_node;

        $now = time();
        // $mode=0;  forcing mode to increase speed
        $tolerance = 600; // dieci minuti
        $limit = $now-$tolerance;
        $out_fields_ar = array('data_uscita','id_utente_studente');
        $clause = "data_uscita > $limit and id_istanza_corso ='$id_course_instance' and id_nodo ='$id_node'";
        $dataHa = $dh->_find_nodes_history_list($out_fields_ar, $clause);
        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            if (gettype($dataHa)=="object") {
                $msg = $dataHa->getMessage();
                return $msg;
            }
            // header("Location: $error?err_msg=$msg");
        } else {
            return (count($dataHa)>=1);
        }
    }


    public function get_last_accessFN($id_course_instance="",$type="T",$dh = null) {
		if (is_null($dh)) {
			$dh = $GLOBALS['dh'];
		}
        // called by browsing/student.php

        if($type=="UT")
        {
            $last_accessAr = $this->_get_last_accessFN($id_course_instance,$dh,false);
        }
        else
        {
            $last_accessAr = $this->_get_last_accessFN($id_course_instance,$dh);
        }
        if (is_array($last_accessAr))
            switch ($type) {
                case  "N":
                    return  $last_accessAr[0]; //es. 100_34
                    break;
                case "T":
                default:
                // vito, 11 mar 2009
                //return  substr(ts2dFN($last_accessAr[1]),0,5); // es. 10/06
                    return  substr($last_accessAr[1],0,5); // es. 10/06
                    break;
                case "UT":
                    return  $last_accessAr[1]; // unixtime
            }
        else
            return "-";
    }

    /**
     *
     * @param  $id_course_instance
     * @return array
     */
    private function _get_last_accessFN($id_course_instance="",$provider_dh, $return_dateonly=true) {
        // if used by student before entering a course, we must pass the DataHandler
        if ($provider_dh==NULL)   {
            $provider_dh = $GLOBALS['dh'];
        }
        //$error = $GLOBALS['error'];
        // $debug = $GLOBALS['debug'];
        $sess_id_user = $_SESSION['sess_id_user'];

        if (!isset($this->id_user)) {
            $id_user = $sess_id_user;
        }
        else {
            $id_user = $this->id_user;
        }

        if ($id_course_instance) {

            $last_visited_node = $provider_dh->get_last_visited_nodes($id_user, $id_course_instance, 10);
            /*
            * vito, 10 ottobre 2008: $last_visited_node è Array([0]=>Array([id_nodo], ...))
            */
            if (!AMA_DB::isError($last_visited_node) && is_array($last_visited_node) && isset($last_visited_node[0])) {

	            $last_visited_time =  ($return_dateonly) ? AMA_DataHandler::ts_to_date($last_visited_node[0]['data_uscita']) : $last_visited_node[0]['data_uscita'] ;

	            return array($last_visited_node[0]['id_nodo'], $last_visited_time);
            } else return "-";
         } else {
            /*
             * Sara, 2/07/2014
             * return the last access between all instances course
             */
            $serviceProviders=$this->getTesters();
            if(!empty($serviceProviders) && is_array($serviceProviders))
            {
                $i=0;
                foreach ($serviceProviders as $Provider) {
                    $provider_dh = AMA_DataHandler::instance(MultiPort::getDSN($Provider));
                    $courseInstances_provider = $provider_dh->get_course_instances_for_this_student($this->getId());
                    if(AMA_DataHandler::isError($courseInstances_provider))
                    {
                        $courseInstances_provider=new ADA_Error($courseInstances_provider);
                    }
                    else
                    {
                        $istance_testerAr[$i]=array('istances'=>$courseInstances_provider,'provider'=>$Provider);
                    }
                    $i++;
                 }
              }
                if(!empty($istance_testerAr))
                {
                    $Max=0;
                    $id_nodo=null;
                    foreach($istance_testerAr as $istanceTs)
                    {
                        $courseInstancesAr=$istanceTs['istances'];
                        $pointer=$istanceTs['provider'];
                        $tester=AMA_DataHandler::instance(MultiPort::getDSN($pointer));
                        foreach($courseInstancesAr as $courseInstance)
                        {
                            $id_instance=$courseInstance['id_istanza_corso'];
                            $last_access=$tester->get_last_visited_nodes($id_user,$id_instance,10);
                            if (!AMA_DB::isError($last_access) && is_array($last_access) && count($last_access)) {
                            	$last_accessAr= array($last_access[0]['id_nodo'], $last_access[0]['data_uscita']);

                            	if($last_accessAr[1]>$Max)
                            	{
                            		$id_nodo=$last_accessAr[0];
                            		$Max=$last_accessAr[1];
                            	}
                            }
                        }
                     }
                       $Last_accessAr=array(0=>$id_nodo,1=>$Max);
                       return $Last_accessAr;
                }
                else
                {
                    return "-";
                }

        }
    }

    public static function is_visited_by_userFN($node_id,$course_instance_id,$user_id) {
        //  returns  the number of visits for this node


        $dh = isset ($GLOBALS['dh']) ? $GLOBALS['dh'] : null;
        $error = isset($GLOBALS['error']) ? $GLOBALS['error'] : null;
        $http_root_dir = isset($GLOBALS['http_root_dir']) ? $GLOBALS['http_root_dir'] : null;
        $debug = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;

        $visit_count = 0;
        $out_fields_ar = array('id_utente_studente','data_visita','data_uscita');
        $history = $dh->find_nodes_history_list($out_fields_ar, $user_id, $course_instance_id, $node_id);
        foreach ($history as $visit) {
            // $debug=1; mydebug(__LINE__,__FILE__,$visit);$debug=0;
            if ($visit[1]== $user_id) {
                $visit_count++;
            }
        }
        return $visit_count;
    }

    public static function is_visited_by_classFN($node_id,$course_instance_id,$course_id) {
        //  returns  the number of visits for this node for instance $course_instance_id

        $dh = $GLOBALS['dh'];
        $error = $GLOBALS['error'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $sess_id_course_instance = $_SESSION['sess_id_course_instance'];
        $sess_id_node = $_SESSION['sess_id_node'];
        $sess_id_course = $_SESSION['sess_id_course'];
        $sess_id_user = $_SESSION['sess_id_user'];
        $debug = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;

        $out_fields_ar = array('id_nodo','data_visita');
        $history = $dh->find_nodes_history_list($out_fields_ar, "", $course_instance_id, $node_id);
        $visit_count = count($history);

        return $visit_count;
    }

    public static function is_visitedFN($node_id) {
        //  returns  the number of global visits for this node

        $dh = $GLOBALS['dh'];
        $debug = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
        $visit_count = 0;
        $out_fields_ar = array('n_contatti');
        //$search_fields_ar = array('id_nodo');
        //$history = $dh->find_nodes_list_by_key($out_fields_ar, $node_id, $search_fields_ar); ???
        $clause = "id_nodo = '$node_id'";
        $history = $dh->_find_nodes_list($out_fields_ar, $clause);
        $visit_count = sizeof($history)-1;
        return $visit_count;
    }

    /**
     * gets the last files for the user in course isntance shared docs area.
     *
     * @param number $id_course_instance isntance id for which to get the files.
     * @param number $maxFiles max number of  files to return
     *
     * @return array if success|null if no file exists or on error
     */
    public function get_new_files($id_course_instance, $maxFiles = 3)
    {
    	$dh        = $GLOBALS['dh'];
    	$common_dh = $GLOBALS['common_dh'];

    	$retval = null;

    	$lastAccessArr = $this->_get_last_accessFN($id_course_instance,null,false);
    	$lastAccess = (!is_array($lastAccessArr)) ? time() : intval($lastAccessArr[1]);

    	$id_course = $dh->get_course_id_for_course_instance($id_course_instance);
    	$course_ha = $dh->get_course($id_course);

    	if (!AMA_DataHandler::isError($course_ha)){
    		$author_id = $course_ha['id_autore'];
	    	//il percorso in cui caricare deve essere dato dal media path del corso, e se non presente da quello di default
	    	if($course_ha['media_path'] != "") {
	    		$media_path = $course_ha['media_path']  ;
	    	}
	    	else {
	    		$media_path = MEDIA_PATH_DEFAULT . $author_id ;
	    	}
	    	$download_path = ROOT_DIR . $media_path;
    	}

    	if (isset($download_path) && is_dir($download_path))
    	{
    		$sortedDir = array();
    		$handle = opendir($download_path);

    		while ($file = readdir($handle))
    		{
    			if ($file !='.' && $file != '..')
    			{
    				$ctime  = filectime($download_path . '/' . $file);
    				$filesPart = explode('_', $file,6);
    				// index 0 is the course instance id
    				$file_id_course = $filesPart[0];
    				// index 1 is the file sender, get her info
    				$file_senderArray = $common_dh->get_user_info($filesPart[1]);
    				/*
    				 *  add files only if:
    				 *  + they belong to the passed instance OR
    				 *  	they've been added to the course by an author
    				 *  + they've been modified after user last access
    				 */
    				if (!AMA_DB::isError($file_senderArray) &&
    					  ($file_id_course == $id_course_instance ||
    					  ($file_senderArray['tipo'] == AMA_TYPE_AUTHOR && $file_id_course == $id_course)) &&
    				    $ctime >= $lastAccess)
    				{
    					$arrKey = $ctime . '-' . $file;
    					$sortedDir[$arrKey]['link'] = $file;
    					$sortedDir[$arrKey]['id_node'] = $id_course . '_' . ADA_DEFAULT_NODE;
    					$sortedDir[$arrKey]['id_course'] = $id_course;
    					$sortedDir[$arrKey]['id_course_instance'] = $id_course_instance;
    					$sortedDir[$arrKey]['displaylink'] = $filesPart[count($filesPart)-1];
    				}
    			}
    		}
    		closedir($handle);

    		if (!empty($sortedDir))
    		{
    			krsort($sortedDir);
    			$retval = array_slice($sortedDir, 0,$maxFiles);
    		}
    	}
    	return $retval;
    }

    /**
     * sets the proper $_SESSION var of userObj and redirects to user home page
     *
     * @param ADALoggableUser $userObj user object to be used to set $_SESSION vars
     * @param boolean $remindMe true if remindme check box has been checked
     * @param string $language lang selection at login form: language to be set
     * @param Object $loginProviderObj login provider class used, null if none used
     */
    public static function setSessionAndRedirect($userObj, $remindMe, $language, $loginProviderObj = null, $redirectURL = null) {
    	if ($userObj->getStatus() == ADA_STATUS_REGISTERED)
    	{
    		/**
    		 * @author giorgio 12/dic/2013
    		 * when a user sucessfully logs in, regenerate her session id.
    		 * this fixes a quite big problem in the 'history_nodi' table
    		 */
    		if (isset($remindMe) && intval($remindMe)>0) {
    			ini_set('session.cookie_lifetime', 60 * 60 * 24 * ADA_SESSION_LIFE_TIME);  // day cookie lifetime
    		}
    		session_regenerate_id(true);

    		$user_default_tester = $userObj->getDefaultTester();

    		if (!MULTIPROVIDER && $userObj->getType()!=AMA_TYPE_ADMIN)
    		{
    			if ($user_default_tester!=$GLOBALS['user_provider'])
    			{
    				// if the user is trying to login in a provider
    				// that is not his/her own,
    				// redirect to his/her own provider home page
    				$redirectURL = preg_replace("/(http[s]?:\/\/)(\w+)[.]{1}(\w+)/", "$1".$user_default_tester.".$3", $userObj->getHomePage());
    				header('Location:'.$redirectURL);
    				exit();
    			}
    		}

    		if (defined('MODULES_GDPR') && MODULES_GDPR === true) {
    			// check if user has accepted the mandatory privacy policies
    			$gdprApi = new GdprAPI();
    			if (!$gdprApi->checkMandatoryPoliciesForUser($userObj)) {
    				$_SESSION[GdprPolicy::sessionKey]['post'] = $_POST;
    				if (!is_null($redirectURL)) {
    					$_SESSION['subscription_page'] = $redirectURL;
    				}
    				$_SESSION[GdprPolicy::sessionKey]['redirectURL'] = !is_null($redirectURL) ? $redirectURL : $userObj->getHomePage();
    				$_SESSION[GdprPolicy::sessionKey]['userId'] = $userObj->getId();
    				$_SESSION[GdprPolicy::sessionKey]['loginRepeaterSubmit'] = basename($_SERVER['SCRIPT_NAME']);
    				redirect(MODULES_GDPR_HTTP . '/'. GdprPolicy::acceptPoliciesPage);
    			}
    		}

    		// user is a ADAuser with status set to 0 OR
    		// user is admin, author or switcher whose status is by default = 0
    		$_SESSION['sess_user_language'] = $language;
    		$_SESSION['sess_id_user'] = $userObj->getId();
    		$GLOBALS['sess_id_user']  = $userObj->getId();
    		$_SESSION['sess_id_user_type'] = $userObj->getType();
    		$GLOBALS['sess_id_user_type']  = $userObj->getType();
    		$_SESSION['sess_userObj'] = $userObj;

    		/* unset $_SESSION['service_level'] to allow the correct label translatation according to user language */
    		unset($_SESSION['service_level']);

    		if($user_default_tester !== NULL) {
    			$_SESSION ['sess_selected_tester'] = $user_default_tester;
    			// sets var for non multiprovider environment
    			$GLOBALS ['user_provider'] = $user_default_tester;
    		}

    		if (!is_null($loginProviderObj)) {
    			$_SESSION['sess_loginProviderArr']['className'] = get_class($loginProviderObj);
    			$_SESSION['sess_loginProviderArr']['id'] = $loginProviderObj->getID();
    			$loginProviderObj->addLoginToHistory($userObj->getId());
    		}
    		if (is_null($redirectURL)) {
                $redirectURL = $userObj->getHomePage();
                if (isset($_REQUEST['r']) && strlen(trim($_REQUEST['r']))>0) {
                    $r = trim($_REQUEST['r']);
                    // redirect only if passed URL host matches HTTP_ROOT_DIR host
                    if (parse_url($r, PHP_URL_HOST) === parse_url(HTTP_ROOT_DIR, PHP_URL_HOST)) {
                        $redirectURL = $r;
                        unset($_REQUEST['r']);
                    }
                }
            }
    		header('Location:'.$redirectURL);
    		exit();
    	}

    	return false;
    }
}

/**
 * AdaAbstractUser class:
 *
 * This is just a rename of the 'old' ADAUser class which is now declared
 * and implemented in its own 'ADAUser.inc.php' file required below.
 *
 * This was made abstract in order to be 100% sure that nobody will ever
 * instate it. Must instantiate the proper ADAUser class instead.
 *
 * The whole ADA system will than be able to use the usual ADAUser class,
 * but with extended methods and properties for each customization.
 *
 *
 * @author giorgio 04/giu/2013
 *
 */

require_once 'ADAUser.inc.php';

abstract class ADAAbstractUser extends ADALoggableUser {
    //protected $history;

	protected  $whatsnew;

    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/browsing/user.php');
        $this->setEditProfilePage('browsing/edit_user.php');
        $this->history = NULL;
    }

    /**
     * Must override setUserId method to get $whatsnew whenever we set $id_user
     *
     *
     * @param $user_id
     * @author giorgio 03/mag/2013
     */
    public function setUserId($id_user) {
    	parent::setUserId($id_user);
    	$this->setwhatsnew (MultiPort::get_new_nodes($this));
    }

    /**
     * whatsnew getter
     * @return array returns whatsnew array, populated in the constructor
     * @author giorgio

     */
    public function getwhatsnew ()
    {
        return $this->whatsnew;
    }

    /**
     * whatsnew setter.
     *
     * @param array $newwhatsnew	new array to be set as the whatsnew array
     *
     * @return
     */
    public function setwhatsnew($newwhatsnew)
    {
        $this->whatsnew = $newwhatsnew;
    }

    /**
     * updates $whatsnew array based on the values from the db.
     *
     * @author giorgio
     *
     */
    public function updateWhatsNew()
    {
        $this->whatsnew = MultiPort::update_new_nodes_in_session($this);
    }

    /**
     *
     * @param $id_course_instance
     * @return void
     */
    public function set_course_instance_for_history($id_course_instance) {
        $historyObj = new History($id_course_instance, $this->id_user);
        // se non e' un errore, allora
        $this->history = $historyObj;
    }

    public function getHistoryInCourseInstance($id_course_instance)
    {
        if(($this->history == null) || ($this->history->id_course_instance != $id_course_instance)) {
            $this->history = new History($id_course_instance, $this->id_user);
        }
        return $this->history;
    }


// MARK: existing methods

    /**
     *
     * @param $id_user
     * @param $id_course_instance
     * @return integer
     */
    public function get_student_level($id_user, $id_course_instance) {
        $dh = $GLOBALS['dh'];
        // FIXME: _get_student_level was a private method, now it is public.
        $user_level = $dh->_get_student_level($id_user,$id_course_instance);
        if (AMA_DataHandler::isError($user_level)) {
            $this->livello = 0;
        }
        else {
            $this->livello = $user_level;
        }
        return $this->livello;
    }

    /**
     *
     * @param $id_user
     * @param $id_course_instance
     * @return void
     */
    public function get_student_score($id_user, $id_course_instance) {
        // NON CI SONO ESERCIZI, NON DOVREBBE ESSERCI PUNTEGGIO
    }

    /**
     *
     * @param $id_student
     * @param $id_course_instance
     * @return integer
     */
    public function get_student_status($id_student,$id_course_instance) {
        $dh = $GLOBALS['dh'];

        $this->status = 0;
        if ($this->tipo == AMA_TYPE_STUDENT) {
            $student_courses_subscribe_statusHa = $dh->course_instance_student_presubscribe_get_status($id_student);
            if (is_object($student_courses_subscribe_statusHa))
                return $student_courses_subscribe_statusHa->error;
            if (empty($student_courses_subscribe_statusHa))
                return "";
            foreach ($student_courses_subscribe_statusHa as $course_subscribe_status) {
                if ($course_subscribe_status['istanza_corso'] == $id_course_instance) {
                    $this->status = $course_subscribe_status['status'];
                    break;
                }
            }
        }
        return $this->status;
    }

    /**
     *
     * @param $id_student
     * @return string
     */
    public function get_student_family($id_student) {
        if (isset($this->template_family)) {
            return $this->template_family;
        }
        else {
            return ADA_TEMPLATE_FAMILY;
        }
    }

    /**
     *
     * @param $id_student
     * @param $node_type
     * @return integer
     */
    public function total_visited_nodesFN($id_student,$node_type="") {
        //  returns 0 or the number of nodes visited by this student
        if(is_object($this->history)) {
            return $this->history->get_total_visited_nodes($node_type);
        }
        return 0;
    }

    /**
     *
     * @param $id_student
     * @return integer
     */
    public function total_visited_notesFN($id_student) {
        $visited_nodes_count = $this->total_visited_nodesFN($id_student,ADA_NOTE_TYPE);
        return $visited_nodes_count;
    }

    public function getDefaultTester() {
        return NULL;
    }

    function get_exercise_dataFN($id_course_instance,$id_student="") {
        $dh = $GLOBALS['dh'];
        $out_fields_ar = array('ID_NODO','ID_ISTANZA_CORSO','DATA_VISITA','PUNTEGGIO','COMMENTO','CORREZIONE_RISPOSTA_LIBERA');
        $dataHa = $dh->find_ex_history_list($out_fields_ar, $this->id_user, $id_course_instance);

        if (AMA_DataHandler::isError($dataHa) || empty($dataHa)) {
            $this->user_ex_historyAr = '';
        } else {
            aasort($dataHa,array("-1")) ;
            $this->user_ex_historyAr = $dataHa;
        }
    }

	function history_ex_done_FN($id_student,$id_profile="",$id_course_instance=""){
		/**
			Esercizi svolti
			Crea array con nodo e punteggio, ordinato in ordine
			decrescente di punteggio.
		*/

			$dh = $GLOBALS['dh'];
			$error = $GLOBALS['error'];
			$http_root_dir = $GLOBALS['http_root_dir'];
			$debug = isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;

		if (empty($id_profile))
			$id_profile = AMA_TYPE_TUTOR;

		$ids_nodi_padri = array();
		if(!empty($this->user_ex_historyAr)){
			foreach($this->user_ex_historyAr as $k=>$e){
				$exer_stats_ha[$k]['nome'] = $e[0];
				$exer_stats_ha[$k]['titolo'] = $e[1];
				$exer_stats_ha[$k]['id_nodo_parent'] = $e[2];
				$exer_stats_ha[$k]['id_exe'] = $e[3];
				$exer_stats_ha[$k]['id_nodo'] = $e[4];
				$exer_stats_ha[$k]['id_istanza'] = $e[5];
				$exer_stats_ha[$k]['data'] = $e[6];
				$exer_stats_ha[$k]['punteggio'] = $e[7];
				$exer_stats_ha[$k]['commento'] = $e[8];
				$exer_stats_ha[$k]['correzione'] = $e[9];

				$ids_nodi_padri[] = $exer_stats_ha[$k]['id_nodo_parent'];
			}

			if (!empty($ids_nodi_padri)) {
				$nodi_padri = $dh->get_nodes($ids_nodi_padri,array('nome','titolo'));
			}

			$label1 = translateFN('Esercizio');
			$label2 = translateFN('Data');
			$label3 = translateFN('Punteggio');
			$label4 = translateFN('Corretto');
			$data = array();

			foreach($exer_stats_ha as $k=>$e){
				$id_exe = $e['id_exe'];
				$id_nodo = $e['id_nodo'];
				$nome = $e['nome'];
				$titolo = $e['titolo'];
				$nome_padre = $nodi_padri[$e['id_nodo_parent']]['nome'];

				$punteggio = $e['punteggio'];
				if (($e['commento']!='-') OR ($e['correzione']!='-')) $corretto =  translateFN('Si');
				else $corretto =  translateFN('-');

				$date = ts2dFN($e['data'])." ".ts2tmFN($e['data']);

				if ($id_profile == AMA_TYPE_TUTOR) {
					$zoom_module = "$http_root_dir/tutor/tutor_exercise.php";
				}
				else {
					$zoom_module = "$http_root_dir/browsing/exercise_history.php";
				}

				// vito, 18 mar 2009
				$link = CDOMElement::create('a');
				if(!empty($id_course_instance) && is_numeric($id_course_instance)) {
					$link->setAttribute('href',$zoom_module.'?op=exe&id_exe='.$id_exe.'&id_student='.$id_student.'&id_nodo='.$id_nodo.'&id_course_instance='.$id_course_instance);
				}
				else {
					$link->setAttribute('href',$zoom_module.'?op=exe&id_exe='.$id_exe.'&id_student='.$id_student.'&id_nodo='.$id_nodo);
				}
				$link->addChild(new CText($nome_padre.' > '));
				$link->addChild(new CText($nome));
				$html = $link->getHtml();

				$data[] = array (
					$label1=>$html,
					$label2=>$date,
					$label3=>$punteggio,
					$label4=>$corretto
				);
			}
			$t = new Table();
			$t->initTable('0','center','1','1','90%','','','','','1','0','','default','exercise_table');
			$t->setTable($data,translateFN("Esercizi e punteggio"),translateFN("Esercizi e punteggio"));
			$res = $t->getTable();
			$res= preg_replace('/class="/', 'class="'.ADA_SEMANTICUI_TABLECLASS.' ', $res, 1); // replace first occurence of class
		}else{
			$res = translateFN("Nessun esercizio.");
		}
		return $res;
	} //end history_ex_done_FN

	/**
	 * this function fix user certificate.
	 *
	 * @return boolean
	 */
	 public function Check_Requirements_Certificate() {
	 	/* be implemented according to the use cases */
	 	return true;
	 }
}

/**
 *
 *
 */
class ADAPractitioner extends ADALoggableUser {
    protected $tariffa;
    protected $profilo;
    protected $isSuper;

    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->tariffa = isset($user_dataAr['tariffa']) ? $user_dataAr['tariffa'] : null;
        $this->profilo = isset($user_dataAr['profilo']) ? $user_dataAr['profilo'] : null;
        $this->isSuper = isset($user_dataAr['tipo']) && $user_dataAr['tipo']==AMA_TYPE_SUPERTUTOR;
        /**
         * @author giorgio 10/apr/2015
         *
         * a supertutor is a tutor with the isSuper property set to true
         */
        if ($this->isSuper && $this->tipo==AMA_TYPE_SUPERTUTOR) $this->tipo = AMA_TYPE_TUTOR;
        $this->setHomePage(HTTP_ROOT_DIR.'/tutor/tutor.php');
        $this->setEditProfilePage('tutor/edit_tutor.php');
    }

    /**
     * converts the Practitioner to an ADAUser
     *
     * @return ADAUser
     */
    public function toStudent() {
    	return new ADAUser(array_merge(array('id'=>$this->getId()),$this->toArray()));
    }

    /*
   * getters
    */
    public function getFee() {
        return $this->tariffa;
    }

    public function getProfile() {
        return $this->profilo;
    }

    public function isSuper() {
    	return (bool) $this->isSuper;
    }

    /*
   * setters
    */
    public function setFee($fee) {
        $this->tariffa = $fee;
    }

    public function setProfile($profile) {
        $this->profilo = $profile;
    }

    public function fillWithArrayData($user_dataAr=null) {
    	if (!is_null($user_dataAr)) {
    		parent::fillWithArrayData($user_dataAr);
    		 
    		$this->tariffa = isset($user_dataAr['tariffa']) ? $user_dataAr['tariffa'] : 0;
    		$this->profilo = isset($user_dataAr['profilo']) ? $user_dataAr['profilo'] : null;
    	}
    }

    public function toArray() {
        $user_dataAr = parent::toArray();

        $user_dataAr['tariffa'] = $this->tariffa;
        $user_dataAr['profilo'] = $this->profilo;

        return $user_dataAr;
    }
}

/**
 *
 *
 */
class ADASwitcher extends ADALoggableUser {
    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/switcher/switcher.php');
        $this->setEditProfilePage('switcher/edit_switcher.php');
    }
}

/**
 *
 *
 */
class ADAAuthor extends ADALoggableUser {
    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/services/author.php');
        $this->setEditProfilePage('services/edit_author.php');

    }
}

/**
 *
 *
 */
class ADAAdmin extends ADALoggableUser {
    public function __construct($user_dataAr=array()) {
        parent::__construct($user_dataAr);

        $this->setHomePage(HTTP_ROOT_DIR.'/admin/admin.php');
    }
}
