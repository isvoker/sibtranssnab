{strip}
<div class="js__module sensei-form sensei-form_vertical">
    <div class="top-buttons-float">
        <a class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} js__save-settings-btn" href="#">Сохранить настройки</a>
        <a class="sensei-btn sensei-btn_m sensei-btn_white" href="{$backlink}"><- Назад</a>
        <a class="go-to-top" href="#">Наверх</a>
    </div>

    <div class="row">
        <label class="checkbox">
            <input class="checkbox__choice" type="checkbox" name="widget_is_enabled" {if $settings.widget_is_enabled}checked="checked"{/if} />
            <span class="checkbox__label">Виджеты включены</span>
            <span class="text-note">Включение / выключение виджетов галереи</span>
        </label>
    </div>
	<div class="row">
		<div class="label">Количество изображений на странице модуля</div>
		<label class="input">
			<input class="input_text" type="text" name="photos_per_page" value="{$settings.photos_per_page}"/>
			<span class="text-note">Целое положительное число кратное 4-м</span>
		</label>
	</div>
	<div class="row">
		<div class="label">Количество изображений в виджетах</div>
		<label class="input">
			<input class="input_text" type="text" name="photos_per_page_in_widget" value="{$settings.photos_per_page_in_widget}"/>
			<span class="text-note">Целое положительное число кратное 4-м</span>
		</label>
	</div>
</div>
{/strip}