{strip}
{if $system_messages}
<div class="system-check js__system-check-container">
    {foreach $system_messages as $message}
    <p class="js__system-check-message">{$message}</p>
    {/foreach}
</div>
{/if}
{/strip}