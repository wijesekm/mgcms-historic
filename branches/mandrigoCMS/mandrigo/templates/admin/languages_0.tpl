<!--MG_TEMPLATE_START_index-->
<h2>Language Manager</h2>
<p><font style="color: #FF0000;">{MSG}</font></p>
<h3>Installed Languages</h3>
<p>The following languages are installed on your server.</p>
<table width="100%">
<tr>
	<th align="left">Language ID</th>
	<th align="left">Language Name</th>
	<th align="left">Language Status</th>
	<th align="left">Language Remove</th>
</tr>
{LANGUAGES}
</table>
<h3>Uninstalled Languages</h3>
<p>Files for the following language exist on your server but the languages themselves have not been installed.</p>
<table width="100%">
<tr>
	<th align="left">Language Name</th>
	<th align="left">Install</th>
</tr>
{ILANGUAGES}
</table>
<!--MG_TEMPLATE_END_index-->

<!--MG_TEMPLATE_START_index_item-->
<!--MG_TEMPLATE_START_item-->
<tr>
	<td align="left">{ID}</td>
	<td align="left">{NAME}</td>
	<td align="left">{STATUS}</td>
	<td align="left">{REMOVE_URL}</td>
</tr>
<!--MG_TEMPLATE_END_item-->
<!--MG_TEMPLATE_END_index_item-->

<!--MG_TEMPLATE_START_install_item-->
<!--MG_TEMPLATE_START_itemi-->
<tr>
	<td align="left">{NAME}</td>
	<td align="left">{INSTALL}</td>
</tr>
<!--MG_TEMPLATE_END_itemi-->
<!--MG_TEMPLATE_END_install_item-->