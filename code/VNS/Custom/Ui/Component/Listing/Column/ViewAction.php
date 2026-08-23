<?php
namespace VNS\Custom\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ViewAction extends Column
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
        ) {
            $this->urlBuilder = $urlBuilder;
            parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['msg'])) {
                    $item[$fieldName]['view'] = [
                        'slideout' => [
                            'content' => $item['msg']
                        ],
                        'label' => __('View'),
                        'post' => true
                    ];
                }
            }
        }
        
        return $dataSource;
    }
    
    /**
     * Render the HTML for the view action column
     *
     * @param string $msgContent
     * @return string
     */
    private function renderViewActionHtml($msgContent)
    {
        $html = '<div class="slideout-container">';
        $html .= '<a href="#" class="view-action">View</a>';
        $html .= '<div class="slideout-content">' . $msgContent . '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get JavaScript Component Configuration
     *
     * @return array
     */
    public function getComponentConfiguration()
    {
        return [
            'dataType' => 'text',
            'component' => 'Magento_Ui/js/grid/columns/actions',
            'indexField' => $this->getData('name') . '_html',
            'name' => $this->getData('name'),
            'hideViewAction' => true,
            'hideDeleteAction' => true,
            'hideEditAction' => true,
            'sorting' => false,
            'resizeEnabled' => false,
            'resizeDefaultWidth' => 100,
            'resizeWidths' => [],
            'slideoutConfig' => [
                'component' => 'Magento_Ui/js/grid/columns/actions/slideout',
                'slideDuration' => 400,
                'hideSlideoutTimeout' => 2000,
                'slideoutContentSelector' => '.slideout-content'
            ],
            'configurableFilter' => false
        ];
    }
}
