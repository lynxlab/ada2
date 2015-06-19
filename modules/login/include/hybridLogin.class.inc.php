<?php
/**
 * LOGIN MODULE
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * google login provider implementation
 */
require_once MODULES_LOGIN_PATH . '/include/Hybrid/Auth.php';

class hybridLogin extends AbstractLogin
{
	/**
	 * Hybrid_Auth object
	 */
	private $hybridauth = null;
	
	/**
	 * Hybrid_Provider_Adapter object
	 */
	private $authProvider = null;
	
	public function __construct($id=null)
	{
		parent::__construct($id);		
		$this->hybridauth = new Hybrid_Auth($this->getConfigFromOptions());		
	}
	
	/**
	 * performs user login using hybridLogin.php redirection
	 * 
	 * (non-PHPdoc)
	 * @see iLogin::doLogin()
	 */
	public function doLogin($name, $pass, $remindMe, $language)
	{
		redirect(MODULES_LOGIN_HTTP . '/hybridLogin.php?id='.$this->id.
				'&remindme='.intval($remindMe).'&lang='.$language);
	}
	
	/**
	 * callback method for addADA success handling, called by
	 * parent::addADAUser just before redirecting
	 * 
	 * This will download user avatar image to proper location
	 *  
	 * @param ADALoggableUser $userObj
	 * @param string $downloadURL
	 * 
	 * @access public
	 */
	public function addADASuccessCallBack($userObj, $downloadURL, $avatar)
	{

		if (is_object($userObj) && $userObj instanceof ADALoggableUser) {
			
			if (!is_null($avatar) && !is_null($downloadURL)) {
				$destDir = ADA_UPLOAD_PATH.$userObj->getId();
				if (!is_dir($destDir)) mkdir($destDir);
				$destFile = $destDir . DIRECTORY_SEPARATOR . $avatar;
				/**
				 * save the image locally from the url
				 */
				$ch = curl_init($downloadURL);
				$fp = fopen($destFile, 'wb');
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
				/**
				 * resize the image if needed
				*/
				require_once ROOT_DIR .'/browsing/include/class_image.inc.php';
				$id_img = new ImageDevice();
				$new_img = $id_img->resize_image($destFile, AVATAR_MAX_WIDTH, AVATAR_MAX_HEIGHT);
				if(stristr($destFile, 'png')) {
					imagepng($new_img,$destFile);
				} else if(stristr($destFile, 'jpeg')!==false || stristr($destFile, 'jpg')!==false) {
					imagejpeg($new_img,$destFile);
				} else if(stristr($destFile, 'gif')) {
					imagegif($new_img,$destFile);
				}
			}
		}
	}
	
	/**
	 * callback method for addADA error handling, called by
	 * parent::addADAUser just before redirecting 
	 * 
	 * @access public
	 */
	public function addADAErrorCallBack()
	{
		$this->logOutFromProvider();
	}
	
	/**
	 * get config array for Hybrid_Auth from options stored in the DB
	 * 
	 * @return array config array for Hybrid_Auth
	 * 
	 * @access public
	 */
	public function getConfigFromOptions()
	{
		$options = $this->loadOptions();
		$providerName = ucfirst(strtolower($this->loadProviderName()));
		
		switch ($providerName) {
			case 'Google':
			case 'Facebook':
				$config = array(
					'base_url' =>   $options['base_url'],
					'providers' => array (
						$providerName => array (
							'enabled' => true,
							'keys'    => array (
								'id' => $options['id'],
								'secret' => $options['secret']
							)
						)
					)
				);
				// optionals
				if (isset($options['scope'])) {
					$config['providers'][$providerName]['scope'] = $options['scope'];
				}
				break;
			default:
				$config = null;
		}
		return $config;
	}
	
	/**
	 * Authenticates to the login provider
	 *
	 * @access public
	 */
	public function authenticate() {
		$this->authProvider = $this->hybridauth->authenticate($this->loadProviderName());
	}
	
	/**
	 * Gets user profile from login provider
	 *
	 * @access public
	 */
	public function getUserProfile() {
		if (is_null($this->authProvider)) $this->authenticate();
		return $this->authProvider->getUserProfile();
	}
	
	/**
	 * Logs out from the login provider
	 *
	 * @access public
	 */
	public function logOutFromProvider() {
		return $this->authProvider->logout();
	}
	
	/**
	 * Builds ADA user array from a user_profile coming from login provider
	 * 
	 * @param stdClass $user_profile
	 * 
	 * @return array an array filled with user data, ready to be saved
	 * 
	 * @access public
	 */
	public function buildADAUserFromProviderObj($user_profile) {
		/**
		 * Prepare email field
		 */
		if (isset($user_profile->emailVerified) && strlen($user_profile->emailVerified)>0) {
			$email = $user_profile->emailVerified;
		} else if (isset($user_profile->email) && strlen($user_profile->email)>0) {
			$email = $user_profile->email;
		} else $email = null;
		
		/**
		 * prepare birthdate
		 */
		if ($user_profile->birthDay>0 && $user_profile->birthMonth>0 && $user_profile->birthYear>0) {
			$birthDate = sprintf("%02d",$user_profile->birthDay). '/' .
					sprintf("%02d",$user_profile->birthMonth) .'/' .
					$user_profile->birthYear;
		} else $birthDate = null;
		 
		/**
		 * prepare gender
		 */
		if (strtolower($user_profile->gender) == 'male') $gender = 'M';
		else if (strtolower($user_profile->gender) == 'female') $gender = 'F';
		else $gender = null;
		 
		/**
		 * prepare avatar
		 */
		if (isset($user_profile->photoURL) && strlen($user_profile->photoURL)>0) {
			// get the basename and remove any URL arguments
			$avatar = strtok(basename($user_profile->photoURL),'?');
			if (stristr($avatar, '.')===false) $avatar .= '.png';
		} else $avatar = null;
		 
		/**
		 * prepare language
		 */
		$language = null;
		if (isset($user_profile->language) && strlen($user_profile->language)>0) {
			if (strlen($user_profile->language)>2) {
				$lang = substr($user_profile->language, 0,2);
			} else $lang = $user_profile->language;
			
			foreach (Translator::getSupportedLanguages() as $supportedLang) {
				if (strtolower($supportedLang['codice_lingua']) === strtolower($lang)) {
					$language = $supportedLang['id_lingua'];
					break;
				}
			}
		}
		 
		/**
		 * build user array
		 */
		$adaUser = array(
				'nome' => $user_profile->firstName,
				'cognome' => $user_profile->lastName,
				'email' => $email,
				'username' => $email,
				'indirizzo' => (isset($user_profile->address) && strlen($user_profile->address)>0) ? $user_profile->address : null,
				'citta' => (isset($user_profile->city) && strlen($user_profile->city)>0) ? $user_profile->city : null,
				'provincia' => (isset($user_profile->region) && strlen($user_profile->region)>0) ? $user_profile->region : null,
				'nazione' => (isset($user_profile->country) && strlen($user_profile->country)>0) ? $user_profile->country : null,
				'birthdate' => $birthDate,
				'sesso' => $gender,
				'telefono' => (isset($user_profile->phone) && strlen($user_profile->phone)>0) ? $user_profile->phone : null,
				'lingua' => $language,
				'cap' => (isset($user_profile->zip) && strlen($user_profile->zip)>0) ? $user_profile->zip : '',
				'avatar' => $avatar,
				'birthcity' => '',
				'matricola' => '',
				'stato' => ''
		);
		
		return $adaUser;
	}
}
