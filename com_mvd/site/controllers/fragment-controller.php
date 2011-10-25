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
 *  (c) Copyright 2010 Desmond Schmidt
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');

/**
 * MVD Component Controller
 */
class MVDControllerFragment extends JController
{
	function __construct()
	{
		parent::__construct();
       	
	}
	
	/**
	 * Ensure that some mvd is selected if none specified
	 * @return an mvd name
	 */
	private function ensureName()
	{
		$name = $_REQUEST['name'];
		if ( !$name )
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
     * Method to display the view.
     * @access public
     * Modified version of twin view that returns formatted html and version info for MVDTouch demo
     * Get html fragments for each side of comparison like this:
     * index.php?option=com_mvd&view=fragment&format=raw&version1=1&version2=3&side=lhs
     * To get XML version table:
     * index.php?option=com_mvd&view=fragment&format=raw&list=y
     */
    function display()
    {
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		$view = &$this->getView($viewName,$viewType,"");
		$model = &$this->getModel("mvd");
		$session = &JFactory::getSession();
		$mvd = $this->ensureName();
		$vt = $model->getVersionTable( $mvd );
		$numVersions = $vt->getNumVersions();
		$listVersions = $_REQUEST['list'];
		if ($listVersions == 'y') {
		    $view->setVersionTable($vt);
		    $view->setListVersions(true);
		} else {
		    $view->setListVersions(false);
    		$version1 = $_REQUEST['version1'];
    		if ( !$version1 )
    			$version1 = 1;
    		$version2 = $_REQUEST['version2'];
    		if ( !$version2 )
    			$version2 = ($numVersions>$version1)?$version1+1:$version1;
    		$side = $_REQUEST['side'];
    		if (!$side || $side == "lhs") {
    		    $compareType = "deleted";
    		} else {
    		    $compareType = "added";
    		}
    		$text1 = $model->compare($mvd,$version1,$version2,$compareType);
    		$view->setVersionTable( $vt );
    		$view->setText( $text1, $version1);
		}
		parent::display();
	}
}
