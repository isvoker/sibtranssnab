(function ($, C) {
	'use strict';

	class Gallery {
		container;
		module = 'PhotoGallery';
		loadButton;
		imagesContainer;

		constructor(dom_object) {
			this.container = dom_object;
			this.setHandlers();
		}

		setHandlers() {
			let $this = this;
			this.loadButton = this.container.find('.js__gallery-load-btn');
			this.imagesContainer = this.container.find('.photogallery-images-container:first');

			this.loadButton.click(function (evt) {
				C.ignoreEvent(evt);

				let options = {
					part: $this.container.data('part'),
					album_id: $this.container.data('album-id'),
					is_widget: $this.container.data('is-widget'),
				};

				if ((!options.part) || (!options.album_id)) {
					return false;
				}

				$this.loadGalleryImages(options);
			});
		}

		loadGalleryImages(options) {
			let $this = this;
			C.xhr({
				data: {
					module: this.module,
					action: 'get_images',
					data: options
				},
				onBeforeSend: () => {
					$this.loadButton.addClass('hidden');
					$this.container.find('.photogallery-request-runner').removeClass('hidden');
				},
				onComplete: () => {
					$this.loadButton.removeClass('hidden');
					$this.container.find('.photogallery-request-runner').addClass('hidden');
				},
				onSuccess: (data) => {
					if (!C.is(data.html, 'undefined')) {
						$this.imagesContainer.append(data.html);
					}

					if (!C.is(data.part, 'undefined')) {
						$this.container.data('part', data.part);
					} else {
						$this.container.removeAttr('data-part');
						$this.loadButton.remove();
					}
				},
				onError: (data) => {
					C.showError(data.error);
				}
			});
		}
	}

	document.addEventListener('DOMContentLoaded', () => {
		$('.js__gallery-container').each(function () {
			new Gallery($(this));
		});
	});

}(jQuery, Common));