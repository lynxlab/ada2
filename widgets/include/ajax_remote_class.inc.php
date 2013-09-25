<?php
/* helper functions */

define ('JQUERY_SUPPORT',true);


class AjaxRemoteContent {
 // uses Ajax to ge a remote content (from ADA)
 
 var $content;
 private static  $placeholder = '[CONTENTPLACEHOLDER]'; 
 
 function __construct($div,$url){
 	
 	$content = "<div id='$div'>".self::$placeholder."</div>";
 	
 	if ($url)
 	{
	 	$replacement = 'Loading...';
	 	
		if (JQUERY_SUPPORT){						
			$ajax_content = "<script type='text/javascript'>					
					\$j('#$div').load('$url');					
					</script>";
		} else {
			// prototype 1.6 versione	
			$ajax_content = "<script type='text/javascript'> 
			new Ajax.Request('".$url."', {
			  method: 'get',	
			  onComplete: function(response) {
				 $('".$div."').update (response.responseText);
				 
			  }
			});
			</script>";
			}		
 	}
 	else { 
 		$replacement = 'CONTENT GENERATOR NOT FOUND';
 		$ajax_content = '';
 	}
 	 	
 	$this->content = str_replace(self::$placeholder, $replacement, $content) . $ajax_content; 	
}
	
 function getContent(){
	 return $this->content;
 }	
}

?>
