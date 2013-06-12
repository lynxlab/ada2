/**
 * Map_new.js - this files provides to draw map
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Massimo di Vita <mambo@lynxlab.com>
 * @copyright		Copyright (c) 2009-2010, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

// browser identification
var isFirefox = /Firefox/.test(navigator.userAgent) ? true : false;
var isChrome = /Chrome/.test(navigator.userAgent) ? true : false;
var isSafari = /Safari/.test(navigator.userAgent) ? true : false;
var isIE =  /MSIE/.test(navigator.userAgent) ? true : false;
var isOpera =  /Opera/.test(navigator.userAgent) ? true : false;
var isPhone =  /android/i.test(navigator.userAgent) || /iphone/i.test(navigator.userAgent)? true : false;


	
/* --------------------------------------------------------- *\

	  Function    : transform()
	  Description : rotate a given line whit a given angle.
	  Usage       : transform( line, deg)
	  Arguments   : line - a given line (HTMLDivElement),
	  		deg - angle ( rad )

\* ---------------------------------------------------------- */
function transform( line, deg){
	
	var rotDeg = deg/Math.PI*180;
	
	if( isFirefox ) line.style.MozTransform = "rotate("+rotDeg+"deg)";
	
	else if( isChrome || isSafari ) line.style.webkitTransform = "rotate("+rotDeg+"deg)"
	
	else if( isOpera ) line.style.OTransform = "rotate("+rotDeg+"deg)"
	
	else if( isIE ) line.style.filter =  "progid:DXImageTransform.Microsoft.Matrix(M11="+Math.cos(deg)+", M12="+(- Math.sin(deg))+", M21="+ Math.sin(deg)+", M22="+(Math.cos(deg))+",sizingMethod='auto expand')";
	
	else return;
	
}



/* --------------------------------------------------------- *\

	  Function    : setAllLinesFromNode()
	  Description : set all lines from a node
	  Usage       : setAllLinesFromNode( element )
	  Arguments   : element - the node who to add lines 
	  		(HTMLDivElement)

\* ---------------------------------------------------------- */
function setAllLinesFromNode( element ){
	
	for(var i = 0; i < element.linkArray.length; i++){
			
		var Node = element.linkArray[i].node
		
		var line = element.linkArray[i].line
		
		var X0 = parseInt(element.style.left.replace("px","") );
		
		var Y0 = parseInt( element.style.top.replace("px","") );
		
		var X1 = parseInt( Node.style.left.replace("px","") )
		
		var Y1 = parseInt( Node.style.top.replace("px","") )
		
		var elemWidth = element.style.width.replace("px","").replace("pt","");


		if( elemWidth == "" ) elemWidth = element.offsetWidth


		if( !isIE) setLine(line, X0, Y0, X1, Y1 )
		
		else  setLine(line, X0, Y0, X1, Y1 )


		var form_ex = $('form_map') ? true:false;

                if(!!form_ex) try{$("input_"+element.id).value = X0+","+Y0+","+elemWidth+",0";}catch(e){}
	
	}
	
	
	// if node has'nt links 	
	if( element.linkArray.length == 0 ){
		
		X0 = parseInt(element.style.left.replace("px","") );
		
		Y0 = parseInt( element.style.top.replace("px","") );
		
		elemWidth = element.style.width.replace("px","").replace("pt","");
		
		
		if( elemWidth == "" ) elemWidth = element.offsetWidth
		
		var form_ex = $('form_map') ? true:false;
		
		if(!!form_ex)	try{$("input_"+element.id).value = X0+","+Y0+","+elemWidth+",0";}catch(e){}
	
	}

}






/* --------------------------------------------------------- *\

	  Function    : setMapMinY()
	  Description : set dinamically the height of map
	  Usage       : setMapMinY()
	  Arguments   : 

\* ---------------------------------------------------------- */
function setMapMinY(){
	
	var thismap = $('map_content');
	
	var maxY = 0;
	
	for( var i = 0; i < $$(".newNodeMap").length; i++){
		
		var thisNode = $$(".newNodeMap")[i];
		
		var thisNodeHeight = parseInt(thisNode.style.top.replace("px",""))

		if( thisNodeHeight > maxY ) maxY = thisNodeHeight		
		
		thismap.style.height = (maxY +50 ) +"px"
	
	};

}







/* --------------------------------------------------------- *\

	  Function    : Node()
	  Description : Node Object
	  Usage       : new Node();
	  Arguments   : 

\* ---------------------------------------------------------- */
var Node = function( element ){
	
	// PROPERTIES
	element.parent = this;
	
	element.linkArray = new Array();
	
	element.linkFromArray = new Array();
	
	element.left = parseInt(element.style.left.replace("px",""))
	
	element.top = parseInt(element.style.top.replace("px",""))
	
	element.width = parseInt(element.style.width.replace("px",""))
	
	element.style.zIndex = 100;
	
	
	if(!isIE) element.height = parseInt(element.getHeight())
	
	else element.height = element.offsetHeight
	
	
	// defining the rectangle occupied from node
	element.rect = {
		el: element, 
		xFrom: element.left, 
		xTo: (element.left+element.width), 
		yFrom: element.top, 
		yTo: (element.top + element.height)
	};
	
	
	
	// METHODS	
	
	// verify if a element is in a rectangle
	element.isInRect = function( obj ){
		
		var xInRect = false
		
		var yInRect = false
		
		if( element.left >= obj.xFrom && element.left  <=  obj.xTo  ) xInRect = true
		
		if( (element.left + element.width ) >= obj.xFrom && (element.left+element.width)  <=  obj.xTo  ) xInRect = true
		
		if( element.top  >= obj.yFrom && element.top  <= obj.yTo  ) yInRect = true
		
		if( (element.top + element.height ) >= obj.yFrom && (element.top + element.height )  <=  obj.yTo ) yInRect = true
		
		
		if( !!xInRect && !!yInRect ) return true;
		
		return false;
	
	}
	
	
	// count links from a node
	element.link = function(){
	
		var linksDiv = element.getElementsByTagName("div");
		
		var thisLinks = linksDiv[linksDiv.length - 1]
		
		
		if(thisLinks.innerHTML =="") return;
		
		else element.links = new Array();
		
		
		var linksArray = thisLinks.innerHTML.split(",");
		
		for( var i = 0; i < linksArray.length; i++){
			
			element.links.push( linksArray[i] );
			
			element.linkTo( linksArray[i]);
			
		};
	}
	
	
	
	// create a links ( line ) from a node
	element.linkTo = function( nodeId ){
		
		var exist = $(nodeId) ? true : false;
		
		if( !exist ) return; // link esterni al gruppo
		
		var node = $(nodeId);
		
		var X0 = parseInt(element.left)
		
		var Y0 = parseInt(element.top)
		
		var X1 = parseInt(node.left)
		
		var Y1 = parseInt(node.top)
		
		newLine(element,node,X0,Y0,X1,Y1);
	
	}
	
	
	
	// create draggable
	new Draggable(element, {
		
		handle: element.getElementsByTagName("img")[0], // handle on icon
		
		onDrag: function(drgObj){
			
			
			// case node has negative y-coordinate
			if( parseInt(drgObj.element.style.top.replace("px","")) < 0 ){
				
				if( drgObj.element.out == true ) return;
				
				drgObj.element.top = "0px";
				
				drgObj.element.style.top = drgObj.element.top;
				
				drgObj.element.left = drgObj.element.style.left;
				
				drgObj.element.out = true;
				
				return;
			
			};
			
			// case node has negative x-coordinate
			if( parseInt(drgObj.element.style.left.replace("px","")) < 0 ){
				
				if( drgObj.element.out == true ) return;
				
				drgObj.element.left = "0px";
				
				drgObj.element.style.left = drgObj.element.left;
				
				drgObj.element.top = drgObj.element.style.top;
				
				drgObj.element.out = true;
				
				return;
			
			};
			
			// case node has y-coordinate > of map height
			if( parseInt(drgObj.element.style.left.replace("px","")) > parseInt( $('map_content').style.maxWidth.replace("px","")) ){
				
				if( drgObj.element.out == true ) return;
				
				drgObj.element.left = $('map_content').style.maxWidth;
				
				drgObj.element.style.left = drgObj.element.left;
				
				drgObj.element.top = drgObj.element.style.top;
				
				drgObj.element.out = true;
				
				return;
			
			};
			
			drgObj.element.out = false;
			
			setMapMinY();
			
			setAllLinesFromNode( drgObj.element )
			
			
			
		},
		
		
		// on end drag re-set all lines ( needed for [the slow] Internet explorer )
		onEnd: function(drgObj){
		
			if( !!drgObj.element.out ){
			
				drgObj.element.style.top = drgObj.element.top
				
				drgObj.element.style.left = drgObj.element.left
				
				setAllLinesFromNode( drgObj.element )
				
			
			}
		
		}
	
	});


}





/* --------------------------------------------------------- *\

	  Function    : setChildDisposition()
	  Description : if a node is in a rectangle of another
	  		node change x and y coordinates
	  Usage       : setChildDisposition()
	  Arguments   : 

\* ---------------------------------------------------------- */
function setChildDisposition(){
	
	for(i = 0; i < this.nodeList.length; i++){
		
		var elem = this.nodeList[i];
		
		if(elem.style.left == "100px" && elem.style.top == "100px") elem.orig = true;
		
		else elem.orig = false;
			
		for( j = 0; j < this.nodeList.length; j++){
			
			var elemToContr = this.nodeList[j];
				
			  if( elem != elemToContr && !rectangleIsFree(elemToContr.rect) && !!elem.orig){
				
		
				elem.style.left = (elemToContr.rect.xTo + 2)+"px";
				
				elem.style.top = (elemToContr.rect.yTo + 2) +"px";
				
				elem.rect.xFrom = parseInt( elem.style.left.replace("px",""))
				
				elem.rect.yFrom = parseInt( elem.style.top.replace("px",""))
				
				elem.rect.xTo = elem.rect.xFrom + elem.width
				
				elem.rect.yTo = elem.rect.yFrom + elem.height
				
				elem.left = elem.rect.xFrom
				
				elem.top = elem.rect.yFrom
				
				this.setChildDisposition();
				
				return;	
				
			}			
			
		};
	};
	
	

}







/* --------------------------------------------------------- *\

	  Function    : Map()
	  Description : Map Object
	  Usage       : new Map();
	  Arguments   : 

\* ---------------------------------------------------------- */
var Map = function(){
	
	
	// PROPERTIES
	
	this.root = $('map_content');
	
	$('map_content').map = this;
	
	this.root.style.maxWidth = this.root.offsetWidth+"px"
	
	this.addNode = Node;
	
	this.nodeList = new Array();
	
	this.setChildDisposition = setChildDisposition;
	
	this.notFree = new Array();
	
	this.maxY = 0;
	
	
	// creating nodes
	for( i = 0; i < this.root.childNodes.length; i++){
		
		if( this.root.childNodes[i].className == "newNodeMap"){
		
			this.addNode(this.root.childNodes[i]);
			
			this.nodeList.push( this.root.childNodes[i] );
		};
		
	};
	
	this.setChildDisposition();
	
	
	// creating links from nodes (lines)
	for( var i = 0; i< this.nodeList.length; i++) this.nodeList[i].link();
	
	var form_ex = $('form_map')? true:false;
	
	try{
		for( var i = 0; i < $$(".newNodeMap").length; i++){
		
			var thisNode = $$(".newNodeMap")[i];
		
			var X0 = parseInt( thisNode.style.left.replace("px","").replace("pt","") );
		
			var Y0 = parseInt( thisNode.style.top.replace("px","").replace("pt","") );
		
			var values = X0+","+Y0+","+parseInt( thisNode.offsetWidth )+",0";
		
			if(!!form_ex) $('form_map').innerHTML += "<input type='hidden' name='input_"+thisNode.id+"' value='"+values+"' id='input_"+thisNode.id+"'/>\n";
		
			var y = parseInt(thisNode.style.top.replace("px",""))
		
			if( y > this.maxY )  this.maxY = y;
		
		
		
	
		}
	
		this.root.height = this.maxY +50
	
		this.root.style.height = this.root.height+"px"
	
	
	
		if(!!form_ex){

                    $('form_map').innerHTML += "<input type='hidden' value='"+i+"' name='nNodeMap'/>\n"; // number of nodes in map

                    // creating submit button (ONLY FOR AUTHOR)
                    var subm_butt = document.createElement("input")

                    subm_butt.type = "button"

                    subm_butt.style.position = "absolute"

                    subm_butt.style.right = "10px"

                    subm_butt.style.top = "10px"

                    subm_butt.value = "salva"

                    subm_butt.onclick = function(){

                            $("form_map").submit()
                    }

                    $('map_content').appendChild( subm_butt )
                };
                
	}catch(e){}
		
}




/* --------------------------------------------------------- *\

	  Function    : newLine()
	  Description : create a new line from a element( a node)
	  		to another node
	  Usage       : newLine(element, node, X0, Y0, X1, Y1)
	  Arguments   : element - the element who lines comes from (HTMLDivElement)
	  		node - the element who lines arrive (HTMLDivElement)
	  		X0 - x-coordinate of left-side of element
	  		Y0 - y-coordinate of top-side of element
	  		X1 - theoretically the width of object ( always 100 )
	  		Y1 - theoretically the height of object ( always 100 )

\* ---------------------------------------------------------- */

function newLine(element, node, X0, Y0, X1, Y1){
	
	// simple geometry
	var dx = X0 - X1
	
	var dy = Y0 - Y1
	
	var lineLength = Math.sqrt( ( dx * dx ) + ( dy * dy ) );
	
	var deg = Math.atan2( dy, dx );
	
	var A01 = Math.cos( deg );
	
	
	
	
	// lines are div whit a border bottom line visible...
	var line = document.createElement("div");
	
	line.style.width = lineLength + "px";
	
	line.style.height = "2px";
	
	line.style.borderBottom = "1px dashed #999999"; 
		
	line.style.position="absolute";
	
	
	
	
	// setting position of lines
	
	if( !isIE ){// the line rotates around his center
		
		line.left = ( ( X0 + X1 ) / 2 - ( lineLength / 2 * A01 ) - ( lineLength / 2 ) * ( 1 - A01 ) ) + 10
	
		line.top = ( ( Y0 + Y1 ) / 2 )+ 10
		
		
	}else{// the line rotates 'attached' to his top and left side ( need four cases )
		
		if( deg <= -Math.PI/2){
		
			line.left = X0+10;
		
			line.top = Y0+10;			
		
		}else if(deg <= 0 && deg > -Math.PI/2){
			
			line.left = X1+10;
		
			line.top = Y0+10;
			
			
		}else if(deg >= Math.PI/2){
			
			line.left = X0+10;
		
			line.top = Y1+10;
			
			
		}else{
			line.left = X1+10;
		
			line.top = Y1+10;
			
			
		}
		
	}
	
	
	line.style.left = line.left + "px"
	
	line.style.top  = line.top + "px"
	
	line.style.zIndex = 1;
	
	
	 
	
	transform(line, deg)
	
	element.linkArray.push({"node":node,"line":line })
	
	node.linkArray.push({"node":element,"line":line})
	
	$("map_content").appendChild(line); 
	
	
}





/* --------------------------------------------------------- *\

	  Function    : setLine()
	  Description : trasforming a given line whit coordinates
	  Usage       : setLine(line, X0, Y0, X1, Y1)
	  Arguments   : line - the line to transform (HTMLDivElement)
	  		X0 - x-coordinate of left-side of element
	  		Y0 - y-coordinate of top-side of element
	  		X1 - theoretically the width of object ( always 100 )
	  		Y1 - theoretically the height of object ( always 100 )

\* ---------------------------------------------------------- */

// see comment for newLine() 
function setLine( line, X0, Y0, X1, Y1){
	
	var dx = X0 - X1;
	
	var dy = Y0 - Y1;
	
	var lineLength = Math.sqrt( ( dx * dx ) + ( dy * dy ) );
	
	var deg = Math.atan2( dy, dx );
	
	var A01 = Math.cos( deg )
	
	line.style.width = lineLength + "px";
	
	line.style.height = "2px"
	
	
	if(!isIE){
		line.left = ( ( X0 + X1 ) / 2 - ( lineLength / 2 * A01 ) - ( lineLength / 2 ) * ( 1 - A01 ) ) + 10
	
		line.top  = ( ( Y0 + Y1 ) / 2 )+ 10
	}else{
		
		
		if( deg < -Math.PI/2){
		
			line.left = X0+10;
		
			line.top = Y0+10;
			
			
		}else if(deg <= 0 && deg > -Math.PI/2){
			
			line.left = X1+10;
		
			line.top = Y0+10;
			
		}else if(deg > Math.PI/2){
			
			line.left = X0+10;
		
			line.top = Y1+10;
			
		}else if(deg >= 0 && deg <= Math.PI/2){
			
			line.left = X1+10;
		
			line.top = Y1+10;
			
		}
	}
	
	line.style.left = line.left + "px"
	
	line.style.top  = line.top + "px"
	
	transform(line, deg)
	
	
}

function rectangleIsFree( obj ){
		
        var xFree = true

        var yFree = true


        for(var i = 0; i < $("map_content").map.nodeList; i++){

                var element = $("map_content").map.nodeList[i];

                if( element.left >= obj.xFrom && element.left  <=  obj.xTo  ) xFree= false

                if( (element.left + element.width ) >= obj.xFrom && (element.left+element.width)  <=  obj.xTo  ) xFree = false

                if( element.top  >= obj.yFrom && element.top  <= obj.yTo  ) yFree = false

                if( (element.top + element.height ) >= obj.yFrom && (element.top + element.height )  <=  obj.yTo ) yFree = false

        }



        if( !!xFree && !!yFree ) return true;

        return false;

}

// EOF
