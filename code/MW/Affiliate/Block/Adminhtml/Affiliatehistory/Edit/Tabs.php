<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatehistory\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatehistory_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Manage Importing'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_section',
            [
                'label' => __('General'),
                'title' => __('General'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliatehistory\Edit\Tab\Form'
                )->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}
