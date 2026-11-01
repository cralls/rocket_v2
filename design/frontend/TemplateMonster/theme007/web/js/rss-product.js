/** RSS PDP Rev2, 2026-11-01: native size selection and existing cart/size guide. */
define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    return function (config, element) {
        var root = $(element);
        var selects = [];

        if (root.data('rssPdpReady')) {
            return;
        }
        root.data('rssPdpReady', true);

        function placeSizeGuide() {
            var guide = root.find('.ave-sizechart-show-link').first();
            var label = root.find('.rss-pdp-size-field > .label, .swatch-attribute.size > .swatch-attribute-label').first();

            if (!guide.length || !label.length) {
                return;
            }
            // Reuse the extension's own link and click handler, including its modal.
            guide.find('.ave-sizechart-button-label').text(config.sizeGuideLabel || $t('Size Guide'));
            guide.addClass('rss-pdp-size-guide');
            if (!$.contains(label[0], guide[0])) {
                label.append(guide);
            }
        }

        function enhanceSizes() {
            root.find('select.super-attribute-select').each(function () {
                var select = $(this);
                var field = select.closest('.field');
                var label = field.children('.label');
                var labelText = label.find('span').first().text() || label.text();
                var grid;

                // Bulk ordering keeps the BSS table and all per-size quantities intact.
                if (select.data('rssSizeReady') || field.hasClass('bss-hidden') || !/size/i.test(labelText)) {
                    return;
                }
                select.data('rssSizeReady', true);
                grid = $('<div>', {'class': 'rss-pdp-sizes', role: 'group', 'aria-label': labelText.trim()});
                select.after(grid);

                function sync() {
                    var options = Array.prototype.filter.call(select[0].options, function (option) {
                        return option.value !== '';
                    });

                    grid.empty();
                    if (!options.length) {
                        select.removeClass('rss-pdp-native-size').removeAttr('aria-hidden tabindex');
                        grid.prop('hidden', true);
                        return;
                    }
                    grid.prop('hidden', false);
                    field.addClass('rss-pdp-size-field');
                    label.find('span').first().text(config.selectSizeLabel || $t('Select Size'));
                    select.addClass('rss-pdp-native-size').attr({'aria-hidden': 'true', tabindex: '-1'});
                    options.forEach(function (option) {
                        $('<button>', {
                            type: 'button',
                            'class': 'rss-pdp-size' + (option.value === select.val() ? ' is-selected' : ''),
                            text: option.text,
                            disabled: select.prop('disabled') || option.disabled,
                            'aria-pressed': option.value === select.val() ? 'true' : 'false'
                        }).on('click', function () {
                            // Magento updates simple-product selection, price and gallery here.
                            select.val(option.value).trigger('change');
                            selects.forEach(function (item) { item.sync(); });
                            if (select.closest('form').data('validator')) {
                                select.valid();
                            }
                            grid.find('button').filter(function () {
                                return $(this).text() === option.text;
                            }).first().trigger('focus');
                        }).appendTo(grid);
                    });
                    placeSizeGuide();
                }

                selects.push({sync: sync});
                select.on('change.rssPdp', sync);
                if (window.MutationObserver) {
                    new MutationObserver(sync).observe(select[0], {
                        childList: true,
                        subtree: true,
                        attributes: true,
                        attributeFilter: ['disabled', 'selected']
                    });
                }
                sync();
            });
            placeSizeGuide();
        }

        function placeCustomize() {
            root.find('.box-tocart .actions').each(function () {
                var actions = $(this);
                if (actions.find('.rss-pdp-customize').length) {
                    return;
                }
                $('<button>', {
                    type: 'button',
                    'class': 'action rss-pdp-customize',
                    disabled: true,
                    text: config.customizeLabel || $t('Customize')
                }).prependTo(actions);
                actions.addClass('rss-pdp-cart-actions');
            });
        }

        enhanceSizes();
        placeCustomize();
        root.on('contentUpdated.rssPdp', function () {
            enhanceSizes();
            placeCustomize();
        });
        root.find('.swatch-opt').on('swatch.initialized.rssPdp', placeSizeGuide);
        // Keep review deep links usable with the retained, collapsible reviews block.
        function revealReviews() {
            if (/^#(reviews|review-form)$/.test(window.location.hash)) {
                root.find('details#reviews').prop('open', true);
            }
        }
        revealReviews();
        $(window).on('hashchange.rssPdp', revealReviews);
    };
});
