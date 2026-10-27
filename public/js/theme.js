(function() {
    "use strict";

    function getSavedTheme() {
        return localStorage.getItem("theme") || "system";
    }

    function resolveTheme(preference) {
        if (preference === "system") {
            return window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        return preference === "dark" ? "dark" : "light";
    }

    function applyTheme(preference) {
        var resolved = resolveTheme(preference);
        document.documentElement.setAttribute("data-theme", resolved);
        document.documentElement.style.colorScheme = resolved;
        if (resolved === "dark") {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        document.querySelectorAll("[data-theme-toggle]").forEach(function(btn) {
            btn.setAttribute("aria-pressed", resolved === "dark" ? "true" : "false");
            btn.setAttribute("data-current-theme", resolved);
        });

        window.dispatchEvent(new CustomEvent("theme-changed", {
            detail: { preference: preference, resolved: resolved }
        }));
    }

    window.setTheme = function(preference) {
        if (["light", "dark", "system"].indexOf(preference) === -1) {
            preference = "system";
        }
        localStorage.setItem("theme", preference);
        document.cookie = "theme=" + encodeURIComponent(preference) + ";path=/;max-age=31536000;SameSite=Lax";
        applyTheme(preference);
    };

    window.toggleTheme = function() {
        var current = resolveTheme(getSavedTheme());
        var next = current === "dark" ? "light" : "dark";
        window.setTheme(next);
    };

    window.getThemePreference = getSavedTheme;

    if (window.matchMedia) {
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", function(e) {
            if (getSavedTheme() === "system") {
                applyTheme("system");
            }
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", function() {
            applyTheme(getSavedTheme());
        });
    } else {
        applyTheme(getSavedTheme());
    }
})();
