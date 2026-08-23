<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MageArray\OrderAttachments\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class Cmsblock implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $option = $this->collectionFactory->create()->toOptionArray();
            $element = ['value' => '0', 'label' => __('Select Static Block')];
            array_unshift($option, $element);
            $this->options = $option;
        }
        return $this->options;
    }
}
