<?php
/**
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 * @version
 * @package
 * @license
 * @copyright (c) 2009 Lynx s.r.l.
 */

/**
 * AMA_Error implements a class for reporting portable errors
 * It extends the RuntimeException standard PhP Exception
 *
 * @author Guglielmo Celata <guglielmo@celata.com>
 * @author giorgio <g.consorti@lynxlab.com>
 */

class AMA_Error extends RuntimeException
{
  /**
   * 
   * @var array
   */
  private $errorMessages;
  public $code;
  
  /**
   * AMA_Error constructor.
   *
   * @param $code mixed AMA error code, or string with error message.
   * @param $debuginfo additional debug info, such as the last query
   *
   * @access public
   *
   */
  function AMA_Error($code = AMA_ERROR, $debuginfo = null) {
    $this->errorMessages = array(
    AMA_ERROR                 => 'sconosciuto',                        // 1 unknown error
    AMA_ERR_ADD               => 'aggiunta del record non riuscita',           // 2 error while adding
    AMA_ERR_REMOVE            => 'cancellazione del record non riuscita',     // 3 error while removing
    AMA_ERR_UNIQUE_KEY        => 'record gi&agrave; esistente',                      // 4  "unique key violated"
    AMA_ERR_NOT_FOUND         => 'record non trovato',                        // 5  "record was not found"
    AMA_ERR_INCONSISTENT_DATA => 'inconsistenza nei dati',                    // 6  "inconsistency in the data was detected"
    AMA_ERR_UPDATE            => 'aggiornamento del database non riuscito',    // 7  "error while updating"
    AMA_ERR_REF_INT_KEY       => 'violazione integrit&agreve;referenziale',            // 8  "referential integrity key violated"
    AMA_ERR_WRONG_USER_TYPE   => 'tipo di utente non corrispondente',         // 9  "wrong type of user detected"
    AMA_ERR_TOO_FEW_ARGS      => 'numero di argomenti per la funzione non corrispondente',// 10  "too few arguments for the function"
    AMA_ERR_WRONG_ARGUMENTS   => 'tipo di argomenti per la funzione non corrispondente',  // 11  "wrong type of argumets fot the function"
    AMA_ERR_SEND_MSG          => 'invio del messaggio non riuscito',           // 12  "error while sending message"
    AMA_ERR_READ_MSG          => 'lettura del messaggio non riuscita',        // 13  "error while reading message"
    AMA_ERR_TRANSACTION_FAIL  => 'transazione fallita',                       //14  "the transaction failed"
    AMA_ERR_GET               => 'lettura dal database  non riuscita'                            //15  "error in get-type function"
    );
    
    $this->code = $code;
    	
    if (is_int($code)) {
    	parent::__construct(self::errorMessage($code), $code );
    } 
    else {
    	parent::__construct($code, AMA_ERROR);
    }
  }

  /**
   * Return a textual error message for an AMA error object
   *
   * @access public
   *
   * @param none
   *
   * @return string error message, or translateFN("unknown") if the error code was
   * not recognized
   */
  function errorMessage($code = "") {
    if (empty($code)) {
      $code = $this->code;
    }
    if (isset($this->errorMessages[$code])) {
      return $this->errorMessages[$code];
    }
    else {
      return $this->errorMessages[AMA_ERROR].": $code";
    }
  }
}
?>