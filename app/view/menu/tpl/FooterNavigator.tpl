{strip}
<nav class="footer-navigator flex ff-rn jc-c">
    {if $show_front}
    <a href="/">Главная</a>
    {/if}
    {foreach $menu as $item}
    <a href="{$item.href}">{$item.name}</a>
    {/foreach}
</nav>
{/strip}