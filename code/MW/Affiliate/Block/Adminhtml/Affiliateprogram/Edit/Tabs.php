<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_program_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Affiliate Program Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_program_detail',
            [
                'label' => __('Program Detail'),
                'title' => __('Program Detail'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab\Form'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'form_conditions',
            [
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab\Conditions'
                )->toHtml()
            ]
        );
        $this->addTab(
            'form_actions',
            [
                'label' => __('Affiliate Commission/Customer Discount'),
                'title' => __('Affiliate Commission/Customer Discount'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab\Actions'
                )->toHtml()
            ]
        );
        $this->addTab(
            'form_program_group',
            [
                'label' => __('Add Group'),
                'title' => __('Add Group'),
                'url'   => $this->getUrl('*/*/group', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'form_program_transaction',
            [
                'label' => __('Program Transactions'),
                'title' => __('Program Transactions'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliateprogram\Edit\Tab\Transaction'
                )->toHtml()
            ]
        );

        return parent::_beforeToHtml();
    }
}
