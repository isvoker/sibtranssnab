(function(C, $) {
	'use strict';

	function sendCommand(action) {
		C.xhr({
			data: {
				controller: 'admin_seo',
				action: action
			},
			onSuccess: () => {
				C.showInfo('Операция выполнена успешно');
			},
			onError: () => {
				C.showError('Выполнение операции было прервано ошибкой');
			}
		});
	}

	document.addEventListener('DOMContentLoaded', () => {
		$('#update-robots_txt').on('click', (evt) => {
			C.ignoreEvent(evt);
			sendCommand('updateRobotsTxt');
		});

		$('#disallow-robots').on('click', (evt) => {
			C.ignoreEvent(evt);
			sendCommand('disallowRobots');
		});

		$('#update-sitemap').on('click', (evt) => {
			C.ignoreEvent(evt);
			sendCommand('updateSitemap');
		});

		$('#make-yml').on('click', (evt) => {
			C.ignoreEvent(evt);
			sendCommand('makeYml');
		});
	});

}(Common, jQuery));