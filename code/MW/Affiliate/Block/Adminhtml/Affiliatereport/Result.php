<?php

namespace MW\Affiliate\Block\Adminhtml\Affiliatereport;

class Result extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_affiliatereport_result';
        $this->_headerText = __('Affiliate Sale Statistic by time range');
        $this->_blockGroup = 'MW_Affiliate';

        parent::_construct();

        $this->buttonList->remove('add');
    }
}
