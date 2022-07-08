{strip}
<div class="container-fluid">
    <div class="text-content">
        {$text_content}
    </div>
</div>
{if $HTML_BLOCK_CONTACT_MAP}
<div class="contacts-map">
    {$HTML_BLOCK_CONTACT_MAP}
</div>
{/if}
{include file="file:[special]callback-form-static.inc.tpl"}
{/strip}