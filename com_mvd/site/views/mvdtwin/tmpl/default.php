<?php
/*
 * This file is part of MVD_GUI.
 *
 *  MVD_GUI is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  MVD_GUI is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with MVD_GUI.  If not, see <http://www.gnu.org/licenses/>.
 */
// No direct access
defined('_JEXEC') or die('Restricted access'); 
?>
<html>
<head>
<LINK REL=StyleSheet HREF="components/com_mvd/views/css/phaidros.css" TYPE="text/css">
<script type="text/javascript">
var leftScrollPos,rightScrollPos;
var scrolledDiff;
var scrolledSpan;
function getOffsetTopByElem( elem )
{
	var offset = 0;
	while ( elem != null )
	{
		offset += elem.offsetTop;
		elem = elem.offsetParent;
	}
	return offset;
}
/**
 * Get the element's offset from the top of the window
 * @return the offset in pixels
 */
function getOffsetTop( id )
{
	var elem = document.getElementById(id);
	return getOffsetTopByElem( elem );
}
/**
 * Get the numerical value of a style property
 * @param id id of the element to get the style off
 * @param styleProp exact property e.g. border-top-width
 * @return the property value as an int
 */
function getStyleValue( id, prop )
{
	var x = document.getElementById(id);
	// test if in IE
	if ( x.currentStyle )
		var y = x.currentStyle[prop];
	else if ( window.getComputedStyle )
		var y = document.defaultView.getComputedStyle(x,null)
			.getPropertyValue(prop);
	return y;
}
/**
 * Get the platform independent width of an element
 * @return a string
 */
function getElementWidth( id )
{
	var value = 0;
	var obj = document.getElementById( id );
	if ( obj.clientWidth != 0 )
		value = obj.clientWidth;
	else if ( obj.offsetWidth != 0 )
		value = obj.offsetWidth;
	else if ( obj.scrollWidth != 0 )
		value = obj.scrollWidth;
	return value;
}
/**
 * Get window height in cross-browser fashion
 * @return the window height as an int
 */
function getWindowHeight()
{
	var myHeight = 0;
  	if ( typeof( window.innerWidth ) == 'number' ) 
	    myHeight = window.innerHeight;
	else if ( document.documentElement 
		&& ( document.documentElement.clientWidth 
		|| document.documentElement.clientHeight ) )
    	//IE 6+ in 'standards compliant mode'
		myHeight = document.documentElement.clientHeight;
	else if ( document.body && ( document.body.clientWidth 
		|| document.body.clientHeight ) )
    //IE 4 compatible
		myHeight = document.body.clientHeight;
	return myHeight;
}
/**
 * On document reload resize the width and height of the main 
 * div and the three components of each side. Also set the height.
 */
function resizeTwinColumns()
{
	var leftDiv = document.getElementById("leftColumn");
	var rightDiv = document.getElementById("rightColumn");
	var tw = getElementWidth( "twinCentreColumn" );
	var totalWidth = parseInt( tw );
	var topOffset = getOffsetTop( "twinCentreColumn" );
	var padding = getStyleValue("displaybox1","padding-left");
	var padVal = 0;
	if ( padding )
		padVal = parseInt( padding );
	padding = getStyleValue("displaybox1","padding-right");
	if ( padding )
		padVal += parseInt( padding );
	// compute each side's width
	var divHeight = getWindowHeight()-topOffset;
	var singleSideWidth = (totalWidth-padVal)/2-10;
	if ( singleSideWidth > 500 )
		singleSideWidth = 500;
	// get all the inner box elements
	var infobox1 = document.getElementById("infobox1");
	var infobox2 = document.getElementById("infobox2");
	var displaybox1 = document.getElementById("displaybox1");
	var displaybox2 = document.getElementById("displaybox2");
	var searchbox1 = document.getElementById("searchbox1");
	var searchbox2 = document.getElementById("searchbox2");
	// set the widths of all 3 boxes on each side. phew!
	infobox1.style.width = (singleSideWidth+padVal)+"px";
	infobox2.style.width = (singleSideWidth+padVal)+"px";
	displaybox1.style.width = singleSideWidth+"px";
	displaybox2.style.width = singleSideWidth+"px";
	searchbox1.style.width = (singleSideWidth+padVal)+"px";
	searchbox2.style.width = (singleSideWidth+padVal)+"px";
	// don't forget the enclosing left and right divs
	leftDiv.style.width = singleSideWidth+"px";
	rightDiv.style.width = singleSideWidth+"px";
	// finally, set the displaybox heights
	var infoHeight1 = infobox1.clientHeight;
	var infoHeight2 = infobox2.clientHeight;
	var searchHeight = searchbox1.clientHeight;
	displaybox1.style.height = (divHeight-(searchHeight+infoHeight1))+"px";
	displaybox2.style.height = (divHeight-(searchHeight+infoHeight2))+"px";
	//describeDiv( "twinCentreColumn" );
	//describeDiv( "leftColumn" );
	//describeDiv( "rightColumn" );
}
function getElementHeight( elem )
{
	if ( elem.height )
		return elem.height;
	else
		return elem.offsetHeight;
}
/**
 * Call this several times a second to dynamically update the scrolling. 
 * In this way the left and right frames will be kept in sync even as 
 * the user scrolls. Cool!
 */
function synchroScroll()
{
	// 1. find the side that has scrolled most recently
	// and the side that has probably remained static
	var leftDiv = document.getElementById("displaybox1");
	var rightDiv = document.getElementById("displaybox2");
	if ( leftDiv.scrollTop != leftScrollPos )
	{
		leftScrollPos = leftDiv.scrollTop;
		scrolledDiv = leftDiv;
		staticDiv = rightDiv;
	}
	else if ( rightScrollPos != rightDiv.scrollTop )
	{
		rightScrollPos = rightDiv.scrollTop;
		scrolledDiv = rightDiv;
		staticDiv = leftDiv;
	}
	else	// nothing to do
		return;
	// 2. find the most central span in the scrolled div
	scrolledDiff = 4294967296;
	scrolledSpan = null;
	var scrolledDivTop = getOffsetTopByElem( scrolledDiv );
	var staticDivTop = getOffsetTopByElem( staticDiv );
	var centre = getElementHeight(scrolledDiv)/2
		+scrolledDiv.scrollTop;
	findSpanAtOffset( scrolledDiv, centre, scrolledDivTop );
	// 3. find the corresponding span on the other side
	if ( scrolledSpan != null )
	{
		var staticId = scrolledSpan.getAttribute("id");
		if ( staticId.charAt(0)=='a' )
			staticId = "d"+staticId.substring(1);
		else
			staticId = "a"+staticId.substring(1);
		var staticSpan = document.getElementById( staticId );
		if ( staticSpan != null )
		{
			// 4. compute relative topOffset of scrolledSpan
			var scrolledTopOffset = scrolledSpan.offsetTop
				-scrolledDivTop;
			// 5. compute relative topOffset of staticSpan
			var staticTopOffset = staticSpan.offsetTop-staticDivTop;
			// 6. scroll the static div level with scrolledSpan
			var top = staticTopOffset-getElementHeight(staticDiv)/2;
			if ( top < 0 )
				staticDiv.scrollTop = 0;
			else
				staticDiv.scrollTop = top;
		}
	}
}
/**
 * Find the span closest to the given pos recursively.
 * @param elem the element to start from
 * @param pos the relative position from div top offset to the 
 * top of the desired span
 * @param divOffset the top offset of the enclosing div
 * relative to the top of the page
 */
function findSpanAtOffset( elem, pos, divOffset )
{
	if ( elem.nodeName == "SPAN"
		&& elem.getAttribute('id') != null )
	{
		var idAttr = elem.getAttribute('id');
		var spanRelOffset = elem.offsetTop-divOffset;
		if ( Math.abs(spanRelOffset-pos) < scrolledDiff )
		{
			scrolledSpan = elem;
			scrolledDiff = Math.abs(spanRelOffset-pos);
		}
	}
	else if ( elem.firstChild != null )
		findSpanAtOffset( elem.firstChild, pos, divOffset );
	if ( elem.nextSibling != null )
		findSpanAtOffset( elem.nextSibling, pos, divOffset );
}
setInterval("synchroScroll()",500);
window.onload=resizeTwinColumns;
</script>
</head>
<body>
<form name="submission" action="<?php echo JRoute::_('index.php') ?>" method="POST">
<!-- wrapper for twin columns -->
<div id="twinCentreColumn">
<?php
$this->writeColumn( 1, "left", $_REQUEST['pattern1'], $this->version1, $this->html1 );
$this->writeColumn( 2, "right", $_REQUEST['pattern2'], $this->version2, $this->html2 );
?>
</div>
<input id="task" name="task" type="hidden"/>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="mvdtwin" />
</form>
</body>
</html>
