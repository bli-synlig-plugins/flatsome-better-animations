(function () {
	"use strict";

	var boot = window.fbaAdmin;
	if (!boot || !boot.settings || !boot.library) {
		return;
	}

	var A = boot.library.animations;
	var EASE = {};
	var EASE_LABELS = {};
	Object.keys(boot.library.easings).forEach(function (key) {
		EASE[key] = boot.library.easings[key].value;
		EASE_LABELS[key] = boot.library.easings[key].label;
	});

	var TX = {};
	Object.keys(boot.library.textStyles).forEach(function (key) {
		TX[key] = boot.library.textStyles[key].label;
	});

	var UNITS = boot.library.textUnits;
	var TEXT_FROM = boot.library.textStyles;

	function clone(value) {
		return JSON.parse(JSON.stringify(value));
	}

	var saved = clone(boot.settings);
	var S = clone(boot.settings);

	function $(id) {
		return document.getElementById(id);
	}

	var tabs = [
		["anim", "Animasjoner", String(Object.keys(A).length)],
		["text", "Tekst", ""],
		["time", "Timing", ""],
		["flat", "Flatsome", ""],
		["a11y", "Tilgjengelighet", ""],
	];

	$("fba-nav").innerHTML = tabs
		.map(function (tab, index) {
			return (
				'<button type="button" role="tab" aria-selected="' +
				(index === 0 ? "true" : "false") +
				'" data-t="' +
				tab[0] +
				'">' +
				tab[1] +
				(tab[2] ? "<small>" + tab[2] + "</small>" : "") +
				"</button>"
			);
		})
		.join("");

	$("fba-nav").addEventListener("click", function (event) {
		var button = event.target.closest("button");
		if (!button) {
			return;
		}
		document.querySelectorAll("#fba-nav button").forEach(function (item) {
			item.setAttribute("aria-selected", item === button ? "true" : "false");
		});
		document.querySelectorAll(".fba-tab").forEach(function (tab) {
			var on = tab.id === "t-" + button.dataset.t;
			tab.classList.toggle("is-on", on);
			tab.hidden = !on;
		});
	});

	function slider(key, label, help, min, max, step, unit) {
		return (
			'<div class="fba-row">' +
			"<div><label class=\"fba-t\" for=\"s-" +
			key +
			'">' +
			label +
			'</label><span class="fba-h">' +
			help +
			"</span></div>" +
			'<div class="fba-sl"><input type="range" id="s-' +
			key +
			'" data-k="' +
			key +
			'" min="' +
			min +
			'" max="' +
			max +
			'" step="' +
			step +
			'"><output id="o-' +
			key +
			'"></output></div></div>'
		);
	}

	function toggle(key, label, help) {
		return (
			'<div class="fba-row is-toggle"><div><label class="fba-t" for="c-' +
			key +
			'">' +
			label +
			'</label><span class="fba-h">' +
			help +
			'</span></div><label class="fba-switch"><input type="checkbox" id="c-' +
			key +
			'" data-c="' +
			key +
			'"><i></i></label></div>'
		);
	}

	function seg(key, label, help, options) {
		return (
			'<div class="fba-row"><div><span class="fba-t">' +
			label +
			'</span><span class="fba-h">' +
			help +
			'</span></div><div class="fba-seg" data-g="' +
			key +
			'">' +
			Object.keys(options)
				.map(function (value) {
					return (
						'<button type="button" data-v="' +
						value +
						'" aria-pressed="false">' +
						options[value] +
						"</button>"
					);
				})
				.join("") +
			"</div></div>"
		);
	}

	var UNITS_FMT = {
		ms: function (v) {
			return v + " ms";
		},
		px: function (v) {
			return v + " px";
		},
		pc: function (v) {
			return v + " %";
		},
	};

	var SLIDER_META = {
		reveal_duration: "ms",
		reveal_opacity_duration: "ms",
		move_distance: "px",
		stagger_step: "ms",
		reveal_threshold: "pc",
		text_stagger: "ms",
		text_duration: "ms",
		transform_duration: "ms",
		opacity_duration: "ms",
		flatsome_distance: "px",
		waypoint_offset: "pc",
	};

	$("fba-lib").innerHTML = Object.keys(A)
		.map(function (key) {
			return (
				'<button type="button" class="fba-anim" data-k="' +
				key +
				'" aria-pressed="false"><div class="fba-stage"><div class="fba-sw"></div></div><b>' +
				A[key].n +
				'</b><span class="fba-d">' +
				A[key].d +
				"</span></button>"
			);
		})
		.join("");

	$("fba-trig").innerHTML =
		seg("ease", "Bevegelseskurve", "Hvordan farten endrer seg", EASE_LABELS) +
		toggle("play_once", "Spill kun én gang", "Av = animasjonen spilles hver gang elementet kommer til syne") +
		toggle("enable_mobile", "Aktiver på mobil", "Slå av for å vise alt umiddelbart på små skjermer");

	$("fba-textc").innerHTML =
		seg("text_style", "Stil", "Hvordan hver bit kommer inn", TX) +
		seg("text_unit", "Del opp i", "Hvor små bitene er", UNITS) +
		slider("text_stagger", "Stagger per bit", "Tid mellom hver bit", 10, 200, 5) +
		slider("text_duration", "Varighet per bit", "Hvor lenge hver bit animerer", 300, 1400, 50);

	$("fba-timec").innerHTML =
		slider("reveal_duration", "Bevegelse", "Hvor lenge elementet flytter seg", 200, 1200, 10) +
		slider("reveal_opacity_duration", "Opasitet", "Fade-tid. Kortere enn bevegelsen føles skarpere", 100, 900, 10) +
		slider("move_distance", "Avstand", "Hvor langt elementet reiser", 0, 80, 2) +
		slider("stagger_step", "Stagger-steg", "Forsinkelse mellom barn i <code>.reveal-stagger</code>", 0, 300, 5) +
		slider("reveal_threshold", "Synlighetsterskel", "Hvor mye som må være synlig før start", 0, 60, 1);

	$("fba-flatc").innerHTML =
		slider("transform_duration", "Transform-varighet", "Flatsome standard: 1600 ms", 200, 1600, 10) +
		slider("opacity_duration", "Opacity-varighet", "Flatsome standard: 700 ms", 100, 900, 10) +
		slider("flatsome_distance", "Forflytningsdistanse", "Flatsome standard: 70 px", 0, 80, 2) +
		slider("waypoint_offset", "Waypoint-offset", "Flatsome standard: 101 % (utenfor skjermen)", 60, 101, 1);

	$("fba-a11y").innerHTML =
		toggle("reduce_motion", "Respekter «reduser bevegelse»", "Viser alt statisk for besøkende som har bedt om det i systemet") +
		toggle("defer_js", "Last skript forsinket", "Laster animasjons-JS etter første visning. Uten JS vises alt umiddelbart");

	function applyFrom(el, anim) {
		el.style.transition = "none";
		el.style.opacity = "0";
		el.style.transform = anim.t || "none";
		el.style.filter = anim.f && anim.f !== "none" ? anim.f : "none";
		el.style.clipPath = anim.c && anim.c !== "none" ? anim.c : "none";
	}

	function applyTo(el, anim, state, delay) {
		var ease = EASE[state.ease] || EASE.myk;
		delay = delay || 0;
		el.style.transition =
			"transform " +
			state.reveal_duration +
			"ms " +
			ease +
			" " +
			delay +
			"ms,opacity " +
			state.reveal_opacity_duration +
			"ms ease " +
			delay +
			"ms,filter " +
			state.reveal_duration +
			"ms " +
			ease +
			" " +
			delay +
			"ms,clip-path " +
			state.reveal_duration +
			"ms " +
			ease +
			" " +
			delay +
			"ms";
		el.style.opacity = "1";
		el.style.transform = "none";
		el.style.filter = "none";
		el.style.clipPath = anim.c && anim.c !== "none" ? "inset(0 0 0 0)" : "none";
	}

	function play(el, key, state, delay) {
		var anim = A[key];
		if (!anim || !el) {
			return;
		}
		el.style.setProperty("--fba-move", state.move_distance + "px");
		el.style.setProperty("--d", state.move_distance + "px");
		applyFrom(el, anim);
		void el.offsetWidth;
		applyTo(el, anim, state, delay || 0);
	}

	var SAMPLE = "Animasjon som føles som ingenting";

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;");
	}

	function buildText() {
		var heading = $("fba-pt");
		var unit = S.text_unit;
		var parts;
		if (unit === "tegn") {
			parts = Array.from(SAMPLE).map(function (char) {
				return char === " " ? " " : char;
			});
		} else if (unit === "ord") {
			parts = SAMPLE.split(" ");
		} else {
			parts = ["Animasjon som", "føles som", "ingenting"];
		}

		heading.innerHTML = parts
			.map(function (part) {
				if (part === " ") {
					return " ";
				}
				var space = unit === "ord" ? " " : "";
				return (
					'<span class="u"><i>' +
					escapeHtml(part) +
					"</i></span>" +
					(unit === "linje" ? "<br>" : space)
				);
			})
			.join("");
		heading.setAttribute("aria-label", SAMPLE);
	}

	function playText() {
		buildText();
		var items = Array.prototype.slice.call($("fba-pt").querySelectorAll("i"));
		var from = TEXT_FROM[S.text_style] || TEXT_FROM.rise;
		items.forEach(function (item) {
			item.style.transition = "none";
			item.style.transform = from.transform;
			item.style.opacity = from.opacity;
			item.style.filter = from.filter;
		});
		void $("fba-pt").offsetWidth;
		items.forEach(function (item, index) {
			var delay = index * S.text_stagger;
			item.style.transition =
				"transform " +
				S.text_duration +
				"ms " +
				EASE.myk +
				" " +
				delay +
				"ms,opacity " +
				S.text_duration * 0.6 +
				"ms ease " +
				delay +
				"ms,filter " +
				S.text_duration +
				"ms ease " +
				delay +
				"ms";
			item.style.transform = "none";
			item.style.opacity = "1";
			item.style.filter = "none";
		});
	}

	function replay() {
		playText();
		play($("fba-pb"), S.default_anim, S, 300);
		play($("fba-pp"), S.default_anim, S, 150);
		var anim = A[S.default_anim];
		$("fba-m1").textContent = (anim ? anim.n : S.default_anim) + " · " + S.reveal_duration + " ms";
		$("fba-m2").textContent =
			"Tekst: " + (TX[S.text_style] || S.text_style).toLowerCase() + " per " + (UNITS[S.text_unit] || S.text_unit).toLowerCase();
	}

	function isDirty() {
		return JSON.stringify(S) !== JSON.stringify(saved);
	}

	function sync() {
		document.querySelectorAll("[data-k]").forEach(function (input) {
			if (input.type !== "range") {
				return;
			}
			input.value = S[input.dataset.k];
			var output = $("o-" + input.dataset.k);
			var unit = SLIDER_META[input.dataset.k] || "ms";
			if (output) {
				output.textContent = UNITS_FMT[unit](S[input.dataset.k]);
			}
		});

		document.querySelectorAll("[data-c]").forEach(function (input) {
			input.checked = !!S[input.dataset.c];
		});

		document.querySelectorAll("[data-g]").forEach(function (group) {
			group.querySelectorAll("button").forEach(function (button) {
				button.setAttribute("aria-pressed", button.dataset.v === String(S[group.dataset.g]) ? "true" : "false");
			});
		});

		document.querySelectorAll(".fba-anim").forEach(function (button) {
			button.setAttribute("aria-pressed", button.dataset.k === S.default_anim ? "true" : "false");
		});

		$("fba-cmp").innerHTML =
			'<div><span>Flatsome standard</span><span class="fba-bar is-old"><i style="width:100%"></i></span><span>1600 ms</span></div>' +
			'<div><span>Din innstilling</span><span class="fba-bar"><i style="width:' +
			((S.override_flatsome ? S.transform_duration : 1600) / 1600) * 100 +
			'%"></i></span><span>' +
			(S.override_flatsome ? S.transform_duration : 1600) +
			" ms</span></div>";

		$("fba-save").classList.toggle("is-show", isDirty());
	}

	document.addEventListener("input", function (event) {
		var target = event.target;
		if (target.dataset.k && target.type === "range") {
			S[target.dataset.k] = Number(target.value);
			sync();
			window.clearTimeout(window._fbaPreview);
			window._fbaPreview = window.setTimeout(replay, 120);
		} else if (target.dataset.c) {
			S[target.dataset.c] = target.checked;
			sync();
		}
	});

	document.addEventListener("click", function (event) {
		var segButton = event.target.closest(".fba-seg button");
		if (segButton) {
			S[segButton.parentElement.dataset.g] = segButton.dataset.v;
			sync();
			replay();
			return;
		}
		var anim = event.target.closest(".fba-anim");
		if (anim) {
			S.default_anim = anim.dataset.k;
			sync();
			replay();
		}
	});

	document.querySelectorAll(".fba-anim").forEach(function (button) {
		button.addEventListener("mouseenter", function () {
			play(button.querySelector(".fba-sw"), button.dataset.k, {
				reveal_duration: 600,
				reveal_opacity_duration: 400,
				move_distance: S.move_distance,
				ease: S.ease,
			});
		});
	});

	$("fba-replay").addEventListener("click", replay);
	$("fba-rst").addEventListener("click", function () {
		S = clone(saved);
		sync();
		replay();
	});

	$("fba-sv").addEventListener("click", function () {
		var button = $("fba-sv");
		button.disabled = true;
		var body = new window.FormData();
		body.append("action", "fba_save_settings");
		body.append("nonce", boot.nonce);
		body.append("settings", JSON.stringify(S));

		window
			.fetch(boot.ajaxUrl, {
				method: "POST",
				credentials: "same-origin",
				body: body,
			})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload || !payload.success) {
					throw new Error("save failed");
				}
				saved = clone(payload.data.settings || S);
				S = clone(saved);
				sync();
				var toast = $("fba-toast");
				toast.classList.add("is-show");
				window.setTimeout(function () {
					toast.classList.remove("is-show");
				}, 2200);
			})
			.catch(function () {
				window.alert("Kunne ikke lagre innstillingene. Prøv igjen.");
			})
			.then(function () {
				button.disabled = false;
			});
	});

	buildText();
	sync();
	window.requestAnimationFrame(function () {
		window.setTimeout(replay, 250);
	});
})();
