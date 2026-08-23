define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
        $.widget('sizechart.js', {
            inputDimensionSelector: 'input.ave-sizechart-input-dimension',
            activeMemberSelector: '#active_member',
            formDimensionSelector: 'form#ave_dimension_group_form',
            unitOfLengthBtnSelector: '.measurement_toggle .unit_of_length_btn',
            startButtonSelector: '.ave-sizechart-show-link',
            startButtonLabelSelector: '.ave-sizechart-show-link .ave-sizechart-button-label',
            currentSizeSelector: '#ave-sizechart-current-size',
            tableCellSelector: 'table.ave-sizechart-table td',
            inputSelectDimensionName: 'ave_sizechart_unit_of_length_select',
            mainMatchClass: 'main-match-size',
            subMatchClass: 'sub-match-size',
            matchClass: 'match-size',
            userDimensions: [],
            dimensions: [],
            membersMeasurements: [],
            sizes: [],
            currentDimension: '',
            accuracy: 4,
            cmInInch: 2.54,
            cmInFeet: 30.48,
            inchInFeet: 12,
            kgInLb: 0.45359237,
            codeCm:   'cm',
            codeInch: 'inch',
            cookieNameCurrent: 'ave_sizechart_current_size',
            setActiveUrl: null,
            setDimensionUrl: null,
            isImageAvailable: null,
            isLoggedIn: 0,
            noNeedSaveDimensionCount: 0,
            yourSizeLabel: 'Your size is',
            yourSizeUndefinedLabel: 'You size is undefined',
            yourSizeOutOfRangeLabel: 'Your size is out of range!',
            sizeChartButtonLabel: 'Size Chart',
            heightType: 'is_height',
            lengthType: 'is_length',
            weightType: 'is_weight',
            unitClassCm: 'unit-cm',
            unitClassInch: 'unit-inch',
            _create: function () {
                ave_sizechart = this;
                this.init();
            },
            init: function () {
                this.sizes = this.options.sizes;
                this.dimensions = this.options.dimensions;
                this.setActiveUrl = this.options.setActiveUrl;
                this.setDimensionUrl = this.options.setDimensionUrl;
                this.membersMeasurements = this.options.membersMeasurements;
                this.currentDimension = this.options.currentDimension;
                this.yourSizeLabel = this.options.yourSizeLabel;
                this.yourSizeUndefinedLabel = this.options.yourSizeUndefinedLabel;
                this.yourSizeOutOfRangeLabel = this.options.yourSizeOutOfRangeLabel;
                this.isLoggedIn = this.options.isLoggedIn;
                this.isImageAvailable = this.options.isImageAvailable;
                this.sizeChartButtonLabel = $(this.startButtonLabelSelector).html();
                if (this.isLoggedIn) {
                    this.initDimensionsByMember();
                }
                this.initDimensionsFromCookie();
                this.initBaseMeasurement();
                this.initListeners();
                this.highlightCellByDimensions();
            },
            initListeners: function () {
                $(this.formDimensionSelector).on('submit', function () {
                    return false;
                });
                $(this.activeMemberSelector).on('change', ave_sizechart.changeMember);
                $(this.startButtonSelector).on('click', ave_sizechart.showPopup);
                $(document).keyup(function (e) {
                    if (e.keyCode === 27) {
                        ave_sizechart.hidePopup();
                    }
                });
                $(this.inputDimensionSelector).on('change', ave_sizechart.changeUserDimension);
                $(this.unitOfLengthBtnSelector).on('click', ave_sizechart.changeMeasurement);
                $(this.tableCellSelector).on('click', ave_sizechart.chooseSize);
            },
            chooseSize: function () {
                $('.' + ave_sizechart.matchClass).each(function (index, element) {
                    $(element).removeClass(ave_sizechart.matchClass);
                });
                $('.' + ave_sizechart.subMatchClass).each(function (index, element) {
                    $(element).removeClass(ave_sizechart.subMatchClass);
                });
                $(this).parent().addClass(ave_sizechart.matchClass);
                var td, tdId, size, input;
                if (this.parentNode.childNodes.length > 0) {
                    for (var i = 0; i < this.parentNode.childNodes.length; i++) {
                        td = this.parentNode.childNodes[i];
                        tdId = td.id;
                        for (var k in ave_sizechart.sizes) {
                            input = $('#ave_sizechart_dimension_' + k);
                            var sizeInch = 0;
                            if (ave_sizechart.sizes.hasOwnProperty(k) && input.length > 0) {
                                for (var sizeId in ave_sizechart.sizes[k]) {
                                    if (ave_sizechart.sizes[k].hasOwnProperty(sizeId) && sizeId == tdId) {
                                        size = ave_sizechart.getAverageSize(ave_sizechart.sizes[k][sizeId]);
                                        if (ave_sizechart.currentDimension === ave_sizechart.codeInch) {
                                            var onlyInch = input.parent().hasClass('feet-row') && !$(this).hasClass('feet');
                                            var onlyFeet = input.parent().hasClass('feet-row') && $(this).hasClass('feet');
                                            if (onlyInch && !onlyFeet) {
                                                var val = ave_sizechart.cmToInch(size, k, 0, 1);
                                                $('#ave_sizechart_dimension_' + k + '-feet').val(val);
                                                sizeInch = ave_sizechart.cmToInch(size, k, onlyInch, onlyFeet);
                                            }
                                            size = ave_sizechart.cmToInch(size, k);
                                        }
                                        if (sizeInch) {
                                            input.val(sizeInch);
                                        } else {
                                            input.val(size);
                                        }
                                        $(td).addClass(ave_sizechart.matchClass);
                                        if (ave_sizechart.isNumber(size)) {
                                            ave_sizechart.setCookie(input.attr("name"), size);
                                            ave_sizechart.highlightCellByDimensions();
                                            ave_sizechart.saveMemberDimension(k, size);
                                            $(input).removeClass('ave-sizechart-error');
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            },
            showPopup: function () {
                var holder = document.getElementById('ave-sizechart-popup-holder'), holder_bg, container, close_btn, tableHolder;
                if (!holder) {
                    holder = document.createElement('div');
                    holder.setAttribute('id', 'ave-sizechart-popup-holder');

                    holder_bg = document.createElement('div');
                    $(holder_bg).addClass('ave-sizechart-popup-holder-background animated');
                    holder_bg.onclick = function () {
                        ave_sizechart.hidePopup();
                    };

                    close_btn = document.createElement('div');
                    $(close_btn).addClass('ave-sizechart-popup-holder-closebutton');
                    close_btn.innerHTML = '<a href="javascript:ave_sizechart.hidePopup();" title="close">&times;</a>';

                    container = document.createElement('div');
                    $(container).addClass('ave-sizechart-popup-holder-content');

                    document.getElementsByTagName('body')[0].appendChild(holder);
                    holder.appendChild(holder_bg);
                    holder.appendChild(container);
                    container.appendChild(close_btn);
                    tableHolder = document.getElementById('ave-sizechart-holder');
                    container.appendChild(tableHolder);
                }
                $(holder).addClass('opened');
                return false;
            },
            hidePopup: function () {
                $('#ave-sizechart-popup-holder').removeClass('opened');
            },
            isNumber: function (n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            },
            setCookie: function (cname, cvalue, exdays) {
                var d = new Date();
                exdays = exdays || 365;
                d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                var expires = "expires=" + d.toUTCString();
                document.cookie = cname + "=" + cvalue + "; " + expires + "; path=/";
                this.userDimensions[cname] = cvalue;
            },
            getCookie: function (cname) {
                var name = cname + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) === ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) === 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            },
            isCurrentDimensionInch: function () {
                return '' === this.getCookie(this.inputSelectDimensionName) && this.currentDimension === this.codeInch
                    || this.getCookie(this.inputSelectDimensionName) === this.codeInch;
            },
            initBaseMeasurement: function () {
                if (this.isCurrentDimensionInch()) {
                    this.currentDimension = this.codeInch;
                    this.showSizes(this.codeInch);
                } else if (this.getCookie(this.inputSelectDimensionName) === this.codeCm) {
                    this.currentDimension = this.codeCm;
                }
            },
            initDimensionsByMember: function () {
                var memberSelectElement = $(ave_sizechart.activeMemberSelector),
                    memberId, measurements, dimensionId, value;
                if ((memberSelectElement != null) && (memberId = memberSelectElement.val())) {
                    measurements = ave_sizechart.membersMeasurements[memberId];
                    $(ave_sizechart.inputDimensionSelector).each(function (index, element) {
                        dimensionId = ave_sizechart.getDimensionIdentifier(element.id);
                        if (typeof measurements != 'undefined' && measurements.hasOwnProperty(dimensionId)) {
                            value = ave_sizechart.isCurrentDimensionInch()
                                  ? ave_sizechart.cmToInch(measurements[dimensionId], dimensionId)
                                  : measurements[dimensionId];
                            ave_sizechart.setCookie($(element).attr("name"), value);
                        } else {
                            ave_sizechart.setCookie($(element).attr("name"), '');
                        }
                    });
                }
            },
            initDimensionsFromCookie: function () {
                var dimensionValue;
                var elements = $(ave_sizechart.inputDimensionSelector);
                $.each(elements, function ( i, element ) {
                    dimensionValue = ave_sizechart.getCookie(element.name);
                    ave_sizechart.userDimensions[element.name] = dimensionValue;
                    if (dimensionValue !== "") {
                        $(element).val(dimensionValue);
                    }
                });
                var dimensionName = ave_sizechart.getCookie(ave_sizechart.inputSelectDimensionName);
                $(this.unitOfLengthBtnSelector).each(function (index, element) {
                    if (dimensionName !== '') {
                        $(element).removeClass('active');
                        if (dimensionName === $(element).data('code')) {
                            $('.ave-sizechart-dimensions').addClass('unit-' + dimensionName);
                            $(element).addClass('active');
                        }
                    }
                });
            },
            lbToKg: function (size) {
                var fraction, sizeValue, sizeLabel;
                sizeValue = (parseFloat(size) * this.kgInLb).toFixed(0);
                fraction = sizeValue % 1;
                sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                sizeLabel = sizeValue;
                return sizeLabel;
            },
            kgToLb: function (size) {
                var fraction, sizeValue, sizeLabel;
                sizeValue = (parseFloat(size) / this.kgInLb).toFixed(0);
                fraction = sizeValue % 1;
                sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                sizeLabel = sizeValue;// + 'lb';
                return sizeLabel;
            },
            getDimension: function (dimensionId) {
                dimensionId = 'dimension_' + this.getDimensionIdentifier(dimensionId);
                return this.dimensions[dimensionId];
            },
            cmToInch: function (size, dimensionId, onlyInch, onlyFeet) {
                if (size == 0) {
                    return 0;
                }
                var dimension = this.getDimension(dimensionId);
                if (dimension && this.weightType == dimension.lengthType) {
                    return this.kgToLb(size);
                } else if (onlyInch && dimension && this.heightType == dimension.lengthType) {
                    var fraction, sizeValue;
                    sizeValue = (parseFloat(size) % this.cmInFeet / this.cmInInch).toFixed(0);
                    fraction = sizeValue % 1;
                    sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                    return sizeValue;
                } else if (onlyFeet && dimension && this.heightType == dimension.lengthType) {
                    var ft;
                    ft = Math.floor(parseFloat(size) / this.cmInFeet);
                    return ft;
                } else if (dimension && (this.heightType == dimension.lengthType || this.lengthType == dimension.lengthType)) {
                    var fraction, sizeValue;
                    sizeValue = (parseFloat(size) / this.cmInInch).toFixed(0);
                    fraction = sizeValue % 1;
                    sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                    return sizeValue;
                }
                return size;
            },
            cmToFeet: function (size, dimensionId) {
                if (size == 0) {
                    return 0;
                }
                var dimension = this.getDimension(dimensionId);
                if (dimension && this.weightType == dimension.lengthType) {
                    return this.kgToLb(size);
                } else if (dimension && this.heightType == dimension.lengthType) {
                    var fraction, sizeValue, sizeLabel, ft;
                    ft = Math.floor(parseFloat(size) / this.cmInFeet);
                    sizeValue = (parseFloat(size) % this.cmInFeet / this.cmInInch).toFixed(0);
                    fraction = sizeValue % 1;
                    sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                    sizeLabel = sizeValue + '"';
                    if (ft > 0) {
                        sizeLabel = ft + '\' ' + sizeLabel;
                    }
                    return sizeLabel;
                } else if (dimension && this.lengthType == dimension.lengthType) {
                    var fraction, sizeValue, sizeLabel;
                    sizeValue = (parseFloat(size) / this.cmInInch).toFixed(0);
                    fraction = sizeValue % 1;
                    sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                    sizeLabel = sizeValue + '"';
                    return sizeLabel;
                }
                return size;
            },
            inchToCm: function (size, dimensionId) {
                if (size == 0) {
                    return 0;
                }
                var dimension = this.getDimension(dimensionId);
                if (dimension && this.weightType == dimension.lengthType) {
                    return this.lbToKg(size);
                } else if (dimension && (this.heightType == dimension.lengthType || this.lengthType == dimension.lengthType)) {
                    var fraction, sizeValue;
                    sizeValue = (parseFloat(size) * this.cmInInch).toFixed(0);
                    fraction = sizeValue % 1;
                    sizeValue = parseInt(sizeValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                    return sizeValue;
                }
                return size;
            },
            getAverageSize: function (size) {
                var averageValue, i, fraction, summarySize = 0;
                if (size.indexOf('-') != -1) {
                    averageValue = size.split('-');
                    for (i = 0; i < averageValue.length; i++) {
                        summarySize += parseFloat(averageValue[i]);
                    }
                    averageValue = (summarySize / i).toFixed(0);
                    fraction = averageValue % 1;
                    averageValue = parseInt(averageValue) + Math.round(fraction * this.accuracy) / this.accuracy;
                } else {
                    averageValue = parseFloat(size);
                }
                return averageValue;
            },
            getSize: function (size, dimensionId, measurement, isInFeet) {
                var sizeValue, i;
                if (size.indexOf('-') != -1) {
                    sizeValue = size.split('-');
                    for (i = 0; i < sizeValue.length; i++) {
                        if (measurement == this.codeInch) {
                            if (isInFeet) {
                                sizeValue[i] = this.cmToFeet(sizeValue[i], dimensionId);
                            } else {
                                sizeValue[i] = this.cmToInch(sizeValue[i], dimensionId);
                            }
                        } else {
                            sizeValue[i] = parseFloat(sizeValue[i]);
                        }
                    }
                    sizeValue = sizeValue.join('-');
                } else {
                    if (measurement == this.codeInch) {
                        if (isInFeet) {
                            sizeValue = this.cmToFeet(size, dimensionId);
                        } else {
                            sizeValue = this.cmToInch(size, dimensionId);
                        }
                    } else {
                        sizeValue = parseFloat(size);
                    }
                }
                return sizeValue;
            },
            getDimensionIdentifier: function (dimensionId) {
                dimensionId = dimensionId.split('on_');
                dimensionId = dimensionId[dimensionId.length - 1];
                dimensionId = dimensionId.split('-feet');
                return dimensionId[0];
            },
            showSizes: function (measurementCode) {
                var sizeValue, sizes, sizeId, sizeLabel;
                for (var dimensionId in this.userDimensions) {
                    if (this.userDimensions.hasOwnProperty(dimensionId) && dimensionId != 'undefined') {
                        if (this.inputSelectDimensionName == dimensionId) {
                            continue;
                        }
                        sizes = this.sizes[this.getDimensionIdentifier(dimensionId)];
                        for (sizeId in sizes) {
                            if (!sizes.hasOwnProperty(sizeId)) {
                                continue;
                            }
                            if (this.codeCm === measurementCode) {
                                sizeValue = this.getSize(sizes[sizeId], dimensionId);
                                sizeLabel = this.getSize(sizes[sizeId], dimensionId);
                            } else {
                                sizeValue = this.getSize(sizes[sizeId], dimensionId, measurementCode);
                                sizeLabel = this.getSize(sizes[sizeId], dimensionId, measurementCode, true);
                            }
                            var el = document.getElementById(sizeId);
                            el.dataset.label = sizeLabel;
                            el.dataset.value = sizeValue;
                            el.innerText = sizeLabel;
                            el.textContent = sizeLabel;
                        }
                    }
                }
            },
            changeMeasurement: function (ev) {
                var measurementCode = $(this).data('code');
                if ($(ev.currentTarget).hasClass('active')) {
                    return;
                }
                $(ave_sizechart.unitOfLengthBtnSelector).each(function (index, element) {
                    $(element).removeClass('active');
                    if (measurementCode == $(element).data('code')) {
                        $(element).addClass('active');
                    }
                });
                ave_sizechart.setCookie(ave_sizechart.inputSelectDimensionName, measurementCode);
                ave_sizechart.currentDimension = measurementCode;
                ave_sizechart.updateInputsByDimension(measurementCode);
                ave_sizechart.showSizes(measurementCode);
                ave_sizechart.highlightCellByDimensions();
                $('.ave-sizechart-dimensions').removeClass(ave_sizechart.unitClassCm);
                $('.ave-sizechart-dimensions').removeClass(ave_sizechart.unitClassInch);
                $('.ave-sizechart-dimensions').addClass('unit-' + measurementCode);
            },
            clearSelectedMatches: function () {
                var listMatchElements = [], listSubMatchElements = [], listMainMatchElements = [], i;
                for (i = 0; i < document.getElementsByClassName(ave_sizechart.mainMatchClass).length; i++) {
                    listMainMatchElements.push(document.getElementsByClassName(ave_sizechart.mainMatchClass)[i]);
                }
                for (i = 0; i < document.getElementsByClassName(ave_sizechart.matchClass).length; i++) {
                    listMatchElements.push(document.getElementsByClassName(ave_sizechart.matchClass)[i]);
                }
                for (i = 0; i < document.getElementsByClassName(ave_sizechart.subMatchClass).length; i++) {
                    listSubMatchElements.push(document.getElementsByClassName(ave_sizechart.subMatchClass)[i]);
                }
                for (i = 0; i < listMainMatchElements.length; i++) {
                    $(listMainMatchElements[i]).removeClass(ave_sizechart.mainMatchClass);
                }
                for (i = 0; i < listSubMatchElements.length; i++) {
                    $(listSubMatchElements[i]).removeClass(ave_sizechart.subMatchClass);
                }
                for (i = 0; i < listMatchElements.length; i++) {
                    $(listMatchElements[i]).removeClass(ave_sizechart.matchClass);
                }
                this.initDescriptionButton();
                $(this.currentSizeSelector).removeClass('out-of-range').html(this.yourSizeUndefinedLabel);
                $(this.startButtonLabelSelector).html(this.sizeChartButtonLabel);
                $(this.startButtonSelector).removeClass('action primary');
            },
            initDescriptionButton: function (visible) {
                var descriptionButton = $('.ave-sizechart-description .button');
                if (descriptionButton && descriptionButton.length > 0) {
                    descriptionButton.each(function (index, item) {
                        if (visible && visible != undefined) {
                            $(item).removeClass('hidden');
                        } else {
                            $(item).addClass('hidden');
                        }
                    });
                }
            },
            activateRecommendationProductSize: function (recSize) {
                recSize = recSize.trim().toLowerCase();
                var options = $('#configurable_swatch_size a.swatch-link');
                for (var i = 0; i < options.length; i++) {
                    if (options[i] && options[i].readAttribute('title') &&
                        recSize == options[i].readAttribute('title').toLowerCase()) {
                        options[i].click();
                        break;
                    }
                }
            },
            highlightCellByDimensions: function () {
                var dimensionUserValue, dimensionCurrentArray, sizeId, matchSizes;
                this.clearSelectedMatches();
                function getMainSize(sizeId)
                {
                    var mainSize = document.getElementById(sizeId).parentElement.getElementsByClassName('ave-main'),
                        recommendationSize = '';
                    if (mainSize.length > 0) {
                        $(mainSize[0]).addClass(ave_sizechart.mainMatchClass);
                        var yourSizeLabel = ave_sizechart.yourSizeLabel,
                            currentSize = '',
                            currentSizes = $('#ave-sizechart-holder .ave-main.' + ave_sizechart.mainMatchClass);
                        if (currentSizes.length == 1) {
                            currentSize += currentSizes[0].textContent;
                            recommendationSize = currentSizes[0].textContent;
                        } else if (currentSizes.length > 1) {
                            currentSize += currentSizes[0].textContent;
                            if (currentSizes[currentSizes.length - 1].textContent != currentSizes[0].textContent) {
                                currentSize += ' - ' + currentSizes[currentSizes.length - 1].textContent;
                                recommendationSize = currentSizes[currentSizes.length - 1].textContent;
                            }
                        }
                        $(ave_sizechart.currentSizeSelector).html(yourSizeLabel + ' ' + currentSize);
                        $(ave_sizechart.startButtonLabelSelector).html(yourSizeLabel + ' ' + currentSize);
                        ave_sizechart.initDescriptionButton(true);
                        ave_sizechart.setCookie(ave_sizechart.cookieNameCurrent, currentSize);
                    }
                    return recommendationSize;
                }
                var recommendationSize = [], mainSize = null;
                for (var dimensionId in this.userDimensions) {
                    if (this.userDimensions.hasOwnProperty(dimensionId) && dimensionId != 'undefined') {
                        if (this.inputSelectDimensionName == dimensionId) {
                            continue;
                        }
                        dimensionUserValue = this.userDimensions[dimensionId];
                        dimensionCurrentArray = this.sizes[this.getDimensionIdentifier(dimensionId)];
                        matchSizes = this.getBestMatchIds(dimensionCurrentArray, dimensionUserValue, dimensionId);
                        if (false === matchSizes) {                          //1 - didn't find any values
                        } else if (matchSizes.hasOwnProperty('id') && matchSizes.hasOwnProperty('value')) {        //2 - strict match
                            $('#' + matchSizes['id']).addClass(this.matchClass).parent().addClass(this.matchClass);
                            mainSize = getMainSize(matchSizes['id']);
                            if (mainSize) {
                                recommendationSize.push(mainSize);
                            }
                        } else if (matchSizes.hasOwnProperty('length') && (matchSizes.length == 1 || matchSizes.length == 2)) {
                                           //3 - find one value
                            for (sizeId in matchSizes) {
                                if (matchSizes.hasOwnProperty(sizeId) && sizeId !== 'length') {
                                    $('#' + sizeId).addClass(this.subMatchClass).parent().addClass(this.subMatchClass);
                                    mainSize = getMainSize(sizeId);
                                    if (mainSize) {
                                        recommendationSize.push(mainSize);
                                    }
                                }
                            }
                        }
                    }
                }
                if (!this.isImageAvailable) {
                    $(this.startButtonSelector).addClass('action primary');
                }
                if (recommendationSize.length > 0) {
                    this.activateRecommendationProductSize(recommendationSize[recommendationSize.length - 1]);
                    $(this.startButtonSelector).addClass('action primary');
                } else {
                    var sizeEntered = false;
                    $(this.inputDimensionSelector).each(function (index, element) {
                        if ($(element).val() != undefined && $(element).val().length > 0) {
                            sizeEntered = true;
                        }
                    });
                    if (sizeEntered) {
                        $(ave_sizechart.currentSizeSelector).addClass('out-of-range').html(this.yourSizeOutOfRangeLabel);
                    }
                }
                $(this.startButtonSelector).removeClass('hidden');
            },
            getBestMatchIds: function (sizes, userSize, dimensionId) {
                var defaultLeft = 0, left = defaultLeft, leftId, defaultRight = 100000, right = defaultRight, rightId, sizeValue,
                    resultSizes = [], dimensionTax = 0, dimensionTaxLast = 0, realSizes = {}, sizeLength = 0, sizeId,
                    userDimension = this.getCookie(this.inputSelectDimensionName), minSize, maxSize, sizeValues;
                for (sizeId in sizes) {
                    if (!sizes.hasOwnProperty(sizeId)) {
                        continue;
                    }
                    if (userDimension == this.codeInch || this.currentDimension == this.codeInch) {
                        sizeValue = this.getSize(sizes[sizeId], dimensionId, this.codeInch);
                    } else {
                        sizeValue = this.getSize(sizes[sizeId], dimensionId);
                    }
                    if (('' + sizeValue).indexOf('-') != -1) {
                        sizeValues = sizeValue.split('-');
                        minSize = parseFloat(sizeValues[0]);
                        maxSize = parseFloat(sizeValues[1]);
                        if (minSize <= userSize && maxSize >= userSize) {
                            return {id: sizeId, value: userSize};
                        } else if (minSize > userSize && minSize < right) {
                            right = minSize;
                            rightId = sizeId;
                        } else if (maxSize < userSize && maxSize > left) {
                            left = maxSize;
                            leftId = sizeId;
                        }
                        if (dimensionTaxLast != 0 && sizeValue != 0) {
                            dimensionTax += minSize - dimensionTaxLast;
                        }
                        dimensionTaxLast = minSize;
                    } else {
                        if (sizeValue == userSize) {
                            return {id: sizeId, value: userSize};
                        } else if (sizeValue > userSize && sizeValue < right) {
                            right = sizeValue;
                            rightId = sizeId;
                        } else if (sizeValue < userSize && sizeValue > left) {
                            left = sizeValue;
                            leftId = sizeId;
                        }
                        if (dimensionTaxLast != 0 && sizeValue != 0) {
                            dimensionTax += sizeValue - dimensionTaxLast;
                        }
                        dimensionTaxLast = sizeValue;
                    }
                    sizeLength++;
                }
                if (sizeLength > 0) {
                    dimensionTax = dimensionTax / sizeLength;
                }
                if (defaultLeft != left) {
                    resultSizes[leftId] = left;
                }
                if (defaultRight != right) {
                    resultSizes[rightId] = right;
                }
                //if entered value is overhead more than average step
                for (sizeId in resultSizes) {
                    if (!resultSizes.hasOwnProperty(sizeId)) {
                        continue;
                    }
                    if ((resultSizes[sizeId] < userSize && (resultSizes[sizeId] + dimensionTax) < userSize)    //right
                        || (resultSizes[sizeId] > userSize && (resultSizes[sizeId] - dimensionTax) > userSize)    //left
                    ) {
                        //do nothing
                    } else {
                        realSizes[sizeId] = resultSizes[sizeId];
                        if (!realSizes.hasOwnProperty('length')) {
                            realSizes['length'] = 0;
                        }
                        realSizes['length']++;
                    }
                }
                if (realSizes == {}) {
                    return false;
                }
                return realSizes;
            },
            changeMember: function () {
                var memberId = $(this).val(),
                    measurements = ave_sizechart.membersMeasurements[memberId];
                /* step 1: change values in dimension inputs */
                $(ave_sizechart.inputDimensionSelector).each(function (index, element) {
                    var dimensionId = element.id,
                        value = 0;
                    dimensionId = ave_sizechart.getDimensionIdentifier(dimensionId);
                    if (typeof measurements != 'undefined' && measurements.hasOwnProperty(dimensionId)) {
                        if (measurements[dimensionId]) {
                            value = ave_sizechart.isNumber(measurements[dimensionId]) ? parseFloat(measurements[dimensionId]) : 0;
                            value = ave_sizechart.isCurrentDimensionInch() ? ave_sizechart.cmToInch(value, dimensionId) : value;
                        }
                    }
                    ave_sizechart.noNeedSaveDimensionCount++;
                    element.value = value;
                    if ("createEvent" in document) {
                        var evt = document.createEvent("HTMLEvents");
                        evt.initEvent("change", false, true);
                        element.dispatchEvent(evt);
                    } else {
                        element.fireEvent("onchange");
                    }
                });
                /* step 2: set default member in db */
                $.ajax({
                    url: ave_sizechart.setActiveUrl,
                    method: 'post',
                    data: {'member_id': memberId}
                }).done(function () {
                    /*data = data.responseText.evalJSON();*/
                });
            },
            saveMemberDimension: function (id, value) {
                if (ave_sizechart.noNeedSaveDimensionCount != 0) {
                    return;
                }
                var memberSelectElement = $(ave_sizechart.activeMemberSelector);
                if ((memberSelectElement != null) && (memberSelectElement.val() > 0)) {
                    if (ave_sizechart.isCurrentDimensionInch()) {
                        value = ave_sizechart.inchToCm(value, id);
                    }
                    if (!ave_sizechart.membersMeasurements.hasOwnProperty(memberSelectElement.val())) {
                        ave_sizechart.membersMeasurements[memberSelectElement.val()] = [];
                    }
                    ave_sizechart.membersMeasurements[memberSelectElement.val()][id] = value;
                    $.ajax({
                        url: ave_sizechart.setDimensionUrl,
                        method: 'post',
                        data: {'dimension_id': id, 'value': value, 'member_id': memberSelectElement.val()}
                    }).done(function () {
                        /*data = data.responseText.evalJSON();*/
                    });
                }
            },
            changeUserDimension: function () {
                var value = $(this).val();
                var errorClass = 'ave-sizechart-error';
                if (ave_sizechart.isNumber(value)) {
                    ave_sizechart.setCookie($(this).attr("name"), value);
                    ave_sizechart.highlightCellByDimensions();
                    ave_sizechart.saveMemberDimension(ave_sizechart.getDimensionIdentifier(this.id), value);
                    $(this).removeClass(errorClass);
                } else {
                    $(this).addClass(errorClass);
                }
                if (ave_sizechart.noNeedSaveDimensionCount > 0) {
                    ave_sizechart.noNeedSaveDimensionCount--;
                }
            },
            updateInputsByDimension(measurementCode) {
                var elements = $(ave_sizechart.inputDimensionSelector),
                    val, id, onlyInch, onlyFeet;
                $.each(elements, function ( i, element ) {
                    val = $(element).val();
                    id = $(element).attr('id');
                    if (ave_sizechart.isCurrentDimensionInch()) {
                        onlyInch = $(this).parent().hasClass('feet-row') && !$(this).hasClass('feet');
                        onlyFeet = $(this).parent().hasClass('feet-row') && $(this).hasClass('feet');
                        if (!onlyInch && onlyFeet) {
                            val = $('#' + id.split('-feet')[0]).val();
                        }
                        val = ave_sizechart.cmToInch(val, id, onlyInch, onlyFeet);
                    } else {
                        onlyInch = $(this).parent().hasClass('feet-row') && !$(this).hasClass('feet');
                        onlyFeet = $(this).parent().hasClass('feet-row') && $(this).hasClass('feet');
                        if (onlyInch && !onlyFeet) {
                            var ft = $('#' + id + '-feet').val();
                            val = ft * ave_sizechart.inchInFeet + parseFloat(val);
                        }
                        if (!onlyFeet) {
                            val = ave_sizechart.inchToCm(val, id);
                        }
                    }
                    $(element).val(val);
                    ave_sizechart.setCookie($(element).attr("name"), val);
                });
            }
        });

    return $.sizechart.js;
});