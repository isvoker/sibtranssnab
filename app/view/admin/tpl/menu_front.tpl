{strip}
<span><strong>Меню:</strong></span>
<br/>
{foreach $menu_front as $item}
<p>
	<a class="link" href="{$item.href}">
		{$item.name}
	</a>
</p>
{/foreach}
{/strip}