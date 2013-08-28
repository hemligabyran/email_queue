<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">
			<xsl:call-template name="tab">
				<xsl:with-param name="href"      select="'emaillog'" />
				<xsl:with-param name="text"      select="'Email log'" />
			</xsl:call-template>
		</ul>
	</xsl:template>

	<xsl:template match="/">
		<xsl:if test="/root/content[../meta/action = 'index']">
			<xsl:call-template name="template">
				<xsl:with-param name="title" select="'Admin - Email log'" />
				<xsl:with-param name="h1"    select="'Email log'" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
