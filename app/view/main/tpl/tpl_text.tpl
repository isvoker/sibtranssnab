{strip}
{include file='file:[main]head.inc.tpl'}
<div class="page-content flex fd-c">
    {include file='file:[main]header.inc.tpl'}

    <main class="main">
        {*{$breadcrumbs}*}
        {*{if $RTP.pageName}<h1>{$RTP.pageName}</h1>{/if}*}
        <div class="text-content">
            {$blockPage}
        </div>
    </main>

    {include file='file:[main]footer.inc.tpl'}
</div>
{include file="file:[main]scripts.inc.tpl"}
</body>
</html>
{/strip}