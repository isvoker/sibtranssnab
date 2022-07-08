{strip}
<form class="sensei-form sensei-form_horizontal js__form js__sensei-form"
      action="."
      method="post"
      data-controller="cms"
      data-action="setOptions">

    <div class="top-buttons-float">
        <button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit}">Сохранить настройки</button>
        <a class="go-to-top" href="#">Наверх</a>
    </div>

    {foreach $options as $opt}
    <div class="row">
        <div class="label">{$opt.name}</div>
        <div class="input">
            {if $opt.edit_mode eq 'text'}
                <input class="input_text js__input{if $opt.is_required} required{/if}"
                       type="text"
                       data-fieldname="{$opt.name}"
                       name="{$opt.ident}" value="{$opt.value}"/>
            {elseif $opt.edit_mode eq 'textarea'}
                <textarea class="input_textarea js__input{if $opt.is_required} required{/if}"
                          data-fieldname="{$opt.name}"
                          name="{$opt.ident}">{$opt.value}</textarea>
            {elseif $opt.edit_mode eq 'checkbox'}
                <input class="input_checkbox js__input"
                       type="checkbox"
                       name="{$opt.ident}" value="1"
                       {if $opt.value} checked="checked"{/if}/>
            {elseif $opt.edit_mode eq 'url'}
                <input class="input_text js__input js__load-fm_input{if $opt.is_required} required{/if}"
                       type="text"
                       data-fieldname="{$opt.name}"
                       name="{$opt.ident}" value="{$opt.value}"/>
                <button type="button"
                        class="sensei-btn sensei-btn_s sensei-btn_white js__load-fm_btn">Выбрать файл</button>
            {elseif $opt.edit_mode eq 'htmleditor'}
                <img class="js__editor-loading hidden-el" src="/static/img/inner/runner.gif" alt="Загрузка...">
                <textarea class="input_textarea editor_textarea js__input{if $opt.is_required} required{/if}"
                          name="{$opt.ident}"
                          data-fieldname="{$opt.name}"
                          readonly="readonly">{$opt.value}</textarea>
                <button type="button"
                        class="sensei-btn sensei-btn_s sensei-btn_white js__load-editor">Редактировать текст</button>
            {elseif $opt.edit_mode eq 'color'}
                <input class="input_text js__input"
                       type="text"
                       value="{$opt.value}" name="{$opt.ident}"
                       data-jscolor="{ldelim}
                            position: 'bottom', width: 280, height: 150, padding: 5, sliderSize: 30,
                            borderRadius: 3, borderWidth: 1, controlBorderWidth: 1, pointerBorderWidth: 1,
                            borderColor: '#000', controlBorderColor: '#FFF', backgroundColor: '#111'
                       {rdelim}"/>
            {/if}

            {if $opt.description}
                <div class="text-note">{$opt.description}</div>
            {/if}
        </div>
    </div>
    {/foreach}
</form>
{/strip}