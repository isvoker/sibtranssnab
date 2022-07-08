(function(w, d, C, $) {
    'use strict';

    let ScrollTop;

    function init() {
        ScrollTop = $(w).scrollTop();

        $('.top-buttons-float').each(function () {
            if (ScrollTop >= $(this).offset().top) {
                $(this).addClass('positioned');
            } else {
                $(this).removeClass('positioned');
            }
        });

	    if ($.fn.datetimepicker) {
		    $.datetimepicker.setLocale('ru');
		    $('.js__datetimepicker').datetimepicker({
			    format: 'd.m.Y H:i:s',
			    step: 1
		    });
		    $('.js__datepicker').datetimepicker({
			    format: 'd.m.Y',
			    step: 1
		    });
		    $('.js__timepicker').datetimepicker({
			    format: 'H:i:s',
			    step: 1
		    });
	    }

        addListeners();
    }

    function addListeners() {
        $(d).on('click', '.js__open-filemanager', function (evt) {
            C.ignoreEvent(evt);
            let input = $(this).closest('.field').find('.select-image');
            C.openFMPopup(input[0]);
        }).on('dblclick', '.js__load-fm_input', function () {
            C.openFMPopup(this);
        }).on('click', '.js__load-fm_btn', function (evt) {
            C.ignoreEvent(evt);
            C.openFMPopup($(this).prev()[0]);
        }).on('click', '.js__load-editor', function () {
            C.initEditor($(this).parent().find('.editor_textarea'));
            $(this).remove();
        });

        C.addListenerByParents(d, 'js__switch', 'click', (evt) => {
            C.ignoreEvent(evt);
            evt.target.closest('.js__switchable').classList.toggle('active');
        });

        C.addListenerByParents(d, 'js__admin-nav-toggle', 'click', (evt) => {
            C.ignoreEvent(evt);
            evt.target.nextSibling.classList.toggle('active');
        });

        C.addListenerByParents(d, 'go-to-top', 'click', (evt) => {
            C.ignoreEvent(evt);
            $(w).scrollTop(0);
        });

        $(d).on('click', '.js__module-install', function (e) {
            C.ignoreEvent(e);

            const button = $(this);
            const ident = button.data('module-ident');

            if (!ident) {
                return false;
            }

            C.xhr({
                data: {
                    controller: 'modules',
                    action: 'module_install',
                    ident: ident
                },
                onBeforeSend: () => {
                    $.blockUI();
                },
                onComplete: () => {
                    $.unblockUI();
                },
                onSuccess: data => {
                    if (!C.is(data.message, 'undefined')) {
                        C.showInfo(data.message);
                    } else {
                        C.showInfo('Модуль успешно установлен');
                    }

                    button.parents('.js__system-check-message').remove();

                    if (!$('.js__system-check-message').length) {
                        $('.js__system-check-container').remove();
                    }
                },
                onError: data => {
                    C.showError(data.error);
                }
            });
        });

        $(d).on('change', '.js__bind_template_to_module', function (e) {
            C.ignoreEvent(e);

            let input = $(this),
                template_ident = input.val(),
                module_ident = input.closest('.js__modules-item').data('module-ident'),
                is_checked = input.prop('checked') ? 1 : 0;

            if (!template_ident || !module_ident) {
                return false;
            }

            C.xhr({
                data: {
                    controller: 'modules',
                    action: 'bind_module_to_template',
                    template_ident: template_ident,
                    module_ident: module_ident,
                    is_checked: is_checked
                },
                onBeforeSend: () => {
                    $.blockUI();
                },
                onComplete: () => {
                    $.unblockUI();
                },
                onError: data => {
                    C.showError(data.error);
                }
            });
        });

        w.addEventListener('scroll', () => {
            ScrollTop = $(w).scrollTop();
            $('.top-buttons-float').each(function () {
                if (ScrollTop >= $(this).offset().top) {
                    $(this).addClass('positioned');
                } else {
                    $(this).removeClass('positioned');
                }
            });
        }, false);

        /* deprecated */
        if (typeof CKEDITOR !== 'undefined') {
            $('.wysiwyg').each(function () {
                let attributeID = $(this).attr('id');
                if (attributeID) {
                    CKEDITOR.replace(attributeID);
                }
            });
        }
    }

    d.addEventListener('DOMContentLoaded', () => {
        init();
    });

}(window, document, Common, jQuery));