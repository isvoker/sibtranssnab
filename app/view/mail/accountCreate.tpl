{strip}
<p>Здравствуйте, {$user.name}.</p>
<p>Вы были зарегистрированы на сайте «<a style="color:{$theme_color_2}" href="{$host}">{$site_name}</a>».</p>
<p><strong>Логин:</strong> {$user.login}</p>
{if $confirmationLink}
<p>Для завершения регистрации перейдите по <strong><a href="{$host}{$confirmationLink}" rel="nofollow">этой ссылке</a></strong>.</p>
{/if}
<p>&nbsp;</p>
<p>Если вы не запрашивали регистрацию на нашем сайте, просто проигнорируйте это письмо или сообщите нам!</p>
{/strip}