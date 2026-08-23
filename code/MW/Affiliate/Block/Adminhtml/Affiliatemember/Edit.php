<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_controller = 'adminhtml_affiliatemember';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Affiliate'));
        $this->buttonList->remove('delete');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $ajaxCheckReferralCodeUrl = $this->getUrl('affiliate/affiliatemember/ajaxcheckreferralcode');
        $ajaxUrl = $this->getUrl('affiliate/affiliatemember/ajaxemail');

        $this->_formScripts[] = "
            require([
                'prototype',
                'mwEffects',
                'mwAutocompleter'
            ], function() {
                if ($('referral_code') != undefined) {
                    $('referral_code').observe('change', function() {
                        var memberId = $('customer_id').value,
                            referralCode = $('referral_code').value;

                        $('advice-validate-referral-code-referral_code').hide();

                        new Ajax.Request(\"$ajaxCheckReferralCodeUrl\", {
                            method: 'get',
                            parameters: {member_id: memberId, referral_code: referralCode},
                            onSuccess: function(transport) {
                                response  = transport.responseText;
                                if (response == '0') {
                                    $('advice-validate-referral-code-referral_code').show()
                                } else {
                                    $('advice-validate-referral-code-referral_code').hide()
                                }
                            }
                        });
                    });
                }

                document.observe('dom:loaded', function() {
                    if ($('referral_code') != undefined) {
                        error = '<label style=\"display:none\" id=\"advice-validate-referral-code-referral_code\" class=\"mage-error\" for=\"referral_code\">This referral code is not available</label>';
                        $('referral_code').insert({after: error});
                    }

                    (function() {
                        if ($('customer_email') != undefined) {
                            new Ajax.Autocompleter('customer_email', 'autocomplete_choices', '".$ajaxUrl."', {
                                'paramName': 'customer_email'
                            });
                        }
                    })();

                    if ($('auto_withdrawn').value == '1') {
                        $('withdrawn_level').up(1).show();
                    } else if ($('auto_withdrawn').value == '2') {
                        $('withdrawn_level').up(1).hide();
                    };

                    $('auto_withdrawn').observe('change', function() {
                        if ($('auto_withdrawn').value == '1') {
                            $('withdrawn_level').up(1).show();
                        } else if ($('auto_withdrawn').value == '2') {
                            $('withdrawn_level').up(1).hide();
                        };
                    });

                    if ($('payment_gateway').value == 'banktransfer') {
                        $('bank_name').up(1).show();
                        if (!$('bank_name').hasClassName('required-entry')) {
                            $('bank_name').addClassName('required-entry');
                        }

                        $('name_account').up(1).show();
                        if (!$('name_account').hasClassName('required-entry')) {
                            $('name_account').addClassName('required-entry');
                        }

                        $('bank_country').up(1).show();
                        if (!$('bank_country').hasClassName('required-entry')) {
                            $('bank_country').addClassName('required-entry');
                        }

                        $('swift_bic').up(1).show();
                        if (!$('swift_bic').hasClassName('required-entry')) {
                            $('swift_bic').addClassName('required-entry');
                        }

                        $('account_number').up(1).show();
                        if (!$('account_number').hasClassName('required-entry')) {
                            $('account_number').addClassName('required-entry');
                        }

                        $('re_account_number').up(1).show();
                        if (!$('re_account_number').hasClassName('required-entry')) {
                            $('re_account_number').addClassName('required-entry');
                        }

                        $('payment_email').up(1).hide();
                        if ($('payment_email').hasClassName('required-entry')) {
                            $('payment_email').removeClassName('required-entry');
                        }
                    } else if ($('payment_gateway').value == 'check') {
                        $('bank_name').up(1).hide();
                        if ($('bank_name').hasClassName('required-entry')) {
                            $('bank_name').removeClassName('required-entry');
                        }

                        $('name_account').up(1).hide();
                        if ($('name_account').hasClassName('required-entry')) {
                            $('name_account').removeClassName('required-entry');
                        }

                        $('bank_country').up(1).hide();
                        if ($('bank_country').hasClassName('required-entry')) {
                            $('bank_country').removeClassName('required-entry');
                        }

                        $('swift_bic').up(1).hide();
                        if ($('swift_bic').hasClassName('required-entry')) {
                            $('swift_bic').removeClassName('required-entry');
                        }

                        $('account_number').up(1).hide();
                        if ($('account_number').hasClassName('required-entry')) {
                            $('account_number').removeClassName('required-entry');
                        }

                        $('re_account_number').up(1).hide();
                        if ($('re_account_number').hasClassName('required-entry')) {
                            $('re_account_number').removeClassName('required-entry');
                        }

                        $('payment_email').up(1).hide();
                        if ($('payment_email').hasClassName('required-entry')) {
                            $('payment_email').removeClassName('required-entry');
                        }
                    } else {
                        $('bank_name').up(1).hide();
                        if ($('bank_name').hasClassName('required-entry')) {
                            $('bank_name').removeClassName('required-entry');
                        }

                        $('name_account').up(1).hide();
                        if ($('name_account').hasClassName('required-entry')) {
                            $('name_account').removeClassName('required-entry');
                        }

                        $('bank_country').up(1).hide();
                        if ($('bank_country').hasClassName('required-entry')) {
                            $('bank_country').removeClassName('required-entry');
                        }

                        $('swift_bic').up(1).hide();
                        if ($('swift_bic').hasClassName('required-entry')) {
                            $('swift_bic').removeClassName('required-entry');
                        }

                        $('account_number').up(1).hide();
                        if ($('account_number').hasClassName('required-entry')) {
                            $('account_number').removeClassName('required-entry');
                        }

                        $('re_account_number').up(1).hide();
                        if ($('re_account_number').hasClassName('required-entry')) {
                            $('re_account_number').removeClassName('required-entry');
                        }

                        $('payment_email').up(1).show();
                        if (!$('payment_email').hasClassName('required-entry')) {
                            $('payment_email').addClassName('required-entry');
                        }
                    };

                    $('payment_gateway').observe('change', function() {
                        if ($('payment_gateway').value == 'banktransfer') {
                            $('bank_name').up(1).show();
                            if (!$('bank_name').hasClassName('required-entry')) {
                                $('bank_name').addClassName('required-entry');
                            }

                            $('name_account').up(1).show();
                            if (!$('name_account').hasClassName('required-entry')) {
                                $('name_account').addClassName('required-entry');
                            }

                            $('bank_country').up(1).show();
                            if (!$('bank_country').hasClassName('required-entry')) {
                                $('bank_country').addClassName('required-entry');
                            }

                            $('swift_bic').up(1).show();
                            if (!$('swift_bic').hasClassName('required-entry')) {
                                $('swift_bic').addClassName('required-entry');
                            }

                            $('account_number').up(1).show();
                            if (!$('account_number').hasClassName('required-entry')) {
                                $('account_number').addClassName('required-entry');
                            }

                            $('re_account_number').up(1).show();
                            if (!$('re_account_number').hasClassName('required-entry')) {
                                $('re_account_number').addClassName('required-entry');
                            }

                            $('payment_email').up(1).hide();
                            if ($('payment_email').hasClassName('required-entry')) {
                                $('payment_email').removeClassName('required-entry');
                            }
                        } else if ($('payment_gateway').value == 'check') {
                            $('bank_name').up(1).hide();
                            if ($('bank_name').hasClassName('required-entry')) {
                                $('bank_name').removeClassName('required-entry');
                            }

                            $('name_account').up(1).hide();
                            if ($('name_account').hasClassName('required-entry')) {
                                $('name_account').removeClassName('required-entry');
                            }

                            $('bank_country').up(1).hide();
                            if ($('bank_country').hasClassName('required-entry')) {
                                $('bank_country').removeClassName('required-entry');
                            }

                            $('swift_bic').up(1).hide();
                            if ($('swift_bic').hasClassName('required-entry')) {
                                $('swift_bic').removeClassName('required-entry');
                            }

                            $('account_number').up(1).hide();
                            if ($('account_number').hasClassName('required-entry')) {
                                $('account_number').removeClassName('required-entry');
                            }

                            $('re_account_number').up(1).hide();
                            if ($('re_account_number').hasClassName('required-entry')) {
                                $('re_account_number').removeClassName('required-entry');
                            }

                            $('payment_email').up(1).hide();
                            if ($('payment_email').hasClassName('required-entry')) {
                                $('payment_email').removeClassName('required-entry');
                            }
                        } else {
                            $('bank_name').up(1).hide();
                            if ($('bank_name').hasClassName('required-entry')) {
                                $('bank_name').removeClassName('required-entry');
                            }

                            $('name_account').up(1).hide();
                            if ($('name_account').hasClassName('required-entry')) {
                                $('name_account').removeClassName('required-entry');
                            }

                            $('bank_country').up(1).hide();
                            if ($('bank_country').hasClassName('required-entry')) {
                                $('bank_country').removeClassName('required-entry');
                            }

                            $('swift_bic').up(1).hide();
                            if ($('swift_bic').hasClassName('required-entry')) {
                                $('swift_bic').removeClassName('required-entry');
                            }

                            $('account_number').up(1).hide();
                            if ($('account_number').hasClassName('required-entry')) {
                                $('account_number').removeClassName('required-entry');
                            }

                            $('re_account_number').up(1).hide();
                            if ($('re_account_number').hasClassName('required-entry')) {
                                $('re_account_number').removeClassName('required-entry');
                            }

                            $('payment_email').up(1).show();
                            if (!$('payment_email').hasClassName('required-entry')) {
                                $('payment_email').addClassName('required-entry');
                            }
                        };
                    });
                });
            });
        ";

        return parent::_prepareLayout();
    }
}
