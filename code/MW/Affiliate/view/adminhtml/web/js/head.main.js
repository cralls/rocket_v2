define([
    'prototype',
    'jquery',
    'mage/calendar',
    'mage/translate'
], function() {
    jQuery.noConflict();

    if (typeof window.MW=='undefined') {
        window.MW = {
            Affiliate: {
                Report: {
                    Dashboard: {}
                }
            }
        };
    } else {
        window.MW.Affiliate = {
            Report: {
                Dashboard: {}
            }
        }
    }
    window.MW.Affiliate.Report.Dashboard = Class.create();
    MW.Affiliate.Report.Dashboard.prototype = {
        initialize: function(params){
            var self = this;
            this.params = params;
            // Radialize the colors
            Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
                return {
                    radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
                    stops: [
                        [0, color],
                        [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                    ]
                };
            });
            Event.observe('aff_report_range', 'change', this.onChangeRange.bind(this));
            Event.observe('aff_report_refresh', 'click', this.onClickRefresh.bind(this));
            Event.observe(window, 'keypress', this.onWindowKeypress.bind(this));

            this.onChangeRange(null, $("aff_report_range"));

            jQuery("#aff_report_from").calendar({
                showsTime: true,
                dateFormat: 'mm/dd/yy',
                timeFormat: 'HH:mm:ss'
            });
            jQuery("#aff_report_to").calendar({
                showsTime: true,
                dateFormat: 'mm/dd/yy',
                timeFormat: 'HH:mm:ss'
            });
        },
        onWindowKeypress: function(event, element){
            console.log(event.keyCode);
            if(Event.KEY_RETURN == event.keyCode){
                this.onChangeRange(null, $("aff_report_range"));
            }

            if(event.keyCode == 115){
                var output = '';
                for (property in this.statistics) {
                    output += property + ': ' + this.statistics[property]+"; <br>\n";
                }
                $("aff_debug").innerHTML = output;
            }
            if(event.keyCode == 99){
                $("aff_debug").innerHTML = '';s
            }
        },
        onClickRefresh: function(event, element){
            var self = this;
            new Ajax.Request(this.params.url, {
                method: 'post',
                parameters: {ajax: true, report_range: $("aff_report_range").value, from: $("aff_report_from").value,  to: $("aff_report_to").value, type: 'dashboard'},
                onSuccess: function(transport){
                    if(transport.responseText){
                        var data = transport.responseText.evalJSON();
                        self.data = data.report;
                        self.buildChart(parseInt($("aff_report_range").value));
                        self.buildPieChartSales(data.report_sales_by_programs);
                        self.buildPieChartCommission(data.report_commission_by_programs);
                        self.fillDataStats(data.statistics);
                        self.fillDataTopAff(data.topaffiliate,data.statistics['total_affiliate_sales'],data.statistics['total_affiliate_commission']);
                    }else{
                        /** Draw empty grah */
                    }
                }
            });
        },
        onChangeRange: function(event, element){
            var self = this;

            if(element == undefined){
                element = $(Event.element(event));
            }
            if(parseInt(element.value) == 7){
                $("aff_custom_range").show();

                return false;
            }

            $("aff_custom_range").hide();
            new Ajax.Request(this.params.url, {
                method: 'post',
                parameters: {ajax: true, report_range: element.value, type: 'dashboard'},
                onSuccess: function(transport){
                    if(transport.responseText){
                        var data = transport.responseText.evalJSON();
                        self.data = data.report;
                        self.buildChart(parseInt(element.value));
                        self.buildPieChartSales(data.report_sales_by_programs);
                        self.buildPieChartCommission(data.report_commission_by_programs);
                        self.fillDataStats(data.statistics);
                        self.fillDataTopAff(data.topaffiliate,data.statistics['total_affiliate_sales'],data.statistics['total_affiliate_commission']);
                        self.statistics = data.statistics;
                    }else{
                        /** Draw empty grah */
                    }
                }
            });
        },
        fillDataStats: function(data){
            /*Fill in table in left*/
            $("aff_total_sales").innerHTML = data.total_affiliate_sales;
            $("aff_total_order").innerHTML = data.total_affiliate_order;
            $("aff_total_commission").innerHTML = data.total_affiliate_commission;
            $("aff_total_withdrawal").innerHTML = data.total_withdrawn;
            $("aff_total_fee").innerHTML = data.total_fee;
            $("aff_total_transactions").innerHTML = data.total_transaction;
            $("aff_avr_commission_per_order").innerHTML = data.avg_commission_per_order;
            $("aff_avr_commission_per_aff").innerHTML = data.avg_commission_per_affiliate;
            $("aff_avr_sales_per_order").innerHTML = data.avg_sales_per_order;
            $("aff_avr_sales_per_aff").innerHTML = data.avg_sales_per_affiliate;
            $("aff_total_aff").innerHTML = data.total_affiliate.all_actived;
        },
        fillDataTopAff: function(data,total_sales,total_commission){
            /*Fill in table in left*/
            var table_content = "";
            if(data != null){
                data.forEach(function showTop(aff,key){
                    var rank = parseInt(key)+1;
                    table_content = table_content + '<tr><td class="a-left">'+rank+'</td><td class="a-left">'+aff['affiliate']+'</td><td class="a-right">'+aff['total_affiliate_sales']+'</td><td class="a-right">'+aff['total_affiliate_commission']+'</td></tr>'
                });
            }
            table_content = table_content + '<tr><td colspan="4">....</td></tr>';
            table_content = table_content + '<tr><td colspan="2">All Affiliate</td>'+
                            '<td class="a-right" id="total_affiliate_sales">'+total_sales+'</td>'+
                            '<td class="a-right" id="total_affiliate_commission">'+total_commission+'</td></tr>';
            $("aff-top").innerHTML = table_content;
        },
        buildPieChartSales: function(data){
            var data = data.evalJSON();
            var _data = new Array();
            if(Object.keys(data).length == 0){
                $("aff-container-sales").innerHTML = '<span style="color: #ccc; text-align: center; display: block;">'+ jQuery.mage.__('No Record')+'</span>';
                return false;
            }
            for(var i = 0; i < Object.keys(data).length; i++){
                var _data_item = new Array();
                _data_item.push(data[i][0], data[i][1]);
                _data.push(_data_item);
            }
            /*Sales by programs chart*/
            var chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'aff-container-sales',
                    plotBackgroundColor: null,
                    plotBorderWidth: 1,
                    plotShadow: false
                },
                exporting:{
                    enabled: false
                },
                title: {
                    text: ''
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:.2f}%</b>'
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    x: 0,
                    verticalAlign: 'top',
                    y: 20,
                    floating: true,
                    backgroundColor: 'transparent',
                    labelFormatter: function () {
                        return this.name+ ": "+this.y + "%";
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            connectorWidth: 2,
                            format: '{point.name}: {point.y:.2f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            },
                        },
                        connectorColor: 'silver',
                        showInLegend: false
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Sales Percent',
                    data: _data
                }]
            });
        },
        buildPieChartCommission: function(data){
            var data = data.evalJSON();
            var _data = new Array();
            if(Object.keys(data).length == 0){
                $("aff-container-commission").innerHTML = '<span style="color: #ccc; text-align: center; display: block;">'+ jQuery.mage.__('No Record')+'</span>';
                return false;
            }
            for(var i = 0; i < Object.keys(data).length; i++){
                var _data_item = new Array();
                _data_item.push(data[i][0], data[i][1]);
                _data.push(_data_item);
            }

            /*Commission by programs chart*/
            var chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'aff-container-commission',
                    plotBackgroundColor: null,
                    plotBorderWidth: 1,
                    plotShadow: false
                },
                exporting:{
                    enabled: false
                },
                title: {
                    text: ''
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.y:.2f}%</b>'
                },
                legend: {
                    layout: 'vertical',
                    align: 'left',
                    x: 0,
                    verticalAlign: 'top',
                    y: 20,
                    floating: true,
                    backgroundColor: 'transparent',
                    labelFormatter: function () {
                        return this.name+ ": "+this.y + "%";
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: true,
                            connectorWidth: 2,
                            format: '{point.name}: {point.y:.2f} %',
                            style: {
                                color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                            },
                        },
                        showInLegend: false
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Commission Percent',
                    data: _data
                }]
            });
        },
        buildChart: function(type){
            var self = this;
            self.buildOptionChart(type);

            var chart = new Highcharts.Chart({
                chart: {
                    renderTo: 'aff-container',
                },
                title: {
                    text: self.data.title
                },
                subtitle: {
                    text: ''
                },
                xAxis: self.xAxis,
                yAxis: [{
                    title: {
                        text: 'Sales Generated By Affiliates',
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    },
                    labels: {
                        format: '{value:.,0f}'+self.data.curency,
                        style: {
                            color: Highcharts.getOptions().colors[0]
                        }
                    }
                },
                    {
                        labels: {
                            format: '{value:.,0f}'+self.data.curency,
                            style: {
                                color: Highcharts.getOptions().colors[1]
                            }
                        },
                        title: {
                            text: 'Affiliate Commission',
                            style: {
                                color: Highcharts.getOptions().colors[1]
                            }
                        },
                        opposite: true
                    },
                    {
                        title: {
                            text: 'Referred Customer Discount',
                            style: {
                                color: Highcharts.getOptions().colors[0]
                            }
                        },
                        labels: {
                            format: '{value:.,0f}'+self.data.curency,
                            style: {
                                color: Highcharts.getOptions().colors[0]
                            }
                        },
                        opposite: true
                    }],
                tooltip: {
                    shared: true,
                    crosshairs: true
                },
                plotOptions: {
                    series: {
                        cursor: 'pointer',
                        point: {
                            events: {
                                click: function (e) {
                                    hs.htmlExpand(null, {
                                        pageOrigin: {
                                            x: e.pageX || e.clientX,
                                            y: e.pageY || e.clientY
                                        },
                                        headingText: this.series.name,
                                        maincontentText: Highcharts.dateFormat('%A, %b %e, %Y', this.x) + '<br/> ' +
                                            'Totals: <b>'+this.y + '&nbsp;'+self.data.curency+'</b>',
                                        width: 150
                                    });
                                }
                            }
                        },
                        fillOpacity: 0.1
                    }
                },
                series: this.series
            });
        },
        buildOptionChart: function(type){
            var self = this;
            var data_commission = null;
            var data_sales = null;
            var data_discount = null;
            switch(type){
                case 1: // Last 24 hours
                    self.xAxis = {
                        type: 'datetime',
                        labels: {
                            format: '{value:%H:%M}',
                            //rotation: 45,
                            align: 'left'
                        }
                    };
                    var pointStart = Date.UTC(self.data.date_start.y, self.data.date_start.m - 1, self.data.date_start.d, self.data.date_start.h);
                    var pointInterval = 1 * 3600 * 1000;

                    data_commission = self.data.commission;
                    data_sales = self.data.sales;
                    data_discount = self.data.discount;
                    break;
                case 2: // Last week
                    self.xAxis = {
                        type: 'datetime',
                        tickInterval: 24 * 3600 * 1000,
                        labels: {
                            format: '{value:%b %d}',
                            align: 'left'
                        }
                    };
                    var pointStart = Date.UTC(self.data.date_start.y, self.data.date_start.m - 1, self.data.date_start.d);
                    var pointInterval = 24 * 3600 * 1000;

                    data_commission = self.data.commission;
                    data_sales = self.data.sales;
                    data_discount = self.data.discount;
                    break;
                case 3: // Last month
                    self.xAxis = {
                        type: 'datetime',
                        tickInterval: 7 * 24 * 3600 * 1000,
                        labels: {
                            format: '{value:%b %d}',
                            align: 'left'
                        }
                    };
                    var pointStart = Date.UTC(self.data.date_start.y, self.data.date_start.m - 1, self.data.date_start.d);
                    var pointInterval = 24 * 3600 * 1000;

                    data_commission = self.data.commission;
                    data_sales = self.data.sales;
                    data_discount = self.data.discount;
                    break;
                case 4: // Last 7 days
                case 5: // Last 30 days
                case 7: // Custom range
                    self.xAxis = {
                        type: 'datetime',
                        dateTimeLabelFormats: {
                            month: '%e. %b',
                            year: '%b'
                        },
                    };

                    var commission = new Array();
                    self.data.commission.each(function(value, k){
                        commission.push([Date.UTC(value[0],  value[1] - 1,  value[2]), value[3]]);
                    });

                    var sales = new Array();
                    self.data.sales.each(function(value, k){
                        sales.push([Date.UTC(value[0],  value[1] - 1,  value[2]), value[3]]);
                    });

                    var discount = new Array();
                    self.data.discount.each(function(value, k){
                        discount.push([Date.UTC(value[0],  value[1] - 1,  value[2]), value[3]]);
                    });

                    data_commission = commission;
                    data_sales = sales;
                    data_discount = discount;

                    var pointStart = null;
                    var pointInterval = 24 * 3600 * 1000;
                    break;
            }
            this.series = [{
                name: 'Sales Generated By Affiliates',
                type: 'area',
                color: '#C74204',
                data: data_sales,
                tooltip: {
                    valueSuffix: ' '+self.data.curency
                },
                pointStart: pointStart,
                pointInterval: pointInterval
            },
                {
                    name: 'Affiliate Commission',
                    type: 'area',
                    color: '#0481C7',
                    data: data_commission,
                    yAxis: 1,
                    tooltip: {
                        valueSuffix: ' '+self.data.curency
                    },
                    pointStart: pointStart,
                    pointInterval: pointInterval

                },
                {
                    name: 'Referred Customer Discount',
                    type: 'area',
                    color: '#2EEC38',
                    data: data_discount,
                    yAxis: 1,
                    tooltip: {
                        valueSuffix: ' '+self.data.curency
                    },
                    pointStart: pointStart,
                    pointInterval: pointInterval

                }];
        }
    }
});