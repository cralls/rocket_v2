<?php

namespace Magenest\Xero\Controller\Adminhtml\Contact;

use Magento\Backend\App\Action\Context;
use Magenest\Xero\Model\XeroContactCron;
use Magento\Framework\Controller\ResultFactory;

class GetXeroContactList extends \Magento\Backend\App\Action
{
    /**
     * @var XeroContactCron
     */
    protected $xeroContact;

    /**
     * GetXeroContactList constructor.
     * @param Context $context
     * @param XeroContactCron $xeroContact
     */
    public function __construct(Context $context, XeroContactCron $xeroContact)
    {
        $this->xeroContact = $xeroContact;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($this->xeroContact->writeContacts()) {
            $response = ['message' => __('All contacts have been saved')];

        } else {
            $response = ['message' => __('No contact has been saved')];
        }
        $result->setData($response);
        return $result;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Xero::config_xero');
    }
}
