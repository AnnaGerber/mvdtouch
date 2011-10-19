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
defined( '_JEXEC' ) or die( 'Restricted access' );
/**
 * Format a string of variants from nmerge into a HTML fragment
 */
class HTMLVariantFormatter
{
	/* array of variants to format */
	private $vars;
	/** corresponding array of their sigla */
	private $sigla;
	/** array of conversions between src XML and HTML */
	private $rules;
	/**
	 * The raw output of the nmerge variants command is a 
	 * square-bracketed list of variants. Each variant begins with 
	 * a list of sigla, followed by a colon, which is the first in the 
	 * string. These are separated out for later inclusion in the HTML.
	 * The contents of each variant may contain unmatched or 
	 * unfinished XML tags. The first task is to complete and match 
	 * the tags. Finally we convert the repaired content into HTML.
	 * @param variants the raw output of the nmerge variants command
	 */
	function __construct( $variants )
	{
		$this->vars = explode( "]\n[", $variants );
		$this->sigla = array();
		$this->rules = array(
			'<hi rend="italic">'=> '<span class="italic">',
			'</hi>'=>'</span>',
			'</l>'=>' ',
			'<l>'=>'\0'
		);
		$root = $this->buildTree();
		//$this->printTree( $root );
		//die("Printed tree");
		$numVars = count($this->vars);
		for ( $i=0;$i<$numVars;$i++ )
		{
			if ( $i == 0 )
				$this->vars[0] = ltrim($this->vars[0],"[");
			if ( $i == $numVars-1 )
				$this->vars[$i] = rtrim($this->vars[$i],"]\n");
			$var = $this->vars[$i];
			$dotpos = strpos($var,":");
			$this->sigla[] = substr($var,0,$dotpos);
			$text = substr( $var, $dotpos+1, strlen($var)-($dotpos+1) );
			$repaired = $this->repair($text);
			$this->vars[$i] = $this->format( $root, $repaired );
		}
	}
	/**
	 * Go through the variant looking for opening or closing tags. 
	 * Read only the tag name. If you encounter a closing tag without 
	 * a previous opening tag, prepend an empty opening-tag of that 
	 * kind to the string. If you encounter a part-opening tag at 
	 * the end, remove it.
	 * @param var the variant to repair
	 * @return the same variant, with tags repaired
	 */
	function repair( $var )
	{
		$state = 0;
		$i = 0;
		$len = strlen( $var );
		$uflTag = false;
		$stack = array();
		$queue = new Queue();
		while ( $i < $len )
		{
			if ( $var[$i] == '>' )
			{
				$uflTag = true;
				$i++;
			}
			else if ( $var[$i] == '<' )
			{
				$add = $this->readTag( $var, $i, $stack, $queue );
				// partial opening tag on right
				if ( $add < 0 )
				{
					$len += $add;
					$var = substr( $var, 0, $len );
					$i = $len;
				}
				else
					$i += $add;
			}
			else
				$i++;
		}
		if ( $uflTag )
		{
			$eTag = $queue->pop();
			if ( $eTag != null )
			{
				$half = $eTag->makePartialStart();
				if ( $var[0] != '>' )
					$var = " ".$var;
				$var = $half.$var;
			}
			else	// no matching end-tag
				$var = substr( $var, strpos($var,'>')+1 );
		}
		while ( count($stack) > 0 )
		{
			$sTag = array_pop($stack);
			$var = $var.$sTag->makeEnd();
		}
		while ( $queue->size() > 0 )
		{
			$eTag = $queue->pop();
			// this should NEVER be null, but it often is - why?
			$var = $eTag->makeStart().$var;
		}
		return $var;
	}
	/**
	 * Read a tag in the string. We don't check syntax: it must 
	 * already be correct.
	 * @param string the string to parse
	 * @param i the index to start reading from
	 * @param stack a stack of unmatched start tags
	 * @param queue a queue of unmatched end-tags
	 */
	function readTag( $string, $i, &$stack, &$queue )
	{
		$start = $i;
		$len = strlen( $string );
		$state = 0;
		$tag = new Tag();
		$start = $i;
		while ( $i < $len && $state >= 0 )
		{
			switch ( $state )
			{
				case 0:	// starting tag
					if ( $string[$i] == '<' )
						$state = 1;
					break;
				case 1:	// checking if start or end tag
					if ( $string[$i] == '/' )
						$tag->setEnd();
					else
						$tag->appendName( $string[$i] );
					$state = 2;
					break;
				case 2:	// matching name
					if ( $string[$i] == '>' )
					{
						if ( $tag->isEnd() )
						{
							$stag = array_pop( $stack );
							if ( $stag == null )
							{
								$queue->push( $tag );
							}
						}
						else 
							array_push( $stack, $tag );
						$state = -1;
					}
					else if ( $string[$i] == ' '||$string[$i] == '\n' )
						$state = 3;
					else
						$tag->appendName( $string[$i] );
					break;
				case 3:	// skipping over attributes
					if ( $string[$i] == '>' )
					{
						if ( $tag->isEnd() )
						{
							$stag = array_pop($stack);
							if ( $stag == null )
								$queue->push( $tag );
						}
						else 
							array_push( $stack, $tag );
						$state = -1;
					}
					break;
			}
			$i++;
		}
		if ( $state == 3 )
			// ran off the end reading a tag: 
			// indicate how many chars to truncate
			return -($len - $start);
		else
			return $i - $start;
	}
	/**
	 * Convert a variant into HTML. The method is to 
	 * take a table of formats, convert it into a generalised 
	 * suffix tree. Then iterate through the characters of the 
	 * variant text, and when you get a longest match, substitute 
	 * the match for its replacement.
	 * @param root the root of the suffix tree of rules
	 * @param var the variant to format
	 * @return the formatted variant
	 */
	private function format( $root, $var )
	{
		$len = strlen( $var );
		// position of first matching character
		$first = true;
		$i = 0;
		$node = $root;
		while ( $i<$len )
		{
			$node = $node->match($var[$i]);
			if ( $node != null )
			{
				//echo "matched: ".$var[$i],"<br>";
				if ( $first )
				{
					$start = $i;
					$first = false;
				}
				if ( $node->isLeaf() )
				{
					//die("It's a (tea)leaf!");
					//echo "Matched ".$this->escape($node->payload)."<br>";
					$matchLen = ($i-$start)+1;
					$left = substr( $var, 0, $start );
					$right = substr( $var, $i+1, $len-$matchLen );
					$payload = ($node->payload=='\0')?"":$node->payload;
					$var = $left.$payload.$right;
					$len = strlen( $var );
					$i = $start + strlen($payload);
				}
				else
					$i++;
			}
			else
			{
				//echo "mis-matched: ".$var[$i],"<br>";
				$node = $root;
				$first = true;
				$i++;
			}
		}
		return $var;
	}
	/**
	 * Construct a generalised suffix tree from the rules
	 */
	private function buildTree()
	{
		$root = new Node('');
		$i=0;
		foreach ( $this->rules as $pattern=>$payload )
		{
			$root->insert( $pattern, $payload );
		}
		return $root;
	}
	/**
	 * This is a debug function
	 * @param root the root containing no value itself
	 */
	private function printTree( $root )
	{
		$temp = $root;
		echo "Node:";
		while ( $temp != null )
		{
			echo $temp->toString();
			if ( $temp->next != null )
				echo ",";
			$temp = $temp->next;
		}
		echo "<br>";
		$temp = $root;
		while ( $temp != null )
		{
			if ( $temp->children != null )
				$this->printTree($temp->children );
			$temp = $temp->next;
		}
		echo "\n";
	}
	/**
	 * Convert the vars array into a single string
	 * @return a HTML fragment
	 */
	public function toString()
	{
		//$text = '<style type="text/css">span.sigla{font-style:italic}</style>';
		$text = "";
		for ( $i=0;$i<count($this->vars);$i++ )
		{
			$text .= '<span class="sigla">'.$this->sigla[$i]
				.'</span>'.": ".$this->vars[$i] . " ";
		}
		return $text;
	}
	/**
	 * Debug routine: replace angle-brackets with entities
	 * @param text the text to escape
	 * @return a string with escaped angle brackets suitable for HTML
	 */
	function escape( $text )
	{
		$text = str_replace( "<", "&lt;", $text );
		$text = str_replace( ">", "&gt;", $text );
		return $text;
	}
}
/** 
 * Node in a suffix tree. Simple implementation.
 */
class Node
{
	/** actual character value of this node */
	private $value;
	/** subordinate nodes down the tree */
	public $children;
	/** first sibling (if any) of this node */
	public $next;
	/** the string to convert the match into */
	public $payload;
	/**
	 * Construct a new node
	 */
	function __construct( $value )
	{
		$this->value = $value;
	}
	/**
	 * Add a string to the suffix tree by finding the 
	 * matching node amongst the siblings of its children. 
	 * @param string the string to convert into a suffix-tree
	 * @param payload the payload to go on the leaf
	 */
	function insert( $string, $payload )
	{
		// echo "$string,";
		$char = $string[0];
		$string = substr( $string, 1 );
		if ( $this->children == null )
			$this->children = new Node( $char );
		$sibling = $this->children;
		while ( $sibling != null )
		{
			if ( $sibling->value == $char )
				break;
			else
				$sibling = $sibling->next;
		}
		// no match: add a new sibling
		if ( $sibling == null )
		{
			$sibling = $this->children;
			while ( $sibling->next != null )
				$sibling = $sibling->next;
			$sibling->next = new Node( $char );
			$sibling = $sibling->next;
		}
		// sibling matches char: recurse
		if ( strlen($string) > 0 )
		{
			//echo "recursing string=$string<br>";
			$sibling->insert( $string, $payload );
		}
		else	// leaf node
		{
			//$escaped = $this->escape( $payload );
			//echo "installing leaf ".$sibling->value." with payload=$escaped<br>";
			$sibling->payload = $payload;
		}
	}
	/**
	 * Debug routine: replace angle-brackets with entities
	 * @param text the text to escape
	 * @return a string with escaped angle brackets suitable for HTML
	 */
	function escape( $text )
	{
		$text = str_replace( "<", "&lt;", $text );
		$text = str_replace( ">", "&gt;", $text );
		return $text;
	}
	/** 
	 * Are we a leaf node?
	 * @return true if we have a payload
	 */
	function isLeaf()
	{
		return $this->payload != null;
	}
	/**	
	 * Get the payload for the conversion
	 * @return a string being the replacement text
	 */
	function getPayload()
	{
		return $this->payload;
	}
	/**
	 * Search for a character amongst the children of a node
	 * @return the first matching child or null
	 */
	function match( $char )
	{
		$child = $this->children;
		while ( $child != null )
		{
			//if ( $char == '>' )
			//	echo "this->value=".$this->value." child->value=".$child->value."<br>";
			if ( $child->value == $char )
				break;
			else
				$child = $child->next;
		}
		return $child;
	}
	/**
	 * Convert to a string for debugging.
	 * @return a string
	 */
	function toString()
	{
		return "(".$this->value.")";
	}
}
/**
 * Simple auxillary class to assist parsing. Tags are queued.
 */
class Queue
{
	private $items;
	public function __construct()
	{
		$this->items = array();
	}
	/**
	 * Add an item to the front of this queue
	 * @param item the item to queue
	 */
	function push( $item )
	{
		for ( $i=count($this->items);$i>0;$i-- )
			$this->items[$i] = $this->items[$i-1];
		$this->items[0] = $item;
	}
	/**
	 * Remove an element from the END of the queue
	 * @return a tag
	 */
	function pop()
	{
		return array_pop( $this->items );
	}
	/**
	 * Get the length of this queue
	 * @return the number of elements in the queue
	 */
	function size()
	{
		return count( $this->items );
	}
}
/**
 * Simple representation of an XML tag. Attributes are ignored.
 */
class Tag
{
	/** tag-name */
	private $name;
	/** flag true for end-tag else start-tag */
	private $end;
	/** basic constructor */
	function __construct()
	{
		$this->end = false;
		$this->name = "";
	}
	/**
	 * Append a character to the tag's name
	 * @param c the character to append
	 */
	function appendName( $c )
	{
		$this->name .= $c;
	}
	/**
	 * Is this an end-tag?
	 * @return true if it is
	 */
	function isEnd()
	{
		return $this->end;
	}
	/**
	 * Record this tag as an end-tag 
	 */
	function setEnd()
	{
		$this->end = true;
	}
	/**
	 * Make the leading half of a tag that might have got 
	 * chopped off
	 * @return a half-tag
	 */
	function makePartialStart()
	{
		return "<".$this->name;
	}
	/**
	 * Make an end-tag
	 * @return an end-tag
	 */
	function makeEnd()
	{
		return "</".$this->name.">";
	}
	/**
	 * Make a start-tag
	 * @return a start-tag
	 */
	function makeStart()
	{
		return "<".$this->name.">";
	}
}
/*
// test code
$formatter = new HTMLVariantFormatter( '[Q1,Q2:purposes.</l>
<l>The]
[Q1:map]
[Q1,Q2:there; know]
[F2,F3:three,]
[F4:Kingdom:]
[Q1:kingdome:]
[Q1,Q2:tis]
[Q1,Q2:first]
[F2,F3,F4,Q1,Q2:cares]
[Q1:busines]
[F3:business]
[F2,F4,Q2:businesse]
[Q1,Q2:state,</l>
<l>Confirming]
[F3,F4,Q1,Q2:younger]
' );
echo $formatter->toString();
*/
