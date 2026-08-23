<?php

namespace MW\Affiliate\Block\Adminhtml\System\Config;

class Paymentgateway extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var StatusField
     */
    protected $_statusRenderer;



    /**
     * Retrieve group column renderer
     *
     * @return Status
     */
    protected function _getStatusRenderer()
    {
        if (!$this->_statusRenderer) {
            $this->_statusRenderer = $this->getLayout()->createBlock(
                'MW\Affiliate\Block\Adminhtml\Form\Field\Status',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_statusRenderer;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'gateway_value',
            [
                'label' => __('Payment Method Code'),
                'style' => 'width:120px'
            ]
        );
        $this->addColumn(
            'gateway_title',
            [
                'label' => __('Payment Method Title'),
                'style' => 'width:200px'
            ]
        );
        $this->addColumn(
            'gateway_fee',
            [
                'label' => __('Payment Processing Fee'),
                'style' => 'width:100px'
            ]
        );
        $this->addColumn(
            'mw_status',
            [
                'label' => __('Enable Frontend'),
                'style' => 'width:100px',
                'renderer' => $this->_getStatusRenderer()
            ]
        );

        $this->_addAfter = false;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getStatusRenderer()->calcOptionHash($row->getData('mw_status'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
