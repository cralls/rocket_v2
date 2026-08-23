<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

use MW\Affiliate\Model\Transactiontype;

class Invitationaction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if (empty($row['status'])) {
            return '';
        }

        if ($row['commission_type'] == Transactiontype::BUY_PRODUCT) {
            $url = $this->getUrl('*/affiliateviewhistory/index', ['orderid' => $row['order_id']]);

            return '<a href="' . $url . '">' . __('View') . '</a>';
        }

        return '';
    }
}
