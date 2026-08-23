<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatebanner\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliate_banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Banner Information'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_banner',
            [
                'label' => __('Banner Information'),
                'title' => __('Banner Information'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliatebanner\Edit\Tab\Form'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'form_banner_member',
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
