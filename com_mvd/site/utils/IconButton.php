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
class IconButton
{
	/** button 'title' */
	private $toolTip;
	/** url to the icon */
	private $iconPath;
	/** the javascript routine to be called onclick */
	private $script;
	/** if true, this should be a submitting button */
	private $submit;
	/** the id for the button if any*/
	private $id;
	/** mark the button as initially disabled */
	private $disabled;
	/**
	 * Convert textual representation of a boolean into 
	 * a real boolean
	 * @param text TRUE or FALSE or true or false
	 * @return true or false
	 */
	function parseBoolean( $text )
	{
		$upper = strtoupper($text);
		if ( $upper == "FALSE" )
			return false;
		else
			return true;
	}
	/**
	 * Construct an icon button 
	 * @param config a string of form: 
	 * tool-tip:icon-path:script:submit(true|false):id
	 */
	function __construct( $config )
	{
		if ( $config )
		{
			$params = split( ":", $config );
			$this->toolTip = $params[0];
			$this->iconPath = $params[1];
			$this->script = $params[2];
			$this->submit = $this->parseBoolean($params[3]);
			$this->id = $params[4];
			$this->disabled = $this->parseBoolean($params[5]);
		}
	}
	/**
	 * Convert this IconButton into HTML for embedding in a page
	 * @return the button in HTML
	 */
	function toHTML()
	{
		$type = ($this->submit)?'submit" value="':'button';
		$idAttr = ($this->id)?'id="'.$this->id.'" ':'';
		$iconUrl = JURI::base().$this->iconPath;
		return '<input '.$idAttr.'title="'.$this->toolTip
		.'" style="width:28px;height:28px;background-image:url(\''
		.$iconUrl.'\')" type="'.$type.'" onclick="'
		.$this->script.'"></input>';
	}
}
?>
