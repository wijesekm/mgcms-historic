<!--MG_TEMPLATE_START_overview-->
<h1>Profile</h1>
{PROFILE}
<!--MG_TEMPLATE_END_overview-->

<!--MG_TEMPLATE_START_user-->
<img class="imgright" style="border:0;background: #FFFFFF;" src="{PICTURE_PATH}" />
<h3>{FULL_NAME} ({USER_NAME})</h3>
<h4>General</h4>
<p>
<font style="font-weight: bold">Full Name: </font>{LAST_NAME}, {FIRST_NAME} {MIDDLE_NAME}<br/>
<font style="font-weight: bold">User Name: </font>{USER_NAME}<br/>
<font style="font-weight: bold">E-Mail: </font>{EMAIL}<br/>
<font style="font-weight: bold">Website: </font>{WEBSITE}<br/>
<font style="font-weight: bold">IM:</font>
</p>
<ul>
	{IM}
</ul>
<font style="font-weight: bold">Groups:</font><br/>
{GROUPS}
</p>
<h4>About</h4>
<p>
{ABOUT}
</p>
<!--MG_TEMPLATE_END_user-->

<!--MG_TEMPLATE_START_group-->
<img class="imgright" src="{PICTURE_PATH}" />
<h3>{GROUP_NAME}</h3>
<h4>General</h4>
<p>
<font style="font-weight: bold">Name: </font>{GROUP_NAME}<br/>
<font style="font-weight: bold">Admins:</font><br/>
{GP_ADMINS}<br/>
<font style="font-weight: bold">Users:</font><br/>
{GP_ADMINS}
</p>
<h4>About</h4>
<p>
{GROUP_ABOUT}
</p>
<!--MG_TEMPLATE_END_group-->

<!--MG_TEMPLATE_START_userdelim-->
<!--MG_TEMPLATE_START_userdelimsub-->
{NAME}<!--MG_CODE_START-->
$index=(int)"{INDEX}";
$index_size=(int)"{INDEX_SIZE}";
if($index < $index_size){
$mg_return=" , ";
}
<!--MG_CODE_END-->
<!--MG_TEMPLATE_END_userdelimsub-->
<!--MG_TEMPLATE_END_userdelim-->
