<?
/*=======================================================================
// File: 				JPGRAPH_SCATTER.PHP
// Description: 		Scatter (and impuls) plot extension for JpGraph
// Created: 			2001-02-11
//	Last edit:			29/04/01 12:49
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS ScatterPlot
// Description: Render X and Y plots
//===================================================
class ScatterPlot extends Plot {
	var $impuls = false;
//---------------
// CONSTRUCTOR
	function ScatterPlot(&$datay,$datax=false) {
		if( (count($datax) != count($datay)) && is_array($datax))
			die("JpGraph: Scatterplot must have equal number of X and Y points.");
		$this->Plot($datay,$datax);
		$this->mark = new PlotMark();
		$this->mark->SetType(MARK_CIRCLE);
		$this->mark->SetColor($this->color);
	}

//---------------
// PUBLIC METHODS	
	function SetImpuls($f=true) {
		$this->impuls = $f;
	}

	function Stroke(&$img,&$xscale,&$yscale) {
		$ymin=$yscale->scale_abs[0];
		for( $i=0; $i<$this->numpoints; ++$i ) {
			if( isset($this->coords[1]) )
				$xt = $xscale->Translate($this->coords[1][$i]);
			else
				$xt = $xscale->Translate($i+1);
			$yt = $yscale->Translate($this->coords[0][$i]);	
			if( $this->impuls ) {
				$img->SetColor($this->color);
				$img->SetLineWeight($this->weight);
				$img->Line($xt,$ymin,$xt,$yt);
			}
			$this->mark->Stroke($img,$xt,$yt);	
		}
	}
} // Class
/* EOF */
?>