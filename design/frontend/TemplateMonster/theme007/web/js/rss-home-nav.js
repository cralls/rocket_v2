define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $nav = $(element);
        var $toggles = $nav.find('.rss-home-nav__toggle');

        function styleHelpLauncher() {
            var attempts = 0;
            var maxAttempts = 20;

            function applyLauncherStyle() {
                if (typeof window.zE === 'function') {
                    window.zE('webWidget', 'updateSettings', {
                        webWidget: {
                            color: {
                                launcher: '#C7C8CA',
                                launcherText: '#000000'
                            }
                        }
                    });
                    return;
                }

                attempts += 1;
                if (attempts < maxAttempts) {
                    window.setTimeout(applyLauncherStyle, 250);
                }
            }

            applyLauncherStyle();
        }

        function closePanel($toggle, animate) {
            var panelId = $toggle.attr('aria-controls');
            var $panel = $('#' + panelId);

            $toggle.attr('aria-expanded', 'false');
            $toggle.closest('.rss-home-nav__group').removeClass('_open');

            if (animate) {
                $panel.stop(true, true).slideUp(180, function () {
                    $panel.prop('hidden', true).removeAttr('style');
                });
            } else {
                $panel.prop('hidden', true);
            }
        }

        function openPanel($toggle) {
            var panelId = $toggle.attr('aria-controls');
            var $panel = $('#' + panelId);

            $toggles.not($toggle).each(function () {
                closePanel($(this), true);
            });

            $toggle.attr('aria-expanded', 'true');
            $toggle.closest('.rss-home-nav__group').addClass('_open');
            $panel.prop('hidden', false).hide().stop(true, true).slideDown(180, function () {
                $panel.removeAttr('style');
            });
        }

        function activateSecondaryTab($tab, focusTab) {
            var $secondary = $tab.closest('[data-rss-secondary-tabs]');
            var $tabs = $secondary.find('.rss-home-nav__tab');
            var panelId = $tab.attr('aria-controls');

            $tabs.each(function () {
                var $currentTab = $(this);
                var currentPanelId = $currentTab.attr('aria-controls');

                $currentTab
                    .removeClass('_active')
                    .attr('aria-selected', 'false')
                    .attr('tabindex', '-1');

                $('#' + currentPanelId).prop('hidden', true);
            });

            $tab
                .addClass('_active')
                .attr('aria-selected', 'true')
                .attr('tabindex', '0');

            $('#' + panelId).prop('hidden', false);

            if (focusTab) {
                $tab.trigger('focus');
            }
        }

        styleHelpLauncher();

        $toggles.on('click', function () {
            var $toggle = $(this);

            if ($toggle.attr('aria-expanded') === 'true') {
                closePanel($toggle, true);
            } else {
                openPanel($toggle);
            }
        });

        $nav.on('click', '.rss-home-nav__tab', function () {
            activateSecondaryTab($(this), false);
        });

        $nav.on('keydown', '.rss-home-nav__tab', function (event) {
            var $tab = $(this);
            var $tabs = $tab.closest('[data-rss-secondary-tabs]').find('.rss-home-nav__tab');
            var index = $tabs.index($tab);
            var nextIndex;

            if (event.key === 'ArrowRight') {
                nextIndex = (index + 1) % $tabs.length;
            } else if (event.key === 'ArrowLeft') {
                nextIndex = (index - 1 + $tabs.length) % $tabs.length;
            } else if (event.key === 'Home') {
                nextIndex = 0;
            } else if (event.key === 'End') {
                nextIndex = $tabs.length - 1;
            } else {
                return;
            }

            event.preventDefault();
            activateSecondaryTab($tabs.eq(nextIndex), true);
        });
    };
});
