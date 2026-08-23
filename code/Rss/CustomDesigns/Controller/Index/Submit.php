<?php
namespace Rss\CustomDesigns\Controller\Index;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Rss\CustomDesigns\Model\CustomDesignFactory;
use Magento\Framework\Controller\Result\RedirectFactory;


class Submit extends Action
{
protected $customDesignFactory;
protected $resultRedirectFactory;


public function __construct(
Context $context,
CustomDesignFactory $customDesignFactory,
RedirectFactory $resultRedirectFactory
) {
parent::__construct($context);
$this->customDesignFactory = $customDesignFactory;
$this->resultRedirectFactory = $resultRedirectFactory;
}


public function execute()
{
$data = $this->getRequest()->getPostValue();
if ($data) {
$model = $this->customDesignFactory->create();
$model->setData([
'customer_name' => $data['customer_name'] ?? '',
'email' => $data['email'] ?? '',
'design_description' => $data['design_description'] ?? ''
]);
$model->save();
$this->messageManager->addSuccessMessage(__('Thank you! Your design has been submitted.'));
}
$redirect = $this->resultRedirectFactory->create();
$redirect->setPath('*/*/');
return $redirect;
}
}