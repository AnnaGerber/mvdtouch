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

/**
 * MVD Component Controller
 */
class MVDControllerTree extends GeneralController
{
	private $image;
	function __construct() 
    {
       	parent::__construct();
       	$this->registerTask('compute','compute');
		$model = &$this->getModel("mvd");
		$session = &JFactory::getSession();
		$mvd = $this->ensureName();
		$this->image = $model->computeTree( $mvd, 
			'true',"1.0",
			"2","Times-Roman" );
	}
	/**
	 * Compute the tree
	 */
	function compute()
	{
		$model = &$this->getModel("mvd");
		$this->image = $model->computeTree( 
			$this->ensureName(), 
			$this->ensureValue('uselengths','true'),
			$this->ensureValue('labelsize',"1.0"),
			$this->ensureValue('improvement','2'),
			$this->ensureValue('font',"Times-Roman") );
		$this->display();
	}
	/**
	 * Set the value of a field in the tree GUI
	 * @param paramKey the key into the REQUEST array
	 * @param defaultValue default to use if not in REQUEST
	 * @return the chosen value
	 */
	function ensureValue( $paramKey, $defaultValue )
	{
		$retValue = "";
		if ( $_REQUEST[$paramKey] )
			$retValue = $_REQUEST[$paramKey];
		else
			$retValue = $defaultValue;
		return $retValue;
	}
    function display()
    {
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		$view = &$this->getView($viewName,$viewType,"");
		$view->setImage( $this->image );
		$view->setName( $this->ensureName() );
		$view->setFont($this->ensureValue('font',"Times-Roman"));
		$view->setLengths($this->ensureValue('uselengths','true'));
		$view->setLabelSize($this->ensureValue('labelsize',"1.0"));
		$view->setImprovement($this->ensureValue('improvement','2'));
		parent::display();
	}
}
