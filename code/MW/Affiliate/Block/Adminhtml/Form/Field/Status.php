<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MW\Affiliate\Block\Adminhtml\Form\Field;

/**
 * Class Status
 * @package MW\Affiliate\Block\Adminhtml\Form\Field
 */
class Status extends \Magento\Framework\View\Element\Html\Select
{
    const ENABLE_VALUE = 'Yes';
    const DISABLE_VALUE = 'No';
    const ENABLE_LABEL = 'YES';
    const DISABLE_LABEL = 'NO';


    protected function getAllOptions()
    {
        return [self::ENABLE_VALUE => __(self::ENABLE_LABEL), self::DISABLE_VALUE => __(self::DISABLE_LABEL)];
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->getAllOptions() as $value => $label) {
                $this->addOption($value, addslashes($label));
            }
        }
        return parent::_toHtml();
    }
}
