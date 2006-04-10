<br/>
<div style="width: 400px; position: relative; text-align: left; margin: 0px auto; border: #808080 1px solid;">
	<div style="font-size: 12px; text-align: center; padding-top:2px; padding-bottom:2px; border-bottom: #808080 1px solid">
		<h4>Login Manager</h4>
	</div>
	<div style="padding:3px;font-size: 13px; text-align:center;">
			<form method="post" action={ACTION}">
				<p style="color: #FF0000">{ERROR}</p>
				<table align="center" style="width: 280px;" cellpadding="0" cellspacing="0">
					<tr>
						<td style="font-size: 6px;">&nbsp;</td>
						<td style="font-size: 6px;">&nbsp;</td>
						<td rowspan="4"><img src="http://kevinwijesekera.net/images/mg_images/login.png" alt="Login!"/></td>
					</tr>
					<tr>
						<td>Username:</td>
						<td><input name="mg_user" value="{MG_USER_NAME}" maxlength="15" style="width: 120px; border: 1px solid silver;"/></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input name="mg_password" type="password" maxlength="30" value="" style="width: 120px; border: 1px solid silver;"/></td>
					</tr>
					<tr>
						<td style="font-size: 6px;">&nbsp;</td>
						<td style="font-size: 6px;">&nbsp;</td>
					</tr>						
				</table>
				<div style="left: 35px; position: relative;">
					<input type="submit" style="border: 1px solid silver;" value="Login"/>
				</div>
			</form>	
			<br/>
			<p style="text-size: 12px; text-align:center;">
				Powered by <a href="http://mandrigo.sourceforge.net/">Mandrigo CMS</a>
			</p>
	</div>
</div>
	