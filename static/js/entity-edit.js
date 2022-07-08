(function(d, C, $) {
	'use strict';

	const form = { node: null, $: null };

	const controller = 'entities';

	const relTable_idNot = [];

	const comboboxConf = {
		width: '100%',
		cls: 'input_text',
		hasDownArrow: false,
		delay: 750,
		valueField: 'id',
		textField: 'name',
		mode: 'remote',
		url: C.getAjaxUrl(true),
		method: 'get',
		loadFilter: (data) => {
			return C.checkResponse(data) ? data.data : [];
		},
		onBeforeLoad: function(param) {
			if (!param.hasOwnProperty('q')) {
				return false;
			}

			$(this).next().find('.textbox-text').addClass('text-note');
			$(this).combobox('setValue', '');
			$(this).combobox('setText', param.q);

			return param.q.length > 2;
		},
		onLoadError: () => {
			C.showError('Ошибка при получении данных');
		}
	};

	let action;
	let entity;

	function init() {
		form.node = d.getElementById('entity-form');

		if (!form.node) {
			return;
		}

		form.$ = $(form.node);

		action = form.node.dataset.action;
		entity = form.node.dataset.entity;

		form.node.querySelectorAll('.input_text.js__autocomplete')
			.forEach((el) => {
				prepareAutoComplete(el);
			});

		[...form.node.getElementsByClassName('rel-objects-search')]
			.forEach((el) => {
				prepareRelObjectsTable(el);
			});

		addListeners();
	}

	function addListeners() {
		form.$.on('click', '.js__set-encoded-value', function() {
			const $identField = $(this).prev('.input_text');
			const $nameField = form.$.find(`.input_text[name="${$(this).data('encode-from')}"]`);

			if (!$nameField.length) {
				return false;
			}

			const nameVal = $nameField.val();
			if (!nameVal) {
				C.showError('Сначала укажите ' + $nameField.data('fieldname'));
				return false;
			}

			$identField
				.val( C.encodeString(nameVal).substr(0, Number( $identField.attr('maxlength') )).toLowerCase() )
				.removeClass('input-wrong')
				.addClass('input-right');

			return false;
		}).on('click', '.js__form-submit', (evt) => {
			C.ignoreEvent(evt);
			submitForm();
		}).on('click', '.js__form-reset', () => {
			if (C.hasOwnProperty('clearFormTreeProcessing')) {
				C.clearFormTreeProcessing(form.$);
			}
			form.$.find('.input_text.js__autocomplete').each(function() {
				$(this).combobox('clear');
			});
		}).on('click', '.js__form-delete', (evt) => {
			C.ignoreEvent(evt);
			deleteEntity();
		}).on('click', '.sensei-form-table .btn_add_new', function() {
			const $lastTr = $(this).closest('table').find('tbody>tr:last-child');
			const withCKE = $lastTr.find('.cke').length;
			let $newTr;

			$lastTr.after($lastTr.outerHTML());
			if (withCKE) {
				$newTr = $lastTr.next();
				$newTr.find('.cke').remove();
				C.initEditor( $newTr.find('.editor_textarea') );
			}
		}).on('click', '.sensei-form-table .btn_delete', function() {
			$(this).closest('tr').find('.input_text, .input_textarea').val('');
		});
	}

	function getComboboxText(controller, value, success_callback) {
		C.xhr({
			data: {
				controller: controller,
				action: 'getNameById',
				id: parseInt(value, 10)
			},
			method: 'GET',
			onSuccess: (data) => {
				success_callback(data.name);
			}
		});
	}

	function prepareAutoComplete(input) {
		const controller = input.dataset.controller;
		const oldValue = input.value;
		const $input = $(input);

		$input.combobox(Object.assign({}, comboboxConf, {
			queryParams: {
				controller: controller,
				action: 'autocomplete'
			},
			onSelect: () => {
				const parentNode = input.parentNode;

				parentNode.querySelector('.textbox-text').classList.remove('text-note');
				input.value = parentNode.querySelector('.textbox-value').value;
			}
		}));

		if (oldValue) {
			getComboboxText(controller, oldValue, (name) => {
				$input.combobox('setText', name);
			});
		}
	}

	function prepareRelObjectsTable(input) {
		const relEntity = input.dataset.entity;
		const relObjectsTable = input.parentNode.querySelector('.rel-objects-table>tbody');
		const $input = $(input);

		if (action === 'update') {
			[...relObjectsTable.getElementsByClassName('js__input input_checkbox')]
				.forEach((el) => {
					relTable_idNot[ relTable_idNot.length ] = el.value;
				});
		}

		$input.combobox(Object.assign({}, comboboxConf, {
			queryParams: {
				controller: controller,
				action: 'findRelObjects',
				entity: entity,
				relEntity: relEntity,
				idNot: relTable_idNot
			},
			formatter: (row) => {
				return `<span style="font-weight:700">${row.name}</span>`;
			},
			onSelect: function(record) {
				$input.combobox('clear');
				$input.next().find('.textbox-text').removeClass('text-note');

				if (relTable_idNot.indexOf(record.id) !== -1) {
					C.showError('Эта запись уже есть в списке');
					return;
				}
				relTable_idNot[ relTable_idNot.length ] = record.id;

				const tr = '<tr>'
					+ '<th class="col-1">'
					+ '<input class="js__input input_checkbox" type="checkbox" checked="checked" '
					+ `name="rel_with[${relEntity}]" value="${record.id}"/>`
					+ '</th>'
					+ `<td class="col-11">${record.name}</td>`
					+ '</tr>';

				relObjectsTable.innerHTML += tr;
				C.styleInput(
					relObjectsTable.querySelector('.input_checkbox:not(.js__styled)')
				);
			}
		}));
	}

	function submitForm() {
		C.updateEditorTextarea();

		if (C.hasOwnProperty('submitFormTreeProcessing')) {
			C.submitFormTreeProcessing(form.$);
		}

		const fields = C.retrieveFormDataToJson(form.node);
		if (fields === false) {
			return false;
		}

		C.xhr({
			data: {
				controller: controller,
				action: action,
				entity: entity,
				fields: fields
			},
			onBeforeSend: () => {
				C.blockIt();
			},
			onComplete: () => {
				C.unblockIt();
			},
			onSuccess: () => {}
		});
	}

	function deleteEntity() {
		C.confirmSimpleDialog('Подтвердить необратимое удаление объекта?', () => {
			C.xhr({
				data: {
					controller: controller,
					action: 'delete',
					entity: entity,
					id: form.node.getElementsByName('id')[0].value
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: () => {
					setTimeout(() => {
						C.redirect('/admin-panel/');
					}, 700);
				}
			});
		}, () => {});
	}

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(document, Common, jQuery));