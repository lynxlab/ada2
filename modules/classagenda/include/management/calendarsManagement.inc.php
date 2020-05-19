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
	 * returns the placeholders for the reminder html
	 *
	 * @return array
	 */
	public static function reminderPlaceholders() {

		$placeHolders = array(
				'coursename' => translateFN('Nome del corso'),
				'instancename' => translateFN('Nome della classe'),
				'name' => translateFN('Nome dello studente'),
				'lastname'=> translateFN('Cognome dello studente'),
				'e-mail' => translateFN('E-Mail dello studente'),
				'tutorname' => translateFN('Nome del tutor').' ('.translateFN('se disponibile').')',
				'tutorlastname' => translateFN('Cognome del tutor').' ('.translateFN('se disponibile').')',
				'eventdate' => translateFN('Data dell\'evento'),
				'eventstart' => translateFN('Ora di inizio dell\'evento'),
				'eventend' => translateFN('Ora di fine dell\'evento')
		);

		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM) {
			$placeHolders = array_merge($placeHolders, array(
					'classroomname' => translateFN('Nome dell\'aula').' ('.translateFN('se disponibile').')',
					'venuename' => translateFN('Nome del luogo').' ('.translateFN('se disponibile').')',
					'venueaddress' => translateFN('Indirizzo del luogo').' ('.translateFN('se disponibile').')',
					'venuemaplink' => translateFN('Link alla mappa dell\'indirizzo del luogo').' ('.translateFN('se disponibile').')'
				)
			);
		}

		return $placeHolders;
	}

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
		$help = translateFN('Da qui puoi inserire o modificare il calendario degli eventi di una classe');
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
				 * stuff needed only if user's a switcher
				 */
				if ($_SESSION['sess_userObj']->getType()==AMA_TYPE_SWITCHER) {
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
					 * repeat classroom event button
					 */
					$repeatButtonDIV = CDOMElement::create('div','id:repeatButtonContainer');
					$repeatButton = CDOMElement::create('input_button','id:repeatButton');
					$repeatButton->setAttribute('onclick', 'javascript:repeatSelectedEvent();');
					$repeatButton->setAttribute('value', translateFN('Ripeti fino a fine corso'));
					$repeatButtonDIV->addChild($repeatButton);

					/**
					 * delete all classroom events button
					 */
					$deleteAllButtonDIV = CDOMElement::create('div','id:deleteAllButtonContainer');
					$deleteAllButton = CDOMElement::create('input_button','id:deleteAllButton');
					$deleteAllButton->setAttribute('onclick', 'javascript:deleteAllEvents();');
					$deleteAllButton->setAttribute('value', translateFN('Cancella Tutti gli eventi'));
					$deleteAllButtonDIV->addChild($deleteAllButton);

					/**
					 * delete classroom event button
					 */
					$deleteButtonDIV = CDOMElement::create('div','id:deleteButtonContainer');
					$deleteButton = CDOMElement::create('input_button','id:deleteButton');
					$deleteButton->setAttribute('onclick', 'javascript:deleteSelectedEvent();');
					$deleteButton->setAttribute('value', translateFN('Cancella Elemento Selezionato'));
					$deleteButtonDIV->addChild($deleteButton);
				}

				/**
				 * informational header div
				 */
				$infoHeaderDIV = CDOMElement::create('h2','id:infoHeader');
				$infoHeaderSPAN = CDOMElement::create('span','class:infoHeaderContent');
				$infoHeaderSPAN->addChild(new CText(translateFN('Calendario degli eventi della classe').': '));
				$infoHeaderSPAN->addChild(CDOMElement::create('span','id:headerInstanceTitle'));
				$infoHeaderDIV->addChild($infoHeaderSPAN);

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
				$filterInstanceState = array(
						MODULES_CLASSAGENDA_ALL_INSTANCES => translateFN('Tutte'),
						MODULES_CLASSAGENDA_NONSTARTED_INSTANCES => translateFN('Non iniziate'),
						MODULES_CLASSAGENDA_STARTED_INSTANCES => translateFN('In corso'),
						MODULES_CLASSAGENDA_CLOSED_INSTANCES => translateFN('Chiuse')
				);
				$filterInstanceSELECT = BaseHtmlLib::selectElement2('id:filterInstanceState,name:filterInstanceState',
						$filterInstanceState,MODULES_CLASSAGENDA_ALL_INSTANCES);
				$filterInstanceLABEL = CDOMElement::create('label','for:filterInstanceState');
				$filterInstanceLABEL->addChild(new CText(translateFN('Filtra le classi').': '));

				/**
				 * checkbox to filter selected instance only
				 */
				$onlySelectedCHECK = CDOMElement::create('checkbox','id:onlySelectedInstance');
				$onlySelectedCHECK->setAttribute('value', 1);
				$onlySelectedCHECK->setAttribute('name', 'onlySelectedInstance');
				$onlySelectedLABEL = CDOMElement::create('label','for:onlySelectedInstance');
				$onlySelectedLABEL->addChild(new CText(translateFN('Mostra solo gli eventi della classe selezionata')));
				if ($_SESSION['sess_userObj']->getType() != AMA_TYPE_SWITCHER) {
					$onlySelectedCHECK->setAttribute('checked', 'checked');
					$onlySelectedCHECK->setAttribute('style', 'display:none;');
					$onlySelectedLABEL->setAttribute('style', 'display:none;');
				}

				$selectClassDIV = CDOMElement::create('div','id:selectClassContainer');
				$selectClassDIV->addChild($instancesLABEL);
				$selectClassDIV->addChild($instancesSELECT);
				$selectClassDIV->addChild($filterInstanceLABEL);
				$selectClassDIV->addChild($filterInstanceSELECT);
				$selectClassDIV->addChild($onlySelectedCHECK);
				$selectClassDIV->addChild($onlySelectedLABEL);

				/**
				 * service (aka course) type box with
				 * an empty span to be filled in by javascript
				 */
				$serviceTypeDIV = CDOMElement::create('div','id:servicetypeContainer');
				$courseIDSPAN = CDOMElement::create('span','id:courseID');
				$courseIDSPAN->setAttribute('style', 'display:none');
				$serviceTypeDIV->addChild($courseIDSPAN);
				$serviceSPANText = CDOMElement::create('span');
				$serviceSPANText->addChild(new CText(translateFN('Corso di tipo').': '));
				$serviceTypeDIV->addChild($serviceSPANText);
				$serviceTypeDIV->addChild(CDOMElement::create('span','id:servicetype'));

				/**
				 * span to hold number of subscribed students
				 */
				$studentCountSPAN = CDOMElement::create('span','class:studentcount');
				$studentCountSPAN->addChild (new CText(translateFN('Numero di studenti iscritti: ')));
				$studentCountSPAN->addChild (CDOMElement::create('span','id:studentcount'));
				$serviceTypeDIV->addChild($studentCountSPAN);

				/**
				 * span to hold instance end date
				 */
				$endDate = CDOMElement::create('span','class:enddatecont');
				$endDate->addChild(new CText(translateFN('Data fine').': '));
				$endDate->addChild(CDOMElement::create('span','id:enddate'));
				$serviceTypeDIV->addChild($endDate);

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
				 * must be there for js reason, but hide it if user's not a switcher
				 */
				$venues = $this->_getVenues();
				if (!is_null($venues)) {
					$classroomsDIV = CDOMElement::create('div','id:classrooms');
					// prepare an empty select to be filled by calendars.js
					$venuesSELECT = CDOMElement::create('select','id:venuesList,name:venuesList');

					/**
					 * checkbox to filter selected venues only
					 */
					$onlySelectedVenueCHECK = CDOMElement::create('checkbox','id:onlySelectedVenue');
					$onlySelectedVenueCHECK->setAttribute('value', 1);
					$onlySelectedVenueCHECK->setAttribute('name', 'onlySelectedVenue');
					$onlySelectedVenueLABEL = CDOMElement::create('label','for:onlySelectedVenue');
					$onlySelectedVenueLABEL->addChild(new CText(translateFN('Mostra solo il luogo selezionato')));

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
					$classroomsDIV->addChild ($onlySelectedVenueCHECK);
					$classroomsDIV->addChild ($onlySelectedVenueLABEL);
					$classroomsDIV->addChild($classroomSPAN);
					$classroomsDIV->addChild($classroomlistDIV);
					if ($_SESSION['sess_userObj']->getType()!=AMA_TYPE_SWITCHER) {
						$classroomsDIV->setAttribute('style', 'display:none;');
					}
				}

				/**
				 * build a DIV to hold the tutor list of the selected instance
				 * must be there for js reason, but hide it if user's not a switcher
				 */
				$tutorsDIV = CDOMElement::create('div', 'id:tutorsListContainer');
				$tutorsSPAN = CDOMElement::create('span','class:selecttutorspan');
				$tutorsSPAN->addChild(new CText(translateFN('Seleziona un tutor').': '));
				$tutorsDIV->addChild($tutorsSPAN);
				$tutorsDIV->addChild(CDOMElement::create('div','id:tutorslist'));
				if ($_SESSION['sess_userObj']->getType()!=AMA_TYPE_SWITCHER) $tutorsDIV->setAttribute('style','display:none;');

				/**
				 * send event reminder button and div to hold the dialog
				 */
				if (in_array($_SESSION['sess_userObj']->getType(), array(AMA_TYPE_SWITCHER, AMA_TYPE_TUTOR))) {
					$reminderButtonDIV = CDOMElement::create('div','id:reminderButtonContainer');
					$reminderButton = CDOMElement::create('input_button','id:reminderButton');
					$reminderButton->setAttribute('onclick', 'javascript:reminderSelectedEvent();');
					$reminderButton->setAttribute('value', translateFN('Invia Promemoria agli iscritti'));
					$reminderButtonDIV->addChild($reminderButton);
				}

				$reminderDialog = CDOMElement::create('div','id:reminderDialog');
				$reminderDialog->setAttribute('title', translateFN('Promemoria Evento'));
				$reminderDialog->addChild (CDOMElement::create('div','id:reminderDialogContent'));

				$reminderLegend = CDOMElement::create('div','id:reminderLegend');
				$reminderLegendTitle = CDOMElement::create('span','class:legendTitle');
				$reminderLegendTitle->addChild (new CText(translateFN('Campi valorizzati')));
				$reminderLegend->addChild($reminderLegendTitle);

				$reminderLegendOL = CDOMElement::create('ol');
				foreach (self::reminderPlaceholders() as $legendItem=>$legendDescription) {
					$legendLI = CDOMElement::create('li','class:legendItem tooltip');
					$legendLI->setAttribute('title',$legendDescription);
					$legendLI->addChild (new CText('{'.$legendItem.'}'));
					$reminderLegendOL->addChild($legendLI);
				}

				$reminderLegend->addChild($reminderLegendOL);
				$reminderDialog->addChild($reminderLegend);

				// this shall become the ok button label inside the dialog
				$reminderOK = CDOMElement::create('span','class:confirmOKLbl');
				$reminderOK->setAttribute('style','display:none;');
				$reminderOK->addChild (new CText(translateFN('Invia')));
				// this shall become the cancel button label inside the dialog
				$reminderCancel = CDOMElement::create('span','class:confirmCancelLbl');
				$reminderCancel->setAttribute('style', 'display:none;');
				$reminderCancel->addChild (new CText(translateFN('Chiudi')));
				// add the elements to the div
				$reminderDialog->addChild($reminderOK);
				$reminderDialog->addChild($reminderCancel);

				/**
				 * confirm dialog box
				 */
				$confirmDIV = CDOMElement::create('div','id:confirmDialog');
				$confirmDIV->setAttribute('title', translateFN('Conferma Azione'));
				// question for not saved events (case instances list is clicked)
				$confirmDelSPAN = CDOMElement::create('span','id:instancesListquestion,class:dialogQuestion');
				$confirmDelSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, li salvo prima di cambiare istanza?')));
				// question for not saved events (case venues list is clicked)
				$confirmVenueDelSPAN = CDOMElement::create('span','id:venuesListquestion,class:dialogQuestion');
				$confirmVenueDelSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, li salvo prima di cambiare luogo?')));
				// question for not saved events (case show active instances is clicked)
				$confirmOnlyActiveSPAN = CDOMElement::create('span','id:filterInstanceStatequestion,class:dialogQuestion');
				$confirmOnlyActiveSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, li salvo prima di filtrare le istanze?')));
				// question asked for tutor overlapping
				$confirmTutorOverlap = CDOMElement::create('span','id:tutorOverlapquestion,class:dialogQuestion');
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
				// question asked when sending a reminder on a non saved event
				$confirmReminderNonSavedEvent = CDOMElement::create('span','id:reminderNonSavedEventquestion,class:dialogQuestion');
				$confirmReminderNonSavedEvent->addChild(new CText(translateFN('È necessario salvare il calendario prima di inviare un promemoria. Lo salvo?')));
				// question for not saved events (case #cancelCalendar button is clicked)
				$confirmCancelCalendarSPAN = CDOMElement::create('span','id:cancelCalendarquestion,class:dialogQuestion');
				$confirmCancelCalendarSPAN->addChild(new CText(translateFN('Ci sono dei dati non salvati, ricaricare il calendario?')));
				// question asked when deleting all instance events
				$confirmDeleteAllSPAN = CDOMElement::create('span','id:deleteAllButtonquestion,class:dialogQuestion');
				$confirmDeleteAllSPAN->addChild(new CText(translateFN('Confermi la cancellazione di tutti gli eventi della classe').' '));
				$confirmDeleteAllSPAN->addChild(CDOMElement::create('span','id:deleteAllButtonInstanceName'));
				$confirmDeleteAllSPAN->addChild(new CText('?'));
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
				$confirmDIV->addChild($confirmReminderNonSavedEvent);
				$confirmDIV->addChild($confirmCancelCalendarSPAN);
				$confirmDIV->addChild($confirmDeleteAllSPAN);
				$confirmDIV->setAttribute('style','display:none;');

				/**
				 * add all generated elements to the container
				 */
				if (isset($selectClassDIV)) {
					$htmlObj->addChild($selectClassDIV);
				}
				$htmlObj->addChild($infoHeaderDIV);
				$htmlObj->addChild($calendarDIV);
				/**
				 * build a wrapper holding the info boxes
				 */
				$calendarBoxes = CDOMElement::create('div','id:calendar-boxes-container');
				$htmlObj->addChild($calendarBoxes);
				$calendarBoxes->addChild($serviceTypeDIV);
				if (isset($classroomsDIV)) $calendarBoxes->addChild($classroomsDIV);
				if (isset($tutorsDIV)) $calendarBoxes->addChild($tutorsDIV);
				if (isset($reminderButtonDIV)) $calendarBoxes->addChild($reminderButtonDIV);
				if (isset($repeatButtonDIV)) $calendarBoxes->addChild($repeatButtonDIV);
				if (isset($deleteButtonDIV)) $calendarBoxes->addChild($deleteButtonDIV);
				if (isset($deleteAllButtonDIV)) $calendarBoxes->addChild($deleteAllButtonDIV);
				if (isset($buttonsDIV)) $calendarBoxes->addChild($buttonsDIV);
				$htmlObj->addChild(CDOMElement::create('div','class:clearfix'));

				$htmlObj->addChild($reminderDialog);
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

	/**
	 * builds export calendar HTML or array data
	 * if type is 'pdf', the HTML is built and converted to a pdf by the rendering engine
	 * if type is 'csv', only the array of data is returned and the caller shall build the csv
	 *
	 * @param Course $courseObj
	 * @param Course_instance $courseInstanceObj
	 * @param string $type 'pdf' or 'csv', defaults to pdf
	 *
	 * @return multitype:Ambigous <NULL, CBaseElement, unknown>
	 */
	public function exportCalendar(Course $courseObj, Course_instance $courseInstanceObj, $type='pdf') {

		$dh = $GLOBALS['dh'];

		if (!in_array($type, self::$exportFormats)) $type = 'pdf';

		$result = $dh->getInstanceFullCalendar($courseInstanceObj->getId());
		if (!AMA_DB::isError($result)) {
			$head = array (
					translateFN('Data'),
					translateFN('Ora Inizio'),
					translateFN('Ora Fine'),
					translateFN('Tutor')
			);
			if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM) {
				$head = array_merge ($head,array(
						translateFN('Aula'),
						translateFN('Luogo'),
						translateFN('Indirizzo')
				));
			}

			if ($type=='pdf') {
				// if type is pdf
				$htmlObj = CDOMElement::create('div','id:pdfCalendar');
				if (count($result)>0) {
					$htmlObj->addChild(BaseHtmlLib::tableElement('class:pdfcalendar',$head,$result));
				}
				return array('htmlObj'   => $htmlObj);
			} else if ($type=='csv') {
				if (count($result)>0) {
					return (array_merge(array($head),$result));
				}
			}
		}
	}

	/**
	 * gets all venues with at least a classroom from the classroom API
	 *
	 * @return array|NULL
	 *
	 * @access private
	 */
	private function _getVenues() {
		if (defined('MODULES_CLASSROOM') && MODULES_CLASSROOM) {
			require_once MODULES_CLASSROOM_PATH . '/include/classroomAPI.inc.php';
			$classroomAPI = new classroomAPI();
			return $classroomAPI->getAllVenuesWithClassrooms();
		} else return null;
	}
} // class ends here