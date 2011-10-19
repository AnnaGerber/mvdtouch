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
class MVDControllerTwin extends JController
{
	function __construct()
	{
		parent::__construct();
       	$this->registerTask('search1','search1');
		$this->registerTask('searchAll1','searchAll1');
       	$this->registerTask('search2','search2');
		$this->registerTask('searchAll2','searchAll2');
	}
	/**
	 * Search for a particular pattern. Get the text with the 
	 * embedded found string and display it. Only one version.
	 */
	function search1()
	{
		$this->perform_search( (int)$_REQUEST['version1'], 1 );
		parent::display();
	}
	/**
	 * Search for a particular pattern. Get the text with the 
	 * embedded found string and display it. Only one version.
	 */
	function search2()
	{
		$this->perform_search( (int)$_REQUEST['version2'], 2 );
		parent::display();
	}
	/**
	 * Search for a particular pattern. Get the text with the 
	 * embedded found string and display it. Search all versions.
	 */
	function searchAll1()
	{
		$this->perform_search( 0, 1 );
		parent::display();
	}
	/**
	 * Search for a particular pattern. Get the text with the 
	 * embedded found string and display it. Search all versions.
	 */
	function searchAll2()
	{
		$this->perform_search( 0, 2 );
		parent::display();
	}
	/**
	 * Perform the actual search and set the result into the view
	 * @param version the version to search for or 0 which means 
	 * all versions
	 * @param version the version(s) to search. 0 means all versions
	 * @param side 1 or 2 indicates left or right side
	 */
	private function perform_search( $version, $side )
	{
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		$view = &$this->getView($viewName,$viewType,"");
		$model = &$this->getModel("mvd");
		$session = &JFactory::getSession();
		$matchset = $session->get( 'MATCHSET'.$side );
		$ms = null;
		$curr_match = null;
		if ( $matchset )
			$ms = new MatchSet( $matchset );
		// search one version specific
		if ( $ms == null 
			|| $ms->version != $version 
			|| $_REQUEST['name'] != $ms->name 
			|| $_REQUEST['pattern'.$side] != $ms->pattern )
		{
			$ms = $model->search( $_REQUEST['name'], 
				$version, $_REQUEST['pattern'.$side]);
			$session->clear('MATCHSET'.$side );
		}
		// end search one version specific
		$matches[] = $ms->getNextMatch();
		// save current match position for find next
		$session->set( 'MATCHSET'.$side, $ms->toString() );
		$ch = new ChunkHandler( "selection".$side, "utf-8" );
		$text = $model->getVersion( $_REQUEST['name'], $matches[0]->version );
		$text = $ch->merge( $text, $matches );
		$view->setVersionTable( $model->getVersionTable($_REQUEST['name']) );
		$view->setText( $text, $matches[0]->version, $side );
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
		$version1 = $_REQUEST['version1'];
		if ( !$version1 )
			$version1 = 1;
		$version2 = $_REQUEST['version2'];
		if ( !$version2 )
			$version2 = ($numVersions>$version1)?$version1+1:$version1;
		$text1 = $model->compare($mvd,$version1,$version2,"deleted");
		$text2 = $model->compare($mvd,$version2,$version1,"added");
		$view->setVersionTable( $vt );
		$view->setText( $text1, $version1, 1 );
		$view->setText( $text2, $version2, 2 );
		parent::display();
	}
}
