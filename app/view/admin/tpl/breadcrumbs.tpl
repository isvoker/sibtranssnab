{strip}
{if $breadcrumbs}
<div class="breadcrumbs">
	{foreach $breadcrumbs as $item}
	{if $item.url eq '#'}
	<span>{$item.name}</span>
	{else}
	<a class="link" href="{$item.url}">{$item.name}</a>
	{/if}
	{/foreach}
</div>
{/if}
{/strip}