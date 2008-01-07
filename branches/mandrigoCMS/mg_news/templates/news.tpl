<!--MG_TEMPLATE_START_top-->
<h1>News</h1>
<!--MG_TEMPLATE_END_top-->

<!--MG_TEMPLATE_START_bot-->

<!--MG_TEMPLATE_END_bot-->

<!--MG_TEMPLATE_START_posts-->

{POSTS}

{NAV}
<!--MG_TEMPLATE_END_posts-->

<!--MG_TEMPLATE_START_post-->
<!--MG_TEMPLATE_START_posttpl-->
		{POST_DATE}
		<h3>{POST_TITLE}</h3>
		<p>{POST_CONTENT}</p>
		<p class="post-footer align-right">
			<a href="{POST_AUTHOR_URL}" class="readmore" >Posted by {POST_AUTHOR}</a>
			<a href="{POST_URL}" class="comments">Comments ({COMMENT_COUNT})</a>
			<span class="date">{TIME}</span> 
		</p>
<!--MG_TEMPLATE_END_posttpl-->		
<!--MG_TEMPLATE_END_post-->

<!--MG_TEMPLATE_START_postdate-->
<!--MG_TEMPLATE_START_postd-->
<h2>{DATE}</h2>
<!--MG_TEMPLATE_END_postd-->
<!--MG_TEMPLATE_END_postdate-->

<!--MG_TEMPLATE_START_nav-->
<p style="text-align: center">[{NAV0}]<br/>
{NAV1}
</p>
<!--MG_TEMPLATE_END_nav-->

<!--MG_TEMPLATE_START_nav0-->
<!--MG_TEMPLATE_START_linkn0-->

<a href="{NAV_URL}">{PAGE_NUM}</a>

<!--MG_TEMPLATE_END_linkn0-->
<!--MG_TEMPLATE_END_nav0-->

<!--MG_TEMPLATE_START_nav1-->

<a href="{PREV_URL}">Prev</a> <<|>> <a href="{NEXT_URL}">Next</a>

<!--MG_TEMPLATE_END_nav1-->

<!--MG_TEMPLATE_START_synd-->

	<link rel="alternate" type="text/xml" title="RDF" href="{RSS1.0_LINK}" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="{RSS0.92_LINK}" />
	<link rel="alternate" type="text/xml" title="RSS 2.0" href="{RSS2.0_LINK}" />
	<link rel="alternate" type="application/atom+xml" title="Atom" href="{ATOM_LINK}" />
	
<!--MG_TEMPLATE_END_synd-->

<!--MG_TEMPLATE_START_coms-->

<h1>Comments</h1>
{COMMENTS}
{NAV}
<!--MG_TEMPLATE_END_coms-->

<!--MG_TEMPLATE_START_com-->
<!--MG_TEMPLATE_START_comsing-->
		<h3>{DATE}</h3>
		<p>{COM_CONTENT}</p>
		<p class="post-footer align-right">
			<a href="{COM_AUTHOR_URL}" class="readmore" >posted by {COM_AUTHOR}</a>
			<span class="date">{TIME}</span> 
		</p>
<!--MG_TEMPLATE_END_comsing-->
<!--MG_TEMPLATE_END_com-->

<!--MG_TEMPLATE_START_addcom-->
<!--MG_TEMPLATE_START_addcommain-->
<h1>Add a Comment</h1>
<p>{ERROR}</p>
{ADDCOM}
<!--MG_TEMPLATE_END_addcommain-->
<!--MG_TEMPLATE_START_addcom_an-->
<p>
<a href="{SITE_URL}/login_manager/">Login</a> or Post Anonymously Below.
</p>
<form method="post" action="{FORM_ACT}">
<table>
<tr>
	<td width="100"><h4>Name:</h4></td>
	<td><input maxLength="255" style="width: 150px;" name="com_name"/></td>
</tr>
<tr>
	<td><h4>Email:</h4></td>
	<td><input maxLength="255" style="width: 150px;" name="com_email"/></td>
</tr>
{CAP}
<tr>
	<td><h4>Comment:</h4></td>
	<td><textarea name="com_comment" style="width: 400px; height: 150px; font-size: 13px;"/></textarea>
</tr>
<tr>
	<td></td>
	<td><input class="button" type="submit" name="sa" value="Post" /></td>
</tr>
</table
</form>

<!--MG_TEMPLATE_END_addcom_an-->
<!--MG_TEMPLATE_START_addcom_auth-->
<p>
Post Comments Below
</p>
<form method="post" action="{FORM_ACT}">
<table>
<tr>
	<td width="100"><h4>Name:</h4></td>
	<td>{NAME}</td>
</tr>
{CAP}
<tr>
	<td><h4>Comment:</h4></td>
	<td><textarea name="com_comment" id="textfield1" rows="9" cols="50"></textarea>
</tr>
<tr>
	<td></td>
	<td><input type="submit" name="sa" id="submit1" value="Post" /></td>
	
</tr>
</table
</form>
<!--MG_TEMPLATE_END_addcom_auth-->
<!--MG_TEMPLATE_START_captcha-->
<tr>
	<td valign="top"><h4>Security Code:</h4></td>
	<td><input maxLength="255" style="width: 80px;" id="textfield1" name="ca_string"/><input type="hidden" value="{CAPID}" name="ca_id"/>
	<br/><img src="{CA_IMG}" alt="CA_IMG" /></td>
</tr>
<!--MG_TEMPLATE_END_captcha-->
<!--MG_TEMPLATE_START_addcomdenied-->
<p>
Please <a href="/login_manager/">Login</a> To post comments to this site.
</p>
<!--MG_TEMPLATE_END_addcomdenied-->
<!--MG_TEMPLATE_END_addcom-->

