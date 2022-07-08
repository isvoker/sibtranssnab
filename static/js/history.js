(function($, C) {
	'use strict';

	document.addEventListener('DOMContentLoaded', () => {
		const request = C.parseQueryString();
		const $table = $('#history__table');
		const $searchForm = $('#history__search-form');
		let hType;
		let dgQueryParams = {
			csrf_key: C.getSessionToken(),
			controller: 'history',
			action: '',
			hEntityID: '',
			restricts: ''
		};

		document.body.style.overflowY = 'scroll';

		$('#history').css('visibility','visible').hide().fadeIn('slow');

		for (hType in request) {break;}

		dgQueryParams.hEntityID = request[hType];
		dgQueryParams.action = `find_${hType}_history`;

		$table.datagrid({
			columns: [[
				{field: 'time', title: '<b>Время</b>', width: 125, sortable: true},
				{field: 'ip', title: 'IP адрес', width: 120, sortable: true},
				{field: 'user_name', title: 'Пользователь', width: 120, sortable: true},
				{field: 'description', title: 'Событие', width: 470}
			]],
			fitColumns: true,
			striped: true,
			nowrap: false,
			pagination: true,
			pageSize: 10,
			pageList: [10, 50, 100],
			url: C.getAjaxUrl(),
			method: 'POST',
			queryParams: dgQueryParams,
			loadFilter: (data) => {
				const output = { rows: [], total: 0 };

				if (C.checkResponse(data)) {
					output.total = data.total;

					data.rows.forEach((row) => {
						const fields = row.fieldsForOutput;

						output.rows.push({
							time: fields.time,
							ip: fields.ip,
							user_name: fields.user_name,
							description: fields.description
						});
					});
				}

				return output;
			},
			onLoadError: () => {
				C.showError('При загрузке данных произошла ошибка');
			},
			onHeaderContextMenu: (e) => {
				C.datagridContextMenu($table, 'DataGridContextMenu', e);
			}
		});

		$searchForm.on('click', '.js__form-submit', () => {
			dgQueryParams.restricts = C.retrieveFormDataToJson( $searchForm[0] );
			$table.datagrid('load', dgQueryParams);
		}).on('click', '.js__form-reset', () => {
			$searchForm.clearForm();
			$searchForm.find('input:hidden').val('___EMPTY');
		});

	});

}(jQuery, Common));