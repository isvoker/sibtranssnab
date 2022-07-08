// dependencies: sensei-forms.js
(function(C, d) {
	'use strict';

	d.addEventListener('DOMContentLoaded', () => {
		C.addListenerByParents(d, 'js__sensei-form', 'submit', onSubmit);
	});

	function onSubmit(evt) {
		C.ignoreEvent(evt);

		const form = evt.target;

		const options = {
			form: form,
			blockElement: form
		};

		if (!C.is(form.dataset.logto, 'undefined')) {
			const logTo = d.getElementById(form.dataset.logto);

			hideMessage(logTo);

			options.isSilent = true;
			options.onSuccess = (data) => {
				if (!C.is(data.msg, 'undefined')) {
					showMessage(logTo, 'info', data.msg);
				}
			};
			options.onError = (data) => {
				if (!C.is(data.error, 'undefined')) {
					showMessage(logTo, 'error', data.error);
				}
			};
		}

		C.submitFrom(options);
	}

	function showMessage(htmlEl, type, text) {
		htmlEl.className = 'sensei-message sensei-message_' + type;
		htmlEl.textContent = text;
	}

	function hideMessage(htmlEl) {
		htmlEl.className = '';
		htmlEl.textContent = '';
	}

}(Common, document));