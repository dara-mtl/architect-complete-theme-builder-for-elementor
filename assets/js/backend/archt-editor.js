/**
 * Editor enhancements: Apply & Preview, Display Location reminder and the Specific Post/Term search.
 *
 * @package ARCHT_Widgets
 */

(($) => {
	'use strict';

	const config = window.archtEditor || {};
	const applicableTypes = ['single', 'archive', 'header', 'footer', 'popup'];
	let previewWindow = null;
	let suppressReminder = false;
	let suppressNext = false;

	const buildPreviewUrl = () => {
		const previewAs = elementor.settings.page.model.get('preview_as');
		const parts = previewAs ? previewAs.split('/') : [];
		const previewId = 2 === parts.length ? parts[1] : '';

		if (!previewId) return null;

		const separator = config.preview.url.includes('?') ? '&' : '?';

		return config.preview.url + separator + 'archt_preview=1'
			+ '&archt_template_id=' + encodeURIComponent(elementor.config.document.id)
			+ '&archt_preview_id=' + encodeURIComponent(previewId)
			+ '&archt_nonce=' + encodeURIComponent(config.preview.nonce);
	};

	const openOrReload = (url) => {
		if (previewWindow && !previewWindow.closed) {
			previewWindow.location.replace(url);
			previewWindow.focus();
		} else {
			previewWindow = window.open(url, '_blank');
		}
	};

	const hasDisplayCondition = () => {
		try {
			const inc = elementor.settings.page.model.get('display_conditions_inc');
			return Array.isArray(inc) ? inc.length > 0 : Boolean(inc);
		} catch (e) {
			console.warn('Architect: could not read display_conditions_inc', e);
			return false;
		}
	};

	const openDisplayTab = () => {
		try {
			$e.route('panel/page-settings/settings');
		} catch (err) {
			$('#elementor-panel-footer-settings').trigger('click');
		}

		setTimeout(() => $('.elementor-tab-control-archt_display').trigger('click'), 50);
	};

	const showReminderDialog = () => new Promise((resolve) => {
		elementorCommon.dialogsManager.createWidget('confirm', {
			id: 'archt-display-conditions-reminder',
			headerMessage: config.i18n.dialogTitle,
			message: config.i18n.dialogMessage,
			strings: {
				confirm: config.i18n.btnSet,
				cancel: config.i18n.btnSave,
			},
			defaultOption: 'confirm',
			onConfirm: () => resolve('set'),
			onCancel: () => resolve('save'),
		}).show();
	});

	const registerAfterSave = () => {
		class ArchtAfterSave extends $e.modules.hookData.After {
			getCommand() {
				return 'document/save/save';
			}

			getId() {
				return 'archt/preview-reload--document/save/save';
			}

			getConditions() {
				return Boolean(previewWindow && !previewWindow.closed);
			}

			apply() {
				const url = buildPreviewUrl();
				if (url) previewWindow.location.replace(url);
			}
		}

		new ArchtAfterSave().register();
	};

	const initSaveIntercept = () => {
		if (!elementor || !elementor.saver) return;

		class BeforeSaveReminder extends $e.modules.hookUI.Before {
			getCommand() {
				return 'document/save/save';
			}

			getId() {
				return 'before-save-reminder';
			}

			getConditions() {
				if (suppressReminder) return false;

				if (suppressNext) {
					suppressNext = false;
					return false;
				}

				return applicableTypes.includes(config.documentType) && !hasDisplayCondition();
			}

			apply(args) {
				return showReminderDialog().then((choice) => {
					if ('set' === choice) {
						openDisplayTab();
						return Promise.reject('archt: save deferred');
					}

					suppressNext = true;
					$e.run('document/save/default', { status: args.status });
				}).catch(() => {});
			}
		}

		$e.hooks.registerUIBefore(new BeforeSaveReminder());
	};

	const searchControls = {
		selected_posts_inc: 'post',
		selected_posts_exc: 'post',
		selected_terms_inc: 'term',
		selected_terms_exc: 'term',
	};

	const initSearchControl = (name, mode) => {
		const $select = $('.elementor-control-' + name).find('select');

		if (!$select.length || $select.data('archtSelect2')) return;

		if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');

		$select.select2({
			allowClear: true,
			minimumInputLength: 1,
			ajax: {
				url: config.select2.url,
				dataType: 'json',
				delay: 250,
				data: (params) => ({
					action: 'archt_search_related_items',
					nonce: config.select2.nonce,
					q: params.term || '',
					mode: mode,
				}),
				processResults: (response) => ({ results: response.success && response.data ? response.data : [] }),
			},
		}).data('archtSelect2', true);
	};

	const initSearchControls = () => Object.entries(searchControls).forEach(([name, mode]) => initSearchControl(name, mode));

	window.archtOpenDisplayTab = openDisplayTab;

	$(window).on('elementor:init', () => {
		elementor.channels.editor.on('elementorThemeBuilder:ApplyPreview', () => {
			suppressReminder = true;

			$e.run('document/save/auto', { force: true }).then(() => {
				suppressReminder = false;
				const url = buildPreviewUrl();
				if (url) openOrReload(url);
			});
		});

		registerAfterSave();
		setTimeout(initSaveIntercept, 0);

		// The panel re-renders on each section open.
		$(document).on('mousedown', '#elementor-panel, .elementor-panel', initSearchControls);
		initSearchControls();
	});
})(jQuery);
