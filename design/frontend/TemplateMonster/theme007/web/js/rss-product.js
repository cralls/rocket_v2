/** RSS PDP: configurable option chips and existing cart/size guide. */
define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    return function (config, element) {
        var root = $(element);

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

        function enhanceRetailOptions() {
            root.find('select.super-attribute-select').each(function () {
                var select = $(this);
                var field = select.closest('.field');
                var label = field.children('.label');
                var labelSpan = label.find('span').first();
                var labelText = (labelSpan.text() || label.text()).trim();
                var optionType = /\bsizes?\b/i.test(labelText) ? 'size' :
                    /\bcolou?rs?\b/i.test(labelText) ? 'color' : '';
                var form = select.closest('form');
                var attributeId = (select.attr('name') || '').match(/^super_attribute\[(\d+)\]$/);
                var grid;

                if (select.data('rssOptionReady') || !optionType) {
                    return;
                }
                select.data('rssOptionReady', true);
                grid = $('<div>', {'class': 'rss-pdp-sizes', role: 'group', 'aria-label': labelText});
                select.after(grid);

                function sync() {
                    var configurable = form.data('mage-configurable');
                    var spConfig = configurable && configurable.options.spConfig;
                    var attributes = spConfig && spConfig.attributes;
                    var attribute = attributes && attributeId && attributes[attributeId[1]];
                    var nativeOptions = Array.prototype.filter.call(select[0].options, function (option) {
                        return option.value !== '';
                    });
                    // Magento normally doesn't populate a dependent select until its parent is chosen.
                    var options = nativeOptions.length ? nativeOptions : (attribute && attribute.options) || [];
                    var active = document.activeElement;
                    var focusedValue = $.contains(grid[0], active) ? active.value : null;
                    var selectedValue = select.val();

                    grid.empty();
                    if (!options.length) {
                        select.removeClass('rss-pdp-native-option').removeAttr('aria-hidden tabindex');
                        grid.prop('hidden', true);
                        return;
                    }
                    grid.prop('hidden', false);
                    field.addClass('rss-pdp-option-field');
                    if (optionType === 'size') {
                        field.addClass('rss-pdp-size-field');
                        labelSpan.text(config.selectSizeLabel || $t('Select Size'));
                    }
                    select.addClass('rss-pdp-native-option').attr({'aria-hidden': 'true', tabindex: '-1'});
                    options.forEach(function (option) {
                        var value = nativeOptions.length ? option.value : String(option.id);
                        var selected = value === selectedValue;
                        var button = $('<button>', {
                            type: 'button',
                            value: value,
                            'class': 'rss-pdp-size' + (selected ? ' is-selected' : ''),
                            text: nativeOptions.length ? option.text : option.label,
                            disabled: !nativeOptions.length || select.prop('disabled') || option.disabled,
                            'aria-pressed': String(selected)
                        }).on('click', function () {
                            // Magento updates simple-product selection, price and gallery here.
                            select.val(value).trigger('change');
                            if (form.data('validator')) {
                                select.valid();
                            }
                        }).appendTo(grid);
                        if (value === focusedValue && !button.prop('disabled')) {
                            button[0].focus({preventScroll: true});
                        }
                    });
                    placeSizeGuide();
                }

                // Listen at the form so Magento's field handlers update dependent selects first.
                form.on('change.rssPdp', 'select.super-attribute-select', sync);
                form.on('configurablecreate.rssPdp', sync);
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
            var colors = [];
            var colorsById = Object.create(null);
            var activeColor = '';
            var colorAttributeId;
            var colorSelect;
            var colorButtons;
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

            function attributeCell(variants, code) {
                return variants.filter(function () {
                    return code.test($(this).attr('attribute-code') || '');
                }).first();
            }

            function nativeAttribute(attributeId) {
                return order.closest('.product-options-wrapper').find('.bss-hidden .super-attribute-select').filter(function () {
                    return this.id === 'attribute' + attributeId;
                });
            }

            rows.each(function () {
                var row = $(this);
                var variants = row.children('.bss-swatch');
                var sizeCell = attributeCell(variants, /(^|_)size(_|$)/i);
                var colorCell = attributeCell(variants, /(^|_)colou?r(_|$)/i);
                var input = row.find('input.qty_att_product').first();
                var colorId = colorCell.attr('attribute-value');
                var colorLabel = variantLabel(colorCell);
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

                if (colorId && colorLabel) {
                    colorAttributeId = colorCell.attr('attribute-id');
                    if (!colorsById[colorId]) {
                        colorsById[colorId] = true;
                        colors.push({id: colorId, label: colorLabel});
                    }
                    row.data('rssBulkColor', colorId);
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

            if (!sizes.length && !colors.length) {
                return;
            }

            toolbar = $('<div>', {'class': 'rss-pdp-bulk-toolbar'}).prependTo(order);
            if (colors.length) {
                colorSelect = nativeAttribute(colorAttributeId);
                colorSelect.closest('.bss-hidden').addClass('rss-pdp-bulk-native-option');
                $('<div>', {'class': 'rss-pdp-bulk-heading', text: $t('Color')}).appendTo(toolbar);
                colorButtons = $('<div>', {
                    'class': 'rss-pdp-sizes rss-pdp-bulk-colors',
                    role: 'group',
                    'aria-label': $t('Filter variants by color')
                }).appendTo(toolbar);
                if (colors.length > 1) {
                    colors.unshift({id: '', label: $t('All Colors')});
                }
                colors.forEach(function (color) {
                    $('<button>', {
                        type: 'button',
                        'class': 'rss-pdp-size',
                        value: color.id,
                        text: color.label
                    }).on('click', function () {
                        filterColor(color.id);
                        if (color.id && colorSelect.find('option').filter(function () {
                            return this.value === color.id;
                        }).length) {
                            colorSelect.val(color.id).trigger('change');
                        }
                    }).appendTo(colorButtons);
                });
            }
            if (sizes.length) {
                $('<div>', {'class': 'rss-pdp-bulk-heading rss-pdp-bulk-size-heading'}).append(
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
                nativeAttribute(sizeAttributeId).closest('.bss-hidden').addClass('rss-pdp-bulk-native-option');
            }

            function filterColor(colorId) {
                activeColor = colorId;
                // Keep every input enabled or disabled as BSS left it, including on filtered rows.
                rows.each(function () {
                    var row = $(this);
                    var rowColor = row.data('rssBulkColor');
                    row.toggleClass('rss-pdp-bulk-filtered', !!activeColor && !!rowColor && rowColor !== activeColor);
                });
                colorButtons.children('button').each(function () {
                    var selected = this.value === activeColor;
                    $(this).toggleClass('is-selected', selected).attr('aria-pressed', String(selected));
                });
                table.parent().scrollTop(0);
                syncChips();
            }

            function canOrder(input) {
                return !input.disabled && !input.readOnly &&
                    !$(input).closest('tr').hasClass('rss-pdp-bulk-filtered');
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
            if (window.MutationObserver) {
                new MutationObserver(syncChips).observe(table[0], {
                    attributes: true,
                    attributeFilter: ['disabled', 'readonly'],
                    subtree: true
                });
            }
            if (colors.length) {
                filterColor(colors[0].id);
            } else {
                syncChips();
            }
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
                enhanceRetailOptions();
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
