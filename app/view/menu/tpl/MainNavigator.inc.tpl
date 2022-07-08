{strip}
{if $menu}
<ul class="drop menu5">
    {foreach $menu as $item}
    <li class="sub-menu-item {if $item.active}active{/if}">
        <div class="flex jc-sb" style="padding: 0 10px 0 5px"><span class="sub-menu-item__arrow">◄</span> {$item.name}
            {if $item.sub_menu and $depth - 1}
                {include file='file:[menu]MainNavigatorSub.inc.tpl' menu=$item.sub_menu}
            {/if}
        </div>
    </li>
    {/foreach}
</ul>
{/if}
{/strip}