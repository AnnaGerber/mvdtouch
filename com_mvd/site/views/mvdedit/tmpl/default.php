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
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_COMPONENT.DS.'utils'); 
require_once('LoadModule.php');
?>
<html>
<head>
<LINK REL=StyleSheet HREF="components/com_mvd/views/css/phaidros.css" TYPE="text/css">
<script type="text/javascript">
/**
 * This will revert because it will 
 * reread the version without saving it
 */
function revert()
{
	var task = document.getElementById("task");
	task.value = "revert";
	document.submission.submit();
}
/**
 * Save the currently edited XML to the MVD
 */
function save()
{
	if ( xmlIsWellFormed() )
	{
		alert("Document was well-formed. Saving.");
		var task = document.getElementById("task");
		task.value = "save";
    	return true;	
    }
	else
	{
		alert( "Document is not well-formed XML" );
		return false;
	}
}
/**
 * Check that the XML is well formed before submitting it
 * @return true or false
 */
function xmlIsWellFormed()
{
	var textarea = document.getElementById("displaybox1");
	var text = textarea.value;
	var result = parseElement( text, 0 );
	if ( result < 0 )
	{
		var realOffset = -result;
		var end = 20 - result;
		var start = end - 40;
		if ( end > text.length-1 )
			end = text.length-1;
		if ( start < 0 )
			start = 0;
		alert( "XML is not well-formed at offset "
			+realOffset
			+" near '"
			+text.substring(start,end)
			+"'" );
		return false;
	}
	else
		return true;
}
/**
 * Parse a single element while checking XML correctness
 */
function parseElement( text, index )
{
	var index = readStartTag( text, index );
	if ( index > 0 )
		index = readBody( text, index );
	if ( index > 0 )
		index = readEndTag( text, index );
	return index;
}
/**
 * Read a start tag
 */
function readStartTag( text, index )
{
	var len = text.length;
	// point to first non-white space
	while ( index < len && 
		isWhiteSpace(text.charCodeAt(index)) )
		index++;
	// look for compulsory left angle bracket
	if ( text.charCodeAt(index) != 60 )
	{
		return -index;
	}
	else
		index++;
	// check there is no end-tag slash
	if ( text.charCodeAt(index) == 47 )
	{
		return -index;
	}
	// look for right angle bracket
	while ( index < len && 
		text.charCodeAt(index) != 62 )
	{
		if ( text.charCodeAt(index)==60 )
			return -index;
		else
			index++;
	}
	// check it is not an empty element
	if ( text.charCodeAt(index-1) == 47 )
	// leave it pointing to the forward slash
		index--;
	else
		index++;
	return index;
}
/**
 * Read the body of an element
 */
function readBody( text, index )
{
	var len = text.length;
	// check for empty body
	if ( text.charCodeAt(index)!= 47 
		|| (index < len-1 
		&& text.charCodeAt(index+1)!=62) )
	{
		while ( index >= 0 )
		{
			while ( index < len && 
				text.charCodeAt(index) != 60 )
				index++;
			if ( index < len-1 && text.charCodeAt(index+1) != 47 )
				index = parseElement( text, index );
			else
				break;
		}
	}
	return index;
}
/**
 * Read an end-tag
 * @param text the text being parsed
 * @param index start offset into text
 */
function readEndTag( text, index )
{
	var len = text.length;
	if ( text.charCodeAt(index)== 47 
		&& (index < len-1) 
		&& text.charCodeAt(index+1)==62 )
		return index+2;
	else if ( text.charCodeAt(index)==60 
		&& (index < len-1) 
		&& text.charCodeAt(index+1)==47 )
	{
		while ( index < len && text.charCodeAt(index)!=62 )
			index++;
		return index+1;
	}
	else
		return -index;
}
/**
 * Roll your own white space detector
 * @param code character to test if whitespace
 * @return true if white, otherwise false
 */
function isWhiteSpace( code )
{
 	return code == 32 
 		|| code == 10 
 		|| code == 13
 		|| code == 9;
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
	{
		var y = x.currentStyle[prop];
	}
	else if ( window.getComputedStyle )
	{
		var y = window.getComputedStyle(x,null)
			.getPropertyValue(prop);
	}
	return y;
}
/**
 * Get the height of the current web page
 * @return its height in pixels 
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
 * Dynamically resize displayBox div
 */
function resize()
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
	setSelection();
	<?php
	$adjustments = "";
	$offsets = "";
	for ( $i=0;$i<count($this->offets);$i++ )
	{
		$adjustments .= sprintf("%06d",$this->adjustments[$i]);
		$offsets.= sprintf("%06d",$this->offsets[$i]);
	}
	?>
	document.adjustments = "<?php echo $adjustments;?>";
	document.offsets = "<?php echo $offsets;?>";
}
/** needed by windowbox */
function saveMainDivHeight( value )
{
	var heightElem = document.getElementById("myDivHeight");
	heightElem.value = value;
}
/**
 * Set the selection based on the current match if any. 
 * Works in any browser.
 */
function setSelection()
{
	var offset = <?php echo ($this->match)?$this->match->offset:0;?>;
	var len = <?php echo ($this->match)?$this->match->length:0;?>;
	if ( len > 0 )
	{
		//alert("Setting selection offset: "+offset+" len:"+len );
		var ta = document.getElementById('displaybox1');
		if ( document.getSelection != null )
		{
			ta.blur();
			ta.selectionStart = offset;
			ta.selectionEnd = offset+len;
			ta.focus();
			scrollToSelection("displaybox1");
		}
		else
		{
			var range = ta.createTextRange();
			range.collapse(true);
			range.moveStart("character",offset);
			range.moveEnd("character",len);
			range.select();
		}
	}
}
/**
 * Scroll to selection in textarea. Simple cross-platform hack 
 * works most of the time.
 * @param id id of textarea
 */
function scrollToSelection(id)
{
	var ta = document.getElementById(id);
	var boxHeight = getStyleValue(id,"height");
	var height = ta.scrollHeight;
	var lineHeight = getStyleValue( id, "line-height" );
	if ( !lineHeight )
	{
		// Chrome returns "normal"
		lineHeight = getStyleValue( id, "font-size" );
		lineHeight = lineHeight*1.15;
	}
	var nLines = height / lineHeight;
	var textLen = <?php echo $this->textLen;?>;
	var approxLineNo = ((nLines*ta.selectionStart)/textLen);
	var scrollTop = approxLineNo*lineHeight-boxHeight/2;
	if ( scrollTop < 0 )
		scrollTop = 0;
	else // approximate adjustment for header
		scrollTop += boxHeight/5;
	if ( scrollTop > (height-boxHeight) )
	{
		scrollTop = height - boxHeight;
	}	
	ta.scrollTop = scrollTop;
}
window.onload=resize;
</script>
</head>
<body>
<div id="central">
<form name="submission" action="index.php" method="POST">
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
<!-- main editing field-->
<textarea name="displaybox1" class="editfield" id="displaybox1">
<?php echo $this->text; ?>
</textarea>
<!-- window box -->
<?php
$component = 'components/com_mvd';
/*substr(JPATH_COMPONENT,strlen(JPATH_ROOT)+1,
	strlen(JPATH_COMPONENT)-strlen(JPATH_ROOT));
	*/
$expandPath = $component.'/views/graphics/Expand24.gif';
	$collapsePath= $component.'/views/graphics/Collapse24.gif';
$searchPath= $component.'/views/graphics/Search24.gif';
$searchAllPath= $component.'/views/graphics/SearchAll24.gif';
$revertPath = $component.'/views/graphics/Refresh24.gif';
$savePath= $component.'/views/graphics/Save24.gif';
$variant_params = array(
	'windowboxId'=>'windowbox1',
	'displayboxId'=>'displaybox1',
	'base' => $this->version,
	'mvd' => $this->name,
	'rawelem' => "displaybox1",
	'collapsePath'=>$collapsePath,
	'expandPath'=>$expandPath
);
echo LoadModule::getModule("mod_windowbox",$variant_params); 
// search box 
$search_params = array(
	'pattern'=>$_REQUEST['pattern1'],
	'searchboxId'=>'1',
	// button config format: tool-tip:icon-path:script:submit(true|false):id
	'leftButton'=>JText::_('VARIANTS_BUTTON').':'.$expandPath.':'."doVariants('variants','windowbox1','displaybox1');toggleIcon('variants')".':false:variants:false',
	'searchButton'=>JText::_('SEARCH_BUTTON').':'.$searchPath.':dosearch1(\'search\'):true:::false',
	'searchAllButton'=>JText::_('SEARCHALL_BUTTON').':'.$searchAllPath.':dosearch1(\'searchall\'):true:::false',
	'rightButton1'=>JText::_('REVERT_BUTTON').':'.$revertPath.':revert():true:::false',
	'rightButton2'=>JText::_('SAVE_BUTTON').':'.$savePath.':save():true:::false'
);
echo LoadModule::getModule("mod_searchbox",$search_params); 
?>
<input id="task" name="task" type="hidden"/>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="mvdedit" />
<input type="hidden" id="myDivHeight"/>
</form>
<!-- end centralColumn-->
</div>
</body>
</html>
