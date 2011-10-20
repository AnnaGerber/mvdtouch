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
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_SITE.DS
	.'components'.DS.'com_mvd'.DS.'utils'); 
require_once('IconButton.php');
jimport( 'joomla.application.component.view');
/**
 * List view class for the MVD Component
 */
class MvdViewMvdlist extends JView
{
    function __construct () 
    {
       	parent::__construct();
		$this->topLevel = 1;
		if ( isset($_REQUEST["folderid"]) )
			$this->folderId = $_REQUEST["folderid"];
		else
			$this->folderId = 1;
        $this->firstColHead = JText::_('WORK');
        $this->secondColHead = JText::_('DESCRIPTION');
        $this->newMvdButton = JText::_('NEW_MVD_BUTTON');
        $this->newFolderButton = JText::_('NEW_FOLDER_BUTTON');
        $this->upButton = JText::_('UP_BUTTON');
        $this->openButton = JText::_('OPEN_BUTTON');
        $this->editButton = JText::_('EDIT_BUTTON');
        $this->deleteButton = JText::_('DELETE_BUTTON');
        $this->moveToButton = JText::_('MOVE_TO_BUTTON');
		$this->nameMVDTooltip = JText::_( 'NAME_FIELD_MVD_TOOLTIP' );
		$this->descMVDTooltip = JText::_( 'DESC_FIELD_MVD_TOOLTIP' );
		$this->encodingTooltip = JText::_( 'ENCODING_FIELD_TOOLTIP' );
		$this->nameFolderTooltip = JText::_( 'NAME_FIELD_FOLDER_TOOLTIP' );
		$this->descFolderTooltip = JText::_( 'DESC_FIELD_FOLDER_TOOLTIP' );
		$this->folderPopupTooltip = JText::_('FOLDER_POPUP_TOOLTIP' );
		$this->newFileButtonTooltip = JText::_('NEW_FILE_BUTTON_TOOLTIP' );
		$this->newFolderButtonTooltip = JText::_('NEW_FOLDER_BUTTON_TOOLTIP' );
		$this->moveToButtonTooltip = JText::_('MOVE_TO_BUTTON_TOOLTIP' );
		$this->upButtonTooltip = JText::_('UP_BUTTON_TOOLTIP' );
		$this->openButtonTooltip = JText::_('OPEN_BUTTON_TOOLTIP' );
		$this->editButtonTooltip = JText::_('EDIT_BUTTON_TOOLTIP' );
		$this->deleteButtonTooltip = JText::_('DELETE_BUTTON_TOOLTIP' );
		$this->folderDescription = JText::_('FOLDER_DESCRIPTION' );
		$this->deletePrompt = JText::_('DELETE_PROMPT' );
		$this->deletePromptFolderExtra = JText::_('DELETE_PROMPT_FOLDER_EXTRA' );
		$this->moveToPrompt = JText::_('MOVE_TO_PROMPT' );
		$this->namePrompt = JText::_('NAME_PROMPT' );
		$this->tableHeight = 155;
    }
    function display($tpl = null)
    {
		jimport( 'joomla.application.pathway' );
		$menu = &JPathway::getInstance('site'); 
		$path = $this->folderPath;
		for ( $i=sizeof($path)-1;$i>=0;$i-- )
		{
			$link = JRoute::_(
				"index.php?option=com_mvd&view=mvdlist&folderid=".$path[$i]->folderId);
			$menu->addItem( $path[$i]->name, $link );
		}
		// set current item
		$menu = &JSite::getMenu();
		$menu->setActive(0);
		parent::display($tpl);
    }
	/**
	 *	Debug function, since print_r writes to stdout
	 *	@return a string representation of the object
	 */
	function toString()
	{
		return "topLevel=".$this->topLevel.
		" folderId=".$this->folderId.
		" firstColHead=".$this->firstColHead.
        " secondColHead=".$this->secondColHead.
        " newMvdButton=".$this->newMvdButton.
        " newFolderButton=".$this->newFolderButton.
        " upButton=".$this->upButton.
        " openButton=".$this->openButton.
        " editButton=".$this->editButton.
        " deleteButton=".$this->deleteButton.
        " moveToButton=".$this->moveToButton.
		" nameMVDTooltip=".$this->nameMVDTooltip.
		" descMVDTooltip=".$this->descMVDTooltip.
		" encodingTooltip=".$this->encodingTooltip.
		" nameFolderTooltip=".$this->nameFolderTooltip.
		" descFolderTooltip=".$this->descFolderTooltip.
		" folderPopupTooltip=".$this->folderPopupTooltip.
		" newFileButtonTooltip=".$this->newFileButtonTooltip.
		" newFolderButtonTooltip=".$this->newFolderButtonTooltip.
		" moveToButtonTooltip=".$this->moveToButtonTooltip.
		" upButtonTooltip=".$this->upButtonTooltip.
		" openButtonTooltip=".$this->openButtonTooltip.
		" editButtonTooltip=".$this->editButtonTooltip.
		" deleteButtonTooltip=".$this->deleteButtonTooltip.
		" folderDescription=".$this->folderDescription.
		" deletePrompt=".$this->deletePrompt.
		" deletePromptFolderExtra=".$this->deletePromptFolderExtra;
	}
	/**
	 * Add an inco-button the the output
	 * @param iconPath the name of the icon file
	 * @param title the localised button title
	 * @param script the script to invoke on clicking the button
	 * @param disabled disable the button (to be activated later)
	 * @param id the id of the button or an empty string
	 * @param submit if 'true' make it a submit button
	 */ 
	function addToolbarButton( $icon, $title, $script, $disabled, $id, $submit )
	{
		//$component = substr(JPATH_COMPONENT,strlen(JPATH_ROOT),
		//	strlen(JPATH_COMPONENT)-strlen(JPATH_ROOT));
		// hardcode to make paths work on windows
		$component = 'components/com_mvd';
		$path = $component.'/views/graphics/'.$icon;
		$newButton = $title.':'.$path.':'.$script.':'.$submit.':'.$id.':'.$disabled;
		$iconButton = new IconButton($newButton);
		echo $iconButton->toHTML().'&nbsp;';
	}
}
