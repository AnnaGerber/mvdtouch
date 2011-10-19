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
defined( '_JEXEC' ) or die( 'Restricted access' );
/*
 * When called as a script this code will execute 
 */
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'Progress.php');
if ( !$_REQUEST['PROGRESS_ID'] )
	die("PROGRESS_ID empty in Progress.php script");
$p = new Progress( $_REQUEST['PROGRESS_ID'] );
// set the path to the database properties
// these are needed because we are outside of Joomla 
// within the Ajax script calling the server
$path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."dbtempconn.properties";
$props = parse_ini_file( $path );
$conn = mysql_connect($_SERVER['SERVER_NAME'], $props['username'], 
	$props['password']) or die ('Error connecting to mysql');
$result = $p->get( $conn, $props['mvd-db-name'] );
// close database connection
mysql_close($conn);
echo $result;
?>
