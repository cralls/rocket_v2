define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (config, element) {
        var $drawer = $(element);

        if ($drawer.data('rss-filter-drawer-initialized')) {
            return;
        }

        $drawer.data('rss-filter-drawer-initialized', true);

        modal({
            type: 'slide',
            title: $.mage.__('Filter Products'),
            modalClass: 'rss-category-filter-modal',
            trigger: '[data-trigger="rss-filter"]',
            responsive: true,
            innerScroll: true,
            clickableOverlay: true,
            buttons: []
        }, $drawer);
    };
});
