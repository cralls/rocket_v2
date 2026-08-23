/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/totals',
        'Magento_Checkout/js/action/get-payment-information',
        'mage/url',
        'MW_Affiliate/js/model/payment/messages',
        'mage/translate',
    ],
    function (ko, $, Component, totals,getPaymentInformationAction, urlBuilder,messageContainer, $t)
    {
        "use strict";

        var affiliateData = JSON.parse(window.affiliateInfo);
        var code = ko.observable(null);
        var isApplied = ko.observable(false);
        if ((affiliateData.refferral_code != null) && ((affiliateData.refferral_code != 0)) ) {
            code(affiliateData.refferral_code);
            isApplied(true);
        }
        var isLoading = ko.observable(false);
        return Component.extend({
            defaults: {
                template: 'MW_Affiliate/checkout/referral'
            },
            code: code,
            /**
             * Applied flag
             */
            isApplied: isApplied,
            affiliateData: affiliateData,
            isLoading : isLoading,

            isEnable: function () {
               return true;
            },

            apply: function () {
                return this.sendAjax(false);
            },
            /**
             * Cancel using coupon
             */
            cancel: function () {
                return this.sendAjax(true);
                //if (this.validate()) {
                //    isLoading(true);
                //    cancelCreditAmountAction(isApplied, isLoading);
                //}
            },

            sendAjax : function (cancel){
                isLoading(true);
                var self = this;
                var url = urlBuilder.build('affiliate/checkout/referralCodePost');
                var removeCode = 0;
                if(cancel == true){
                    removeCode = 1;
                }

                var messageSuccess = $t('The referral code was applied successfully.');
                var messageCancel = $t('The referral code has been cancelled successfully.');
                var messageFailed = $t('The referral code is invalid.');

                $.ajax(
                    {
                        url: url,
                        method: 'post',
                        data : {
                            code_value : self.code(),
                            removeCode : removeCode,
                        },
                        success: function(response) {
                            //window.location.href = urlBuilder.build('checkout/cart');
                            if(response.canceled){
                                self.isApplied(false);
                                self.code('');
                                messageContainer.addSuccessMessage({
                                    'message': messageCancel
                                });
                            }
                            if(response.success){
                                self.isApplied(true);
                                messageContainer.addSuccessMessage({
                                    'message': messageSuccess
                                });
                            }
                            if(response.failed){
                                messageContainer.addErrorMessage({
                                    'message': messageFailed
                                });
                            }
                            self.isLoading(false);

                            /* update total sumary */
                            var deferred = $.Deferred();
                            isApplied(true);
                            totals.isLoading(true);
                            getPaymentInformationAction(deferred);
                            $.when(deferred).done(function () {
                                totals.isLoading(false);
                            });

                        }
                    }
                );
            }
        });
    }
);