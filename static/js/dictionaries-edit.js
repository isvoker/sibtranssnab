(function(C, $) {
	'use strict';

	document.addEventListener('DOMContentLoaded', () => {

		const $dicTree = $('#dic-tree');
		const dicId = $dicTree.data('id');
		const controller = 'dictionaries';
		const url = `${C.getAjaxUrl(true)}&controller=${controller}&dicId=${dicId}&action=tree`;

		$dicTree.etree({
			url       : url,
			createUrl : url + 'NodeCreate',
			updateUrl : url + 'NodeUpdate',
			destroyUrl: url + 'NodeDestroy',
			dndUrl    : url + 'NodeDnD',
			loadFilter: (data) => {
				if (Array.isArray(data)) {
					data = data[0];
				}
				if (C.checkResponse(data, 'Data corrupted', true)) {
					return data.hasOwnProperty('data') ? data.data : data;
				}

				return [];
			},
			onSelect: (node) => {
				$('#dic-info').show('slow');
				$('#dic-info>span').text(node.id);
			}
		});

		$('#dic-toolbar').on('click', '.js__add', () => {
			$dicTree.etree('create');

			const node = $dicTree.tree('getSelected');
			$dicTree.etree(
				'reload',
				node ? node.target : null
			);
		}).on('click', '.js__edit', () => {
			$dicTree.etree('edit');
		}).on('click', '.js__delete', () => {
			$dicTree.etree('destroy');
		}).on('click', '.js__reload', () => {
			$dicTree.etree('reload');
		}).on('click', '.js__cache', () => {
			C.localStorageClear('dicCache_' + $dicTree.data('alias'));
			C.showInfo('Локальный кэш данного словаря очищен');
		});

	});

}(Common, jQuery));