<?php
/*=======================================================================
// File: 				JPGRAPH_LOG.PHP
// Description: 		Log scale plot extension for JpGraph
// Created: 			2001-01-08
//	Last edit:			29/04/01 12:49
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS LogScale
// Description: Logarithmic scale between world and screen
//===================================================
class LogScale extends LinearScale {
//---------------
// CONSTRUCTOR
	function LogScale($min,$max,$type="y") {
		$this->LinearScale($min,$max,$type);
		$this->ticks = new LogTicks();
	}

//----------------
// PUBLIC METHODS	
	function	Translate($a) {
		if( $a==0 ) $a=1;
		$a=log10($a);
		return floor($this->off + ($a*1.0 - $this->scale[0]) * $this->scale_factor); 
	}
	
	function GetMinVal() {
		return pow(10,$this->scale[0]);
	}
	
	function GetMaxVal() {
		return pow(10,$this->scale[1]);
	}
	
	function AutoScale(&$img,$min,$max,$maxsteps) {
		if( $min==0 ) $min=1;
		assert($max>0);		
		$smin = floor(log10($min));
		$smax = ceil(log10($max));
		// Logscale doesn't use step so we just return 0:s to have the same signature
		// as the linear scale.
		$this->Update($img,$smin,$smax);					
		$this->ticks->Set(0,0);			
		//return array(0,0);
	}
//---------------
// PRIVATE METHODS	
} // Class

//===================================================
// CLASS LogTicks
// Description: 
//===================================================
class LogTicks extends Ticks{
//---------------
// CONSTRUCTOR
	function LogTicks() {
	}
//---------------
// PUBLIC METHODS	
	function IsSpecified() {
		return true;
	}
	
	// Method description
	function Stroke(&$img,&$scale,$pos) {
		$start = $scale->GetMinVal();
		$limit = $scale->GetMaxVal();
		$nextMajor = 10*$start;
		$step = $nextMajor / 10.0;
		
		if( $scale->type == "y" ) {
			$a=$pos + $this->direction*$this->GetMinTickAbsSize();
			$a2=$pos + $this->direction*$this->GetMajTickAbsSize();	
			$count=1; 
			$this->maj_ticks_pos[0]=$scale->Translate($start);
			if( $this->supress_first )
				$this->maj_ticks_label[0]="";
			else
				$this->maj_ticks_label[0]=$start;	
			$i=1;
			for($y=$start; $y<=$limit; $y+=$step,++$count  ) {
				$ys=$scale->Translate($y);	
				$this->ticks_pos[]=$ys;
				if( $count % 10 == 0 ) {
					$img->Line($pos,$ys,$a2,$ys);
					$this->maj_ticks_pos[$i]=$ys;
					$this->maj_ticks_label[$i]=$nextMajor;	
					++$i;						
					$nextMajor *= 10;
					$step *= 10;	
					$count=1; 				
				}
				else
					$img->Line($pos,$ys,$a,$ys);		
			}		
		}
		else {
			$a=$pos - $this->direction*$this->GetMinTickAbsSize();
			$a2=$pos - $this->direction*$this->GetMajTickAbsSize();	
			$count=1; 
			$this->maj_ticks_pos[0]=$scale->Translate($start);
			if( $this->supress_first )
				$this->maj_ticks_label[0]="";
			else
				$this->maj_ticks_label[0]=$start;	
			$i=1;			
			for($x=$start; $x<=$limit; $x+=$step,++$count  ) {
				$xs=$scale->Translate($x);	
				$this->ticks_pos[]=$xs;
				if( $count % 10 == 0 ) {
					$img->Line($xs,$pos,$xs,$a2);
					$this->maj_ticks_pos[$i]=$ys;
					$this->maj_ticks_label[$i]=$nextMajor;	
					++$i;								
					$nextMajor *= 10;
					$step *= 10;	
					$count=1; 				
				}
				else
					$img->Line($xs,$pos,$xs,$a);		
			}		
		}
		return true;
	}
} // Class
/* EOF */
?>