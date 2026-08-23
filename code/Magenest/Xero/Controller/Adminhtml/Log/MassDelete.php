<?php
namespace Magenest\Xero\Controller\Adminhtml\Log;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magenest\Xero\Model\ResourceModel\Log\CollectionFactory;
use Magenest\Xero\Model\LogFactory;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LogFactory $logFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LogFactory $logFactory
    ) {
        $this->logFactory = $logFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $logModel = $this->logFactory->create();
            $ids = [];
            $xmlIds = [];
            $count = 0;
            $affectedRows = 0;
            $lastItemId = $collection->getLastItem()->getId();
            $resource = $logModel->getResource();
            $connection = $resource->getConnection();
            foreach ($collection as $item) {
                $ids[] = $item->getId();
                if ($xmlId = $item->getXmlLogId()) {
                    $xmlIds[] = $xmlId;
                }
                $count++;
                if ($count >= 5000 || $item->getId() == $lastItemId) {
                    $idsString = implode(',', $ids);
                    $affectedRows += $connection->delete($resource->getMainTable(), 'id IN ('.$idsString.')');
                    if ($xmlIds) {
                        $xmlIdsString = implode(',', $xmlIds);
                        $connection->delete($resource->getTable('magenest_xero_xml_log'), 'id IN ('.$xmlIdsString.')');
                    }
                    $count = 0;
                    $ids = [];
                }
            }

            $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', $affectedRows));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Xero::log');
    }
}
