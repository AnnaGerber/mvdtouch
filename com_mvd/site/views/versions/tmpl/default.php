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
// function of view: display version information
defined('_JEXEC') or die('Restricted access'); 
?>
<?php echo $this->html;?>
<form name="submission" action="index.php" method="POST">
<input id="task" name="task" type="hidden"/>
<input type="hidden" name="option" value="com_mvd" />
<input type="hidden" name="view" value="versions" />
<input name="name" type="hidden" value="<?php echo $this->name;?>"></input>
</form>

