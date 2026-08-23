<?php

namespace MW\Affiliate\Controller\Index;

class Index extends \MW\Affiliate\Controller\Index
{
    public function execute()
    {
        $this->_forward('referralaccount');
    }
}
