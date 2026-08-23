<?php

namespace MW\Affiliate\Api\Data\Commission;

/**
 * Interface CommissionInterface
 * @package MW\Affiliate\Api\Data\Commission
 */
interface CommissionInterface
{

    const HISTORY_ID = "history_id";
    const ORDER_ID = 'order_id';
    const CUSTOMER_ID = 'customer_id';
    const TOTAL_COMMISSION = 'total_commission';
    const TOTAL_DISCOUNT = 'total_discount';
    const TRANSACTION_TIME = 'transaction_time';
    const COMMISSION_TYPE = 'commission_type';
    const SHOW_CUSTOMER_INVITED = 'show_customer_invited';
    const CUSTOMER_INVITED = 'customer_invited';
    const INVITATION_TYPE = 'invitation_type';
    const STATUS = 'status';

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getHistoryId();

    /**
     * @api
     * @param int $id
     * @return CommissionInterface
     */
    public function setHistoryId($id);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getOrderId();

    /**
     * @api
     * @param string $orderId
     * @return CommissionInterface
     */
    public function setOrderId($orderId);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getCustomerId();

    /**
     * @api
     * @param int $customerId
     * @return CommissionInterface
     */
    public function setCustomerId($customerId);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getTotalCommission();

    /**
     * @api
     * @param float $totalCommission
     * @return CommissionInterface
     */
    public function setTotalCommission($totalCommission);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getTotalDiscount();

    /**
     * @api
     * @param float $totalDiscount
     * @return CommissionInterface
     */
    public function setTotalDiscount($totalDiscount);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getTransactionTime();

    /**
     * @api
     * @param string $transactionTime
     * @return CommissionInterface
     */
    public function setTransactionTime($transactionTime);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getCommissionType();

    /**
     * @api
     * @param int $commissionType
     * @return CommissionInterface
     */
    public function setCommissionType($commissionType);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getShowCustomerInvited();

    /**
     * @api
     * @param int $show_customer_invited
     * @return CommissionInterface
     */
    public function setShowCustomerInvited($show_customer_invited);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getCustomerInvited();

    /**
     * @api
     * @param int $customerInvited
     * @return CommissionInterface
     */
    public function setCustomerInvited($customerInvited);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getInvitationType();

    /**
     * @api
     * @param int $invitationType
     * @return CommissionInterface
     */
    public function setInvitationType($invitationType);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getStatus();

    /**
     * @api
     * @param int $status
     * @return CommissionInterface
     */
    public function setStatus($status);
}
