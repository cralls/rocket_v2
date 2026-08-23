<?php

namespace MW\Affiliate\Controller\Website;

use MW\Affiliate\Model\Statuswebsite;

class Save extends \MW\Affiliate\Controller\Website
{
    /**
     * Save the new website
     */
    public function execute()
    {
        $url = $this->getRequest()->getParam('website_domain');

        if ($this->_dataHelper->validateDomainUrl($url)) {

            // Check domain is registerd by another affiliate or not
            $isExisted = $this->_websitememberFactory->create()->getCollection()
                ->addFieldToFilter('domain_name', ['eq' => trim($url)]);
            if (count($isExisted) > 0) {
                $this->messageManager->addError(__('This domain is registered by another affiliate'));
                $this->_redirect('*/*');
                return;
            }

            $verifiedKey = $this->_dataHelper->getWebsiteVerificationKey(trim($url));
            $website = [
                'customer_id'     => (int) $this->_customerSession->getCustomer()->getId(),
                'domain_name'     => trim($url),
                'verified_key'  => $verifiedKey,
                'status'        => Statuswebsite::UNVERIFIED
            ];

            try {
                $model = $this->_websitememberFactory->create();
                $model->setData($website)->save();

                $this->messageManager->addSuccess(__('Your new website has been added successfully. To verify, please insert this verified key: %1 to your website header. Then click "Verify now" to verify.', htmlspecialchars($verifiedKey)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Invalid domain'));
        }

        $this->_redirect('*/*');
    }
}
