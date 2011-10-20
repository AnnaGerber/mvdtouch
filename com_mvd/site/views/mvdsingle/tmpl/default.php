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
// No direct access
 
defined('_JEXEC') or die('Restricted access'); 
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_COMPONENT.DS.'utils');
require_once('LoadModule.php');
?>
<LINK REL=StyleSheet HREF="components/com_mvd/views/css/phaidros.css" TYPE="text/css">
<style type="text/css">
div#displaybox1 
{ 
	overflow: auto; 
	height: 700px; 
	width: 550px; 
	padding-right: 4px; 
}
div#central 
{ 
	border: 0; 
	position: relative; 
	height: 805px; 
	top: 40px; 
	background-color: white; 
	width: 978px; 
	margin-left: auto; 
	margin-right: auto; 
	text-align: left;
}
</style>
<script type="text/javascript">
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
 * On document load resize the central div to fit exactly 
 * in the window.
 */
function resizeWindow()
{
	var centreDiv = document.getElementById( "central" );
	var topOffset = getOffsetTop( "central" );
	var divHeight = getWindowHeight()-topOffset;
	// compute the padding of the display box
	var padding= getStyleValue("displaybox1", "padding");
	padding += getStyleValue("displaybox1","padding-left");
	padding += getStyleValue("displaybox1","padding-right");
	var dispWidth = getStyleValue("displaybox1","width");
	var singleSideWidth = dispWidth-padding;
	// get all the inner box elements
	var infobox1 = document.getElementById("infobox1");
	var displaybox1 = document.getElementById("displaybox1");
	var searchbox1 = document.getElementById("searchbox1");
	// set the widths of all 3 boxes
	infobox1.style.width = (singleSideWidth+padding)+"px";
	displaybox1.style.width = singleSideWidth+"px";
	searchbox1.style.width = (singleSideWidth+padding)+"px";
	// don't forget the enclosing div
	centreDiv.style.width = singleSideWidth+"px";
	// finally, set the displaybox heights
	var infoHeight1 = infobox1.clientHeight;
	var searchHeight1 = searchbox1.clientHeight;
	var height = (divHeight-(searchHeight1+infoHeight1));
	displaybox1.style.height = height+"px";
	saveMainDivHeight( height );
	scrollToSelection();
	// copy html=>xml hash to the adjustment and offset tables
	<?php
	$adjustments = "";
	$offsets = "";
	$keys = array_keys($this->offsets);
	sort( $keys );
	for ( $i=0;$i<count($keys);$i++ )
	{
		$adjustment = $keys[$i] - $this->offsets[$keys[$i]];
		$offsets .= sprintf("%06d",$keys[$i]);
		$adjustments .= sprintf("%06d",$adjustment);
	}
	?>
	document.adjustments = "<?php echo $adjustments;?>";
	document.offsets = "<?php echo $offsets;?>";
}
/**
 * Scroll to the current selection if present 
 */
function scrollToSelection()
{
	var selElem = document.getElementById("selection1");
	var divElem = document.getElementById("displaybox1");
	if ( selElem != null )
	{
		var selOffset = getOffsetTop( "selection1" );
		var divOffset = getOffsetTop( "displaybox1" );
		var scrollAmount = (selOffset-divOffset)-(divElem.offsetHeight/2);
		if ( scrollAmount < 0 )
			scrollAmount = 0;
		else if ( scrollAmount > divElem.scrollHeight-divElem.offsetHeight )
			scrollAmount = divElem.scrollHeight-divElem.offsetHeight;
		divElem.scrollTop = scrollAmount;
	}
}
/** needed by windowbox */
function saveMainDivHeight( value )
{
	var heightElem = document.getElementById("myDivHeight");
	heightElem.value = value;
}
function getMainDivHeight()
{
	var heightElem = document.getElementById("myDivHeight");
	var intValue = parseInt(heightElem.value);
	return intValue;
}
window.onload=resizeWindow;
</script>
<form name="submission" action="<?php echo JRoute::_('index.php') ?>" method="POST">
<div id="central">
<!-- information box -->
<?php 
$vthtml = $this->vt->toHTMLSelect( $this->version, 
	'versionPopup1', 'popup1' );
$longName = $this->vt->getLongNameFor($this->version);
$info_params = array(
	'version'=>$this->version,
	'popupId'=>'versionPopup1',
	'vthtml'=>$vthtml,
	'mvd'=>$this->name,
	'longName'=>$longName,
	'infoboxId'=>'1',
	'jsFunctionName'=>'popup1'
);
echo LoadModule::getModule("mod_infobox",$info_params);
?>
<!-- body text -->
<div id="displaybox1">
<?php echo $this->html; ?>
</div>
<!-- window box -->
<?php
$component = 'components/com_mvd';
/*substr(JPATH_COMPONENT,strlen(JPATH_ROOT),
	strlen(JPATH_COMPONENT)-strlen(JPATH_ROOT));
	*/
	
$expandPath = $component.'/views/graphics/Expand24.gif';
$collapsePath= $component.'/views/graphics/Collapse24.gif';
$searchPath= $component.'/views/graphics/Search24.gif';
$searchAllPath= $component.'/views/graphics/SearchAll24.gif';
$variant_params = array(
	'windowboxId'=>'windowbox1',
	'displayboxId'=>'displaybox1',
	'base' => $this->version,
	'mvd' => $this->name,
	'rawelem' => "",
	'collapsePath'=>$collapsePath,
	'expandPath'=>$expandPath
);
echo LoadModule::getModule("mod_windowbox",$variant_params); 
// search box
$search_params = array(
	'pattern'=>$_REQUEST['pattern1'],
	'searchboxId'=>'1',
	// button config format: tool-tip:icon-path:script:submit(true|false):id:disabled
	'leftButton'=>JText::_('VARIANTS_BUTTON').':'.$expandPath.':'."doVariants('variants','windowbox1','displaybox1')".':false:variants:false',
	'searchButton'=>JText::_('SEARCH_BUTTON').':'.$searchPath.':dosearch1(\'search\'):true:::false',
	'searchAllButton'=>JText::_('SEARCHALL_BUTTON').':'.$searchAllPath.':dosearch1(\'searchall\'):true:::false',
);
echo LoadModule::getModule("mod_searchbox",$search_params); 
?>
</div>
<input id="task" name="task" type="hidden"/>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="mvdsingle" />
<input type="hidden" id="myDivHeight"/>
</form>
