<?php

namespace MW\Affiliate\Block\Adminhtml\Renderer;

class Storeview extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row['store_view'] == 0) {
            return __('All Store Views');
        }

        $result = '';
        $storeview = explode(',', $row['store_view']);
        $data = $this->_systemStore->getStoresStructure(false, $storeview);
        foreach ($data as $website) {
            $result .= $website['label'] . '<br/>';
            foreach ($website['children'] as $group) {
                $result .= str_repeat('&nbsp;', 3) . $group['label'] . '<br/>';
                foreach ($group['children'] as $store) {
                    $result .= str_repeat('&nbsp;', 6) . $store['label'] . '<br/>';
                }
            }
        }

        return $result;
    }
}
