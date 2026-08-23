<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab;

use Magento\Framework\Stdlib\DateTime;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_backendSession = $context->getBackendSession();
        $this->_systemStore = $systemStore;
        $this->_storeManager = $context->getStoreManager();
        $this->_pricingHelper = $pricingHelper;
        $this->_programFactory = $programFactory;
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
            ['legend'=>__('Program Information')]
        );

        $fieldset->addField(
            'send_mail',
            'checkbox',
            [
                'label'     => __('Notify Affiliate of program changes via email'),
                'name'      => 'send_mail',
                'onclick'   => 'this.value = this.checked ? 1 : 0;'
            ]
        );
        $fieldset->addField(
            'program_name',
            'text',
            [
                'label'     => __('Program Name'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'program_name'
            ]
        );
        $fieldset->addField(
            'description',
            'textarea',
            [
                'label'     => __('General description of Affiliate Program'),
                'name'      => 'description',
                'required'  => true
            ]
        );

        // Store View
        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_view',
                'multiselect',
                [
                    'name'      => 'store_view[]',
                    'label'     => __('Store View'),
                    'title'     => __('Store View'),
                    'required'  => true,
                    'values'    => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
        } else {
            $fieldset->addField(
                'store_view',
                'hidden',
                [
                    'name'      => 'store_view[]',
                    'value'     => $this->_storeManager->getStore(true)->getId()
                ]
            );
        }

        $fieldset->addField(
            'program_position',
            'text',
            [
                'label'     => __('Program priority'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'program_position',
                'note'      => __('Important: Only 1 Affiliate program will take effect if purchased product belongs to 2 or more programs. Set program executing priority under Configuration-Manage Affiliate Commission and Customer Discount')
            ]
        );
        $fieldset->addField(
            'status_program',
            'select',
            [
                'label'     => __('Status'),
                'note'         => __('Enable and save this Affiliate program to activate.'),
                'name'      => 'status_program',
                'values'    => [
                    [
                        'value' => 1,
                        'label' => __('Enabled')
                    ],
                    [
                        'value' => 2,
                        'label' => __('Disabled')
                    ]
                ],
            ]
        );
        $fieldset->addField(
            'start_date',
            'date',
            [
                'label'     => __('Start Date'),
                'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                'date_format'    => DateTime::DATE_INTERNAL_FORMAT,
                'name'      => 'start_date'
            ]
        );
        $fieldset->addField(
            'end_date',
            'date',
            [
                'label'     => __('End Date'),
                'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                'date_format'    => DateTime::DATE_INTERNAL_FORMAT,
                'name'      => 'end_date'
            ]
        );
        $fieldset->addField(
            'total_members',
            'label',
            [
                'label'     => __('Total Members'),
                'name'      => 'total_members',
                'readonly'  => 'readonly'
            ]
        );
        $fieldset->addField(
            'total_commission_result',
            'label',
            [
                'label' => __('Total Commission')
            ]
        );

        $affiliateGroupData = $this->_backendSession->getAffiliateDataProgram();
        if ($affiliateGroupData) {
            $form->setValues($affiliateGroupData);
            $this->_backendSession->setAffiliateData(null);
        } else {
            $affiliateGroupData = $this->_coreRegistry->registry('affiliate_data_program');

            if ($affiliateGroupData) {
                $form->setValues($affiliateGroupData);

                $programId = $this->getRequest()->getParam('id');
                $program = $this->_programFactory->create()->load($programId);
                $totalCommission = $this->_pricingHelper->currency($program->getTotalCommission(), true, false);
                $form->getElement('total_commission_result')->setValue($totalCommission);
                $form->getElement('status_program')->setValue($program->getStatus());
            }
        }

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
        return __('Program Detail');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Program Detail');
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
