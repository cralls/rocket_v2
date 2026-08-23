<?php
namespace MageArray\OrderAttachments\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Attachments extends Template implements TabInterface
{
    protected $_template = 'order/view/tab/attachments.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null,
        \Magento\Framework\App\Request\Http $request,
        \MageArray\OrderAttachments\Helper\Data $helperData
    ) {
        parent::__construct($context, $data,$jsonHelper,$directoryHelper);
        $this->request = $request;
        $this->helperData = $helperData;
    }
    /**
     * @return mixed
     */
    public function getTabLabel()
    {
        $orderId = $this->request->getParam('order_id');
        $filesArr = $this->helperData->getAttachedFiles($orderId);
        return __('Order Attachments ('.count($filesArr).')');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Order Attachments');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl('orderattachments/index/upload');
    }
}
