
define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/payment/default'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        redirectAfterPlaceOrder: false,
        defaults: {
            template: 'Silksoftwarecorp_Alipay/payment/alipay'
        },

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            $.mage.redirect(
                window.checkoutConfig.payment.alipay.payUrl
            );
        },
        getPaymentLogoUrl: function() {
            return window.checkoutConfig.payment.alipay.logoUrl;
        },
        getInstructions: function() {
            return window.checkoutConfig.payment.alipay.instructions;
        }
    });
});
