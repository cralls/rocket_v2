<?php

/**
 * Dimension.php
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Dimension extends AbstractModel implements IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'averun_sizechart_dimension';

    /**
     * @var string
     */
    protected $_cacheTag = 'averun_sizechart_dimension';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'averun_sizechart_dimension';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\ResourceModel\Dimension');
    }

    /**
     * Save from collection data
     *
     * @param array $data
     * @return $this|bool
     */
    public function saveCollection(array $data)
    {
        if (isset($data[$this->getId()])) {
            $this->addData($data[$this->getId()]);
            $this->getResource()->save($this);
        }
        return $this;
    }

    public function beforeSave()
    {
        if (!$this->getId()) {
            $this->setUniqueIdentifier(EntityTypeInterface::DIMENSION_CODE . '_entity');
        }
        return parent::beforeSave();
    }
}
