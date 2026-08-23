require([
    'jquery',
	'jquery/ui',
	'jquery/validate',
	'mage/translate'
], function($) {
	$.validator.addMethod(
		'validate-re_account_number', function(value) {
		    var conf = $('#re_account_number');
            var pass = false;
            if ($('#account_number')) {
                pass = $('#account_number');
            }

            return (pass.va() == conf.val());
		},
		$.mage.__('Please make sure your account number match.')
	);
});
