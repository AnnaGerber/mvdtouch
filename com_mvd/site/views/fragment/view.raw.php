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
require_once('LoadModule.php');
jimport( 'joomla.application.component.view');
 
/**
 * Compare view class for the MVD Component
 */
 
class MvdViewFragment extends JView
{
	public $html1;
	public $vt;
	public $version1;
    function setVersionTable( $vt )
	{
		$this->vt = $vt;
	}
	/**
	 * The text for this view is changing or is being set for 
	 * the first time. We must convert the TEI-XML into HTML.
	 * @param xml the xml for the side
	 * @param versionId the version id of the side
  	 */
	function setText( $xml, $versionId)
	{
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load( JPATH_BASE."/components/com_mvd/xsl/formats.xsl" );
		$xp->importStylesheet( $xsl );
		$xmlDoc = new DomDocument;
		
		$xmlDoc->loadXML( $xml );
		$this->version1 = $versionId;
		$this->html1 = $xp->transformToXML($xmlDoc);
	  	if ( !$this->html1 )
			error_log("<p>Transform 1 failed</p>");
		
		
	}

	function writeColumn($html )
	{
		echo $html;
	}
	/**
	 * Just call the superclass
	 */
	function display( $tpl = null )
    {
        parent::display($tpl);
    }
}
