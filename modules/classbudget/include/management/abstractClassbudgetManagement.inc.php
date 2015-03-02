<?php
/**
 * Base Management Class
 *
 * @package			classbudget module
 * @author			Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright		Copyright (c) 2015, Lynx s.r.l.
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link			classbudget
 * @version			0.1
 */

/**
 * base class for module
 *
 * @author giorgio
 */

abstract class abstractClassbudgetManagement
{
	/**
	 * array of available export formats
	 *
	 * @var array
	 */
	public static $exportFormats = array ('pdf','csv');
	
	protected $_id_course_instance;
	protected $_objType;
	protected $_tableCaption;

	public $dataCostsArr;
	public $headerRowLabels;
	
	/**
	 * name constructor
	 */
	public function __construct($data=array()) {
		if (is_array($data) && count($data)>0) {
			$this->_fillFromArray($data);
		}
	}
	
	/**
	 * build, manage and display the module's pages
	 *
	 * @return array
	 *
	 * @access public
	 */
	public function run($action=null) {
		
		if (is_null($this->_id_course_instance)) {
			$htmlObj = CDOMElement::create('div','class:'.$this->_objType.'budgeterror');
			$htmlObj->addChild(new CText(translateFN('Istanza corso non valida')));
		} else {
			$htmlObj = CDOMElement::create('div','id:'.$this->_objType.'BudgetContainer');
			/**
			 * Build our own table here, there's too much that BaseHtmlLib cannot handle
			*/
			$table = CDOMElement::create('table', 'class:classbudgettable,id:'.$this->_objType.'BudgetTable');
			$table->setAttribute('data-instance-id', $this->_id_course_instance);
	
			$c = CDOMElement::create('caption');
			$ch3 = CDOMElement::create('h3');
			$ch3->addChild(new CText($this->_tableCaption));
			$c->addChild($ch3);
			$table->addChild($c);
	
			$thead = CDOMElement::create('thead');
			$tr = CDOMElement::create('tr');
			foreach ($this->headerRowLabels as $el) {
				$th = CDOMElement::create('th');
				$th->addChild(new CText($el));
				$tr->addChild($th);
			}
			$thead->addChild($tr);
			$table->addChild($thead);
	
			$tbody = CDOMElement::create('tbody');
			$grandTotal = 0;
			$countRow = 0;
			foreach ($this->dataCostsArr as $id_classroom=>$data) {
				$tr = CDOMElement::create('tr','id:'.$this->_objType.$id_classroom);
				if (!is_null($data['cost_'.$this->_objType.'_id'])) {
					$tr->setAttribute('data-cost-'.$this->_objType.'-id', $data['cost_'.$this->_objType.'_id']);
				}
				$tr->setAttribute('class', ($countRow++%2) ? 'evenrow' : 'oddrow');
	
				// name
				$td = CDOMElement::create('td','class:displayname');
				$td->addChild(new CText($data['displayname']));
				$tr->addChild($td);
				// time
				$td = CDOMElement::create('td','class:totaltime');
				$td->setAttribute('data-totaltime-millis', $data['totaltime']);
				$td->addChild(new CText($data['formattime']));
				$tr->addChild($td);
				// hourly rate
				$td = CDOMElement::create('td','class:price');
				if ($action==MODULES_CLASSBUDGET_EDIT) {
					$inputField = CDOMElement::create('text','id:'.$this->_objType.'_hourly_rate['.$id_classroom.']');
					$inputField->setAttribute('class', 'price');
					$inputField->setAttribute('name', $this->_objType.'_hourly_rate['.$id_classroom.']');
					$inputField->setAttribute('value', sprintf('%.02f',$data['hourly_rate']));
					$inputField->setAttribute('onchange', 'javascript:updateTotal(this);');
					$td->addChild($inputField);
				} else {
					$td->addChild(new CText(number_format($data['hourly_rate'], ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP)));
				}
				$tr->addChild($td);
				// totale
				$td = CDOMElement::create('td','class:price total');
				$total = ($data['totaltime'] / 3600) * $data['hourly_rate'];
				$td->setAttribute('data-total', $total);
				$grandTotal += $total;
				$td->addChild(new CText(number_format($total, ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP)));
				$tr->addChild($td);
					
				$tbody->addChild($tr);
			}
			$table->addChild($tbody);
			$tfoot = CDOMElement::create('tfoot');
			$tr = CDOMElement::create('tr');
			$foot = array (
					translateFN('Totale'),
					null,
					null,
					$grandTotal
			);
			foreach ($foot as $index=>$el) {
				$th = CDOMElement::create('th');
				if ($index==0) {
					$th->setAttribute('class', 'caption grandtotal');
				} else if (is_numeric($el)) {
					$th->setAttribute('class', 'price grandtotal');
					$th->setAttribute('data-grandtotal', $grandTotal);
					$el = number_format($grandTotal,ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP);
				}
				$th->addChild(new CText($el));
				$tr->addChild($th);
			}
			$tfoot->addChild($tr);
			$table->addChild($tfoot);
			$htmlObj->addChild($table);
		}
		return $htmlObj;
	}
	
	/**
	 * Builds the cost array from the data returned by the db
	 * each element of the returned array will have its key
	 * set to the $this->_objType id for that row and the following fields:
	 *
	 * 'totaltime' => total time in milliseconds (int type)
	 * 'formattime' => total time formatted as a string in hour:minutes format (string type)
	 * 'displayname' => string to be displayed (string type)
	 * 'hourly_rate' => hourly rate for the classroom (float type)
	 * 'cost_'.$this->_objType.'_id' => unique row id (if present: int type or null)
	 *
	 * @param array $res
	 *
	 * @return array
	 *
	 * @access private
	 */
	protected function _buildCostArrayFromRes ($res) {
		$retval = array();
		foreach ($res as $row) {
			$id = $row['id_'.$this->_objType];
			$retval[$id] = array();
			unset($row['id_'.$this->_objType]);
			foreach ($row as $field=>$value) {
				switch ($field) {
					case 'cost_'.$this->_objType.'_id':
						$retval[$id][$field]= (isset($value) && is_numeric($value)) ? (int) $value : null;
						break;
					case 'totaltime':
						$hours = floor($value / 3600);
						$minutes = floor(($value / 60) % 60);
						$retval[$id][$field] = (int) $value;
						$retval[$id]['formattime'] = sprintf("%02d:%02d",$hours,$minutes);
						break;
					case 'venuename':
						$retval[$id]['displayname'] = $value . ' - '.$row['roomname'];
						break;
					case 'name':
						$retval[$id]['displayname'] = $value . ' '.$row['lastname'];
						break;
					case 'default_rate':
						/**
						 * if a rate relative to the passed istance has been returned
						 * use it, else use the default coming from the classroom table
						 */
						if (!is_null($row['cost_rate']) && floatval($row['cost_rate'])>0) {
							$retval[$id]['hourly_rate'] = floatval($row['cost_rate']);
						} else if (!is_null($value) && floatval($value)>0) {
							$retval[$id]['hourly_rate'] = floatval($value);
						} else $retval[$id]['hourly_rate'] = floatval(0);
						break;
					default:
						break;
				} // switch
			} // foreach $row
		} // foreach $res
		return $retval;
	}
	
	/**
	 * builds proper array to be exported in CSV format
	 * 
	 * @return array
	 * 
	 * @access public
	 */
	public function buildCostArrayForCSV() {
		
		// add a row with tile and all empty cells
		// and the header row
		foreach ($this->headerRowLabels as $index=>$headerVal) {
			if ($index==0)
				$retArray[0][$index] = $this->_tableCaption;
			else
				$retArray[0][$index] = '';
			
			$retArray[1][$index] = str_replace(ADA_CURRENCY_SYMBOL, 
					html_entity_decode(ADA_CURRENCY_SYMBOL,ENT_COMPAT | ENT_HTML401, ADA_CHARSET ), 
					$headerVal);
		}
		
		// table body
		$grandTotal = 0;
		foreach ($this->dataCostsArr as $key=>$value) {
			$total = ($value['totaltime'] / 3600) * $value['hourly_rate'];
			$grandTotal += $total; 
			$retArray[] = array ($value['displayname'],
					$value['formattime'],
					number_format($value['hourly_rate'], ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP),
					number_format($total, ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP)
			);
		}
		
		$numRecord = count ($retArray);
		$lastIndex = count ($retArray[0])-1;
		
		// add grand total and an empty row
		foreach ($this->headerRowLabels as $index=>$notused) {
			if ($index==0) $retArray[$numRecord][$index] = translateFN('Totale');
			else if ($index==$lastIndex) $retArray[$numRecord][$index] = number_format($grandTotal, ADA_CURRENCY_DECIMALS, ADA_CURRENCY_DECIMAL_POINT, ADA_CURRENCY_THOUSANDS_SEP);
			else $retArray[$numRecord][$index] = '';
						
			$retArray[$numRecord+1][$index] = '';
		}

		return $retArray;
	}
			
	/**
	 * fills object properties from an array
	 * 
	 * @param array $data assoc array to get values from
	 * 
	 * @access private
	 */
	protected function _fillFromArray($data) {
		foreach ($data as $key=>$val) {
			if (property_exists($this, $key)) $this->{$key} = trim($val);
		}
	}
	
	/**
	 * returns object properties as an array
	 * 
	 * @return array
	 * 
	 * @access public
	 */
	public function toArray() {
		return (array) $this;
	}
	
} // class ends here