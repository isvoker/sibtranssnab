{strip}
{if $menu}
<ul class="drop_two flashmenu menu3">
    {foreach $menu as $item}
    <li {if $item.active}class="active"{/if}>
        <a href="{$item.href}">{$item.name}</a>
    </li>
    {/foreach}
</ul>
{/if}
{/strip}