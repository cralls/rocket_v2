define([
    'jquery',
    'mage/template',
    'mage/translate',
    'jquery/ui',
    'jquery/file-uploader'
], function($, mageTemplate, $t){
    'use strict';

    $.widget("magearray.magearrayFileUpload", {
        itemsCount: 0,
        itemIndex: 0,
        linkTitle: '',
        options: {
        },
        _create: function() {
			
            this.itemTemplate = mageTemplate(this.options.itemTemplate);
			var allowedFile = $(this.options.allowedFile).html();
			var maxFileSize = $(this.options.maxFileSize).html();
			var extArray = $.map(allowedFile.split(','), $.trim);
            this.linkTitle = $(this.options.addLink).html();
			var fileNo = 1;
			var typeError = [];
			var sizeError = [];
			if($('div.allowed-file-type')){
				jQuery('div.allowed-file-type').append($.mage.__('Allowed file types are  %1 .').replace('%1', extArray.join(", ")));
			}
			if($('div.allowed-file-size')){
				jQuery( 'div.allowed-file-size' ).append($.mage.__('Allowed maximum file size is  %1 mb.').replace('%1', maxFileSize));
			}
            this.element.fileupload({
				add: function(e, data) {
					var uploadErrors = [];
					
					var error = 0;
					var ext = data.files[0].name.split('.').pop().toLowerCase();
					jQuery(".error-msg").text("");
					if($.inArray(ext, extArray) == -1) {
						typeError.push(data.files[0].name);
						error = 1;
					}
					if(data.files[0].size > (maxFileSize*1024*1024)) {//2 MB
						sizeError.push(data.files[0].name);
						error = 1;
					}
					if(error == 0) {
						data.submit();
					}
					if(fileNo == data.originalFiles.length){
						fileNo = 1;
						if(typeError.length > 0){
							var files = typeError.join(",");
							uploadErrors.push($.mage.__('Not an accepted file type of %1 .').replace('%1', files));
							typeError = [];
						}
						if(sizeError.length > 0){
							var files = sizeError.join(",");
							uploadErrors.push($.mage.__('Filesize is too big of %1 .').replace('%1', files));
							sizeError = [];
						}
					}else{
						fileNo = fileNo + 1;
					}
					
					if(uploadErrors.length > 0) {
						jQuery('.error-msg').append(uploadErrors.join("\n"));
					}
				},
				start: function (e) {
					$('.fileupload-loader').show();
				},
                dataType: 'json',
                done: $.proxy(this.onUpload, this)
            });
            this._bind();
        },
        destroy: function() {
            this.element.fileupload('destroy');
            this._unbind();
        },
        _bind: function() {
            $(this.options.itemsContainer).on('click', this.options.removeLinks, $.proxy(this.onRemoveClick, this));
        },
        _unbind: function() {
            $(this.options.itemsContainer).off('click', this.options.removeLinks);
        },
        onUpload: function(e, data) {
			$('.fileupload-loader').hide();
            if (typeof data['result'] !== "undefined") {
                var result = data['result'];

                if (!result['error']) {
                    this.addItem(data['result']);
                } else {
                    this.showError(result['error']);
                }
            }
        },
        onRemoveClick: function(event) {
            var item = $(event.target).closest('li');
            if (item) {
                this.removeItem(item);
            }
            event.preventDefault();
        },
        addItem: function(data) {
			
            var templateData = {
                'index': this.itemIndex++,
                'file': data.file,
                'fileName': data.name,
                'fileSize': data.text_file_size,
                'currentDate': data.currentDate,
            };
            $(this.options.itemsContainer).append(this.itemTemplate(templateData));
            this.itemsCount++;
            this.switchLinkTitle();
            this.updateItemsContainerVisibility();
        },
        removeItem: function(item) {
            item.hide();
            item.find('[data-role=remove]').val(1);
            this.itemsCount--;
            this.switchLinkTitle();
            this.updateItemsContainerVisibility();
        },
        switchLinkTitle: function() {
            var addLink = $(this.options.addLink);
            if (this.itemsCount > 0) {
                addLink.html(addLink.data('switch-title'));
            } else {
                addLink.html(this.linkTitle);
            }
        },
        updateItemsContainerVisibility: function() {
            var itemsContainer = $(this.options.itemsContainer);
            if (this.itemsCount > 0) {
                itemsContainer.show();
            } else {
                itemsContainer.hide();
            }
        },
        showError: function(message) {
            $(this.options.errorContainer)
                .html(message)
                .fadeIn()
                .delay(1000)
                .fadeOut();
        }
    });

    return $.magearray.magearrayFileUpload;
});