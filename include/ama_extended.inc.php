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
	 * loads and prepares all extra fields to be put in the
	 * object via the setExtra method called in the multiport
	 * NOTE: this MUST be implemented if user class hasExtra is true.
	 * can be empty or removed (no, it won't be called) if hasExtra is false.
	 * 
	 * @param int $userId
	 * @return array extra user data stored in the object
	 */
	public function getExtraData ($userId)
	{

	}
	
	public function disconnect() {
		parent::disconnect();
		self::$instance = NULL;
	}
	

}
?>