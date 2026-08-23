require([
    'prototype'
], function() {
    document.observe('dom:loaded', function() {
		if ($('affiliate_money_affiliate_withdrawn_period') != undefined) {
			if ($('affiliate_money_affiliate_withdrawn_period').value == '1') {
				$('affiliate_money_affiliate_withdrawn_day').up(1).show();
				$('affiliate_money_affiliate_withdrawn_month').up(1).hide();
			} else if ($('affiliate_money_affiliate_withdrawn_period').value == '2') {
				$('affiliate_money_affiliate_withdrawn_day').up(1).hide();
				$('affiliate_money_affiliate_withdrawn_month').up(1).show();
			}

			$('affiliate_money_affiliate_withdrawn_period').observe('change', function() {
				if ($('affiliate_money_affiliate_withdrawn_period').value == '1') {
					$('affiliate_money_affiliate_withdrawn_day').up(1).show();
					$('affiliate_money_affiliate_withdrawn_month').up(1).hide();
				} else if ($('affiliate_money_affiliate_withdrawn_period').value == '2') {
					$('affiliate_money_affiliate_withdrawn_day').up(1).hide();
					$('affiliate_money_affiliate_withdrawn_month').up(1).show();
				}
			});
		}
    });
});
