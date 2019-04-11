<?php
/**
 * SERVICE-COMPLETE MODULE.
 *
 * @package        service-complete module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>
 * @copyright      Copyright (c) 2013, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           service-complete
 * @version		   0.1
 */

/**
 * class for representing an operation between
 * to operands to be done when building, loading,
 * saving or evaluating a completeConditionSet.
 *
 * @author giorgio
 */
class Operation
{
	/**
	 * first operand.
	 * can be a pointer to another Operation object
	 *
	 * @var mixed scalar operand or Operation object pointer
	 */
	private $_firstOperand;

	/**
     * operator between the two operands
     * can be anything that eval can understand
	 *
	 * @var string
	 */
	private $_operator;

	/**
	 * second operand.
	 * can be a pointer to another Operation object
	 *
	 * @var mixed scalar operand or Operation object pointer
	 */
	private $_secondOperand;

	/**
	 * priority. This is actually the number of
	 * the column in which to show the operand in
	 * the ui. Therefore it is useful only when
	 * one of the two operands in the operation
	 * is a 'term' and tell us in which column does
	 * the 'term' belong.
	 *
	 * @var integer
	 */
	private $_priority;

	/**
	 * instance id used when evaluating the oepration
	 *
	 * @var integer
	 */
	private $_instanceIDForEval;

	/**
	 * user id used when evaluating the oepration
	 *
	 * @var integer
	 */
	private $_userIDForEval;

	/**
	 * true to write debug info in ADA log subdir
	 *
	 * @var boolean
	 */
	protected $logToFile = false;

    /**
     * Operation constructor.
     */
    public function __construct()
    {
		$this->_firstOperand = null;
		$this->_operator = null;
		$this->_secondOperand = null;
		$this->_priority = null;

		$this->_instanceIDForEval = null;
		$this->_userIDForEval = null;
    }

    /**
     * operation setter, set the properites to the passed values
     *
     * @param string $firstOperand
     * @param string $operator
     * @param string $secondOperand
     * @param int    $priority
     *
     * @throws Exception
     * @return Operation
     * @access private
     */
    private function setOperation ($firstOperand=null, $operator=null, $secondOperand=null, $priority=0)
    {
    	if (!is_null($firstOperand))
    	{
    		$this->_firstOperand  = $firstOperand;
    		$this->_operator      = $operator;
    		$this->_secondOperand = $secondOperand;
    		$this->_priority      = $priority;
    		return $this;
    	} else {
    		throw new Exception('Could not build operation '.print_r($firstOperand,true).print_r($operator,true).print_r($secondOperand,true), ADA_ERROR_ID_UNKNOWN_ERROR);
    		return null;
    	}
    }

    /**
     * Converts an operation tree back to an array
     * The parameter is passed by reference so the caller
     * must pass an empty array and she will find the
     * converted result there
     *
     * It cannot return the array directly because for
     * the recursion to work it must return the generated
     * array key each time a recursion ends
     *
     * @param array $data
     * @return number
     * @access public
     */
    public function toArray(&$data = array())
    {
    	static $key=0;

    	if ($this->_firstOperand  instanceof self) $operand1 = 'expr('.$this->_firstOperand->toArray($data).')';
    	else $operand1 = $this->_firstOperand;

    	if (!is_null($this->_operator) && !is_null($this->_secondOperand)) {
    		$operator = $this->_operator;

    		if ($this->_secondOperand  instanceof self) $operand2 = 'expr('.$this->_secondOperand->toArray($data).')';
    		else $operand2 = $this->_secondOperand;
    	} else {
    		$operator = null;
    	}

    	if (!isset($operand2)) $operand2 = null;

    	$data[++$key] = array ('id'=>$key, 'operator'=>$operator, 'operand1'=>$operand1, 'operand2'=>$operand2, 'priority'=>$this->_priority);

    	return $key;
    }

    /**
     * converts the operation tree into a string
     * that can be passed to eval
     *
     * @param int $id_course id of the course to evaluate condition, if any
     * @param int $id_user id of the user to evaluate condition, if any
     * @return string
     * @access public
     */
    public function toString()
    {
    	$converted = '';

    	if ($this->_firstOperand instanceof self) $converted .= '('.$this->_firstOperand->toString ().')';
    	else $converted .= $this->_firstOperand;

    	if (!is_null($this->_operator) && !is_null($this->_secondOperand)) {
    		$converted .= $this->_operator;

    		if ($this->_secondOperand instanceof self) $converted .= '('.$this->_secondOperand->toString ().')';
    		else $converted .= $this->_secondOperand;
    	}
    	return $converted;
    }

    /**
     * substitutes the parameter stored in the db with the same parameter
     * plus the passed parameters in the array
     *
     * e.g.
     * 1. if in the DB there is:
     *
     *   completeConditionTime::buildAndCheck(13)
     *
     * and the appended parameters are:
     *
     *   array (
     *       'id_course_instance' => 100,
     *       'id_user' => 25 )
     *
     * the result will be:
     * completeConditionTime::buildAndCheck(13,100,25)
     *
     * 2. if in the DB there is:
     *
     *   completeConditionFoo::buildAndCheck()
     *
     * and the appended parameters are:
     *
     *   array (
     *       'id_course_instance' => 100,
     *       'id_user' => 25 )
     *
     * the result will be:
     * completeConditionFoo::buildAndCheck(100,25)
     *
     * @param string $stringToEval the string to perform substituions
     * @param array $params parameters to be appended
     * @return string
     * @access private
     */
    private static function appendParamsToStr ($stringToEval, $params)
    {
    	// first substitute for method that have no default parameter
    	$stringToEval = preg_replace('/[(][)]/', "(null,".implode (',', $params).")", $stringToEval);
    	// then substitute the methods that have the default parameter
    	return preg_replace('/[(]([-]?[0-9]+)[)]/', "($1,".implode (',', $params).")", $stringToEval);
    }

    /**
     * converts the string and evaluates the
     * operation by a call to eval function
     *
     * @param int $id_instance id of the instance to evaluate condition, if any
     * @param int $id_user id of the user to evaluate condition, if any
     * @throws Exception
     * @return unknown|NULL
     * @access public
     */
    public function evaluate($params = array())
    {
    	$stringToEval = $this->toString();

    	if (!empty($params)) $stringToEval = self::appendParamsToStr($stringToEval,$params);

    	foreach ($GLOBALS['completeClasses'] as $className)
    	{
    		@include_once MODULES_SERVICECOMPLETE_PATH . '/include/' . $className . '.class.inc.php';
    	}
    	$reteval = eval ("\$value = $stringToEval;");
    	if ($reteval!==false) return $value;
    	else {
    		throw new Exception('Could not evaluate expression: '.$stringToEval, ADA_ERROR_ID_UNKNOWN_ERROR);
    		return null;
    	}
    }

    /**
     * static method to instantiate and build an operation
     *
     * @param string $firstOperand
     * @param string $operator
     * @param string $secondOperand
     * @param int    $priority
     *
     * @return Operation|NULL
     * @access public
     */
    public static function build ($firstOperand=null, $operator=null, $secondOperand=null, $priority=0)
    {
    	$operation = new self();
			$operation->setOperation($firstOperand,$operator,$secondOperand, $priority);
			$operation->setLogToFile(defined('MODULES_SERVICECOMPLETE_LOG') && MODULES_SERVICECOMPLETE_LOG === true);
    	return $operation;
    }

    /**
     * static method to build the operation tree from an
     * array coming from the DB or returned from a toArray()
     * method call, that is in the form of:
     *
	 *	Array
	 *	(
	 *	    [0] => Array
	 *	        (
	 *	            [id] => 1
	 *	            [operator] => +
	 *	            [operand1] => expr(5)
	 *	            [operand2] => expr(3)
	 *	        )
	 *	    [1] => Array
	 *	        (
	 *	            [id] => 2
	 *	            [operator] => *
	 *	            [operand1] => 4
	 *	            [operand2] => 5
	 *	        )
	 *	    [2] => Array
	 *	        (
	 *	            [id] => 3
	 *	            [operator] => /
	 *	            [operand1] => expr(2)
	 *	            [operand2] => 12
	 *	        )
	 *	    [3] => Array
	 *	        (
	 *	            [id] => 4
	 *	            [operator] => -
	 *	            [operand1] => expr(1)
	 *	            [operand2] => 6
	 *	        )
	 *	    [4] => Array
	 *	        (
	 *	            [id] => 5
	 *	            [operator] => *
	 * 	            [operand1] => 2
	 *	            [operand2] => 3
	 *	        )
	 *	)
     *
     * @param unknown $inputOperations
     * @throws Exception
     * @return mixed|NULL
     * @access public
     */
    public static function buildOperationTreeFromArray($inputOperations=array())
    {
    	if (!empty($inputOperations))
    	{
    		// loopCounter use is explained at the end of the below foreach
    		$loopCounter = count($inputOperations);
    		$operationsArray = array();

    		while (!empty($inputOperations) && ($loopCounter-- >= 0))
    		{
    			foreach ($inputOperations as $key=>$currentOperation)
    			{
    				$processed = false;
    				$matches = array();
    				// must check operand1 and operand2 are pointer to expressions
    				if (preg_match('/expr[(](\d+)[)]/', $currentOperation['operand1'],$matches)) {
    					$op1Pointer = $matches[1];
    				} else $op1Pointer = false;

    				if (preg_match('/expr[(](\d+)[)]/', $currentOperation['operand2'],$matches)) {
    					$op2Pointer = $matches[1];
    				} else $op2Pointer = false;

    				if (!$op1Pointer && !$op2Pointer)
    				{
    					// neither operand are pointers, create the operation
    					$operationsArray[$currentOperation['id']] = self::build(
    							$currentOperation['operand1'],$currentOperation['operator'],$currentOperation['operand2'],$currentOperation['priority']
    					);
    					$processed = true;
    				}
    				else if ($op1Pointer && !$op2Pointer && isset($operationsArray[$op1Pointer]))
    				{
    					// operand 1 is a pointer and its pointed operation has been set up, create the operation
    					$operationsArray[$currentOperation['id']] = self::build(
    							$operationsArray[$op1Pointer], $currentOperation['operator'],$currentOperation['operand2'],$currentOperation['priority']
    					);
    					$processed = true;
    				}
    				else if (!$op1Pointer && $op2Pointer && isset($operationsArray[$op2Pointer]))
    				{
    					// operand 2 is a pointer and its pointed operation has been set up, create the operation
    					$operationsArray[$currentOperation['id']] = self::build(
    							$currentOperation['operand1'], $currentOperation['operator'],$operationsArray[$op2Pointer],$currentOperation['priority']
    					);
    					$processed = true;
    				}
    				else if ($op1Pointer && $op2Pointer && isset($operationsArray[$op1Pointer]) && isset($operationsArray[$op2Pointer]))
    				{
    					// both operands are pointers and their pointed operations have been set up, create the operation
    					$operationsArray[$currentOperation['id']] = self::build(
    							$operationsArray[$op1Pointer], $currentOperation['operator'],$operationsArray[$op2Pointer],$currentOperation['priority']
    					);
    					$processed = true;
    				}

    				if ($processed)
    				{
    					unset ($inputOperations[$key]);
    					break;
    				}
    			}
    			/**
    			 * each iteration of the above foreach loop
    			 * should process one row returned from the DB.
    			 *
    			 * So if $loopCounter goes below zero (i.e.
    			 * exceeds the initial $inputOperations length)
    			 * and there are still some rows to be processed, something went wrong
    			 * and while loop must exit setting $operationsArray to empty
    			 */
    			if ($loopCounter <= 0 && !empty($inputOperations)) {
    				$operationsArray = array();
    			}
    		} // ends while (!empty($inputOperations) && ($loopCounter-- >= 0))
    	} // ends if (!empty($inputOperations))

    	/**
    	 * if the $operationArray is not empty, its last element
    	 * will contain the whole expression to be evaluated.
    	 */
    	if (!empty($operationsArray)) return end($operationsArray);
    	else {
    		throw new Exception('Could not generate operations tree', ADA_ERROR_ID_UNKNOWN_ERROR);
    		return null;
    	}
    }

    /**
     * builds a single operation from datas coming from $_POST
     * see static buildOperationFromPOST for details
     *
     * @param array  $data
     * @param string $operator
     * @param int    $priority
     * @return Operation|NULL
     * @access private
     */
    private static function buildSingleOperationFromPOST($data=array(),$operator=' && ', $priority=0)
    {
    	$keys = array_keys($data);
    	$operations = array();

    	for ($i=0; $i<count($keys); $i++)
    	{
    	$currentKey = $keys[$i];
    	$nextKey    = isset($keys[$i+1]) ? $keys[$i+1] :null;

    	if ($i>0 && is_null($nextKey)) break;

    	if ($i==0)
    		$currentoperand = $data[$currentKey];
    	else if (isset($operations[$i-1]))
    		$currentoperand = $operations[$i-1];
    	else
    		$currentoperand = null;

    		$nextoperand = !is_null ($nextKey) ? $data[$nextKey] : null;

    	if (!is_null($currentoperand) && is_null($nextoperand))
    		$operations[$i] = self::build($currentoperand,null,null,$priority);
    		else if (!is_null($currentoperand) && !is_null($nextoperand))
    		$operations[$i] = self::build($currentoperand,$operator,$nextoperand,$priority);
    	}

    	if (!empty($operations)) return end($operations);
    	else return null;
    }

	/**
	 * builds an operation tree from data coming from $_POST
	 *
	 * This method assumes that you have x sets of variable number of elements each
	 * and then:
	 * 1. the elements are used as operands with $opBetweenOperands operator (defaults to '&&' logical AND)
	 * 2. the x sets are used as operands with $opBetweenSets operator (defaults to '||' logical OR)
	 * E.g. the resulting full operation shall be:
	 *
	 * (val1_1 && val 1_2 && ... val1_n) || (val2_1 && val2_2 && ... val2_m) || ... (valx_1 && valx_2 && ... valx_w)
	 *
	 *
	 * @param array $data
	 * @param string $opBetweenGroups operation to be performed between two groups (i.e. the || in the above example)
	 * @param string $opBetweenOperands operation to be performed between two operands (i.e. the && in the above example)
	 * @return Operation|NULL
	 * @access public
	 */
    public static function buildOperationTreeFromPOST ($data=array(), $opBetweenGroups=null, $opBetweenOperands=null)
    {
    	if (is_null($opBetweenGroups))   $opBetweenGroups   = CompleteConditionSet::$opBetweenGroups;
    	if (is_null($opBetweenOperands)) $opBetweenOperands = CompleteConditionSet::$opBetweenOperands;

    	$operation = null;
    	$priority = 0;

    	foreach ($data as $row)
    	{
    		if (!empty($row) && !is_null($row))
    			$operation = Operation::build( self::buildSingleOperationFromPOST($row,$opBetweenOperands,$priority),
    					$opBetweenGroups,
    					$operation,
    					$priority++
    			);
    	}
    	return $operation;
		}

    /**
     * logToFile setter
     *
     * @param boolean $logToFile
     * @return CompleteConditionSet
     */
    public function setLogToFile($logToFile = false)
    {
        $this->logToFile = $logToFile;
        return $this;
    }

    /**
     * logToFile getter
     *
     * @return bool
     */
    public function getLogToFile()
    {
        return $this->logToFile;
    }
}
?>