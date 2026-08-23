<?php
namespace Averun\SizeChart\Model\ResourceModel\Size;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_joinedFields = [];

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author avemagento
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\Size', 'Averun\SizeChart\Model\ResourceModel\Size');
    }

    /**
     * get sizes as array
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     * @author avemagento
     */
    protected function _toOptionArray($valueField = 'entity_id', $labelField = 'name', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    /**
     * get options hash
     *
     * @access protected
     * @param string $valueField
     * @param string $labelField
     * @return array
     * @author avemagento
     */
    protected function _toOptionHash($valueField = 'entity_id', $labelField = 'name')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @access public
     * @return \Magento\Framework\Db\Select
     * @author avemagento
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
}
