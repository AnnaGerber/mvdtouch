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
<xsl:template match="/"><html><body><xsl:apply-templates/></body></html></xsl:template>
<xsl:template match="//teiHeader">
</xsl:template>
<xsl:template match="//empty">
<p class="large">NO TEXT</p>
</xsl:template>
<xsl:template match="//sp/stage">
<span class="stageitalic"><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//sp">
<p class="sp"><xsl:apply-templates/></p>
</xsl:template>
<xsl:template match="//l">
<xsl:if test="@type='half'">
<span class="half">&#160;</span>
</xsl:if>
<xsl:apply-templates/><br/>
</xsl:template>
<xsl:template match="//speaker">
<span class="speaker"><xsl:apply-templates/> </span>
</xsl:template>
<xsl:template match="//div[@type='poem']">
<div class="poem"><xsl:apply-templates/></div>
</xsl:template>
<!-- milestones -->
<xsl:template match="//ms">
<ms>
<xsl:attribute name="n"><xsl:value-of select="@n"/></xsl:attribute>
<xsl:if test="@l">
<xsl:attribute name="l"><xsl:value-of select="@l"/></xsl:attribute>
</xsl:if>
</ms>
</xsl:template>
<!-- chunks -->
<xsl:template match="//ch[@type='found']">
<span class="selected"><xsl:attribute name="id"><xsl:value-of select="@selid"/></xsl:attribute><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//ch[@type='deleted']">
<span class="deleted"><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//ch[@type='added']">
<span class="added"><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//ch[@type='found,deleted']">
<span class="founddeleted" id="selection1"><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//ch[@type='merged']">
<xsl:choose>
	<xsl:when test="@id">
	<span><xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute><xsl:apply-templates/></span>
	</xsl:when>
	<xsl:otherwise>
	<xsl:apply-templates/>
	</xsl:otherwise>
</xsl:choose>
</xsl:template>
<xsl:template match="//ch[@type='found,added']">
<span class="foundadded" id="selection1"><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//ch[@type='parent']">
	<xsl:call-template name="wrapSpan">
		<xsl:with-param name="thisSide">left</xsl:with-param>
		<xsl:with-param name="thatSide">right</xsl:with-param>
		<xsl:with-param name="class">transposed</xsl:with-param>
	</xsl:call-template>
</xsl:template>
<xsl:template match="//ch[@type='child']">
	<xsl:call-template name="wrapSpan">
		<xsl:with-param name="thisSide">left</xsl:with-param>
		<xsl:with-param name="thatSide">right</xsl:with-param>
		<xsl:with-param name="class">transposed</xsl:with-param>
	</xsl:call-template>
</xsl:template>
<xsl:template name="wrapSpan">
	<xsl:param name="thisSide"/>
	<xsl:param name="thatSide"/>
	<xsl:param name="class" select="none"/>
	<xsl:variable name="iden" select="@id"/>
	<span>
	<xsl:attribute name="id">
		<xsl:value-of select="concat($thisSide,$iden)"/>
	</xsl:attribute>
	<xsl:if test="$class!='none'">
		<xsl:attribute name="class">
			<xsl:value-of select="$class"/>
		</xsl:attribute>
	</xsl:if>
	<xsl:attribute name="onclick">
		<xsl:value-of select="concat('javascript:performlink(','&quot;',$thisSide,$iden,'&quot;,','&quot;',$thatSide,$iden,'&quot;)')"/>
	</xsl:attribute>
	<xsl:apply-templates/>
	</span>
</xsl:template>
<xsl:template match="//sp/p">
<span class="sp"><xsl:apply-templates/></span>
</xsl:template>

<xsl:template match="//lg">
<p class="lg"><xsl:apply-templates/></p>
</xsl:template>
<xsl:template match="//hi[@rend='italic']">
<em><xsl:apply-templates/></em>
</xsl:template>
<xsl:template match="//head">
<h3><xsl:apply-templates/></h3>
</xsl:template>
<xsl:template match="//stage">
<p>
<xsl:choose>
<xsl:when test="@rend='italic'">
<xsl:attribute name="class">stageitalic</xsl:attribute>
</xsl:when>
<xsl:otherwise>
<xsl:attribute name="class">stage</xsl:attribute>
</xsl:otherwise>
</xsl:choose>
<xsl:apply-templates/></p>
</xsl:template>
<xsl:template match="//role">
<span class="role"><xsl:apply-templates/></span>
</xsl:template>
<xsl:template match="//castList">
<p class="cast"><b>Attori: </b><xsl:apply-templates/></p>
</xsl:template>
<!-- sylesheet rules from the Digital Variants Website-->
<xsl:template match="seg">
    <xsl:choose>
        <xsl:when test="@type='ripetizione'">
            <font color="green" size="4">
                <u>
                    <xsl:apply-templates/>
                </u>
            </font>
        </xsl:when>
        <xsl:when test="@type='individuazione sequenza narrativa'">
            <font color="green" size="5">
                <h3>
                    <xsl:value-of select="@type"/>
                </h3> [ <xsl:apply-templates/> ] </font>
        </xsl:when>
        <xsl:when test="@type='rivedere'">
            <font color="fuchsia"> [ <xsl:apply-templates/> ] </font>
        </xsl:when>
        <xsl:otherwise>
            <font color="blue">
                <xsl:apply-templates/>
            </font>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>
<xsl:template match="//head">
<h3><xsl:apply-templates/></h3>
</xsl:template>
<xsl:template match="bibl/title">
    <h1 align="center">
        <xsl:apply-templates/>
    </h1>
</xsl:template>
<xsl:template match="bibl/author">
    <h2 align="center">
        <xsl:apply-templates/>
    </h2>
</xsl:template>
<xsl:template match="p">
    <p>
        <xsl:apply-templates/>
    </p>
</xsl:template>
<xsl:template match="add">
    <font color="blue"> [ <xsl:apply-templates/> ] </font>
</xsl:template>
<xsl:template match="del">
    <xsl:choose>
        <xsl:when test="@rend='tratto a matita'">
            <strike style="color: red">
                <xsl:apply-templates/>
            </strike>   
        </xsl:when>
            <xsl:when test="@rend='lettera sovrascritta a matita'">
            <font color="brown" size="5">
               {<xsl:apply-templates/>} 
            </font>
        </xsl:when>
        <xsl:otherwise>
            <font color="blue">
                <xsl:apply-templates/>
            </font>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>
<xsl:template match="div2">
    <h3>
        <xsl:value-of select="@id"/>
    </h3>
    <xsl:apply-templates/>
</xsl:template>
</xsl:stylesheet>
