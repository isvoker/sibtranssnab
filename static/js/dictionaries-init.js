(function(w, d, C, $) {
	'use strict';

	const controller = 'dictionaries';
	const localStorageKeyPrefix = 'dicCache_';

	function getText(dic, code, onSuccess) {
		C.xhr({
			data: {
				controller: controller,
				action: 'getText',
				dic: dic,
				code: code
			},
			method: 'get',
			onSuccess: (data) => {
				onSuccess(data.text);
			}
		});
	}

	function getRowsFromDic(dicAlias) {
		return new Promise((resolve, reject) => {
			let cache = C.localStorageGet(localStorageKeyPrefix + dicAlias);

			if (!cache || !cache.hasOwnProperty('revision')) {
				cache = { revision: 0, rows: [] };
			}

			C.xhr({
				data: {
					controller: controller,
					action: 'getRows',
					dic: dicAlias,
					revisionInCache: cache.revision
				},
				onSuccess: (data) => {
					if (data.hasOwnProperty('revision')) {
						cache = {
							revision: data.revision,
							rows: data.rows
						};
						C.localStoragePut(localStorageKeyPrefix + dicAlias, cache);
					}
					resolve(cache.rows);
				},
				onError: (data) => {
					C.logError(`Dictionary ${dicAlias} not loaded`);
				}
			});
		});
	}

	function makeSelect(input) {
		const selectedValue = input.dataset.code;

		getRowsFromDic(input.dataset.dic).then((rows) => {
			buildSelectOption(input, rows, selectedValue);
		});
	}

	function buildSelectOption(input, rows, value) {
		let option = '';

		rows.forEach((row) => {
			option += `<option value="${row.value}" ${row.value === value ? 'selected="selected"' : ''}>${row.text}</option>`;
		});
		if (input.classList.contains('required')) {
			input.innerHTML = option;
		} else {
			input.innerHTML += option;
		}
	}

	function makeAutoComplete(input) {
		const $input = $(input);
		const options = input.dataset;

		$input.combobox({
			required: $input.hasClass('required'),
			width: '100%',
			cls: 'input_text',
			hasDownArrow: false,
			delay: 750,
			valueField: 'id',
			textField: 'value',
			mode: 'remote',
			url: C.getAjaxUrl(true),
			method: 'get',
			queryParams: {
				controller: controller,
				action: 'getRowsLike',
				dic: options.dic
			},
			loadFilter: (data) => {
				if (!C.checkResponse(data, 'Data corrupted', true)) {
					return [];
				}

				const output = [];

				data.rows.forEach((row) => {
					output.push({id: row.value, value: row.text});
				});

				return output;
			},
			onBeforeLoad: (param) => {
				if (!param.hasOwnProperty('q')) {
					$input.combobox('setValue', options.code);
					return false;
				}

				$input.next().find('.textbox-text').addClass('text-note');
				$input.combobox('setValue', '');
				$input.combobox('setText', param.q);

				return param.q.length > 2;
			},
			onLoadError: () => {
				C.showError('Ошибка при получении данных');
			},
			onSelect: () => {
				const parentNode = input.parentNode;

				parentNode.querySelector('.textbox-text').classList.remove('text-note');
				input.value = parentNode.querySelector('.textbox-value').value;
			}
		});

		if (options.code) {
			$input.combobox('disable');
			getText(options.dic, options.code, (text) => {
				$input.combobox('setText', text);
				$input.combobox('enable');
			});
		}
	}

	// http://www.jeasyui.com/tutorial/tree/tree6.php
	function convertDataForTree(rows) {
		const nodes = [];
		const toDo = [];

		function exists(rows, id) {
			return rows.some( row => row.value === id );
		}

		// get the top level nodes
		rows.forEach((row) => {
			const node = { id: row.value, text: row.text };

			nodes.push(node);
			if (row.hasOwnProperty('parentId') && !exists(rows, row.parentId)) {
				toDo.push(node);
			}
		});

		while (toDo.length) {
			// the parent node
			const node = toDo.shift();
			// get the children nodes
			rows.forEach((row) => {
				if (row.parentId === node.id) {
					(node.children = node.children || []).push(child);
					toDo.push(child);
				}
			});
		}

		return nodes;
	}

	function makeTree(input) {
		const $input = $(input);
		const options = input.dataset;
		const isCheckbox = options.checkbox || false;

		$input.after( C.defaultOverlay.message );
		$input.tree({
			url: C.getAjaxUrl(true),
			method: 'get',
			queryParams: {
				controller: controller,
				action: 'getRows',
				dic: options.dic
			},
			animate: true,
			checkbox: isCheckbox,
			cascadeCheck: options.cascadeCheck,
			onlyLeafCheck: options.onlyLeafCheck,
			loadFilter: (data) => {
				$input.next().remove();

				if (C.checkResponse(data, 'Data corrupted', true)) {
					return convertDataForTree(data.rows);
				}

				return [];
			},
			onLoadSuccess: (node, data) => {
				const required = $input.hasClass('required');
				let first;

				if (required && data.length) {
					first = $input.tree('getRoot');
					if (isCheckbox) {
						$input.tree('check', first.target); //а что будет при onlyLeafCheck ?
					} else {
						$input.tree('select', first.target);
					}
				}
			},
			onLoadError: () => {
				C.showError('Ошибка при получении данных');
			},
			onBeforeCheck: (node, checked) => {
				if (
					isCheckbox
					&& checked
					&& options.multimax
					&& $input.tree('getChecked').length >= options.multimax
				) {
					C.showError(`Максимальное количество значений - ${options.multimax}!`);
					return false;
				}

				return true;
			}
		});
	}

	window.setDicHandlers = () => {
		d.querySelectorAll('.js__input_dic:not(.js__input_dic-handled)')
			.forEach((input) => {
				if (input.classList.contains('js__input_dic-select')) {
					makeSelect(input);
				} else if (input.classList.contains('js__input_dic-autocomplete')) {
					makeAutoComplete(input);
				} else if (input.classList.contains('js__input_dic-tree')) {
					makeTree(input);
				}

				input.classList.add('js__input_dic-handled');
			});
	};

	d.addEventListener('DOMContentLoaded', () => {
		window.setDicHandlers();
	});

}(window, document, Common, jQuery));