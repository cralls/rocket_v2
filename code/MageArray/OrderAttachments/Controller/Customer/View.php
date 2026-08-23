<?php

namespace MageArray\OrderAttachments\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class View extends Action
{
    protected $_customerSession;
    protected $_customerUrl;

    /**
     * View constructor.
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(Context $context, Session $customerSession, Url $customerUrl)
    {
        $this->_customerSession = $customerSession;
        $this->_customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_customerUrl->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('My Order Attachments'));
        $this->_view->renderLayout();
    }
}
