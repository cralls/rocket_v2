<?php

namespace Rocket\CustomOrder\Controller\Sport;

use Magento\Framework\Controller\ResultFactory;

class UnderwaterHockey extends \Magento\Framework\App\Action\Action
{
   
    /**
     * 
     * @return type
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }

}
