<?php
/*=======================================================================
// File: 				JPGRAPH_BAR.PHP
// Description: 		Bar plot extension for JpGraph
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
// CLASS Gradient
// Description: Handles gradient fills. This is to be
// considered a "friend" class of Class Image
//===================================================
class Gradient {
	var $img=null;
//---------------
// CONSTRUCTOR
	function Gradient(&$img) {
		$this->img = $img;
	}

//---------------
// PUBLIC METHODS	
	function FilledRectangle($xl,$yt,$xr,$yb,$from_color,$to_color,$style=1) {
		switch( $style ) {	
		case 1:  // HORIZONTAL
			$steps = $xr-$xl;
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			for($x=$xl; $x<=$xl+$steps; ++$x) {
				$this->img->current_color = $colors[$x-$xl];
				$this->img->Line($x,$yt,$x,$yb);
			}
			break;
		case 2: // VERTICAL
			$steps = $yb-$yt;	
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			for($y=$yt; $y<$yt+$steps; ++$y) {
				$this->img->current_color = $colors[$y-$yt];
				$this->img->Line($xl,$y,$xr,$y);
			}
			break;
		case 3: // VERTICAL FROM MIDDLE
			$steps = ($yb-$yt)/2;
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			for($y=$yt, $i=0; $y<$yt+$steps; ++$y, ++$i) {
				$this->img->current_color = $colors[$i];
				$this->img->Line($xl,$y,$xr,$y);
			}
			--$i;
			for($y=$yt+$steps; $i>0; ++$y, --$i) {
				$this->img->current_color = $colors[$i];
				$this->img->Line($xl,$y,$xr,$y);
			}
			$this->img->Line($xl,$y,$xr,$y);
			break;
		case 4: // HORIZONTAL FROM MIDDLE
			$steps = ($xr-$xl)/2;
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			for($x=$xl, $i=0; $x<$xl+$steps; ++$x, ++$i) {
				$this->img->current_color = $colors[$i];
				$this->img->Line($x,$yb,$x,$yt);
			}
			--$i;
			for($x=$xl+$steps; $i>0; ++$x, --$i) {
				$this->img->current_color = $colors[$i];
				$this->img->Line($x,$yb,$x,$yt);
			}
			$this->img->Line($x,$yb,$x,$yt);		
			break;
		case 5: // Rectangle
			$steps = floor(min(($yb-$yt)+1,($xr-$xl)+1)/2);	
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			$dx = ($xr-$xl)/2;
			$dy = ($yb-$yt)/2;
			$x=$xl;$y=$yt;$x2=$xr;$y2=$yb;
			for($x=$xl, $i=0; $x<$xl+$dx && $y<$yt+$dy ; ++$x, ++$y, --$x2, --$y2, ++$i) {
				assert($i<count($colors));
				$this->img->current_color = $colors[$i];			
				$this->img->Rectangle($x,$y,$x2,$y2);
			}
			$this->img->Line($x,$y,$x2,$y2);
			break;
		case 6: // HORIZONTAL WIDER MIDDLE
			$steps = ($xr-$xl)/3;
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			for($x=$xl, $i=0; $x<$xl+$steps; ++$x, ++$i) {
				$this->img->current_color = $colors[$i];
				$this->img->Line($x,$yb,$x,$yt);
			}
			--$i;
			$this->img->current_color = $colors[$i];
			for($x=$xl+$steps; $x<$xl+2*$steps; ++$x) {
				$this->img->Line($x,$yb,$x,$yt);
			}
			for($x=$xl+2*$steps; $i>=0; ++$x, --$i) {
				$this->img->current_color = $colors[$i];				
				$this->img->Line($x,$yb,$x,$yt);		
			}				
			break;
		case 7: // VERTICAL WIDER MIDDLE
			$steps = ($yb-$yt)/3;
			$this->GetColArray($from_color,$to_color,$steps,$colors);
			for($y=$yt, $i=0; $y<$yt+$steps; ++$y, ++$i) {
				$this->img->current_color = $colors[$i];
				$this->img->Line($xl,$y,$xr,$y);
			}
			--$i;
			$this->img->current_color = $colors[$i];
			for($y=$yt+$steps; $y<$yt+2*$steps; ++$y) {
				$this->img->Line($xl,$y,$xr,$y);
			}
			for($y=$yt+2*$steps; $i>=0; ++$y, --$i) {
				$this->img->current_color = $colors[$i];				
				$this->img->Line($xl,$y,$xr,$y);		
			}				
			break;
		default:
			die("JpGraph Error: Unknown gradient style (=$style).");
			break;
		}
	}

//---------------
// PRIVATE METHODS	
	function GetColArray($from_color,$to_color,$arr_size,&$colors,$numcols=100) {
		if( $arr_size==0 ) return;
		// If color is give as text get it's corresponding r,g,b values
		$from_color = $this->img->rgb->Color($from_color);
		$to_color = $this->img->rgb->Color($to_color);
		
		$rdelta=($to_color[0]-$from_color[0])/$numcols;
		$gdelta=($to_color[1]-$from_color[1])/$numcols;
		$bdelta=($to_color[2]-$from_color[2])/$numcols;
		$stepspercolor	= $numcols/$arr_size;
		$prevcolnum	= -1;
		for ($i=0; $i<$arr_size; ++$i) {
			$colnum	= floor($stepspercolor*$i);
			if ( $colnum == $prevcolnum ) 
				$colors[$i]	= $colidx;
			else {
				$r = floor($from_color[0] + $colnum*$rdelta);
				$g = floor($from_color[1] + $colnum*$gdelta);
				$b = floor($from_color[2] + $colnum*$bdelta);
				$colidx = $this->img->rgb->Allocate(sprintf("#%02x%02x%02x",$r,$g,$b));
				$colors[$i]	= $colidx;
			}
			$prevcolnum = $colnum;
		}
	 }	
} // Class


//===================================================
// CLASS BarPlot
// Description: 
//===================================================
class BarPlot extends Plot {
	var $width=0.4; // in percent of major ticks
	var $fill_color=false; // No fill default
	var $ymin=0;
	var $grad=false,$grad_style=1;
	var $grad_fromcolor=array(50,50,200),$grad_tocolor=array(255,255,255);
//---------------
// CONSTRUCTOR
	function BarPlot(&$datay) {
		$this->Plot($datay);		
		++$this->numpoints;
	}

//---------------
// PUBLIC METHODS	
	function SetYStart($y) {	// DEPRECATED NAME!
		$this->ymin=$y;
	}
	
	function SetYMin($y) {
		$this->ymin=$y;
	}
	
	function Legend(&$graph) {
		if( $this->fill_color && $this->legend!="" )
			$graph->legend->Add($this->legend,$this->fill_color);		
	}

	// Gets called before any axis are stroked
	function PreStrokeAdjust(&$graph) {
		parent::PreStrokeAdjust($graph);
		// Center each bar within each major tick
		$graph->xaxis->scale->ticks->SetXLabelOffset(0.5);
		$graph->SetTextScaleOff(0.5-$this->width/2);						
		$graph->xaxis->scale->ticks->SupressTickMarks();
	}

	function Min() {
		$m = parent::Min();
		if( $m[1] > 0 ) $m[1]=$this->ymin;
		return $m;	
	}
	
	function SetWidth($w) {
		assert($w > 0 && $w <= 1.0);
		$this->width=$w;
	}
	
	function SetNoFill() {
		$this->grad = false;
		$this->fill_color=false;
	}
		
	function SetFillColor($c) {
		$this->fill_color=$c;
	}
	
	function SetFillGradient($from_color,$to_color,$style) {
		$this->grad=true;
		$this->grad_fromcolor=$from_color;
		$this->grad_tocolor=$to_color;
		$this->grad_style=$style;
	}
	
	function Stroke(&$img,&$xscale,&$yscale) { 
		$img->SetColor($this->color);
		$img->SetLineWeight($this->weight);
		$numbars=count($this->coords[0]);
		if( $yscale->scale[0] >= 0 )
			$zp=$yscale->scale_abs[0]; 
		else
			$zp=$yscale->Translate(0.0);
		$abswidth=round($this->width*$xscale->scale_factor,0);
		for($i=0; $i<$numbars; $i++) {
			$x=$xscale->Translate($i+1);
			$pts=array(
				$x,$zp,
				$x,$yscale->Translate($this->coords[0][$i]),
				$x+$abswidth,$yscale->Translate($this->coords[0][$i]),
				$x+$abswidth,$zp);
			if( $this->grad ) {
				$grad = new Gradient($img);
				$grad->FilledRectangle($pts[2],$pts[3],
											  $pts[6],$pts[7],
											  $this->grad_fromcolor,$this->grad_tocolor,$this->grad_style);				
			}
			elseif( $this->fill_color ) {
				$img->SetColor($this->fill_color);
				$img->FilledPolygon($pts,4);
				$img->SetColor($this->color);
			}
			$img->Polygon($pts,4);
		}
		return true;
	}
} // Class

//===================================================
// CLASS GroupBarPlot
// Description: 
//===================================================
class GroupBarPlot extends BarPlot {
	var $plots;
	var $width=0.7;
	var $nbrplots=0;
	var $numpoints;
//---------------
// CONSTRUCTOR
	function GroupBarPlot($plots) {
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
	
	function Min() {
		return array(0,$this->ymin);	// Must be adjusted for log plots
	}
	
	function Max() {
		list($xmax,$ymax) = $this->plots[0]->Max();
		foreach($this->plots as $p) {
			list($xm,$ym) = $p->Max();
			$xmax = max($xmax,$xm);
			$ymax = max($ymax,$ym);
		}
		return array($xmax,$ymax);
	}
	
	// Stroke all the bars next to each other
	function Stroke(&$img,&$xscale,&$yscale) { 
		$i=0;
		$tmp=$xscale->off;
		foreach( $this->plots as $p ) {
			$p->ymin=$this->ymin;
			$p->SetWidth($this->width/$this->nbrplots);
			$xscale->off = $tmp+$i*round($xscale->ticks->major_step*$xscale->scale_factor*$this->width/$this->nbrplots);
			$p->Stroke($img,$xscale,$yscale);
			++$i;
		}
		$xscale->off=$tmp;
	}
} // Class

//===================================================
// CLASS AccBarPlot
// Description: 
//===================================================
class AccBarPlot extends BarPlot {
	var $plots=null,$nbrplots=0,$numpoints=0;
//---------------
// CONSTRUCTOR
	function AccBarPlot($plots) {
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
		return array(0,$this->ymin);	// Must be adjusted for log plots
	}

	// Method description
	function Stroke(&$img,&$xscale,&$yscale) {
		$img->SetLineWeight($this->weight);
		for($i=0; $i<$this->numpoints-1; $i++) {
			$accy=0; 
			for($j=0; $j<$this->nbrplots; ++$j ) {				
				$img->SetColor($this->plots[$j]->color);
				$yt=$yscale->Translate($this->plots[$j]->coords[0][$i]+$accy);
				$accyt=$yscale->Translate($accy);
				$xt=$xscale->Translate($i+1);
				$abswidth=round($this->width*$xscale->scale_factor,0);
				$pts=array($xt,$accyt,$xt,$yt,$xt+$abswidth,$yt,$xt+$abswidth,$accyt);
				if( $this->plots[$j]->fill_color ) {
					$img->SetColor($this->plots[$j]->fill_color);
					$img->FilledPolygon($pts,4);
					$img->SetColor($this->plots[$j]->color);
				}
				$accy+=$this->plots[$j]->coords[0][$i];
				$img->Polygon($pts,4);
			}
		}
		return true;
	}
} // Class

/* EOF */
?>