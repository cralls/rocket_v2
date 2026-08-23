/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/block-loader',
    'Magento_Ui/js/modal/alert'
], function ($, ko, Component, blockLoader, alert) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magenest_Xero/report',
            targetUrl: '',
            loaderFileUrl: ''
        },
        requestLogList: ko.observableArray([]),
        logList: ko.observableArray([]),
        isLoading: ko.observable(false),

        initialize: function () {
            return this._super()._create();
        },

        _create: function () {
            blockLoader(this.loaderFileUrl);
        },
        
        getHistory: function () {
            let self = this;
            let startDate = $('[data-role="show-report-date"] :input[type=text]:first').val();
            let endDate = $('[data-role="show-report-date"] :input[type=text]:last').val();
            let showResult = $('[data-block="show-report-result"]');
            let serviceUrl = this.targetUrl;

            if (startDate > endDate) {
                alert({
                    content: 'Invalid date range: Start date must less or equal than End date'
                });
                return;
            }

            serviceUrl = serviceUrl + '?start_date=' + startDate + '&end_date=' + endDate;
            showResult.show();
            self.isLoading(true);
            self.logList([]);
            self.requestLogList([]);
            return $.ajax({
                url: serviceUrl,
                data: {},
                type: 'GET'
            }).done(

                function (response) {
                    let jsonData = JSON.parse(JSON.stringify(response));
                    let i = 0;
                    let item = false;

                    self.isLoading(false);
                    if (!jsonData.items.length) {
                        return;
                    }
                    for (i = 0; i < jsonData.items.length; i++) {
                        item = jsonData.items[i];
                        if (typeof item.date != 'undefined')
                            self.requestLogList.push(item);

                        if (typeof item.type == 'undefined')
                            continue;
                        self.logList.push(item);
                        if (typeof item.count_failed == 'undefined')
                            item.count_failed = 0;
                    }
                }
            );
        }
    });
});
