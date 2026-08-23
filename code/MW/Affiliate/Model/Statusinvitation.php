<?php

namespace MW\Affiliate\Model;

class Statusinvitation extends \Magento\Framework\DataObject
{
    const INVITATION    = 1;
    const CLICKLINK        = 2;
    const REGISTER           = 3;
    const PURCHASE           = 4;
    const SUBSCRIBE        = 5;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \MW\Affiliate\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($data);
    }

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::INVITATION    => __('Invitation'),
            self::CLICKLINK        => __('Referral Visitor'),
            self::REGISTER           => __('Referral Sign-up'),
            self::PURCHASE           => __('Referral Purchase'),
            self::SUBSCRIBE        => __('Referral Subscribe')
        ];
    }

    /**
     * @param $status
     * @return string
     */
    public static function getLabel($status)
    {
        $options = self::getOptionArray();

        return $options[$status];
    }

    /**
     * @param $orderId
     * @param $email
     * @param $type
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionDetail($orderId, $email, $type)
    {
        $result = "";
        switch ($type) {
            case self::CLICKLINK:
                $result = __('Commission for referral visitors');
                break;
            case self::REGISTER:
                $registerCustomerName = $this->_dataHelper->getBackendCustomerNameByEmail($email);
                $result = __('New customer account: %1 (%2)', $registerCustomerName, $email);
                break;
            case self::PURCHASE:
                $purchaseCustomerName = $this->_dataHelper->getBackendCustomerNameByEmail($email);
                $result = __('Order <b>#%1</b> of %2 (%3)', $orderId, $purchaseCustomerName, $email);
                break;
            case self::SUBSCRIBE:
                $subscriberName = $this->_dataHelper->getBackendCustomerNameByEmail($email);
                $result = __('New subscriber: %1 (%2)', $subscriberName, $email);
                break;
        }

        return $result;
    }

    /**
     * @param $orderId
     * @param $email
     * @param $type
     * @return \Magento\Framework\Phrase|string
     */
    public function getTransactionDetailCsv($orderId, $email, $type)
    {
        $result = "";
        switch ($type) {
            case self::CLICKLINK:
                $configValue =  $this->_dataHelper->getStoreConfig('affiliate/general/referral_visitor_commission');
                $configComponents = explode('/', $configValue);
                $visitorNo  = intval($configComponents[1]);
                $plural = ($visitorNo > 1) ? 's' : '';

                $result = __('Commission for %1 referral visitor %2', $visitorNo, $plural);
                break;
            case self::REGISTER:
                $registerCustomerName = $this->_dataHelper->getBackendCustomerNameByEmail($email);
                $result = __('New customer account: %1 (%2)', $registerCustomerName, $email);
                break;
            case self::PURCHASE:
                $purchaseCustomerName = $this->_dataHelper->getBackendCustomerNameByEmail($email);
                $result = __('Order #%1 of %2 (%3)', $orderId, $purchaseCustomerName, $email);
                break;
            case self::SUBSCRIBE:
                $subscriberName = $this->_dataHelper->getBackendCustomerNameByEmail($email);
                $result = __('New subscriber: %1 (%2)', $subscriberName, $email);
                break;
        }

        return $result;
    }
}
