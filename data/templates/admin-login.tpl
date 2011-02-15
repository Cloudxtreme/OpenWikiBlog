{include file="header.tpl" title=Header}
<div class="text_container">
	{if $login_state == "not_logged"}
	<h2>{$restricted_area}</h2>
	<form action="?page=9&do=login" method="POST">
		<table border="0">
			<tr>
				<td style="color: white;">
					{$login_text}:
				</td>

				<td>
					<input type="text" name="pa_first_input">
				</td>
		
			</tr>

			<tr>
				<td style="color: white;">
					{$passwd_text}:
				</td>

				<td>
					<input type="password" name="pa_second_input">
				</td>
			</tr>

			<tr>
				<td colspan="2" style="text-align: center;">
					<br/><input type="submit" value="{$login_submit}" style="width: 80%;">
				</td>
			</tr>
		</table>
	</form>
	{/if}
</div>
{include file="footer.tpl" title=footer}
