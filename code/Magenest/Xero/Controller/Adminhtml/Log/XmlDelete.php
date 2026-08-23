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

use Magenest\Xero\Model\ResourceModel\XmlLog;
use Magento\Backend\App\Action;

class XmlDelete extends Action
{
    protected $xmlLogResource;

    public function __construct(
        Action\Context $context,
        XmlLog $xmlLogResource
    ) {
        $this->xmlLogResource = $xmlLogResource;
        parent::__construct($context);
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        $connection = $this->xmlLogResource->getConnection();

        $connection->delete($this->xmlLogResource->getMainTable());
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
