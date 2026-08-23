<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliategroup;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_escaper = $context->getEscaper();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_controller = 'adminhtml_affiliategroup';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Group'));
        $this->buttonList->update('delete', 'label', __('Delete Group'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
    }

    /**
     * Retrieve text for header element
     *
     * @return string
     */
    public function getHeaderText()
    {
        $affiliateGroupData = $this->_coreRegistry->registry('affiliate_data_group');
        if ($affiliateGroupData && $affiliateGroupData->getId()) {
            return __("Edit Group '%1'", $this->_escaper->escapeHtml($affiliateGroupData->getGroupName()));
        } else {
            return __('Add Group');
        }
    }
}
