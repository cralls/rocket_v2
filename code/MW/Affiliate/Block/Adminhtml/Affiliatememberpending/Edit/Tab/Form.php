<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatememberpending\Edit\Tab;

use MW\Affiliate\Model\Autowithdrawn;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        array $data = []
    ) {
        $this->_pricingHelper = $pricingHelper;
        $this->_customerFactory = $customerFactory;
        $this->_countryFactory = $countryFactory;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
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
            'affiliate_form',
            ['legend' => __('Affiliate Pending Information')]
        );

        $customerId = $this->getRequest()->getParam('id');
        $customer = $this->_customerFactory->create()->load($customerId);
        $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($customerId);

        $paymentGateway = $affiliateCustomer->getPaymentGateway();
        $paymentEmail = $affiliateCustomer->getPaymentEmail();
        if ($paymentGateway == 'banktransfer') {
            $paymentEmail = '';
        }

        $fieldset->addField(
            'website_name',
            'label',
            [
                'label' => __('Link Affiliate With'),
                'value' => $this->_storeManager->getWebsite($customer ->getWebsiteId())->getName()
            ]
        );
        $fieldset->addField(
            'referral_name',
            'note',
            [
                'label' => __('Pending Affiliate Name'),
                'text'  => $this->_dataHelper->getLinkCustomer($customerId, $customer->getName())
            ]
        );
        $fieldset->addField(
            'customer_email',
            'note',
            [
                'label' => __('Pending Affiliate Email'),
                'text'  => $this->_dataHelper->getLinkCustomer($customerId, $customer->getEmail())
            ]
        );
        $fieldset->addField(
            'payment_gateway',
            'label',
            [
                'label' => __('Payment Method'),
                'value' => $this->_dataHelper->getLabelPaymentGateway($paymentGateway)
            ]
        );

        if ($paymentGateway != 'banktransfer') {
            $fieldset->addField(
                'payment_email',
                'label',
                [
                    'label' => __('Withdrawal Notification Email'),
                    'name'  => 'payment_email',
                    'class' => 'validate-email',
                    'value' => $paymentEmail
                ]
            );
        }

        $fieldset->addField(
            'auto_withdrawn',
            'label',
            [
                'label' => __('Withdrawal Request Method '),
                'value' => Autowithdrawn::getLabel($affiliateCustomer->getAutoWithdrawn())
            ]
        );
        $fieldset->addField(
            'withdrawn_level',
            'label',
            [
                'label'     => __('Auto payment when account balance reaches'),
                'readonly'  => 'readonly',
                'value'     => $this->_pricingHelper->currency(round($affiliateCustomer->getWithdrawnLevel(), 0), true, false)
            ]
        );
        $fieldset->addField(
            'reserve_level',
            'label',
            [
                'label' => __('Reserve Level (to be kept in account)'),
                'class' => 'required-entry',
                'value' => $this->_pricingHelper->currency(round($affiliateCustomer->getReserveLevel(), 0), true, false)
            ]
        );

        if ($paymentGateway == 'banktransfer') {
            $fieldset->addField(
                'bank_name',
                'label',
                [
                    'label' => __('Bank Name'),
                    'name'  => 'bank_name',
                    'value' => $affiliateCustomer->getBankName()
                ]
            );
            $fieldset->addField(
                'name_account',
                'label',
                [
                    'label' => __('Name on account'),
                    'name'  => 'name_account',
                    'value' => $affiliateCustomer->getNameAccount()
                ]
            );
            $fieldset->addField(
                'bank_country',
                'label',
                [
                    'name'  => 'bank_country',
                    'label' => __('Bank Country'),
                    'value' => $this->_countryFactory->create()->load($affiliateCustomer->getBankCountry())->getName()
                ]
            );
            $fieldset->addField(
                'swift_bic',
                'label',
                [
                    'label'     => __('SWIFT code'),
                    'name'      => 'swift_bic',
                    'value'     => $affiliateCustomer->getSwiftBic()
                ]
            );
            $fieldset->addField(
                'account_number',
                'label',
                [
                    'label'     => __('Account Number'),
                    'name'      => 'account_number',
                    'value'     => $affiliateCustomer->getAccountNumber()
                ]
            );
        }

        $fieldset->addField(
            'referral_site',
            'textarea',
            [
                'label'     => __("Affiliate's Website(s)"),
                'name'      => 'referral_site',
                'readonly'  => true,
                'value'     => $affiliateCustomer->getReferralSite()
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
        return __('General information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('General information');
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
