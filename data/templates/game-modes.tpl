{include file="header.tpl" title=Header}
<div class="text_container">
	<h2>{$text_header}</h2>
	<br/><br/>

	<table cellspacing="10">
		{foreach from=$modes key=itemID item=Item}
			<tr><td><a href="#">{$Item.name}</a></td><td><a href="#">{$Item.desc}</a></td></tr>
		{/foreach}
	</table>
</div>
{include file="footer.tpl" title=footer}
