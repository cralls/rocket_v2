<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Rmasystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Rmasystem\Api\Data;

/**
 * Interface for rma shipping label search results.
 * @api
 */
interface ShippinglabelSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get shipping label list.
     *
     * @return \Webkul\Rmasystem\Api\Data\ShippingLabelInterface[]
     */
    public function getItems();

    /**
     * Set shipping label list.
     *
     * @api
     * @param \Webkul\Rmasystem\Api\Data\ShippingLabelInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
