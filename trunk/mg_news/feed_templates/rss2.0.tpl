		
<!--MG_TEMPLATE_START_feed-->
<?xml version="1.0" encoding="{ENCODING}"?>
<!-- generator="mandrigoCMS/{MANDRIGO_VERSION}" -->
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/" 
				   xmlns:admin="http://webns.net/mvcb/" 
				   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" 
				   xmlns:content="http://purl.org/rss/1.0/modules/content/">
	<channel>
		<title>{FEED_TITLE}</title>
		<link>{FEED_URL}</link>
		<description>{FEED_DESCRIPTION}</description>
		<language>{FEED_LANG}</language>
		<docs>http://backend.userland.com/rss</docs>
		<admin:generatorAgent rdf:resource="http://mandrigo.org/"/>
		<ttl>{TTL}</ttl>
		{POSTS}
	</channel>
</rss>
<!--MG_TEMPLATE_END_feed-->

<!--MG_TEMPLATE_START_feeditem-->
		<item>
			<title>{POST_TITLE}</title>
			<link>{POST_URL}</link>
			<pubDate>{POST_DATE}</pubDate>
			<guid isPermaLink="false">{POST_ID}@{SITE_URL}{SITE_PAGE}</guid>
			<description>{CONTENT_NOHTML}</description>
			<content:encoded>{CONTENT_ENCODED}</content:encoded>
			<comments>{POST_URL}</comments>
		</item>
<!--MG_TEMPLATE_END_feeditem-->