{strip}
    <b>{$user.name}</b>{if $user.banned} (Заблокирован){/if}<br />
    <a href="{$cfg.url.accountEdit}">Редактировать профиль</a><br />
    <a href="{$cfg.url.logout}">Выйти</a>
{/strip}