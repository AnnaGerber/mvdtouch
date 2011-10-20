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
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
define( "MIN_SNAKE", 5 );
// maximum number of samples per line
define( "MAX_SAMPLES", 20 );
// percent of points to sample per row/col
define( "PERCENT_RAND", 0.01 );
/**
 * Holder for snake data
 */
class Snake
{
	public $len;
	public $x;
	public $y;
	public $freq;
	/**
	 * Construct a Snake
	 * @param len length of the snake
	 * @param x coordinate in x-direction (xml)
	 * @param y coordinate in y-dicrection (html)
	 */
	function __construct( $len, $x, $y )
	{
		$this->len = $len;
		$this->x = $x;
		$this->y = $y;
		$this->freq = 1;
	}
	/**
	 * To find MUMs (maximal unique matches)
	 * record the frequency of this snake
	 */
	function inc()
	{
		$this->freq++;
	}
}
/**
 * Single version view class for the MVD Component
 */
class MvdViewMvdsingle extends JView
{
	public $html;
	public $vt;
	public $version;
	public $name;
	/** hash: html->xml offsets*/
	public $offsets;
	/**
	 * The text for this view is changing or is being set for 
	 * the first time. We must convert the TEI-XML into HTML.
	 * @param xml the xml from the version
	 * @param version the version id
	 * @param vt the version table for this mvd
 	 */
	function setText( $xml, $versionId, $name, $vt )
	{
		$this->vt = $vt;
		$this->version = $versionId;
		$this->name = $name;
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load( JPATH_BASE."/components/com_mvd/xsl/formats.xsl" );
		$xp->importStylesheet( $xsl );
		$xmlDoc = new DomDocument;
		$xmlDoc->loadXML( $xml );
		$this->html = $xp->transformToXML($xmlDoc);
		$this->html = trim( $this->html );
      	if ( !$this->html )
			echo "<p>Transform failed</p>";
		else
		{
			$this->offsets = array();
			$starttime = microtime(true);
			$htmlStarts = array();
			$xmlStarts = array();
			$table = array();
			error_log("Length of HTML=".strlen($this->html));
			$this->parse( $this->html, $htmlStarts, false );
			$this->parse( $xml, $xmlStarts, true );
			// get unique alignable strings in both html and xml
			foreach ( $htmlStarts as $key=>$value )
			{
				if ( $value->freq == 1 && $xmlStarts[$key] 
					&& $xmlStarts[$key]->freq == 1 )
				{
					$value->x = $xmlStarts[$key]->x;
					$this->offsets[$value->y] = $value->x;
				}
			}
			$endtime = microtime(true)-$starttime;
			//error_log("Time taken for LCS=".$endtime);
		}
	}
	/**
	 * Parse an XML or HTML file looking for text runs (snakes)
	 * @param text the text to parse
	 * @param snakes initially empty array to store the run-starts
	 * @param usex store offset in Snake's x var instead of y
	 */
	function parse( &$text, &$snakes, $usex )
	{
		$len = strlen($text);
		$state = 0;
		$start = 0;
		$length = 0;
		for ( $i=0;$i<$len;$i++ )
		{
			$tok = $text[$i];
			switch ( $state )
			{
				case 0:	// looking for '<'
					if ( $tok == '<' )
						$state = 2;
					else if ( $tok != ' ' && $tok != '\t' 
						&& $tok != '\n' && $tok != '\r' )
					{
						$state = 1;
						$length = 1;
						$start = $i;
					}
					break;
				case 1:	// reading text
					if ( $tok == '<' )
					{
						$key = trim(substr($text,$start,$length));
						if ($snakes[$key])
							$snakes[$key]->inc();
						else if ( $usex )
							$snakes[$key] = new Snake($length,$start,0);
						else
							$snakes[$key] = new Snake($length,0,$start);
						$state = 2;
					}
					else
						$length++;
					break;
				case 2:	// reading tag
					if ( $tok == '>' )
						$state = 0;
					break;
			}
		}
	}
	/**
	 * Just call the superclass
	 */
	function display($tpl = null)
    {
        parent::display($tpl);
    }
}

