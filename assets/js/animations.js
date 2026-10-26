(function () {
    "use strict";

    var settings = window.fbaSettings || {
        overrideFlatsome : true,
        waypointOffset   : 88,
        staggerStep      : 80,
        revealSystem     : true,
        revealThreshold  : 0.12,
    };

    var prefersReduced = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;

    function overrideFlatsomeAnimate() {
        if ( ! settings.overrideFlatsome ) return;
        if ( typeof jQuery === "undefined" ) return;
        if ( typeof Waypoint === "undefined" ) return;

        var offset = settings.waypointOffset + "%";

        jQuery("[data-animate]").each(function () {
            var el  = this;
            var $el = jQuery(el);

            if ( $el.attr("data-animated") === "true" ) return;

            var animVal = $el.data("animate");
            if ( animVal === "" || animVal === "none" ) {
                $el.attr("data-animated", "true");
                return;
            }

            var windowCtx = Waypoint.Context.findByElement(window);
            if ( windowCtx ) {
                var toDestroy = [];
                for ( var key in windowCtx.waypoints.vertical ) {
                    var wp = windowCtx.waypoints.vertical[key];
                    if ( wp && wp.element === el ) {
                        toDestroy.push(wp);
                    }
                }
                toDestroy.forEach(function (wp) { wp.destroy(); });
            }

            if ( prefersReduced ) {
                $el.attr("data-animated", "true");
                return;
            }

            $el.waypoint(
                function (direction) {
                    if ( direction !== "down" ) return;
                    var target = jQuery(this.element);
                    if ( target.attr("data-animated") === "true" ) return;
                    setTimeout(function () {
                        target.attr("data-animated", "true");
                    }, 50);
                },
                { offset: offset }
            );
        });
    }

    var revealObserver = null;

    function initReveal() {
        if ( ! settings.revealSystem ) return;
        if ( prefersReduced ) {
            document.querySelectorAll(".reveal").forEach(function (el) {
                el.classList.add("is-visible");
            });
            return;
        }

        document.querySelectorAll(".reveal-stagger").forEach(function (parent) {
            var children = parent.querySelectorAll(".reveal");
            children.forEach(function (child, i) {
                if ( ! child.style.getPropertyValue("--reveal-delay") ) {
                    child.style.setProperty(
                        "--reveal-delay",
                        i * settings.staggerStep + "ms"
                    );
                }
            });
        });

        document.querySelectorAll(".reveal[data-reveal-delay]").forEach(function (el) {
            var d = el.getAttribute("data-reveal-delay");
            if ( d ) el.style.setProperty("--reveal-delay", d + "ms");
        });

        if ( revealObserver ) revealObserver.disconnect();

        revealObserver = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    var el = entry.target;
                    if ( entry.isIntersecting ) {
                        el.classList.add("is-visible");
                        revealObserver.unobserve(el);
                    }
                });
            },
            {
                threshold  : settings.revealThreshold,
                rootMargin : "0px 0px -6% 0px",
            }
        );

        document.querySelectorAll(".reveal").forEach(function (el) {
            revealObserver.observe(el);
        });
    }

    function init() {
        initReveal();

        if (
            typeof jQuery !== "undefined" &&
            typeof Waypoint !== "undefined" &&
            settings.overrideFlatsome
        ) {
            jQuery(function () {
                setTimeout(overrideFlatsomeAnimate, 150);
            });
        }
    }

    if ( document.readyState === "loading" ) {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }

    document.addEventListener("flatsome-infiniteScroll-append", function () {
        initReveal();
        if ( typeof jQuery !== "undefined" && typeof Waypoint !== "undefined" ) {
            setTimeout(overrideFlatsomeAnimate, 150);
        }
    });

    window.flatsomeBetterAnimations = {
        initReveal          : initReveal,
        overrideFlatsome    : overrideFlatsomeAnimate,
    };

})();
