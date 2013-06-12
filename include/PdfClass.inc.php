<?php
/**
 * PdfClass file
 *
 * PHP version 5
 *
 * @package   Default
 * @author    giorgio <g.consorti@lynxlab.com>
 * @copyright Copyright (c) 2010, Lynx s.r.l.
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

 /**
  * Helper class for generating PDF documents inside ADA.
  * Extends Cezpdf class adding help to generate same PDF layout either in
  * landscape or portrait mode. Capable of adding an header and footer to the document
  * 
  * Refer to Cezpdf documentation and website for usage (http://pdf-php.sourceforge.net/)
  *
  * @package   Default
  * @author    giorgio <g.consorti@lynxlab.com>
  * @copyright Copyright (c) 2010, Lynx s.r.l.
  * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
  */

require_once '../config_path.inc.php';
require_once ROOT_DIR.'/config/config_install.inc.php';

define ('CEZPDF_PATH' , ROOT_DIR . '/include/Cezpdf');
define ('CEZPDF_FONTS_PATH' , CEZPDF_PATH.'fonts');

define ('DEFAULT_DOC_FONT', 'Helvetica');
define ('DEFAULT_DOC_FONTSIZE', 12);

require_once CEZPDF_PATH.'/Cezpdf.php';


 class PdfClass extends Cezpdf
 {
 	private $_topMargin = 75;
 	private $_bottomMargin = 70;
 	private $_leftMargin = 30;
 	private $_rightMargin = 30;
 	
 	private $_headerHeight = 60;
 	private $_footerHeight = 60;
 	
 	private $_headerImgWidth  = 95;
 	private $_headerImgHeight = 43;
 	
 	public $docFontSize = DEFAULT_DOC_FONTSIZE;
 	
     /**
      * PdfClass constructor.
      */
     public function __construct($orientation='portrait', $title='')
     {
     	 if (strtolower($orientation)!=='landscape')  $orientation = 'portrait';

     	 parent::__construct('a4',$orientation);         
         /**
          * sets default info
          */
         $this->addInfo('Author', PORTAL_NAME);
         $this->addInfo('Creator','ADA platform v'.ADA_VERSION);
         $this->addInfo('Producer', 'Lynx Srl');
         $this->addInfo('CreationDate', date('YmdHis'));
          
         /**
          * sets defaults margins
          */
         $this->ezSetMargins($this->_topMargin, $this->_bottomMargin, $this->_leftMargin, $this->_rightMargin);
         /**
          * sets the title if it's been passed
          */
         if (isset($title) && $title!='') $this->setTitle($title);
         /**
          * sets 'Helvetica' as default font
          */
         $this->setFont(DEFAULT_DOC_FONT, DEFAULT_DOC_FONTSIZE);
     }
     
     /**
      * sets meta title of generated PDF document.
      *
      * @param string $title main title of PDF document
      *
      * @return this
      * @access public
      */
     public function setTitle($title)
     {
     	if ($title!='') $this->addInfo('Title', $title);
     	return $this;
     }
     
     /**
      * sets the font to be used for writing text in the document.
      *
      * @param string $fontName	font name to be used, available choices: 'Courier', Helvetica', 'Times'
      * @param int    $fontSize the size of the font
      *
      * @return this
      * @access public 
      */
     public function setFont($fontName, $fontSize = DEFAULT_DOC_FONTSIZE )
     {
         if (isset($fontName) && $fontName!='' && is_file(CEZPDF_FONTS_PATH.'/'.$fontName.'.afm'))
         	$this->selectFont(CEZPDF_FONTS_PATH.'/'.$fontName);
         $this->docFontSize = $fontSize;
         return  $this;
     }
     
     /**
      * adds a header to each page consisting of the passed image file at the top left corner,
      * followed by a center justified message and closed with a black solid line at the bottom
      *
      * @param string $headerText  text to be displayed as header
	  * @param int    $size        text font size to be used. Defaults to 12
      * @param string $imgFileName filename of image to be used as header. can be empty.  
      *
      * @return this
      * @access public
      */
     public function addHeader ($headerText, $imgFileName = '', $size = 12)
     {
    	$headerObj = $this->openObject();
    	
    	if (is_file($imgFileName))
    	{
    		// add template header logo, rescaled to fixed measurements because I need to know
    		// exact height
    		// $this->ez['pageHeight']-$this->ez['topMargin']-$this->_headerImgHeight
    		$this->addPngFromFile($imgFileName ,
    				$this->ez['leftMargin'], 
    				$this->ez['pageHeight'] - $this->_headerHeight + 5,
    				$this->_headerImgWidth, 
    				$this->_headerImgHeight);
    	}
    	
    	if (strlen($headerText))
    	{    	
    		$this->addText($this->ez['leftMargin'] + $this->_headerImgWidth +10,
    				$this->ez['pageHeight']-$this->ez['marginTop']-$this->_headerImgHeight, $size,
    				$headerText);
    	}
    	
    	$this->line($this->ez['leftMargin'],
    				$this->ez['pageHeight'] - $this->_headerHeight, 
    				$this->ez['pageWidth'] - $this->ez['rightMargin'],
    				$this->ez['pageHeight'] - $this->_headerHeight);
    	    	
    	$this->closeObject();
    	$this->addObject($headerObj, "all");
    	
    	return $this;
     }
     
     /**
      * adds a footer to each page consisting of a solid black line at bottom of page,
      * followed by a left justified message and right justified page numbers
      *
      * @param string  $footerText  text to be display as footer
      * @param boolean $pageNumbers true to include right justified page numbers. Defaults to true
      * @param int     $size        text font size to be used. Defaults to 8
      *
      * @return this
      * @access public
      */
     public function addFooter($footerText, $pageNumbers = true, $size = 8)
     {
     	$textVerticalOffset = (int ) ($size * 1.3);     	
     	if ($pageNumbers)
     	{
     		$this->ezStartPageNumbers($this->ez['pageWidth']-$this->ez['rightMargin'],$this->_footerHeight - $textVerticalOffset,$size,'','Pag. {PAGENUM} / {TOTALPAGENUM}');
     	}
     	if ($footerText!='')
     	{
     		$footerObj = $this->openObject();
     		$this->line($this->ez['leftMargin'],$this->_footerHeight,$this->ez['pageWidth']-$this->ez['rightMargin'],$this->_footerHeight);     		
     		$this->addText($this->ez['leftMargin'], $this->_footerHeight - $textVerticalOffset , $size, $footerText);     		
     		$this->closeObject();
     		$this->addObject($footerObj, "all");     		 
     	}
     	
     	return $this;     	
     }
     
     /**
      * stream out the PDF file with a given filename
      *
      * @param string $filename	the file name that will be asked at browser' save as dialog
      *
      * @return this
      * @access public
      */
     public function saveAs($filename)
     {
     	$this->ezStream( array ('Content-Disposition'=>$filename) );
     }
     
 }

?>