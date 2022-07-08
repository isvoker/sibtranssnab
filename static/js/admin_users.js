(function(d, $, C) {
	'use strict';

	const $table = $('#users-datagrid');
	const $toolbar = $('#users-toolbar');
	const searchForm = d.getElementById('search-form');
	const controller = 'admin_users';
	let dgQueryParams = {
		controller: controller,
		action: 'find',
		restricts: '[]'
	};
	let grpData = C.localStorageGet('admin_grpData');
	let grpDialogExists = false;

	function init() {
		if (!$table.length) {
			return;
		}

		d.body.style.overflowY = 'scroll';

		initGroupsControl();

		initTable();

		initListeners();
	}

	function initGroupsControl() {
		if (grpData !== null) {
			generateGrpSelect(grpData);
		} else {
			C.xhr({
				data: {
					controller: controller,
					action: 'getAllGroups'
				},
				method: 'GET',
				onSuccess: (data) => {
					grpData = data.groups;
					C.localStoragePut('admin_grpData', data.groups);
					generateGrpSelect(grpData);
				}
			});
		}
	}

	function generateGrpSelect(grpData) {
		let option = '<option></option>';

		grpData.forEach((group) => {
			option += `<option value="${group.id}">${group.text}</option>`;
		});
		d.getElementById('groups-select').innerHTML = option;
	}

	function openGroupsDialog() {
		const $grpDialog = $('#groups-dialog');
		const $grpTree = $('#groups-tree');

		if (!grpDialogExists) {
			$grpTree.tree({
				data: grpData,
				checkbox: true
			});

			$grpDialog.dialog({
				closed: true,
				closable: true,
				draggable: true,
				resizable: true,
				shadow: true,
				title: 'Выберите группы',
				width: 350,
				buttons: [
					{
						text: 'Выбрать',
						iconCls: 'icon-ok',
						handler: () => {
							const checkedNodes = $grpTree.tree('getChecked');
							const selRow = $table.edatagrid('getSelected');
							let i = 0;
							let groupsStr = '';
							let selRowIdx;

							checkedNodes.forEach((node) => {
								if (C.is(node, 'object') && node.hasOwnProperty('id')) {
									groupsStr += (i++ ? '|' : '') + node.id;
								}
							});

							selRowIdx = $table.edatagrid('getRowIndex', selRow);
							$table.edatagrid('updateRow', {
								index: selRowIdx,
								row: {
									groupsArr: groupsStr.split('|'),
									groups: groupsStr.replace(/\|/g, ', ')
								}
							});
							C.xhr({
								data: {
									controller: controller,
									action: 'setUserGroups',
									userId: selRow.id,
									groups: groupsStr
								}
							});
							$grpDialog.dialog('close');
						}
					}, {
						text: 'Отмена',
						iconCls: 'icon-cancel',
						handler: () => {
							$grpDialog.dialog('close');
						}
					}
				],
				onOpen: () => {
					const selRow = $table.edatagrid('getSelected');

					if (selRow.groupsArr.length > 0) {
						C.searchAndMark($grpTree, true, grpData, selRow.groupsArr);
					}
				}
			});

			grpDialogExists = true;
		}
		$grpDialog.dialog('open');
	}

	function initTable() {
		const url = C.getAjaxUrl(true);

		$table.edatagrid({
			fitColumns: true,
			striped: true,
			nowrap: false,
			idField: 'id',
			singleSelect: true,
			pagination: true,
			pageSize: 50,
			pageList: [25, 50, 100],
			destroyMsg: {
				norecord: {
					title: 'Предупреждение',
					msg: 'Пользователь не выбран'
				},
				confirm: {
					title: 'Подтвердите',
					msg: 'Пользователь будет необратимо удалён'
				}
			},
			url: url,
			saveUrl: `${url}&controller=${controller}&action=insert`,
			updateUrl: `${url}&controller=${controller}&action=update`,
			destroyUrl: `${url}&controller=${controller}&action=delete`,
			method: 'POST',
			queryParams: dgQueryParams,
			columns: [[
				{field: 'id', title: 'ID', width: 50, sortable: true, hidden: true},
				{field: 'login', title: 'Логин', width: 200, sortable: true,
					styler: (value, row) => {
						let style = '';

						if (row.statuses % 3 === 0) {
							style = 'background-color:#F05;';
						}

						return style;
					},
					editor: {type: 'validatebox', options:{required: true}}},
				{field: 'email', title: 'E-mail', width: 200, editor: {type: 'validatebox', options: {required: true, validType: 'email'}}},
				{field: 'name', title: 'Имя', width: 300, editor: {type: 'validatebox', options: {required: true}}},
				{field: 'groups', title: 'Группы', width: 150},
				{field: 'description', title: 'Описание', width: 200, editor: {type: 'text'}}
			]],
			loadFilter: (data) => {
				const output = { rows: [], total: 0 };

				if (C.checkResponse(data)) {
					output.total = data.total;

					data.usersList.forEach((user) => {
						const fields = user.fieldsForOutput;
						const groups = user.extraData.grp_list;

						output.rows.push({
							id: fields.id,
							login: fields.login,
							email: fields.email,
							name: fields.name,
							groupsArr: groups.split(', '),
							groups: groups,
							description: fields.description,
							statuses: parseInt(user.fields.statuses, 10)
						});
					});
				}

				return output;
			},
			onLoadError: () => {
				C.showError('При загрузке данных произошла ошибка');
			},
			onHeaderContextMenu: (evt) => {
				C.datagridContextMenu($table, 'DataGridContextMenu', evt);
			},
			onSave: (index, row) => {
				if (C.checkResponse(row)) {
					if (!row.newUser) {
						return;
					}

					const fields = row.user.fieldsForOutput;
					const groups = row.user.extraData.grp_list;

					$table.edatagrid('updateRow', {
						index: index,
						row: {
							id: fields.id,
							login: fields.login,
							email: fields.email,
							name: fields.name,
							groupsArr: groups.split(', '),
							groups: groups,
							description: fields.description
						}
					});
					C.showInfo('Пользователь добавлен');
				}
			}
		});
	}

	function initListeners() {
		searchForm.addEventListener('submit', (evt) => {
			C.ignoreEvent(evt);

			dgQueryParams.restricts = C.retrieveFormDataToJson(searchForm);
			$table.edatagrid('load', dgQueryParams);
		});

		$toolbar.on('click', '.js__add', () => {
			$table.edatagrid('addRow');
		}).on('click', '.js__save', () => {
			$table.edatagrid('saveRow');
		}).on('click', '.js__cancel', () => {
			$table.edatagrid('cancelRow');
		}).on('click', '.js__delete', () => {
			$table.edatagrid('destroyRow');
		}).on('click', '.js__lock', () => {
			const selRow = $table.edatagrid('getSelected');

			if (selRow) {
				C.xhr({
					data: {
						controller: controller,
						action: 'block',
						userId: selRow.id
					},
					onSuccess: () => {
						C.showInfo('Статус пользователя изменён');
						$table.edatagrid('reload', dgQueryParams);
					}
				});
			}
		}).on('click', '.js__pass', () => {
			const selRow = $table.edatagrid('getSelected');

			if (selRow && C.is(selRow, 'object')) {
				$.messager.prompt('', 'Новый пароль:', (r) => {
					if (r) {
						C.xhr({
							data: {
								controller: controller,
								action: 'changePassword',
								userId: selRow.id,
								value: C.encryptString(r)
							},
							onSuccess: () => {
								C.showInfo('Пароль изменён');
							}
						});
					}
				});
			}
		}).on('click', '.js__groups', () => {
			const selRow = $table.edatagrid('getSelected');

			if (selRow && C.is(selRow, 'object')) {
				openGroupsDialog();
			}
		}).on('click', '.js__history', () => {
			const selRow = $table.edatagrid('getSelected');

			if (selRow && C.is(selRow, 'object')) {
				C.redirect('/history/?user=' + selRow.id, true);
			}
		});

		d.addEventListener('keydown', (evt) => {
			if (evt.key === 'Escape') {
				$table.edatagrid('cancelRow');
			}
		});
	}

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(document, jQuery, Common));