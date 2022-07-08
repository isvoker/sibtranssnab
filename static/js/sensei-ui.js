// dependencies: sensei-core.js
(function(С, w, d, $) {
	'use strict';

	let scrollTimer;

	function init() {
		С.getError();
		С.getInfo();

		w.addEventListener('scroll', () => {
			clearTimeout(scrollTimer);
			if (!d.body.classList.contains('disable-hover')) {
				d.body.classList.add('disable-hover');
			}
			scrollTimer = setTimeout(function() {
				d.body.classList.remove('disable-hover');
			}, 500);
		}, false);

		if (d.querySelector('.flex') && !testFlexSupport()) {
			d.body.classList.add('flex-compat');
		}

		if ($.fn.UItoTop) {
			$().UItoTop({
				scrollSpeed: 1200,
				easingType: 'easeOutQuart'
			});
		}

		if ($.blockUI) {
			$.blockUI.defaults.message = С.defaultOverlay.message;
			$.blockUI.defaults.css = {};
			$.blockUI.defaults.overlayCSS.backgroundColor = С.defaultOverlay.backgroundColor;
			$.blockUI.defaults.overlayCSS.opacity = 1;
			$.blockUI.defaults.baseZ = 9999;
			$.blockUI.defaults.fadeOut = 0;
		}

		if ($.jGrowl) {
			$.jGrowl.defaults.position = 'bottom-right';
			$.jGrowl.defaults.life = 10000;
			$.jGrowl.defaults.closerTemplate = '<div>закрыть все</div>';
		}

		if ($.fn.fancybox) {
			let FancyboxConfig = {
				loop : false,
				keyboard    : true,
				arrows      : true,
				infobar     : true,
				toolbar     : true,
				buttons : [
					'fullScreen',
					'close'
				],
				protect : false,
				animationEffect : 'fade',
				transitionEffect : 'slide',
				lang : 'ru',
				i18n : {
					'ru' : {
						CLOSE       : 'Закрыть',
						NEXT        : 'След.',
						PREV        : 'Пред.',
						ERROR       : 'Изображение не может быть открыто. <br /> Пожалуйста, повторите попытку позже.',
						PLAY_START  : 'Включить слайдшоу',
						PLAY_STOP   : 'Остановить слайдшоу',
						FULL_SCREEN : 'На весь экран',
						THUMBS      : 'Иконки'
					}
				}
			};
			for (var i in FancyboxConfig) {
				$.fancybox.defaults[i] = FancyboxConfig[i];
			}
		}

		if ($.fn.dPaginator) {
			$('.paginator').each(function() {
				const $paginator = $(this);
				const selectedPage = parseInt(this.dataset.currentPage, 10);
				const pagesToShow = parseInt(this.dataset.pages_to_show);
				const pagesTotal = Math.ceil(this.dataset.totalItems / this.dataset.perPage);

				if (pagesTotal > 1) {
					$paginator.dPaginator({
						pagesTotal: pagesTotal,
						pagesToShow: pagesToShow,
						pageWidth: 15,
						selectedPage: selectedPage,
						acceleration: 3,
						onPageClicked: (num) => {
							let searchParams;
							let re;

							if (selectedPage !== num) {
								searchParams = (window.location.search || '?').substr(1);
								if (searchParams !== '') {
									re = new RegExp('(?:^page=[^&]*&?(?:page=[^&]*&?)*)|(?:&page=[^&]*)', 'gi');
									searchParams = searchParams.replace(re, '');
									if (searchParams.length) {
										searchParams += '&';
									}
								}
								С.redirect(`?${searchParams}page=${num}`);
							}
						}
					});
				} else {
					$paginator.remove();
				}
			});
		}

		if ($.fn.inputmask) {
			$('.js__mask-phone').inputmask("8 (999) 999-99 99");
		}

		С.addListenerByParents(d, 'js__scroll-to', 'click', (evt) => {
			const target = $(evt.target).attr('href');

			if (target.length && target !== '#') {
				С.ignoreEvent(evt);
				scrollTo( $(target).offset().top );
			}
		});
	}

	function show(el) {
		el.classList.remove('hidden-el');
	}

	function hide(el) {
		el.classList.add('hidden-el');
	}

	function blockIt(el, options) {
		if (!$.blockUI) {
			return;
		}

		if (С.is(el, 'undefined')) {
			$.blockUI();
		} else {
			const defaults = {
				css: {
					backgroundColor: 'transparent',
					border: '0 none'
				}
			};

			$(el).block(
				С.is(options, 'undefined')
					? defaults
					: $.extend({}, defaults, options)
			);
		}
	}

	function unblockIt(el) {
		if (!$.blockUI) {
			return;
		}

		if (С.is(el, 'undefined')) {
			$.unblockUI();
		} else {
			$(el).unblock();
		}
	}

	function __domToCSS(name) {
		return name.replace(/([A-Z])/g, (str, m1) => {
			return '-' + m1.toLowerCase();
		}).replace(/^ms-/, '-ms-');
	}

	function testFlexSupport() {
		try {
			let props, i;

			if ('CSS' in w && 'supports' in w.CSS) {
				const prop = 'flexBasis';
				const value = '1px';
				const ucProp = prop.charAt(0).toUpperCase() + prop.slice(1);
				const cssomPrefixes = ['Webkit', 'Moz', 'ms'];

				props = (cssomPrefixes.join(ucProp + ' ') + ucProp + ' ' + prop).split(' ');
				i = props.length;
				while (i--) {
					if (w.CSS.supports(__domToCSS(props[i]), value)) {
						return true;
					}
				}

				return false;
			} else {
				const elem = d.createElement('i');
				const style = elem.style;

				props = ['-webkit-flex', '-moz-flex', '-ms-flexbox', 'flex'];
				i = props.length;
				while (i--) {
					if ((style.display = props[i]) && style.display === props[i]) {
						return true;
					}
				}

				return false;
			}
		} catch(e) {
			return false;
		}
	}

	function makeImagePopup(link, overlayCSS) {
		$(link)
			.prop('rel', 'fancybox-gallery')
			.fancybox({
				openEffect: 'fade',
				closeEffect: 'fade',
				helpers: {
					overlay: {
						css: С.is(overlayCSS, 'undefined')
							? { backgroundColor: 'rgba(20,43,67,.6)' }
							: overlayCSS
					},
					title: {type: 'inside'}
				}
			});
	}

	function confirmSimpleDialog(title, confirmCallback, cancelCallback) {
		if ($.messager) {
			$.messager.confirm('', title, (consent) => {
				if (consent) {
					confirmCallback();
				} else {
					cancelCallback();
				}
			});
		} else {
			if (confirm(title)) {
				confirmCallback();
			} else {
				cancelCallback();
			}
		}
	}

	// Return the current scrollbar offsets as the x and y properties of an object
	function getScrollOffsets() {
		// This works for all browsers except IE versions 8 and before
		if (!С.is(w.pageXOffset, 'undefined')) {
			return {
				x: w.pageXOffset,
				y: w.pageYOffset
			};
		}
		// For IE (or any browser) in Standards mode
		if (d.compatMode === 'CSS1Compat') {
			return {
				x: d.documentElement.scrollLeft,
				y: d.documentElement.scrollTop
			};
		}
		// For browsers in Quirks mode
		return {
			x: d.body.scrollLeft,
			y: d.body.scrollTop
		};
	}

	function getScrollPercent() {
		return  (d.documentElement.scrollTop || d.body.scrollTop)
			/ ( (d.documentElement.scrollHeight || d.body.scrollHeight)
				- d.documentElement.clientHeight )
			* 100;
	}

	function scrollTo(offset) {
		$('html, body').animate({
			scrollTop: offset || 0
		}, 1000);
	}

	С.defaultOverlay = {
		backgroundColor: 'rgba(0,0,0,0.7)',
		message: '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>'
	};
	С.show = show;
	С.hide = hide;
	С.blockIt = blockIt;
	С.unblockIt = unblockIt;
	С.testFlexSupport = testFlexSupport;
	С.makeImagePopup = makeImagePopup;
	С.confirmSimpleDialog = confirmSimpleDialog;
	С.getScrollOffsets = getScrollOffsets;
	С.getScrollPercent = getScrollPercent;
	С.scrollTo = scrollTo;

	d.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(Common, window, document, jQuery));