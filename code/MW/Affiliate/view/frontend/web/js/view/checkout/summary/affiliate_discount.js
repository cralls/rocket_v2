/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component, quote) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'MW_Affiliate/checkout/summary/affiliate/discount'
            },
            isDisplayed: function() {
                return this.isFullMode();
            },
            getValue: function() {

                var totals = quote.getTotals()();
                //return window.affiliateDiscount;
                return 0;
            },
            hasDiscount: function() {
                if (this.getValue() != 0) {
                    return true;
                } else {
                    return false;
                }
            }
        });
    }
);
