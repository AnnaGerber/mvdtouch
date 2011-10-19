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
require_once('general-controller.php');
/**
 * MVD Component Controller
 */
class MVDControllerEdit extends GeneralController
{
	function __construct()
	{
		parent::__construct();
       	$this->registerTask('save','save');
		$this->registerTask('revert','revert');
	}
	/**
	 * Perform the actual search and set the result into the view
	 * @param version the version to search for or 0 which means 
	 * all versions
	 */
	protected function perform_search( $version )
	{
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		$view = &$this->getView($viewName,$viewType,"");
		$model = &$this->getModel("mvd");
		$session = &JFactory::getSession();
		$matchset = $session->get( 'MATCHSET' );
		$ms = null;
		$curr_match = null;
		if ( $matchset )
			$ms = new MatchSet( $matchset );
		// search one version specific
		if ( $ms == null 
			|| $ms->version != $version 
			|| $_REQUEST['name'] != $ms->name 
			|| $_REQUEST['pattern1'] != $ms->pattern )
		{
			$ms = $model->search( $_REQUEST['name'], 
				$version, $_REQUEST['pattern1']);
			$session->clear( 'MATCHSET' );
		}
		// end search one version specific
		$match = $ms->getNextMatch();
		// save current match position for find next
		$session->set( 'MATCHSET', $ms->toString() );
		$text = $model->getVersion( $_REQUEST['name'], $match->version );
		$view->setText( $text, $match->version, $_REQUEST['name'],
			$model->getVersionTable($_REQUEST['name']) );
		$view->setMatch( $match );
	}
	/**
 	 * Throw away any edits in the current display by refreshing it.
	 */
	function revert()
	{
		$this->display();
	}
	/**
	 * Turn the ampersands of the submitted text into '&amp;' 
	 * They won't be so in the submitted text, but they need to 
	 * be like that in the MVD. When we re-edit the XML they will 
	 * get converted back into raw ampersands again.
	 * @param text the unentitified text
	 * @return an entitified version
	 */
	function entitify( $text )
	{
		$output = "";
		$len = strlen( $text );
		for ( $i=0;$i<$len;$i++ )
		{
			if ( $text[$i] == '&' )
				$output .= '&amp;';
			else
				$output .= $text[$i];
		}
		return $output;
	}
	/**
 	 * This has to be a submit not Ajax because in Ajax we can only pass 
	 * get paramaters. And the text can be too long for that. So we just 
	 * set the text and do a refresh.
	 */
	function save()
	{
		$model = &$this->getModel("mvd");
		$model->save( $_REQUEST['name'], $_REQUEST['version1'], 
			$this->entitify($_REQUEST['displaybox1']) );
		$this->display();
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
		$name = $this->ensureName();
		$version = $this->ensureVersion();
		$text = $model->getVersion($name,$version);
		$view->setText( $text, $version, $name,
			$model->getVersionTable($name) );
		parent::display();
	}
}
