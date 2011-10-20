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

/**
 * Versions view class for the MVD Component
 */
class MvdViewVersions extends JView
{
	public $vt;
	public $html;
	public $name;
	/**
	 * Set this object's version table
	 * @param vt the version table read from the MVD
	 */
	function setVersionTable( &$vt )
	{
		$this->vt = $vt;
		$this->name = $vt->getName();
	}
	/**
	 * Nothing special to do
 	 */
    function display($tpl = null)
    {
		$xp = new XSLTProcessor();
		$xsl = new DomDocument;
		$xp->setParameter("","collapsedimage",JURI::base(true)."/components/com_mvd/views/graphics/Forward16.gif");
		$xp->setParameter("","expandedimage",JURI::base(true)."/components/com_mvd/views/graphics/Down16.gif");
		$user =& JFactory::getUser();
		if ( $user->usertype=="Super Administrator" 
			|| $user->usertype=="Editor" )
		{
			$xsl->load( JPATH_BASE."/components/com_mvd/xsl/edit-versions.xsl" );
		}
		else
		{
			$xsl->load( JPATH_BASE."/components/com_mvd/"
				."xsl/view-versions.xsl" );
		}
		$xp->importStylesheet( $xsl );
		$xmlDoc = new DomDocument;
		$xmlDoc->loadXML( $this->vt->toXML() );
		$this->html = $xp->transformToXML($xmlDoc);
		parent::display($tpl);
    }
}
