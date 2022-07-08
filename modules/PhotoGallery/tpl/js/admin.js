(function (d, C, $) {
	'use strict';

	let container,
		module = 'PhotoGallery',
		is_sortable;

	function init() {
		container = $('.js__module');

		if (container.length !== 1) {
			return false;
		}

		is_sortable = container.data('is-sortable');

		setSettingsHandlers();
		setAlbumsHandlers();
		setImagesHanlders();
		setSelectHandlers();
		setClipboardHandlers();
	}

	function setSettingsHandlers() {
		container.find('.js__save-settings-btn').on('click', function (e) {
			C.ignoreEvent(e);

			container.find('.input-wrong').removeClass('input-wrong');

			let fields = collectSettingsFields();

			if (!fields) {
				return false;
			}

			if (!C.is(fields.error, 'undefined')) {
				C.showError(fields.error.message);
				if (fields.error.selector) {
					container.find(fields.error.selector).addClass('input-wrong');
				}
				return false;
			}

			C.xhr({
				data: {
					module: module,
					action: 'update_settings',
					settings: fields
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt()
				},
				onSuccess: () => {
					C.showInfo('Настройки модуля успешно сохранены');
				},
				onError: (data) => {
					C.showError(data.error);
				}
			});
		});
	}

	function setAlbumsHandlers() {
		if (container.find('.album-parent-gallery-id-field').length) {
			const ParentGallerySelectedElementId = container.find('.album-parent-gallery-id-field').val();
			container.find('.album-parent-album-id-field option').not('[value="0"]').not('[data-gallery-id="' + ParentGallerySelectedElementId + '"]').hide();

			container.find('.album-parent-gallery-id-field').change(function () {
				let GalleryId = $(this).val();

				container.find('.album-parent-album-id-field option').hide();
				container.find('.album-parent-album-id-field option[data-gallery-id="' + GalleryId + '"]').show();
				container.find('.album-parent-album-id-field option:first').show().prop('selected', true);
			});
		}

		container.find('.js__album-remove').click(function (e) {
			C.ignoreEvent(e);

			let ParentRow = $(this).parents('.items-list__item'),
				AlbumId = $(this).data('album-id');

			if (!AlbumId || !confirm('Внимание! Альбом и все его изображения будут безвозвратно удалены. Продолжить?')) {
				return false;
			}

			C.xhr({
				data: {
					module: module,
					action: 'AdminAlbumRemove',
					album_id: AlbumId
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: () => {
					ParentRow.remove();
					Common.showInfo('Альбом успешно удален');
				},
				onError: (data) => {
					C.showError(data.error);
				}
			});
		});

		if (is_sortable && $(d).find('.items-list__item').length > 1) {

			$(d).on('click', '.js__move-item', function (e) {
				C.ignoreEvent(e);
			});

			$(d).on('mousedown', '.js__move-item', function (e) {
				C.ignoreEvent(e);
			});

			$('.js__items-list').sortable({
				axis: 'y',
				handle: '.js__move-item',
				stop: function () {
					let iterator = 1;
					let pairs = [];
					$('.items-list__item').each(function () {
						pairs.push({
							id: $(this).data('album-id'),
							posit: iterator
						});
						iterator++;
					});

					C.xhr({
						data: {
							module: module,
							action: 'albums_set_position',
							data: pairs
						},
						onBeforeSend: function () {
							C.blockIt();
						},
						onComplete: function () {
							C.unblockIt();
						},
						onError: (data) => {
							Common.showError(data.error);
						}
					});
				}
			});
		}
	}

	function setImagesHanlders() {
		if (container.find('.gallery-images-move-to-gallery').length) {
			let SelectedMoveToGalleryId = container.find('.gallery-images-move-to-gallery').val();
			container.find('.gallery-images-move-to-album option').not('[value="0"]').not('[data-gallery-id="' + SelectedMoveToGalleryId + '"]').hide();

			container.find('.gallery-images-move-to-gallery').change(function () {
				let SelectedMoveToGalleryId = $(this).val();

				container.find('.gallery-images-move-to-album option').hide();
				container.find('.gallery-images-move-to-album option[data-gallery-id="' + SelectedMoveToGalleryId + '"]').show();
				container.find('.gallery-images-move-to-album option:first').show().prop('selected', true);
			});
		}

		if (container.find('.photo-parent-gallery-id-field').length) {
			let ParentGallerySelectedElementId = container.find('.photo-parent-gallery-id-field').val();
			container.find('.photo-parent-album-id-field option').not('[value="0"]').not('[data-gallery-id="' + ParentGallerySelectedElementId + '"]').hide();


			container.find('.photo-parent-gallery-id-field').change(function (event) {
				let GalleryId = $(this).val();

				container.find('.photo-parent-album-id-field option').hide();
				container.find('.photo-parent-album-id-field option[data-gallery-id="' + GalleryId + '"]').show();
				container.find('.photo-parent-album-id-field option:first').show().prop('selected', true);
			});
		}

		if (container.find('.gallery-albums-list-filter').length) {
			container.find('.gallery-albums-list-filter-button').click(function (e) {
				C.ignoreEvent(e);

				let FilterUrl = container.find('.gallery-albums-list-filter:first').find('option:selected').data('url');
				if (FilterUrl) {
					document.location.href = FilterUrl;
					return false;
				}
			});
		}

		$(document).on('click', '.images-list-remove-item', function (e) {
			C.ignoreEvent(e);

			let imageId = $(this).data('image-id');

			if (!imageId) {
				return false;
			}

			if (!confirm('Внимание! Изображение будет безвозвратно удалено. Продолжить?')) {
				return false;
			}

			C.xhr({
				data: {
					module: module,
					action: 'admin_images_remove',
					data: [imageId]
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: () => {
					$(`.images-list-item[data-image-id="${imageId}"]`).remove();
					Common.showInfo('Изображение успешно удалено');
				},
				onError: (data) => {
					C.showError(data.error);
				}
			});
		});

		container.find('.images-list-remove-selected').click(function (e) {
			C.ignoreEvent(e);

			let selected_item = $('.selected-item');

			if (!selected_item.length) {
				return false;
			}

			if (!confirm('Внимание! Все отмеченные изображения будут безвозвратно удалены. Продолжить?')) {
				return false;
			}

			let ImagesIds = [];

			selected_item.each(function () {
				ImagesIds.push(
					$(this).data('image-id')
				);
			});

			C.xhr({
				data: {
					module: module,
					action: 'admin_images_remove',
					data: ImagesIds
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: () => {
					selected_item.each(function () {
						$(this).remove();
					});
					Common.showInfo('Изображения успешно удалены');
				},
				onError: (data) => {
					C.showError(data.error);
				}
			});
		});

		container.find('.images-list-move-selected').click(function (e) {
			C.ignoreEvent(e);

			let selected_item = $('.selected-item');

			let currentAlbumId = Number($(this).data('current-album-id'));

			let data = {
				album_id: Number(container.find('.gallery-images-move-to-album').val())
			};

			if ((!selected_item.length) || (!data.album_id)) {
				return false;
			}

			if (data.album_id === currentAlbumId) {
				Common.showError('Назначение перемещения совпадает с текущим положением');
				return false;
			}

			if (!confirm('Внимание! Все отмеченные изображения будут перемещены в указанную гарелею. Продолжить?')) {
				return false;
			}

			data.images_ids = [];

			selected_item.each(function () {
				data.images_ids.push($(this).data('image-id'));
			});

			C.xhr({
				data: {
					module: module,
					action: 'admin_images_move_to_album',
					data: data
				},
				onBeforeSend: () => {
					C.blockIt();
				},
				onComplete: () => {
					C.unblockIt();
				},
				onSuccess: () => {
					selected_item.each(function () {
						$(this).fadeOut(
							500,
							function () {
								$(this).remove();
							}
						);
					});
					C.showInfo('Изображения успешно перемещены');
				},
				onError: (data) => {
					C.showError(data.error);
				}
			});
		});

		container.find('.js__select-image-from-fm').click(function (e) {
			C.ignoreEvent(e);

			let button = $(this);

			C.openFileManager(false, false, function (finder) {

				let albumId = button.data('album-id');

				finder.on('files:choose', function (evt) {
					const filesObjects = evt.data.files;
					let files = [];

					filesObjects.forEach(function (fileObj) {
						files.push(fileObj.getUrl());
					});

					selectImageFromFM({
						images_urls: files,
						album_id: albumId
					});
				});

				finder.on('file:choose:resizedImage', function (evt) {
					let Files = [evt.data.resizedUrl];

					selectImageFromFM({
						images_urls: Files,
						album_id: albumId
					});
				});

			});
		});
	}

	function setSelectHandlers() {
		$(document).on('click', '.images-item-selector', function (e) {
			C.ignoreEvent(e);

			if ($(this).hasClass('checked')) {
				$(this).parents('.images-list-item').removeClass('selected-item');
				$(this).removeClass('checked');
			} else {
				$(this).parents('.images-list-item').addClass('selected-item');
				$(this).addClass('checked');
			}
		});

		$('.images-list-select-all').on('click', function (e) {
			C.ignoreEvent(e);

			if (!parseInt($(this).attr('data-selected'))) {
				$('.images-item-selector').each(function () {
					$(this).addClass('checked');
					$(this).parents('.images-list-item').addClass('selected-item');
				});
				$('.images-list-select-all').attr('data-selected', '1').text($(this).data('unselect-text'));
			} else {
				$('.images-item-selector').each(function () {
					$(this).removeClass('checked');
					$(this).parents('.images-list-item').removeClass('selected-item');
				});
				$('.images-list-select-all').removeAttr('data-selected').text($(this).data('select-text'));
			}
		});
	}

	function setClipboardHandlers() {
		if ((typeof ClipboardJS !== 'undefined') && container.find('.js__copy-to-clipboard').length) {
			container.find('.js__copy-to-clipboard').click(function (evt) {
				C.ignoreEvent(evt);
			});
			new ClipboardJS('.js__copy-to-clipboard');
		}
	}

	function selectImageFromFM(data) {
		C.xhr({
			data: {
				module: module,
				action: 'admin_add_images_to_album',
				data: data
			},
			onBeforeSend: () => {
				C.blockIt();
			},
			onComplete: () => {
				C.unblockIt();
			},
			onSuccess: (data) => {
				if (typeof data.selected_images !== 'undefined') {
					$.each(data.selected_images, function (index, image_html) {
						if (image_html) {
							container.find('.images-list').append(image_html);
							container.find('.items-list-empty').addClass('hidden');
						}
					});
				}
			},
			onError: (data) => {
				C.showError(data.error);
			}
		});
	}

	function collectSettingsFields() {
		let fields = {
			widget_is_enabled: container.find('[name="widget_is_enabled"]').is(':checked') ? 1 : 0,
			photos_per_page: container.find('[name="photos_per_page"]').val(),
			photos_per_page_in_widget: container.find('[name="photos_per_page_in_widget"]').val(),
		}

		if (!fields.photos_per_page || !fields.photos_per_page_in_widget) {
			fields.error = {
				message: 'Заполните обязательные поля',
			};
			return fields;
		}

		if (isNaN(fields.photos_per_page) || !isFinite(fields.photos_per_page)) {
			fields.error = {
				message: 'Значение должно быть числовым',
				selector: '[name="photos_per_page"]'
			};
			return fields;
		}

		if (isNaN(fields.photos_per_page_in_widget) || !isFinite(fields.photos_per_page_in_widget)) {
			fields.error = {
				message: 'Значение должно быть числовым',
				selector: '[name="photos_per_page_in_widget"]'
			};
			return fields;
		}

		return fields;
	}

	document.addEventListener('DOMContentLoaded', () => {
		init();
	});

}(document, Common, jQuery));