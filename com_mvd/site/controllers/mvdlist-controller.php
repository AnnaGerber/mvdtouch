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
 * MVDList Component Controller
 */
class MVDControllerList extends JController
{
    function __construct() 
    {
       	parent::__construct();
       	$this->registerTask('open','openMVD');
       	$this->registerTask('newMVD','newMVD');
       	$this->registerTask('newFolder','newFolder');
       	$this->registerTask('edit','editMVD');
       	$this->registerTask('delete','deleteMVD');
		$this->registerTask('deleteFolder','deleteFolder');
       	$this->registerTask('up','upFolder');
       	$this->registerTask('moveTo','moveTo');
       	$this->registerTask('openFolder','openFolder');
		$session = &JFactory::getSession();
		$session->set( 'selected_mvd', $_REQUEST['name'] );
    }
    /**
     * Move up the folder hierarchy if possible 
     */
    function upFolder()
    {
		$folderId = $_REQUEST["folderid"];
		$model = $this->getModel();
		$folderId = $model->upFolder( $folderId );
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		$view = &$this->getView($viewName,$viewType,"");
		$view->folderId = $folderId;
		$this->display();
    }
    /**
     * Create a new folder from submitted params. Don't change the 
 	 * current folderid.
     * @access public
     */
    function newFolder()
    {
		$folderName = $_REQUEST["NEW_FOLDER"];
		$parentId = $_REQUEST["folderid"];
		$model = $this->getModel();
		$model->newFolder( $folderName, $parentId );
		$this->display();
    }
    /**
     * Move the currently selected MVD to a new folder
     * @access public
     */
    function moveTo()
    {
		$folder = $_REQUEST["SELECT_FOLDER"];
		$mvd = $_REQUEST["name"];
		$model = $this->getModel();
		$model->moveTo( $mvd, $folder );		 
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
		if ( strcmp($viewName,"mvdlist") == 0 )
		{
			$view = &$this->getView($viewName,$viewType,"");
			$model = &$this->getModel("mvd");
			if ( $model != null )
			{
				$mvds = $model->getMvds( $view->folderId );
				$folders = $model->getAllFoldersAt( $view->folderId );
				$allFolders = $model->getAllFolderNames();
				$view->folderName = $model->getFolderName( $view->folderId );
				$view->folderPath = $model->getFolderPath( $view->folderId );
				$view->folderList = $allFolders;
				$rows = array();
				if ( $folders != null )
				{
					for ($i = 0; $i < sizeof($folders); $i++ )
					{
						$folders[$i]->kind = "folder";
						$rows[] = $folders[$i];
					}
				}
				if ( $mvds != null )
				{
					for ($i = 0; $i < sizeof($mvds); $i++ )
					{
						$mvds[$i]->kind = "work";
						$rows[] = $mvds[$i];
					}
				}
				$view->assignRef( 'rows', $rows );
			}
		}
        parent::display();
    }
    /**
     * Create a new MVD from the input parameters
     * @access public
     */
    function newMVD()
    {
        $model = $this->getModel();
		$model->newMVD( $_REQUEST["NEW_MVD"], 
			$_REQUEST["MVD_DESCRIPTION"], 
			$_REQUEST["MVD_ENCODING"],
			$_REQUEST["folderid"] );
		$this->display();
    }
    /**
     * Open the given folder
     * @access public
     */
    function openFolder()
    {
		$folder = $_REQUEST["foldername"];
        $model = $this->getModel();
		$folderId = $model->getFolderId( $folder );
		$document = &JFactory::getDocument();
		$viewType = $document->getType();
		$viewName = JRequest::getCmd( 'view', $this->getName() );
		if ( strcmp($viewName,"mvdlist") == 0 )
		{
			$view = &$this->getView($viewName,$viewType,"");
			$view->folderId = $folderId;
		}
		$this->display();
    }
    /**
     * Redirect to edit view
     * @access public
     */
    function openMVD()
    {
		$session = &JFactory::getSession();
		$session->set( 'selected_mvd', $_REQUEST['name'] );
		parent::setRedirect("http://".
			$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].
			"?option=com_mvd&view=MVDSingle&name=".$_REQUEST['name']
			."&version1=".$_REQUEST['version1']);
	 	parent::redirect();  
    }
    /**
     * Edit the MVD specified in the input parameters
     * @access public
     */
    function editMVD()
    {
		$session = &JFactory::getSession();
		$session->set( 'selected_mvd', $_REQUEST['name'] );
        parent::setRedirect("http://".
			$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].
			"?option=com_mvd&view=MVDEdit&name=".$_REQUEST['name']
			."&version1=".$_REQUEST['version1']);
		parent::redirect();  
    }
    /**
     * Delete the specified MVD via SQL
     * @access public
     */
    function deleteMVD()
    {
		if ( $_REQUEST["name"] )
		{
			$model = $this->getModel();
			$model->deleteMVD( $_REQUEST["name"] );
		}
		$this->display();
    }
    /**
     * Delete the specified folder and all its contents
     * @access public
     */
    function deleteFolder()
    {
		if ( $_REQUEST["name"] )
		{
			$model = $this->getModel();
			$model->deleteFolder( $_REQUEST["name"] );
		}
		$this->display();
    }
}
