<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliategroup\Edit\Tab;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_backendSession = $context->getBackendSession();
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
            ['legend'=>__('Group Information')]
        );

        $fieldset->addField(
            'group_name',
            'text',
            [
                'name'         => 'group_name',
                'label'     => __('Group Name'),
                'required'     => true
            ]
        );
        $fieldset->addField(
            'limit_day',
            'text',
            [
                'name'      => 'limit_day',
                'label'     => __('Maximum number of days affiliate will earn commission from new registered customer (referral) and new referred customer will receive discount'),
                'required'  => true,
                'note'      => __('For unregistered customers go to Affiliate Pro - Configuration-General-Referred and Unregistered Customer...'),
                'class' => 'validate-digits'
            ]
        );
        $fieldset->addField(
            'limit_order',
            'text',
            [
                'name'         => 'limit_order',
                'label'     => __('Maximum number of orders affiliate will earn commission from new referral and new referral will receive discount'),
                'required'     => true,
                'note'      => __('Insert 0 if no limitation.'),
                'class'     => 'validate-digits'
            ]
        );
        $fieldset->addField(
            'limit_commission',
            'text',
            [
                'name'         => 'limit_commission',
                'label'     => __('Maximum commission affiliate can earn from each referral'),
                'required'     => true,
                'note'      => __('Insert 0 if no limitation.'),
                'class'     => 'validate-digits'
            ]
        );

        $affiliateGroupData = $this->_backendSession->getAffiliateDataGroup();
        if ($affiliateGroupData) {
            $form->setValues($affiliateGroupData);
            $this->_backendSession->setAffiliateData(null);
        } else {
            $affiliateGroupData = $this->_coreRegistry->registry('affiliate_data_group');
            if ($affiliateGroupData) {
                $form->setValues($affiliateGroupData->getData());
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
        return __('Group Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Group Information');
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
