/**
 * Navigation Menu widget.
 *
 * @package ARCHT_Widgets
 */

(($) => {
	'use strict';

	window.addEventListener('elementor/frontend/init', function () {
		const NavMenuHandler = elementorModules.frontend.handlers.Base.extend({
			bindEvents() {
				this.initMenu();
			},

			getDefaultSettings() {
				return {
					selectors: {
						menuLinks: '.archt-has-submenu-container > a.archt-menu-item',
						menuIcons: '.archt-has-submenu-container svg',
						openSubmenus: '.archt-has-submenu .sub-menu.open',
						submenuSelector: ':scope > .sub-menu, :scope > .archt-has-submenu-container ~ .sub-menu',
						hasSubmenu: '.archt-has-submenu',
					},
				};
			},

			getDefaultElements() {
				const selectors = this.getSettings('selectors');

				return {
					$menuLinks: this.$element.find(selectors.menuLinks),
					$menuIcons: this.$element.find(selectors.menuIcons),
				};
			},

			initMenu() {
				const $menu = this.$element.find('[data-layout]').first();

				if ('expanded' === $menu.data('layout')) {
					this.initExpandedMenu($menu);
					return;
				}

				this.bindMenuLinkEvents();
				this.bindMenuIconEvents();
				this.bindOutsideClickEvent();
				this.bindHoverEvents();
				this.initSubmenuPositions();
			},

			/**
			 * Expanded layout: submenus open in the flow, on click only. The arrow toggles; a parent link toggles
			 * when it has no URL, or when there is no arrow to click (first click opens, second follows the link).
			 */
			initExpandedMenu($menu) {
				if ($menu.hasClass('archt-expanded-always-open')) return;

				const accordion = '1' === String($menu.data('accordion'));

				const setOpen = (li, open, animate) => {
					const $sub = $(li).children('.sub-menu');

					$(li).toggleClass('archt-submenu-open', open);
					$(li).find('> .archt-has-submenu-container > a').attr('aria-expanded', open ? 'true' : 'false');

					if (!animate) {
						$sub.toggleClass('open', open);
						return;
					}

					$sub.stop(true, true)[open ? 'slideDown' : 'slideUp'](200, () => {
						$sub.toggleClass('open', open).css('display', '');
					});
				};

				const toggle = (li) => {
					const open = !$(li).hasClass('archt-submenu-open');

					if (open && accordion) {
						$(li).siblings('.archt-submenu-open').each((i, sibling) => setOpen(sibling, false, true));
					}

					setOpen(li, open, true);
				};

				$menu.find('.archt-has-submenu').each((i, li) => {
					setOpen(li, $(li).is('.current-menu-ancestor, .current-menu-parent'), false);
				});

				// The editor has no current page, so open the first branch to keep the submenu styles visible.
				if (elementorFrontend.isEditMode() && !$menu.find('.archt-submenu-open').length) {
					setOpen($menu.find('.archt-has-submenu').first()[0], true, false);
				}

				$menu.on('click', '.archt-has-submenu-container svg', function (event) {
					event.preventDefault();
					toggle(this.closest('.archt-has-submenu'));
				});

				$menu.on('click', '.archt-has-submenu-container > a', function (event) {
					const li = this.closest('.archt-has-submenu');
					const href = this.getAttribute('href') || '';
					const noUrl = '' === href || '#' === href;
					const noArrow = !this.parentNode.querySelector('svg');

					if (noUrl || (noArrow && !$(li).hasClass('archt-submenu-open'))) {
						event.preventDefault();
						toggle(li);
					}
				});
			},

			adjustSubmenuPosition(submenu) {
				submenu.classList.remove('archt-submenu-flip-left');

				if (submenu.getBoundingClientRect().right > window.innerWidth) {
					submenu.classList.add('archt-submenu-flip-left');
				}
			},

			initSubmenuPositions() {
				if (window.innerWidth <= 1024) return;

				const selectors = this.getSettings('selectors');
				const self = this;

				this.$element.find(selectors.hasSubmenu).each(function () {
					const submenu = this.querySelector(selectors.submenuSelector);

					if (!submenu) return;

					// Temporarily make the submenu measurable without showing it.
					submenu.style.visibility = 'hidden';
					submenu.style.display = 'block';
					self.adjustSubmenuPosition(submenu);
					submenu.style.display = '';
					submenu.style.visibility = '';
				});
			},

			bindHoverEvents() {
				const selectors = this.getSettings('selectors');
				const self = this;

				this.$element.find(selectors.hasSubmenu).on('mouseenter', function () {
					if (window.innerWidth <= 1024) return;

					const submenu = this.querySelector(selectors.submenuSelector);

					if (submenu) self.adjustSubmenuPosition(submenu);
				});
			},

			bindMenuLinkEvents() {
				const selectors = this.getSettings('selectors');
				const self = this;

				this.elements.$menuLinks.on('click', function (event) {
					event.stopImmediatePropagation();

					const parentLi = this.closest('.archt-has-submenu');

					if (!parentLi) return;

					const submenu = parentLi.querySelector(selectors.submenuSelector);

					if (!submenu) return;

					if (window.innerWidth <= 1024 || !submenu.classList.contains('open')) {
						event.preventDefault();
						submenu.classList.toggle('open');

						if (submenu.classList.contains('open')) self.adjustSubmenuPosition(submenu);
					}
				});
			},

			bindMenuIconEvents() {
				const selectors = this.getSettings('selectors');
				const self = this;

				this.elements.$menuIcons.on('click', function (event) {
					event.preventDefault();
					event.stopImmediatePropagation();

					const parentLi = this.closest('.archt-has-submenu');

					if (!parentLi) return;

					const submenu = parentLi.querySelector(selectors.submenuSelector);

					if (!submenu) return;

					submenu.classList.toggle('open');

					if (submenu.classList.contains('open')) self.adjustSubmenuPosition(submenu);
				});
			},

			// Namespaced per widget, so destroying one menu leaves the others' handlers in place.
			getEventNamespace() {
				return '.archtNavMenu-' + this.getID();
			},

			bindOutsideClickEvent() {
				const selectors = this.getSettings('selectors');
				const $element = this.$element;

				$(document).on('click' + this.getEventNamespace(), function (event) {
					$element.find(selectors.openSubmenus).each(function () {
						if (!$(this).closest('.archt-has-submenu')[0].contains(event.target)) {
							$(this).removeClass('open');
						}
					});
				});
			},

			onDestroy() {
				$(document).off(this.getEventNamespace());
			},
		});

		elementorFrontend.elementsHandler.attachHandler('navigation-menu', NavMenuHandler);
	});
})(jQuery);
