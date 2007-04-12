<!--MG_TEMPLATE_START_top-->
<h1>Site Directory</h1>
<!--MG_TEMPLATE_END_top-->


<!--MG_TEMPLATE_START_li_item-->
<li><a href="{URL}">{NAME}</a></li>
<!--MG_TEMPLATE_END_li_item-->


<!--MG_TEMPLATE_START_ul_item-->
<!--MG_CODE_START-->
$var="{LEVEL}";
if($var=="base"){
	$mg_return="<ul>";
}
else{
	$mg_return='<ul style="padding-left: 20px;">';
}
<!--MG_CODE_END-->
{UL_LIST}
</ul>
<!--MG_TEMPLATE_END_ul_item-->



<!--MG_TEMPLATE_START_bottom-->

<!--MG_TEMPLATE_END_bottom-->