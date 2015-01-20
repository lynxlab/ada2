<?php
/**
 * Calendars Management Class
 *
 * @package			classagenda module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2014, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classagenda
 * @version			0.1
 */

/**
 * class for managing Calendars
 *
 * @author giorgio
 */
require_once MODULES_CLASSAGENDA_PATH . '/include/management/abstractClassagendaManagement.inc.php';

class calendarsManagement extends abstractClassAgendaManagement
{
	
	/**
	 * build, manage and display the module's pages
	 *
	 * @return array
	 * 
	 * @access public
	 */
	public function run($action=null) {
		
		require_once ROOT_DIR . '/include/HtmlLibrary/BaseHtmlLib.inc.php';
		
		/* @var $html	string holds html code to be retuned */
		$htmlObj = null;
		/* @var $path	string  path var to render in the help message */
		$help = translateFN('Da qui puoi inserire o modifcare il calendario delle lezioni di una classe');
		/* @var $status	string status var to render in the breadcrumbs */
		$title= translateFN('Calendario');
		
		switch ($action) {
			case MODULES_CLASSAGENDA_EDIT_CAL:
				/**
				 * edit action, build needed HTML objects
				 */
				$htmlObj = CDOMElement::create('div','id:calendarContainer');
				$calendarDIV = CDOMElement::create('div','id:classcalendar');
				
				/**
				 * bottom buttons div
				 */
				$buttonsDIV = CDOMElement::create('div','id:buttonsContainer');			
				$saveButton = CDOMElement::create('input_button','id:saveCalendar');
				$saveButton->setAttribute('value', translateFN('Salva'));
				$cancelButton = CDOMElement::create('input_button','id:cancelCalendar');
				$cancelButton->setAttribute('value', translateFN('Annulla'));
				$buttonsDIV->addChild($saveButton);
				$buttonsDIV->addChild($cancelButton);
				
				/**
				 * courses instances list shall be obtained by the javascript,
				 * build empty select item and a span to hold number of subscribed students
				 * 
				 */
				$instancesSELECT = BaseHtmlLib::selectElement2('id:instancesList,name:instancesList',array());
				$instancesLABEL = CDOMElement::create('label','for:instancesList');
				$instancesLABEL->addChild(new CText(translateFN('Seleziona una classe').': '));
				
				/**
				 * checkbox to filter active instances only
				 */
				$onlyActiveCHECK = CDOMElement::create('checkbox','id:onlyActiveInstances');
				$onlyActiveCHECK->setAttribute('value', 1);
				$onlyActiveCHECK->setAttribute('name', 'onlyActiveInstances');
				$onlyActiveLABEL = CDOMElement::create('label','for:onlyActiveInstances');
				$onlyActiveLABEL->addChild(new CText(translateFN('Mostra solo istanze attive')));
				
				/**
				 * checkbox to filter selected instance only
				 */
				$onlySelectedCHECK = CDOMElement::create('checkbox','id:onlySelectedInstance');
				$onlySelectedCHECK->setAttribute('value', 1);
				$onlySelectedCHECK->setAttribute('name', 'onlySelectedInstance');
				$onlySelectedLABEL = CDOMElement::create('label','for:onlySelectedInstance');
				$onlySelectedLABEL->addChild(new CText(translateFN('Mostra solo istanza selezionata')));
				
				/**
				 * span to hold number of subscribed students
				 */
				$studentCountSPAN = CDOMElement::create('span','class:studentcount');
				$studentCountSPAN->addChild (new CText(translateFN('Numero di studenti iscritti: ')));
				$studentCountSPAN->addChild (CDOMElement::create('span','id:studentcount'));
				
				$selectClassDIV = CDOMElement::create('div','id:selectClassContainer');
				$selectClassDIV->addChild($instancesLABEL);
				$selectClassDIV->addChild($instancesSELECT);
				$selectClassDIV->addChild($onlyActiveCHECK);
				$selectClassDIV->addChild($onlyActiveLABEL);
				$selectClassDIV->addChild($onlySelectedCHECK);
				$selectClassDIV->addChild($onlySelectedLABEL);
				$selectClassDIV->addChild($studentCountSPAN);
				
				/**
				 * service (aka course) type box with
				 * an empty span to be filled in by javascript
				 */
				$serviceTypeDIV = CDOMElement::create('div','id:servicetypeContainer');
				$serviceSPANText = CDOMElement::create('span');
				$serviceSPANText->addChild(new CText(translateFN('Corso di tipo').': '));
				$serviceTypeDIV->addChild($serviceSPANText);
				$serviceTypeDIV->addChild(CDOMElement::create('span','id:servicetype'));
				/**
				 * total course instance duration and hours allocated by calendar
				 */
				$serviceTypeDurationUL = CDOMElement::create('ul','id:serviceduration');
				$serviceTypeDurationUL->setAttribute('style', 'display:none');

				$durationHours = CDOMElement::create('li','class:durationLI');
				$durationHours->addChild(new CText(translateFN('Durata prevista in ore').': '));
				$durationHours->addChild(CDOMElement::create('span','id:duration_hours'));
				$serviceTypeDurationUL->addChild($durationHours);
				
				$allocatedHours = CDOMElement::create('li','class:allocatedLI');
				$allocatedHours->addChild(new CText(translateFN('Tempo allocato (ore:minuti)').': '));
				$allocatedHours->addChild(CDOMElement::create('span','id:allocated_hours'));
				$serviceTypeDurationUL->addChild($allocatedHours);
				
				$lessonsNumber = CDOMElement::create('li','class:lessonsLI');
				$lessonsNumber->addChild(new CText(translateFN('Numero di incontri').': '));
				$lessonsNumber->addChild(CDOMElement::create('span','id:lessons_count'));
				$serviceTypeDurationUL->addChild($lessonsNumber);
				
				$serviceTypeDIV->addChild($serviceTypeDurationUL);
				
				/**
				 * get Venues, build select item
				 * and needed empty div to hold classroom list for selected venue
				 */
				$venues = $this->_getVenues();
				if (!is_null($venues)) {
					$classroomsDIV = CDOMElement::create('div','id:classrooms');
					
					foreach ($venues as $venue) {
						$dataAr[$venue['id_venue']] = $venue['name'];
					}
					reset($dataAr);
					
					/**
					 * venues html select element
					 */
					$venuesSELECT = BaseHtmlLib::selectElement2('id:venuesList,name:venuesList',$dataAr,key($dataAr));
					unset($dataAr);					
					$venuesLABEL = CDOMElement::create('label','for:venuesList,class:venuesListLabel');
					$venuesLABEL->addChild(new CText(translateFN('Seleziona un luogo').': '));
					
					/**
					 * container for classroom radio buttons
					 */
					$classroomSPAN = CDOMElement::create('span','class:selectclassroomspan');
					$classroomSPAN->addChild(new CText(translateFN('Seleziona un\'aula').': '));
					$classroomlistDIV = CDOMElement::create('div','id:classroomlist');
					
					$classroomsDIV->addChild ($venuesLABEL);
					$classroomsDIV->addChild ($venuesSELECT);
					$classroomsDIV->addChild($classroomSPAN);
					$classroomsDIV->addChild($classroomlistDIV);
				}
				
				/**
				 * build a DIV to hold the tutor list of the selected instance
				 */
				$tutorsDIV = CDOMElement::create('div', 'id:tutorsListContainer');
				$tutorsSPAN = CDOMElement::create('span','class:selecttutorspan');
				$tutorsSPAN->addChild(new CText(translateFN('Seleziona un tutor').': '));
				$tutorsDIV->addChild($tutorsSPAN);
				$tutorsDIV->addChild(CDOMElement::create('div','id:tutorslist'));
				
				/**
				 * delete classroom event button
				 */
				$deleteButtonDIV = CDOMElement::create('div','id:deleteButtonContainer');
				$deleteButton = CDOMElement::create('input_button','id:deleteButton');
				$deleteButton->setAttribute('onclick', 'javascript:deleteSelectedEvent();');
				$deleteButton->setAttribute('value', translateFN('Cancella Elemento Selezionato'));
				$deleteButtonDIV->addChild($deleteButton);
				
				/**
				 * confirm dialog box
				 */
				$confirmDIV = CDOMElement::create('div','id:confirmDialog');
				$confirmDIV->setAttribute('title', translateFN('Conferma Azione'));
				// question for not saved events (case instances list is clicked)
				$confirmDelSPAN = CDOMElement::create('span','id:instancesListquestion');
				$confirmDelSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, li salvo prima di cambiare istanza?')));
				// question for not saved events (case venues list is clicked)
				$confirmVenueDelSPAN = CDOMElement::create('span','id:venuesListquestion');
				$confirmVenueDelSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, li salvo prima di cambiare luogo?')));
				// question for not saved events (case show active instances is clicked)
				$confirmOnlyActiveSPAN = CDOMElement::create('span','id:onlyActiveInstancesquestion');
				$confirmOnlyActiveSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, li salvo prima di filtrare le istanze?')));
				// question asked for tutor overlapping
				$confirmTutorOverlap = CDOMElement::create('span','id:tutorOverlapquestion');
				$confirmTutorOverlap->addChild(new CText(translateFN('Il tutor').' '));
				$confirmTutorOverlap->addChild(CDOMElement::create('span','id:overlapTutorName'));
				$confirmTutorOverlap->addChild(new CText(' '.translateFN('ha già un evento per la classe').'<br/>'));
				$confirmTutorOverlap->addChild(CDOMElement::create('span','id:overlapInstanceName'));
				$confirmTutorOverlap->addChild(new CText('<br/>'.translateFN('in data').' '));
				$confirmTutorOverlap->addChild(CDOMElement::create('span','id:overlapDate'));
				$confirmTutorOverlap->addChild(new CText(' '.translateFN('dalle ore').' '));
				$confirmTutorOverlap->addChild(CDOMElement::create('span','id:overlapStartTime'));
				$confirmTutorOverlap->addChild(new CText(' '.translateFN('alle ore').' '));
				$confirmTutorOverlap->addChild(CDOMElement::create('span','id:overlapEndTime'));
				$confirmTutorOverlap->addChild(new CText('<br/>'.translateFN('Vuoi mantenere la modifica fatta?')));
				// this shall become the ok button label inside the dialog
				$confirmOK = CDOMElement::create('span','class:confirmOKLbl');
				$confirmOK->setAttribute('style','display:none;');
				$confirmOK->addChild (new CText(translateFN('Si')));
				// this shall become the cancel button label inside the dialog
				$confirmCancel = CDOMElement::create('span','class:confirmCancelLbl');
				$confirmCancel->setAttribute('style', 'display:none;');
				$confirmCancel->addChild (new CText(translateFN('No')));
				// add the elements to the div
				$confirmDIV->addChild($confirmOK);
				$confirmDIV->addChild($confirmCancel);
				$confirmDIV->addChild($confirmDelSPAN);
				$confirmDIV->addChild($confirmVenueDelSPAN);
				$confirmDIV->addChild($confirmOnlyActiveSPAN);
				$confirmDIV->addChild($confirmTutorOverlap);
				$confirmDIV->setAttribute('style','display:none;');
				
				/**
				 * add all generated elements to the container
				 */				
				if (isset($selectClassDIV)) {
					$htmlObj->addChild($selectClassDIV);
				}
				$htmlObj->addChild($calendarDIV);
				$htmlObj->addChild($serviceTypeDIV);
				if (isset($classroomsDIV)) {
					$htmlObj->addChild($classroomsDIV);					
				}
				$htmlObj->addChild($tutorsDIV);
				$htmlObj->addChild($deleteButtonDIV);
				$htmlObj->addChild($buttonsDIV);
				$htmlObj->addChild(CDOMElement::create('div','class:clearfix'));
				
				$htmlObj->addChild($confirmDIV);
				
				break;				
			default:
				/**
				 * return an empty page as default action
				 */
				break;
		}
		
		return array(
			'htmlObj'   => $htmlObj,
			'help'      => $help,
			'title'     => $title,
		);
	}
	
	private function _getVenues() {
		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM) {
			require_once MODULES_CLASSROOM_PATH . '/include/classroomAPI.inc.php';
			$classroomAPI = new classroomAPI();
			return $classroomAPI->getAllVenuesWithClassrooms();
		} else return null;
	}
} // class ends here