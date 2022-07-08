// dependencies: sensei-core.js, sensei-ui.js
(function(C, d) {
	'use strict';

	let previousRadioElem;
	let styledInputsCnt = 0;

	function init() {
		d.querySelectorAll('.sensei-form .input_checkbox:not(.js__styled), .sensei-form .input_radio:not(.js__styled)')
			.forEach(styleInput);

		d.querySelectorAll('.sensei-form_need-validation')
			.forEach(addFieldsValidation);

		d.querySelectorAll('.js__input[type="password"]')
			.forEach( (input) => {
				input.addEventListener('mousedown', () => {
					changeInputType(input, 'text');
				});
				input.addEventListener('mouseup', () => {
					changeInputType(input, 'password');
				});
			});

		C.addListenerByParents(d, 'input_radio', 'click', onClickRadio);

		C.addListenerByParents(d, 'captcha_refresh', 'click', reloadCaptcha);

		C.addListenerByParents(d, 'js__submit-form', 'click', onSubmitForm);

		C.addListenerByParents(d, 'js__form-reset', 'click', onResetForm);
	}

	function changeInputType(input, newType) {
		if (input.value) {
			input.type = newType;
			input.focus();
		}
	}

	function onClickRadio(evt) {
		const input = evt.target;
		const previousValue = input.dataset.previous || '0';

		if (
			previousValue === '1'
			&& previousRadioElem === input
		) {
			input.checked = false;
		}
		input.dataset.previous = input.checked ? '1' : '0';
		previousRadioElem = input;
	}

	function reloadCaptcha(evt) {
		C.ignoreEvent(evt);

		const parentNode = evt.target.parentNode;
		const img = parentNode.querySelector('.captcha');
		const input = parentNode.querySelector('.input_captcha');
		let imgSrc = img.src.replace(/[?&]\d+$/, '');

		imgSrc += (imgSrc.indexOf('?') === -1 ? '?' : '&') + Date.now();
		img.src = imgSrc;
		input.value = '';
		input.focus();
	}

	function onSubmitForm(evt) {
		C.ignoreEvent(evt);

		const form = evt.target.closest('.js__form');

		submitFrom({
			form: form,
			blockElement: form.parentNode
		});
	}

	function onResetForm(evt) {
		const form = evt.target.closest('.js__form');

		form.querySelectorAll('.input_checkbox:checked')
			.forEach((input) => {
				input.checked = false;
			});

		//form.reset();
	}

	function styleInput(input) {
		let id;
		let label;
		let labelText;

		if (input.classList.contains('js__styled')) {
			return;
		}

		styledInputsCnt++;

		id = input.id || `_${input.name}_${input.value}_${styledInputsCnt}`;
		label = d.createElement('label');
		label.setAttribute('for', id);
		if (input.disabled) {
			label.classList.add('disabled');
		}
		labelText = input.dataset.label;
		if (!C.is(labelText, 'undefined') && labelText) {
			label.textContent = labelText;
		}
		input.id = id;
		input.classList.add('js__styled');
		input.parentNode.insertBefore(label, input.nextSibling);
	}

	function addFieldsValidation(form) {
		// allow only Backspace(8), Enter(13), Delete(46), arrows(37-40), digits(48-57) and F5(116)
		$(form).on('keypress', '.ft_integer, .ft_float', function(e) {
			const input = this;
			const keycode = e.keyCode || e.which || 0; // IE || other browsers

			if (
				keycode !== 8 && keycode !== 13 && keycode !== 46
				&& (keycode < 37 || keycode > 40)
				&& (keycode < 48 || keycode > 57)
				&& keycode !== 116
			) {
				return false;
			}

			const valueLength = input.value.length;
			let isDate = false;
			let isTime = false;
			let isDateTime = false;

			if (
				input.classList.contains('ft_date')
				|| input.classList.contains('ft_date_w_delimiter')
			) {
				isDate = true;
			} else if (input.classList.contains('ft_datetime')) {
				isDateTime = true;
			} else if (input.classList.contains('ft_time')) {
				isTime = true;
			}
			if (
				((isDate && valueLength >= 10) || (isTime && valueLength >= 8) || (isDateTime && valueLength >= 19))
				&& keycode >= 48 && keycode <= 57
			) {
				return false;
			}

			if (keycode !== 8) {
				if (isDate && (valueLength === 2 || valueLength === 5)) {
					input.value += '.';
				} else if (isTime && (valueLength === 2 || valueLength === 5)) {
					input.value += ':';
				} else if (isDateTime) {
					if (valueLength === 2 || valueLength === 5) {
						input.value += '.';
					} else if (valueLength === 10) {
						input.value += ' ';
					} else if (valueLength === 13 || valueLength === 16) {
						input.value += ':';
					}
				}
			}

			return true;
		});
	}

	function getInputValue(input) {
		return typeof input.value === 'undefined'
		|| (
			input.tagName === 'INPUT'
			&& (input.type === 'checkbox' || input.type === 'radio')
			&& !input.checked
		)
			? '' : String( input.value );
	}

	function getInputTitle(input) {
		return input.dataset.fieldname || input.placeholder || input.name;
	}

	function validateInputValue(input) {
		const options = input.dataset;
		const title = getInputTitle(input);
		const value = getInputValue(input);
		const isRequired = input.classList.contains('required') || input.getAttribute('required') === 'required';
		let isValid = true;
		let errorMsg;

		if (
			isValid
			&& isRequired
			&& C.isEmptyStr(value)
		) {
			isValid = false;
			errorMsg = `Поле "${title}" не может быть пустым!`;
		}

		if (
			isValid
			&& options.hasOwnProperty('regexp')
			&& !(new RegExp(options.regexp)).test(value)
		) {
			isValid = false;
			errorMsg = `Неправильно заполнено поле "${title}"!`;
		}

		if (
			isValid
			&& options.hasOwnProperty('morethen')
		) {
			const inputForComparison = d.querySelector(`.js__input[name="${options.morethen}"]`);

			if (!C.isEmptyStr(value) && parseFloat(value) < parseFloat(inputForComparison.value)) {
				isValid = false;
				errorMsg = `Значение поля "${title}" должно быть не меньше, чем значение поля "${getInputTitle(inputForComparison)}"!`;
			}
		}

		if (
			isValid
			&& options.hasOwnProperty('morethen_val')
		) {
			const valueForComparison = options.morethen_val;

			if (!C.isEmptyStr(value) && parseFloat(value) < parseFloat(valueForComparison)) {
				isValid = false;
				errorMsg = `Значение поля "${title}" должно быть не меньше, чем ${valueForComparison}!`;
			}
		}

		if (!isValid) {
			C.showError(errorMsg);
			input.classList.remove('input-right');
			input.classList.add('input-wrong');
			input.focus();

			return false;
		}

		input.classList.remove('input-wrong');
		input.classList.add('input-right');

		return true;
	}

	function validateFormInputs(form) {
		let isValid = true;

		[].every.call(form.getElementsByClassName('js__input'), (input) => {
			isValid = validateInputValue(input);
			return isValid;
		});

		return isValid;
	}

	function retrieveFormDataToJson(form) {
		const data = {};
		let formIsValid = true;

		[].every.call(form.getElementsByClassName('js__input'), (input) => {
			if (!validateInputValue(input)) {
				formIsValid = false;
				return false;
			}

			let inputName = input.name || input.getAttribute('comboname') || 'unknownInput';
			let inputValue = getInputValue(input);

			if (inputName.endsWith('[]')) {
				inputName = inputName.slice(0, -2);
			}

			if (input.classList.contains('sensitive')) {
				inputValue = C.encryptString(inputValue);
			} else if (input.tagName === 'TEXTAREA') {
				inputName = '*' + inputName;
				inputValue = C.base64_encode(inputValue);
			}

			if (data.hasOwnProperty(inputName)) {
				if (C.is(data[inputName], 'string')) {
					data[inputName] = [ data[inputName] ];
				}
				data[inputName].push(inputValue);
			} else {
				data[inputName] = inputValue;
			}

			return true;
		});

		return formIsValid ? JSON.stringify(data) : false;
	}

	/**
	 * options = {
	 *   form: HTMLElement,
	 *     ?form.dataset: {
	 *       controller,
	 *       module,
	 *       action,
	 *       resetonsubmit,
	 *       reloadonsubmit,
	 *       goal
	 *     },
	 *   ?blockElement: HTMLElement,
	 *   ?controller: string,
	 *   ?action: string,
	 *   ?isSilent: bool,
	 *   ?onSuccess: function
	 * }
	 */
	function submitFrom(options) {
		if (!options.hasOwnProperty('form')) {
			console.warn('Form is undefined');
			return;
		}

		C.updateEditorTextarea();

		const xhrOptions = {};
		const formOptions = options.form.dataset || {};

		const data = {
			controller: options.controller || formOptions.controller || 'specific',
			module: options.module || formOptions.module,
			action: options.action || formOptions.action,
			fields: retrieveFormDataToJson(options.form)
		};

		if (data.fields === false) {
			C.logError('Form fields is empty');
			return;
		}

		xhrOptions.data = data;
		xhrOptions.isSilent = options.hasOwnProperty('isSilent') ? options.isSilent : false;

		if (options.hasOwnProperty('blockElement')) {
			xhrOptions.onBeforeSend = () => {
				C.blockIt(options.blockElement);
			};
		}

		xhrOptions.onComplete = () => {
			const captchaRefreshBtn = options.form.querySelector('.captcha_refresh');

			if (captchaRefreshBtn) {
				captchaRefreshBtn.click();
			}

			if (options.hasOwnProperty('blockElement')) {
				C.unblockIt(options.blockElement);
			}
		};

		xhrOptions.onSuccess = (data) => {
			if (
				!C.is(formOptions.goal, 'undefined')
				&& C.is(C.onReachGoal, 'function')
			) {
				C.onReachGoal(formOptions.goal, 'form');
			}

			if (C.is(options.onSuccess, 'function')) {
				options.onSuccess(data);
			}

			if (!C.is(formOptions.resetonsubmit, 'undefined')) {
				options.form.reset();
			}

			if (!C.is(data.redirect, 'undefined')) {
				setTimeout(() => {
					C.redirect(data.redirect);
				}, 1000);
			} else if (!C.is(formOptions.reloadonsubmit, 'undefined')) {
				setTimeout(() => {
					d.location.reload();
				}, 2 * 1000);
			}
		};

		if (C.is(options.onError, 'function')) {
			xhrOptions.onError = options.onError;
		}

		C.xhr(xhrOptions);
	}

	/* specific functions for AEngine */

	const CKFinderOptions = { configPath: '', language: 'ru', skin: 'neko' };

	function clearForm(form) {
		form.find('input,textarea').val('');
		form.find('input[type="checkbox"]').prop('checked', false);
		form.find('select option').prop( 'selected', false );
		if (typeof CKEDITOR !== 'undefined') {
			for (let i in CKEDITOR.instances) {
				CKEDITOR.instances[i].setData('');
			}
		}
	}

	function openFileManager(input, options, callback) {
		let fmCallback;

		if (typeof callback === 'function') {
			fmCallback = callback;
		} else {
			fmCallback = function (finder) {
				finder.on('files:choose', function (evt) {
					input.val( evt.data.files.first().getUrl() );
				});
				finder.on('file:choose:resizedImage', function (evt) {
					input.val( evt.data.resizedUrl );
				});
			};
		}

		CKFinder.modal(Object.assign({}, CKFinderOptions, {
			chooseFiles: true,
			chooseFilesOnDblClick: true,
			height: 800,
			width: 1200,
			onInit: fmCallback
		}));
	}

	function openFMPopup(input, callback, options) {
		let fmCallback;

		if (typeof callback === 'function') {
			fmCallback = callback;
		} else {
			fmCallback = (finder) => {
				finder.on('files:choose', (evt) => {
					input.value = evt.data.files.first().getUrl();
				});
				finder.on('file:choose:resizedImage', (evt) => {
					input.value = evt.data.resizedUrl;
				});
			};
		}

		CKFinder.modal(Object.assign({}, CKFinderOptions, {
			chooseFiles: true,
			chooseFilesOnDblClick: true,
			height: 800,
			width: 1200,
			onInit: fmCallback
		}));
	}

	function initEditor($textarea) {
		let editor;

		$textarea.parent().prepend( Common.defaultOverlay.message );
		$textarea.attr('id', `text-editor_${$textarea.attr('name')}_${d.getElementsByClassName('cke').length}`);
		$textarea.removeAttr('readonly');
		editor = CKEDITOR.replace($textarea.attr('id'), {});
		editor.once('loaded', () => {
			$textarea.parent().children().first().remove();
		});
		CKFinder.setupCKEditor(editor, CKFinderOptions);
	}

	function updateEditorTextarea() {
		if (d.querySelector('.cke')) {
			Object.entries(CKEDITOR.instances).forEach(([inputId, editor]) => {
				d.getElementById(inputId).value = editor.getData();
			});
		}
	}

	/* /specific functions for AEngine */

	C.styleInput = styleInput;
	C.addFieldsValidation = addFieldsValidation;
	C.getInputValue = getInputValue;
	C.getInputTitle = getInputTitle;
	C.validateInputValue = validateInputValue;
	C.validateFormInputs = validateFormInputs;
	C.retrieveFormDataToJson = retrieveFormDataToJson;
	C.submitFrom = submitFrom;

	C.clearForm = clearForm;
	C.openFileManager = openFileManager;
	C.openFMPopup = openFMPopup;
	C.initEditor = initEditor;
	C.updateEditorTextarea = updateEditorTextarea;

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(Common, document));