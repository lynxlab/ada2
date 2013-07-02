<?php

/**
 * 
 * @package		openlabor
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @copyright           Copyright (c) 20012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU Public License v.3
 * @link					
 * @version		0.1
 */

require_once CORE_LIBRARY_PATH .'/includes.inc.php';

class searchHtmlLib {

  /**
   * 
   * @param type $jobsData
   * @param type $withLink
   * @return type object
   */  
  public static function OffersTable($jobsData,$withLink) {
        $summary =  translateFN('Risultati della ricerca'); // per: '.$labelsDesc;
         $thead_data = array(
              translateFN('Position'),
              translateFN('Position Code'),
              translateFN('Job Expiration'),
              translateFN('City of company'),
              translateFN('Qualification required'),
              translateFN('CPI'),
          );
        $tbody_dataAr = array();

        foreach ($jobsData as $singleJobObj) {
            $singleJobData = (array)$singleJobObj;
            if ($withLink) {
                $href = HTTP_ROOT_DIR.'/modules/jobSearch/search.php?op=jcard&id='.$singleJobData['idJobOffers'];
                $textLink = $singleJobData['position'];
                $linkJobCard = BaseHtmlLib::link($href, $textLink);
                $singleJobData['position'] =$linkJobCard; 
                
            }
            $tbody_dataAr[] = array(
                  $singleJobData['position'],
                  $singleJobData['positionCode'],
                  AMA_Common_DataHandler::ts_to_date($singleJobData['jobExpiration']),
                  $singleJobData['cityCompany'],
                  $singleJobData['qualificationRequired'],
                  $singleJobData['nameCPI']
            );

        }
        $element_attributes ="id:sortableTable, class:dataTable";
        $jobsTable = BaseHtmlLib::tableElement($element_attributes, $thead_data, $tbody_dataAr);
        return $jobsTable;
    }
    
    public static function CPIShow($CPIData,$withLink=false) {
      $cpi_container = CDOMElement::create('div','id:map_container');
      $map_div = CDOMElement::create('div','id:map');
      $cpi_container->addChild($map_div); 
      $data_div = CDOMElement::create('div','id:cpi_data');
      unset($CPIData['cpicod']);
      $data_list = BaseHtmlLib::plainListElement('class:cpi_data', $CPIData, FALSE);
      $data_div->addChild($data_list); 
    
      $cpi_container->addChild($data_div); 
      
      
      return $cpi_container;

    }
    
    public static function jobCardTable($jobsData,$withLink=false) {
        /*
        $summary =  translateFN('Risultati della ricerca'); // per: '.$labelsDesc;
         $thead_data = array(
              translateFN('Position'),
              translateFN('Position Code'),
              translateFN('Job Expiration'),
              translateFN('City of company'),
              translateFN('Qualification required'),
              translateFN('CPI'),
          );
        $tbody_dataAr = array();
         * 
         */

        foreach ($jobsData as $singleJobObj) {
            $singleJobData = (array)$singleJobObj;
            if ($withLink) {
//                $linkToDetail = '<a href='.HTTP_ROOT_DIR.'/browsing/external_link.php?url='. $singleJobData['linkMoreInfo'];
                $linkToDetail = '<a href='.$singleJobData['linkMoreInfo'];
                $linkToDetail .= ' target="_blank">'.$singleJobData['position'].'</a>';
                $singleJobData['position'] =$linkToDetail; 
                /*
                $href = HTTP_ROOT_DIR.'/browsing/external_link.php?url='. $singleJobData['linkmoreinfo'];
                $textLink = $singleJobData['position'];
                $linkJobCard = BaseHtmlLib::link($href, $textLink);
                $singleJobData['position'] =$linkJobCard; 
                 * 
                 */
                /*
                 * LINK TO CPI (MAP AND DATA)
                 */
                
//                  $CPIData = array();  
                $CPIData = array(
//                  $CPIData[$singleJobData['idcpi']] = array (
                    'cpicod'=>$singleJobData['CPICod'],
                    'namecpi'=>$singleJobData['nameCPI'],
                    'address'=>$singleJobData['address'], 
                    'cap'=>$singleJobData['cap'],
                    'city'=>$singleJobData['city'],
                    'cpizone'=>$singleJobData['CPIzone'],
                    'phone'=>$singleJobData['phone'],
                    'fax'=>$singleJobData['fax'],
                    'email'=>$singleJobData['email'],
                    'latitude'=>$singleJobData['latitude'],
                    'longitude'=>$singleJobData['longitude']
                );
//                $CPIDataSerialized = serialize($CPIData);
//                $href = HTTP_ROOT_DIR.'/modules/jobSearch/search.php?op=cpi&cpidata='.urlencode($CPIDataSerialized);
                $idCpi = $singleJobData['idCPI'];
                $_SESSION['sess_cpi_'.$idCpi] = $CPIData;
                $href = HTTP_ROOT_DIR.'/modules/jobSearch/search.php?op=cpi&idcpi='.$idCpi;
                $textLink = $singleJobData['CPICod'];
                $linkCpiCard = BaseHtmlLib::link($href, $textLink);
                $singleJobData['CPIname'] =$linkCpiCard; 
            }
//            print_r($singleJobData);
            /*
            $tbody_dataAr[] = array(
                translateFN('Position'),
                $singleJobData['position']
            );
            $tbody_dataAr[] = array(
                translateFN('Job Expiration'),
                AMA_Common_DataHandler::ts_to_date($singleJobData['jobexpiration']),
            );
             * 
             */
          if ($singleJobData['minAge'] > 0 && $singleJobData['maxAge']> 0) {
              $age = $singleJobData['minAge'] . ' - '. $singleJobData['maxAge'];
          } else {
              $age = translateFN('No Limited');
          }
 
           $tbody_dataAr = array(
               array(
                    translateFN('Position'),
                    $singleJobData['position']
               ),
               array(
                   translateFN('Professional profile'),
                   $singleJobData['professionalProfile']
               ),
                array(
                    translateFN('Position Code'),
                    $singleJobData['positionCode']
               ),
                array(
                    translateFN('Job Expiration'),
                    AMA_Common_DataHandler::ts_to_date($singleJobData['jobExpiration'])
               ),
               array(
                   translateFN('Workplace'),
                   $singleJobData['cityCompany']
               ),
                array(
                    translateFN('Number of workers required'),
                    $singleJobData['workersRequired']
               ),
                array(
                    translateFN('Centro Per l\'Impiego'),
                    $singleJobData['CPIname']
               ),
               array(
                   translateFN('Qualification required'),
                   $singleJobData['qualificationRequired']
               ),
               array(
                   translateFN('Experience required'),
                   $singleJobData['experienceRequired']
               ),
               array(
                   translateFN('Age'),
                   $age
               ),
               array(
                   translateFN('Own vehicle'),
                   $singleJobData['ownVehicle']
               ),
               array(
                   translateFN('Other requirements'),
                   $singleJobData['notes']
               ),
               array(
                   translateFN('Reserved for disable'),
                   $singleJobData['reservedForDisabled']
               ),
               array(
                   translateFN('Favored Category requested'),
                   $singleJobData['favoredCategoryRequests']
               ),
           );

        }
        /*
            $singleJobData['positioncode'],
              AMA_Common_DataHandler::ts_to_date($singleJobData['jobexpiration']),
              $singleJobData['citycompany'],
              $singleJobData['qualificationrequired'],
              $singleJobData['namecpi']
        );
         * 
         */

        $element_attributes ="id:jcard, class:jcard";
        $jobsTable = BaseHtmlLib::tableElement($element_attributes, $thead_data, $tbody_dataAr);
        return $jobsTable;
        
    }
    
}

