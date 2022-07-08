{strip}
{include file='file:[main]head.inc.tpl'}
<div class="page-content flex fd-c">
    {include file='file:[main]header.inc.tpl'}

    <main class="main container">
        {$breadcrumbs}
        {if $RTP.pageName}<h1>{$RTP.pageName}</h1>{/if}
        <div class="flex ff-rn jc-sb">
            <aside class="main__aside f-00">
                {$submenu}
            </aside>
            <div class="main__content f-10">
                <div class="text-content">
                    {$blockPage}
                </div>
            </div>
        </div>
    </main>

    {include file='file:[main]footer.inc.tpl'}
</div>
{include file="file:[main]scripts.inc.tpl"}
</body>
</html>
{/strip}