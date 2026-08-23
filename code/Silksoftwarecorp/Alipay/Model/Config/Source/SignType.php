<?php
/**
 * @author Silksoftware Team
 * @package Silksoftwarecorp_Alipay
 * @copyright Copyright (c) 2018 Silk Software Corp. (https://www.silksoftware.com)
 * @license  https://www.silksoftware.com/licenses/magento_extensions_license_1.0.txt | SILK Software Corp. | Extension License 1.0
 */
namespace Silksoftwarecorp\Alipay\Model\Config\Source;

class SignType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'RSA2',
                'label' => 'RSA2',
            ],
            [
                'value' => 'RSA',
                'label' => 'RSA'
            ]
        ];
    }
}
