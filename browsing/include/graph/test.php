<?php
include ("jpgraph.php");
include ("jpgraph_pie.php");

// Some data
$data = array(38,62);

// Create the Pie Graph. Note you may cach this by adding the
// ache file name as PieGraph(300,300,"SomCacheFileName")
$graph = new PieGraph(300,200);
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Example 1 Pie plot");
$graph->title->SetFont(FONT1_BOLD);

// Create graph
$p1 = new PiePlot($data);
$p1->SetLegends(array("Jan","Feb"));

$graph->Add($p1);

// .. and finally stroke it
$graph->Stroke();

?>