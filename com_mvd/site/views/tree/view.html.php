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
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
set_include_path(get_include_path().PATH_SEPARATOR.JPATH_SITE.DS
	.'components'.DS.'com_mvd'.DS.'utils'); 
require_once('IconButton.php');

/**
 * Tree view class for the MVD Component
 */
 
class MvdViewTree extends JView
{
	public $name;
	public $imageUrl;
	public $font;
	public $uselengths;
	public $labelsize;
	public $improvement;
    function __construct () 
    {
       	parent::__construct();
		$this->lengthsLabel = JText::_('LENGTHS_LABEL');
		$this->labelSize = JText::_('LABEL_SIZE');
		$this->small = JText::_('SMALL');
		$this->medium = JText::_('MEDIUM');
		$this->large = JText::_('LARGE');
		$this->huge = JText::_('HUGE');
		$this->improvement = JText::_('IMPROVEMENT');
		$this->nBody = JText::_('NBODY');
		$this->none = JText::_('NONE');
		$this->equalDaylight = JText::_('EQUAL_DAYLIGHT');
		$this->zoomInTooltip = JText::_('ZOOMIN_TOOLTIP');
		$this->zoomOutTooltip = JText::_('ZOOMOUT_TOOLTIP');
		$this->none = JText::_('NONE');
		$this->nBody = JText::_('NBODY');
		$this->equalDaylight = JText::_('EQUAL_DAYLIGHT');
		$component = substr(JPATH_COMPONENT,strlen(JPATH_ROOT),
			strlen(JPATH_COMPONENT)-strlen(JPATH_ROOT));
		$this->image = JURI::base(true).DS.'components'.DS.'com_mvd'
			.DS.'views'.DS.'graphics'.DS.'plot.jpg';
		$session = &JFactory::getSession();
 	}
	/**
	 * Nothing special to do
 	 */
    function display($tpl = null)
    {
        parent::display($tpl);
    }
	/**
	 * Add an icon-button the the output
	 * @param iconPath the name of the icon file
	 * @param title the localised button title
	 * @param script the script to invoke on clicking the button
	 * @param disabled disable the button (to be activated later)
	 * @param id the id of the button or an empty string
	 * @param submit if 'true' make it a submit button
	 */ 
	function addToolbarButton( $icon, $title, $script, $disabled, $id, $submit )
	{
		$component = substr(JPATH_COMPONENT,strlen(JPATH_ROOT),
			strlen(JPATH_COMPONENT)-strlen(JPATH_ROOT));
		$path = $component.DS.'views'.DS.'graphics'.DS.$icon;
		$newButton = $title.':'.$path.':'.$script.':'.$submit.':'.$id.':'.$disabled;
		$iconButton = new IconButton($newButton);
		echo $iconButton->toHTML().'&nbsp;';
	}
	/**
	 * Set the name of the mvd
	 * @param name the name of the mvd
	 */
	function setName( $name )
	{
		$this->name = $name;
	}
	/**
	 * Set the name of the font
	 * @param font the name of the font
	 */
	function setFont( $font )
	{
		$this->font = $font;
	}
	/**
	 * Set whether lengths are to be used
	 * @param lengths 'true' if lengths are to be used
	 */
	function setLengths( $lengths )
	{
		$this->uselengths = $lengths;
	}
	/**
	 * Set what kind of tree improvement to use
	 * @param improvement the improvement to use (0-2 as string)
	 */
	function setImprovement( $improvement )
	{
		$this->improvement = $improvement;
	}
	/**
	 * Set the size of labels
	 * @param labelsize size of labels as a decimal string
	 */
	function setLabelSize( $labelsize )
	{
		$this->labelsize = $labelsize;
	}
	/**
	 * Set the image of the tree to display
	 * @param image the image recently computed
	 */
	function setImage( $image )
	{
		$rpos = strrpos( $image, DS );
		if ( $rpos )
			$image = substr( $image, $rpos+1 );
		$this->imageUrl = JURI::base()."tmp".DS.$image;
	}
	/**
	 * Print a select control to the HTML
	 * @param name the name of the select control
	 * @param values array of the values to return for each option
	 * @param value the current value of the select
	 * @param options array of the options
	 * @param action the script to activate on change
	 */
	function printSelect( $name, $values, $value, $options, $action )
	{
		$onChangeAttr = "";
		if ( $action )
			 $onChangeAttr = " onchange=\"$action\"";
		echo "<select name=\"$name\"$onChangeAttr>";
		for ( $i=0;$i<count($options);$i++ )
		{
			$valueAttr = "";
			if ( $values != null )
				$valueAttr = " value=\"".$values[$i]."\"";
			echo "<option".$valueAttr;
			if ( $values == null && $options[$i] == $value )
				echo " selected";
			else if ( $values[$i] == $value )
				echo " selected";
			echo ">".$options[$i]."</option>";
		}
		echo "</select>";
	}
}
