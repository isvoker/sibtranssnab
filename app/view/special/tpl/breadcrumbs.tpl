{strip}
<div class="breadcrumbs" itemscope="" itemtype="http://schema.org/BreadcrumbList">
	{foreach $breadcrumbs as $item}
		{if $item.url eq '#'}
		{$item.name}
		{else}
		<span itemprop="itemListElement" itemscope="" itemtype="http://schema.org/ListItem">
			<a href="{$item.url}" itemprop="item">
				<span itemprop="name">{$item.name}</span>
			</a>
			<meta itemprop="position" content="{$item@iteration}"/>
		</span>
		{/if}
	{/foreach}
</div>
{/strip}