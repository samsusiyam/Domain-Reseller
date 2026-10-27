(function() {
    'use strict';

    function getSavedTheme() {
        return localStorage.getItem('theme') || 'system';
    }

    function resolveTheme(preference) {
        if (preference === 'system') {
            return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return preference === 'dark' ? 'dark' : 'light';
    }

    function applyTheme(preference) {
        var resolved = resolveTheme(preference);
        document.documentElement.setAttribute('data-theme', resolved);
        document.documentElement.style.colorScheme = resolved;
        if (resolved === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Update active state in toggle UI
        document.querySelectorAll('[data-theme-btn]').forEach(function(btn) {
            var btnVal = btn.getAttribute('data-theme-btn');
            if (btnVal === preference) {
                btn.setAttribute('aria-pressed', 'true');
                btn.classList.add('active');
            } else {
                btn.setAttribute('aria-pressed', 'false');
                btn.classList.remove('active');
            }
        });

        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: { preference: preference, resolved: resolved }
        }));
    }

    window.setTheme = function(preference) {
        if (['light', 'dark', 'system'].indexOf(preference) === -1) {
            preference = 'system';
        }
        localStorage.setItem('theme', preference);
        document.cookie = 'theme=' + encodeURIComponent(preference) + ';path=/;max-age=31536000;SameSite=Lax';
        applyTheme(preference);
    };

    window.getThemePreference = getSavedTheme;

    // React to system OS color scheme changes
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            if (getSavedTheme() === 'system') {
                applyTheme('system');
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            applyTheme(getSavedTheme());
        });
    } else {
        applyTheme(getSavedTheme());
    }
})();
