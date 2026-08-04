/**
 * Promptless Search Overlay
 *
 * Full-screen header search with instant results (docs/SEARCH_DESIGN.md).
 *
 * - Instant results via core REST /wp/v2/search (debounced 250ms, min 2
 *   chars, in-flight requests aborted per keystroke).
 * - ARIA combobox pattern (APG): the input owns aria-expanded and
 *   aria-activedescendant; results are role=listbox/option; a polite
 *   live region announces result counts.
 * - Keyboard: "/" or Cmd/Ctrl+K opens (never while typing in a field),
 *   Esc closes, ArrowUp/Down traverse, Enter follows the active result
 *   or submits the form (-> /?s= results page).
 * - Focus is trapped while open and returned to the trigger on close.
 * - No-JS fallback: the form is a plain GET to the site root; this file
 *   only ever enhances.
 *
 * Config via wp_localize_script -> window.promptlessSearch:
 *   { restUrl, searchUrl, perPage, minChars, strings: { one, many, none } }
 */
(function () {
	'use strict';

	var cfg = window.promptlessSearch || {};
	var MIN = parseInt(cfg.minChars, 10) || 2;
	var PER_PAGE = parseInt(cfg.perPage, 10) || 6;
	var strings = cfg.strings || {};

	var overlay, panel, input, resultsEl, statusEl, emptyEl, footerEl, viewAllEl;
	var lastFocus = null;
	var debounceTimer = null;
	var controller = null;
	var activeIndex = -1;
	var options = [];

	function qs(sel, ctx) {
		return (ctx || document).querySelector(sel);
	}

	function init() {
		overlay = document.getElementById('promptless-search-overlay');
		if (!overlay) {
			return;
		}
		panel = qs('.promptless-search-overlay__panel', overlay);
		input = qs('.promptless-search-overlay__input', overlay);
		resultsEl = qs('.promptless-search-overlay__results', overlay);
		statusEl = qs('.promptless-search-overlay__status', overlay);
		emptyEl = qs('.promptless-search-overlay__empty', overlay);
		footerEl = qs('.promptless-search-overlay__footer', overlay);
		viewAllEl = qs('.promptless-search-overlay__view-all', overlay);

		document.querySelectorAll('.promptless-header__search-toggle').forEach(function (btn) {
			btn.addEventListener('click', function () {
				open(btn);
			});
		});

		overlay.addEventListener('click', function (e) {
			if (e.target.closest('[data-search-close]')) {
				close();
			}
		});

		document.addEventListener('keydown', onDocumentKeydown);
		overlay.addEventListener('keydown', trapFocus);
		input.addEventListener('input', onInput);
		input.addEventListener('keydown', onInputKeydown);
	}

	function isOpen() {
		return overlay && !overlay.hidden;
	}

	function open(trigger) {
		if (isOpen()) {
			return;
		}
		lastFocus = trigger || document.activeElement;
		overlay.hidden = false;
		document.documentElement.classList.add('promptless-search-open');
		document.querySelectorAll('.promptless-header__search-toggle').forEach(function (btn) {
			btn.setAttribute('aria-expanded', 'true');
		});
		// Force a reflow between un-hiding and adding the class so the
		// opacity transition runs from the hidden state. Deliberately NOT
		// requestAnimationFrame: rAF never fires in background tabs, which
		// would leave the overlay transparent and unfocused if opened
		// programmatically there - reflow is synchronous and always works.
		void overlay.offsetHeight;
		overlay.classList.add('is-open');
		input.focus();
	}

	function close() {
		if (!isOpen()) {
			return;
		}
		overlay.classList.remove('is-open');
		overlay.hidden = true;
		document.documentElement.classList.remove('promptless-search-open');
		document.querySelectorAll('.promptless-header__search-toggle').forEach(function (btn) {
			btn.setAttribute('aria-expanded', 'false');
		});
		clearTimeout(debounceTimer);
		if (controller) {
			controller.abort();
			controller = null;
		}
		input.value = '';
		clearResults();
		if (lastFocus && typeof lastFocus.focus === 'function') {
			lastFocus.focus();
		}
		lastFocus = null;
	}

	function onDocumentKeydown(e) {
		if (!isOpen()) {
			var t = e.target;
			var tag = (t.tagName || '').toLowerCase();
			var typing = tag === 'input' || tag === 'textarea' || tag === 'select' || t.isContentEditable;
			var slash = e.key === '/' && !typing && !e.metaKey && !e.ctrlKey && !e.altKey;
			var palette = (e.metaKey || e.ctrlKey) && !e.shiftKey && !e.altKey && String(e.key).toLowerCase() === 'k';
			if (slash || palette) {
				var trigger = qs('.promptless-header__search-toggle');
				if (trigger) {
					e.preventDefault();
					open(trigger);
				}
			}
			return;
		}
		if (e.key === 'Escape') {
			e.preventDefault();
			close();
		}
	}

	function trapFocus(e) {
		if (e.key !== 'Tab' || !isOpen()) {
			return;
		}
		var focusables = panel.querySelectorAll('a[href], button:not([disabled]), input');
		if (!focusables.length) {
			return;
		}
		var first = focusables[0];
		var last = focusables[focusables.length - 1];
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		}
	}

	function onInput() {
		var q = input.value.trim();
		clearTimeout(debounceTimer);
		if (controller) {
			controller.abort();
			controller = null;
		}
		if (q.length < MIN) {
			clearResults();
			return;
		}
		debounceTimer = setTimeout(function () {
			fetchResults(q);
		}, 250);
	}

	function fetchResults(q) {
		controller = new AbortController();
		overlay.classList.add('is-loading');
		var url = cfg.restUrl + (cfg.restUrl.indexOf('?') === -1 ? '?' : '&') +
			'search=' + encodeURIComponent(q) + '&per_page=' + PER_PAGE;
		fetch(url, { signal: controller.signal })
			.then(function (r) {
				return r.ok ? r.json() : [];
			})
			.then(function (data) {
				overlay.classList.remove('is-loading');
				render(q, Array.isArray(data) ? data : []);
			})
			.catch(function (err) {
				if (err && err.name === 'AbortError') {
					return; // A newer keystroke owns the overlay state.
				}
				overlay.classList.remove('is-loading');
				render(q, []);
			});
	}

	function clearResults() {
		resultsEl.innerHTML = '';
		resultsEl.hidden = true;
		emptyEl.hidden = true;
		footerEl.hidden = true;
		overlay.classList.remove('is-loading');
		statusEl.textContent = '';
		input.setAttribute('aria-expanded', 'false');
		input.removeAttribute('aria-activedescendant');
		activeIndex = -1;
		options = [];
	}

	function render(q, data) {
		resultsEl.innerHTML = '';
		activeIndex = -1;
		options = [];
		input.removeAttribute('aria-activedescendant');

		var viewAllUrl = cfg.searchUrl + encodeURIComponent(q);

		if (!data.length) {
			resultsEl.hidden = true;
			emptyEl.hidden = false;
			footerEl.hidden = true;
			statusEl.textContent = strings.none || 'No results found.';
			input.setAttribute('aria-expanded', 'true');
			return;
		}

		data.forEach(function (item, i) {
			var li = document.createElement('li');
			li.id = 'promptless-search-option-' + i;
			li.setAttribute('role', 'option');
			li.setAttribute('aria-selected', 'false');
			li.className = 'promptless-search-overlay__result';

			var a = document.createElement('a');
			a.href = item.url;
			a.tabIndex = -1;

			var title = document.createElement('span');
			title.className = 'promptless-search-overlay__result-title';
			// REST titles are pre-rendered text for the search endpoint;
			// assign via textContent so nothing is ever interpreted as HTML.
			title.textContent = item.title || '';

			var type = document.createElement('span');
			type.className = 'promptless-search-overlay__result-type';
			type.textContent = item.subtype || item.type || '';

			a.appendChild(title);
			a.appendChild(type);
			li.appendChild(a);

			li.addEventListener('mousemove', function () {
				setActive(i);
			});
			li.addEventListener('click', function () {
				window.location.href = item.url;
			});

			resultsEl.appendChild(li);
			options.push(li);
		});

		resultsEl.hidden = false;
		emptyEl.hidden = true;
		viewAllEl.href = viewAllUrl;
		footerEl.hidden = false;
		input.setAttribute('aria-expanded', 'true');

		statusEl.textContent = data.length === 1
			? (strings.one || '1 result')
			: (strings.many || '%d results').replace('%d', data.length);
	}

	function setActive(i) {
		if (activeIndex > -1 && options[activeIndex]) {
			options[activeIndex].classList.remove('is-active');
			options[activeIndex].setAttribute('aria-selected', 'false');
		}
		activeIndex = i;
		if (i > -1 && options[i]) {
			options[i].classList.add('is-active');
			options[i].setAttribute('aria-selected', 'true');
			input.setAttribute('aria-activedescendant', options[i].id);
			options[i].scrollIntoView({ block: 'nearest' });
		} else {
			input.removeAttribute('aria-activedescendant');
		}
	}

	function onInputKeydown(e) {
		if (!options.length) {
			return;
		}
		if (e.key === 'ArrowDown') {
			e.preventDefault();
			setActive(activeIndex + 1 >= options.length ? 0 : activeIndex + 1);
		} else if (e.key === 'ArrowUp') {
			e.preventDefault();
			setActive(activeIndex - 1 < 0 ? options.length - 1 : activeIndex - 1);
		} else if (e.key === 'Enter' && activeIndex > -1) {
			e.preventDefault();
			var link = options[activeIndex].querySelector('a');
			if (link) {
				window.location.href = link.href;
			}
		}
		// Plain Enter with no active option falls through to the form's
		// native GET submit -> the /?s= results page.
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
