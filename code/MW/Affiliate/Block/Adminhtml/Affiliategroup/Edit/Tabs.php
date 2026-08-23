<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliategroup\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliategroup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Affiliate Group Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_member_detail',
            [
                'label' => __('General Information'),
                'title' => __('General Information'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliategroup\Edit\Tab\Form'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'form_group_program',
            [
                'label' => __('Programs'),
                'title' => __('Programs'),
                'url'   => $this->getUrl('*/*/program', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'form_group_member',
            [
                'label' => __('Members'),
                'title' => __('Members'),
                'url'   => $this->getUrl('*/*/member', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_beforeToHtml();
    }
}
