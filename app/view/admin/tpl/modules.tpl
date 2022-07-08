{strip}
{if $modules}
<div class="sensei-message">Виджеты модулей необходимо включить для нужных шаблонов</div>
<div class="modules-list">
    {foreach $modules as $module}
    <div class="modules-list-item js__modules-item flex ff-rw jc-sb ai-c" data-module-ident="{$module.fields.ident}">
        <div class="col-2">
            <span class="modules-list-item__name">
            {if $module.extraData.link}
            <a class="link" href="{$module.extraData.link}" title="Перейти к управлению модулем '{$module.fields.name}'">{$module.fields.name}</a>
            {else}
            {$module.fields.name}
            {/if}
            </span>
        </div>
        <div class="col-2 modules-list-item__icon">
            {if $module.extraData.icon}
            <img src="{$module.extraData.icon}" alt=""/>
            {/if}
        </div>
        <div class="col-4 modules-list-item__info">
            {if $module.extraData.info}
                {foreach $module.extraData.info as $item}
                <p>
                    <strong>{$item.name}</strong> : {$item.value}
                </p>
                {/foreach}
            {/if}
        </div>
        <div class="col-4 modules-list-item__templates">
            {if $module.fields.is_widget}
            {foreach $module.extraData.templates as $template}
                <p>
                    <label class="checkbox">
                        <input class="checkbox__choice js__bind_template_to_module" type="checkbox" value="{$template.ident}" {if $template.active}checked="checked"{/if}>
                        <span class="checkbox__label">{$template.name}</span>
                    </label>
                </p>
            {/foreach}
            {/if}
        </div>
    </div>
    {foreachelse}
    <div class="no-elements-in-list">Установленных модулей нет</div>
    {/foreach}
</div>
{/if}
{/strip}