/**
 * Custom CSS editor enhancements.
 *
 * @package ARCHT_Widgets
 */

(($) => {
	'use strict';

	const addCustomCss = (css, context) => {
		if (!context) return;

		const model = context.model;
		const customCSS = model.get('settings').get('custom_css');
		const $elHandle = $(context.el).find('.elementor-editor-element-settings .elementor-editor-element-edit').first();
		let selector = '.elementor-element.elementor-element-' + model.get('id');

		if ('document' === model.get('elType')) {
			selector = elementor.config.document.settings.cssWrapperSelector;
		}

		if (customCSS) {
			css += customCSS.replace(/selector/g, selector);
			if ($elHandle.length) $elHandle.css('color', 'red');
		} else if ($elHandle.length) {
			$elHandle.css('color', 'white');
		}

		return css;
	};

	const addPageCustomCss = () => {
		let customCSS = elementor.settings.page.model.get('custom_css');

		if (customCSS) {
			customCSS = customCSS.replace(/selector/g, elementor.config.document.settings.cssWrapperSelector);
			elementor.settings.page.getControlsCSS().elements.$stylesheetElement.append(customCSS);
		}
	};

	elementor.hooks.addFilter('editor/style/styleText', addCustomCss);
	elementor.on('preview:loaded', addPageCustomCss);
})(jQuery);
