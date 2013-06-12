<?php
/*=======================================================================
// File: 				JPGRAPH_LINE.PHP
// Description: 		Line plot extension for JpGraph
// Created: 			2001-01-08
//	Last edit:			29/04/01 12:48
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS LinePlot
// Description: 
//===================================================
class LinePlot extends Plot{
	var $filled=false;
	var $fill_color;
	var $mark=null;
	var $step_style=false, $center=false;
//---------------
// CONSTRUCTOR
	function LinePlot(&$datay,$datax=false) {
		$this->Plot($datay,$datax);
		$this->mark = new PlotMark();
		$this->mark->SetColor($this->color);
	}
//---------------
// PUBLIC METHODS	
	// Set style, filled or open
	function SetFilled($f=true) {
		$this->filled=$f;
	}
	
	function SetStepStyle($f=true) {
		$this->step_style = $f;
	}
	
	function SetColor($c) {
		parent::SetColor($c);
		$this->mark->SetColor($this->color);
	}
	
	function SetFillColor($c,$f=true) {
		$this->fill_color=$c;
		$this->filled=$f;
	}
	
	function Legend(&$graph) {
		if( $this->legend!="" ) {
 			if( $this->filled ) {
 				$graph->legend->Add($this->legend,
 					$this->fill_color,$this->mark);
 			} else {
 				$graph->legend->Add($this->legend,
 					$this->color,$this->mark);
 			}
 		}	
	}
	
	function SetCenter($c=true) {
		$this->center=$c;
	}	
	
	// Gets called before any axis are stroked
	function PreStrokeAdjust(&$graph) {
		if( $this->center ) {
			++$this->numpoints;
			$a=0.5; $b=0.5;
		} else {
			$a=0; $b=0;
		}
		$graph->xaxis->scale->ticks->SetXLabelOffset($a);
		$graph->SetTextScaleOff($b);						
		$graph->xaxis->scale->ticks->SupressMinorTickMarks();
	}
	
	function Stroke(&$img,&$xscale,&$yscale) {
		$numpoints=count($this->coords[0]);
		if( isset($this->coords[1]) )
			$exist_x = (count($this->coords[1])==$numpoints);
		else 
			$exist_x = false;

		if( $exist_x )
			$xs=$this->coords[1][0];
		else
			$xs=1;

		$img->SetStartPoint($xscale->Translate($xs),
								  $yscale->Translate($this->coords[0][0]));
		
		if( $this->filled ) {
			$cord[] = $xscale->Translate($xs);
			$cord[] = $yscale->Translate($yscale->GetMinVal());
		}
		$cord[] = $xscale->Translate($xs);
		$cord[] = $yscale->Translate($this->coords[0][0]);
		$yt_old = $yscale->Translate($this->coords[0][0]);
		$img->SetColor($this->color);
		$img->SetLineWeight($this->weight);					
		for( $pnts=1; $pnts<$numpoints; ++$pnts) {
			if( $exist_x ) $x=$this->coords[1][$pnts];
			else $x=$pnts+1;
			$xt = $xscale->Translate($x);
			$yt = $yscale->Translate($this->coords[0][$pnts]);
			$cord[] = $xt;
			$cord[] = $yt;
			if( $this->step_style ) {
				$img->LineTo($xt,$yt_old);
				$img->LineTo($xt,$yt);
			}
 		 	else {
 		 		if( $this->coords[0][$pnts] != "-" ) { 		 			
 					if ($this->coords[0][$pnts] != "" && $this->coords[0][$pnts-1] != "") { 
 						$img->LineTo($xt,$yt);
 					} 
 					else {
 						$img->SetStartPoint($xt,$yt);
 					}
 				}
 			}
			$yt_old = $yt;
		}	
		if( $this->filled ) {
			$cord[] = $xt;
			$cord[] = $yscale->Translate($yscale->GetMinVal());					
			$img->SetColor($this->fill_color);	
			$img->FilledPolygon($cord);
			$img->SetColor($this->color);
			$img->Polygon($cord);
		}
		$adjust=0;
		if( $this->filled ) $adjust=2;
		for($i=$adjust; $i<count($cord)-$adjust; $i+=2) {
			if( $this->coords[0][($i-$adjust)/2] != "" && $this->coords[0][($i-$adjust)/2] != "-")
				$this->mark->Stroke($img,$cord[$i],$cord[$i+1]);
		}
	}
//---------------
// PRIVATE METHODS	
} // Class


//===================================================
// CLASS AccLinePlot
// Description: 
//===================================================
class AccLinePlot extends Plot {
	var $plots=null,$nbrplots=0,$numpoints=0;
//---------------
// CONSTRUCTOR
	function AccLinePlot($plots) {
		$this->plots = $plots;
		$this->nbrplots = count($plots);
		$this->numpoints = $plots[0]->numpoints;		
	}

//---------------
// PUBLIC METHODS	
	function Legend(&$graph) {
		foreach( $this->plots as $p )
			$p->Legend($graph);
	}
	
	function Max() {
		$accymax=0;
		list($xmax,$dummy) = $this->plots[0]->Max();
		foreach($this->plots as $p) {
			list($xm,$ym) = $p->Max();
			$xmax = max($xmax,$xm);
			$accymax += $ym;
		}
		return array($xmax,$accymax);
	}

	function Min() {
		list($xmin,$ymin)=$this->plots[0]->Min();
		foreach( $this->plots as $p ) {
			list($xm,$ym)=$p->Min();
			$xmin=Min($xmin,$xm);
			$ymin=Min($ymin,$ym);
		}
		return array($xmin,$ymin);	
	}

	// To avoid duplicate of line drawing code here we just
	// change the y-values for each plot and then restore it
	// after we have made the stroke. We must do this copy since
	// it wouldn't be possible to create an acc line plot
	// with the same graphs, i.e AccLinePlot(array($pl,$pl,$pl));
	// since this method would have a side effect.
	function Stroke(&$img,&$xscale,&$yscale) {
		$img->SetLineWeight($this->weight);
		// Allocate array
		$coords[$this->nbrplots][$this->numpoints]=0;
		for($i=0; $i<$this->numpoints; $i++) {
			$coords[0][$i]=$this->plots[0]->coords[0][$i]; 
			$accy=$coords[0][$i];
			for($j=1; $j<$this->nbrplots; ++$j ) {
				$coords[$j][$i] = $this->plots[$j]->coords[0][$i]+$accy; 
				$accy = $coords[$j][$i];
			}
		}
		for($j=$this->nbrplots-1; $j>=0; --$j) {
			$p=$this->plots[$j];
			for( $i=0; $i<$this->numpoints; ++$i) {
				$tmp[$i]=$p->coords[0][$i];
				$p->coords[0][$i]=$coords[$j][$i];
			}
			$p->Stroke($img,$xscale,$yscale);
			for( $i=0; $i<$this->numpoints; ++$i) 
				$p->coords[0][$i]=$tmp[$i];
			$p->coords[0][]=$tmp;
		}
	}
} // Class

/* EOF */
?>
