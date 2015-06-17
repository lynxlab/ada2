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
class hybridLogin extends AbstractLogin
{
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
	 * @param Hybrid_Provider_Adapter $authProvider
	 * 
	 * @access public
	 */
	public function addADAErrorCallBack($authProvider)
	{
		$authProvider->logout();
	}
}
