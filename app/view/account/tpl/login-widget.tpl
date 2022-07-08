{strip}
<div class="login-widget">
    {if $session.user.isAuth}
        Вы вошли как<br />
        <a href="{$cfg.url.account}" class="link">{$session.user.name|truncate:32}</a>&nbsp;
        ( <a class="link" href="{$cfg.url.logout}">выйти</a> )
    {else}
        <a class="link" href="{$cfg.url.login}">Вход</a>
    {/if}
</div>
{/strip}