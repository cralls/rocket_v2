<?php

namespace MW\Affiliate\Block\Adminhtml\System\Config;

class Label2 extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '<div style="font-size:13px; margin-left:-205px; margin-top:5px; margin-bottom:5px;"><b><u>'
            . __('Customers')
            . '</u></b></div>';

        return $html;
    }
}
