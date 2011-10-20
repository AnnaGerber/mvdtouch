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
require_once( JPATH_COMPONENT.DS.'utils'.DS.'HTMLVariantFormatter.php');
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'mvdlist-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'import-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'edit-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'view-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'twin-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'tree-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'versions-controller.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'fragment-controller.php' );
/**
 * MVD Component Controller
 */
class MVDController extends JController
{
    function __construct() 
    {
       	parent::__construct();
       	$this->registerTask('variants','getVariants');
       	$this->registerTask('variantsraw','getVariantsRaw');
       	$this->registerTask('selectmvd','setSelectedMvd');
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		if ( strcasecmp($viewName,"mvdlist")==0 )
			$classname = 'MVDControllerList';
		else if ( strcasecmp($viewName,"mvdsingle")==0 )
			$classname = 'MVDControllerSingle';
		else if ( strcasecmp($viewName,"mvdtwin")==0 )
			$classname = 'MVDControllerTwin';
		else if ( strcasecmp($viewName,"mvdedit")==0 )
			$classname = 'MVDControllerEdit';
		else if ( strcasecmp($viewName,"import")==0 )
			$classname = 'MVDControllerImport';
		else if ( strcasecmp($viewName,"importconfirm")==0 )
			$classname = 'MVDControllerImport';
		else if ( strcasecmp($viewName,"importerror")==0 )
			$classname = 'MVDControllerImport';
		else if ( strcasecmp($viewName,"versions")==0 )
			$classname = 'MVDControllerVersions';
		else if ( strcasecmp($viewName,"tree")==0 )
			$classname = 'MVDControllerTree';
		else if (strcasecmp($viewName,"fragment")==0)
		    $classname = 'MVDControllerFragment';
		if ( $classname )
		{
			error_log("controller");
			$controller = new $classname();
			// Perform the Request task
			$controller->execute( JRequest::getVar('task') );
			// Redirect if set by the controller
			$controller->redirect();
		}
	}
    /**
     * Get variants for the current MVD, for a given offset, length 
	 * and base text. Since this can be called by any view, we put 
	 * it here. The url needs to define the mvd name, the base version, 
	 * the offset and the length
     * @access public
     */
    function getVariants()
	{
		$model = &$this->getModel("mvd");
		$variants = $model->get_variants( $_REQUEST['name'], 
			$_REQUEST['base'], 
			$_REQUEST['offset'], $_REQUEST['length'] 
		);
		$hvf = new HTMLVariantFormatter( $variants );
		echo $hvf->toString();
	}
    /**
     * Get the variants for a range but rather than htmlformatting it,
	 * just replace all angle-brackets with their entitites.
     * @access public
	 * @return an unformatted string containing variants
     */
    function getVariantsRaw()
	{
		$model = &$this->getModel("mvd");
		$variants = $model->get_variants( $_REQUEST['name'], 
			$_REQUEST['base'], 
			$_REQUEST['offset'], $_REQUEST['length'] 
		);
		$escaped = $this->escape( $variants );
		echo $escaped;
	}
	/**
	 * Convert every left and right angle bracket into their 
	 * escaped equivalents. (Ampersands must already be entities 
	 * or it wouldn't be XML.)
	 * @param xml raw XML
	 * @return escaped XML for display
	 */
	function escape( &$xml )
	{
		$len = strlen( $xml );
		$state = 0;
		$escaped = "";
		for ( $i=0;$i<$len;$i++ )
		{
			if ( $xml[$i] == '<' )
				$escaped .= "&lt;";
			else if ( $xml[$i] == '>' )
				$escaped .= "&gt;";
			else
				$escaped .= $xml[$i];
		}
		return $escaped;
	}
	/**
 	 * Like the getVariants call this is an Ajax method but 
	 * is only called by the mvdlist view. If the user selects 
	 * an mvd and then clicks on a tab he/she doesn't submit, so no 
 	 * parameters are set in the request. We therefore have to rely 
	 * on the session, but that cannot be normally set in 
	 * javascript/html. So we use Ajax.
	 */
	function setSelectedMvd()
	{
		$session = &JFactory::getSession();
		// the name is set in the request by the url call
		$session->set( 'selected_mvd', $_REQUEST['name'] );
	}
	/** 
 	 * Override to prevent echoed display
 	 */
	function display()
	{
	}
}
?>