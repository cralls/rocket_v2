<?php

namespace MW\Affiliate\Controller\Website;

use MW\Affiliate\Model\Statuswebsite;

class Verify extends \MW\Affiliate\Controller\Website
{
    /**
     * Verify a website
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('id');

        if (!$websiteId) {
            $this->messageManager->addError(__('Website ID does not exist'));
            $this->_redirect('*/*');
        }

        $model = $this->_websitememberFactory->create()->load($websiteId);
        $domainName = $model->getDomainName();
        $htmlContent = @file_get_contents($domainName);
        $verifiedKey = $model->getVerifiedKey();

        $verifiedFileUrl  = $domainName . '/' . hash('md5', $domainName) . '.txt';
        $verifiedFileContent = @file_get_contents($verifiedFileUrl);

        // Check meta-tag is inserted to affiliate website OR verified-file is upload to affiliate website host?
        if (strpos($htmlContent, $verifiedKey) !== false || strcmp($verifiedFileContent, hash('md5', $domainName)) === 0) {
            $model->setId($websiteId)->setStatus(Statuswebsite::VERIFIED);
            $model->save();

            $this->messageManager->addSuccess(__('Your website has been verified successfully'));
        } else {
            $this->messageManager->addError(__('Verify unsuccessfully! Please try again'));
        }

        $this->_redirect('*/*');
    }
}
