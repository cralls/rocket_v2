<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Field;

class Custom extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    public function getElementHtml()
    {
        $html = '<div id="' . $this->getHtmlId() . '"' . $this->serialize($this->getHtmlAttributes()) . '></div>';
        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
