(function(d, $, C) {
	'use strict';

	const controller = 'admin_guard';
	const url = `${C.getAjaxUrl(true)}&controller=${controller}`;
	const actionFindLogs = 'findGuardLogs';
	const actionFindLockouts = 'findGuardLockouts';
	const $tableLogs = $('#datagrid_logs');
	const $tableLockouts = $('#datagrid_lockouts');
	const datagridProps = {
		fitColumns: true,
		striped: true,
		nowrap: false,
		pagination: true,
		singleSelect: true,
		pageSize: 25,
		pageList: [25, 50, 100],
		url: url,
		method: 'POST',
		loadFilter: (data) => {
			const output = { rows: [], total: 0 };

			if (C.checkResponse(data)) {
				output.rows = data.list;
				output.total = data.total;
			}

			return output;
		},
		onLoadError: () => {
			C.showError('При загрузке данных произошла ошибка');
		}
	};

	function init() {
		if (!$tableLogs.length) {
			return;
		}

		d.body.style.overflowY = 'scroll';

		initTables();
		initListeners();
	}

	function showMessage(text) {
		const htmlEl = d.getElementById('sensei-message');

		htmlEl.className = 'sensei-message sensei-message_info';
		htmlEl.innerHTML = text;
	}

	function initTables() {
		$tableLogs.datagrid(Object.assign({}, datagridProps, {
			title: 'Журнал событий',
			queryParams: {action: actionFindLogs},
			columns: [[
				{field: 'id', title: 'ID', sortable: true, hidden: true, width: 50},
				{field: 'code', title: 'Код', sortable: true, width: 50},
				{field: 'time', title: 'Время события', sortable: true, width: 150},
				{field: 'ip', title: 'IP', sortable: true, width: 150},
				{field: 'user_login', title: 'Логин', sortable: true, width: 300},
				{field: 'data', title: 'Доп. информация', width: 300}
			]],
			onHeaderContextMenu: (evt) => {
				C.datagridContextMenu($tableLogs, 'LogsContextMenu', evt);
			}
		}));

		$tableLockouts.datagrid(Object.assign({}, datagridProps, {
			title: 'Заблокированные хосты',
			queryParams: {action: actionFindLockouts},
			columns: [[
				{field: 'id', title: 'ID', sortable: true, hidden: true, width: 50},
				{field: 'active', title: 'Активно?', sortable: true, hidden: true, width: 50},
				{field: 'time_start', title: 'Время блокировки', sortable: true, width: 150},
				{field: 'time_end', title: 'Время разблокировки', sortable: true, width: 150},
				{field: 'ip', title: 'IP', sortable: true, width: 150}
			]],
			onHeaderContextMenu: (evt) => {
				C.datagridContextMenu($tableLockouts, 'LockoutsContextMenu', evt);
			}
		}));
	}

	function initListeners() {
		d.getElementById('btn_search-logs').addEventListener('click', () => {
			$tableLogs.datagrid('load', {
				action: actionFindLogs,
				searchStr: d.getElementById('input_search-logs').value
			});
		});

		d.getElementById('btn_search-lockouts').addEventListener('click', () => {
			$tableLockouts.datagrid('load', {
				action: actionFindLockouts,
				searchStr: d.getElementById('input_search-lockouts').value
			});
		});

		d.getElementById('btn_dump-bd').addEventListener('click', () => {
			C.xhr({
				data: {
					controller: controller,
					action: 'makeDbDump'
				},
				isSilent: true,
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: (data) => {
					showMessage(data.msg);
				}
			});
		});
	}

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(document, jQuery, Common));