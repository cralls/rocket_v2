<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatememberpending;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'MW_Affiliate';
        $this->_controller = 'adminhtml_affiliatememberpending';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->add(
            'approve',
            [
                'label' => __('Approve'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/approve', ['id'=>$this->getRequest()->getParam('id')]) .'\')',
                'class' => 'add'
            ]
        );
    }
}
