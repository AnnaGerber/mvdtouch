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
defined('_JEXEC') or die();

jimport( 'joomla.base.object' );

/**
 * Component in a path (for constructing breadcrumb trails)
 */
class PathChunk extends JObject
{
	public $name;
	public $folderId;
	function __construct( $name, $folderId )
	{
		$this->name = $name;
		$this->folderId = $folderId;
	}
}
