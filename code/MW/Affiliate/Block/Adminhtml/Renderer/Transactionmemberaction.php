<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

use MW\Affiliate\Model\Transactiontype;

class Transactionmemberaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['history_id'])) {
            return '';
        }

        if ($row['commission_type'] == Transactiontype::BUY_PRODUCT) {
            $url = $this->getUrl(
                'affiliate/affiliatemember/edit',
                [
                    'id' => $this->getRequest()->getParam('id'),
                    'orderid' => $row->getOrderId()
                ]
            );

            return '<a href="' . $url . '">' . __('View') . '</a>';
        }

        return '';
    }
}
