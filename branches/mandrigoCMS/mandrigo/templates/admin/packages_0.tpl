<!--MG_TEMPLATE_START_index-->
<h2>Package Manager</h2>
<p><font style="color: #FF0000;">{MSG}</font></p>
<h3>Installed Packages</h3>
<p>The following packages are installed on your server.</p>
<table width="100%">
<tr>
	<th align="left">Package ID</th>
	<th align="left">Package Name</th>
	<th align="left">Package Status</th>
	<th align="left">Package Disable</th>
	<th align="left">Package Remove</th>
</tr>
{PACKAGES}
</table>
<h3>Uninstalled Packages</h3>
<p>Files for the following packages exist on your server but the packages themselves have not been installed.</p>
<table width="100%">
<tr>
	<th align="left">Package Name</th>
	<th align="left">Install</th>
</tr>
{IPACKAGES}
</table>
<!--MG_TEMPLATE_END_index-->

<!--MG_TEMPLATE_START_install_item-->
<!--MG_TEMPLATE_START_itemi-->
<tr>
	<td align="left">{NAME}</td>
	<td align="left">{INSTALL}</td>
</tr>
<!--MG_TEMPLATE_END_itemi-->
<!--MG_TEMPLATE_END_install_item-->

<!--MG_TEMPLATE_START_index_item-->
<!--MG_TEMPLATE_START_item-->
<tr>
	<td align="left">{ID}</td>
	<td align="left">{NAME}</td>
	<td align="left">{STATUS}</td>
	<td align="left">{DISABLE_URL}</td>
	<td align="left">{REMOVE_URL}</td>
</tr>
<!--MG_TEMPLATE_END_item-->
<!--MG_TEMPLATE_END_index_item-->