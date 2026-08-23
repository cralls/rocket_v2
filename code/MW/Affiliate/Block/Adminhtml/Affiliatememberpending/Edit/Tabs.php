<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatememberpending\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('affiliatememberpending_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Affiliate Pending'));
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
                'label' => __('General information'),
                'title' => __('General information'),
                'content' => $this->getLayout()->createBlock(
                    'MW\Affiliate\Block\Adminhtml\Affiliatememberpending\Edit\Tab\Form'
                )->toHtml(),
                'active' => true
            ]
        );

        return parent::_beforeToHtml();
    }
}
