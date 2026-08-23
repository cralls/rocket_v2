<?php

namespace MW\Affiliate\Controller\Adminhtml\Affiliatemember;

use MW\Affiliate\Model\Statusactive;

class Ajaxemail extends \MW\Affiliate\Controller\Adminhtml\Affiliatemember
{
    /**
     * Autocomplete customer email from ajax
     *
     * @return null|string
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('isAjax')) {
            return null;
        }

        $search = $this->getRequest()->getParam('customer_email');
        $search = preg_replace('/[^a-z0-9 ]/si', '', $search);

        $ids = [];
        $existedAffiliates = $this->_affiliatecustomersFactory->create()
            ->getCollection()
            ->addFieldToFilter('active', ['in' => [Statusactive::ACTIVE, Statusactive::PENDING]]);
        foreach ($existedAffiliates as $affiliate) {
            $ids[] = $affiliate->getCustomerId();
        }

        $customers = $this->_customerFactory->create()
            ->getCollection()
            ->addFieldToFilter('email', ['like' => $search . '%']);

        if (count($ids)) {
            $customers->addFieldToFilter('entity_id', ['nin' => $ids]);
        }

        if (sizeof($customers)) {
            $html = [];
            foreach ($customers as $customer) {
                $html[] = '<li>' . $customer->getEmail() . '</li>';
            }
            $html = '<ul>' . implode('', $html) . '</ul>';

            $this->getResponse()->setBody($html);
        } else {
            $html = '<ul><li>No result found</li></ul>';
            $this->getResponse()->setBody($html);
        }
        return;
    }
}
