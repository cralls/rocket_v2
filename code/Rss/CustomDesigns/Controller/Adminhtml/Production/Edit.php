<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Production;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Rss\CustomDesigns\Model\ProductionRequestFactory;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::production_requests';
    
    protected $resultPageFactory;
    protected $registry;
    protected $factory;
    
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        ProductionRequestFactory $factory
        ) {
            parent::__construct($context);
            $this->resultPageFactory = $resultPageFactory;
            $this->registry = $registry;
            $this->factory = $factory;
    }
    
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('entity_id');
        $model = $this->factory->create();
        
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(
                    __('This Production Request no longer exists.')
                    );
                return $this->_redirect('customdesigns/production/listing');
            }
        }
        
        $this->registry->register('rss_production_request', $model);
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Rss_CustomDesigns::production_requests');
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Production Request') : __('New Production Request')
            );
        
        return $resultPage;
    }
}
