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
/**
 * This class provides a method for a simultaneously active 
 * view and a controller to exchange information about 
 * the progress of operations. It does this by circumventing 
 * the Joomla classes entirely, accessing the database 
 * separately but cleanly. The "progress" table contains 
 * entries for the progress of opearations currently being 
 * undertaken, identified by their ids.
 */
class Progress
{
	private $progress_id;
	function __construct( $progressId )
	{
		$this->progress_id = $progressId;
		if ( !$progressId )
			die ("Progress id is null in Progress constructor");
	}
	/**
	 * Get the progress amount - the only thing stored in the 
	 * progress table apart from the progress_id key.
	 * @param conn provided if no connection is current
	 * @param db the name of the database if required
	 * @return the number of items already processed
	 */
	function get( $conn=null, $db=null )
	{
		$query="select amount from progress where progress_id=\""
			.$this->progress_id."\";"; 
		if ( !$conn )
			$conn = mysql_connect();
		else
			mysql_select_db( $db );
		$result = mysql_query( $query, $conn )
			or die( "Failed to execute " . $query );
		$thisrow = mysql_fetch_row( $result );
		if ( $thisrow ) 
			return $thisrow[0];
		else
			echo "Progress amount for ".$this->progress_id
				." not found.<br>";
		return 0;
	}
	/**
	 * Set the progress amount 
	 */
	function set( $amount )
	{
		$query="select amount from progress where progress_id=\""
			.$this->progress_id."\";"; 
		$result = mysql_query( $query )
			or die( "Failed to execute " . $query );
		if ( mysql_num_rows($result) == 1 )
			$query="update progress set amount=\"$amount\""
				." where progress_id=\"".$this->progress_id."\";"; 
		else
			$query="insert into progress (progress_id,amount) values ('"
				.$this->progress_id."','$amount');";
		$result = mysql_query($query)
			or die("Failed Query of " . $query);
	}
	/**
	 * Clear the values in the database
	 */
	function clear()
	{
		$query="delete from progress where progress_id=\""
			.$this->progress_id."\";"; 
		$result = mysql_query($query)
			or die("Failed Query of " . $query);
	}
}
?>
