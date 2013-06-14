<?php
/**
 * AMA_DataHandler class
 *
 * This is the new implementation of the AMA_DataHandler class that
 * the whole ADA system is used to work with.
 *
 * The new implementation shall basically manage any extra required
 * field that a user may have beside the 'standard ones'. Theese are
 * usually stored in the tables named: 'autore', 'studente', 'tutor'
 * depending upon user's role.
 *
 * PLS NOTE:
 * For the customizations, you must implement all the stuff you need here,
 * keeping in mind that the parent it's always there to help you, kiddy!
 *
 *
 * @package		model
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		AMA_DataHandler
 * @version		0.1
 * @see			AMA_Tester_DataHandler
 */

/**
 * AMA_DataHandler.
 *
 */
class AMA_DataHandler extends AMA_Tester_DataHandler
{
	/**
	 * AMA_DataHandler constructor is inherited from AMA_Tester_DataHandler
	 * for time being there's no need to implement a new one.
	 */


	/**
	 * sets student datas into the student table in the proper tester.
	 * Do no call parent function here, since we do not want to save
	 * standard user (student) data (they're personal data and are saved separately)
	 *
	 * @param number id_student id of the student whose datas are to be updated
	 * @param array datas to be saved
	 *
	 * @return id of saved student on success, AMA_Error on ErrorException
	 * @access public
	 *
	 * @see AMA_Tester_DataHandler::set_student()
	 */
	public function set_student($id_student ,$user_dataAr, $extraTableName = false, $userObj=null) {
		$db =& $this->getConnection();

		/*
		 * if we're not saving extra fields, just call the parent
		* BUT: if we're saving extra fields, we do not call the parent because we want
		* extra fields to be saved by themselves!!
		*/
		$retval = false;
		if (!$extraTableName) $retval = parent::set_student($id_student, $user_dataAr);
		else
		{
			switch ($extraTableName)
			{
				case 'studente':
					$user_id_sql =  'SELECT id_utente_studente FROM '.$extraTableName.' WHERE id_utente_studente=?';
					$user_id = $this->getOnePrepared($user_id_sql, array($id_student));
					// if it's an error return it right away
					if (AMA_DB::isError($user_id)) $retval = $user_id;
					else
					{
						// get ExtraFields array
						$extraFields = $userObj->getExtraFields();
						// if $user_id not found, build an insert into else an update
						if ($user_id===false)
						{
							$saveQry = "INSERT INTO ".$extraTableName." ( id_utente_studente, ";
							$saveQry .= implode(", ", $extraFields);
							$saveQry .= ") VALUES (".$id_student. str_repeat(",?", count($extraFields)).")" ;
						}
						else
						{
							$saveQry = "UPDATE ".$extraTableName." SET ";
							foreach ($extraFields as $num=>$field)
							{
								$saveQry .= $field."=?";
								if ($num < count($extraFields)-1) $saveQry .= ", ";
							}
							$saveQry .= " WHERE id_utente_studente=".$id_student;
						}

						// build valuesAr with extraFields only
						foreach ($extraFields as $field)
						{
							if (isset ($user_dataAr[$field]))
								$valuesAr[] = $user_dataAr[$field];
							else
								$valuesAr[] = null;
						}

						$result = $this->queryPrepared($saveQry, $valuesAr);
						if (AMA_DB::isError($result)) $retval = $result;
						else $retval = true;						
					}						
					break;
				case 'xxx':
					break;
			}
		}

		return $retval; // return true on success, else the erorr
	}


	/**
	 * loads and prepares all extra fields to be put in the
	 * object via the setExtra method called in the multiport
	 * NOTE: this MUST be implemented if user class hasExtra is true.
	 * can be empty or removed (no, it won't be called) if hasExtra is false.
	 *
	 * @param int $userId
	 * @return array extra user data stored in the object
	 */
	public function getExtraData (ADAUser $userObj)
	{
		$db =& $this->getConnection();
		
		/**
		 * get extras from table studente
		 */
		$selQry = "SELECT ". implode(", ", $userObj->getExtraFields()) . " FROM studente WHERE id_utente_studente=?";
		$extraAr = $this->getRowPrepared($selQry,array($userObj->getId()),AMA_FETCH_ASSOC);	
		/**
		 * TODO: load other tables here and merge values into $extraAr
		 */
		return $extraAr;

	}

}
?>