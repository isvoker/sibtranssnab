// dependencies: sensei-core.js, sensei-ui.js, sensei-forms.js
(function(C, w, d, $) {
	'use strict';

	function init() {
		d.addEventListener('click', (evt) => {
			const el = evt.target;

			if ( !(el && el.classList) ) {
				return;
			}

			if (el.classList.contains('js__empty')) {

				C.ignoreEvent(evt);

			} else if (el.classList.contains('js__print-page')) {

				el.parentNode.removeChild( el );
				window.print();

			} else if (el.classList.contains('js__switch')) {

				C.ignoreEvent(evt);
				el.closest('.js__switchable').classList.toggle('active');

			} else if (el.classList.contains('js__tab-header')) {

				C.ignoreEvent(evt);

				if (el.classList.contains('active')) {
					return;
				}

				const tabs = el.closest('.js__tabs');

				tabs.querySelectorAll('.js__tab-header.active, .js__tab-content.active')
					.forEach((el) => {
						el.classList.remove('active');
					});

				el.classList.add('active');
				console.log($(el))
				tabs.getElementsByClassName('js__tab-content')[ $(el).index() ].className +=' active';

			} else if (el.classList.contains('js__accordion-header')) {

				C.ignoreEvent(evt);

				el.classList.toggle('active');
				el.nextElementSibling.classList.toggle('active');

			} else if (el.classList.contains('js__md_open')) {

				C.ignoreEvent(evt);
				openModalDialog( $('#' + el.dataset.md) );

			} else if (
				(
					el.classList.contains('blockUI')
					&& el.classList.contains('blockOverlay')
				)
				|| el.classList.contains('js__md_close')
			) {

				C.ignoreEvent(evt);
				closeModalDialog();

			} else if (el.classList.contains('js__md-form_submit')) {

				C.ignoreEvent(evt);
				C.submitFrom({
					form: el.closest('.js__form'),
					blockElement: el.closest('.js__form').closest('.js__modal-dialog'),
					onSuccess: closeModalDialog
				});
			}
		});

		d.addEventListener('keyup', (evt) => {
			if (evt.key === "Escape") {
				C.closeModalDialog();
			}
		});
	}

	function blockElement($el) {
		$el.block();
	}

	function unblockElement($el) {
		$el.unblock();
	}

	function openModalDialog($dialog) {
		closeModalDialog();

		$.blockUI({
			message: $dialog,
			css: {
				backgroundColor: 'transparent',
				border: '0 none',
				left: '50%',
				top: '50%',
				width: $dialog.data('width') + 'px'
			},
			overlayCSS: {
				backgroundColor: 'rgba(0,0,0,.6)',
				opacity: 1
			}
		});
	}

	function closeModalDialog() {
		$.unblockUI();
	}

	C.blockElement = blockElement;
	C.unblockElement = unblockElement;
	C.openModalDialog = openModalDialog;
	C.closeModalDialog = closeModalDialog;

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(Common, window, document, jQuery));