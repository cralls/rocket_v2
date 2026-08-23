<?php

namespace MW\Affiliate\Block\Adminhtml;

class Affiliatehistory extends \Magento\Backend\Block\Widget\Grid\Container
{
    public function _construct()
    {
        $this->_controller = 'adminhtml_affiliatehistory';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_headerText = __('Commission History');

        parent::_construct();

        $this->buttonList->remove('add');
        $this->buttonList->add(
            'import',
            [
                'label' => __('Update Affiliate Transactions via CSV'),
                'class' => 'add',
                'onclick' => 'setLocation("' . $this->getUrl('*/*/import') .'")',
            ],
            -100
        );
    }
}
