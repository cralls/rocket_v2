<?php

namespace MW\Affiliate\Block\Adminhtml\System\Config;

class AuthorInformation extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $html = $this->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('MW_Affiliate::system/config/author.phtml')
            ->toHtml();

        return $html;
    }
}
