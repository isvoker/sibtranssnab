{strip}
<div class="submenu-container">
    {foreach $menu as $item}
    <div class="submenu-item">
        <a class="{if $item.active} active{/if}" href="{$item.href}">
            {$item.name}
        </a>
        {if $item.sub_menu and $depth - 1}
            {include file='file:[menu]submenu.inc.tpl' menu=$item.sub_menu depth=$depth - 1}
        {/if}
    </div>
    {/foreach}
</div>
{/strip}