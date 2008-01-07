<!--MG_TEMPLATE_START_overview-->
<h1>Mandrigo Gallery</h1>
{GALLERY_ITEM}
<!--MG_TEMPLATE_END_overview-->

<!--MG_TEMPLATE_START_album-->
{ALBUM_NAV}
<h3>Albums</h3>
<table>
	{GA_ALBUMS}
</table>

<h3>Images</h3>
<table>
	{GA_IMAGES}
</table>
{IMAGE_NAV}
<!--MG_TEMPLATE_END_album-->

<!--MG_TEMPLATE_START_rowset-->
<!--MG_TEMPLATE_START_rowsetsub-->
<tr>
	{DATA}
</tr>
<!--MG_TEMPLATE_END_rowsetsub-->
<!--MG_TEMPLATE_END_rowset-->

<!--MG_TEMPLATE_START_item-->
<!--MG_TEMPLATE_START_itemsub-->
<td><a href="{URL}" title="{TITLE}"><img src="{IMG_URL}" alt="{TITLE}"/></a></td>
<!--MG_TEMPLATE_END_itemsub-->
<!--MG_TEMPLATE_END_item-->


<!--MG_TEMPLATE_START_singleimg-->

{ALBUM_NAV}

<a href="{PREV_IMAGE}" title="Previous"><img style="margin: 0 5px 0 5px" src="{LEFT_ARROW_URL}" alt="<<" /></a>
<a href="{NEXT_IMAGE}" title="Gallery Home"><img style="margin: 0 5px 0 5px" src="{GALLERY_URL}" alt="H" /></a>
<a href="{NEXT_IMAGE}" title="Previous"><img style="margin: 0 5px 0 5px" src="{RIGHT_ARROW_URL}" alt=">>" /></a>

<img src="{IMG_URL}" alt="{IMG_ID}" />

<!--MG_TEMPLATE_END_singleimg-->