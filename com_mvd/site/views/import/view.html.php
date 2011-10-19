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

// no direct access
// browse view
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_SITE.DS
	.'components'.DS.'com_mvd'.DS.'utils'); 
require_once('Progress.php');

/**
 * Import control page for the MVD Component
 */
 
class MvdViewImport extends JView
{
    function __construct () 
    {
       	parent::__construct();
		$this->importPrompt = JText::_('IMPORT_MESSAGE' );
		$this->importTo = JText::_('IMPORT_TO' );
		$this->importButton = JText::_('IMPORT_BUTTON' );
		$parts = explode( DS, JPATH_BASE );
		$this->base = $parts[count($parts)-1];
		$session = &JFactory::getSession();
		$this->selectedMvd = $session->get( 'selected_mvd' );
		$this->progressId = uniqid();
		if ( !$this->progressId )
			die( "Died in MvdViewImport");
		$progress = new Progress( $this->progressId );
		$progress->set( 0 );
	}
    function display($tpl = null)
    {
		jimport( 'joomla.application.pathway' );
		$menu = &JPathway::getInstance('site'); 
		$pathNames = $menu->getPathwayNames();
		if ( count($pathNames) == 1 )
		{
			$menu->addItem( "Import", "" );
		}
        parent::display($tpl);
    }
}
