/**
 * Publish dropdown "Display Location" item.
 *
 * @package ARCHT_Widgets
 */

(() => {
	const v2 = window.elementorV2 || {};
	const menu = v2.editorAppBar && v2.editorAppBar.documentOptionsMenu;
	const useActiveDocument = v2.editorDocuments && (v2.editorDocuments.__useActiveDocument || v2.editorDocuments.useActiveDocument);

	if (!menu || !useActiveDocument || !v2.icons) {
		console.warn('Architect: app bar menu API not found, Publish dropdown item skipped.');
		return;
	}

	const config = window.archtAppBar || {};
	const applicableTypes = ['single', 'archive', 'header', 'footer', 'popup'];

	menu.registerAction({
		id: 'archt-display-location',
		priority: 10,
		useProps: () => {
			const doc = useActiveDocument();

			return {
				icon: v2.icons.SitemapIcon,
				title: config.title,
				visible: Boolean(doc) && applicableTypes.includes(doc.type.value),
				onClick: () => window.archtOpenDisplayTab && window.archtOpenDisplayTab(),
			};
		},
	});
})();
