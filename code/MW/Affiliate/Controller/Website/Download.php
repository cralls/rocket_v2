<?php

namespace MW\Affiliate\Controller\Website;

class Download extends \MW\Affiliate\Controller\Website
{
    /**
     * Download verification file
     */
    public function execute()
    {
        $websiteId = $this->getRequest()->getParam('id');

        if (!$websiteId) {
            $this->messageManager->addError(__('Website id is not exist'));
            $this->_redirect('*/*');
        }

        $hashDomainName = hash('md5', $this->_websitememberFactory->create()->load($websiteId)->getDomainName());
        $fileName = $hashDomainName . '.txt';
        $fileContent = $hashDomainName;

        header('Content-Description: File Transfer');
        header('Cache-Control:public');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName.'"');
        header('Content-Length: ' . strlen($fileContent));

        $this->getResponse()->setBody($fileContent);
        return;
    }
}
