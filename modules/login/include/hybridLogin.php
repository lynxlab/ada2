<?php
/**
 * LOGIN MODULE
 *
 * @package     login module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2015-2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\Login;

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

/**
 * google login provider implementation
 */
class hybridLogin extends abstractLogin
{
	/**
	 * class for managing options data
	 */
	const MANAGEMENT_CLASS = __NAMESPACE__ . '\\hybridManagement';

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
	}

	/**
	 * sets the hybridauth object.
	 * MUST be called before any attempt to authenticate with the provider
	 *
	 * @access public
	 */
	public function loadHybridAuth()
	{
		$this->hybridauth = new Hybridauth($this->getConfigFromOptions());
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

		if (is_object($userObj) && $userObj instanceof \ADALoggableUser) {

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
				$id_img = new \ImageDevice();
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
			case 'Microsoftgraph':
				$config = array(
					'callback' => trim(HttpClient\Util::getCurrentUrl()),
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
				if (isset($options['fields'])) {
					$config['providers'][$providerName]['fields'] = $options['fields'];
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
	 * @return number successfulOptionsID (note that only one option set is supported for this login provider)
	 *
	 * @access public
	 */
	public function authenticate() {
		$this->authProvider = $this->hybridauth->authenticate($this->loadProviderName());
		return intval($this->options['providers_options_id']);
	}

	/**
	 * Gets user profile from login provider
	 *
	 * @access public
	 */
	public function getUserProfile() {
		if (is_null($this->authProvider)) $this->authenticate($this->loadProviderName());
		return $this->authProvider->getUserProfile();
	}

	/**
	 * Logs out from the login provider
	 *
	 * @access public
	 */
	public function logOutFromProvider() {
		return $this->authProvider->disconnect();
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

			foreach (\Translator::getSupportedLanguages() as $supportedLang) {
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
				'nazione' => null,
				'birthdate' => $birthDate,
				'sesso' => $gender,
				'telefono' => (isset($user_profile->phone) && strlen($user_profile->phone)>0) ? $user_profile->phone : null,
				'lingua' => $language,
				'cap' => (isset($user_profile->zip) && strlen($user_profile->zip)>0) ? $user_profile->zip : '',
				'avatar' => $avatar,
				'birthcity' => (isset($user_profile->region) && strlen($user_profile->region)>0) ? $user_profile->region : '',
				'matricola' => '',
				'stato' => ''
		);

		return $adaUser;
	}

	/**
	 * generate HTML for login provider configuration page
	 */
	public function generateConfigPage() {
		$optionSetList = $this->loadOptions();

		if (isset($optionSetList['providers_options_id']) && intval($optionSetList['providers_options_id'])>0) {
			$optionID = intval($optionSetList['providers_options_id']);
		} else $optionID = null;

		// If no option id return abstract config page (aka 'no options to configure' message)
		if (is_null($optionID)) return parent::generateConfigPage();

		$configIndexDIV = \CDOMElement::create('div','id:configindex');

		$newButton = \CDOMElement::create('button');
		$newButton->setAttribute('class', 'newButton tooltip top');
		$newButton->setAttribute('title', translateFN('Clicca per creare un nuova chiave'));
		$newButton->setAttribute('onclick', 'javascript:addOptionRow();');
		$newButton->addChild (new \CText(translateFN('Nuova Chiave')));
		$configIndexDIV->addChild($newButton);
		$configIndexDIV->addChild(\CDOMElement::create('div','class:clearfix'));

		$tableOutData = array();
		if (!\AMA_DB::isError($optionSetList)) {
			unset ($optionSetList['optionscount']);
			unset ($optionSetList['providers_options_id']);
			/**
			 * Add an empty table with one row that will be hidden
			 * and will be used as a template when adding new rows
			 */
			$optionSetList = array(''=>'') + $optionSetList;

			$labels = array (translateFN('chiave'), translateFN('valore'),translateFN('azioni'));
			foreach ($optionSetList as $i=>$elementArr) {
				$links = array();
				$linksHtml = "";

				for ($j=0;$j<1;$j++) {
					switch ($j) {
						case 0:
							$type = 'delete';
							$title = translateFN ('Cancella');
							$link = 'deleteOptionSet($j(this), '.$optionID.', \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
							break;
					}

					if (isset($type)) {
						$links[$j] = \CDOMElement::create('li','class:liactions');
						$linkshref = \CDOMElement::create('button');
						$linkshref->setAttribute('onclick','javascript:'.$link);
						$linkshref->setAttribute('class', $type.'Button tooltip');
						$linkshref->setAttribute('title',$title);
						if ($j==0) $linkshref->setAttribute('data-delkey', $i);
						$links[$j]->addChild ($linkshref);
						// unset for next iteration
						unset ($type);
					}
				}
				if (!empty($links)) {
					$linksul = \CDOMElement::create('ul','class:ulactions');
					foreach ($links as $link) $linksul->addChild ($link);
					$linksHtml = $linksul->getHtml();
				} else $linksHtml = '';

				$tableOutData[$i] = array (
						$labels[0]=>$i,
						$labels[1]=>str_replace(array("\r\n", "\r", "\n"), "<br />", $elementArr),
						$labels[2]=>$linksHtml);
			}

			$emptyrow = array(array_shift($tableOutData));
			$EmptyTable = \BaseHtmlLib::tableElement('id:empty'.strtoupper((new \ReflectionClass($this))->getShortName()),$labels,$emptyrow);
			$EmptyTable->setAttribute('style', 'display:none');
			$EmptyTable->setAttribute('class', ADA_SEMANTICUI_TABLECLASS);

			$OutTable = \BaseHtmlLib::tableElement('id:complete'.strtoupper((new \ReflectionClass($this))->getShortName()).'List',
					$labels,$tableOutData,'',translateFN('Opzioni '.strtoupper($this->loadProviderName())));
			$OutTable->setAttribute('data-optionid', $optionID);
			$OutTable->setAttribute('class', ADA_SEMANTICUI_TABLECLASS);

			$configIndexDIV->addChild($EmptyTable);
			$configIndexDIV->addChild($OutTable);

			// if there are more than 10 rows, repeat the add new button below the table
			if (count($optionSetList)>10) {
				$bottomButton = clone $newButton;
				$bottomButton->setAttribute('class', 'newButton bottom tooltip');
				$configIndexDIV->addChild($bottomButton);
			}
		}
		return $configIndexDIV;
	}

}
