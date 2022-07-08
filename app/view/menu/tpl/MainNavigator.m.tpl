{strip}
{*<nav class="header-nav js__header-nav-container hidden">
    <ul>
        {if $show_front}
        <li>
            <a href="/">Главная</a>
        </li>
        {/if}
        {foreach $menu as $item}
        <li>
            <a href="{$item.href}">{$item.name}</a>
            {if $item.sub_menu and $depth - 1}
                {include file='file:[menu]MainNavigator.inc.tpl' menu=$item.sub_menu}
            {/if}
        </li>
        {/foreach}
    </ul>
</nav>*}
{/strip}