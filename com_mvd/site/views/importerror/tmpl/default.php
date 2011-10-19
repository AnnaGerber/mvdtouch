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
// import error
defined('_JEXEC') or die('Restricted access'); 
?>
<html>
<head><style type="text/css">
div#central
{
	width: 550px;
	margin-left: auto; margin-right: auto; text-align: left;
	margin-top: 100px;
}
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
	echo '<form action="index.php" method="POST">';
	echo '<div id="central">';
	echo $this->errorMessage;
	echo $this->errorReport;
	echo '<table><tr align="right"><td><input type="submit"';
	echo 'name="'.$this->okayButton.'"/></td></tr></table>';
	echo '</div>';
?>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" id="task" name="task" />
</form>
</body>
</html>

