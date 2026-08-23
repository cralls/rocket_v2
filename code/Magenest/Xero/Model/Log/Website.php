<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 08/01/2020
 * Time: 11:28
 */

namespace Magenest\Xero\Model\Log;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class Website implements ArrayInterface
{
    protected $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**@#+
     * constant
     */
    const DEFAULT_WEBSITE = '0';

    /**
     * Options array
     *
     * @var array
     */
    protected $_options = [
        self::DEFAULT_WEBSITE => 'Default Website',
    ];

    /**
     * Return options array
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach ($this->toArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $websites = $this->storeManager->getWebsites();
        foreach ($websites as $website) {
            $this->_options[$website->getId()] = $website->getName();
        }
        return $this->_options;
    }
}
