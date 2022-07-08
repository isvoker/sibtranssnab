{strip}
{include file='file:[main]head.inc.tpl'}
<div class="admin-panel">
    <header class="admin-panel__header">
        <div class="flex ff-rn jc-sb ai-c">
            <div class="flex ai-fe">
                <a class="header-logo" href="{$AdminPanelPath}">
                    <img src="/static/img/admin/admin-logo.png" alt="AEngine"/>
                </a>
                <a class="header-open-site-link external" href="/" target="_blank">Перейти на сайт</a>
            </div>
	        <a class="admin-panel__exit-link" href="?action=logout">Выход</a>
        </div>
    </header>
    {if $menu}
    <a class="admin-nav-btn flex fd-c jc-c ai-c js__admin-nav-toggle" href="#"><i></i></a>
    <aside class="admin-panel__aside">
        {$menu}
    </aside>
    {/if}
    <main class="admin-panel__content">
        {$system_messages}
        <div class="content-wrapper">
            <h1>{$RTP.pageName}</h1>
            {$breadcrumbs}
            {$blockPage}
        </div>
    </main>
    <footer class="admin-panel__footer">
        <div class="admin-panel__developer">
            <a href="//web-ae.ru/" target="_blank">AE-студия</a> - создание и продвижение сайтов
        </div>
    </footer>
</div>
{include file='file:[admin]admin-icons.inc.tpl'}
{include file="file:[main]scripts.inc.tpl"}
</body>
</html>
{/strip}