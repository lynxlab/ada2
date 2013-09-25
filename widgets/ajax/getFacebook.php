<?php
/*
 * senza nome.php
 * 
 * Copyright 2013 stefano <steve@lynxlab.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */


// $newsmsg = "Hi there, I'm the widget content loaded from ". __FILE__;

// Without this "ini_set" Facebook's RSS url is all screwy for reading!
// This is the most essential line, don't forget it.
ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');

// This URL is the URL to the Facebook Page's RSS feed.
// Go to the page's profile, and on the left-hand side click "Get Updates vis RSS"
$rssUrl = "http://www.facebook.com/feeds/page.php?format=rss20&id=209676172418229";
    $xml = simplexml_load_file($rssUrl); // Load the XML file

// This creates an array called "entry" that puts each <item> in FB's
// XML format into the array
$entry = $xml->channel->item;

// This is just a blank string I create to add to as I loop through our
// FB feed. Feel free to format however you want, or do whatever else
	// you want with the data.
$returnMarkup = '';

// Now we'll loop through are array. I just have it going up to 3 items
// for this example.
for ($i = 0; $i < 3; $i++) {
$returnMarkup .= "<h3>".$entry[$i]->title."</h3>"; // Title of the update
		$returnMarkup .= "<p>".$entry[$i]->link."</p>"; // Link to the update
		$returnMarkup .= "<p>".$entry[$i]->description."</p>"; // Full content
		$returnMarkup .= "<p>".$entry[$i]->pubDate."</p>"; // The date published
$returnMarkup .= "<p>".$entry[$i]->author."</p>"; // The author (Page Title)
}

// Finally, we return (or in this case echo) our formatted string with our
// Facebook page feed data in it!

switch ($widget_mode) {
case ADA_WIDGET_SYNC_MODE:
	return $returnMarkup;
	break;
case ADA_WIDGET_ASYNC_MODE:
default:
	echo $returnMarkup;

}
   

?>

