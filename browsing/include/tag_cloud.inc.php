<?php
// this should be created by reading courses info
/*   $publishedServices = $common_dh->get_published_courses();
    if(!AMA_Common_DataHandler::isError($publishedServices)) {
        foreach($publishedServices as $service) {
            $serviceId = $service['id_servizio']; // how to keep track of it?
            $tok = strtok($service['nome'], " .,");
            $tokensAR = array[$tok];
            while ($tok !== false) {
				$tok = strtok(" .,");
				tokensAR[] = $tok;
			}
			$tok = strtok($service['descrizione'], " .,");
            $tokensAR = array[$tok];
            while ($tok !== false) {
				$tok = strtok(" .,");
				tokensAR[] = $tok;
			}
           
            );
            $ordersTokens = array_count_values($tokensAR);
        }
        $tags = array();
        foreach ($ordersTokens as $tag=>$count){
			$tags[] = array('weight'  =>$count, 'tagname' =>$tag, 'url'=>'info.php?op=course_info&id= $serviceId);
		}
*/
$tags = array(
        array('weight'  =>40, 'tagname' =>'lingue', 'url'=>'info.php?op=course_info&id=2'),
        array('weight'  =>12, 'tagname' =>'programmazione', 'url'=>'info.php?op=course_info&id=0'),
        array('weight'  =>10, 'tagname' =>'sicurezza', 'url'=>'info.php?op=course_info&id=1'),
        array('weight'  =>15, 'tagname' =>'reti', 'url'=>'info.php?op=course_info&id=2'),
        array('weight'  =>28, 'tagname' =>'consulenza', 'url'=>'info.php?op=course_info&id=2'),
        array('weight'  =>35, 'tagname' =>'web 2.0', 'url'=>'info.php?op=course_info&id=9'),
        array('weight'  =>20, 'tagname' =>'android', 'url'=>'info.php?op=course_info&id=1'),
);

 
/*** create a new tag cloud object ***/
$tagCloud = new tagCloud($tags);

return $tagCloud -> displayTagCloud();


class tagCloud{

/*** the array of tags ***/
private $tagsArray;


public function __construct($tags){
 /*** set a few properties ***/
 $this->tagsArray = $tags;
}

/**
 *
 * Display tag cloud
 *
 * @access public
 *
 * @return string
 *
 */
public function displayTagCloud(){
 $ret = '';
 shuffle($this->tagsArray);
 foreach($this->tagsArray as $tag)
    {
    $ret.='<a style="font-size: '.$tag['weight'].'px;" href="'.$tag['url'].'">'.$tag['tagname'].'</a>'."\n";
    }
 return $ret;
}
    

} /*** end of class ***/

?>
