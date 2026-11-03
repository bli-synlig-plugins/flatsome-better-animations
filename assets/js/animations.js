(function () {
	"use strict";

	function toBool(value, fallback) {
		if (value === undefined || value === null || value === "") {
			return fallback;
		}
		return value === true || value === 1 || value === "1" || value === "true";
	}

	function toNumber(value, fallback) {
		var number = parseFloat(value);
		return isNaN(number) ? fallback : number;
	}

	var raw = window.fbaSettings || {};
	var settings = {
		overrideFlatsome: toBool(raw.overrideFlatsome, true),
		waypointOffset: toNumber(raw.waypointOffset, 88),
		staggerStep: toNumber(raw.staggerStep, 80),
		revealSystem: toBool(raw.revealSystem, true),
		revealThreshold: toNumber(raw.revealThreshold, 0.12),
		playOnce: toBool(raw.playOnce, true),
		enableMobile: toBool(raw.enableMobile, true),
		reduceMotion: toBool(raw.reduceMotion, true),
		defaultAnim: raw.defaultAnim || "fade-up",
		textStyle: raw.textStyle || "rise",
		textUnit: raw.textUnit || "ord",
		textStagger: toNumber(raw.textStagger, 45),
		textDuration: toNumber(raw.textDuration, 800),
		mobileMax: toNumber(raw.mobileMax, 849),
	};

	var prefersReduced =
		!!settings.reduceMotion &&
		window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	var isMobileOff =
		!settings.enableMobile &&
		window.matchMedia("(max-width: " + (settings.mobileMax || 849) + "px)").matches;

	var revealObserver = null;

	function whenJquery(callback) {
		if (typeof window.jQuery !== "undefined") {
			callback(window.jQuery);
			return;
		}

		var tries = 0;
		var timer = window.setInterval(function () {
			tries += 1;
			if (typeof window.jQuery !== "undefined") {
				window.clearInterval(timer);
				callback(window.jQuery);
			} else if (tries > 40) {
				window.clearInterval(timer);
			}
		}, 50);
	}

	function overrideFlatsomeAnimate($) {
		if (!settings.overrideFlatsome) {
			return;
		}
		if (typeof window.Waypoint === "undefined") {
			return;
		}

		var offset = settings.waypointOffset + "%";

		$("[data-animate]").each(function () {
			var el = this;
			var $el = $(el);

			if ($el.attr("data-animated") === "true") {
				return;
			}

			var animVal = $el.data("animate");
			if (animVal === "" || animVal === "none") {
				$el.attr("data-animated", "true");
				return;
			}

			var windowCtx = window.Waypoint.Context.findByElement(window);
			if (windowCtx) {
				var toDestroy = [];
				for (var key in windowCtx.waypoints.vertical) {
					var wp = windowCtx.waypoints.vertical[key];
					if (wp && wp.element === el) {
						toDestroy.push(wp);
					}
				}
				toDestroy.forEach(function (item) {
					item.destroy();
				});
			}

			if (prefersReduced) {
				$el.attr("data-animated", "true");
				return;
			}

			$el.waypoint(
				function (direction) {
					if (direction !== "down") {
						return;
					}
					var target = $(this.element);
					if (target.attr("data-animated") === "true") {
						return;
					}
					window.setTimeout(function () {
						target.attr("data-animated", "true");
					}, 50);
				},
				{ offset: offset }
			);
		});
	}

	function skipNode(node) {
		if (!node || node.nodeType !== 1) {
			return false;
		}
		var tag = node.tagName;
		return tag === "SCRIPT" || tag === "STYLE" || tag === "NOSCRIPT" || tag === "TEXTAREA";
	}

	function wrapPiece(text) {
		var span = document.createElement("span");
		span.className = "fba-u";
		var inner = document.createElement("i");
		inner.textContent = text;
		span.appendChild(inner);
		return span;
	}

	function splitTextNode(node, unit) {
		var text = node.nodeValue;
		if (text === null) {
			return;
		}
		if (!text.trim()) {
			return;
		}

		var frag = document.createDocumentFragment();

		if (unit === "tegn") {
			var chars = Array.from(text);
			for (var i = 0; i < chars.length; i += 1) {
				if (chars[i] === " ") {
					frag.appendChild(document.createTextNode(" "));
				} else if (chars[i] === "\n") {
					frag.appendChild(document.createElement("br"));
				} else {
					frag.appendChild(wrapPiece(chars[i]));
				}
			}
		} else if (unit === "linje") {
			var lines = text.split(/\n/);
			for (var l = 0; l < lines.length; l += 1) {
				if (lines[l] !== "") {
					frag.appendChild(wrapPiece(lines[l]));
				}
				if (l < lines.length - 1) {
					frag.appendChild(document.createElement("br"));
				}
			}
		} else {
			var parts = text.split(/(\s+)/);
			for (var p = 0; p < parts.length; p += 1) {
				if (parts[p] === "") {
					continue;
				}
				if (/^\s+$/.test(parts[p])) {
					frag.appendChild(document.createTextNode(parts[p]));
				} else {
					frag.appendChild(wrapPiece(parts[p]));
				}
			}
		}

		node.parentNode.replaceChild(frag, node);
	}

	function splitElement(root, unit) {
		if (root.getAttribute("data-fba-split") === "1") {
			return;
		}

		var target = root;
		var selector = root.getAttribute("data-reveal-target");
		if (selector) {
			var found = root.querySelector(selector);
			if (found) {
				target = found;
			}
		}

		if (unit === "linje") {
			var blocks = target.children;
			if (blocks.length > 1) {
				Array.prototype.forEach.call(blocks, function (child) {
					if (skipNode(child)) {
						return;
					}
					if (child.matches(".fba-u")) {
						return;
					}
					child.classList.add("fba-u");
					if (!child.querySelector(":scope > i")) {
						var inner = document.createElement("i");
						while (child.firstChild) {
							inner.appendChild(child.firstChild);
						}
						child.appendChild(inner);
					}
				});
				root.setAttribute("data-fba-split", "1");
				applyTextDelays(root);
				return;
			}
		}

		var walker = document.createTreeWalker(target, NodeFilter.SHOW_TEXT, {
			acceptNode: function (node) {
				var parent = node.parentNode;
				if (!parent || parent.nodeType !== 1) {
					return NodeFilter.FILTER_REJECT;
				}
				if (parent.classList.contains("fba-u") || parent.tagName === "I") {
					return NodeFilter.FILTER_REJECT;
				}
				if (skipNode(parent)) {
					return NodeFilter.FILTER_REJECT;
				}
				return NodeFilter.FILTER_ACCEPT;
			},
		});

		var nodes = [];
		while (walker.nextNode()) {
			nodes.push(walker.currentNode);
		}
		nodes.forEach(function (node) {
			splitTextNode(node, unit);
		});

		root.setAttribute("data-fba-split", "1");
		applyTextDelays(root);
	}

	function applyTextDelays(root) {
		var units = root.querySelectorAll(".fba-u");
		var step = settings.textStagger || 45;
		units.forEach(function (el, index) {
			el.style.setProperty("--reveal-delay", index * step + "ms");
		});
	}

	function prepareText(el) {
		if (!el.classList.contains("reveal-text")) {
			return;
		}

		if (!el.classList.contains("reveal-text--rise") &&
			!el.classList.contains("reveal-text--fade") &&
			!el.classList.contains("reveal-text--blur") &&
			!el.classList.contains("reveal-text--rot")
		) {
			el.classList.add("reveal-text--" + (settings.textStyle || "rise"));
		}

		var unit = el.getAttribute("data-reveal-unit") || settings.textUnit || "ord";
		if (!el.classList.contains("reveal-text--tegn") &&
			!el.classList.contains("reveal-text--ord") &&
			!el.classList.contains("reveal-text--linje")
		) {
			el.classList.add("reveal-text--" + unit);
		}

		splitElement(el, unit);
	}

	function showImmediately(el) {
		el.classList.add("is-visible");
	}

	function isColumn(el) {
		if (!el || el.nodeType !== 1) {
			return false;
		}
		if (el.classList.contains("col") || el.classList.contains("col-inner")) {
			return true;
		}
		var id = el.id || "";
		return id.indexOf("col-") === 0 || id.indexOf("col_") === 0;
	}

	function nestedInOtherColumn(el, parent) {
		var node = el.parentElement;
		while (node && node !== parent) {
			if (isColumn(node)) {
				return true;
			}
			node = node.parentElement;
		}
		return false;
	}

	function childColumns(parent) {
		var cols = [];
		var nodes = parent.querySelectorAll(".col, .col-inner, [id^='col-'], [id^='col_']");
		for (var i = 0; i < nodes.length; i += 1) {
			var col = nodes[i];
			if (nestedInOtherColumn(col, parent)) {
				continue;
			}
			if (col.classList.contains("col-inner") && col.parentElement && isColumn(col.parentElement)) {
				continue;
			}
			cols.push(col);
		}
		if (cols.length) {
			return cols;
		}
		Array.prototype.forEach.call(parent.children, function (child) {
			if (isColumn(child)) {
				cols.push(child);
			}
		});
		return cols;
	}

	function applyChildAnims() {
		document.querySelectorAll("[data-fba-child-anim]").forEach(function (parent) {
			var anim = parent.getAttribute("data-fba-child-anim");
			if (!anim) {
				return;
			}
			var staggerOff = parent.getAttribute("data-fba-stagger") === "off";
			var baseDelay = parseInt(parent.getAttribute("data-reveal-delay") || "0", 10) || 0;
			var kids = childColumns(parent);
			kids.forEach(function (col) {
				if (!col.classList.contains("reveal")) {
					col.classList.add("reveal");
					col.classList.add("reveal--" + anim);
				}
				if (staggerOff && baseDelay && !col.style.getPropertyValue("--reveal-delay")) {
					col.style.setProperty("--reveal-delay", baseDelay + "ms");
				}
			});
		});
	}

	function isRevealEl(el) {
		return el && el.classList && (el.classList.contains("reveal") || el.classList.contains("reveal-text"));
	}

	function nestedInReveal(el, parent) {
		var node = el.parentElement;
		while (node && node !== parent) {
			if (isRevealEl(node)) {
				return true;
			}
			node = node.parentElement;
		}
		return false;
	}

	function staggerTargets(parent) {
		var nodes = parent.querySelectorAll(".reveal, .reveal-text");
		var targets = [];
		for (var i = 0; i < nodes.length; i += 1) {
			var el = nodes[i];
			if (el === parent || nestedInReveal(el, parent)) {
				continue;
			}
			targets.push(el);
		}
		return targets;
	}

	function applyStaggerDelays() {
		document.querySelectorAll(".reveal-stagger").forEach(function (parent) {
			var baseDelay = parseInt(parent.getAttribute("data-reveal-delay") || "0", 10) || 0;
			staggerTargets(parent).forEach(function (child, i) {
				if (!child.style.getPropertyValue("--reveal-delay")) {
					child.style.setProperty(
						"--reveal-delay",
						baseDelay + i * settings.staggerStep + "ms"
					);
				}
			});
		});

		document.querySelectorAll(".reveal[data-reveal-delay], .reveal-text[data-reveal-delay]").forEach(function (el) {
			var delay = el.getAttribute("data-reveal-delay");
			if (delay) {
				el.style.setProperty("--reveal-delay", delay + "ms");
			}
		});
	}

	function observeReveals() {
		if (revealObserver) {
			revealObserver.disconnect();
		}

		revealObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					var el = entry.target;
					if (entry.isIntersecting) {
						el.classList.add("is-visible");
						if (settings.playOnce) {
							revealObserver.unobserve(el);
						}
					} else if (!settings.playOnce) {
						el.classList.remove("is-visible");
					}
				});
			},
			{
				threshold: 0,
				rootMargin: "0px 0px -8% 0px",
			}
		);

		document.querySelectorAll(".reveal, .reveal-text").forEach(function (el) {
			if (el.classList.contains("is-visible") && settings.playOnce) {
				return;
			}
			revealObserver.observe(el);
		});
	}

	function initReveal() {
		if (!settings.revealSystem) {
			return;
		}

		applyChildAnims();
		document.querySelectorAll(".reveal-text").forEach(prepareText);
		applyStaggerDelays();

		if (prefersReduced || isMobileOff || typeof window.IntersectionObserver === "undefined") {
			document.querySelectorAll(".reveal, .reveal-text").forEach(showImmediately);
			return;
		}

		observeReveals();
	}

	var failsafeTimer = null;

	function startFailsafe() {
		if (failsafeTimer) {
			window.clearTimeout(failsafeTimer);
		}
		failsafeTimer = window.setTimeout(function () {
			document.querySelectorAll(".reveal:not(.is-visible), .reveal-text:not(.is-visible)").forEach(showImmediately);
		}, 2500);
	}

	function init() {
		initReveal();
		startFailsafe();
		window.requestAnimationFrame(function () {
			initReveal();
		});

		if (settings.overrideFlatsome) {
			whenJquery(function ($) {
				$(function () {
					window.setTimeout(function () {
						overrideFlatsomeAnimate($);
					}, 150);
				});
			});
		}
	}

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}

	window.addEventListener("load", function () {
		window.requestAnimationFrame(function () {
			initReveal();
		});
	});

	document.addEventListener("flatsome-infiniteScroll-append", function () {
		initReveal();
		startFailsafe();
		if (settings.overrideFlatsome) {
			whenJquery(function ($) {
				window.setTimeout(function () {
					overrideFlatsomeAnimate($);
				}, 150);
			});
		}
	});

	window.flatsomeBetterAnimations = {
		initReveal: initReveal,
		overrideFlatsome: function () {
			whenJquery(overrideFlatsomeAnimate);
		},
	};
})();
