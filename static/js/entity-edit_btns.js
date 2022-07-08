(function(d, C) {
	'use strict';

	function onDelete(evt) {
		C.ignoreEvent(evt);

		const entity = evt.target.dataset.entity;
		const id = evt.target.dataset.id;

		if (!entity || !id) {
			C.logError('Required argument is empty');
			return;
		}

		C.confirmSimpleDialog('Подтвердить необратимое удаление объекта?', () => {
			C.xhr({
				data: {
					controller: 'entities',
					action: 'delete',
					entity: entity,
					id: id
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: () => {
					setTimeout(() => {
						d.location.reload();
					}, 1000);
				}
			});
		}, () => {});
	}

	d.addEventListener('DOMContentLoaded', () => {
		C.addListenerByParents(d, 'js__entity_delete', 'click', onDelete);
	});

}(document, Common));