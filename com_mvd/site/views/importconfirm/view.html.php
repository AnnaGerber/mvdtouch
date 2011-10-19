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
 
class MvdViewImportConfirm extends JView
{
    function __construct () 
    {
       	parent::__construct();
		$this->confirmMessage = JText::_('CONFIRM_MESSAGE');
		$this->abortMessage = JText::_('ABORT_MESSAGE');
		$this->commitButton = JText::_('COMMIT_BUTTON' );
		$this->abortButton = JText::_('ABORT_BUTTON' );
		$this->versionHead = JText::_('VERSION_HEAD');
		$this->shortNameHead = JText::_('SHORT_NAME_HEAD');
		$this->longNameHead = JText::_('LONG_NAME_HEAD');
		$this->fileHead = JText::_('FILE_HEAD');
		$this->percentHead = JText::_('PERCENT_HEAD');
		$this->percentLimit = (float)JText::_('PERCENT_LIMIT');
		$session = &JFactory::getSession();
		$files = $session->get( 'FILELIST' );
		if ( $files )
		{
			$this->filelist = array();
			$filearray = explode( ';',$files );
			for ( $i=0;$i<count($filearray);$i++ )
			{
				$contents = explode(':',$filearray[$i]);
				if ( count($contents)==5 )
				{
					$file = array();
					$file['version'] = $contents[0];
					$file['short_name'] = $contents[1];
					$file['long_name'] = $contents[2];
					$file['name'] = $contents[3];
					$file['percent'] = $contents[4];
					$this->filelist[] = $file;
				}
				else // debug
				{
					$this->filelist = array();
					$file = array();
					$file['version'] = "number";
					$file['short_name'] = "of";
					$file['long_name'] = "elements";
					$file['name'] = "was";
					$file['percent'] = "not 5 but ".count($contents);
					$this->filelist[] = $file;
				}
			}
		}
	}
    function display($tpl = null)
    {
		jimport( 'joomla.application.pathway' );
		$menu = &JPathway::getInstance('site'); 
		$link = JRoute::_( "index.php?option=com_mvd&view=import");
		$menu->addItem( "Import", $link );
		$menu->addItem( "Confirm", "" );
		parent::display($tpl);
    }
}
