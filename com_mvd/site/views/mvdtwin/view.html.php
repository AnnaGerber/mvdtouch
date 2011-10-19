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
 
class MvdViewMvdtwin extends JView
{
	public $html1;
	public $html2;
	public $vt;
	public $version1;
	public $version2;
	/**
	 * Set the version table that will be shared by both columns
	 * @param vt a version-table object from the model
	 */
	function setVersionTable( $vt )
	{
		$this->vt = $vt;
	}
	/**
	 * The text for this view is changing or is being set for 
	 * the first time. We must convert the TEI-XML into HTML.
	 * @param xml the xml for the side
	 * @param versionId the version id of the side
	 * @param side the side to set txt for
  	 */
	function setText( $xml, $versionId, $side )
	{
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load( JPATH_BASE.DS."components".DS."com_mvd"
			.DS."xsl".DS."formats.xsl" );
		$xp->importStylesheet( $xsl );
		$xmlDoc = new DomDocument;
		if ( $side == 1 )
		{
			$xmlDoc->loadXML( $xml );
			$this->version1 = $versionId;
			$this->html1 = $xp->transformToXML($xmlDoc);
			//$this->html1 = trim( $this->html1 );
		  	if ( !$this->html1 )
				error_log("<p>Transform 1 failed</p>");
		}
		else // side 2
		{
			$xmlDoc->loadXML( $xml );
			error_log("Length of xml=".strlen($xml));
			$this->version2 = $versionId;
			$this->html2 = $xp->transformToXML($xmlDoc);
			//$this->html2 = trim( $this->html2 );
		  	if ( !$this->html2 )
				error_log("<p>Transform 2 failed</p>");
		}
	}
	/**
	 * Write a left or right column
	 * @param side number of the side
	 * @param sideName name of the side, i.e. 'left' or 'right'
	 * @param pattern pattern if any to search for
	 * @param version version id of the text
	 * @param html the content of the side's main text
	 */ 
	function writeColumn( $side, $sideName, $pattern, $version, $html )
	{
		echo '<div id="'.$sideName.'Column">';
		// information box 
		$vthtml = $this->vt->toHTMLSelect( $version, 
			'versionPopup'.$side, 'popup'.$side );
		$longName = $this->vt->getLongNameFor($version);
		$info_params = array(
			'version'=>$_REQUEST['version'.$side],
			'popupId'=>'versionPopup'.$side,
			'vthtml'=>$vthtml,
			'mvd'=>$_REQUEST['name'],
			'longName'=>$longName,
			'infoboxId'=>$side,
			'jsFunctionName'=>'popup'.$side
		);
		echo LoadModule::getModule("mod_infobox",$info_params);
		// main display box
		echo '<div id="displaybox'.$side.'">';
		echo $html;
		echo '</div>';
		// searchbox without windowbox button
		$component = substr(JPATH_COMPONENT,strlen(JPATH_ROOT),
			strlen(JPATH_COMPONENT)-strlen(JPATH_ROOT));
		$searchPath= $component.DS.'views'.DS.'graphics'.DS.'Search24.gif';
		$searchAllPath= $component.DS.'views'.DS.'graphics'.DS.'SearchAll24.gif';
		$search_params = array(
			'pattern'=>$_REQUEST['pattern'.$side],
			'searchboxId'=>$side,
			'searchButton'=>JText::_('SEARCH_BUTTON').':'.$searchPath
				.':dosearch'.$side.'(\'search\'):true::',
			'searchAllButton'=>JText::_('SEARCHALL_BUTTON').':'
				.$searchAllPath.':dosearch'.$side.'(\'searchall\'):true::',
		);
		echo LoadModule::getModule("mod_searchbox",$search_params); 
		echo '</div>';
	}
	/**
	 * Just call the superclass
	 */
	function display( $tpl = null )
    {
        parent::display($tpl);
    }
}
