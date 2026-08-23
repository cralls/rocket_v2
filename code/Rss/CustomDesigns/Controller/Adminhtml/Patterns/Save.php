<?php
namespace Rss\CustomDesigns\Controller\Adminhtml\Patterns;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Rss_CustomDesigns::custom_designs';
    
    protected $jsonFactory;
    protected $resource;
    
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        ResourceConnection $resource
        ) {
            parent::__construct($context);
            $this->jsonFactory = $jsonFactory;
            $this->resource = $resource;
    }
    
    public function execute()
    {
        $request = $this->getRequest();
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('rss_custom_designs_patterns');
        
        if ($request->isXmlHttpRequest()) {
            $result = $this->jsonFactory->create();
            $items = $request->getParam('items', []);
            
            if (!count($items)) {
                return $result->setData([
                    'error' => true,
                    'message' => __('No data to save.')
                ]);
            }
            
            try {
                foreach ($items as $patternId => $data) {
                    unset($data['pattern_id']);
                    unset($data['updated_at']);
                    unset($data['created_at']);
                    
                    $connection->update(
                        $table,
                        $data,
                        ['pattern_id = ?' => $patternId]
                        );
                }
                
                return $result->setData([
                    'error' => false,
                    'message' => __('Saved successfully.')
                ]);
            } catch (\Exception $e) {
                return $result->setData([
                    'error' => true,
                    'message' => $e->getMessage()
                ]);
            }
        }
        
        $data = [
            'pattern_number'    => (string)$request->getParam('pattern_number'),
            'version'           => (string)$request->getParam('version'),
            'description'       => (string)$request->getParam('description'),
            'art_templates_url' => (string)$request->getParam('art_templates_url'),
            'http_address'      => (string)$request->getParam('http_address'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];
        
        try {
            $connection->insert($table, $data);
            $this->messageManager->addSuccessMessage(__('Pattern saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        
        return $this->_redirect('customdesigns/patterns/index');
    }
}