<?xml version="1.0" encoding="utf-8"?>
<!--
This file is part of MVD_GUI.

MVD_GUI is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

MVD_GUI is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with MVD_GUI. If not, see <http://www.gnu.org/licenses/>.
-->

<!-- This stylesheet contains the formats used on all pages -->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="html" encoding="utf-8"/>
<xsl:param name="collapsedimage"/>
<xsl:param name="expandedimage"/>
<xsl:template match="/version-table">
<html>
<head>
<style type="text/css">
ol.collapsed
{
	display:none;
}
input.collapsed
{
	background-image:url("<xsl:value-of select="$collapsedimage"/>");
	width:16px;
	height:16px;
	border:0px;
	padding:0px;
	vertical-align:middle;
}
ol.expanded
{
	display:block;
}
input.expanded
{
	background-image:url("<xsl:value-of select="$expandedimage"/>");
	height:16px;
	width:16px;
	border:0px;
	padding:0px;
	vertical-align:middle;
}
select.backup
{
	font-size: 10px;
	padding: 0px;
}
</style>
<script type="text/javascript">
/**
 * Switch the style of a paragraph and button to be collapsed when 
 * expanded and vice versa. Do this with class attributes since style
 * can't be edited.
 * @param pid id of paragraph-like block to be collapsed/expanded
 * @param bid id of button to do the collapsing/expanding
 */
function toggle( pid, bid )
{
	var input = document.getElementById( bid );
	var p = document.getElementById( pid );
	if ( input.className=="collapsed" )
	{
		input.className = "expanded";
		p.className="expanded";
	}
	else
	{
		input.className = "collapsed";
		p.className="collapsed";
	}
}
</script>
</head>
<body><h3><xsl:value-of select="@name"/></h3><xsl:apply-templates/>
</body></html></xsl:template>
<!-- match each variant group-->
<xsl:template match="//group">
<xsl:variable name="gid" select="concat('g',@id)"/>
<xsl:variable name="pid" select="concat('p',@id)"/>
<input type="button" class="collapsed">
<xsl:attribute name="id"><xsl:value-of select="$gid"/></xsl:attribute>
<xsl:attribute name="onclick">
<xsl:value-of select="concat('toggle(&quot;',$pid,'&quot;',',','&quot;',$gid,'&quot;',')')"/>
</xsl:attribute>
</input><xsl:text> </xsl:text>
<b><xsl:value-of select="@name"/></b><br/>
<ol class="collapsed">
<xsl:attribute name="id"><xsl:value-of select="$pid"/></xsl:attribute>
<xsl:apply-templates/></ol>
</xsl:template>
<!-- match each version within a group -->
<xsl:template match="//version">
<li><em><xsl:value-of select="@shortname"/>:</em> <xsl:value-of select="@longname"/>.
 <em>Backup: <xsl:value-of select="@backup"/></em></li>
</xsl:template>

</xsl:stylesheet>
