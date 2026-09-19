define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var root = element;
        var mainView = root.querySelector('[data-rss-cookie-view="main"]');
        var preferencesView = root.querySelector('[data-rss-cookie-view="preferences"]');
        var analyticsToggle = root.querySelector('[data-rss-analytics-toggle]');
        var expireDays = parseInt(config.expireDays, 10) || 1;
        var resolvedCookieName = 'cookienotice';
        var analyticsCookieName = 'rss_analytics_consent';

        function getCookie(name) {
            var prefix = name + '=';
            var parts = document.cookie ? document.cookie.split(';') : [];
            var i;
            var item;

            for (i = 0; i < parts.length; i += 1) {
                item = parts[i].trim();
                if (item.indexOf(prefix) === 0) {
                    return decodeURIComponent(item.substring(prefix.length));
                }
            }

            return null;
        }

        function setCookie(name, value) {
            var date = new Date();
            date.setTime(date.getTime() + (expireDays * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + encodeURIComponent(value) +
                ';expires=' + date.toUTCString() + ';path=/;SameSite=Lax';
        }

        function analyticsAllowed() {
            var explicitChoice = getCookie(analyticsCookieName);
            var legacyChoice = getCookie(resolvedCookieName);

            if (explicitChoice !== null) {
                return explicitChoice === '1';
            }

            return legacyChoice === 'true';
        }

        function updateGoogleConsent(allowed) {
            window.dataLayer = window.dataLayer || [];
            window.gtag = window.gtag || function () {
                window.dataLayer.push(arguments);
            };

            window.gtag('consent', 'update', {
                analytics_storage: allowed ? 'granted' : 'denied',
                ad_storage: 'denied',
                ad_user_data: 'denied',
                ad_personalization: 'denied'
            });

            window.dataLayer.push({
                event: 'rss_cookie_consent_update',
                rss_analytics_consent: allowed ? 'granted' : 'denied'
            });
        }

        function setView(viewName) {
            var showPreferences = viewName === 'preferences';

            mainView.hidden = showPreferences;
            preferencesView.hidden = !showPreferences;

            if (showPreferences) {
                analyticsToggle.checked = analyticsAllowed();
            }

            window.setTimeout(function () {
                var firstButton = root.querySelector(
                    '[data-rss-cookie-view="' + viewName + '"] button:not([disabled]), ' +
                    '[data-rss-cookie-view="' + viewName + '"] input:not([disabled])'
                );

                if (firstButton) {
                    firstButton.focus();
                }
            }, 0);
        }

        function show(viewName) {
            root.hidden = false;
            document.documentElement.classList.add('rss-cookie-consent-open');
            setView(viewName || 'main');
        }

        function hide() {
            root.hidden = true;
            document.documentElement.classList.remove('rss-cookie-consent-open');
        }

        function resolve(allowAnalytics) {
            setCookie(resolvedCookieName, allowAnalytics ? 'true' : '0');
            setCookie(analyticsCookieName, allowAnalytics ? '1' : '0');
            updateGoogleConsent(allowAnalytics);
            hide();

            window.dispatchEvent(new CustomEvent('rss:cookie-consent-resolved', {
                detail: {
                    analytics: allowAnalytics
                }
            }));
        }

        function hasResolvedChoice() {
            return getCookie(resolvedCookieName) !== null;
        }

        if (!hasResolvedChoice()) {
            show('main');
        }

        root.addEventListener('click', function (event) {
            var button = event.target.closest('[data-rss-cookie-action]');

            if (!button) {
                return;
            }

            switch (button.getAttribute('data-rss-cookie-action')) {
                case 'accept':
                    resolve(true);
                    break;
                case 'reject':
                    resolve(false);
                    break;
                case 'preferences':
                    setView('preferences');
                    break;
                case 'back':
                    setView('main');
                    break;
                case 'save':
                    resolve(analyticsToggle.checked);
                    break;
            }
        });

        $(document).on('click.rssCookieConsent', '[data-rss-cookie-settings]', function (event) {
            event.preventDefault();
            show('preferences');
        });

        window.addEventListener('rss:cookie-settings-open', function () {
            show('preferences');
        });
    };
});
