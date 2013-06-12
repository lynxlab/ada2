<?php
/*=======================================================================
// File: 				JPGRAPH_CANVAS.PHP
// Description: 		Canvas drawing extension for JpGraph
// Created: 			2001-01-08
//	Last edit:			08/03/01 21:05
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS CanvasGraph
// Description: Creates a simple canvas graph which
// might be used together with the basic Image drawing
// primitives. Useful to auickoly produce some arbitrary
// graphic which benefits from all the functionality in the
// graph liek caching for example. 
//===================================================
class CanvasGraph extends Graph {
//---------------
// CONSTRUCTOR
	function CanvasGraph($aWidth=300,$aHeight=200,$aCachedName="",$a=0) {
		$this->Graph($aWidth,$aHeight,$aCachedName,$a);
	}

//---------------
// PUBLIC METHODS	

	// Method description
	function Stroke() {
		if( $this->texts != null )
			foreach( $this->texts as $t) {
				$t->x *= $this->img->width;
				$t->y *= $this->img->height;
				$t->Stroke($this->img);
			}
				
		// Finally stream the generated picture					
		$this->cache->PutAndStream($this->img,$this->cache_name);	
	}

} // Class
/* EOF */
?>