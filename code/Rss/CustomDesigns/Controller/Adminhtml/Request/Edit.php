<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Rss\CustomDesigns\Model\CustomDesignFactory;
use Rss\CustomDesigns\Model\ResourceModel\CustomDesign as ResourceModel;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';
    
    protected $factory;
    protected $resource;
    protected $registry;
    protected $pageFactory;
    
    public function __construct(
        Action\Context $context,
        CustomDesignFactory $factory,
        ResourceModel $resource,
        Registry $registry,
        PageFactory $pageFactory
        ) {
            parent::__construct($context);
            $this->factory     = $factory;
            $this->resource    = $resource;
            $this->registry    = $registry;
            $this->pageFactory = $pageFactory;
    }
    
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        
        $model = $this->factory->create();
        
        if ($id) {
            $this->resource->load($model, $id);
            if (!$model->getId()) {
                throw new LocalizedException(__('This request no longer exists.'));
            }
        }
        
        // Register model for the form
        $this->registry->register('rss_custom_design', $model);
        
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Rss_CustomDesigns::custom_designs');
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Custom Design Request') : __('New Custom Design Request')
            );
        
        return $resultPage;
    }
}
