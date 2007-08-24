<!--MG_TEMPLATE_START_overview-->
<form action="{FORM_ACTION}" method="post">
	<div style="margin-top: 50px; font-weight: bold; text-align: center; margin-left: 40px; width: 500px; font-size: 14px; border: 1px solid #808080; background: #D3D3D3;">
		Form Mail
	</div>
	<div style="margin-left: 40px; margin-bottom: 50px; width: 480px; padding: 10px; font-size: 14px; border: 1px solid #808080; border-top: 0;">
		<p style="text-align: center; font-size: 10px; color: RED;">{FMAIL_TOP}</p>
		<table width="100%">
			<tr>
				<td style="width: 150px;">Mailing To:</td>
				<td>{FMAIL_SNAME}</td>
			</tr>
			<tr>
				<td style="width: 150px;">Name:</td>
				<td><input maxLength="255" value="{FMAIL_PNAME}" style="width: 150px;" name="fmail_name"/> {FMAIL_STAR2}</td>
			</tr>
			<tr>
				<td style="width: 150px;">E-Mail:</td>
				<td><input maxLength="255" value="{FMAIL_PMAIL}" style="width: 150px;" name="fmail_mail"/> {FMAIL_STAR3}</td>
			</tr>
			<tr>
				<td style="width: 150px;">Subject:</td>
				<td><input maxLength="255" value="{FMAIL_PSUBJ}" style="width: 150px;" name="fmail_subj"/> {FMAIL_STAR4}</td>
			</tr>
			<tr>
				<td style="width: 150px;">Security Code:</td>
				<td><input maxLength="255" style="width: 150px;" name="ca_string"/><input name="ca_id" type="hidden" value="{FMAIL_CAID}"/> {FMAIL_STAR5}</td>
			</tr>
			<tr>
				<td style="width: 150px;">&nbsp;</td>
				<td><img style="border: 0;" src="{FMAIL_CAIMG}" title=""/></td>
			</tr>
			<tr>
				<td colspan="2" style="width: 150px;">Message: {FMAIL_STAR1}</td>
			</tr>
			<tr>
				<td colspan="2"><textarea name="fmail_message" style="width: 400px; height: 250px; font-size: 13px;">{FMAIL_PMSG}</textarea></td>
			</tr>
			<tr>
				<td colspan="2"><input class="button" type="submit" name="sa" value="Send Message" /></td>
			</tr>
		</table>
	</div>
</form>
<!--MG_TEMPLATE_END_overview-->