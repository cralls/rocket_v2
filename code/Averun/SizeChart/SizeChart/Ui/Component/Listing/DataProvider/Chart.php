<?php
/**
 * DataProvider
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Ui\Component\Listing\DataProvider;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataProvider;

class Chart extends UiDataProvider
{
    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $searchResult->setStoreId($this->request->getParam('store', 0))
            ->addAttributeToSelect(['is_active', 'name', 'image', 'category', 'dimension', 'type']); // Add here needed EAV attributes to display on grid

        return parent::searchResultToOutput($searchResult);
    }

    /**
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        $storeId = $this->request->getParam('store', 0);
        if ($storeId) {
            $this->data['config']['update_url'] = sprintf(
                '%s%s/%s',
                $this->data['config']['update_url'],
                'store',
                $storeId
            );
        }
        return parent::prepareUpdateUrl();
    }
}
