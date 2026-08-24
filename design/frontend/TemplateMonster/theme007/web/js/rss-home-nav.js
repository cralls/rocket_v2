define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $nav = $(element);
        var $toggles = $nav.find('.rss-home-nav__toggle');

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

        $toggles.on('click', function () {
            var $toggle = $(this);

            if ($toggle.attr('aria-expanded') === 'true') {
                closePanel($toggle, true);
            } else {
                openPanel($toggle);
            }
        });
    };
});
