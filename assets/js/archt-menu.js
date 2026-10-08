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
				this.menu = this.$element[0].querySelector('.archt-menu');

				if (!this.menu) return;

				this.layout = this.menu.dataset.layout;
				this.onClick = this.onClick.bind(this);
				this.onKeydown = this.onKeydown.bind(this);
				this.onFocusout = this.onFocusout.bind(this);
				this.onPointer = this.onPointer.bind(this);
				this.onOutsideClick = this.onOutsideClick.bind(this);

				this.menu.addEventListener('click', this.onClick);
				this.menu.addEventListener('keydown', this.onKeydown);

				if ('expanded' !== this.layout) {
					this.menu.addEventListener('focusout', this.onFocusout);
					document.addEventListener('click', this.onOutsideClick);
				}

				if ('horizontal' === this.layout) this.menu.addEventListener('mouseover', this.onPointer);

				// The editor has no current page, so open the first branch to keep the submenu styles visible.
				if ('expanded' === this.layout && elementorFrontend.isEditMode() && !this.menu.querySelector('.is-open')) {
					const first = this.menu.querySelector('.archt-has-submenu');

					if (first) this.setOpen(first, true, false);
				}
			},

			unbindEvents() {
				if (!this.menu) return;

				this.menu.removeEventListener('click', this.onClick);
				this.menu.removeEventListener('keydown', this.onKeydown);
				this.menu.removeEventListener('focusout', this.onFocusout);
				this.menu.removeEventListener('mouseover', this.onPointer);
				document.removeEventListener('click', this.onOutsideClick);
			},

			/**
			 * The toggle button opens its submenu. A parent link opens it instead when it has no URL,
			 * or when there is no toggle to press (first click opens, second follows the link).
			 */
			onClick(event) {
				const toggle = event.target.closest('.archt-menu__toggle');

				if (toggle) {
					event.preventDefault();
					this.toggle(toggle.parentElement);
					return;
				}

				const link = event.target.closest('.archt-menu__link');
				const li = link && link.parentElement;

				if (!li || !li.classList.contains('archt-has-submenu')) return;

				const href = link.getAttribute('href') || '';
				const noToggle = !li.querySelector(':scope > .archt-menu__toggle');

				if ('' === href || '#' === href || (noToggle && !li.classList.contains('is-open'))) {
					event.preventDefault();
					this.toggle(li);
				}
			},

			onKeydown(event) {
				if ('Escape' !== event.key) return;

				const li = event.target.closest('.archt-has-submenu.is-open');

				if (!li || 'expanded' === this.layout) return;

				this.setOpen(li, false);
				(li.querySelector(':scope > .archt-menu__toggle') || li.querySelector(':scope > .archt-menu__link')).focus();
			},

			onFocusout(event) {
				this.menu.querySelectorAll('.archt-has-submenu.is-open').forEach((li) => {
					if (!li.contains(event.relatedTarget)) this.setOpen(li, false);
				});
			},

			onOutsideClick(event) {
				if (this.menu.contains(event.target)) return;

				this.menu.querySelectorAll('.archt-has-submenu.is-open').forEach((li) => this.setOpen(li, false));
			},

			onPointer(event) {
				const li = event.target.closest('.archt-has-submenu');

				if (li && li !== this.hovered) this.flip(li);

				this.hovered = li;
			},

			toggle(li) {
				const open = !li.classList.contains('is-open');

				// Dropdowns overlap, so only one branch stays open; Expanded closes siblings when set as an accordion.
				if (open && ('expanded' !== this.layout || '1' === this.menu.dataset.accordion)) {
					li.parentElement.querySelectorAll(':scope > .is-open').forEach((sibling) => this.setOpen(sibling, false));
				}

				this.setOpen(li, open);
			},

			setOpen(li, open, animate = true) {
				const toggle = li.querySelector(':scope > .archt-menu__toggle');
				const sub = li.querySelector(':scope > .archt-menu__sub');

				if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

				if (!open) li.querySelectorAll('.is-open').forEach((child) => this.setOpen(child, false, false));

				if ('horizontal' === this.layout && open && sub) this.flip(li);

				if ('expanded' !== this.layout || !animate || !sub) {
					li.classList.toggle('is-open', open);
					return;
				}

				const $sub = $(sub).stop(true, true);

				if (open) {
					li.classList.add('is-open');
					$sub.hide().slideDown(200, () => $sub.css('display', ''));
				} else {
					$sub.slideUp(200, () => {
						li.classList.remove('is-open');
						$sub.css('display', '');
					});
				}
			},

			// Closed dropdowns are not rendered, so they are shown invisibly for the measure.
			flip(li) {
				const sub = li.querySelector(':scope > .archt-menu__sub');

				if (!sub) return;

				sub.classList.remove('is-flipped');
				sub.style.cssText += 'display:block;visibility:hidden;transition:none';

				const rect = sub.getBoundingClientRect();
				const overflow = 'rtl' === getComputedStyle(sub).direction ? rect.left < 0 : rect.right > document.documentElement.clientWidth;

				sub.style.removeProperty('display');
				sub.style.removeProperty('visibility');
				sub.style.removeProperty('transition');
				sub.classList.toggle('is-flipped', overflow);
			},
		});

		elementorFrontend.elementsHandler.attachHandler('navigation-menu', NavMenuHandler);
	});
})(jQuery);
