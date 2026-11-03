(function () {
	if (typeof angular === "undefined") {
		return;
	}

	var module;
	try {
		module = angular.module("uxBuilder");
	} catch (error) {
		return;
	}

	module.run([
		"$templateCache",
		function ($templateCache) {
			$templateCache.put(
				"components/ux-option/types/fba-anim.html",
				'<ux-option-fba-anim option="$ctrl.option" model="$ctrl.model"></ux-option-fba-anim>'
			);
		},
	]);

	module.component("uxOptionFbaAnim", {
		bindings: {
			option: "<",
			model: "=",
		},
		template:
			'<div class="fba-ux-option">' +
				'<button type="button" class="fba-ux-none" ng-click="$ctrl.select(\'\')" ng-class="{ \'is-on\': !$ctrl.model }" aria-pressed="{{ !$ctrl.model }}">' +
					'<span class="fba-ux-none__label">Ingen</span>' +
				"</button>" +
				'<div class="fba-ux-grid">' +
					'<button type="button" class="fba-ux-card" ng-repeat="item in $ctrl.items" ng-click="$ctrl.select(item.id)" ng-mouseenter="$ctrl.preview($event, item)" ng-class="{ \'is-on\': $ctrl.model === item.id }" aria-pressed="{{ $ctrl.model === item.id }}">' +
						'<span class="fba-ux-stage"><span class="fba-ux-sw"></span></span>' +
						"<b>{{ item.name }}</b>" +
					"</button>" +
				"</div>" +
			"</div>",
		controller: function () {
			var ctrl = this;
			var library = (window.fbaMotion && window.fbaMotion.animations) || {};

			ctrl.items = Object.keys(library).map(function (id) {
				return {
					id: id,
					name: library[id].n,
					transform: library[id].t,
					filter: library[id].f,
					clip: library[id].c,
				};
			});

			ctrl.select = function (id) {
				ctrl.model = id || "";
			};

			ctrl.preview = function (event, item) {
				var sw = event.currentTarget.querySelector(".fba-ux-sw");
				if (!sw) {
					return;
				}
				sw.style.transition = "none";
				sw.style.opacity = "0";
				sw.style.transform = item.transform || "none";
				sw.style.filter = item.filter && item.filter !== "none" ? item.filter : "none";
				sw.style.clipPath = item.clip && item.clip !== "none" ? item.clip : "none";
				void sw.offsetWidth;
				sw.style.transition =
					"transform 600ms cubic-bezier(0.22, 1, 0.36, 1), opacity 400ms ease, filter 600ms cubic-bezier(0.22, 1, 0.36, 1), clip-path 600ms cubic-bezier(0.22, 1, 0.36, 1)";
				sw.style.opacity = "1";
				sw.style.transform = "none";
				sw.style.filter = "none";
				sw.style.clipPath = item.clip && item.clip !== "none" ? "inset(0 0 0 0)" : "none";
			};
		},
	});
})();
