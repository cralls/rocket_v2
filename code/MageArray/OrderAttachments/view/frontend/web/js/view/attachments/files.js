define(
    [
        'jquery',
        'ko',
        'uiComponent',
		'jquery/file-uploader',
		'Magento_Checkout/js/model/full-screen-loader',
		'Magento_Ui/js/modal/modal',
		'mage/translate'

    ],
    function ($, ko, Component, jQfileUpload, fullScreenLoader, modal, $t) {
		'use strict';
		var checkoutConfig = window.checkoutConfig.attachments;
		var quoteData = checkoutConfig.fileData;
		
		var mediaPath = checkoutConfig.mediaPath;
		
		var removeUrl = checkoutConfig.removeUrl;
		var enabledModule = checkoutConfig.enabledModule;
		var allowedFile = checkoutConfig.allowedFile;
		var maxFileSize = checkoutConfig.maxFileSize;
		var desc = checkoutConfig.descBlock;
		var extArray = $.map(allowedFile.split(','), $.trim);
		if(enabledModule==1){
			var canvisible=true;
		}else{
			var canvisible=false;
		}
		
		var displayFileType = checkoutConfig.displayFileType;
		var displayMaxSize = checkoutConfig.displayMaxSize;
		ko.bindingHandlers.fileUpload = {
			
			init: function(element, valueAccessor) {
				var fileNo = 1;
				var typeError = [];
				var sizeError = [];
				if(quoteData){
					$.each(quoteData, function( index, value ) {
						var fileObj = $.parseJSON(value.file_data);
					
 					var fileUrl = mediaPath+fileObj['file'];
					var remUrl="'"+removeUrl + "id/" +value.id+"'"; 
					jQuery( 'div.filePreview' ).append( '<p class="attach_id_'+value.id+'"><a target="_blank" href="'+fileUrl+'">'+fileObj['name']+'</a><span class="remove-btn"><a href="" data-bind="click: removeAttachment.bind($data,'+value.id+') ">Remove</a></span></p>' ); 
					});
				} 
				if(displayFileType == 1){
					jQuery( 'div.allowed-file-type' ).append($.mage.__('Allowed file types are  %1 .').replace('%1', extArray.join(", ")));
				}
				if(displayMaxSize == 1){
					jQuery( 'div.allowed-file-size' ).append($.mage.__('Allowed maximum file size is  %1 mb.').replace('%1', maxFileSize));
				}
				$('#fileupload').fileupload({
					
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
						  fullScreenLoader.startLoader(); 
					},
					progressall: function (e, data) {
						var bar = $('.bar');
						var percent = $(".percent");
						$('.indicator').show();
						bar.css('width', '0%');
						var progress = parseInt(data.loaded / data.total * 100, 10); 
						bar.css('width', progress + "%");
						percent.html(progress + "%");
						 if(progress == 100){
							 setTimeout(function(){ 
								$(".indicator").hide();        
							 }, 1000); 
						 }
					},
					dataType: 'json',
					url: window.checkoutConfig.attachments.uploadUrl,
					done: function (e, data) {
						var fileUrl = mediaPath+data.result.file;
						var remUrl=removeUrl + "id/" +data.result.id
						var newElement = '<p class="attach_id_'+data.result.id+'"><a target="_blank" href="'+fileUrl+'">'+data.result.name+'</a><span class="remove-btn"><a href="javascript:void(0);" data-bind="click: removeAttachment.bind($data,'+data.result.id+') ">Remove</a></span></p>';
						jQuery( 'div.filePreview ' ).append(newElement);
						jQuery( 'div.filePreview .attach_id_'+data.result.id ).applyBindings();
						fullScreenLoader.stopLoader();  
					}
				});
			},
		};
		return Component.extend({
			defaults: {
				template: 'MageArray_OrderAttachments/attachments/files'
			},
			canVisibleFileUpload:canvisible,
			attachDesc:desc,
			openModel : function() {
					var self = this,
					modelClass = "cartDetails cartBox";

				var options =
				{
					type: 'popup',
					modalClass: modelClass,
					responsive: true,
					innerScroll: true,
					title: false,
					buttons: false
				};
				if (desc) {
					var popup = modal(options, $('#confirm_content'));
					jQuery('#confirm_content').html(desc);
					$('#confirm_content').modal('openModal');
				} 
			},
			removeAttachment : function(data) {
				var remUrl = removeUrl + "id/" +data; 
				fullScreenLoader.startLoader();
				$.ajax({
					url: remUrl,
					type: 'POST',
					dataType: 'json',
					success: function (result) {
						fullScreenLoader.stopLoader();
						if(result.status == 1){
							$(".attach_id_"+data).hide();
						}
					}
				});
			}
		});
    }
);
