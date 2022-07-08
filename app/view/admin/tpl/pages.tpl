{strip}
<div class="sensei-form sensei-form_horizontal">
	<div class="row">
		<div class="label">Поиск</div>
		<div class="input">
			<input id="search-input" class="input_text js__input" type="text"/>
			<div class="text-note">Введите часть названия или URL, минимум 3 символа</div>
		</div>
	</div>
</div>

<div id="search-results"></div>

<div class="horizontal__holder">
	<ul id="pages-tree"></ul>
</div>

{if $delWithSubPages}
<div class="sensei-message sensei-message_warning">При удалении дочерние страницы так же удаляются!</div>
{else}
<div class="sensei-message sensei-message_info">При удалении дочерние страницы перемещаются в раздел Trash</div>
{/if}

<div id="href-info" class="sensei-message sensei-message_info" style="display:none">
	Для вставки ссылки на эту страницу укажите атрибут HREF = "<span></span>"
</div>

<div id="keys-pattern" class="hidden-el">
	<button type="button" class="control-btn control-btn_edit js__edit-page" title="Редактировать"></button>
	<button type="button" class="control-btn control-btn_delete js__delete-page" title="Удалить"></button>
	<button type="button" class="control-btn control-btn_add js__add-page" title="Создать страницу следом за этой"></button>
	<button type="button" class="control-btn control-btn_down-right js__add-subpage" title="Создать дочернюю страницу"></button>
	<button type="button" class="control-btn control-btn_refresh js__reload-tree" title="Обновить список дочерних страниц"></button>
	<button type="button" class="control-btn control-btn_play js__go-to-page" title="Открыть страницу в новой вкладке"></button>
</div>
{/strip}