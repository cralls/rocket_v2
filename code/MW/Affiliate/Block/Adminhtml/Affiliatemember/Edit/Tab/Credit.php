<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

class Credit extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        array $data = []
    ) {
        $this->_pricingHelper = $pricingHelper;
        $this->_creditcustomerFactory = $creditcustomerFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'affiliate_form_credit',
            ['legend' => __('Credit Information')]
        );

        $customerId = $this->getRequest()->getParam('id');
        $creditCustomer = $this->_creditcustomerFactory->create()->load($customerId);

        $fieldset->addField(
            'credit',
            'label',
            [
                'label' => __('Current balance'),
                'name'  => 'credit',
                'value' => $this->_pricingHelper->currency($creditCustomer->getCredit(), true, false)
            ]
        );
        $fieldset->addField(
            'amount_credit',
            'text',
            [
                'label' => __('Manual Adjustment'),
                'name'  => 'amount_credit',
                'note' => __('Amount of Credit which you want to add or subtract to Affiliate Account. Ex. 50, -50.'),
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'payout_credit',
            'text',
            [
                'label' => __('Manual Payout'),
                'name'  => 'payout_credit',
                'note' => __('Amount of Credit which you want to pay. Ex. 50'),
                'class' => 'validate-number'
            ]
        );
        $fieldset->addField(
            'comment',
            'textarea',
            [
                'label' => __('Accompanying Comment'),
                'name'  => 'comment'
            ]
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Manual Adjustment/Payout');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Manual Adjustment/Payout');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
