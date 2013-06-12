<?php
/*=======================================================================
// File: 				JPGRAPH_PIE.PHP
// Description: 		Pie plot extension for JpGraph
// Created: 			2001-02-14
//	Last edit:			29/04/01 12:41
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS PiePlot
// Description: 
//===================================================
class PiePlot {
	var $posx=0.5,$posy=0.5;
	var $radius=0.3;
	var $explode_slice=-1;
	var $labels, $legends=null;
	var $data=null;
	var $title;
	var $startangle=0;
	var $weight=1, $color="black";
	var $font_family=FONT1,$font_style=FS_NORMAL,$font_size=12,$font_color="black";
	var $legend_margin=6,$show_labels=true;
	var $precision=1,$show_psign=true;
	var $themearr=array(
		"earth" 	=> array(10,34,40,45,46,62,63,134,74,77,120,136,141,168,180,209,218,346,395,89,430),
		"pastel" => array(27,38,42,58,66,79,105,110,128,147,152,230,236,240,331,337,405,415),
		"water"  => array(8,370,10,40,335,56,213,237,268,14,326,387,24,388),
		"sand"   => array(27,168,34,170,19,50,65,72,131,209,46,393));
	var $theme="earth",$colors=array();
	var $setslicecolors=array();
	
//---------------
// CONSTRUCTOR
	function PiePlot(&$data) {
		$this->data = $data;
		$this->title = new Text("");
		$this->title->SetFont(FONT1,FS_BOLD);
	}

//---------------
// PUBLIC METHODS	
	function SetCenter($x,$y=0.5) {
		$this->posx = $x;
		$this->posy = $y;
	}
	
	function SetTheme($t) {
		if( in_array($t,array_keys($this->themearr)) )
			$this->theme = $t;
		else
			die("JpGraph Error: Unknown theme: $t");
	}
	
	function ExplodeSlice($e) {
		$this->explode_slice=$e;
	}
	
	function SetSliceColors($c) {
		$this->setslicecolors = $c;
	}
	
	function SetStartAngle($a) {
		assert($a>=0 && $a<2*M_PI);
		$this->startangle = $a;
	}
	
	function SetFont($family,$style=FS_NORMAL,$size=12) {
		$this->font_family=$family;
		$this->font_style=$style;
		$this->font_size=$size;
	}
	
	// Size in percentage
	function SetSize($size) {
		assert($size>0 && $size<=0.5);
		$this->radius = $size;
	}
	
	function SetFontColor($color) {
		$this->font_color = $color;
	}
	
	function SetLegends($l) {
		$this->legends = $l;
	}
	
	function HideLabels($f=true) {
		$this->show_labels = !$f;
	}
	
	function Legend(&$graph) {
		$colors = array_keys($graph->img->rgb->rgb_table);
   	sort($colors);	
   	$ta=$this->themearr[$this->theme];	
   	
   	if( $this->setslicecolors==null ) 
   		$numcolors=count($ta);
   	else
   		$numcolors=count($this->setslicecolors);
		
		$i=0;
		if( count($this->legends)>0 ) {
			foreach( $this->legends as $l ) {
				if( $this->setslicecolors==null ) 
					$graph->legend->Add($l,$colors[$ta[$i%$numcolors]]);
				else
					$graph->legend->Add($l,$this->setslicecolors[$i%$numcolors]);
				++$i;
				if( $i==count($this->data) ) return;
			}
		}
	}
	
	function SetPrecision($p,$psign=true) {
		$this->precision = $p;
		$this->show_psign=$psign;
	}
	
	function Stroke(&$img) {
		
		$colors = array_keys($img->rgb->rgb_table);
   	sort($colors);	
   	$ta=$this->themearr[$this->theme];	
   	
   	if( $this->setslicecolors==null ) 
   		$numcolors=count($ta);
   	else
   		$numcolors=count($this->setslicecolors);
   	
		// Draw the slices
		$sum=0;
		foreach($this->data as $d)
			$sum += $d;
		
		// Format the titles for each slice
		for( $i=0; $i<count($this->data); ++$i) {
			$l = round(100*$this->data[$i]/$sum,$this->precision);
			$l = sprintf("%01.".$this->precision."f",$l);
			if( $this->show_psign ) $l .= "%";
			$this->labels[$i]=$l;
		}
		
		// Set up the pic-circle
		$radius = floor($this->radius*min($img->width,$img->height));
		$xc = $this->posx*$img->width;
		$yc = $this->posy*$img->height;

		// Draw the first slice first line
		$img->SetColor($this->color);			
		$img->SetLineWeight($this->weight);
		$a = $this->startangle;
		$x = round(cos($a)*$radius);
		$y = round(sin($a)*$radius);
		$img->Line($xc,$yc,$xc+$x,$yc-$y);		

		
		if( $this->explode_slice>=0 ) {
			if( $this->explode_slice>0 )
				$p = $this->explode_slice-1;
			else 
				$p = count($this->data)-1;
				
			$acc=0;
			for($i=0; $i<$this->explode_slice; ++$i)
				$acc += $this->data[$i];
				
			$start = 360-($acc/$sum)*360;
			$end = 360-(($this->data[$this->explode_slice]/$sum)*360+($acc/$sum)*360);
			
			$img->Arc($xc,$yc,2*$radius,2*$radius,$start,$end);
		}
		else
			$img->Circle($xc,$yc,$radius);
		
		for($i=0; $i<count($this->data); ++$i) {
			$img->SetColor($this->color);	
			$d = $this->data[$i];
			$la = $a + M_PI*$d/$sum;
			$old_a = $a;
			$a += 2*M_PI*$d/$sum;
			$x = round(cos($a)*$radius);
			$y = round(sin($a)*$radius);
			
			// Don't stroke last line since this is the same as the first
			// line drawn but due to rounding error it might be just a 
			// tad pixel off and may not look as good.
			if( $i<count($this->data)-1)
				$img->Line($xc,$yc,$xc+$x,$yc-$y);
			
			if( $this->setslicecolors==null )
				$slicecolor=$colors[$ta[$i%$numcolors]];
			else
				$slicecolor=$this->setslicecolors[$i%$numcolors];
				
			if( $i == $this->explode_slice ) {
				$this->explode_slice($img,$slicecolor,$this->labels[$i],$xc,$yc,$radius,$old_a,$a);
			}
			else {
				if( $this->show_labels ) 
					$this->StrokeLabels($this->labels[$i],$img,$xc,$yc,$la,$radius);			
				$img->SetColor($slicecolor);
				$xf = cos($la)*$radius/2;
				$yf = sin($la)*$radius/2;			
				$img->Fill($xf+$xc,$yc-$yf); 
			}
		}	
		
		// Adjust title position
		$this->title->Pos($xc,$yc-$img->GetFontHeight()-$radius,"center","bottom");
		$this->title->Stroke($img);
		
	}

//---------------
// PRIVATE METHODS	
	function explode_slice($img,$color,$label,$xc,$yc,$r,$old_a,$a) {
		$extract=0.3;
		$am = abs($a-$old_a)/2+$old_a;
		$xc = cos($am)*$r*$extract+$xc;
		$yc = $yc - sin($am)*$r*$extract;
		
		$x1 = cos($old_a)*$r + $xc;
		$x2 = cos($a)*$r + $xc;
		$y1 = $yc - sin($old_a)*$r;
		$y2 = $yc - sin($a)*$r;

		$xf=cos($am)*$r*0.5+$xc;
		$yf=$yc-sin($am)*$r*0.5;
						
		$old_a *= 360/(2*M_PI);
		$a *= 360/(2*M_PI);
		$start = 360-$a;
		$end = $start + abs($old_a-$a);
		
		$img->SetColor($this->color);
		$img->Arc($xc,$yc,$r*2,$r*2,$start,$end);
		$img->Line($xc,$yc,$x1,$y1);
		$img->Line($xc,$yc,$x2,$y2);
		
		$img->SetColor($color);
		$img->Fill($xf,$yf);
		
		$this->StrokeLabels($label,$img,$xc,$yc,$am,$r);					
	}
	

	function StrokeLabels($label,$img,$xc,$yc,$a,$r) {
		// Draw title of this axis
		
		$img->SetFont($this->font_family,$this->font_style,$this->font_size);
		$img->SetColor($this->font_color);
		$img->SetTextAlign("left","top");
		$marg=6;
		$r += $img->GetFontHeight()/2;
		$xt=round($r*cos($a)+$xc);
		$yt=round($yc-$r*sin($a));

		// Position the axis title. 
		// dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
		// that intersects with the extension of the corresponding axis. The code looks a little
		// bit messy but this is really the only way of having a reasonable position of the
		// axis titles.
		$h=$img->GetTextHeight($label);
		$w=$img->GetTextWidth($label);
		while( $a > 2*M_PI ) $a -= 2*M_PI;
		if( $a>=7*M_PI/4 || $a <= M_PI/4 ) $dx=0;
		if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dx=($a-M_PI/4)*2/M_PI; 
		if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dx=1;
		if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dx=(1-($a-M_PI*5/4)*2/M_PI);
		
		if( $a>=7*M_PI/4 ) $dy=(($a-M_PI)-3*M_PI/4)*2/M_PI;
		if( $a<=M_PI/4 ) $dy=(1-$a*2/M_PI);
		if( $a>=M_PI/4 && $a <= 3*M_PI/4 ) $dy=1;
		if( $a>=3*M_PI/4 && $a <= 5*M_PI/4 ) $dy=(1-($a-3*M_PI/4)*2/M_PI);
		if( $a>=5*M_PI/4 && $a <= 7*M_PI/4 ) $dy=0;
		
		$img->StrokeText($xt-$dx*$w,$yt-$dy*$h,$label);		
	}	
} // Class



//===================================================
// CLASS PieGraph
// Description: 
//===================================================
class PieGraph extends Graph {
	var $posx, $posy, $radius;		
	var $legends=array();	
	var $plots=array();
//---------------
// CONSTRUCTOR
	function PieGraph($width=300,$height=200,$cachedName="") {
		$this->Graph($width,$height,$cachedName,0);
		$this->posx=$width/2;
		$this->posy=$height/2;
		$this->SetColor(array(255,255,255));		
	}

//---------------
// PUBLIC METHODS	
	function Add(&$pie) {
		$this->plots[] = $pie;
	}
	
	function SetColor($c) {
		$this->SetMarginColor($c);
	}

	// Method description
	function Stroke() {
		
		$this->StrokeFrame();		
		
		foreach($this->plots as $p) 
			$p->Stroke($this->img);
		
		foreach( $this->plots as $p)
			$p->Legend($this);	
		
		$this->legend->Stroke($this->img);
		$this->title->Center($this->img->left_margin,$this->img->width-$this->img->right_margin,5);
		$this->title->Stroke($this->img);		

		// Stroke texts
		if( $this->texts != null )
			foreach( $this->texts as $t) {
				$t->x *= $this->img->width;
				$t->y *= $this->img->height;
				$t->Stroke($this->img);
			}
		
		// Finally output the image
		$this->cache->PutAndStream($this->img,$this->cache_name);					
	}
} // Class

/* EOF */
?>
