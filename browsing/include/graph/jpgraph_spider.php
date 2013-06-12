<?php
/*=======================================================================
// File: 				JPGRAPH_SPIDER.PHP
// Description: 		Spider plot extension for JpGraph
// Created: 			2001-02-04
//	Last edit:			29/04/01 12:50
// Author:				Johan Persson (johanp@aditus.nu)
// Ver:					1.2.2
//
// License:				This code is released under GPL 2.0
//
//========================================================================
*/

//===================================================
// CLASS FontProp
// Description: Utility class to enable the use
// of a "title" instance variable for the spider axis.
// This clas is only used to hold a font and a color
// property for the axis title.
//===================================================
class FontProp {
	var $font_family=FONT1, $font_style=FS_NORMAL,$font_size=14,$font_color=array(0,0,0);	
	function SetFont($family,$style=FS_NORMAL,$size=14) {
		$this->font_family = $family;
		$this->font_style = $style;
		$this->font_size = $size;
	}
	
	function SetColor($c) {
		$this->font_color = $c;
	}
}
	

//===================================================
// CLASS SpiderAxis
// Description: Implements axis for the spider graph
//===================================================
class SpiderAxis extends Axis {
	var $title_color="navy";
	var $title=null;
//---------------
// CONSTRUCTOR
	function SpiderAxis(&$img,&$aScale,$color=array(0,0,0)) {
		parent::Axis($img,$aScale,$color);
		$this->len=$img->plotheight;
		$this->font_size = FONT1;
		$this->title = new FontProp();
		$this->color = array(0,0,0);
	}
//---------------
// PUBLIC METHODS	
	function SetTickLabels($l) {
		$this->ticks_label = $l;
	}
	
	function Stroke($pos,$a,&$grid,$title,$lf) {
		$this->img->SetColor($this->color);
		$x=round($this->scale->world_abs_size*cos($a)+$this->scale->scale_abs[0]);
		$y=round($pos-$this->scale->world_abs_size*sin($a));
		
		// Draw axis
		$this->img->SetColor($this->color);
		$this->img->SetLineWeight($this->weight);
		if( !$this->hide )
			$this->img->Line($this->scale->scale_abs[0],$pos,$x,$y);
		
		// Prepare to draw ticks
		$maj_step_abs = abs($this->scale->scale_factor*$this->scale->ticks->major_step);	
		$min_step_abs = abs($this->scale->scale_factor*$this->scale->ticks->minor_step);	
		$nbrmaj = floor(($this->scale->world_abs_size)/$maj_step_abs);
		$nbrmin = floor(($this->scale->world_abs_size)/$min_step_abs);
		$skip = round($nbrmin/$nbrmaj); // Don't draw minor ontop of major

		// Draw major ticks
		$ticklen2=4;
		$dx=round(sin($a)*$ticklen2);
		$dy=round(cos($a)*$ticklen2);
		$label=$this->scale->scale[0]+$this->scale->ticks->major_step;

		for($i=1; $i<=$nbrmaj; ++$i) {
			$xt=round($i*$maj_step_abs*cos($a))+$this->scale->scale_abs[0];
			$yt=$pos-round($i*$maj_step_abs*sin($a));
			$majlabel[]=$label;
			$label += $this->scale->ticks->major_step;
			$grid[]=$xt;
			$grid[]=$yt;
			if( $lf ) {
				$majpos[($i-1)*2]=$xt+2*$dx;
				$majpos[($i-1)*2+1]=$yt-$this->img->GetFontheight()/2;				
			}
			if( !$this->scale->ticks->supress_tickmarks && !$this->hide)			
				$this->img->Line($xt+$dx,$yt+$dy,$xt-$dx,$yt-$dy);
		}

		// Draw minor ticks
		$ticklen2=3;
		$dx=round(sin($a)*$ticklen2);
		$dy=round(cos($a)*$ticklen2);
		if( !$this->scale->ticks->supress_tickmarks && !$this->scale->ticks->supress_minor_tickmarks)	{						
			for($i=1; $i<=$nbrmin; ++$i) {
				if( ($i % $skip) == 0 ) continue;
				$xt=round($i*$min_step_abs*cos($a))+$this->scale->scale_abs[0];
				$yt=$pos-round($i*$min_step_abs*sin($a));
				if( !$this->hide )
					$this->img->Line($xt+$dx,$yt+$dy,$xt-$dx,$yt-$dy);
			}
		}
		
		// Draw labels
		if( $lf && !$this->hide ) {
			$this->img->SetFont($this->font_family,$this->font_style,$this->font_size);	
			$this->img->SetTextAlign("left","top");
			$this->img->SetColor($this->color);
			for($i=0; $i<count($majpos)/2; ++$i) {
				if( $this->ticks_label != null )
					$this->img->StrokeText($majpos[$i*2],$majpos[$i*2+1],$this->ticks_label[$i]);
				else
					$this->img->StrokeText($majpos[$i*2],$majpos[$i*2+1],$majlabel[$i]);
			}
		}
		
		// Draw title of this axis
		$this->img->SetFont($this->title->font_family,$this->title->font_style,$this->title->font_size);
		$this->img->SetColor($this->title->font_color);
		$marg=6;
		$xt=round(($this->scale->world_abs_size+$marg)*cos($a)+$this->scale->scale_abs[0]);
		$yt=round($pos-($this->scale->world_abs_size+$marg)*sin($a));

		// Position the axis title. 
		// dx, dy is the offset from the top left corner of the bounding box that sorrounds the text
		// that intersects with the extension of the corresponding axis. The code looks a little
		// bit messy but this is really the only way of having a reasonable position of the
		// axis titles.
		$h=$this->img->GetFontHeight();
		$w=$this->img->GetTextWidth($title);
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
		
		if( !$this->hide )
			$this->img->StrokeText($xt-$dx*$w,$yt-$dy*$h,$title);
	}
	
} // Class


//===================================================
// CLASS SpiderGrid
// Description: Draws grid for the spider graph
//===================================================
class SpiderGrid extends Grid {
//------------
// CONSTRUCTOR
	function SpiderGrid() {
	}

//----------------
// PRIVATE METHODS	
	function Stroke(&$img,&$grid) {
		if( !$this->show ) return;
		$nbrticks = count($grid[0])/2;
		$nbrpnts = count($grid);
		$img->SetColor($this->grid_color);
		$img->SetLineWeight($this->weight);
		for($i=0; $i<$nbrticks; ++$i) {
			for($j=0; $j<$nbrpnts; ++$j) {
				$pnts[$j*2]=$grid[$j][$i*2];
				$pnts[$j*2+1]=$grid[$j][$i*2+1];
			}
			for($k=0; $k<$nbrpnts; ++$k ){
				$l=($k+1)%$nbrpnts;
				if( $this->type == "solid" )
					$img->Line($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1]);
				elseif( $this->type == "dotted" )
					$img->DashedLine($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1],1,6);
				elseif( $this->type == "dashed" )
					$img->DashedLine($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1],2,4);
				elseif( $this->type == "longdashed" )
					$img->DashedLine($pnts[$k*2],$pnts[$k*2+1],$pnts[$l*2],$pnts[$l*2+1],8,6);
			}
			$pnts=array();
		}
	}
} // Class


//===================================================
// CLASS SpiderPlot
// Description: Plot a spiderplot
//===================================================
class SpiderPlot {
	var $data=array();
	var $fill=true, $fill_color=array(200,170,180);
	var $color=array(0,0,0);
	var $legend="";
	var $weight=1;
//---------------
// CONSTRUCTOR
	function SpiderPlot($data) {
		$this->data = $data;
	}

//---------------
// PUBLIC METHODS	
	function Min() {
		return Min($this->data);
	}
	
	function Max() {
		return Max($this->data);
	}
	
	function SetLegend($legend) {
		$this->legend=$legend;
	}
	
	function SetFill($f=true) {
		$this->fill = $f;
	}
	
	function SetLineWeight($w) {
		$this->weight=$w;
	}
		
	function SetColor($color,$fill_color=array(160,170,180)) {
		$this->color = $color;
		$this->fill_color = $fill_color;
	}
	
	function Stroke(&$img, $pos, &$scale, $startangle) {
		$nbrpnts = count($this->data);
		$astep=2*M_PI/$nbrpnts;		
		$a=$startangle;
		
		// Rotate each point to the correct axis-angle
		for($i=0; $i<$nbrpnts; ++$i) {
			$c=$this->data[$i];
			$x=round(($c-$scale->scale[0])*$scale->scale_factor*cos($a)+$scale->scale_abs[0]);
			$y=round($pos-($c-$scale->scale[0])*$scale->scale_factor*sin($a));		
			$pnts[$i*2]=$x;
			$pnts[$i*2+1]=$y;
			$a += $astep;
		}
		if( $this->fill ) {
			$img->SetColor($this->fill_color);
			$img->FilledPolygon($pnts);
		}
		$img->SetLineWeight($this->weight);
		$img->SetColor($this->color);
		$img->Polygon($pnts);
	}
	
//---------------
// PRIVATE METHODS
	function GetCount() {
		return count($this->data);
	}
	
	function Legend(&$graph) {
		if( $this->legend=="" ) return;
		if( $this->fill )
			$graph->legend->Add($this->legend,$this->fill_color);
		else
			$graph->legend->Add($this->legend,$this->color);	
	}
	
} // Class

//===================================================
// CLASS SpiderGraph
// Description: Main container for a spider graph
//===================================================
class SpiderGraph extends Graph {
	var $posx;
	var $posy;
	var $len;		
	var $plots=null, $axis_title=null;
	var $grid,$axis;
//---------------
// CONSTRUCTOR
	function SpiderGraph($width=300,$height=200,$cachedName="") {
		$this->Graph($width,$height,$cachedName,0);
		$this->yscale = new LinearScale(1,1);
		$this->yscale->ticks->SupressMinorTickMarks();
		$this->axis = new SpiderAxis($this->img,$this->yscale);
		$this->grid = new SpiderGrid();
		$this->posx=$width/2;
		$this->posy=$height/2;
		$this->len=min($width,$height)*0.3;
		$this->SetColor(array(255,255,255));
		$this->SetTickDensity(TICKD_NORMAL);
	}

//---------------
// PUBLIC METHODS
	function SupressTickMarks($f=true) {
		$this->axis->scale->ticks->SupressTickMarks($f);
	}

	function SetPlotSize($s) {
		$this->len=min($this->img->width,$this->img->height)*$s/2;
	}

	function SetTickDensity($densy=TICKD_NORMAL) {
		$this->ytick_factor=25;		
		switch( $densy ) {
			case TICKD_DENSE:
				$this->ytick_factor=12;			
				break;
			case TICKD_NORMAL:
				$this->ytick_factor=25;			
				break;
			case TICKD_SPARSE:
				$this->ytick_factor=40;			
				break;
			case TICKD_VERYSPARSE:
				$this->ytick_factor=70;			
				break;		
			default:
				die("Unsupported Tick density: $densy");
		}
	}

	function SetCenter($px,$py=0.5) {
		assert($px > 0 && $py > 0 );
		$this->posx=$this->img->width*$px;
		$this->posy=$this->img->height*$py;
	}

	function SetColor($c) {
		$this->SetMarginColor($c);
	}
			
	function SetTitles($title) {
		$this->axis_title = $title;
	}

	function Add(&$splot) {
		$this->plots[]=$splot;
	}
	
	function GetPlotsYMinMax() {
		$min=$this->plots[0]->Min();
		$max=$this->plots[0]->Max();
		foreach( $this->plots as $p ) {
			$max=max($max,$p->Max());
			$min=min($min,$p->Min());
		}
		return array($min,$max);
	}	

	// Method description
	function Stroke() {
		// Set Y-scale
		if( !$this->yscale->IsSpecified() && count($this->plots)>0 ) {
			list($min,$max) = $this->GetPlotsYMinMax();
			$this->yscale->AutoScale($this->img,0,$max,$this->len/$this->ytick_factor);
		}
		$this->yscale->SetConstants($this->posx,$this->len);
		$nbrpnts=$this->plots[0]->GetCount();
		if( $this->axis_title==null ) {
			for($i=0; $i<$nbrpnts; ++$i ) 
				$this->axis_title[$i] = $i+1;
		}
		elseif(count($this->axis_title)<$nbrpnts) 
			die("JpGraph: Number of titles does not match number of points in plot.");
		foreach( $this->plots as $p )
			if( $nbrpnts != $p->GetCount() )
				die("JpGraph: Each spider plot must have the same number of data points.");

		$this->StrokeFrame();
		$astep=2*M_PI/$nbrpnts;

		// Prepare legends
		foreach( $this->plots as $p)
			$p->Legend($this);
		$this->legend->Stroke($this->img);			
		
		// Plot points
		$a=M_PI/2;
		foreach( $this->plots as $p )
			$p->Stroke($this->img, $this->posy, $this->yscale, $a);
		
		// Draw axis and grid
		for( $i=0,$a=M_PI/2; $i<$nbrpnts; ++$i, $a+=$astep ) {
			$this->axis->Stroke($this->posy,$a,$grid[$i],$this->axis_title[$i],$i==0);
		}	
		$this->grid->Stroke($this->img,$grid);
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