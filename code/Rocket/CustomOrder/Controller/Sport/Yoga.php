<?php

namespace Rocket\CustomOrder\Controller\Sport;

use Magento\Framework\Controller\ResultFactory;

class Yoga extends \Magento\Framework\App\Action\Action
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
