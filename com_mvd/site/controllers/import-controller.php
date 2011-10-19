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
define('PERCENT_LIMIT', 90.0 );
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_SITE.DS
	.'components'.DS.'com_mvd'.DS.'utils'); 
require_once('Progress.php');

/**
 * MVD Component Controller
 */
class MVDControllerImport extends JController
{
	function __construct() 
    {
       	parent::__construct();
       	$this->registerTask('import','importToMVD');
		$this->registerTask('abort','abortMVD');
		$this->registerTask('commit','commitMVD');
		$this->registerTask('progress','getCompleteImports');
		$this->completeImports = 0;
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
		$rows = $model->getAllMvds();
		$mvds = array();
		for ( $i=0;$i<sizeof($rows);$i++ )
		{
			$mvds[] = $rows[$i]->name;
		}
		$view->assignRef( 'mvds', $mvds );
		$view->assignRef( 'screen', $this->screen );
		$view->assignRef( 'percents', $this->percents );
		$view->assignRef( 'files', $this->files );
		parent::display();
	}
	/**
	 * Derive a unique short name for the given version
	 * @return a string
	 */
	function getShortName( $version )
	{
		// a leading * means this is machine-generated
		if ( $version <= 26 )
			return "*".chr(ord('A')+($version-1));
		else if ( $version <= 42 )
			return "*".chr(ord('a')+($version-1));
		else	// what the hell ...
			return "*$version";
	}
	/**
	 * Derive a unique long name for the given version
	 * @return a string
	 */
	function getLongName( $version )
	{
		return JText::_('VERSION')." $version";
	}
	/**
	 * Import a set of files to the named mvd. Constructed MVD 
	 * is stored in the temporary works table. User will later 
	 * be asked to confirm import or to abort if one of the 
	 * versions was not similar enough.
	 */
	function importToMVD()
	{
		$session = &JFactory::getSession();
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		$view = &$this->getView($viewName,$viewType,"");
		$model = &$this->getModel("mvd");
		// copy record to tempworks table
		$mvd = $_REQUEST['name'];
		$model->copyToTemp( $mvd );
		// import files in $_FILES array
		$files = array();
		$version = $model->getNumVersions( $mvd );
		if ( !$_REQUEST['PROGRESS_ID'] )
			die( "Died in import controller");
		$progress = new Progress( $_REQUEST['PROGRESS_ID'] );
		foreach ( $_FILES as $file )
		{
			$version++;
			$shortName = $this->getShortName($version);
			$longName = $this->getLongName($version);
			$retval = $model->add( 
				$mvd, 
				$file['tmp_name'],
				JText::_('TOP_LEVEL'),
				$shortName,
				$longName );
			$file_entry = array();
			$this->error = $this->getError( $retval );
			if ( strlen($error)==0 )
			{
				$progress->set( ++$this->completeImports );
				// aid to debugging
				sleep( 10 );
				$file_entry['name'] = $file['name'];
				$file_entry['short_name'] = $shortName;
				$file_entry['long_name'] = $longName;
				$file_entry['version'] = (string)$version;
				$file_entry['percent'] = $retval;
				$files[] = $file_entry;
			}
			else
			{
				$session->set( 'ERROR', $this->error );
				$this->redirectTo("importError");
				break;
			}
		}
		$this->files = $files;
		// check for mismatched versions
		$this->abort = "false";
		foreach ( $files as $file )
		{
			if ( (float)$file['percent'] > PERCENT_LIMIT )
			{
				$this->abort = "true";
				break;
			}
		}
		$progress->clear();
		$this->redirectTo("importConfirm");
	}
	/**
	 * Get the error message if any preceding the percentage
	 * @param retval the value returned by nmerge
 	 * @return the empty string or error message extracted from retval
	 */
	function getError( $retval )
	{
		$error = "";
		$spacePos = strrpos( $retval, " " );
		if ( $spacePos )
			$error = substr( $retval, 0, $spacePos );
		return $error;
	}
	/**
	 * Replace the semi and full colons in a field with "."
	 * @return the cleaned field
	 */
	function replaceSemiAndFullColons( $field )
	{
		$field = str_replace( ":", ".", $field );
		$field = str_replace( ";", ".", $field );
		return $field;
	}
	/**
 	 * Move to the indicated view
	 * @param viewName the name of the desired view
	 */
	function redirectTo( $viewName )
	{
		$session = &JFactory::getSession();
		$filelist = "";
		foreach ( $this->files as $file )
		{
			if ( strlen($filelist)>0 )
				$filelist .= ';';
			$v = $this->replaceSemiAndFullColons( $file['version'] );
			$s = $this->replaceSemiAndFullColons( $file['short_name'] );
			$l = $this->replaceSemiAndFullColons( $file['long_name']);
			$n = $this->replaceSemiAndFullColons( $file['name'] );
			$p = $this->replaceSemiAndFullColons( $file['percent'] );
			$filelist .= "$v:$s:$l:$n:$p";
		}
		$session->set( 'FILELIST', $filelist );
		parent::setRedirect("http://".
			$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].
				"?option=com_mvd&view=$viewName&name="
				.$_REQUEST['name']."&abort=".$this->abort);
	 	parent::redirect();  
	}
	/**
	 * Commit a temporary MVD to permanant storage.
	 */
	function commitMVD()
	{
		$model = &$this->getModel("mvd");
		$model->commitTempMVD($_REQUEST['name']);
		$this->redirectTo( "mvdlist" );
	}
	/**
	 * Abort a temporary MVD.
	 */
	function abortMVD()
	{
		$model = &$this->getModel("mvd");
		$model->abortTempMVD($_REQUEST['name']);
		$this->redirectTo( "mvdlist" );
	}
}
