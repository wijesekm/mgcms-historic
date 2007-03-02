<?xml version="1.0" encoding="{ENCODING}"?>
<!-- generator="mandrigoCMS/{MANDRIGO_VERSION}" -->
<feed xml:lang="{FEED_LANG}" xmlns="http://www.w3.org/2005/Atom">
	<title>{FEED_TITLE}</title>
	<link rel="alternate" type="text/html" href="{FEED_URL}" />
	<link rel="self" type="text/html" href="{ATOM_URL}" />
	<id>{ATOM_URL}</id>
	<subtitle>{FEED_DESCRIPTION}</subtitle>
	<generator uri="http://mandrigo.org" version="{MANDRIGO_VERSION}">mandrigoCMS</generator>
	<updated>{LAST_UPDATED}</updated>
	{POSTS}
</feed>
<!--FEED_DELIM-->
	<entry>
		<title type="text">{POST_TITLE}</title>
		<link rel="alternate" type="text/html" href="{POST_URL}" />
		<author>
			<name>{POST_USERNAME}</name>
			<uri>{POST_USER_URL}</uri>
		</author>
		<id>{POST_URL}</id>
		<published>{POST_DATE}</published>
		<updated>{POST_DATE}</updated>
		<content type="html"><![CDATA[{CONTENT}]]></content>
	</entry>