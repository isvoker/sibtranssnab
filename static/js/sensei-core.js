const Common = function(w, d, $) {
	'use strict';

	let RSA_PUBLIC_KEY = '';
	let USER_TOKEN = '';
	let AJAX_URL = '';

	(function init() {
		w.onerror = errorHandler;
		USER_TOKEN = w.CSRF_KEY;
		AJAX_URL = '/ajax/';

		$.ajaxSetup({
			method: 'POST',
			url: getAjaxUrl(true),
			dataType: 'json',
			error: (jqXHR, textStatus, errorThrown) => {
				showError(`${textStatus}: ${errorThrown}`);
			}
		});

		$.fn.outerHTML = function() {
			return this.length
				? this[0].outerHTML || $(this).clone().wrap('<div></div>').parent().html()
				: null;
		};
	}());

	function getAjaxUrl(withToken) {
		return withToken
			? `${AJAX_URL}?csrf_key=${USER_TOKEN}`
			: AJAX_URL;
	}

	function getSessionToken() {
		return USER_TOKEN;
	}

	function is(obj, type) {
		return typeof obj === type;
	}

	function isEmptyStr(str) {
		return str === ''
			|| String(str).replace(/ +/i, '') === '';
	}

	function isJSON(text) {
		try {
			JSON.parse(text);
			return true;
		} catch (ex) {
			return false;
		}
	}

	function in_array(needle, haystack, argStrict) {
		const strict = Boolean(argStrict);
		let key = '';

		// we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
		// in just one for, in order to improve the performance
		// deciding which type of comparison will do before walk array
		if (strict) {
			for (key in haystack) {
				if (haystack.hasOwnProperty(key) && haystack[key] === needle) {
					return true;
				}
			}
		} else {
			for (key in haystack) {
				if (haystack.hasOwnProperty(key) && haystack[key] == needle) {
					return true;
				}
			}
		}

		return false;
	}

	function writeMessage(text, options) {
		if (is(text, 'undefined')) {
			console.error('[Common.writeMessage] Message is undefined');
			return;
		}

		options = options || {};
		const type = options.hasOwnProperty('type') ? options.type : 'log';
		const isSilent = options.hasOwnProperty('isSilent') ? options.isSilent : false;

		if (is(console[type], 'function')) {
			console[type](text);
		}

		if (isSilent) {
			return;
		}

		if ($.jGrowl) {
			const jGrowlOptions = options.hasOwnProperty('jGrowlOptions')
				? options.jGrowlOptions : {};
			$.jGrowl(text, jGrowlOptions);
		} else {
			alert(text);
		}
	}

	function logInfo(text) {
		writeMessage(text, {type: 'info', isSilent: true});
	}

	function logError(text) {
		writeMessage(text, {type: 'error', isSilent: true});
	}

	function showInfo(text) {
		if (text === null || isEmptyStr(text)) {
			return;
		}

		writeMessage(text, {type: 'info'});
	}

	function showError(text, dbgText, isSilent) {
		if (text === null || isEmptyStr(text)) {
			return;
		}

		writeMessage(text, {
			type: 'error',
			isSilent: isSilent,
			jGrowlOptions: {theme: 'jGrowl-error', life: 30000}
		});

		if (isSilent) {
			return;
		}

		let htmlBlock;

		if (
			!is(dbgText, 'undefined')
			&& !isEmptyStr(dbgText)
			&& (htmlBlock = d.getElementById('ErrorMsgDbg'))
		) {
			htmlBlock.classList.add('active');
			htmlBlock.innerHTML = dbgText;
		}
	}

	function getInfo() {
		const htmlBlock = d.getElementById('InfoMsg');

		showInfo(htmlBlock ? htmlBlock.innerHTML : '');
	}

	function getError() {
		const htmlBlock = d.getElementById('ErrorMsg');

		showError(htmlBlock ? htmlBlock.innerHTML : '');
	}

	function errorHandler(errorMsg, url, lineNo, columnNo/*, errorObj*/) {
		const error = {
			href: w.location.href,
			msg: errorMsg,
			file: url,
			line: lineNo,
			column: is(columnNo, 'undefined') ? 0 : columnNo,
			stack: ''
		};

		try {
			xhr({
				data: {
					controller: 'cms',
					action: 'jsErrorReport',
					error: error
				}
			});
		} catch (e) {
			console.error('Could not send error report');
		}

		return false;
	}

	/**
	 * ?foo=bar&arr[]=1
	 * -> {foo: 'bar', arr: [1]}
	 */
	function parseQueryString(queryString) {
		if (
			is(queryString, 'undefined')
			|| !is(queryString, 'string')
		) {
			queryString = w.location.search || '';
		}

		if (queryString === '') {
			return {};
		}

		queryString = queryString.substr(1);

		const params = queryString.split('&');
		const map = {};

		for (const i in params) {
			if (params.hasOwnProperty(i)) {
				const param = params[i].split('=');
				const paramName = decodeURIComponent( param[0].toLowerCase() );
				const paramValue = is(param[1], 'undefined')
					? true
					: is(param[1], 'string')
						? decodeURIComponent( param[1].toLowerCase() )
						: param[1];

				if (paramName.match(/\[([\w\d\-_]+)?\]$/)) {
					const key = paramName.replace(/\[([\w\d\-_]+)?\]/, '');

					if (!map.hasOwnProperty(key)) {
						map[key] = [];
					}
					if (paramName.match(/\[[\w\d\-_]+\]/)) {
						const index = /\[([\w\d\-_]+)\]/.exec(paramName)[1];
						map[key][index] = paramValue;
					} else {
						map[key].push(paramValue);
					}
				} else {
					if (!map.hasOwnProperty(paramName)) {
						map[paramName] = paramValue;
					} else if (map[paramName] && is(map[paramName], 'string')) {
						map[paramName] = [map[paramName]];
						map[paramName].push(paramValue);
					} else {
						map[paramName].push(paramValue);
					}
				}
			}
		}

		return map;
	}

	function handleState(data) {
		return Boolean(
			data
			&& data.hasOwnProperty('status')
			&& data.status === 200
		);
	}

	function checkResponse(data, errorText, isSilent) {
		isSilent = is(isSilent, 'undefined')
			? false : isSilent;

		if (data) {
			if (
				data.hasOwnProperty('status')
				&& data.status === 200
			) {
				return true;
			} else if (
				data.hasOwnProperty('error')
				&& data.hasOwnProperty('error_debug')
			) {
				showError(data.error, data.error_debug, isSilent);
			}
		}

		if (!is(errorText, 'undefined')) {
			showError(errorText, '', isSilent);
		}

		return false;
	}

	function xhr(options) {
		const settings = {};

		if (options.hasOwnProperty('async')) {
			settings.async = Boolean(options.async);
		}

		if (
			!options.hasOwnProperty('data')
			|| (!options.data.hasOwnProperty('controller') && !options.data.hasOwnProperty('module'))
			|| !options.data.hasOwnProperty('action')
		) {
			throw new Error('XMLHttpRequest body is incomplete');
		}

		settings.data = options.data;
		settings.data.csrf_key = w.CSRF_KEY;
		settings.dataType = 'json';

		const isSilent = options.hasOwnProperty('isSilent')
			? options.isSilent : false;

		if (!isSilent) {
			settings.error = (jqXHR, textStatus, errorThrown) => {
				showError(`${textStatus}: ${errorThrown}`);
			};
		}

		settings.method = options.hasOwnProperty('method')
			&& (options.method.toLowerCase() === 'get')
			? 'GET' : 'POST';

		if (
			options.hasOwnProperty('onBeforeSend')
			&& is(options.onBeforeSend, 'function')
		) {
			settings.beforeSend = options.onBeforeSend;
		}

		if (
			options.hasOwnProperty('onComplete')
			&& is(options.onComplete, 'function')
		) {
			settings.complete = options.onComplete;
		}

		settings.success = (data, textStatus, jqXHR) => {
			if (checkResponse(data, null, isSilent)) {
				if (!isSilent && data.hasOwnProperty('msg')) {
					showInfo(data.msg);
				}
				if (options.hasOwnProperty('onSuccess')) {
					options.onSuccess(data, textStatus, jqXHR);
				}
			} else if (options.hasOwnProperty('onError')) {
				options.onError(data, textStatus, jqXHR);
			}
		};

		$.ajax(AJAX_URL, settings);
	}

	function redirect(url, blank) {
		if (url.indexOf('://') === -1) {
			url = w.location.protocol + '//' + w.location.host
				+ ((url.indexOf('?') === 0) ? w.location.pathname : '') + url;
		}

		if (is(blank, 'undefined') || !blank) {
			w.location.href = url;
		} else {
			const win = w.open(url, '_blank');

			if (win !== null) {
				win.focus();
			}
		}
	}

	function download(url) {
		$(`<form action="${url}" method="GET"></form>`)
			.appendTo('body').submit().remove();
	}

	function loadExternalJS(path) {
		const tag = d.createElement('script');

		tag.setAttribute('type', 'text/javascript');
		tag.setAttribute('src', path);
		d.getElementsByTagName('head')[0].appendChild(tag);
	}

	/* private */ function localStorageHandler(action, key, value) {
		if (!is(w.localStorage, 'undefined') && w.localStorage) {
			try {
				return w.localStorage[action]('sensei_' + key, value);
			} catch (e) {
				console.error(`Method localStorage.${action} not supported`);
				return null;
			}
		}
	}

	function localStoragePut(key, value) {
		localStorageHandler('setItem', key, JSON.stringify(value));
	}

	function localStorageGet(key) {
		const value = localStorageHandler('getItem', key);
		return value !== null ? JSON.parse(value) : null;
	}

	function localStorageClear(key) {
		localStorageHandler('removeItem', key);
	}

	function getPublicKey() {
		xhr({
			async: false,
			method: 'GET',
			data: {
				controller: 'cms',
				action: 'getPublicKey'
			},
			onSuccess: (data) => {
				RSA_PUBLIC_KEY = data.key;
			},
			onError: () => {
				showError('RSA_PUBLIC_KEY is not found');
				throw new Error('RSA_PUBLIC_KEY is not found');
			}
		});
	}

	function base64_encode(str) {
		if (!str) {
			return str;
		}

		// Adapted from Solution #1 at https://developer.mozilla.org/en-US/docs/Glossary/Base64
		const encodeUTF8string = (str) => {
			// first we use encodeURIComponent to get percent-encoded UTF-8,
			// then we convert the percent encodings into raw bytes which
			// can be fed into the base64 encoding algorithm.
			return encodeURIComponent(str).replace(/%([0-9A-F]{2})/g,
				function toSolidBytes(match, p1) {
					return String.fromCharCode('0x' + p1);
				});
		};

		return w.btoa(encodeUTF8string(str));
	}

	function base64_decode(data) {
		if (!data) {
			return data;
		}

		// Adapted from Solution #1 at https://developer.mozilla.org/en-US/docs/Glossary/Base64
		const decodeUTF8string = function(str) {
			// Going backwards: from bytestream, to percent-encoding, to original string.
			return decodeURIComponent(str.split('').map((c) => {
				return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
			}).join(''));
		};

		return decodeUTF8string(w.atob(data));
	}

	function encodeString(str) {
		// like a ISO 9 and RFC 3986
		const ru2en = {
			ru_str: "АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя /",
			en_str: ['a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t',
				'u','f','x','c','ch','sh','shh','\'\'','y','\'','e','yu','ja',
				'a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t',
				'u','f','x','c','ch','sh','shh','\'\'','y','\'','e','yu','ja','_','_'],
			doIt: (inp) => {
				let a = inp.split('');

				for (let i = 0, aLen = a.length; i < aLen; i++) {
					a[i] = ru2en.ru2en[ a[i] ] || a[i];
				}
				return a.join('').replace(/[^A-Za-z0-9\-\._~]/g, '');
			}
		};
		const ruLen = ru2en.ru_str.length;

		ru2en.ru2en = {};
		for (let i = 0; i < ruLen; i++) {
			ru2en.ru2en[ ru2en.ru_str.charAt(i) ] = ru2en.en_str[i];
		}

		return ru2en.doIt(str);
	}

	function encryptString(str) {
		if (!is(str, 'string')) {
			return null;
		}

		const MAX_PART_LEN = 245;
		const SEPARATOR = '*';
		let cnt = 0;
		let part = '';
		let result = '';

		if (RSA_PUBLIC_KEY === '') {
			getPublicKey();
		}

		const cryptor = new JSEncrypt();
		cryptor.setPublicKey(RSA_PUBLIC_KEY);

		const saltyData = JSON.stringify({
			data: base64_encode(str),
			salt: getSessionToken()
		});
		let dataLen = saltyData.length;

		while (dataLen > 0) {
			part = saltyData.substr(cnt * MAX_PART_LEN, MAX_PART_LEN);
			result += (cnt ? SEPARATOR : '') + cryptor.encrypt(part);
			dataLen -= MAX_PART_LEN;
			cnt++;
		}

		return result;
	}

	function getOptionsFromClass(element, optName) {
		const regReplace = new RegExp('^' + optName + '\\-', 'i');

		return String(
			element.className.match('\\b' + optName + '\\-[\\w\\|]+\\b', 'gi') || ''
		).replace(regReplace, '').split('|');
	}

	function addListenerByParents(parents, childClass, events, listener) {
		if (!Array.isArray(parents)) {
			parents = [parents];
		}
		if (!Array.isArray(events)) {
			events = [events];
		}

		parents.forEach((parent) => {
			if (!parent) {
				return;
			}

			events.forEach((event) => {
				parent.addEventListener(event, (event) => {
					let target = event.target;

					if (
						target
						&& target.classList
						&& !target.classList.contains(childClass)
					) {
						target = target.closest('.' + childClass);
					}
					if (target) {
						listener.call(target, event);
					}
				});
			});
		});
	}

	/**
	 * @param evt Event
	 * @param allowOtherListeners bool, default TRUE
	 */
	function ignoreEvent(evt, allowOtherListeners) {
		if (evt.stopPropagation) {
			evt.stopPropagation();
		}

		evt.preventDefault();

		if (
			is(allowOtherListeners, 'undefined')
			|| allowOtherListeners === false
		) {
			evt.stopImmediatePropagation();
		}
	}

	return {
		getAjaxUrl: getAjaxUrl,
		getSessionToken: getSessionToken,
		is: is,
		isEmptyStr: isEmptyStr,
		isJSON: isJSON,
		in_array: in_array,
		logInfo: logInfo,
		logError: logError,
		showInfo: showInfo,
		showError: showError,
		getInfo: getInfo,
		getError: getError,
		errorHandler: errorHandler,
		parseQueryString: parseQueryString,
		handleState: handleState,
		checkResponse: checkResponse,
		xhr: xhr,
		redirect: redirect,
		download: download,
		loadExternalJS: loadExternalJS,
		localStoragePut: localStoragePut,
		localStorageGet: localStorageGet,
		localStorageClear: localStorageClear,
		base64_encode: base64_encode,
		base64_decode: base64_decode,
		encodeString: encodeString,
		encryptString: encryptString,
		getOptionsFromClass: getOptionsFromClass,
		addListenerByParents: addListenerByParents,
		ignoreEvent: ignoreEvent
	};

}(window, document, jQuery);