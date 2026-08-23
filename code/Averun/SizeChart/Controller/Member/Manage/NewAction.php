<?php
namespace Averun\SizeChart\Controller\Member\Manage;

use Averun\SizeChart\Controller\Member\Manage;

class NewAction extends Manage
{
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
