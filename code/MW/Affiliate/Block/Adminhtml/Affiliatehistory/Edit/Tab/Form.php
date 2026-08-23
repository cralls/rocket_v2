<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatehistory\Edit\Tab;

use MW\Affiliate\Model\Status;

class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
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
            'affiliatehistory_form',
            ['legend'=>__('Update Affiliate Transactions via CSV')]
        );

        $fieldset->addField(
            'status_update',
            'select',
            [
                'name'         => 'status_update',
                'label'     => __('Update Status'),
                'required'     => true,
                'values'    => Status::getOptionAction(),
            ]
        );
        $fieldset->addField(
            'filename',
            'file',
            [
                'name'      => 'filename',
                'label'     => __('Upload CSV File'),
                'required'  => true
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
        return __('Update Affiliate Transactions via CSV');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Update Affiliate Transactions via CSV');
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
