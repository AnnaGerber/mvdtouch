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
// confirm import
defined('_JEXEC') or die('Restricted access'); 
?>
<html>
<head>
<LINK REL=StyleSheet HREF="components/com_mvd/views/css/tables.css" TYPE="text/css">
<style type="text/css">
<!--
div#central
{
	width: 550px;
	margin-left: auto; margin-right: auto; text-align: left;
	margin-top: 100px;
}
td.cellok
{
	text-align: center;
	padding-left: 10px;
	padding-right: 10px;
}
td.cellover
{
	text-align: center;
	background-color: pink;
	text-align: center;
	padding-left: 10px;
	padding-right: 10px;
}
td#buttonrow
{
	padding-top: 10px;
	text-align: right;
}
input#default
{
	outline-style: outset;
	outline-color: grey;
	font-weight: bold;
	margin-left: 20px;
	margin-right: 3px;
}
-->
</style>
<script type="text/javascript">
function doAbort()
{
	var taskEl = document.getElementById("task");
	taskEl.value = "abort";
}
function doCommit()
{
	var taskEl = document.getElementById("task");
	taskEl.value = "commit";
}
</script>
</head>
<body>
<form action="index.php" method="POST">
<?php
function printAbortCommitRow( $abort, $commitButton, $abortButton )
{
	echo '<tr><td id="buttonrow">';
	if ( $abort )
	{
		echo '<input type="submit" onclick="doCommit()" value="';
		echo $commitButton;
		echo '"/>';
		echo '<input id="default" type="submit" onclick="doAbort()" value="';
		echo $abortButton;
	}
	else
	{
		echo '<input type="submit" onclick="doAbort()" value="';
		echo $abortButton;
		echo '"/>';
		echo '<input id="default" type="submit" onclick="doCommit()" value="';
		echo $commitButton;
	}
	echo '"/></td></tr>';
}
echo '<div id="central">';
if ( strcmp($_REQUEST['abort'],"true")==0 )
	echo $this->abortMessage;
else
	echo $this->confirmMessage;
echo '<table align="center"><tr><td>';
echo '<table id="myScrollTable" align="center">';
echo '<thead class="fixedHeader">';
echo '<tr>';
echo '<th>'.$this->versionHead.'</th>';
echo '<th>'.$this->shortNameHead.'</th>';
echo '<th>'.$this->longNameHead.'</th>';
echo '<th>'.$this->fileHead.'</th>';
echo '<th>'.$this->percentHead.'</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';
function printCell( $content, $percent, $limit )
{
	echo '<td class=';
	if ( $percent>$limit )
		echo '"cellover">';
	else
		echo '"cellok">';
	echo $content;
	echo '</td>';
}
foreach ( $this->filelist as $file )
{
	$percent = (float)$file['percent'];
	$limit = $this->percentLimit;
	echo '<tr>';
		printCell($file['version'],$percent,$limit);
		printCell($file['short_name'],$percent,$limit);
		printCell($file['long_name'],$percent,$limit);
		printCell($file['name'],$percent,$limit);
		printCell($file['percent'],$percent,$limit);
	echo '</tr>';
}
echo '</tbody>';
echo '</table></td></tr>';
if ( strcmp($_REQUEST['abort'],"true")==0 )
	printAbortCommitRow( true, $this->commitButton, $this->abortButton );
else
	printAbortCommitRow( false, $this->commitButton, $this->abortButton );
echo '</table></div>';
?>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" id="task" name="task" />
<input type="hidden" name="view" value="importConfirm" />
<input type="hidden" name="name" value="<?php echo $_REQUEST['name'];?>"/>
</form>
</body>
</html>

