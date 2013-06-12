<?php
/**
 * SERVICE.
 *
 * @package		service
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		services_class
 * @version		0.1
 */

class Service
{

  private $implementors = array();

  private $title;
  private $level;
  private $description;
  private $duration;
  private $min_meetings;
  private $max_meetings;
  private $meeting_max_time;

  static public function  findServicesToSubscribe($orderBy = 'service',$minLevel = 1,$maxLevel = 5){
    $common_dh = $GLOBALS['common_dh'];
    $callerModule = $GLOBALS['self'];
    $sess_id_user = $_SESSION['sess_id_user'];
    $userObj = $_SESSION['sess_userObj'];



    // filtering on levels
    // $level_ha = Multiport::get_service_max_level($sess_id_user); FIXME: it OUGHT TO be used to filter services

    // version using COMMON

    if (isset($minLevel) AND ($minLevel<5)){
      $livelloMin = $minLevel;
    } else {
      $livelloMin = 1;
    }
    if (isset($maxLevel) AND ($maxLevel>1)){
      $livelloMax = $maxLevel;
    } else {
      $livelloMax = 5;
    }


    $clause = "s.livello <= $livelloMax AND s.livello >= $livelloMin ";

    // 	ordering
    if ($orderBy == 'service'){
 	    $service_infoAr = $common_dh->get_services(array('s.nome','t.nazione','s.livello'),$clause);
    } elseif($orderBy == 'country'){
      $service_infoAr = $common_dh->get_services(array('t.nazione','t.provincia','t.nome'),$clause);
    }

    $s = 0;
    $providers_data = array();
    foreach ($service_infoAr as $course_dataHa){
      //var_dump($course_dataHa);
      $service_implementation_id = $course_dataHa[3];
      $provider_name =  $course_dataHa[5];
      $provider_id =  $course_dataHa[4];
      if (!isset($providers_data[$provider_id])){
        $provider_dataHa =  $common_dh->get_tester_info_from_id($provider_id);
        $provider_pointer = $provider_dataHa[10];
        $providers_data[$provider_id] = $provider_dataHa;
      } else {
        $provider_pointer = $providers_data[$provider_id][10];
      }
      $provider_dsn = Multiport::getDSN($provider_pointer);
      if($provider_dsn != null) {
        // $provider_dataHa = $common_dh->get_tester_info_from_pointer($provider);
        $provider_dh = AMA_DataHandler::instance($provider_dsn);
        $id_course_instanceAr = $provider_dh->get_course_instance_for_this_student_and_course_model($sess_id_user, $service_implementation_id);

      } else {
        $id_course_instanceAr = NULL;
      }

      // already subscribed?
      if (
      (AMA_DataHandler::isError($id_course_instanceAr)) OR ($id_course_instanceAr == NULL)
      )
      { // never subscribed
        $id_course_instance = 0;
      } else {
        //var_dump($id_course_instanceAr);
        $id_course_instance = $id_course_instanceAr['istanza_id'];
        /* // FIXME: we have to get the real status AND expiration date for service implementation
         *   $now = time();
         if ($data_fine < $now){
         $stato = ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED;
         } else {
         $stato = ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED;
         }
         $service_infoAr[$s][9] = $stato;
         */
      }
      $service_infoAr[$s][8] = $id_course_instance;
      $s++;
    }

    $optionsAr = array(
		'callerModule'=>$callerModule,
		'orderBy'=>$orderBy
    );
    return GuestHtmlLib::displayAllServicesData($userObj,$service_infoAr,$optionsAr);

  }

  static public function findServiceFromImplementor($id_course){
    $common_dh = $GLOBALS['common_dh'];
    $service_dataHa = $common_dh->get_service_info_from_course($id_course);
    $serviceObj = new Service($service_dataHa);
    return $serviceObj;
  }

  public function __construct($serviceAr) {
    $common_dh = $GLOBALS['common_dh'];
    $this->id_service = $serviceAr[0];
    /*

    $providersAr = $common_dh->get_tester_for_service( $this->id_service);

    if (AMA_DataHandler::isError($providersAr)){
  		// ??
  		} else {
  		// $provider_pointer = $this->get_provider_pointer();
  		foreach ($providersAr as $providerAr){
  		$id_provider = $providerAr[0];
  		$implementorsAr = $common_dh->get_courses_for_service($this->id_service,$id_provider);
  		$implementorId = $implementorsAr[0];
  		$provider_dataHa =  $common_dh->get_tester_info_from_id($id_provider);
  		$provider_pointer = $provider_dataHa[10];
  		$implementorObj = new Service_implementor($implementorId,$provider_pointer);
  		//    	    var_dump($this->implementors);
  		$this->implementors[$implementorId] = $implementorObj;
  		}
  		}

  		$providersAr = $common_dh->get_tester_info_from_service( $this->id_service);
  		//T.id_tester,T.nome,T.ragione_sociale,T.indirizzo,T.provincia,T.nazione,T.telefono,T.e_mail,T.responsabile,T.puntatore
  		if (AMA_DataHandler::isError($providersAr)){
  		// ??
  		} else {
  		// $provider_pointer = $this->get_provider_pointer();
  		foreach ($providersAr as $providerAr){
  		$id_provider = $providerAr['id_tester'];
  		$implementorsAr = $common_dh->get_courses_for_service($this->id_service,$id_provider);
  		$implementorId = $implementorsAr[0];
  		$provider_pointer = $providerAr['puntatore'];
  		$implementorObj = new Service_implementor($implementorId,$provider_pointer);
  		$this->implementors[$implementorId] = $implementorObj;
  		}
  		}
  		*/

    if (AMA_DataHandler::isError($serviceAr)){
      //
    } else {
      $this->title =  $serviceAr[1];
      $this->description = $serviceAr[2];
      $this->level = $serviceAr[3];
      // durata_servizio, min_incontri, max_incontri, durata_max_incontro
      $this->duration =  $serviceAr[4];
      $this->min_meetings =  $serviceAr[5];
      $this->max_meetings =  $serviceAr[6];
      $this->meeting_max_time =  $serviceAr[7]/ 60;
    }
  }

  /* Getters */

  public function get_title(){
    return translateFN($this->title);
  }

  public function get_description(){
    return translateFN($this->description);
  }

  public function get_level(){
    return $this->level;
  }

  public function get_duration(){
    return $this->duration;
  }

  public function get_min_meetings(){
    return $this->min_meetings;
  }

  public function get_max_meetings(){
    return $this->max_meetings;
  }

  public function get_meeting_max_time(){
    return $this->meeting_max_time;
  }

  public function get_service_info(){
  	 $serviceAr = array(
      $this->get_title(),
      $this->get_description(),
      $this->get_level(),
      $this->get_duration(),
      $this->get_min_meetings(),
      $this->get_max_meetings(),
      $this->get_meeting_max_time(),
  	);
    return $serviceAr;
  }

  public function get_implementors(){

    $courseAr = array();
    foreach ($this->implementors  as $implementorId){
      $implementorObj = new Service_implementor($implementorId);
      //    	    var_dump($this->implementors);
      if (!isset($this->implementors[$implementorId])){
        $this->implementors[$implementorId] = $implementorObj;
      }
      $courseAr[$implementorId] = $implementorObj;
   	}
    return  $courseAr;
  }

  public function get_implementor($implementorId){
    if  (!isset($this->implementors[$implementorId])){
      $courseAr = $this->get_implementors();
      return $courseAr[$implementorId];
    } else {
      return $this->implementors[$implementorId];
    }
  }

} // end class Service

class Service_implementor
{
  private $provider_name;
  private $provider_ragsoc;

  private $provider_address;
  private $provider_department;
  private $provider_country;
  private $provider_city;
  private $provider_phone;
  private $provider_email;
  private $provider_responsible;

  private $provider_pointer;
  private $provider_desc;

  private $implementorId;

  private $name;
  private $title;
  private $id_author;
  private $id_layout;

  private $d_create;
  private $d_publish;
  private $id_start_node;
  private $id_toc_node;
  private $media_path;

  private $description;

  static public function findImplementor($implementorId){
    $common_dh = $GLOBALS['common_dh'];

    //$provider_dataHa = $common_dh->get_tester_info_from_id($id_provider);
    $provider_dataHa = $common_dh->get_tester_info_from_id_course($implementorId);
    if (AMA_DataHandler::isError($provider_dataHa)){
      // ?
    } else {
      $provider_dsn = Multiport::getDSN($provider_dataHa['puntatore']);
      if($provider_dsn != null) {
        $provider_dh = AMA_DataHandler::instance($provider_dsn);
        if (AMA_DataHandler::isError($provider_dh)){
          return $provider_dh;
        } else {
          $courseAr = $provider_dh->get_course($implementorId);

          if (AMA_DataHandler::isError($courseAr)){
            // continue
            $courseAr = array();
            $courseAr['id_course'] = $implementorId;

          } else {
            if (!isset($courseAr['id_nodo_iniziale'])){
              $courseAr['id_nodo_iniziale'] = 0;
            }

            $id_start_node = $courseAr['id_nodo_iniziale'];
            $id_desc = $implementorId."_".$id_start_node;
            $user_level = "999";

            $nodeHa = $provider_dh->get_node_info($id_desc);
            if (AMA_DataHandler::isError($nodeHa)){
              // continue
              $nodeHa = array();
              $nodeHa['text'] = NULL;
            }
          }
        }
      }
    }
    $serviceImplementorObj = new Service_implementor($provider_dataHa,$courseAr,$nodeHa);
    return $serviceImplementorObj;
  }

  public function __construct($provider_dataHa,$courseAr,$nodeHa) {

    $this->implementorId = $courseAr['id_course'];

    // id_tester,nome,ragione_sociale,indirizzo,citta,provincia,nazione,telefono,e_mail,responsabile,puntatore
    $this->provider_id = $provider_dataHa['id_tester'];
    $this->provider_name = $provider_dataHa['nome'];
    $this->provider_country = $provider_dataHa['nazione'];
    $this->provider_department = $provider_dataHa['provincia'];
    $this->provider_desc = $provider_dataHa['descrizione'];
    $this->provider_e_mail = $provider_dataHa['e_mail'];
    $this->provider_phone = $provider_dataHa['telefono'];
    $this->provider_address = $provider_dataHa['indirizzo'];
    $this->provider_ragsoc = $provider_dataHa['ragione_sociale'];
    $this->provider_responsible = $provider_dataHa['responsabile'];
    $this->provider_city = $provider_dataHa['citta'];
    $this->provider_pointer = $provider_dataHa['puntatore'];

    $this->name = $courseAr['nome'];
    $this->title = $courseAr['titolo'];
    // $this->id_author = $courseAr['id_autore'];
    // $this->id_layout = $courseAr['id_layout'];
    if ($courseAr['descr'] == NULL){
      $this->descr = level2descriptionFN($serviceAr[3]);
    }  else {
      $this->descr = $courseAr['descr'];
    }

    $this->d_create = $courseAr['d_create'];
    $this->d_publish = $courseAr['d_publish'];
    $this->id_start_node = $courseAr['id_nodo_iniziale'];
    //  $this->id_toc_node = $courseAr['id_nodo_toc'];
    //  $this->media_path = $courseAr['media_path'];

    if ($nodeHa['text'] == NULL) {
      //	$errorObj = new ADA_error($nodeHa); //FIXME: mancano gli altri dati
      $this->description = translateFN("Not available");
    }
    else {
      $this->description = $nodeHa['text'];
    }
  }

  /* Getters */

  public function get_title(){
    return $this->title;
  }

  public function get_description(){
    return $this->description;
  }

  public function get_descr(){
    return $this->descr;
  }

  public function get_name(){
    return $this->name;
  }

  public function get_creation_date(){
    return $this->d_create;
  }

  public function get_publish_date(){
    return $this->d_publish;
  }

  public function get_start_node(){
    return $this->start_node;
  }

  public function get_provider_pointer(){
    return $this->provider_pointer;
  }

  public function get_provider_name(){
    return $this->provider_name;
  }

  public function get_provider_country(){
    return $this->provider_country;
  }

  public function get_provider_department(){
    return $this->provider_department;
  }

  public function get_provider_city(){
    return $this->provider_city;
  }

  public function get_provider_desc(){
    return $this->provider_desc;
  }

  public function get_provider_address(){
    return $this->provider_address;
  }

  public function get_provider_e_mail(){
    return $this->provider_e_mail;
  }

  public function get_provider_phone(){
    return $this->provider_phone;
  }

  public function get_provider_ragsoc(){
    return $this->provider_ragsoc;
  }

  public function get_provider_responsible(){
    return $this->provider_responsible;
  }

  /**
   *
   * @return an associative array containing the information about provider and course
   */
  public function get_implementor_info(){
  	 $courseAr = array(
    	 $this->get_title(),
    	 $this->get_description(),
    	 $this->get_name(),
    	 $this->get_creation_date(),
    	 $this->get_publish_date(),
    	 $this->get_start_node(),
    	 $this->get_provider_name(),
    	 $this->get_provider_country(),
    	 $this->get_provider_department(),
    	 $this->get_provider_city(),
    	 $this->get_descr(),
    	 $this->get_provider_desc(),
    	 $this->get_provider_e_mail(),
    	 $this->get_provider_phone(),
    	 $this->get_provider_ragsoc(),
    	 $this->get_provider_address(),
    	 $this->get_provider_responsible()
  	 );
  	 return $courseAr;
  }
}
?>