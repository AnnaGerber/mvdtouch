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
 *  Copyright 2010 Desmond Schmidt
 */ 
defined('_JEXEC') or die('Restricted access'); 
?>
<div id="central" style="border:0;position:relative;height:805px;background-color:white;margin-left: auto;margin-right:auto;text-align:left">
<script type="text/javascript">
/**
 * Get the numerical value of a style property
 * @param id id of the element to get the style off
 * @param styleProp exact property e.g. border-top-width
 * @return the property value as an int
 */
function getStyleValue( id, prop )
{
	var value = getStyle( id, prop );
	if ( value )
		return parseInt( value );
	else
		return 0;
}
/**
 * Get the style of the named element
 * @param id id of the element to get the style off
 * @param styleProp exact property e.g. border-top-width
 * @return a css string describing the property's value
 */
function getStyle( id, prop )
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
 * Get the element's offset from the top of the window
 * @return the offset in pixels
 */
function getOffsetTop( id )
{
	var elem = document.getElementById(id);
	var offset = 0;
	while ( elem != null )
	{
		offset += elem.offsetTop;
		elem = elem.offsetParent;
	}
	return offset;
}
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
 * Zoom in by increasing the width and height of the image 
 * up to 8 times the initial size. 
 */
function zoomIn()
{
	var plotElem = document.getElementById("plot");
	var boxElem = document.getElementById("imagebox");
	var divWidth = getStyleValue("imagebox","width");
	var divHeight = getStyleValue("imagebox","height");
	var newWidth = Math.round(plotElem.width*1.25);
	var newHeight = Math.round(plotElem.height*1.25);
	if ( newWidth <= divWidth*8 && newHeight <= divHeight*8 )
	{
		plotElem.width = newWidth;
		plotElem.height = newHeight;
		boxElem.scrollLeft = (newWidth-divWidth)/2;
		boxElem.scrollTop = (newHeight-divHeight)/2;
	}
}
/**
 * Zoom out by reducing the width and height of the image 
 * provided it doesn't become less than that of the enclosing div. 
 */
function zoomOut()
{
	var plotElem = document.getElementById("plot");
	var divWidth = getStyleValue("imagebox","width");
	var boxElem = document.getElementById("imagebox");
	var divHeight = getStyleValue("imagebox","height");
	var newWidth = Math.round(plotElem.width*0.75);
	var newHeight = Math.round(plotElem.height*0.75);
	if ( newWidth >= divWidth && newHeight >= divHeight )
	{
		plotElem.width = newWidth;
		plotElem.height = newHeight;
		boxElem.scrollLeft = (newWidth-divWidth)/2;
		boxElem.scrollTop = (newHeight-divHeight)/2;
	}
	else
	{
		plotElem.width = divWidth;
		plotElem.height = divHeight;
	}
}
/**
 * Set the size of the central div on reload
 */
function resizeWindow()
{
	var centreDiv = document.getElementById("central");
	var imageDiv = document.getElementById("imagebox");
	var panelDiv = document.getElementById("controlpanel");
	var plotElem = document.getElementById("plot");
	var topOffset = getOffsetTop( "central" );
	var divHeight = getWindowHeight()-topOffset;
	var panelHeight = panelDiv.offsetHeight;
	var imgHeight = divHeight-panelHeight;
	var imgWidth = Math.round(imgHeight*0.707);
	plotElem.width = imgWidth;
	plotElem.height = imgHeight;
	//alert( "imgWidth="+imgWidth+" imgHeight="+imgHeight);
	imageDiv.setAttribute("style","height:"+imgHeight
		+"px;width:"+imgWidth+"px;overflow:auto");
	centreDiv.setAttribute( "style", centreDiv.style.cssText
		+";width:"+imgWidth+"px" );
}
/**
 * Something has changed and we must submit
 */
function submitit()
{
	var lengths = document.getElementById("lengths");
	var uselengths = document.getElementById("uselengths");
	if ( lengths.checked )
		uselengths.value = "true";
	else
		uselengths.value = "false";
	document.submission.submit();
}
window.onload=resizeWindow;
</script>
<!-- image div -->
<div id="imagebox">
<img id="plot" src="<?php echo $this->imageUrl; ?>"/>
</div>
<div id="controlpanel">
<form name="submission" action="index.php" method="POST">
<table width="100%"><tr>
<!--font popup-->
<td>Font: 
<?php $this->printSelect('font',null,$this->font,
array('Helvetica','Helvetica-Oblique','Helvetica-Bold',
'Helvetica-BoldOblique','Times','Times-Roman','Times-Italic',
'Times-Bold','Times-BoldItalic','Courier'),"submitit()");?>
</td>
<!-- lengths checkbox-->
<td><?php echo $this->lengthsLabel; ?>: 
<input name="lengths" id="lengths" onchange="submitit()" type="checkbox" 
<?php if ($this->uselengths=='true') echo " checked";?>></input></td>
<!--zoom in button-->
<td align="right"><?php $this->addToolbarButton(
'ZoomIn24.gif',$this->zoomInTooltip,
'zoomIn()','false','zoominbutton','false');?>
</td>
</tr>
<tr>
<!--labelsize popup-->
<td><?php echo $this->labelSize; ?>: 
<?php $this->printSelect('labelsize',
array('0.33','1.0','1.5','2.0'),
$this->labelsize,
array($this->small,$this->medium,$this->large,$this->huge),
'submitit()');?>
</td>
<!--improvement popup-->
<td><?php $this->printSelect('improvement',
array('0','1','2'),
$this->improvement,
array($this->none,$this->equalDaylight,$this->nBody),
'submitit()');?>
</td>
<!--zoom out button-->
<td align="right"><?php $this->addToolbarButton('ZoomOut24.gif',$this->zoomOutTooltip,
	'zoomOut()','false','zoomoutbutton','false');?></td>
</tr></table>
<input id="task" name="task" value="compute" type="hidden"/>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="tree" />
<input name="name" type="hidden" value="<?php echo $this->name;?>"></input>
<input id="uselengths" name="uselengths" type="hidden" value="<?php echo $this->uselengths;?>"/>
</form>
</div>
</div>
