jQuery(function($) {
	$.fn.authchoice = function(options) {
		options = $.extend({
			popup: {
				resizable: 'yes',
				scrollbars: 'no',
				toolbar: 'no',
				menubar: 'no',
				location: 'no',
				directories: 'no',
				status: 'yes',
				width: 450,
				height: 380
			}
		}, options);

		return this.each(function() {
			var $container = $(this);

			$container.find('a').on('click', function(e) {
				e.preventDefault();

				var authChoicePopup = null;

				if (authChoicePopup = $container.data('authChoicePopup')) {
					authChoicePopup.close();
				}

				var url = this.href;
				var popupOptions = options.popup;

				var localPopupWidth = this.getAttribute('data-popup-width');
				if (localPopupWidth) {
					popupOptions.width = localPopupWidth;
				}
				var localPopupHeight = this.getAttribute('data-popup-height');
				if (localPopupWidth) {
					popupOptions.height = localPopupHeight;
				}

				popupOptions.left = (window.screen.width - options.popup.width) / 2;
				popupOptions.top = (window.screen.height - options.popup.height) / 2;

				var popupFeatureParts = [];
				for (var propName in popupOptions) {
					popupFeatureParts.push(propName + '=' + popupOptions[propName]);
				}
				var popupFeature = popupFeatureParts.join(',');

				authChoicePopup = window.open(url, 'yii_auth_choice', popupFeature);
				authChoicePopup.focus();

				$container.data('authChoicePopup', authChoicePopup);
			});
		});
	};
});
