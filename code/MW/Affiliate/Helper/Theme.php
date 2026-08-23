<?php

namespace MW\Affiliate\Helper;

class Theme extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \MW\RewardPoints\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \MW\Affiliate\Helper\Data $dataHelper
    ) {
        parent::__construct($context);
        $this->_layoutFactory = $layoutFactory;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * Display Referral Code Form on the Shopping cart page
     * @return html
     */
    public function getRefferalCodeBlockOnCart()
    {
        if ($this->_dataHelper->ModuleIsEnable('MW_Affiliate')) {
            return $this->_layoutFactory->create()->createBlock(
                'MW\Affiliate\Block\Cart\ReferalForm'
            )->setTemplate(
                'MW_Affiliate::cart/referal_form.phtml'
            )->toHtml();
        }
        return '';
    }

    /**
     * Display Credit block on the Shopping cart page
     * @return html
     */
    public function getCreditBlockOnCart()
    {
        if ($this->_dataHelper->ModuleIsEnable('MW_Affiliate')) {
            return $this->_layoutFactory->create()->createBlock(
                'MW\Affiliate\Block\Cart\CreditForm'
            )->setTemplate(
                'MW_Affiliate::cart/credit.phtml'
            )->toHtml();
        }
        return '';
    }
}
