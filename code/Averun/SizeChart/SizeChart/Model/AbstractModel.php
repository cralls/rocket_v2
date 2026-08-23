<?php
/**
 * AbstractModel
 *
 * @copyright Copyright © 2017 Averun. All rights reserved.
 * @author    dev@averun.com
 */

namespace Averun\SizeChart\Model;

use Magento\Framework\Model\AbstractModel as AbstractModelCore;
use Magento\Framework\DataObject\IdentityInterface;

class AbstractModel extends AbstractModelCore implements IdentityInterface
{

    /**
     * CMS page cache tag
     */
    const CACHE_TAG = '';

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Check for unique of identifier of block to selected store(s).
     *
     * @param String $object
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function getIsUniqueIdentifier(String $identifier, String $tableName)
    {
        $select = $this->getResource()->getConnection()->select()->reset()
            ->from(['c' => $this->getResource()->getTable($tableName)])
            ->where('c.identifier = ?', $identifier);
        if ($this->getResource()->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }

    protected function setUniqueIdentifier($tableName)
    {
        $data = $this->getData();
        if (empty($data['identifier'])) {
            $identifier = strtolower($data['store_id'] . '_' . $data['name']);
            $identifier = preg_replace('/[^A-Za-z0-9_\-]/', '_', $identifier);
            $identifier = preg_replace('/_+/', '_', $identifier);
            if ($this->getIsUniqueIdentifier($identifier, $tableName)) {
                $data['identifier'] = $identifier;
            } else {
                $i = 0;
                do {
                    $i++;
                    $data['identifier'] = $identifier . '_' . $i;
                } while (!$this->getIsUniqueIdentifier($identifier . '_' . $i, $tableName));
            }
        }
        $this->setData('identifier', $data['identifier']);
    }
}
