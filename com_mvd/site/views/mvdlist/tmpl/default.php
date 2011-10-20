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
<div id="central">
<script type="text/javascript">
/** 
 * Update the selection in the table 
 * @param button the button that was clicked in the work
 * or description column
 * @dblclick if true, act as if as an open cmd was issued
 */
function update( button, dblclick )
{
	var tableDiv = document.getElementById("myScrollTable");
	var cells = tableDiv.getElementsByTagName("td");
	var descriptionCell,workCell,kindValue;
	for (var cell = 0; cell < cells.length;cell++)
	{
   		var item = cells[cell];
		/* clear old selection, if present */
		if ( item.style.backgroundColor == document.highlightColour )
		{
			item.style.backgroundColor 
				= document.oldCellBackgroundColor;
			deselectRow();
		}
		var children = item.getElementsByTagName("button");
		for ( var child = 0; child < children.length; child++ )
		{
			if ( children[child].title == button.title )
			{
				if ( children[child].className == "description"
					||children[child].className == "folderdescription" )
					descriptionCell = item;
				else if ( children[child].className == "work")
				{
					kindValue = "work";
					workCell = item;
				}
				else if ( children[child].className == "folder" )
				{
					kindValue = "folder";
					workCell = item;
				}
			}
		}
	}
	// having determined the selected cell
	// take appropriate action
	if ( workCell )
	{
		var nameField = document.getElementById("name");
		var kindField = document.getElementById("kind");
		nameField.value = workCell.childNodes[0].title;
		if ( kindValue == "work" )
		{
			setSelectedMvd( workCell.childNodes[0].title );
		}
		kindField.value = kindValue;
		document.oldCellBackgroundColor 
			= workCell.style.backgroundColor;
		workCell.style.backgroundColor = document.highlightColour;
		selectRow();
	}
	if ( descriptionCell )
	{
		descriptionCell.style.backgroundColor = document.highlightColour;
	}
	if ( dblclick )
	{
		doOpen();
		document.submission.submit();
	}
}
/**
 * Send an Ajax call to the controller setting the name of the 
 * currently selected mvd. The name is stored in the PHP session.
 * @param mvd the name of the mvd
 */
function setSelectedMvd( mvd )
{
	// the format has to be raw, or it will wrap the output in HTML
	var myUrl='<?php echo JURI::base()."index.php?option=com_mvd&format=raw&task=selectmvd&name=";?>'+mvd;
	if ( window.XMLHttpRequest )
	{
		myRequest = new XMLHttpRequest();
		if ( myRequest.overrideMimeType )
			myRequest.overrideMimeType('text/plain');
	}
	else if ( window.ActiveXObject )
	{
		try 
		{
			myRequest = new ActiveXObject("Msxm12.XMLHTTP");
		}
		catch ( e )
		{
			try
			{
				myRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch ( e )
			{
			}
		}
	}
	if ( !myRequest )
	{
		alert("Cannot create XMLHTTP object" );
		return false;
	}
	// send the http request
	myRequest.open('GET', myUrl, true );
	myRequest.send( null );
	// we're not interested in the response
}
/**
 * Respond to a deselection of a row
*/
function deselectRow()
{
<?php
$user =& JFactory::getUser();
if ($user->authorize( 'com_content', 'edit' )) 
{
?>
	disableButton( "deletebutton" );
	disableButton( "editbutton" );
	disableButton( "movetobutton" );
<?php
}
?>
	disableButton( "openbutton" );	
}
/**
 * Respond to selection of a row
*/
function selectRow()
{
<?php
$user =& JFactory::getUser();
if ($user->authorize( 'com_content', 'edit' )) 
{
?>
	enableButton( "deletebutton" );
	enableButton( "editbutton" );
	enableButton( "movetobutton" );
<?php
}
?>
	enableButton( "openbutton" );	
}
/**
 * Change the hidden task input field to have the given task 
 * as its value
 */
function doButton( task )
{
    var hiddenField = document.getElementById('phptask');
    hiddenField.value = task;
}
/**
 * Handle an edit request.
 */
function doEdit()
{
	doButton( 'edit' );
}
/**
 * Handle an open request.
 */
function doOpen()
{
	var kindField = document.getElementById("kind");
	if ( kindField.value == "folder" )
	{
		var folderNameField = document.getElementById("foldername");
		var selectedNameField = document.getElementById("name");
		folderNameField.value = selectedNameField.value;
		doButton('openFolder');
	}
	else
	{
		doButton( 'open' );
	}
}
/**
 * Move up in the hierarchy
 */
function doUp()
{
	doButton("up");
}
/**
 * Disable a button
 * @param buttonId the id of the button to disable
 */
function disableButton( buttonId )
{
	var button = document.getElementById(buttonId);
	button.disabled = true;
}
/**
 * Enable a button
 * @param buttonId the id of the button to denable
 */
function enableButton( buttonId )
{
	var button = document.getElementById(buttonId);
	button.disabled = false;
}
/* Functions only available to editors */
<?php
$user =& JFactory::getUser();
if ($user->authorize( 'com_content', 'edit' )) 
{
?>
/**
 * Handle a delete request. Only proceed if the user OKs it
 */
function doDelete()
{
	var nameField = document.getElementById('name');
	var kindField = document.getElementById("kind");
	var prompt ="";
	if ( kindField.value == "folder" )
		prompt = "<?php echo $this->deletePrompt;?>"+" "+nameField.value
			+" "+"<?php echo $this->deletePromptFolderExtra;?>"+"?";
	else
		prompt = "<?php echo $this->deletePrompt;?>"+" "+nameField.value+"?";
	var answer = confirm( prompt );
	if ( answer )
	{
		if ( kindField.value == "folder" )
			doButton( 'deleteFolder' );
		else
			doButton( 'delete' );
	}
}
/**
 * Create a new folder at the current level by adding a 
 * dialog below the main table
 * @param nameTooltip tooltip for name
 * @param descTooltip tooltip for encoding of title
 * @author Desmond Schmidt
 */
function doNewFolder( nameTooltip, descTooltip )
{
	var nameField = document.getElementById('name');
	var nameTitle = nameField.value;
	var table = document.getElementById('table');
	// name row
	var nameBox = document.createElement("input");
	nameBox.setAttribute("name","NEW_FOLDER");
	nameBox.setAttribute("id","NEW_FOLDER");
	nameBox.title = nameTooltip;
	addRow( table, 0, document.createTextNode(
		"<?php echo $this->namePrompt;?>"+":"), nameBox );
	// OK and CANCEL buttons
	var cancelButton = document.createElement("input");
	cancelButton.setAttribute("type","button");
	cancelButton.setAttribute("value","Cancel");
	cancelButton.setAttribute("onclick","removeNewDialog(1)");
	var submitButton = document.createElement("input");
	submitButton.setAttribute("type","button");
	submitButton.setAttribute("value","OK");
	submitButton.setAttribute("onclick","verifyNewFolder()");
	addRow( table, 1, cancelButton, submitButton );
	disableButton( "newfolderbutton" );
	disableButton( "newmvdbutton" );
}
// check that arguments for new MVD make sense
function verifyNewFolder()
{
	var nameField = document.getElementById("NEW_FOLDER");
	if ( nameField.value.length == 0 )
		alert("Name for folder is required");
	else
	{
		doButton("newFolder");
		document.submission.submit();
	}
}
/**
 * Update the currently selected MVD's folderid
 */
function updateFolderId()
{
}
/**
 * Move the selected folder to another part of the folder 
 * hierarchy. Display a dialog below the main table containing 
 * the name of the current mvd and a popup menu of group names 
 * with that mvd's current group selected. Also display a cancel 
 * and OK button. No validation required.
 * @param popupTooltip tooltip for folder popup
 */
function moveTo( popupTooltip )
{
	// moveto row
	var nameField = document.getElementById("name");
	var folderPopup = document.createElement("select");
	var folderField = document.getElementById("foldername");
	var table = document.getElementById('table');
	var moveToPrompt = document.createTextNode(
		"<?php echo $this->moveToPrompt; ?>"+": ");	
	folderPopup.setAttribute("id","SELECT_FOLDER");
	folderPopup.setAttribute("name","SELECT_FOLDER");
	folderPopup.setAttribute("onchange","updateFolderId()");
	for ( var i=0;i<document.folders.length;i++ )
	{
		var previous = null;
		var option = document.createElement('option');
		option.text = document.folders[i];
		if ( document.folders[i] == folderField.value )
			option.selected = true;
		try
		{
			// standards compliant
	  		folderPopup.add(option,previous);
			previous = option;
	  	}
		catch(ex)
	  	{
	  		// IE only
	  		folderPopup.add(option); 
	  	}
	}
	addRow( table, 0, moveToPrompt, folderPopup );
	// OK and CANCEL buttons
	var cancelButton = document.createElement("input");
	cancelButton.setAttribute("type","button");
	cancelButton.setAttribute("value","Cancel");
	cancelButton.setAttribute("onclick","removeNewDialog(1)");
	var submitButton = document.createElement("input");
	submitButton.setAttribute("type","button");
	submitButton.setAttribute("value","OK");
	submitButton.setAttribute("onclick","doMoveTo()");
	addRow( table, 1, cancelButton, submitButton );
	disableButton( "newfolderbutton" );
	disableButton( "newmvdbutton" );
}
/**
 *	Execute a move to operation
 */
function doMoveTo()
{
	doButton("moveTo");
	document.submission.submit();
}
/**
 * Create a new mvd dialog below the table
 * @param nameTooltip tooltip for name
 * @param descTooltip tooltip for encoding of title
 * @param encTooltip tooltip for encoding popup
 */
function doNewMvd( nameTooltip, descTooltip, encTooltip )
{
	var nameField = document.getElementById('name');
	var nameTitle = nameField.value;
	var table = document.getElementById('table');
	// name row
	var nameBox = document.createElement("input");
	nameBox.setAttribute("name","NEW_MVD");
	nameBox.setAttribute("id","NEW_MVD");
	nameBox.title = nameTooltip;
	addRow( table, 0, document.createTextNode("Name:"), nameBox );
	// description
	var descBox = document.createElement("input");
	descBox.setAttribute("name","MVD_DESCRIPTION");
	descBox.title = descTooltip;
	addRow( table, 1, document.createTextNode("Description:"), descBox );
	// encoding row
	var encodingPopup = document.createElement("select");
	encodingPopup.setAttribute("name","MVD_ENCODING");
	encodingPopup.title=encTooltip;
	var option1 = document.createElement('option');
	option1.text='UTF-8';
	var option2 = document.createElement('option');
	option2.text = 'UTF-16';
	try
	{
		// standards compliant
  		encodingPopup.add(option1,null);
  		encodingPopup.add(option2,option1);
  	}
	catch(ex)
  	{
  		// IE only
  		optionPopup.add(option1); 
   		encodingPopup.add(option2);
  	}
	addRow( table, 2, document.createTextNode("Encoding:"), encodingPopup );
	// OK and CANCEL buttons
	var cancelButton = document.createElement("input");
	cancelButton.setAttribute("type","button");
	cancelButton.setAttribute("value","Cancel");
	cancelButton.setAttribute("onclick","removeNewDialog(3)");
	var submitButton = document.createElement("input");
	submitButton.setAttribute("type","button");
	submitButton.setAttribute("value","OK");
	submitButton.setAttribute("onclick","verifyNewMvd()");
	addRow( table, 3, cancelButton, submitButton );
	disableButton( "newfolderbutton" );
	disableButton( "newmvdbutton" );
}
// check that arguments for new MVD make sense
function verifyNewMvd()
{
	var nameField = document.getElementById("NEW_MVD");
	if ( nameField.value.length == 0 )
		alert("Name for MVD is required");
	else
	{
		doButton("newMVD");
		document.submission.submit();
	}
}
// toggle a given radio button on or off, 
// and treat other radio buttons as a group
function toggleRadio( src )
{
	var radioNone = document.getElementById("RADIO_MASK_NONE");
	var radioXML = document.getElementById("RADIO_MASK_XML");
	var radioText = document.getElementById("RADIO_MASK_TEXT");
	radioNone.checked = false;
	radioText.checked = false;
	radioXML.checked = false;
	src.checked = true;
}
// return the last cell
function addRow( table, index, cell1, cell2 )
{
	var x=table.insertRow( index );
	var y=x.insertCell(0);
	y.appendChild(cell1);
	var z = x.insertCell(1);
	z.appendChild(cell2);
	return z;
}
function removeNewDialog( rows )
{
	var table = document.getElementById('table');
	for ( var i=rows;i>=0;i-- )
	{
		table.deleteRow(i);
	}
	enableButton( "newmvdbutton" );
	enableButton( "newfolderbutton" );
}
<?php
}
?>
document.folders = "<?php echo $this->folderList; ?>".split(",");
document.highlightColour = "lightblue";
function resizeWindow()
{
	var table = document.getElementById("tablecontainer");
	var wHeight = (window.innerHeight)?window.innerHeight:document.body.offsetHeight;
	var topOffset = Math.floor((wHeight-table.offsetHeight)/4);
	var centralDiv = document.getElementById("central");
	centralDiv.style.height = wHeight-centralDiv.offsetTop+"px";
	table.style.marginTop = topOffset+"px";
}
window.onload=resizeWindow;
-->
</script>
<style type="text/css">
div#scrolling 
{ 
	width : 550px; 
	height : <?php echo $this->tableHeight;?>px; 
	overflow : auto; 
}
td#buttoncol
{
	width: 120px;
}
table#table
{
	margin-top: 20px;
	clear: both;
	width: 550px;
	margin-left: auto; margin-right: auto; text-align: left;
}
table#tablecontainer
{
	width: 550px;
	margin-left: auto; margin-right: auto; text-align: left;
}
table#myScrollTable {
	text-align: left;
	font-size: 12px;
	width: 100%;
	font-family: verdana;
	background: #c0c0c0;
}
table#myScrollTable thead  {
	cursor: pointer;
}
table#myScrollTable thead tr,
table#myScrollTable tfoot tr {
	background: #c0c0c0;
}
table#myScrollTable tbody tr {
	background: #f0f0f0;
}
table#myScrollTable tbody tr td {
	border: 1px solid white;
}
table#myScrollTable thead tr td {
	border: 1px solid white;
}
td.workcol
{
	width: 30%;
}
td.descol
{
	width 70%;
}
button.work, button.description
{
	border: 0px;
	font: normal normal 12px Verdana, Geneva, Arial, Helvetica, sans-serif;
	background-color: transparent;
	text-align: left;
	padding-left: 2px;
}
button.folder, button.folderdescription
{
	border: 0px;
	font: bold 12px Verdana, Geneva, Arial, Helvetica, sans-serif;
	background-color: transparent;
	text-align: left;
	padding-left: 2px;
}
input.button
{
	width: 100px;
}
div#central 
{
	border: 0; 
	position: relative; 
	background-color: white; 
	width: 650px; 
	margin-left: auto; 
	margin-right: auto; 
	text-align: left;
}
</style>
<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="submission">
<table id="tablecontainer">
<tr>
<td>
<?php
// button toolbar
// check if user can see these buttons
$user =& JFactory::getUser();
if ($user->authorize( 'com_content', 'edit' )) 
{
	$this->addToolbarButton('New24.gif',$this->newFileButtonTooltip,
		'doNewMvd(\''.$this->nameMVDTooltip.'\',\''.$this->descMVDTooltip.'\',\''.
		$this->encodingTooltip.'\')','false','newmvdbutton','false');
	$this->addToolbarButton('NewFolder24.gif',$this->newFolderButtonTooltip,
		'doNewFolder(\''.$this->nameFolderTooltip.'\',\''.$this->descFolderTooltip.'\')',
		'false','newfolderbutton','false');
	$this->addToolbarButton('Move24.gif',$this->moveToButtonTooltip,
		'moveTo(\''.$this->folderPopupTooltip.'\')','true','movetobutton','false');
}
// all users can see these two
$disabled = ($this->folderId==1)?'true':'false';
$this->addToolbarButton('Up24.gif',$this->upButtonTooltip,'doUp()',$disabled,'upbutton','true');
$this->addToolbarButton('Open24.gif',$this->openButtonTooltip,'doOpen()','false','openbutton','true');
// check if user can see these buttons
$user =& JFactory::getUser();
if ($user->authorize( 'com_content', 'edit' )) 
{
	$this->addToolbarButton('Edit24.gif',$this->editButtonTooltip,'doEdit()','true','editbutton','true');
	$this->addToolbarButton('Delete24.gif',$this->deleteButtonTooltip,'doDelete()','true','deletebutton','true');
}
?>
</td>
</tr>
<tr><td>
<table cellspacing="1" cellpadding="2" class="" id="myScrollTable">
<thead class="fixedHeader">
    <tr>
        <th><?php echo $this->firstColHead; ?></th>
        <th><?php echo $this->secondColHead; ?></th>
    </tr>
</thead>
<tbody class="scrollContent">
<?php if ( count($this->rows) )
{
    foreach ($this->rows as $row )
    {?>
    <tr>
        <td class="workcol"><button 
<?php if ($row->kind == "work") echo "class=\"work\""; else echo "class=\"folder\"";?> type="button" onclick="update(this,false)" ondblclick="update(this,true)" title="<?php echo $row->name;?>"><?php echo $row->name;?></button></td>
        <td class="descol"><button <?php if ($row->kind == "work") echo "class=\"description\""; else echo "class=\"folderdescription\"";?> type="button" onclick="update(this,false)" ondblclick="update(this,true)" title="<?php echo $row->name;?>"><?php if ($row->kind=="folder")echo $this->folderDescription;else echo $row->description;?></button></td>
    </tr>
    <?php }
}
else
{
	?><tr><td>None</td><td>No description</td></tr><?php
}
?>
</tbody>
</table>
<!-- Don't name the buttons because their values are internationalised. Use hidden input field instead to submit task name -->
</td>
</tr><tr><td>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="mvdlist" />
<input id="phptask" type="hidden" name="task" />
<input id="name" name="name" type="hidden" />
<input id="kind" type="hidden" />
<input id="folderid" type="hidden" name="folderid" value="<?php echo $this->folderId;?>"/>
<input id="foldername" name="foldername" type="hidden" value="<?php echo $this->folderName;?>"/>
<!-- since the user can't select a version in this view, it is always 1 -->
<input type="hidden" name="version1" value="1"/>
<!-- expand this for dialogs -->
<table id="table">
</table></td></tr></table></form></div>
