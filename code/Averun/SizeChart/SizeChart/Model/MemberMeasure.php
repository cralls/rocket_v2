<?php
namespace Averun\SizeChart\Model;

class MemberMeasure extends \Magento\Framework\Model\AbstractModel
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('Averun\SizeChart\Model\ResourceModel\MemberMeasure');
    }

    public function loadByFields($bindFields)
    {
        $this->getResource()->loadByFields($this, $bindFields);
        return $this;
    }
}
