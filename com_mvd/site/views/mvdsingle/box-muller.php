<?php
$html = "The quick brown fox jumps of over the lazy dog";
$xml = "The very quick rabbit leaps over the very lazy dog";
$ylen = strlen($html);
$xlen = strlen($xml);
findLCS( $xml, $html, 0, 0, $xlen-1, $ylen-1 );
/**
 * Generate a pair of random numbers with a mean of 0.
 * They range by the normal distribution from the mean 
 * by 1 per standard deviation.
 */
function getrandpair( &$x1, &$x2 )
{
	do 
	{
		$r1 = 2.0 * rand()/getrandmax() - 1.0;
		$r2 = 2.0 * rand()/getrandmax() - 1.0;
		$w = $r1 * $r1 + $r2 * $r2;
	} while ( $w >= 1.0 );
	$w = sqrt( (-2.0 * log($w) ) / $w );
	$x1 = $r1 * $w;
	$x2 = $r2 * $w;
}
/**
 * Attempt to scale the normalised random numbers 
 * within half the given range
 * @param xr the random number, maybe negative, roughly 0-3
 * @param xlen length of the text to scale it over
 */
function scaled( $xr, $xlen )
{
	return round($xr/3.0*($xlen/2));
}/**
 * Recursively find the LCS in a given rectangle 
 * within the edit graph.
 * @param xtext the text along the x-axis
 * @param ytext the text along the y-axis
 * @param startx offset of the first char in xtext
 * @param starty offset of the first char in ytext
 * @param endx offset of the last char in xtext
 * @param endy offset of the last char in ytext
 */
function findLCS( &$xtext, &$ytext, $startx, $starty, $endx, $endy )
{
	$xlen = 1+$endx-$startx;
	$ylen = 1+$endy-$starty;
	if ( $xlen > $ylen )
		$this->findLCSHoriz( $xtext, $ytext, $startx, $starty, $endx, $endy );
	else
		$this->findLCSVert( $xtext, $ytext, $startx, $starty, $endx, $endy );
}
/**
 * Find the LCS when the x-text is longer than the y-text
 * We must keep track of which is which.
 * @param xtext the text along the x-axis
 * @param ytext the text along the y-axis
 * @param startx offset of the first char in xtext
 * @param starty offset of the first char in ytext
 * @param endx offset of the last char in xtext
 * @param endy offset of the last char in ytext
 */
function findLCSHoriz( &$xtext, &$ytext, $startx, $starty, $endx, $endy )
{
	$xlen = 1 + $endx - $startx;
	$ylen = 1 + $endy - $starty;
	$diff = ($xlen - $ylen)/2;
	$itersPerRow = round((0.05*$xlen)/2);
	if ( $itersPerRow < 1 )
		$itersPerRow = 1;
	for ( $y=$starty;$y<=$endy;$y++ )
	{
		for ( $j=0;$j<$itersPerRow;$j++ )
		{
			$xr1 = 0;
			$xr2 = 0;
			// generate normal distribution random values
			getrandpair($xr1,$xr2);
			$x = $diff+$y+scaled($xr1,$xlen-$diff);
			if ( $x >=$startx && $x <=$endx && $xtext[$x]==$ytext[$y] )
				findSnakes( $x, $y, $xtext, $ytext, $startx, $starty, $endx, $endy );
			$x = $diff+$y+scaled($xr2,$xlen-$diff);
			if ( $x >=$startx && $x <=$endx && $xtext[$x]==$ytext[$y] )
				findSnakes( $x, $y, $xtext, $ytext, $startx, $starty, $endx, $endy );
		}
	}
	$this->recurse( $xtext, $ytext, $startx, $starty, $endx, $endy );
}
/**
 * Find the LCS when the y-text is longer than the x-text
 * We must keep track of which is which.
 * @param xtext the text along the x-axis
 * @param ytext the text along the y-axis
 * @param startx offset of the first char in xtext
 * @param starty offset of the first char in ytext
 * @param endx offset of the last char in xtext
 * @param endy offset of the last char in ytext
 */
function findLCSVert( &$xtext, &$ytext, $startx, $starty, $endx, $endy )
{
	$xlen = 1 + $endx - $startx;
	$ylen = 1 + $endy - $starty;
	$diff = ($ylen - $xlen)/2;
	$itersPerCol = round((0.05*$ylen)/2);
	if ( $itersPerCol < 1 )
		$itersPerCol = 1;
	for ( $x=$startx;$x<=$endx;$x++ )
	{
		for ( $j=0;$j<$itersPerCol;$j++ )
		{
			$xr1 = 0;
			$xr2 = 0;
			// generate normal distribution random values
			getrandpair($xr1,$xr2);
			$y = $diff+$x+scaled($xr1,$ylen-$diff);
			if ( $y >=0 && $x <$xlen && $xtext[$x]==$ytext[$y] )
				findSnakes( $x, $y, $xtext, $ytext, $startx, $starty, $endx, $endy );
			$y = $diff+$x+scaled($xr2,$ylen-$diff);
			if ( $x >=0 && $x <$xlen && $xtext[$x]==$ytext[$y] )
				findSnakes( $x, $y, $xtext, $ytext, $startx, $starty, $endx, $endy );
		}
	}
	$this->recurse( $xtext, $ytext, $startx, $starty, $endx, $endy );
}
/**
 * Install the found snake and decide if we reurse further
 * @param xtext the text along the x-axis
 * @param ytext the text along the y-axis
 * @param startx offset of the first char in xtext
 * @param starty offset of the first char in ytext
 * @param endx offset of the last char in xtext
 * @param endy offset of the last char in ytext
 */
function recurse( &$xtext, &$ytext, $startx, $starty, $endx, $endy )
{
	if ( $maxlen >= 5 )
		$this->offsets[$this->besty] = $this->bestx;
	else
	{
		$this->bestx = $startx = round(($endx-$startx)/2);
		$this->besty = $starty = round(($endy-$starty)/2);
		$this->maxlen = 0;
	}
	// now recurse up
	$xsize = $this->bestx - $startx;
	$ysize = $this->besty - $starty;
	if ( $xsize > 5 && $ysize > 5 )
		findLCS( $xtext, $ytext, $startx, $starty, $bestx-1, $besty-1 );
	// and recurse down
	$xsize = 1 + $endx - ($this->bestx+$this->maxlen);
	$ysize = 1 + $endy - ($this->besty+$this->maxlen);
	if ( $xsize > 5 && $ysize > 5 )
	{
		findLCS( $xtext, $ytext, $this->bestx+$this->maxlen+1, 
			$this->besty+$this->maxlen+1, $endx, $endy );
	}
}
/**
 * Given a match at x,y try to extend it within the limits 
 * of start, end etc. Update the maximum snake if bigger 
 * than the previous one.
 * @param x the x-coordinate of the match
 * @param y the y-coordinate of the match
 * @param xtext the text along the x-axis
 * @param ytext the text along the y-axis
 * @param startx offset of the first char in xtext
 * @param starty offset of the first char in ytext
 * @param endx offset of the last char in xtext
 * @param endy offset of the last char in ytext
 */
function findSnakes( $x, $y, &$xtext, &$ytext, $startx, 
	$starty, $endx, $endy )
{
	$xx = $x;
	$yy = $y;
	$len = 0;
	// extend down
	while ( $xx<=$endx && $yy<=$endy 
		&& $xtext[$xx]==$ytext[$yy] )
	{
		$len++;
		$xx++;
		$yy++;
	}
	// extend up
	$posx = $x;
	$posy = $y;
	$xx = $x-1;
	$yy = $y-1;				
	while ( $xx>=$startx && $yy>=$starty 
		&& $xtext[$xx]==$ytext[$yy] )
	{
		$len++;
		// save last *matching* position 
		$posx = $xx;
		$posy = $yy;
		$xx--;
		$yy--;
	}
	if ( $len > $this->maxlen && $len >= 5 )
	{
		$this->maxlen = $len;
		$this->bestx = $posx;
		$this->besty = $posy;
	}
}
?>
