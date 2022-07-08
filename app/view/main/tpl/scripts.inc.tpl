{strip}
{foreach $importedResources.js as $js}
    <script src="{$js@key}"></script>
{/foreach}
{$customHtml.footer}
{/strip}