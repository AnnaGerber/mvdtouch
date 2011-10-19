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
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_SITE.DS
	.'components'.DS.'com_mvd'.DS.'utils'); 
class GeneralController extends JController
{
	function __construct()
	{
		parent::__construct();
       	$this->registerTask('search','search');
		$this->registerTask('searchAll','searchAll');
	}
	/**
	 * Search for a particular pattern. Get the text with the 
	 * embedded found string and display it. Only one version.
	 */
	function search()
	{
		$this->perform_search( (int)$_REQUEST['version1'] );
		parent::display();
	}
	/**
	 * Search for a particular pattern. Get the text with the 
	 * embedded found string and display it. Search all versions.
	 */
	function searchAll()
	{
		$this->perform_search( 0 );
		parent::display();
	}
	/**
	 * Abstract method - implement in subclass
	 * @param version the version to search for or 0 which means 
	 * all versions
	 */
	protected function perform_search( $version )
	{
	}
	/**
	 * Ensure that some mvd is selected if none specified
	 * @return an mvd name
	 */
	protected function ensureName()
	{
		if ( isset($_REQUEST['name']) )
			$name = $_REQUEST['name'];
		else
		{
			// try the session
			$session = &JFactory::getSession();
			$name = $session->get( 'selected_mvd' );
			// get first available name
			if ( !$name )
			{
				$model = &$this->getModel("mvd");
				$name = $model->getFirstLongTextName();
			}
		}
		return $name;
	}
	/**
	 * Ensure that some version is selected if none specified
	 * @return a version number
	 */
	protected function ensureVersion()
	{
		if ( isset($_REQUEST['version1']) )
			$version = $_REQUEST['version1'];
		else
			$version = "1";
		return $version;
	}
}
