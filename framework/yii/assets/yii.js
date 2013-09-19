/**
 * Yii JavaScript module.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */

/**
 * yii is the root module for all Yii JavaScript modules.
 * It implements a mechanism of organizing JavaScript code in modules through the function "yii.initModule()".
 *
 * Each module should be named as "x.y.z", where "x" stands for the root module (for the Yii core code, this is "yii").
 *
 * A module may be structured as follows:
 *
 * ~~~
 * yii.sample = (function($) {
 *     var pub = {
 *         // whether this module is currently active. If false, init() will not be called for this module
 *         // it will also not be called for all its child modules. If this property is undefined, it means true.
 *         isActive: true,
 *         init: function() {
 *             // ... module initialization code go here ...
 *         },
 *
 *         // ... other public functions and properties go here ...
 *     };
 *
 *     // ... private functions and properties go here ...
 *
 *     return pub;
 * })(jQuery);
 * ~~~
 *
 * Using this structure, you can define public and private functions/properties for a module.
 * Private functions/properties are only visible within the module, while public functions/properties
 * may be accessed outside of the module. For example, you can access "yii.sample.isActive".
 *
 * You must call "yii.initModule()" once for the root module of all your modules.
 */
yii = (function ($) {
	var pub = {
		/**
		 * The selector for links that support confirmation and form submission.
		 */
		linkClickSelector: 'a[data-confirm], a[data-method]',

		/**
		 * @return string|undefined the CSRF variable name. Undefined is returned is CSRF validation is not enabled.
		 */
		getCsrfVar: function () {
			return $('meta[name=csrf-var]').prop('content');
		},

		/**
		 * @return string|undefined the CSRF token. Undefined is returned is CSRF validation is not enabled.
		 */
		getCsrfToken: function () {
			return $('meta[name=csrf-token]').prop('content');
		},

		/**
		 * Displays a confirmation dialog.
		 * The default implementation simply displays a js confirmation dialog.
		 * You may override this by setting `yii.confirm`.
		 * @param message the confirmation message.
		 * @return boolean whether the user confirms with the message in the dialog
		 */
		confirm: function (message) {
			return confirm(message);
		},

		/**
		 * Returns a value indicating whether to allow executing the action defined for the specified element.
		 * @param $e the jQuery representation of the element
		 * @return boolean whether to allow executing the action defined for the specified element.
		 */
		allowAction: function ($e) {
			var message = $e.data('confirm');
			if (!message) {
				return true;
			}
			return pub.confirm(message);
		},

		/**
		 * Handles form submission triggered by elements with "method" data attribute.
		 * If the element is enclosed within an existing form, the form will be submitted.
		 * Otherwise, a new form will be created and submitted. The new form's method and action
		 * are determined using the element's "method" and "action" data attributes, respectively.
		 * If the "action" data attribute is not specified, it will try the "href" property and
		 * the current URL.
		 * @param $e the jQuery representation of the element
		 */
		handleSubmit: function ($e) {
			var method = $e.data('method');
			if (method === undefined) {
				return;
			}

			var $form = $e.closest('form');
			if (!$form.length) {
				var action = $e.data('action');
				if (action === undefined) {
					action = $e.prop('href');
					if (action === undefined) {
						action = window.location.href;
					}
				}
				$form = $('<form method="' + method + '" action="' + action + '"></form>');
				var target = $e.prop('target');
				if (target) {
					$form.attr('target', target);
				}
				if (!method.match(/(get|post)/i)) {
					$form.append('<input name="_method" value="' + method + '" type="hidden">');
				}
				var csrfVar = pub.getCsrfVar();
				if (csrfVar) {
					$form.append('<input name="' + csrfVar + '" value="' + pub.getCsrfToken() + '" type="hidden">');
				}
				$form.hide().appendTo('body');
			}

			var activeFormData = $form.data('yiiActiveForm');
			if (activeFormData) {
				// remember who triggers the form submission. This is used by yii.activeForm.js
				activeFormData.submitObject = $e;
			}

			$form.trigger('submit');
		},

		initModule: function (module) {
			if (module.isActive === undefined || module.isActive) {
				if ($.isFunction(module.init)) {
					module.init();
				}
				$.each(module, function () {
					if ($.isPlainObject(this)) {
						pub.initModule(this);
					}
				});
			}
		},

		init: function () {
			var $document = $(document);

			$document.on('click.yii', pub.linkClickSelector, function () {
				var $this = $(this);
				if (!pub.allowAction($this)) {
					$this.stopImmediatePropagation();
					return false;
				} else {
					if ($this.data('method')) {
						pub.handleSubmit($this);
						return false;
					}
				}
			});
		}
	};
	return pub;
})(jQuery);

jQuery(document).ready(function () {
	yii.initModule(yii);
});
