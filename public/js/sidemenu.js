(function (exports) {
	'use strict';

	/**
	 * Run the given callback function once after the DOM is ready.
	 *
	 * @param {() => void} fn
	 */
	function onDomReady(fn) {
		if (document.readyState !== "loading") return fn();
		document.addEventListener("DOMContentLoaded", fn);
	}

	/**
	 * Provide the given element with a unique generated `id`, if it does not have one already.
	 *
	 * @param {HTMLElement} element
	 */
	function ensureElementHasId(element) {
		if (!element.id) {
			element.id =
				element.tagName + "-" + (~~(Math.random() * 1e9) + 1e9).toString(16);
		}
	}

	/**
	 * @param {HTMLElement} parent
	 * @param {HTMLElement} child
	 */
	function prependNode(parent, child) {
		var children = parent.childNodes;
		if (children.length) parent.insertBefore(child, children[0]);
		else parent.appendChild(child);
	}

	/**
	 * Ponyfill for Element.prototype.closest.
	 * @param {Element} element
	 * @param {DOMString} selectors
	 * @return {Element | null}
	 */
	function closest(element, selectors) {
		if (Element.prototype.closest) {
			return element.closest(selectors);
		}
		var matches =
			Element.prototype.matches ||
			Element.prototype.msMatchesSelector ||
			Element.prototype.webkitMatchesSelector;

		do {
			if (matches.call(element, selectors)) {
				return element;
			}
			element = element.parentElement || element.parentNode;
		} while (element !== null && element.nodeType === 1);

		return null;
	}

	// @ts-check

	onDomReady(initSidemenus);

	/**
	 * Add a close/open toggle behaviour to the sidemenus. Safe to call again to
	 * apply to newly added sidemenus.
	 */
	function initSidemenus() {
		var sidemenus = document.querySelectorAll(".sidemenu > nav");
		for (var i = 0; i < sidemenus.length; i++) {
			var sidemenu = sidemenus[i];
			if (!(sidemenu instanceof HTMLElement)) {
				continue;
			}
			if (!sidemenu.querySelector("button.sidemenu-toggle")) {
				addToggleButton(sidemenu);
			}
		}
	}

	/**
	 * @param {HTMLElement} sidemenu
	 */
	function addToggleButton(sidemenu) {
		var main = closest(sidemenu, ".sidemenu");
		var ul = sidemenu.querySelector("ul");

		if (!main || !ul) {
			return;
		}

		ensureElementHasId(ul);

		var openLabel = sidemenu.dataset.openLabel || "Zijbalknavigatie";
		var closeLabel = sidemenu.dataset.closeLabel || "Sluit zijbalknavigatie";
		var openIcon = "ro-icon-hamburger";
		var closeIcon = "ro-icon-close";

		var button = document.createElement("button");
		button.className = "ro-icon secondary sidemenu-toggle";
		button.setAttribute("aria-controls", ul.id);

		function isClosed() {
			return main.classList.contains("sidemenu-closed");
		}

		function setClosed(closed) {
			button.innerText = closed ? closeLabel : openLabel;
			button.classList.add(closed ? openIcon : closeIcon);
			button.classList.remove(closed ? closeIcon : openIcon);
			button.setAttribute("aria-expanded", String(!closed));
			if (closed) {
				main.classList.add("sidemenu-closed");
			} else {
				main.classList.remove("sidemenu-closed");
			}
		}

		setClosed(isClosed());

		button.addEventListener("click", function () {
			setClosed(!isClosed());
		});

		prependNode(sidemenu, button);
	}

	exports.initSidemenus = initSidemenus;

	Object.defineProperty(exports, '__esModule', { value: true });

	return exports;

}({}));
