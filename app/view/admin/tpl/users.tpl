{strip}
<form id="search-form" action="." method="post" enctype="multipart/form-data">
	<div class="sensei-toolbar">
		<label class="sensei-toolbar__control">
			<span class="sensei-toolbar__label">Логин</span>
			<input class="input_text js__input" type="text" name="login"/>
		</label>

		<label class="sensei-toolbar__control">
			<span class="sensei-toolbar__label">Имя</span>
			<input class="input_text js__input" type="text" name="name"/>
		</label>

		<label class="sensei-toolbar__control">
			<span class="sensei-toolbar__label">Группа</span>
			<select id="groups-select" class="input_select js__input" name="group"></select>
		</label>

		<label class="sensei-toolbar__control">
			<button class="sensei-btn sensei-btn_s sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_search" type="submit">Найти</button>
		</label>
	</div>
</form>

<div class="horizontal__holder">
	<table id="users-datagrid"></table>
</div>

<div id="users-toolbar" class="sensei-toolbar">
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_add js__add" type="button" title="Добавить пользователя">Добавить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_save js__save" type="button" title="Сохранить изменения">Сохранить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_cancel js__cancel" type="button" title="Отменить редактирование">Отменить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_delete js__delete" type="button" title="Удалить пользователя">Удалить</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_error js__lock" type="button" title="Блокировать/Разблокировать пользователя">Блокировать/Разблокировать</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_key js__pass" type="button" title="Задать новый пароль пользователя">Сменить пароль</button>
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_groups js__groups" type="button" title="Изменить группы пользователя">Группы</button>
	{if $historyEnabled}
	<button class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_history js__history" type="button" title="Посмотреть историю действий">История</button>
	{/if}
</div>

<div class="sensei-message sensei-message_info">Красным цветом отмечены заблокированные пользователи. Блокировка означает невозможность выполнить вход.</div>

<div id="groups-dialog">
	<ul id="groups-tree"></ul>
</div>
{/strip}