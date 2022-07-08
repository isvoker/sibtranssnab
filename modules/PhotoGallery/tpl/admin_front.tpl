{strip}
<div class="module-nav">
    {foreach $module_menu as $item}
    <a class="module-nav__link" href="{$item.url}">
        <span class="module-nav__title">{$item.name}</span>
        <span class="module-nav__description">{$item.description}</span>
    </a>
    {/foreach}
</div>
{/strip}