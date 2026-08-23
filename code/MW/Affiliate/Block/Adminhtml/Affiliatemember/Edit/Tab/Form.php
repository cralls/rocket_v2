<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab;

use MW\Affiliate\Model\Autowithdrawn;
use MW\Affiliate\Model\Statusreferral;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_country;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Directory\Model\Config\Source\Country $country
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_country = $country;
        $this->_pricingHelper = $pricingHelper;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_groupFactory = $groupFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
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
            'affiliate_form',
            ['legend' => __('General Information')]
        );

        $customerId = $this->getRequest()->getParam('id');
        $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($customerId);
        $groupMembers      = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId);

        $groupId = 0;
        if (sizeof($groupMembers) > 0) {
            foreach ($groupMembers as $groupMember) {
                $groupId = $groupMember->getGroupId();
            }
        }

        $isNewObject = true;
        $affiliateData = $this->_coreRegistry->registry('affiliate_data_member');
        if ($affiliateData && $affiliateData->getId()) {
            $isNewObject = false;
        }

        if ($isNewObject) {
            $fieldset->addField(
                'customer_email',
                'text',
                [
                    'label'     => __('Affiliate Email'),
                    'required'  => true,
                    'name'        => 'customer_email',
                    'class'     => 'validate-email'
                ]
            );

            $fieldset->addType(
                'autocomplete_choices_field',
                'MW\Affiliate\Block\Adminhtml\Affiliatemember\Edit\Tab\Field\Custom'
            );

            $fieldset->addField(
                'autocomplete_choices',
                'autocomplete_choices_field',
                [
                    'name'  => 'autocomplete_choices',
                    'class' => 'autocomplete'
                ]
            );
        } else {
            $customer = $this->_customerFactory->create()->load($customerId);
            $fieldset->addField(
                'customer_id',
                'hidden',
                [
                    'name'  => 'customer_id',
                    'value' => $customerId
                ]
            );
            $fieldset->addField(
                'referral_name',
                'note',
                [
                    'label'     => __('Affiliate Name'),
                    'text'         => $this->_dataHelper->getLinkCustomer($customerId, $customer->getName())
                ]
            );
            $fieldset->addField(
                'customer_email_edit',
                'note',
                [
                    'label'     => __('Affiliate Email'),
                    'text'      => $this->_dataHelper->getLinkCustomer($customerId, $customer->getEmail())
                ]
            );
        }

        $fieldset->addField(
            'group_id',
            'select',
            [
                'label'     => __('Group Name'),
                'name'      => 'group_id',
                'required'  => true,
                'values'    => $this->__getGroupArray(),
                'value'     => $groupId
            ]
        );

        if (!$isNewObject) {
            $fieldset->addField(
                'referral_code',
                'text',
                [
                    'label'     => __('Referral Code (manual)'),
                    'name'        => 'referral_code',
                    'required'    => true,
                    'class'        => 'validate-referral-code',
                    'value'     => $affiliateCustomer->getReferralCode()
                ]
            );
            $fieldset->addField(
                'check_referral_code',
                'hidden',
                [
                    'value' => '0'
                ]
            );
        }

        $fieldset->addField(
            'affiliate_status',
            'select',
            [
                'label'     => __('Status'),
                'name'      => 'affiliate_status',
                'values'    => Statusreferral::getOptionArray(),
                'value'     => $affiliateCustomer->getStatus()
            ]
        );
        $fieldset->addField(
            'affiliate_parent',
            'text',
            [
                'label'     => __('Affiliate Parent Email'),
                'class'     => 'validate-email',
                'name'      => 'affiliate_parent',
                'value'     => $this->_customerFactory->create()->load($affiliateCustomer->getCustomerInvited())->getEmail()
            ]
        );
        $fieldset->addField(
            'payment_gateway',
            'select',
            [
                'label'     => __('Payment Method'),
                'name'      => 'payment_gateway',
                'values'    => $this->_dataHelper->_getPaymentGatewayArray(),
                'value'     => $affiliateCustomer->getPaymentGateway()
            ]
        );
        $fieldset->addField(
            'payment_email',
            'text',
            [
                'label'     => __('Withdrawal Notice Email'),
                'name'      => 'payment_email',
                'required'  => true,
                'class'        => 'validate-email',
                'value'     => ($affiliateCustomer->getPaymentGateway() == 'banktransfer') ? '' : $affiliateCustomer->getPaymentEmail()
            ]
        );
        $fieldset->addField(
            'auto_withdrawn',
            'select',
            [
                'label'     => __('Withdrawal Method'),
                'name'      => 'auto_withdrawn',
                'values'    => Autowithdrawn::getOptionArray(),
                'value'     => $affiliateCustomer->getAutoWithdrawn()
            ]
        );
        $fieldset->addField(
            'withdrawn_level',
            'text',
            [
                'label'     => __('Auto payment when account balance reaches'),
                'name'         => 'withdrawn_level',
                'class'        => 'required-entry validate-digits',
                'value'     => round($affiliateCustomer->getWithdrawnLevel(), 0),
                'note'         => __('Note: Over reserve level')
            ]
        );
        $fieldset->addField(
            'reserve_level',
            'text',
            [
                'label'     => __('Reserve Level (to be kept in account)'),
                'name'      => 'reserve_level',
                'class'     => 'validate-digits',
                'value'     => round($affiliateCustomer->getReserveLevel(), 0)
            ]
        );
        $fieldset->addField(
            'bank_name',
            'text',
            [
                'label'     => __('Bank Name'),
                'name'      => 'bank_name',
                'required'  => true,
                'value'     => $affiliateCustomer->getBankName()
            ]
        );
        $fieldset->addField(
            'name_account',
            'text',
            [
                'label'     => __('Name on account'),
                'name'      => 'name_account',
                'required'  => true,
                'value'     => $affiliateCustomer->getNameAccount()
            ]
        );
        $fieldset->addField(
            'bank_country',
            'select',
            [
                'name'  => 'bank_country',
                'required'  => true,
                'label'     => __('Bank Country'),
                'values'    => $this->_country->toOptionArray(),
                'value'     => $affiliateCustomer->getBankCountry()
            ]
        );
        $fieldset->addField(
            'swift_bic',
            'text',
            [
                'label'     => __('SWIFT code'),
                'name'      => 'swift_bic',
                'required'  => true,
                'value'     => $affiliateCustomer->getSwiftBic()
            ]
        );
        $fieldset->addField(
            'account_number',
            'text',
            [
                'label'     => __('Account Number'),
                'name'      => 'account_number',
                'required'  => true,
                'value'     => $affiliateCustomer->getAccountNumber()
            ]
        );
        $fieldset->addField(
            're_account_number',
            'text',
            [
                'label'     => __('Re Account Number'),
                'name'      => 're_account_number',
                'class'     => 'validate-re_account_number',
                'required'  => true,
                'value'     => $affiliateCustomer->getReAccountNumber()
            ]
        );

        if ($isNewObject) {
            $fieldset->addField(
                'referral_site',
                'textarea',
                [
                    'label'     => __('Affiliate Website(s)'),
                    'name'      => 'referral_site',
                    'value'     => $affiliateCustomer->getReferralSite(),
                    'note'      => __('Separate with commas (,)')
                ]
            );
        } else {
            $fieldset->addField(
                'total_commission',
                'label',
                [
                    'label'     => __('Total Credit/Adjustments'),
                    'name'      => 'total_commission',
                    'readonly'  => 'readonly',
                    'value'     => $this->_pricingHelper->currency($affiliateCustomer->getTotalCommission(), true, false)
                ]
            );
            $fieldset->addField(
                'total_paid',
                'label',
                [
                    'label'     => __('Total Paid Out/Credits Used'),
                    'name'      => 'total_paid',
                    'readonly'  => 'readonly',
                    'value'     => $this->_pricingHelper->currency($affiliateCustomer->getTotalPaid(), true, false)
                ]
            );

            $creditCustomer = $this->_creditcustomerFactory->create()->load($customerId);
            $fieldset->addField(
                'balance',
                'label',
                [
                    'label'     => __('Current Balance'),
                    'name'      => 'balance',
                    'readonly'  => 'readonly',
                    'value'     => $this->_pricingHelper->currency($creditCustomer->getCredit(), true, false)
                ]
            );
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }


    /**
     * @return array
     */
    private function __getGroupArray()
    {
        $result = [];
        $result[''] = __('Please select a group');

        $groups = $this->_groupFactory->create()->getCollection();
        foreach ($groups as $group) {
            $result[$group->getGroupId()] = $group->getGroupName();
        }

        return $result;
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
