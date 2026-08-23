<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatebanner\Edit\Tab;

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
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_backendSession = $context->getBackendSession();
        $this->_systemStore = $systemStore;
        $this->_storeManager = $context->getStoreManager();
        $this->_groupFactory = $groupFactory;
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
            'affiliate_banner_form',
            ['legend'=>__('Banner Information')]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label'     => __('Status'),
                'name'      => 'status',
                'values'    => [
                    [
                        'value'     => 1,
                        'label'     => __('Enabled')
                    ],
                    [
                        'value'     => 2,
                        'label'     => __('Disabled')
                    ]
                ]
            ]
        );
        $fieldset->addField(
            'title_banner',
            'text',
            [
                'label'     => __('Title'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'title_banner'
            ]
        );
        $fieldset->addField(
            'group_id',
            'multiselect',
            [
                'label'     => __('Group Name'),
                'name'      => 'group_id[]',
                'required'  => true,
                'values'    => $this->_getGroupArray()
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
            'link_banner',
            'text',
            [
                'label'     => __('Banner Link'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'link_banner'
            ]
        );
        $fieldset->addField(
            'width',
            'text',
            [
                'label'     => __('Width'),
                'class'     => 'required-entry validate-digits',
                'required'  => true,
                'note'         => __('Unit: Pixel (37.8 pixels = 1 cm)'),
                'name'      => 'width'
            ]
        );
        $fieldset->addField(
            'height',
            'text',
            [
                'label'     => __('Height'),
                'class'     => 'required-entry validate-digits',
                'required'  => true,
                'note'         => __('Unit: Pixel (37.8 pixels = 1 cm)'),
                'name'      => 'height'
            ]
        );
        $fieldset->addField(
            'image_name',
            'image',
            [
                'label'     => __('Upload Image'),
                'required'  => false,
                'name'      => 'image_name'
            ]
        );

        $affiliateGroupData = $this->_backendSession->getInvitationData();
        if ($affiliateGroupData) {
            $form->setValues($affiliateGroupData);
            $this->_backendSession->setTestData(null);
        } else {
            $affiliateGroupData = $this->_coreRegistry->registry('affiliate_data_banner');

            if ($affiliateGroupData) {
                $form->setValues($affiliateGroupData->getData());
                $imageName = $affiliateGroupData->getImageName();

                if (isset($imageName) && $imageName != '') {
                    $form->getElement('image_name')->setValue($imageName);
                }
            }
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get all affiliate groups and push them to an array
     *
     * @return array
     */
    private function _getGroupArray()
    {
        $result = [];
        $groups = $this->_groupFactory->create()->getCollection();

        foreach ($groups as $group) {
            $result[] = [
                'label' => $group->getGroupName(),
                'value' => $group->getGroupId()
            ];
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
        return __('Banner Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Banner Information');
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
