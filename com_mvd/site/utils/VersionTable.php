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
 * Information about a group
 */
class Group
{
	private $parentid;
	private $name;
	function __construct( $parentid, $name )
	{
		$this->name = $name;
		$this->parentid = $parentid;
	}
	/**
	 * Debug: print all the information in this group
	 */
	function printall()
	{
		echo "parentid=".$this->parentid." name=".$this->name."<br>";
	}
	/**
	 * Debug: print all the information in this group
	 * @return a string
	 */
	function getName()
	{
		return $this->name;
	}
	/**
	 * Get the parent id
	 * @return an int
	 */
	function getParentID()
	{
		return $this->parentid;
	}
}
/**
 * Information about a version
 */
class Version
{
	private $gid;
	private $backup;
	private $shortName;
	private $longName;
	function __construct( $gid, $backup, $shortName, $longName )
	{
		$this->gid = $gid;
		$this->backup = $backup;
		$this->shortName = $shortName;
		$this->longName = $longName;
	}
	/**
	 * Debug: print all the information in this version
	 */
	function printall()
	{
		echo "gid=".$this->gid." backup=".$this->backup." shortName="
		.$this->shortName." longName=".$this->longName."<br>";
	}
	/**
	 * Get the long name of this version
	 * @return a string
	 */
	function getLongName()
	{
		return $this->longName;
	}
	/**
	 * Get the short name of this version
	 * @return a string
	 */
	function getShortName()
	{
		return $this->shortName;
	}
	/**
	 * Get the group ID of this version
	 * @return an int
	 */
	function getGroupId()
	{
		return $this->gid;
	}
	/**
	 * Get the ID of the backup version
	 * @return an int
	 */
	function getBackup()
	{
		return $this->backup;
	}
}
/**
 * Construct a table of versions and groups from the output 
 * of the nmerge list command
 */
class VersionTable 
{
	/** array of Group objects */
	private $groups;
	/** array of Version objects */
	private $versions;
	/** name of the mvd we came from */
	private $name;
	/**
	 * Construct a version table by parsing the output of 
	 * the nmerge list command
	 * @param name the name of the mvd
	 * @param $table versions returned by the nmerge list command
	 */
	function __construct( $name, $table )
	{
		$this->name = $name;
		$lines = explode("\n",$table);
		$this->groups = array();
		$this->versions = array();
		for ( $i=0;$i<count($lines);$i++ )
		{
			if ( strlen($lines[$i]) > 0 )
			{
				$elements = preg_split("/\s[a-zA-Z]+=/",$lines[$i]);
				$gid = (int)trim($elements[1],'"');
				$index = $gid-1;
				if ( $index < 0 ) die("Group table index < 0: table=".$table);
				$this->groups[$index] = new Group( 
					(int)trim($elements[2],'"'), trim($elements[3],'"') );
				$this->versions[] = new Version( $gid, 
					(int)trim($elements[4],'"'), trim($elements[5],'"'), 
					trim($elements[6],'"') );
			}
		}
	}
	/**
 	 * Get the number of versions in the table for this mvd
	 * @return an int
	 */
	function getNumVersions()
	{
		return count( $this->versions );
	}
	/**
 	 * Get the number of versions in the table for this mvd
	 * @return an int
	 */
	function getNumGroups()
	{
		return count( $this->groups );
	}
	/**
	 * Get the name of the mvd this version table belongs to
	 * @return a string
 	 */
	function getName()
	{
		return $this->name;
	}
	/**
	 * Fetch the long version name for the given version id
	 * @param versionId an int being the version ID (index+1)
	 * @return a string
	 */
	function getLongNameFor( $versionId )
	{
		if ( $versionId == 0 || $versionId > count($this->versions) ) 
			die ("versionID must be greater than 0 and less than or equal to "
			.count($this->versions));
		return $this->versions[$versionId-1]->getLongName();
	}
	/**
	 * Fetch the group ID for the given version ID
	 * @param versionId an int being the version ID (index+1)
	 * @return an int being the version's group ID
	 */
	function getGroupIdFor( $versionId )
	{
		if ( $versionId == 0 ) die ("versionID must be greater than 0");
		return $this->versions[$versionId-1]->getGroupId();
	}
	/**
	 * Get group name for the given version
	 * @param versionId an int being the version ID (index+1)
	 * @return a string being the versions group name
	 */
	function getGroupNameFor( $versionId )
	{
		if ( $versionId == 0 ) die ("versionID must be greater than 0");
		$gid = $this->versions[$versionId-1]->getGroupId();
		if ( $gid == 0 || $gid > count($this->groups) ) 
			die ("Group ID $gid should be > 0 or less than or "
				."equal to the number of groups");
		$this->groups[$gid-1]->getName();
	}
	/**
 	 * Convert the versions of a group to HTML. 
	 * @param name the popup's ID 
	 * @param selected the ID of the selected version
	 * @param groupId the ID of the group whose versions are desired
	 */
	function versionsToHTML( $name, $selected, $groupId )
	{
		$html = "";
		for ( $i=0,$id=1;$i<count($this->versions);$i++,$id++ )
		{
			if ( $this->versions[$i]->getGroupId() == $groupId )
			{
				$idValue = "$name.$id";
				$html .= "<option id=\"$idValue\"";
				if ( $i+1 == $selected )
					$html .= " selected=\"selected\"";
				$html .= ">";
				$html .= $this->versions[$i]->getShortName();
				$html .= "</option>";
			}
		}
		if (strlen($html)==0 ) die("no versions found for group id $groupId");
		return $html;
	}
	/**
	 * Convert the table to a select control in HTML
	 * @param selected the version ID that is to be selected
	 * @param name the name of the select control to generate
	 * @param jsfunction the name of the popup's javascript function
	 * @return the version table as a HTML select control
	 */
	function toHTMLSelect( $selected, $name, $jsfunction )
	{
		$html = "<select onchange=\"$jsfunction()\" id=\"$name\">";
		if ( count($this->groups) > 0 )
		{
			for ( $i=0;$i<count($this->groups);$i++ )
			{
				$html .= "<optgroup label=\"";
				if ( !$this->groups[$i] ) die("Not a valid group at $i");
				$html .= $this->groups[$i]->getName()."\">";
				$html .= $this->versionsToHTML( $name, $selected, $i+1 );
				$html .= "</optgroup>";
			}
		}
		else
			$html .= $this->versionsToHTML( $name, $selected, 0 );
		$html .= "</select>";
		return $html;
	}
	/**
	 * Convert a version table to XML. This can then be converted via
	 * XSLT to various HTML forms, for example a form to edit
	 * the version table itself, or a view-only form of the version 
	 * table.
	 * @return an XML representation of the version table
	 */
	function toXML()
	{
		$string = '<version-table name="'.$this->name.'">';
		$string .= $this->writeGroupVersions( 0 );
		$string .= "</version-table>";
		error_log( "XML:".$string);
		return $string;
	}
	/**
	 * Write out the versions and any nested groups recursively
	 * @param gid the group id whose versions and subgroups to write
	 * @return an XML string
	 */
	function writeGroupVersions( $gid )
	{
		$string = "";
		// first write out this group's versions
		for ( $j=0;$j<count($this->versions);$j++ )
		{
			if ( $this->versions[$j]->getGroupId() == $gid )
			{
				$version = $this->versions[$j];
				$sname = $version->getShortName();
				$lname = $version->getLongName();
				$gname = $this->groups[$gid-1]->getName();
				$bid = $version->getBackup;
				$backup = ($bid==0)?JText::_('NO_BACKUP')
					:$this->versions[$bid]->getShortName();
				$string .= '<version shortname="'.$sname.'" longname="'
				.$lname.'" group="'.$gname.'" backup="'.$backup.'"/>';
			}
		}
		// now write out the nested groups
		for ( $i=0;$i<count($this->groups);$i++ )
		{
			$group = $this->groups[$i];
			if ( $group->getParentID() == $gid )
			{
				$gname = $group->getName();
				$id = $i+1;
				$pgname = $this->groups[$group->getParentID()-1];
				$string .= '<group id="'.$id.'" name="'.$gname.'" parent="'.$pgname.'">';
				$string .= $this->writeGroupVersions( $i+1 );
				$string .= '</group>';
			}
		}
		return $string;
	}

}
