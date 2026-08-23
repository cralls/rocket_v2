<?php

namespace Meetanshi\Alipay\Block\Payment;

use Magento\Payment\Block\ConfigurableInfo;

/**
 * Class Info
 * @package Meetanshi\Alipay\Block\Payment
 */
class Info extends ConfigurableInfo
{
    /**
     * @var string
     */
    protected $_template = 'Meetanshi_Alipay::info.phtml';

    /**
     * @param string $field
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel($field)
    {
        switch ($field) {
            case 'sign':
                return __('Sign');
            case 'trade_no':
                return __('Trade No');
            case 'sign_type':
                return __('Sign Type');
            case 'out_trade_no':
                return __('Out Trade No');
            case 'trade_status':
                return __('Trade Status');
            default:
                break;
        }
    }
}
