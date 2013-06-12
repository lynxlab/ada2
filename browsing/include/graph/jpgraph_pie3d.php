<?php
/*=======================================================================
// File: 				JPGRAPH_PIE3D.PHP
// Description: 		3D Pie plot extension for JpGraph
// Created: 			2001-03-24
//	Last edit:			29/04/01 12:49
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS PiePlot3D
// Description: Plots a 3D pie with a specified projection 
// angle between 20 and 70 degrees.
//===================================================
class PiePlot3D {
	var $posx=0.5,$posy=0.5;
	var $radius=0.3;
	var $explode_slice=-1;
	var $labels, $legends=null,$labelmargin=0.30;
	var $data=null;
	var $title;
	var $angle=45;	// Deault projection angle for 3D
	var $startangle=0;
	var $weight=1, $color="black";
	var $font_family=FONT1,$font_style=FS_NORMAL,$font_size=12,$font_color="black";
	var $legend_margin=6,$show_labels=true;
	var $precision=1,$show_psign=true;
	var $themearr=array(
		"earth" 	=> array(120,10,209,45,134,74,77,136,34,62,141,46,168,180,40,346,395,89,63,430,218),
		"pastel" => array(27,38,42,58,66,79,105,110,128,147,152,230,236,240,331,337,405,415),
		"water"  => array(387,8,370,213,10,237,24,40,326,335,299),
		"sand"   => array(19,34,46,50,65,72,131,168,170,209,393));
	var $theme="earth";
	var $setslicecolors=array();
	var $labelhintcolor="red",$showlabelhint=true;
	
//---------------
// CONSTRUCTOR
	function PiePlot3d(&$data) {
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
	
	// Specify projection angle for 3D in degrees
	// Must be between 20 and 70 degrees
	function SetAngle($a) {
		if( $a<30 || $a>70 )
			die("JpGraph: 3D Pie projection angle must be between 30 and 70 degrees.");
		else
			$this->angle = $a;
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
	
	function SetLabelMargin($m) {
		assert($m>0 && $m<1);
		$this->labelmargin=$m;
	}
	
	function ShowLabelHint($f=true) {
		$this->showlabelhint=$f;
	}
	
	function SetLabelHintColor($c) {
		$this->labelhintcolor=$c;
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
		
		// Set up the pie-circle
		$thick=0.16-($this->angle-20)/60*0.07;
	
		$width = floor(2.0*$this->radius*min($img->width,$img->height));
		$height = ($this->angle/90.0)*$width;
		$xc = $this->posx*$img->width;
		$yc = $this->posy*$img->height;

		$img->SetColor($this->color);			
		$img->Ellipse($xc,$yc,$width,$height);
		$img->Arc($xc,$yc+$width*$thick,$width,$height,0,180);
		$img->Line($xc+$width/2,$yc,$xc+$width/2,$yc+$width*$thick);
		$img->Line($xc-$width/2,$yc,$xc-$width/2,$yc+$width*$thick);
		
		// Draw the first slice first line
		$img->SetColor($this->color);			
		$img->SetLineWeight($this->weight);
		$a = $this->startangle;

		$xp = $width*cos($a)/2+$xc;
		$yp = $yc-$height*sin($a)/2;
		$img->Line($xc,$yc,$xp,$yp);

		for($i=0; $i<count($this->data); ++$i) {
			$img->SetColor($this->color);	
			$d = $this->data[$i];
			$la = $a + M_PI*$d/$sum;
			$old_a = $a;
			$a += 2*M_PI*$d/$sum;

			$xp = $width*cos($a)/2+$xc;
			$yp = $yc-$height*sin($a)/2;

			if( $i<count($this->data)-1)
				$img->Line($xc,$yc,$xp,$yp);
			if( $a > M_PI && $a < 0.999*2*M_PI )
				$img->Line($xp,$yp,$xp,$yp+$width*$thick-1);

			
			if( $this->setslicecolors==null )
				$slicecolor=$colors[$ta[$i%$numcolors]];
			else
				$slicecolor=$this->setslicecolors[$i%$numcolors];
				
			if( $i == $this->explode_slice ) {
				$this->explode_slice($img,$slicecolor,$this->labels[$i],$xc,$yc,$radius,$old_a,$a);
			}
			else {
				if( $this->show_labels ) {
					$margin = 1 + $this->labelmargin;
					$xp = $width*cos($la)/2*$margin;
					$yp = $height*sin($la)/2*$margin;
									
					if( ($la >= 0 && $la <= M_PI) || $la>2*M_PI*0.98 ) {
						$this->StrokeLabels($this->labels[$i],$img,$la,$xc+$xp,$yc-$yp);	
						if( $this->showlabelhint ) {
							$img->SetColor($this->labelhintcolor);
							$img->Line($xc+$xp/$margin,$yc-$yp/$margin,$xc+$xp,$yc-$yp);
						}
					}
					else {
						$this->StrokeLabels($this->labels[$i],$img,$la,$xc+$xp,$yc-$yp+$width*$thick);	
						if( $this->showlabelhint ) {
							$img->SetColor($this->labelhintcolor);
							$img->Line($xc+$xp/$margin,$yc-$yp/$margin+$width*$thick,$xc+$xp,$yc-$yp+$width*$thick);
						}
					}					

				}
				$img->SetColor($slicecolor);
				$xp = $width*cos($la)/3+$xc;
				$yp = $yc-$height*sin($la)/3;
				$img->Fill($xp,$yp); 

				// Make the edge color 30% darker
				$tmp=$img->rgb->Color($slicecolor);
				$tmp[0] *= 0.7;
				$tmp[1] *= 0.7;
				$tmp[2] *= 0.7;
				$img->SetColor($tmp);
				
				if( $a > 2*M_PI*1.01 && $old_a < 2*M_PI*0.99) {
					$xp = 2+$width*cos($old_a)/2+$xc;
					if( $xp >= $xc+$width/2 ) continue;
					$yp = $yc-$height*sin($old_a)/2;
					//$img->Line($xc,$yc,$xp,$yp);
					$img->Fill($xp,$yp+2); 
				}
				elseif( $old_a >= M_PI && $a <= 2*M_PI*1.01) {
					$xp = $width*cos($la)/2+$xc;
					$yp = $yc-$height*sin($la)/2;
					//$img->Line($xc,$yc,$xp,$yp+$thick*$width/2.0);
					$img->Fill($xp,$yp+$thick*$width/2.0); 
				}
				elseif( $old_a < M_PI && $a > M_PI*1.03 ) {
					$xp = $width*cos($a)/1.97+$xc;
					if( $xp <= $xc-$width ) continue;
					$yp = $yc-$height*sin($a)/1.97;
					//$img->Line($xc,$yc,$xp,$yp);
					$img->Fill($xp,$yp);
				}
			}
		}	
		
		// Adjust title position
		$this->title->Pos($xc,$yc-$img->GetFontHeight()-$this->radius,"center","bottom");
		$this->title->Stroke($img);

		// Draw the pie ellipse one more time since the filling might have
		// written partly on the lines due to the filling in the edges.
		$img->SetColor($this->color);			
		$img->Ellipse($xc,$yc,$width,$height);
		$img->Arc($xc,$yc+$width*$thick,$width,$height,0,180);
		$img->Line($xc+$width/2,$yc,$xc+$width/2,$yc+$width*$thick);
		$img->Line($xc-$width/2,$yc,$xc-$width/2,$yc+$width*$thick);
		
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
	

	function StrokeLabels($label,$img,$a,$xp,$yp) {
		// Draw title of this axis
		
		$img->SetFont($this->font_family,$this->font_style,$this->font_size);
		$img->SetColor($this->font_color);
		$img->SetTextAlign("left","top");
		$marg=6;
		//$r = $img->GetFontHeight()/2;

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
		
		$img->StrokeText($xp-$dx*$w,$yp-$dy*$h,$label);		
	}	
} // Class

/* EOF */
?>
