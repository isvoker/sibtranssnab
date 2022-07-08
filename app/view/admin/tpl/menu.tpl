{strip}
<nav class="admin-nav">
    <div class="admin-nav__list">
        {foreach $menu as $item}
        <div class="admin-nav__item{if $item.active} active{/if}">
            <a href="{$item.full_path}" title="{$item.name}" class="flex ai-c">
	            <svg class="admin-nav__item-icon"><use href="#ico-{$item.ident}"></use></svg>
	            {$item.name}
            </a>
            {if $item.sub_pages}
            <div class="admin-nav__submenu">
                {foreach $item.sub_pages as $subitem}
                <div class="admin-nav__subitem flex ai-c{if $subitem.active} active{/if}">
                    {if $subitem.full_path}
                    <a href="{$subitem.full_path}" title="{$subitem.name}">{$subitem.name}</a>
                    {else}
                    <span>{$subitem.name}</span>
                    {/if}
                    {if $subitem.description}
                    <div class="admin-nav-info">
                        <span class="admin-nav-info__link">?</span>
                        <div class="admin-nav-info__modal">{$subitem.description}</div>
                    </div>
                    {/if}
                </div>
                {/foreach}
            </div>
            {/if}
        </div>
        {/foreach}
    </div>
</nav>
{/strip}