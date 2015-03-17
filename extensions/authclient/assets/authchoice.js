/**
 * Yii auth choice widget.
 *
 * This is the JavaScript widget used by the yii\authclient\widgets\AuthChoice widget.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0
 */
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

                var authChoicePopup = $container.data('authChoicePopup');

                if (authChoicePopup) {
                    authChoicePopup.close();
                }

                var url = this.href;
                var popupOptions = $.extend({}, options.popup); // clone

                var localPopupWidth = this.getAttribute('data-popup-width');
                if (localPopupWidth) {
                    popupOptions.width = localPopupWidth;
                }
                var localPopupHeight = this.getAttribute('data-popup-height');
                if (localPopupWidth) {
                    popupOptions.height = localPopupHeight;
                }

                popupOptions.left = (window.screen.width - popupOptions.width) / 2;
                popupOptions.top = (window.screen.height - popupOptions.height) / 2;

                var popupFeatureParts = [];
                for (var propName in popupOptions) {
                    if (popupOptions.hasOwnProperty(propName)) {
                        popupFeatureParts.push(propName + '=' + popupOptions[propName]);
                    }
                }
                var popupFeature = popupFeatureParts.join(',');

                authChoicePopup = window.open(url, 'yii_auth_choice', popupFeature);
                authChoicePopup.focus();

                $container.data('authChoicePopup', authChoicePopup);
            });
        });
    };
});
