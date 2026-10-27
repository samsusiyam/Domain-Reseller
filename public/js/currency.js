/**
 * Global Real-time Currency Switcher
 * Single Source of Truth: All prices stored in USD (data-usd="XX.XX")
 */
(function () {
    'use strict';

    const USD_FORMATTER = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    const BDT_FORMATTER = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^|;\\s*)(' + name + ')=([^;]*)'));
        return match ? decodeURIComponent(match[3]) : null;
    }

    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${encodeURIComponent(value)}; path=/; expires=${d.toUTCString()}; SameSite=Lax`;
    }

    // Initialize State
    let currentCurrency = localStorage.getItem('currency') || getCookie('currency') || window.CURRENCY_CONFIG?.current || 'USD';
    let exchangeRate = Number(window.CURRENCY_CONFIG?.rate) || 120.00;

    window.formatPrice = function (usdAmount, currency) {
        currency = currency || currentCurrency;
        const num = Number(usdAmount) || 0;
        if (currency === 'BDT') {
            const bdtVal = Math.round(num * exchangeRate);
            return '৳' + BDT_FORMATTER.format(bdtVal);
        }
        return USD_FORMATTER.format(num);
    };

    window.getCurrentCurrency = function () {
        return currentCurrency;
    };

    window.getExchangeRate = function () {
        return exchangeRate;
    };

    function updateDomPrices() {
        // 1. Update all .price[data-usd] elements
        document.querySelectorAll('.price[data-usd]').forEach(function (el) {
            const usd = el.getAttribute('data-usd');
            if (usd !== null && usd !== '') {
                const num = Number(usd) || 0;
                const formatted = window.formatPrice(num, currentCurrency);

                if (el.getAttribute('data-show-equivalent') === 'true') {
                    if (currentCurrency === 'BDT') {
                        el.innerHTML = `${formatted} <span class="text-[12px] text-[#6B7280] font-normal tabular-nums">(${USD_FORMATTER.format(num)})</span>`;
                    } else {
                        const bdtVal = '৳' + BDT_FORMATTER.format(Math.round(num * exchangeRate));
                        el.innerHTML = `${formatted} <span class="text-[12px] text-[#6B7280] font-normal tabular-nums">(${bdtVal})</span>`;
                    }
                } else {
                    el.textContent = formatted;
                }
            }
        });

        // 2. Update toggle button styling & aria-pressed across desktop & mobile
        document.querySelectorAll('[data-currency-toggle]').forEach(function (btn) {
            const val = btn.getAttribute('data-currency-val');
            const isActive = (val === currentCurrency);
            btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            if (isActive) {
                btn.classList.add('bg-white', 'text-[#111827]', 'font-semibold', 'border', 'border-[#E5E7EB]');
                btn.classList.remove('text-[#6B7280]');
            } else {
                btn.classList.remove('bg-white', 'text-[#111827]', 'font-semibold', 'border', 'border-[#E5E7EB]');
                btn.classList.add('text-[#6B7280]');
            }
        });
    }

    window.setCurrency = function (currency) {
        currency = String(currency).toUpperCase();
        if (currency !== 'USD' && currency !== 'BDT') {
            currency = 'USD';
        }

        currentCurrency = currency;
        localStorage.setItem('currency', currency);
        setCookie('currency', currency, 365);

        updateDomPrices();

        window.dispatchEvent(new CustomEvent('currency-changed', {
            detail: { currency: currentCurrency, rate: exchangeRate }
        }));

        // Sync with backend session in background
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/currency/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({ currency: currentCurrency })
            }).catch(function () {});
        } catch (e) {}
    };

    function refreshRate() {
        fetch('/api/rates')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.success && data.rate) {
                    exchangeRate = Number(data.rate);
                    if (window.CURRENCY_CONFIG) {
                        window.CURRENCY_CONFIG.rate = exchangeRate;
                    }
                    if (currentCurrency === 'BDT') {
                        updateDomPrices();
                    }
                }
            })
            .catch(function () {});
    }

    // Dynamic Observer for AJAX search results, cart items, etc.
    const observer = new MutationObserver(function (mutations) {
        let shouldUpdate = false;
        mutations.forEach(function (m) {
            if (m.addedNodes.length > 0) {
                m.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        if (node.matches && (node.matches('.price[data-usd]') || node.querySelector('.price[data-usd]'))) {
                            shouldUpdate = true;
                        }
                    }
                });
            }
        });
        if (shouldUpdate) {
            updateDomPrices();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            observer.observe(document.body, { childList: true, subtree: true });
            updateDomPrices();
            refreshRate();
        });
    } else {
        observer.observe(document.body, { childList: true, subtree: true });
        updateDomPrices();
        refreshRate();
    }
})();
