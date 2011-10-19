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
/**
 * Class to parse the output of nmerge compare and search
 */
class ChunkHandler
{
	/** currently defined values of states */
	private $states = "";
	/* last written out value of states */
	private $old = "";
	/* true if we are currently parsing an XML tag */
	private $inTag = false;
	/** true if we are in text within an element */
	private $inText = false;
	/** current id of a chunk */
	private $id = "";
	/** output string being built */
	private $output = "";
	/** id for selection span */
	private $selid;
	/** overall length of the text part of all chunks */
	private $length;
	/** prefix for ids on this side */
	private $prefix;
	/** end of previously parsed text fragment (for utf-8) */
	private $utf8Handler;
	/** encoding of the text */
	private $encoding;
	/**
	 * Constructor
 	 * @param selid the selection id used to scroll to
	 * @param encoding the encoding of the text
	 */
	function __construct( $selid, $encoding )
	{
		$this->selid = $selid;
		if ( $encoding == "utf-8" )
			$this->utf8Handler = new Utf8Buffer();
		else
			$this->uft8Handler = false;
		$this->encoding = $encoding;
	}
	/**
	 * Parse a chunk from mvd compare.
	 * @param text the text to parse containing many chunks
	 * @param output the output string empty or part-constructed
	 * @param i index into text to start from
	 * @param len overall length of text
	 * @return number of characters of text consumed
	 */
	public function parse( &$text, $i, $len )
	{
		$start = $i;
		$this->id = "";
		$i += $this->parseHeader( $text, $i );
		// in case the last chunk is followed by a CR
		if ( $i < $len )
		{
			$i += $this->parseText( $text, $i );
		}
		return $i-$start;
	}
	/**
	 * Parse the header for a chunk. Set $states, $id
	 * @param text the text to parse from
	 * @param i the start-offset within text
	 * @return the number of characters consumed by the header
	 */
	private function parseHeader( &$text, $i )
	{
		$start = $i;
		$number1 = "";
		$number2 = "";
		$state = 0;
		$this->states = "";
		while ( $state >= 0 )
		{
			switch ( $state )
			{
				case 0:	// looking for '['
					if ( $text[$i] == '[' )
						$state = 1;
					break;
				case 1:	// reading chunk-states list
					if ( $text[$i] == ':' )
						$state = 2;
					else
						$this->states .= $text[$i];
					break;
				case 2:	// reading first number or text
					if ( $text[$i] == ':' )
					{
						$state = 3;
					}
					else if ( $text[$i] < '0' 
						|| $text[$i] > '9' )
					{
						$i -= strlen($number1)+1;
						$state = -1;
					}
					else
						$number1 .= $text[$i];
					break;
				case 3:	// reading second number or text
					if ( $text[$i] == ':' )
						$state = -1;
					else if ( $text[$i] < '0' 
						|| $text[$i] > '9' )
					{
						$i -= strlen($number2)+1;
						$state = -1;
					}
					else
						$number2 .= $text[$i];
					break;
			}
			$i++;
		}
		if ( $number1 && $number2 )
			$this->id = $number1."a";
		return $i - $start;
	}
	/**
	 * Parse the text-portion of a chunk.
	 * @param text the text to parse
	 * @param i index into text to start
	 * @return the number of characters consumed including the 
	 * trailing ]
	 */
	private function parseText( &$text, $i )
	{
		$start = $i;
		$state = 0;
		while ( $state >= 0 )
		{
			switch ( $state )
			{
				case 0:	// looking for '\'
					if ( $text[$i] == '\\' )
						$state = 1;
					else if ( $text[$i] == ']' )
						$state = -1;
					break;
				case 1:	// looking for '\\' or ']'
					if ( $text[$i] == '\\' || $text[$i] == ']' )
						$state = 0;
					break;
			}
			$i++;
		}
		$chunkLen = ($i-1)-$start;
		$this->mergeMatch( $text, null, $start, $i-1 );
		$this->length += $chunkLen;
		if ( $this->utf8Handler )
			$this->utf8Handler->update( $text, $start, $chunkLen );
		// include trailing square bracket in length consumed
		return $i-$start;
	}
	/**
	 * Merge a match into a single version which may already contain 
	 * state information. This happens when compare and search are combined. 
	 * Search without compare requires merging of matches with plain XML. 
	 * This method does all three.
	 * @param text the text of a version
	 * @param match a single found match to overlay or null
	 * @param i initial offset within text
	 * @param end last offset of trailing ']' or length of the text
	 */
	function mergeMatch( $text, $match, $i, $end )
	{
		$backslash = false;
		$start = $i;
		for ( ;$i<$end;$i++,$this->offset++ )
		{
			// check for match
			if ( $match )
			{
				// update states as required
				if ( $i == $match->offset+$match->length )
				{
					if ( !$this->states )
					{
						// substract match states from the last 
						// written out ones
						$diff = $this->old;
						for ( $j=0;$j<count($match->states);$j++ )
						{
							$diff = $this->subtract_state( 
								$diff, $match->states[$j] );
						}
						if ( $diff )
							$this->states = $diff;
						else
							$this->states = "none";
					}
					// else new states is already specified
				}
				else if ( $i==$match->offset )
				{
					for ( $j=0;$j<count($match->states);$j++ )
						$this->add_state( $match->states[$j] );
				}
			}
			// now states is up to date - process text
			if ( $text[$i] == '<' )
			{
				if ( $this->inText && $this->old )
				{
					$this->output .= '</ch>';
					if ( !$this->states ) 
						$this->states = $this->old;
					$this->old = "";
				}
				$this->inText = false;
				$this->inTag = true;
			}
			else if ( $text[$i] == '>' )
			{
				$this->inTag = false;
			}
			else if ( !$this->inTag && !$this->inText )
			{
				// we are entering text
				$ordVal = ord($text[$i]);
				if ( $ordVal != 8 && $ordVal != 32 
					&& $ordVal != 10 && $ordVal != 13 )
				{
					$this->inText = true;
				}
			}
			if ( $this->inText && $this->states 
				&& (!$this->utf8Handler 
				|| $this->utf8Handler->isValidPosition($text,$i,$start)) )
					$this->write_chunk_start( $text, $i );
			// process escaped ']'s
			if ( $text[$i] == '\\' )
				$backslash = true;
			else
			{
				$this->output .= $text[$i];
				$backslash = false;
			}
		}	
	}
	/**
	 * Write out the start of a chunk. Close any preceding 
	 * open chunk.
	 */
	private function write_chunk_start()
	{
		if ( $this->old )
		{
			$this->output .= '</ch>';
			$this->old = "";
		}
		if ( $this->states != 'none' )
		{
			$insert = '<ch type="'.$this->states.'"';
			if ( strstr($this->states,"found") )
			{
				//error_log("this->selid=".$this->selid);
				$insert .= ' selid="'.$this->selid.'"';
			}
			if ( $this->id )
			{
				$insert .= ' id="'.$this->prefix.$this->id.'"';
				$this->id = $this->incId($this->id);
			}
			$insert .= '>';
			$this->output .= $insert;
			$this->old = $this->states;
		}
		$this->states = "";
	}
	/**
	 * Merge a set of matches into a single version
	 * @param text the text of a version
	 * @param match a single found match
	 * @return the merged text
	 */
	function merge( $text, $match )
	{
		$this->offset = 0;
		$this->length = strlen($text);
		$this->mergeMatch( $text, $match, 0, $this->length );
		// close open chunk if present
		$this->closeTags();
		//$this->writeToFile( $this->output );
		return $this->getOutput();
	}
	/**
	 * Debug: write the text to a file. Don't use in production 
	 * copy!
	 * @param reference to the text in question
	 */
	private function writeToFile( &$text )
	{
		$myFile = "/tmp/dump".$this->prefix.".xml";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, $text);
		fclose($fh);
	}
	/**
	 * Convert the textual output of an nmerge compare to XML. 
	 * @param compare the compare text as output by nmerge
	 * @param prefix prepend this to each id in the XML
	 * @return the XML with embedded chunk markers
	 */
	public function compareToXML( &$compare, $prefix )
	{
		$i = 0;
		$n = strlen( $compare );
		$this->offset = 0;
		$this->length = 0;
		$this->prefix = $prefix;
		while ( $i < $n )
		{
			$i += $this->parse( $compare, $i, $n );
		}
		$this->closeTags();
		//$this->writeToFile( $this->output );
		return $this->getOutput();
	}
	/**
 	 * Close any open chunk tag at the end.
	 */
	private function closeTags()
	{
		if ( $this->old && $this->inText )
			$this->output .= '</ch>';
		$state = 0;
		$i = $outlen = strlen($this->output)-1;
		while ( $state >= 0 && $i >= 0 )
		{
			switch ( $state )
			{
				case 0:	// not yet in text, looking for '>'
					if ( $this->output[$i] == '>' )
						$state = 1;
					break;
				case 1:	// looking for '<'
					if ( $this->output[$i] == '<' )
						$state = 2;
					break;
				case 2: // maybe entering text
					if ( $this->output[$i] == '>' )
						$state = 1;
					else 
					{
						$ordVal = ord($this->output[$i]);
						if ( $ordVal != 8 && $ordVal != 10
							&& $ordVal != 13 && $ordVal != 32 )
						{
							$i++;
							$this->output = substr($this->output,0,$i)
								.substr($this->output,$i,($outlen-$i)+1);
							$state = -1;
						}
					}
					break;
			}
			$i--;
		}
	}
	/**
	 * Remove the given state from the states list if present
	 * @param old the states string to subtract from
	 * @param state the state to remove
	 * @return the new set of states as a string
	 */
	private function subtract_state( $old, $state )
	{
		$new_states = $old;
		$pos = strpos( $old, $state );
		if ( $pos >= 0 )
		{
			$prefix = substr($this->states,0,$pos-1);
			$suffix = substr($old,$pos+strlen($state));
			$new_states = $prefix.$suffix;
		}
		return $new_states;
	}
	/**
	 * Add the given state to the states list if not already present
	 * @param state the state to add
	 */
	private function add_state( $state )
	{
		$pos = strpos( $this->states, $state );
		if ( $pos === false )
		{
			if ( $this->states )
				$this->states .= ",";
			$this->states .= $state;
		}
	}
	/**
	 * Increment an id. Pop off the last letter, increment and 
	 * put it back on. If we go over 26 segments use doubled letters.
	 * @param id the id to increment
	 */
	private function incId( $id )
	{
		$len = strlen( $id );
		$suffix = substr( $id, $len-1 );
		$prefix = substr( $id, 0, $len-1 );
		if ( ord($suffix) == ord('z') )
		{
			$prefix .= $suffix;
			$suffix = 'a';
		}
		else
			$suffix = chr(ord($suffix)+1);
		return $prefix.$suffix;
	}
	/**
	 * Get the output string after processing
	 * @return a string
	 */
	public function getOutput()
	{
		return $this->output;
	}
}
/**
 * A queue of the last 3 chars of the previous text chunks. 
 * Store them in ord form, to make comparison easy.
 */
class Utf8Buffer
{
	public $prev;
	public $pprev;
	public $ppprev;
	/**
 	 * Construct the buffer
	 */
	function __construct()
	{
		$this->prev = false;
		$this->pprev = false;
		$this->ppprev = false;
	}
	/**
	 * Update the prevchars at the end of a chunk. 
	 * @param text reference to the overall text block
	 * @param start the start pos of this chunk in text
	 * @param len the length of the chunk
	 */
	function update( &$text, $start, $len )
	{
		if ( $len > 2 )
		{
			$this->ppprev = ord($text[($start+$len)-3]);
			$this->pprev = ord($text[($start+$len)-2]);
			$this->prev = ord($text[($start+$len)-1]);
		}
		else if ( $len > 1 )
		{
			$this->ppprev = $this->pprev;
			$this->pprev = ord($text[($start+$len)-2]);
			$this->prev = ord($text[($start+$len)-1]);
		}
		else if ( $len > 0 )
		{
			$this->ppprev = $this->pprev;
			$this->pprev = $this->prev;
			$this->prev = ord($text[($start+$len)-1]);
		}
	}
	/**
	 * Get the previous char to the current one even if we go 
	 * off the front of the current chunk.
	 * @param text the overall block of text from nmerge
	 * @param i index before which prevchar is sought
	 * @param start the start of the current chunk in text
	 * @return the previous character in the text or false
	 */
	private function prevChar( &$text, $i, $start )
	{
		if ( $i > $start )
			return ord($text[$i-1]);
		else if ( $i == $start )
			return $this->prev;
		else if ( $i+1 == $start )
			return $this->pprev;
		else if ( $i+2 == $start )
			return $this->ppprev;
		else
			return false;
	}
	/** 
	 * Ensure we don't split a multi-byte character. We will assume UTF-8 
	 * for this version. Actually, php5 doesn't really support unicode.
	 * @param text the text into which we wish to insert a tag
	 * @param i position in text before which the tag will go
	 * @param start the initial position within text for this chunk
	 * @return true if it is OK to insert a tag here
	 */
	function isValidPosition( &$text, $i, $start )
	{
		$prev = $this->prevChar( $text, $i, $start );
		$pprev = $this->prevChar( $text, $i-1, $start );
		$ppprev = $this->prevChar( $text, $i-2, $start );
		if ( $prev && ($prev&0xc0)==0xc0 )
			return false;
		else if ( $pprev && ($pprev&0xe0)==0xe0 )
			return false;
		else if ( $ppprev && ($ppprev&0xf0)==0xf0 )
			return false;
		else
			return true;
	}
}
