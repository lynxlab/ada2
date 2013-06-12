<?php
class TokenManager
{
  public static function createTokenForUserRegistration($userObj) {
    return self::createToken(new UserRegistrationToken(), $userObj);
  }

  public static function createTokenForPasswordChange($userObj) {
    return self::createToken(new ChangePasswordToken(), $userObj);
  }

  public static function updateToken(ActionToken $token) {
    $common_dh = $GLOBALS['common_dh'];

    $token_dataAr = $token->toArray();
    $result = $common_dh->update_token($token_dataAr);
    if(AMA_Common_DataHandler::isError($result)) {
      return false;
    }
    return true;
  }

  private static function createToken($tokenObj, $userObj) {
    $tokenObj->generateTokenStringFrom($userObj->getUserName());
    $tokenObj->setUserId($userObj->getId());

    if(self::save($tokenObj)) {
      return $tokenObj;
    }
    return false;
  }

  private static function save(ActionToken $token) {
    $common_dh = $GLOBALS['common_dh'];

    $token_dataAr = $token->toArray();
    $result = $common_dh->add_token($token_dataAr);
    if (AMA_Common_DataHandler::isError($result)) {
      return false;
    }

    return true;
  }
}

class TokenFinder
{
  public static function findTokenForUserRegistration($user_id, $token) {
    $common_dh = $GLOBALS['common_dh'];

    $token_dataAr = $common_dh->get_token($token, $user_id, ADA_TOKEN_FOR_REGISTRATION);
    if(AMA_Common_DataHandler::isError($token_dataAr)) {
      return false;
    }
    $tokenObj = new UserRegistrationToken();
    $tokenObj->fromArray($token_dataAr);
    return $tokenObj;
  }

  public static function findTokenForPasswordChange($user_id, $token) {
    $common_dh = $GLOBALS['common_dh'];

    $token_dataAr = $common_dh->get_token($token, $user_id, ADA_TOKEN_FOR_PASSWORD_CHANGE);
    if(AMA_Common_DataHandler::isError($token_dataAr)) {
      return false;
    }
    $tokenObj = new ChangePasswordToken();
    $tokenObj->fromArray($token_dataAr);
    return $tokenObj;
  }
}

abstract class ActionToken
{
  protected $isValid;
  protected $expiresAfter;
  protected $creationTimestamp;
  protected $userId;
  protected $action;
  protected $tokenString;

  public function isValid() {
    return !$this->alreadyUsed() && !$this->isExpired();
  }

  public function alreadyUsed() {
    return $this->isValid == ADA_TOKEN_IS_NOT_VALID;
  }

  public function markAsUsed() {
    $this->isValid = ADA_TOKEN_IS_NOT_VALID;
  }

  public function isExpired() {
    return ($this->creationTimestamp + $this->expiresAfter) < time();
  }

  public function fromArray($token_dataAr = array()) {
    $this->isValid           = $token_dataAr['valido'];
    $this->creationTimestamp = $token_dataAr['timestamp_richiesta'];
    $this->userId            = $token_dataAr['id_utente'];
    $this->tokenString       = $token_dataAr['token'];
  }

  public function toArray() {
    $token_dataAr = array(
      'token'               => $this->tokenString,
	  'id_utente'           => $this->userId,
      'timestamp_richiesta' => $this->creationTimestamp,
      'azione'              => $this->action,
      'valido'              => $this->isValid
    );

    return $token_dataAr;
  }

  public function setUserId($user_id) {
    $this->userId = $user_id;
  }

  public function generateTokenStringFrom($text) {
    $this->tokenString = sha1($text . $this->creationTimestamp);
  }

  public function getTokenString() {
    return $this->tokenString;
  }

  protected function initializeToken() {
    $this->isValid           = ADA_TOKEN_IS_VALID;
    $this->creationTimestamp = time();
  }
}

class UserRegistrationToken extends ActionToken
{
  public function __construct() {

    parent::initializeToken();

    $this->action       = ADA_TOKEN_FOR_REGISTRATION;
    $this->expiresAfter = ADA_TOKEN_FOR_REGISTRATION_EXPIRES_AFTER;
  }
}

class ChangePasswordToken extends ActionToken
{
  public function __construct() {

    parent::initializeToken();

    $this->action       = ADA_TOKEN_FOR_PASSWORD_CHANGE;
    $this->expiresAfter = ADA_TOKEN_FOR_PASSWORD_CHANGE_EXPIRES_AFTER;
  }
}

?>