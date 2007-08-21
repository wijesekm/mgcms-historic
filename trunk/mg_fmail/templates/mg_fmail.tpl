<form action="{FORM_ACTION}" method="post">
	<div style="font-weight: bold; text-align: center; margin-left: 60px; width: 400px; font-size: 14px; border: 1px solid #808080; background: #D3D3D3;">
		Form Mail
	</div>
	<div style="margin-left: 60px; width: 400px; font-size: 14px; border: 1px solid #808080; border-top: 0;">
		<table width="100%">
			<tr>
				<td style="width: 150px;"><h4>Name:</h4></td>
				<td><input maxLength="255" value="{FMAIL_PNAME}" style="width: 150px;" name="fmail_name"/></td>
				<td style="width: 20px;">{FMAIL_STAR2}</tr>
			</tr>
			<tr>
				<td style="width: 150px;"><h4>E-Mail:</h4></td>
				<td><input maxLength="255" value="{FMAIL_PMAIL}" style="width: 150px;" name="fmail_mail"/></td>
				<td style="width: 20px;">{FMAIL_STAR3}</tr>
			</tr>
			<tr>
				<td style="width: 150px;"><h4>Subject:</h4></td>
				<td><input maxLength="255" value="{FMAIL_PSUBJ}" style="width: 150px;" name="fmail_subj"/></td>
				<td style="width: 20px;">{FMAIL_STAR4}</tr>
			</tr>
			<tr>
				<td style="width: 150px;"><h4>Security Code:</h4></td>
				<td><input maxLength="255" style="width: 150px;" name="ca_id"/></td>
				<td style="width: 20px;">{FMAIL_STAR5}</tr>
			</tr>
			<tr>
				<td style="width: 150px;"><h4>Message:</h4></td>
				<td colspan="2">{FMAIL_STAR5}></td>
			</tr>
			<tr>
				<td colspan="3"><textarea name="fmail_message" style="width: 400px; height: 250px; font-size: 13px;">{FMAIL_PMSG}</textarea></td>
			</tr>
			<tr>
				<td colspan="3"><input type="submit" name="sa" value="Send Message" /></td>
			</tr>
		</table>
	</div>
</form>