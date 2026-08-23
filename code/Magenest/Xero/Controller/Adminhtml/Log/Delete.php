<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 27/08/2020
 * Time: 12:02
 */

namespace Magenest\Xero\Controller\Adminhtml\Log;


use Magenest\Xero\Model\ResourceModel\Log;
use Magento\Backend\App\Action;

class Delete extends Action
{
    protected $logResource;

    public function __construct(
        Action\Context $context,
        Log $logResource
    ){
        $this->logResource = $logResource;
        parent::__construct($context);
    }

    public function execute()
    {
        $connection = $this->logResource->getConnection();

        $connection->delete($this->logResource->getMainTable());
        return $this->_redirect('*/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Xero::log');
    }
}