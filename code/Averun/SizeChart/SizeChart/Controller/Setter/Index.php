<?php
namespace Averun\SizeChart\Controller\Setter;

use Averun\SizeChart\Controller\Setter;
use Magento\Framework\Controller\Result\Forward;

class Index extends Setter
{
    /**
     * @return Forward
     */
    public function execute()
    {
        return $this->_redirect('sizechart/member_manage');
    }
}
