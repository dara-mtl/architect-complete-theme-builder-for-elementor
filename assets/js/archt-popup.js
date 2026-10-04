/**
 * Popup triggers and close handling.
 *
 * @package ARCHT_Widgets
 */

(($) => {
	'use strict';

	$(window).on('elementor/frontend/init', function () {
		const $document = $(document);
		const config = window.archtPopup || {};
		const isEditMode = Boolean(window.elementorFrontend && elementorFrontend.isEditMode && elementorFrontend.isEditMode());

		const CLOSE_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
			+ '<path d="M19 6.41 17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>'
			+ '</svg>';

		/**
		 * Move the popup content into a scrolling inner box, so the close button can sit outside of it.
		 */
		function wrapContent(popup) {
			if (popup.querySelector(':scope > .archt-popup-inner')) return;

			const inner = document.createElement('div');

			inner.className = 'archt-popup-inner';

			while (popup.firstChild) inner.appendChild(popup.firstChild);

			popup.appendChild(inner);
		}

		/**
		 * Inject the close button. The editor builds the popup children in JS, so PHP markup never reaches the canvas.
		 */
		function ensureCloseButton(popup) {
			if ('0' === popup.getAttribute('data-close-button') || popup.querySelector('.archt-popup-close-btn')) return;

			// Frontend: on the popup box, outside the scrolling content. Editor: in the first container, keeping the
			// document's own children untouched for Elementor (.e-con covers v3 and v4).
			const host = isEditMode ? popup.querySelector(':scope > .e-con') || popup : popup;
			const button = document.createElement('a');

			button.className = 'archt-popup-close-btn';
			button.setAttribute('href', '#');
			button.setAttribute('role', 'button');
			button.setAttribute('tabindex', '0');
			button.setAttribute('aria-label', config.closeLabel || 'Close');
			button.innerHTML = CLOSE_ICON;

			host.insertBefore(button, host.firstChild);
		}

		if (!isEditMode) document.querySelectorAll('[data-popup-id]').forEach(wrapContent);

		document.querySelectorAll('[data-popup-id]').forEach(ensureCloseButton);

		if (isEditMode) {
			document.querySelectorAll('[data-popup-id]').forEach((popup) => {
				// Re-inject when the editor re-renders; subtree, as the button sits inside the container.
				new MutationObserver(() => ensureCloseButton(popup)).observe(popup, { childList: true, subtree: true });
			});

			return;
		}

		document.documentElement.style.setProperty('--scrollbar-width', (window.innerWidth - document.documentElement.clientWidth) + 'px');

		const viewsKey = ($popup) => 'archt_popup_views_' + $popup.data('popup-id');
		const showLimit = ($popup) => parseInt($popup.data('show-times'), 10) || 0;

		function storedViews($popup) {
			try {
				return parseInt(localStorage.getItem(viewsKey($popup)), 10) || 0;
			} catch (err) {
				return 0;
			}
		}

		function canShowPopup($popup) {
			const limit = showLimit($popup);
			return 0 === limit || storedViews($popup) < limit;
		}

		// Counted on open only, so blocked triggers don't use up the allowance.
		function recordView($popup) {
			if (0 === showLimit($popup)) return;

			try {
				localStorage.setItem(viewsKey($popup), storedViews($popup) + 1);
			} catch (err) {
				// Storage unavailable, the view limit is skipped.
			}
		}

		function clearCloseTimer($popup) {
			const timer = $popup.data('archtCloseTimer');

			if (timer) {
				clearTimeout(timer);
				$popup.removeData('archtCloseTimer');
			}
		}

		function openPopup($popup) {
			if (!$popup || !$popup.length) return;
			if ($popup.hasClass('is-open') && !$popup.hasClass('is-closing')) return;
			if (!canShowPopup($popup)) return;

			if (1 == $popup.data('exclusive')) {
				$('[data-popup-id].is-open').each(function () {
					const $other = $(this);

					if ($other.data('popup-id') !== $popup.data('popup-id')) closePopup($other);
				});
			}

			const entrance = $popup.data('entrance-animation') || '';
			const exit = $popup.data('exit-animation') || '';

			clearCloseTimer($popup);
			$popup.off('.archtPopup');
			$popup.removeClass('reverse-animation archt-hide is-closing ' + exit);

			if (entrance) $popup.addClass(entrance);

			$popup.removeAttr('aria-hidden');
			$popup.addClass('is-open');
			recordView($popup);

			if (1 == $popup.data('prevent-scroll')) $('body').addClass('archt-popup-open');

			$('.popup-bg-' + $popup.data('popup-id')).show();
		}

		function finishClose($popup, exit) {
			clearCloseTimer($popup);
			$popup.off('.archtPopup');
			$popup.removeClass('is-open is-closing reverse-animation ' + exit).addClass('archt-hide').attr('aria-hidden', 'true');

			// Another open popup may still need the page locked.
			if (!$('[data-popup-id].is-open[data-prevent-scroll="1"]').length) $('body').removeClass('archt-popup-open');
			$('.popup-bg-' + $popup.data('popup-id')).fadeOut();
		}

		function closePopup($popup) {
			if (!$popup || !$popup.length) return;
			if ($popup.hasClass('is-closing') || !$popup.hasClass('is-open')) return;

			const entrance = $popup.data('entrance-animation') || '';
			const exit = $popup.data('exit-animation') || '';

			$popup.off('.archtPopup');
			$popup.addClass('is-closing');

			if (entrance) $popup.removeClass(entrance);

			if (!exit) {
				finishClose($popup, exit);
				return;
			}

			// animationend bubbles up from widgets animating inside the popup.
			$popup.on('animationend.archtPopup webkitAnimationEnd.archtPopup', (e) => {
				if (e.target === $popup[0]) finishClose($popup, exit);
			});

			setTimeout(() => {
				$popup.addClass(exit + ' reverse-animation');

				// No animationend without keyframes or with reduced motion; close on a timer.
				const seconds = parseFloat(window.getComputedStyle($popup[0]).animationDuration) || 0;

				$popup.data('archtCloseTimer', setTimeout(() => finishClose($popup, exit), (seconds * 1000) + 150));
			}, 10);
		}

		$document.on('click', function (e) {
			const $target = $(e.target);

			// Don't swallow clicks on popup content that also matches the trigger selector.
			if ($target.closest('[data-popup-id]').length) return;

			$('[data-open-class]').each(function () {
				const $popup = $(this);
				const selector = $popup.data('open-class');

				if (!selector) return;

				let matched;

				try {
					matched = $target.closest(selector).length;
				} catch (err) {
					return;
				}

				if (matched) {
					e.preventDefault();
					openPopup($popup);
				}
			});
		});

		$document.on('click', '.archt-popup-close-btn, .archt-popup-close, .archt-popup-close *, [data-popup-close]', function (e) {
			e.preventDefault();
			$(this).blur();
			closePopup($(this).closest('[data-popup-id]'));
		});

		$document.on('keydown', function (e) {
			if ('Escape' !== e.key) return;

			const $popup = $('[data-popup-id].is-open').last();

			if ($popup.length) closePopup($popup);
		});

		$('[data-popup-id]').each(function () {
			const $popup = $(this);
			const triggers = ($popup.data('triggers') || '').toString().split(',');

			triggers.forEach((trigger) => {
				trigger = trigger.trim();

				if ('onload' === trigger) openPopup($popup);

				if ('delay' === trigger) {
					const delay = parseFloat($popup.data('delay')) || 0;

					setTimeout(() => openPopup($popup), delay * 1000);
				}

				if ('scroll' === trigger) {
					const threshold = parseInt($popup.data('scroll'), 10) || 0;
					const onScroll = () => {
						const scrollable = document.body.scrollHeight - window.innerHeight;
						const percent = scrollable > 0 ? (window.scrollY / scrollable) * 100 : 0;

						if (percent >= threshold) {
							window.removeEventListener('scroll', onScroll);
							openPopup($popup);
						}
					};

					window.addEventListener('scroll', onScroll, { passive: true });
				}
			});
		});

		$document.on('click', '[class^="popup-bg-"]', function (e) {
			const $overlay = $(this);

			if (!$(e.target).is($overlay)) return;

			const overlayClass = $overlay.attr('class').split(' ').find((c) => 0 === c.indexOf('popup-bg-'));

			if (!overlayClass) return;

			const $popup = $('[data-popup-id="' + overlayClass.replace('popup-bg-', '') + '"]');

			if (1 == $popup.data('close-on-bg')) closePopup($popup);
		});
	});
})(jQuery);
