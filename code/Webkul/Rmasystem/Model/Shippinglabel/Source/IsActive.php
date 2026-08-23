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
namespace Webkul\Rmasystem\Model\Shippinglabel\Source;

class IsActive implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\Rmasystem\Model\Shippinglabel
     */
    protected $shippinglabel;

    /**
     * Constructor
     *
     * @param \Webkul\Rmasystem\Model\Shippinglabel $shippinglabel
     */
    public function __construct(\Webkul\Rmasystem\Model\Shippinglabel $shippinglabel)
    {
        $this->shippinglabel = $shippinglabel;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->shippinglabel->getAvailableStatuses();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
