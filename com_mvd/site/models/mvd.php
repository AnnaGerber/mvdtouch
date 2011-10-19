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
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_SITE.DS
	.'components'.DS.'com_mvd'.DS.'utils'); 
require_once('PathChunk.php');
require_once('VersionTable.php');
require_once('MatchSet.php');
define( "ROOT", 1 );
define( "PS", PATH_SEPARATOR );
/**
 * Model for mvd component
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
class MvdModelMvd extends JModel
{
	/**
	 * Delete an MVD from the database
	 * @return name the name of the MVD
	 */
	function deleteMVD( $name )
	{
		$db = &JFactory::getDBO();
		$query = "delete from works where name='$name';";
		$db->setQuery( $query );
		$db->query();
	}
	/**
	 * Delete a folder and all its contents.
	 * @return name the name of the folder
	 */
	function deleteFolder( $name )
	{
		$db = &JFactory::getDBO();
		$query = "select id from folders where name='$name';";
		$db->setQuery( $query );
		$result = $db->loadObjectList();
		if ( count($result) == 1 )
			$this->deleteFolderById( $result[0]->id );
		else
			error_log("Folder $name not found or is not unique\n");
	}
	/**
	 * Delete a folder and its contents by its id. This is called 
	 * recursively.
	 * @param id the id of the folder to delete
	 */
	function deleteFolderById( $id )
	{
		$db = &JFactory::getDBO();
		$query = "delete from folders where id='$id';";
		$db->setQuery( $query );
		$res1 = $db->query();
		if ( $res1 )
		{
			/* delete child works */
			$query = "delete from works where folder_id='$id';";
			$db->setQuery( $query );
			$res2 = $db->query();
			/* delete child folders (recurse) */
			$query = "select id from folders where parent_id='$id';";
			$db->setQuery( $query );
			$res3 = $db->loadObjectList();
			foreach ( $res3 as $row )
				$this->deleteFolderById( $row[0]->id );
		}
	}
	/**
	 * Create a new MVD in the database
	 * @param name the name of the MVD
	 * @param description its description
	 * @param encoding the character encoding of the MVD contents
	 * @param folderId the id of the desired enclosing folder
	 */
	function newMVD( $name, $description, $encoding, $folderId )
	{
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c create -m $name.mvd "
			."-e $encoding -d \"$description\" -y components".DS."com_mvd".DS."dbconn "
			."-z $folderId";
		/*error_log($command);*/
		shell_exec( $command );
	}
	/**
	 * Create a new folder in the database (no nmerge access needed)
	 * @param name the name of the folder
	 * @param parentId the id of the enclosing folder
	 */
	function newFolder( $name, $parentId )
	{
		$db = &JFactory::getDBO();
		$query = "insert into folders (name,parent_id) ".
			"values('$name','$parentId');";
		$db->setQuery( $query );
		$db->query();
	}
	/**
  	 * Run a query to fetch all mvds for a given folder id
	 * @param folderId id of the parent folder
 	 */
	function getMvds( $folderId )
	{
		$db = &JFactory::getDBO();
		$query = "select name,description from works where folder_id=$folderId";
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	/**
 	 *	Get ALL MVDs in ALL folders
	 */
	function getAllMvds()
	{
		$db = &JFactory::getDBO();
		$query = "select name from works";
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
	/**
  	 * Run a query to fetch all folders of a given parent
 	 */
	function getAllFoldersAt( $parentId )
	{
		if ( $parentId!=0 )
		{
		    $db = &JFactory::getDBO();
		    $query = "select name from folders where parent_id=$parentId";
		    $db->setQuery( $query );
		    /* may also return null */
		    return $db->loadObjectList();
		}
		else
		{
		    return null;
		}
	}
	/**
 	 * Get the names of ALL the folders including root
	 * @return a comma-separated list of folder names
 	 */
	function getAllFolderNames()
	{
		$db = &JFactory::getDBO();
		$query = "select name from folders";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		if ( $rows != null )
		{
			$result = "";
			for ( $i=0;$i<sizeof($rows);$i++ )
			{
				$result .= $rows[$i]->name;
				if ( $i < sizeof($rows)-1 )
				{
					$result .= ",";
				}
			}
			return $result;
		}
		else return "";
	}
	/**
   	 * Move up one level in the folder hierarchy
	 * @param folderId the folder id whose parent id is sought
	 * @return the parent folder id or root if already at root
	 */
	function upFolder( $folderId )
	{
		if ( $folderId!=ROOT )
		{
		    $db = &JFactory::getDBO();
		    $query = "select parent_id from folders where id=$folderId";
		    $db->setQuery( $query );
		    $rows = $db->loadObjectList();
		    if ( sizeof($rows)==1 )
		    {
				return $rows[0]->parent_id;
		    }
		    else
		    {
				return ROOT;
		    }
		}
		else
			return ROOT;
	}
	/**
   	 * Get the path to a given folder id
	 * @param folderId the folder id whose path is sought
	 * @return an array of PathComponents
	 */
	function getFolderPath( $folderId )
	{
		$db = &JFactory::getDBO();
		$path = array();
		while ( $folderId != 0 )
		{
			$query = "select name,parent_id from folders where id=$folderId";
			$db->setQuery( $query );
			$row = $db->loadRow();
			if ( $row != null )
			{
				$path[] = new PathChunk($row[0],$folderId);
				$folderId = $row[1];
			}
			else
				$folderId = 0;
		}
		return $path;
	}
	/**
 	 *	Get the id for a named folder
	 *	@param folder the name of the folder
 	 *	@return the id of that folder
	 */
	function getFolderId( $folder )
	{
		$db = &JFactory::getDBO();
		$query = "select id from folders where name='$folder'";	
		$db->setQuery( $query );
		$result = $db->loadObjectList();
		//error_log("folder name=$folder. id=".$result[0]->id);
		if ( count($result==1)	 )
			return $result[0]->id;
		else
		{
			error_log("folderid not found or non-unique foldername $folder");
			return "1";
		}
	}
	/**
 	 *	Get the name of a folder
	 *	@param folderId the id of the folder
 	 *	@return the name of that folder
	 */
	function getFolderName( $folderId )
	{
		$db = &JFactory::getDBO();
		$query = "select name from folders where id='$folderId'";	
		$db->setQuery( $query );
		$result = $db->loadObjectList();
		if ( count($result==1)	 )
			return $result[0]->name;
		else
		{
			error_log("folderid not found or non-unique foldername $folder");
			return "1";
		}
	}
	/**
 	 *	Move an MVD to a new folder
 	 *	@param mvd the name of the mve to move
	 *	@param folder name of the new folder
	 */
	function moveTo( $mvd, $folder )
	{
		$newId = $this->getFolderId( $folder );
		if ( $newId )
		{
			$db = &JFactory::getDBO();
			$query = "update works set folder_id=\"$newId\" where name=\"$mvd\";";
			$db->setQuery( $query );
			$db->query();
		}
	}
	/**
	 * Copy an MVD from works to tempworks, so it can be manipulated.
	 * @name the name of the MVD to move
	 */
	function copyToTemp( $name )
	{
		$db = &JFactory::getDBO();
		$query = "INSERT INTO tempworks SELECT * FROM works where name=\"$name\";";
		$db->setQuery( $query );
		$db->query();
	}
	/**
	 * Add a new version to an MVD
	 * @param name the name of the mvd (unique)
	 * @param file the file whose contents are the new version
	 * @param group the new version's group name
	 * @param sname the short name of the version
	 * @param lname the long descriptive name of the version
	 * @return the percentage of the version that was unique 
	 * (or 0.0% if this is the first version)
	 */
	function add( $name, $file, $group, $sname, $lname )
	{
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c add "
			."-m \"$name.mvd\" "
			."-y components".DS."com_mvd".DS."dbtempconn "
			."-g \"$group\" "
			."-s \"$sname\" "
			."-l \"$lname\" "
			."-t \"$file\"";
		return shell_exec( $command );
	}
	/**
	 *	Commit an MVD in the tempworks table to the
	 *	live works table.
	 *	@param mvd the name of the mvd to commit
	 */
	function commitTempMVD( $mvd )
	{
		$db = &JFactory::getDBO();
		// remove old mvd
		$query = "DELETE from works where name=\"$mvd\";";
		$db->setQuery( $query );
		$db->query();
		// add new one
		$query = "INSERT INTO works SELECT * FROM tempworks where name=\"$mvd\";";
		$db->setQuery( $query );
		$db->query();
		// remove temp copy
		$query = "DELETE from tempworks where name=\"$mvd\";";
		$db->setQuery( $query );
		$db->query();
	}
	/**
	 * Remove the temporary mvd from the tempworks table
	 * @param mvd the name of the temporary mvd
 	 */
	function abortTempMVD( $mvd )
	{
		$db = &JFactory::getDBO();
		$query = "DELETE FROM tempworks where name=\"$mvd\";";
		$db->setQuery( $query );
		$db->query();
	}
	/**
 	 * Get the number of versions currently in the mvd, 
	 * main table. To do this we read from the console by counting 
 	 * lines produced by the list command
	 * @param name the name of the mvd whose nversions is desired
	 * @return the number of versions of the mvd as an int
	 */
	function getNumVersions( $name )
	{
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c list "
			."-m $name.mvd "
			."-y components".DS."com_mvd".DS."dbconn "
			."| wc -l";
		//error_log($command);
		$nversions = shell_exec( $command );
		return (int)$nversions;
	}
	/**
	 * Get the text of the specified version.
	 * @param name the name of the mvd to read from
	 * @param version the version number to fetch
	 */
	function getVersion( $name, $version )
	{
		if ( !$name || !$version ) 
			die ("Empty name or version: name=$name version=$version");
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c read "
			."-m $name.mvd "
			."-v $version "
			."-y components".DS."com_mvd".DS."dbconn ";
		//error_log($command);
		$text = shell_exec( $command );
		return $text;
	}
	/**
	 * Get the table of all versions and groups.
	 * @param name the name of the mvd to read from
	 * @return a VersionTable object
	 */
	function getVersionTable( $name )
	{
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c list "
			."-m $name.mvd "
			."-y components".DS."com_mvd".DS."dbconn ";
		$text = shell_exec( $command );
		//error_log($command);
		return new VersionTable( $name, $text );
	}
	/**
	 * Precede each double quote mark with a backslash
	 * @param string the string to escape
	 * @return the escaped string
	 */
	private function escape_quotes( $string )
	{
		$escaped = "";
		for ( $i=0;$i<strlen($string);$i++ )
		{
			if ( $string[$i] == '"' )
				$escaped .= '\\"';
			else
				$escaped .= $string[$i];
		}
		return $escaped;
	}
	/**
	 * Search in a single version
	 * @param name the name of the MVD to search in
	 * @param version the version to search in
	 * @param pattern the pattern to search for
	 * @return a set of matches
	 */
	function search( $name, $version, $pattern )
	{
		$pattern = $this->escape_quotes( $pattern );
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c find "
			."-m $name.mvd "
			."-f \"$pattern\" ";
		if ( $version != 0 )
			$command .="-v $version ";
		$command .= "-y components".DS."com_mvd".DS."dbconn ";
		$text = shell_exec( $command );
		return new MatchSet( $name, $text, $pattern, $version );
	}
	/**
	 * Get variants for a base version, range and length. 
	 * @param name the name of the mvd
	 * @param base the base version
	 * @param offset the offset into the current version
	 * @param length the length of the span to compute variants for
	 * @return a html text fragment containing the variants
	 */
	function get_variants( $name, $base, $offset, $length )
	{
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c variants "
			."-m $name.mvd "
			."-k $length "
			."-o $offset "
			."-v $base "
			."-y components".DS."com_mvd".DS."dbconn ";
		//error_log( $command );
		return shell_exec( $command );
	}
	/**
	 * Compare one version with another. The resulting text will 
	 * be a series of Chunks that will need further processing by 
	 * ChunkHandler to turn it into XML that can be transformed 
	 * into HTML by the view.
	 * @param name name of the mvd to compare versions in
	 * @param version1 number of first version
	 * @param version2 number of the second version
	 * @param unique unique state for version1 only text (added or deleted)
	 * @param an XML text with embedded &lt;chunk&gt; elements
	 */
	function compare( $name, $version1, $version2, $unique )
	{
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c compare "
			."-m $name.mvd "
			."-v $version1 "
			."-w $version2 "
			."-u $unique "
			."-y components".DS."com_mvd".DS."dbconn ";
		$chunks = shell_exec( $command );
		// hardwired encoding - should fetch from database
		$ch = new ChunkHandler( "", "utf-8" );
		$xml = $ch->compareToXML( $chunks, $unique[0] );
		return $xml;
	}
	/**
	 * Get the name of the first MVD whose content exceeds 
	 * 200 bytes.
	 * @return an MVD name
	 */
	function getFirstLongTextName()
	{
		$db = &JFactory::getDBO();
		$query = "select name from works where length(data) > 200";	
		$db->setQuery( $query );
		$result = $db->loadObjectList();
		if ( count($result>=1) )
			return $result[0]->name;
		else
		{
			error_log("No long mvds found");
			return "";
		}
	}
	/**
	 * Replace a version in the MVD with an edited copy.
	 * @param name the name of the mvd
	 * @param version the version1 to save
	 * @param text the text of the new version
	 */
	function save( $name, $version, $text )
	{
		// 1. write text to a temporary file
		$dir = JPATH_ROOT.DS."tmp";
		$tempname = tempnam( $dir, "mvd" );
		$handle = fopen( $tempname, "w" );
		if ( $handle )
		{
			if ( fwrite($handle,$text) )
			{
				fclose( $handle );
				// 2. get nmerge to update from that file
				$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
				$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
				$command = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c update "
					."-m $name.mvd "
					."-v $version "
					."-t $tempname "
					."-y components".DS."com_mvd".DS."dbconn ";
				shell_exec( $command );
			}
			else
			{
				fclose( $handle );
				error_log( "Couldn't write to $tempname: ".$php_errormsg.strlen($text) );
			}
			unlink( $tempname );
		}
		else
			error_log( "Couldn't open file $tempname for writing" );
	}
	/**
 	 * Create a new temporary jpg file and delete any other ones 
	 * older than 3 minutes.
	 * @return the full path to the temporary jpg file
	 */
	function newTempJpg()
	{
		$tmpdir = JPATH_ROOT.DS."tmp";
		// for all temp files if older than 1 hour, delete
		$dirhandle = opendir( $tmpdir );
		$file = TRUE;
		// time in seconds
		$currtime = time();
		while ( $file )
		{
			$file = readdir( $dirhandle );
			$prefix = substr($file,0,3);
			if ( $prefix == 'jpg' )
			{
				$path = $tmpdir."/".$file;
				$mtime = filemtime( $path );
				if ( $currtime-180 > $mtime )
				{
					unlink( $path );
				}
			}
		}
		closedir( $dirhandle );
		$outfile = tempnam( $tmpdir, "jpg" );
		$jpgname = $outfile.".jpg";
		rename( $outfile, $jpgname );
		return $jpgname;
	}
	/**
	 * Compute a new tree for the given mvd and options
	 * @param name name of the mvd
	 * @param lengths 'true' or 'false' - whether to use branch lengths
	 * @param labelsize size of the labels as a decimal string
	 * @param improvement 0="None", 2="n-Body" or 1="Equal daylight"
	 * @param name of supported laserwriter font
	 * @return the full path to the temporary jpg file 
	 */
	function computeTree( $name, $lengths, $labelsize, $improvement, $font )
	{
		$tmpjpg = $this->newTempJpg();
		$local_gs = "components".DS."com_mvd".DS."gs";
		$gs = (file_exists($local_gs))?$local_gs:"gs";		
		$nmerge = "components".DS."com_mvd".DS."nmerge.jar";
		$drawtree = "components".DS."com_mvd".DS."drawtree";
		$fitch = "components".DS."com_mvd".DS."fitch";
		$jdbc = "components".DS."com_mvd".DS."mysql-connector-java-5.1.10-bin.jar";
		$java_cmd = "java -cp .".PS."$jdbc".PS."$nmerge MvdTool -c difference "
			."-m $name.mvd "
			."-y components".DS."com_mvd".DS."dbconn ";
		$drawtree_cmd = "$drawtree -f $font -B $lengths -C $labelsize -I $improvement ";
		$gs_cmd = "$gs -sDEVICE=jpeg -r300x300 -sOutputFile=$tmpjpg -dNOPAUSE";
		$command = "$java_cmd | $fitch | $drawtree_cmd | $gs_cmd";
		//error_log( $command );
		$res = shell_exec( $command );	
		$command = "chmod 644 $tmpjpg";
		$res = shell_exec( $command );
		return $tmpjpg;
	}
}
