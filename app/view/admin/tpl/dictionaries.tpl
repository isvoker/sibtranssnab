{strip}
{if $dictionary}

<h2>{$dictionary.name}</h2>

<div id="dic-toolbar" class="sensei-toolbar">
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_reload js__reload" type="button" title="Перезагрузить всё дерево">Обновить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_add js__add" type="button" title="Добавить новый элемент внутри выделенного">Добавить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_edit js__edit" type="button" title="Изменить значение выделенного элемента">Изменить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_delete js__delete" type="button" title="Отметить элемент как удалённый">Удалить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_options js__cache" type="button" title="Очистить кэш словаря">Очистить кэш</button>
</div>

<div class="horizontal__holder">
	<ul id="dic-tree" data-id="{$dictionary.id}" data-alias="{$dictionary.alias}"></ul>
</div>

<div id="dic-info" class="sensei-message sensei-message_info" style="display:none">
	Код элемента — <span></span>
</div>

{elseif $dictionaries}

<div class="text-content">
	{foreach $dictionaries as $dictionary}
	<p>
		<a class="sensei-ico sensei-ico_folder" href="?id={$dictionary.id}">#{$dictionary.id}. {$dictionary.name} ({$dictionary.alias})</a>
	</p>
	{/foreach}
</div>

{/if}
{/strip}