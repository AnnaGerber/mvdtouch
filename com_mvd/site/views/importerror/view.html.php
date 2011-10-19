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
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * Import control page for the MVD Component
 */
 
class MvdViewImportError extends JView
{
    function __construct () 
    {
       	parent::__construct();
		$this->errorMessage = JText::_('ERROR_MESSAGE');
		$this->okayButton = JText::_('OKAY_BUTTON' );
		$session = &JFactory::getSession();
		$this->errorReport = $SESSION->GET( 'ERROR' );
	}
    function display($tpl = null)
    {
		jimport( 'joomla.application.pathway' );
		$menu = &JPathway::getInstance('site'); 
		$link = JRoute::_( "index.php?option=com_mvd&view=import");
		$menu->addItem( "Import", $link );
		$menu->addItem( "Error", "" );
		parent::display($tpl);
    }
}
