<?xml version="1.0" encoding="{ENCODING}"?>
<!-- generator="mandrigoCMS/{MANDRIGO_VERSION}" -->
<rdf:RDF xmlns="http://purl.org/rss/1.0/" 
		 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
		 xmlns:dc="http://purl.org/dc/elements/1.1/" 
		 xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"					
		 xmlns:admin="http://webns.net/mvcb/" 
		 xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel rdf:about="{FEED_URL}">
		<title>{FEED_TITLE}</title>
		<link>{FEED_URL}</link>
		<description>{FEED_DESCRIPTION}</description>
		<dc:language>{FEED_LANG}</dc:language>
		<admin:generatorAgent rdf:resource="http://mandrigo.org/"/>
		<sy:updatePeriod>{UPDATE_PERIOD}</sy:updatePeriod>
		<sy:updateFrequency>{UPDATE_FREQ}</sy:updateFrequency>
		<sy:updateBase>2000-01-01T12:00+00:00</sy:updateBase>
		<items>
			<rdf:Seq>
				{FEED_OVERVIEW}
			</rdf:Seq>
		</items>
	</channel>
	{POSTS}
</rdf:RDF>
<!--FEED_DELIM-->
		<item rdf:about="{POST_URL}">
			<title>{POST_TITLE}</title>
			<link>{POST_URL}</link>
			<dc:date>{POST_DATE}</dc:date>
			<dc:creator>{POST_USERNAME}</dc:creator>
			<dc:subject>{POST_TITLE}</dc:subject>
			<description>{CONTENT_NOHTML}</description>
			<content:encoded><![CDATA[{CONTENT}]]></content:encoded>
		</item>
<!--FEED_DELIM-->
<rdf:li rdf:resource="{POST_URL}"/>