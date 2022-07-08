(function(d, C, $) {
	'use strict';

	const controller = 'admin_pages';
	const editPath = '/admin-panel/entity_edit/?entity=CPage';

	let $tree;
	let keys;

	function init() {
		$tree = $('#pages-tree');
		keys = $('#keys-pattern').html();

		initTree();
		initListeners();

		Search.init();
	}

	function initTree() {
		$tree.tree({
			url: C.getAjaxUrl(true),
			animate: true,
			lines: true,
			dnd: true,
			queryParams: {
				controller: controller,
				action: 'pages'
			},
			loadFilter: (data) => {
				return C.checkResponse(data) && data.hasOwnProperty('data') ? data.data : data;
			},
			onLoadSuccess: () => {
				addEditButtons($('.tree-node'));
			},
			onSelect: (node) => {
				$('#href-info').show({duration: 600});
				$('#href-info>span').text(node.full_path);
			},
			onDblClick: (node) => {
				$tree.tree('beginEdit', node.target);
			},
			onBeforeEdit: () => {
				$tree.tree('disableDnd');
			},
			onAfterEdit: (node) => {
				C.xhr({
					data: {
						controller: controller,
						action: 'updateName',
						id: node.id,
						text: node.text
					}
				});
				$tree.tree('enableDnd');
			},
			onCancelEdit: () => {
				$tree.tree('enableDnd');
			},
			onDrop: (target, source, point) => {
				const targetNode = $tree.tree('getNode', target);

				if (!targetNode || !source) {
					return false;
				}

				C.xhr({
					data: {
						controller: controller,
						action: 'dnd',
						id: source.id,
						targetId: targetNode.id,
						point: point
					}
				});
				addEditButtons($('#' + source.domId));
			}
		})
	}

	function initListeners() {
		$tree.on('click', '.control-btn', (evt) => {
			const btn = evt.target;
			const node = getTreeNode(btn.parentNode);

			if (evt.target.classList.contains('js__edit-page')) {
				C.redirect(`${editPath}&id=${node.id}`, true);
			} else if (evt.target.classList.contains('js__add-page')) {
				const parentNode = getTreeNode(btn.closest('ul').previousElementSibling);

				if (parentNode) {
					C.redirect(`${editPath}&f[parent]=${parentNode.id}&posit_after=${node.id}`, true);
				} else {
					C.showError('На этом уровне создать страницу не получится');
				}
			} else if (evt.target.classList.contains('js__add-subpage')) {
				C.redirect(`${editPath}&f[parent]=${node.id}`, true);
			} else if (evt.target.classList.contains('js__delete-page')) {
				destroyNode(node);
			} else if (evt.target.classList.contains('js__reload-tree')) {
				$tree.tree('reload', node.target);
			} else if (evt.target.classList.contains('js__go-to-page')) {
				C.redirect(node.full_path, true);
			}

			C.ignoreEvent(evt);
		});
	}

	function addEditButtons($node) {
		$node.find('.tree-icon:not(.with-keys)').after(keys).addClass('with-keys');
	}

	function getTreeNode(node) {
		return $tree.tree('getNode', $(node));
	}

	function destroyNode(node) {
		$.messager.confirm('Подтвердите', 'Вы действительно хотите удалить эту страницу?', (r) => {
			if (r) {
				C.xhr({
					data: {
						controller: controller,
						action: 'delete',
						id: node.id
					},
					onSuccess: () => {
						$tree.tree('remove', node.target);
					}
				});
			}
		});
	}

	const Search = {
		searchInputId: 'search-input',

		resultsBlock: null,

		queryMinLength: 3,

		init: function() {
			this.resultsBlock = d.getElementById('search-results');
			this.addListeners();

			return true;
		},

		addListeners: function() {
			const _ = this;

			d.getElementById( _.searchInputId ).addEventListener('input', (evt) => {
				_.onInput(evt);
			});
		},

		onInput: function(evt) {
			const _ = this;
			const query = evt.target.value;

			_.clearResults();

			if (query.length < _.queryMinLength) {
				return;
			}

			C.xhr({
				data: {
					controller: controller,
					action: 'find',
					q: query
				},
				method: 'GET',
				onSuccess: (data) => {
					if (data.hasOwnProperty('items')) {
						_.drawResults(data.items);
					}
				}
			});
		},

		drawResults: function(items) {
			if (!items.length) {
				this.clearResults();
				return;
			}

			let html = '<div class="horizontal__holder"><table class="sensei-table columns">' +
				'<thead><tr><td class="col-1">Операции</td><td class="col-6">Страница</td><td class="col-5">URL</td></tr></thead>' +
				'<tbody>';

			items.forEach((item) => {
				html += '<tr>'
					+ '<td><div class="control-btns">'
						+ `<a class="control-btn control-btn_edit" href="${editPath}&id=${item.id}"`
							+ ' target="_blank" title="Редактировать"></a>'
						+ '<a class="control-btn control-btn_delete js__entity_delete" href="javascript:void(0)"'
							+ ` data-entity="CPage" data-id="${item.id}" title="Удалить"></a>`
					+ '</div></td>'
					+ `<td><a href="${item.full_path}" target="_blank">${item.text}</a></td>`
					+ `<td>${item.full_path}</td>`
				+ '</tr>';
			});
			html += '</tbody></table></div><hr/>';

			if (html) {
				this.resultsBlock.innerHTML = html;
			}
		},

		clearResults: function() {
			this.resultsBlock.textContent = '';
		}

	};

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(document, Common, jQuery));