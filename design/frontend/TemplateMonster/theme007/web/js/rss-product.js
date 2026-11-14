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
            var label = root.find('.rss-pdp-bulk-size-heading').first();

            if (!label.length) {
                label = root.find('.rss-pdp-size-field > .label, .swatch-attribute.size > .swatch-attribute-label').first();
            }

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

        function enhanceBulkOrder() {
            var order = root.find('#bss_configurablegridview').first();
            var table = order.find('.configurable-product-table').first();
            var rows = table.find('tbody > tr.item-info');
            var headers = table.find('thead th');
            var sizes = [];
            var byId = Object.create(null);
            var sizeAttributeId;
            var toolbar;
            var chips;

            if (!rows.find('.qty_att_product').length || order.hasClass('rss-pdp-bulk')) {
                return;
            }

            function variantLabel(cell) {
                // BSS replaces the original label with a swatch on some products.
                return (cell.find('.attr-label').text() || cell.find('[option-label]').first().attr('option-label') || '').trim();
            }

            rows.each(function () {
                var row = $(this);
                var variants = row.children('.bss-swatch');
                var sizeCell = variants.filter(function () {
                    return /(^|_)size(_|$)/i.test($(this).attr('attribute-code') || '');
                }).first();
                var input = row.find('input.qty_att_product').first();
                var sizeId = sizeCell.attr('attribute-value');
                var sizeLabel = variantLabel(sizeCell);
                var variantLabels = variants.map(function () {
                    return variantLabel($(this));
                }).get().filter(Boolean);

                row.children('td').each(function (index) {
                    $(this).attr('data-rss-bulk-label', headers.eq(index).text().trim());
                });

                if (input.length) {
                    input.attr('inputmode', 'decimal');
                    if (!input.attr('aria-label') && variantLabels.length) {
                        input.attr('aria-label', $t('Quantity for %1').replace('%1', variantLabels.join(', ')));
                    }
                }

                if (!sizeId || !sizeLabel) {
                    return;
                }
                sizeAttributeId = sizeCell.attr('attribute-id');
                if (!byId[sizeId]) {
                    byId[sizeId] = {label: sizeLabel, inputs: []};
                    sizes.push(byId[sizeId]);
                }
                if (input.length) {
                    byId[sizeId].inputs.push(input[0]);
                }
            });

            order.addClass('rss-pdp-bulk');
            order.closest('.product-options-wrapper').addClass('rss-pdp-bulk-options');
            order.find('.reset-configurablegridview').empty().append($('<button>', {
                type: 'button',
                'class': 'rss-pdp-bulk-reset',
                text: $t('Reset quantities')
            }));

            if (!sizes.length) {
                return;
            }

            toolbar = $('<div>', {'class': 'rss-pdp-bulk-toolbar'}).prependTo(order);
            $('<div>', {'class': 'rss-pdp-bulk-size-heading'}).append(
                $('<span>', {text: $t('Select Sizes')})
            ).appendTo(toolbar);
            chips = $('<div>', {
                'class': 'rss-pdp-sizes',
                role: 'group',
                'aria-label': $t('Jump to a size quantity')
            }).appendTo(toolbar);
            $('<p>', {
                'class': 'rss-pdp-bulk-hint',
                text: $t('Choose a size to jump to its quantity. You can order more than one size.')
            }).appendTo(toolbar);

            order.closest('.product-options-wrapper').find('.bss-hidden .super-attribute-select').filter(function () {
                return this.id === 'attribute' + sizeAttributeId;
            }).closest('.bss-hidden').addClass('rss-pdp-bulk-native-size');

            function canOrder(input) {
                return !input.disabled && !input.readOnly && $(input).is(':visible');
            }

            function syncChips() {
                sizes.forEach(function (size) {
                    var available = size.inputs.filter(canOrder);
                    var hasQuantity = available.some(function (input) {
                        return parseFloat(input.value) > 0;
                    });

                    size.button.prop('disabled', !available.length)
                        .toggleClass('is-selected', hasQuantity)
                        .attr('aria-label', (hasQuantity ? $t('Size %1, quantity entered') : $t('Size %1'))
                            .replace('%1', size.label));
                });
            }

            sizes.forEach(function (size) {
                size.button = $('<button>', {
                    type: 'button',
                    'class': 'rss-pdp-size',
                    text: size.label
                }).on('click', function () {
                    var input = size.inputs.filter(canOrder)[0];

                    if (input) {
                        input.scrollIntoView({block: 'nearest', inline: 'nearest'});
                        input.focus({preventScroll: true});
                    }
                }).appendTo(chips);
            });
            // BSS owns quantity validation, totals and cart data. Reflect its existing inputs and reset.
            order.on('input.rssBulk change.rssBulk', '.qty_att_product', syncChips);
            order.on('click.rssBulk', '.reset-configurablegridview', syncChips);
            root.on('contentUpdated.rssBulk', syncChips);
            syncChips();
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

        function enhanceOptions() {
            if (root.find('#bss_configurablegridview').length) {
                enhanceBulkOrder();
                placeSizeGuide();
            } else {
                enhanceSizes();
            }
        }

        enhanceOptions();
        placeCustomize();
        root.on('contentUpdated.rssPdp', function () {
            enhanceOptions();
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
