<!--MG_TEMPLATE_START_feed--><?xml version="1.0" encoding="{ENCODING}"?>
<!-- generator="mandrigoCMS/{MANDRIGO_VERSION}" -->
<rss version="0.92">
	<channel>
		<title>{FEED_TITLE}</title>
		<link>{PAGE_URL}</link>
		<description>{FEED_DESCRIPTION}</description>
		<language>{FEED_LANG}</language>
		<docs>http://backend.userland.com/rss092</docs>
		{POSTS}
	</channel>
</rss>

<!--MG_TEMPLATE_END_feed-->

<!--MG_TEMPLATE_START_feeditem-->
<!--MG_TEMPLATE_START_feeditemsub-->
		<item>
			<title>{POST_TITLE}</title>
			<description>{CONTENT_ENCODED}</description>
			<link>{POST_URL}</link>
		</item>
		
<!--MG_TEMPLATE_END_feeditemsub-->
<!--MG_TEMPLATE_END_feeditem-->