<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:include href="tpl.default.xsl" />

	<xsl:template name="tabs">
		<ul class="tabs">
			<xsl:call-template name="tab">
				<xsl:with-param name="href" select="'emaillog'" />
				<xsl:with-param name="text" select="'Email log'" />
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

	<xsl:template match="content[../meta/action = 'index']">
		<table>
			<thead>
				<tr>
					<th class="small_row">Id</th>
					<th>Skapat</th>
					<th>Skickat</th>
					<th>Adress</th>
					<th>Titel</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="/root/content/emails/email">
					<tr>
						<xsl:if test="position() mod 2 = 1">
							<xsl:attribute name="class">odd</xsl:attribute>
						</xsl:if>
						<td><xsl:value-of select="@id"/></td>
						<td><xsl:value-of select="queued"/></td>
						<td><xsl:value-of select="sent"/></td>
						<td><xsl:value-of select="to_email"/></td>
						<td><xsl:value-of select="subject"/></td>
						<td><xsl:value-of select="status"/></td>
					</tr>
				</xsl:for-each>
			</tbody>
		</table>
		<xsl:if test="/root/content/mails_meta/pages &gt; 1">
			<div class="pagination-wrapper clear">
				<ul class="pagination">
					<li class="previous">
						<xsl:if test="/root/content/mails_meta/actual_page = 1">
							<strong>‹ Föregående sida</strong>
						</xsl:if>
						<xsl:if test="/root/content/mails_meta/actual_page &gt; 1">
							<a href="/admin/emaillog?page={/root/content/mails_meta/actual_page -1}">‹ Föregående sida</a>
						</xsl:if>
					</li>
					<xsl:if test="/root/content/mails_meta/actual_page &gt; 5">
						<li>
							<a href="/admin/emaillog?page=1">1</a>
						</li>
						<li>...</li>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="/root/content/mails_meta/actual_page &gt; 5">
							<xsl:choose>
								<xsl:when test="/root/content/mails_meta/pages &gt; (/root/content/mails_meta/actual_page + 5)">
									<xsl:call-template name="pagination">
										<xsl:with-param name="page"    select="/root/content/mails_meta/actual_page - 3" />
										<xsl:with-param name="limiter" select="number(5)" />
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<xsl:call-template name="pagination">
										<xsl:with-param name="page"    select="/root/content/mails_meta/actual_page - 3" />
										<xsl:with-param name="limiter" select="number(7)" />
									</xsl:call-template>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="/root/content/mails_meta/pages &gt; (/root/content/mails_meta/actual_page + 5)">
									<xsl:call-template name="pagination">
										<xsl:with-param name="limiter" select="number(7)" />
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<xsl:call-template name="pagination">
										<xsl:with-param name="limiter" select="number(9)" />
									</xsl:call-template>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:if test="/root/content/mails_meta/pages &gt; (/root/content/mails_meta/actual_page + 5)">
						<li>...</li>
						<li>
							<a href="/admin/emaillog?page={/root/content/mails_meta/pages}"><xsl:value-of select="/root/content/mails_meta/pages"/></a>
						</li>
					</xsl:if>
					<li class="next">
						<xsl:if test="/root/content/mails_meta/pages = /root/content/mails_meta/actual_page">
							<strong>Nästa sida ›</strong>
						</xsl:if>
						<xsl:if test="/root/content/mails_meta/pages != /root/content/mails_meta/actual_page">
							<a href="/admin/emaillog?page={/root/content/mails_meta/actual_page +1}">Nästa sida ›</a>
						</xsl:if>
					</li>
				</ul>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template name="pagination">
		<xsl:param name="page">1</xsl:param>
		<xsl:param name="limiter">9</xsl:param>

		<li>
			<xsl:if test="$page = /root/content/mails_meta/actual_page">
				<xsl:attribute name="class">active</xsl:attribute>
				<strong><xsl:value-of select="$page" /></strong>
			</xsl:if>
			<xsl:if test="$page != /root/content/mails_meta/actual_page">
				<a href="/admin/emaillog?page={$page}"><xsl:value-of select="$page" /></a>
			</xsl:if>
		</li>
		<xsl:if test="$page != /root/content/mails_meta/pages and $limiter != 1">
			<xsl:call-template name="pagination">
				<xsl:with-param name="page" select="$page + 1" />
				<xsl:with-param name="limiter" select="$limiter - 1" />
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
