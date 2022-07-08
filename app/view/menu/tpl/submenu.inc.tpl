{strip}
<div class="submenu-item__subitems">
    {foreach $menu as $item}
    <div class="submenu-subitem{if $item.active} opened{/if}">
        <a class="{if $item.id eq $current_page_id} active{/if}" href="{$item.href}">{$item.name}</a>
        {if $item.sub_menu and $depth - 1}
            {include file='file:[menu]submenu.inc.tpl' menu=$item.sub_menu depth=$depth - 1}
        {/if}
    </div>
    {/foreach}
</div>
{/strip}