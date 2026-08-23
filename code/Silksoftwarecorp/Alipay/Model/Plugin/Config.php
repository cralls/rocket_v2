<?php

/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */

namespace Silksoftwarecorp\Alipay\Model\Plugin;

class Config
{
    /**
     * @var \Silksoftwarecorp\Alipay\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currency;

    public function __construct(
        \Silksoftwarecorp\Alipay\Helper\Data $helper,
        \Magento\Directory\Model\Currency $currency
    )
    {
        $this->helper = $helper;
        $this->currency = $currency;
    }


    public function beforeSave(\Magento\Config\Model\Config $subject){
        $sectionId = $subject->getSection();
        $groups = $subject->getGroups();
        if($sectionId != 'payment'){
            return;
        }

        if(!isset($groups['alipay']['fields']['active']['value']) || (isset($groups['alipay']['fields']['active']['value']) && $groups['alipay']['fields']['active']['value'] != 1)){
            return;
        }

        $allowCurrencies = $this->currency->getConfigAllowCurrencies();
        if(!in_array('CNY', $allowCurrencies)){
            throw new \Exception(__("Alipay require CNY currency rate. Please Select Chinese Yuan in Currency Setup > Allowed Currencies and set the currency rate."));
        }
        return;
    }
}
