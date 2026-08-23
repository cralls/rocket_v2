<?php
// File: app/code/VNS/OrderImport/Plugin/CheckmoPaymentMethodPlugin.php

namespace VNS\OrderImport\Plugin;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class CheckmoPaymentMethodPlugin
{
    /**
     * @var State
     */
    protected $state;
    
    /**
     * Constructor
     *
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }
    
    /**
     * After Is Available
     *
     * @param \Magento\OfflinePayments\Model\Checkmo $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsAvailable(\Magento\OfflinePayments\Model\Checkmo $subject, $result)
    {
        try {
            // Check if current request is from the admin area
            if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
                return $result; // Keep original behavior in admin area
            }
        } catch (LocalizedException $e) {
            // Handling exception if area code is not set
        }
       	
        return false; // Hide 'checkmo' in frontend
		return $result;
    }
}
