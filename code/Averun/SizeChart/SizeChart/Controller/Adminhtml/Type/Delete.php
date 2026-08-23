<?php
/**
 * Delete
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */
namespace Averun\SizeChart\Controller\Adminhtml\Type;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Averun\SizeChart\Model\TypeFactory;

class Delete extends Action
{
    /** @var typeFactory $objectFactory */
    protected $objectFactory;

    /**
     * @param Context $context
     * @param TypeFactory $objectFactory
     */
    public function __construct(
        Context $context,
        TypeFactory $objectFactory
    ) {
        $this->objectFactory = $objectFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Averun_SizeChart::type');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', null);

        try {
            $objectInstance = $this->objectFactory->create()->load($id);
            if ($objectInstance->getId()) {
                $objectInstance->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
            } else {
                $this->messageManager->addErrorMessage(__('Record does not exist.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        
        return $resultRedirect->setPath('*/*');
    }
}
