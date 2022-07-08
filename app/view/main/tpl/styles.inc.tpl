{strip}
{foreach $importedResources.css as $css}
    <link rel="stylesheet" type="text/css" href="{$css@key}"/>
{/foreach}
<style>
    {if $THEME_COLOR_1}
    .theme-color-1 {ldelim}color:{$THEME_COLOR_1}{rdelim}
    .background-theme-color-1 {ldelim}background-color:{$THEME_COLOR_1}{rdelim}
    :root {ldelim}--theme-color-1:{$THEME_COLOR_1}{rdelim}
    {/if}
    {if $THEME_COLOR_2}
    .theme-color-2 {ldelim}color:{$THEME_COLOR_2}{rdelim}
    .background-theme-color-2 {ldelim}background-color:{$THEME_COLOR_2}{rdelim}
    :root {ldelim}--theme-color-2:{$THEME_COLOR_2}{rdelim}
    {/if}
</style>
{/strip}