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
<!-- Select files to import -->
<html>
<head><style type="text/css">
div#central
{
	width: 550px;
	margin-left: auto; margin-right: auto; text-align: left;
	margin-top: 100px;
}
input.undone
{
	background-color: lightpink;
}
input.done
{
	background-color: palegreen;
}
</style>
<script type="text/javascript">
/**
 * Execute a function periodically.
 * @param arg0 is the period in milliseconds
 * @param other args are the arguments for the function to call
 */
Function.prototype.executePeriodically = function ()
{
	// s is the function to call, i.e. us
	var s = this;
	if (typeof arguments[0].callee != 'undefined')
		var arquments = arguments[0];
	else
		var arquments = arguments;
	var delay = arquments[0];
	this.__INTERVAL__ = null;

	// apply remaining args to the function to call
	var args = [];
	for (var i=1; i<arquments.length; i++)
	{
		args.push(arquments[i]); 
	}
	s.apply(this,args);

	// if a timeout is already defined, clear it
	if (this.__INTERVAL__)
		clearTimeout(this.__INTERVAL__);

	// set up the periodic call to s
	this.__INTERVAL__ = setTimeout (
	function ()
	{
		s.executePeriodically(arquments);
	},delay);
	return s;
}
/**
 * Add or update a file in the table. If there is already an 
 * empty row, don't create a new one. Otherwise create an 
 * empty row at the bottom.
 * @param input the file input object that called us
 */
function addFile(input)
{
	// check there are no empty rows
	var hasEmptyRow = 0;
	var tableEl = document.getElementById("filelist");
	for ( var i=0;i<tableEl.rows.length;i++ )
	{
		var row = tableEl.rows[i];
		for ( var j=0;j<row.cells.length;j++ )
		{
			var cell = row.cells[j];
			var child = cell.firstChild;
			if ( child instanceof HTMLInputElement && child.value.length==0 )
				hasEmptyRow = 1;
		}
	}
	// add empty row if not present
	if ( !hasEmptyRow )
	{
		var row = tableEl.rows.length;
		var x=tableEl.insertRow( tableEl.rows.length );
		var y=x.insertCell(0);
		y.setAttribute("align","right");
		var newInput = document.createElement("input");
		newInput.setAttribute("type","file");
		newInput.setAttribute("size","45");
		newInput.setAttribute("onchange","addFile(this)");
		newInput.setAttribute("value","<?php echo $this->addButton;?>");
		newInput.setAttribute("class","undone");
		y.appendChild( newInput );
	}
	// add delete buttons to rows that aren't empty
	for ( var i=0;i<tableEl.rows.length;i++ )
	{
		var row = tableEl.rows[i];
		for ( var j=0;j<row.cells.length;j++ )
		{
			var cell = row.cells[j];
			var child = cell.firstChild;
			if ( child instanceof HTMLInputElement && child.value.length != 0 )
			{
				var base = "/"+"<?php echo $this->base;?>";
				var deleteButton = document.createElement("a");
				deleteButton.setAttribute("style","hover{cursor:pointer}");
				deleteButton.setAttribute("href","javascript:removeRow("+i+")");
				var img = document.createElement("img");
				img.setAttribute("style","outline:none;width:16px;height:16px");
				img.setAttribute("width","16");
				img.setAttribute("src",base+"/images/cancel_f2.png");
				deleteButton.appendChild( img );
				var text = document.createTextNode(" ");
				cell.insertBefore( text, cell.firstChild );
				cell.insertBefore( deleteButton, cell.firstChild );
			}
		}
	}
}
/**
 * Remove a row from the file upload table
 * @param index the 0-based index of the row to delete
 */
function removeRow(index)
{
	var tableEl = document.getElementById("filelist");
	tableEl.deleteRow(index);
	for ( var i=0;i<tableEl.rows.length;i++ )
	{
		var row = tableEl.rows[i];
		for ( var j=0;j<row.cells.length;j++ )
		{
			var cell = row.cells[j];
			var child = cell.firstChild;
			if ( child instanceof HTMLInputElement )
				continue;
			else
				child.setAttribute("href",
					"javascript:removeRow("+i+")");
		}
	}
}
/**
 *	The user pressed Import
 */
function doImport()
{
	var taskField = document.getElementById("task");
	taskField.value = "import";
	// set the names of the file input fields
	var tableEl = document.getElementById("filelist");
	for ( var i=0;i<tableEl.rows.length;i++ )
	{
		var row = tableEl.rows[i];
		for ( var j=0;j<row.cells.length;j++ )
		{
			var cell = row.cells[j];
			var child = cell.firstChild;
			if ( child instanceof HTMLAnchorElement )
			{
				child = child.nextSibling.nextSibling;
			}
			if ( child.value && child.value.length>0 )
			{
				child.setAttribute("name", "file-"+child.value);
			}
		}
	}
}
/**
 * Ajax function to update progress as the background colour of 
 * each input file (and yes, you can specify that for input files 
 * even in IE).
 * @param url url to call to get the number of completed imports
 */
function getProgress( url )
{
	if ( window.XMLHttpRequest )
		xmlhttp=new XMLHttpRequest();
	else
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	xmlhttp.open( "GET", url, false );
	xmlhttp.send( null );
	var amount = Number(xmlhttp.responseText);
	var tableEl = document.getElementById("filelist");
	for ( var i=0;i<tableEl.rows.length&&i<amount;i++ )
	{
		var row = tableEl.rows[i];
		for ( var j=0;j<row.cells.length;j++ )
		{
			var cell = row.cells[j];
			var child = cell.firstChild;
			if ( child instanceof HTMLAnchorElement )
			{
				child = child.nextSibling.nextSibling;
			}
			if ( child instanceof HTMLInputElement )
			{
				child.setAttribute("class","done");
			}
		}
	}
}
/**
 * Execute this periodic function on startup, which executes every 
 * 3/4 of a second and fetches the number of completed imports. 
 */
window.onload = function ()
{
	getProgress.executePeriodically(5000,
		"http://localhost/joomla/components/com_mvd/utils/sprogress.php?PROGRESS_ID=<?php echo $this->progressId;?>");
}
</script>
</head>
<body>
<?php
echo '<form enctype="multipart/form-data" action="index.php" method="POST">';
echo '<div id="central">';
echo $this->importPrompt;
echo '<table id="filelist" align="center">';
echo '<tr><td><input size="45" type="file" class="undone" onchange="addFile(this)"/>';
echo '</td></tr>';
echo '</table>';
echo '<table align="center">';
echo '<tr align="right"><td><p align="right"><input type="submit" onclick="doImport()"';
echo 'name="import" value="';
echo $this->importButton;
echo '"/> ';
echo $this->importTo;
echo ' <select name="name">';
$selected = $this->selectedMvd;
foreach ( $this->mvds as $mvd )
{
	echo "<option";
	if ( $selected == null || strlen($selected) == 0 || $this->selectedMvd == $mvd )
	{
		echo " selected";
		$selected = $mvd;
	}
	echo ">$mvd</option>\n";
}
echo '</select></td></tr>';
echo '</table></div>';
?>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="import" />
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
<input type="hidden" name="PROGRESS_ID" value="<?php echo $this->progressId;?>"/>
<input type="hidden" id="task" name="task" />
</form>
</body>
</html>

