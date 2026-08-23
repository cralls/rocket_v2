<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 04/12/2019
 * Time: 14:38
 */

namespace Magenest\Xero\Model\Config\Source;

use Magenest\Xero\Helper\Signature;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Block\System\Config\Form\Field;

class RandomState extends Field
{
    /**
     * create element for Access token field in store configuration
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _renderValue(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $randomState = $this->getRandomState();
        $element->setValue($randomState);
        $element->setDisabled('disabled');
        $element->setStyle('display: none;');
        return parent::_renderValue($element);
    }

    protected function getRandomState()
    {
        $websiteId = $this->getRequest()->getParam('website');
        if ($websiteId) {
            $state = 'website-'.$websiteId;
        } else {
            $state = 'default-0';
        }
        $state .= '-'.$this->mathRandom->getRandomString('10');

        return $state;
    }
}
