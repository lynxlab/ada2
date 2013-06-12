<?php

/* classe di conversione dati da e verso xml
usa le classei PEAR serialize e unserialize
va chiamato cosi':

$xmlObj = new xmlConverter();
$xmlObj->setXml($xml_data);
$xmlObj->xml2array();
$dataHa = $xmlObj->getdata();


o al contrario:
$xmlObj = new xmlConverter();
$xmlObj->setdata($data);
$xmlObj->array2xml();
$xml_data = $xmlObj->getxmldata();

*/

class XMLconverter{

var $data = array();
var $xml_data = "";

function setxml($xml_data){
	$this->xml_data = $xml_data;
}

function setdata($data){
	$this->data = $data;
}

function getxmldata(){
	return $this->xml_data;
}

function getdata(){
	return $this->data;
}


function xml2array(){
//converte dati xml in un array
/**
 * uses PEAR error management
 */
require_once 'PEAR.php';
/**
 * uses XML_Parser to unserialize document
 */
require_once "XML/Parser.php";

/**
 * uses unserializer 
 */
require_once "XML/Unserializer.php";

	$xml_data = $this->xml_data ;
	$unserializer = &new XML_Unserializer();
	$unserializer->unserialize($xml_data);
	$data = $unserializer->getUnserializedData();
	$this->data = $data;

}

function array2xml(){
	
/**
 * uses PEAR error management
 */
require_once 'PEAR.php';

/**
 * uses XML_Util to create XML tags
 */
require_once "XML/Util.php";
/**
 * uses serializer 
 */
require_once "XML/Serializer.php";



	$data = $this->data;

	$options = array(
                     XML_SERIALIZER_OPTION_INDENT      => "\t",        // indent with tabs
                     XML_SERIALIZER_OPTION_LINEBREAKS  => "\n",        // use UNIX line breaks
                      XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML,
		      XML_SERIALIZER_OPTION_RETURN_RESULT=> true,
                      XML_SERIALIZER_OPTION_DEFAULT_TAG => 'item'       // tag for values with numeric keys
    	);
    	$serializer = new XML_Serializer($options);

    	$xml_data        = $serializer->serialize($data);
	$this->xml_data = $xml_data;
	
}

} // class
?>