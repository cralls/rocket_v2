<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Rss\CustomDesigns\Model\CustomDesignFactory;
use Rss\CustomDesigns\Model\ResourceModel\CustomDesign as CustomDesignResource;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';
    
    protected $factory;
    protected $resource;
    
    public function __construct(
        Action\Context $context,
        CustomDesignFactory $factory,
        CustomDesignResource $resource
        ) {
            parent::__construct($context);
            $this->factory  = $factory;
            $this->resource = $resource;
    }
    
    public function execute()
    {
        $request = $this->getRequest();
        
        if (!$request->isPost()) {
            return $this->_redirect('customdesigns/index/index');
        }
        
        $postData = $request->getPostValue();
        
        if (empty($postData)) {
            $this->messageManager->addErrorMessage(__('No data to save.'));
            return $this->_redirect('customdesigns/index/index');
        }
        
        try {
            $entityId = isset($postData['entity_id']) ? (int)$postData['entity_id'] : null;
            
            $model = $this->factory->create();
            
            if ($entityId) {
                $this->resource->load($model, $entityId);
                if (!$model->getId()) {
                    throw new LocalizedException(__('This request no longer exists.'));
                }
            }
            
            /**
             * Explicit field whitelist
             */
            $data = [
                'request_type'        => $postData['request_type']        ?? null,
                'sales_person_name'   => $postData['sales_person_name']   ?? null,
                'sales_person_email'  => $postData['sales_person_email']  ?? null,
                'customer_name'       => $postData['customer_name']       ?? null,
                'priority'            => $postData['priority']            ?? null,
                'date_needed'         => !empty($postData['date_needed'])
                ? date('Y-m-d', strtotime($postData['date_needed']))
                : null,
                'sportswear_type'     => $postData['sportswear_type']     ?? null,
                'product_other'       => $postData['product_other']       ?? null,
                'level'               => $postData['level']               ?? null,
                'level_other'         => $postData['level_other']         ?? null,
                'logos_url'           => $postData['logos_url']           ?? null,
                'pantone_pref'        => $postData['pantone_pref']        ?? null,
                'design_style'        => $postData['design_style']        ?? null,
                'comments'            => $postData['comments']            ?? null,
            ];
            
            $model->addData($data);
            
            if (!$model->getId()) {
                $model->setData('created_at', date('Y-m-d H:i:s'));
            }
            
            $model->setData('updated_at', date('Y-m-d H:i:s'));
            
            $this->resource->save($model);
            
            $this->messageManager->addSuccessMessage(__('Custom design request saved.'));
            
            /**
             * Redirect logic
             */
            if ($request->getParam('back')) {
                return $this->_redirect(
                    'customdesigns/request/edit',
                    ['entity_id' => $model->getId()]
                    );
            }
            
            return $this->_redirect('customdesigns/index/index');
            
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('An error occurred while saving the request.')
                );
        }
        
        return $this->_redirect('customdesigns/index/index');
    }
}
