{strip}
	<div class="sensei-message sensei-message_info">Ваш IP: <strong>{$userIP}</strong></div>

	<div class="sensei-toolbar">
		<label class="sensei-toolbar__control">
			<span class="sensei-toolbar__label">Фильтр по коду/IP/логину</span>
			<input id="input_search-logs" class="input_text" type="text"/>
		</label>

		<label class="sensei-toolbar__control">
			<button id="btn_search-logs" class="sensei-btn sensei-btn_s sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_search" type="button">Найти</button>
		</label>
	</div>

	<div class="horizontal__holder">
		<table id="datagrid_logs"></table>
	</div>

	<hr/>

	<div class="sensei-toolbar">
		<label class="sensei-toolbar__control">
			<span class="sensei-toolbar__label">Фильтр по IP</span>
			<input id="input_search-lockouts" class="input_text" type="text"/>
		</label>

		<label class="sensei-toolbar__control">
			<button id="btn_search-lockouts" class="sensei-btn sensei-btn_s sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_search" type="button">Найти</button>
		</label>
	</div>

	<div class="horizontal__holder">
		<table id="datagrid_lockouts"></table>
	</div>

	<div class="sensei-message sensei-message_info text-content">
		За <strong>{$failThreshold}</strong> нарушений в течение <strong>{$failPeriod}</strong> минут выдаётся бан на <strong>{$banPeriod}</strong> минут. За <strong>{$banLimit}</strong> временных банов назначается перманентный бан.
	</div>
	<hr/>

	<div class="sensei-toolbar">
		<button id="btn_dump-bd" class="sensei-btn sensei-btn_m sensei-btn_{$cfg.button.colorSubmit} sensei-ico sensei-ico_db" type="button">Сделать дамп БД</button>
	</div>

	<div id="sensei-message"></div>
{/strip}