<?php

namespace MW\Affiliate\Api\Data\Account;

/**
 * Interface AccountInterface
 * @package MW\Affiliate\Api\Data\Account
 */
interface AccountInterface
{

    const CUSTOMER_ID = "customer_id";
    const ACTIVE = 'active';
    const PAYMENT_GATEWAY = 'payment_gateway';
    const PAYMENT_EMAIL = 'payment_email';
    const AUTO_WITHDRAWN = 'auto_withdrawn';
    const WITHDRAWN_LEVEL = 'withdrawn_level';
    const RESERVE_LEVEL = 'reserve_level';
    const TOTAL_COMMISSION = 'total_commission';
    const BASE_TOTAL_COMMISSION = 'base_total_commission';
    const TOTAL_PAID = 'total_paid';
    const BASE_TOTAL_PAID = 'base_total_paid';
    const REFERRAL_CODE = 'referral_code';
    const BANK_INFORMATION = 'bank_information';
    const REFERRAL_SITE = 'referral_site';
    const CUSTOMER_INVITED = 'customer_invited';
    const INVITATION_TYPE = 'invitation_type';
    const LINK_CLICK_ID_PIVOT = 'link_click_id_pivot';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';

    /*----------------------------------------------------------------*/
    /**
     * @api
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @api
     * @param int $id
     * @return AccountInterface
     */
    public function setCustomerId($id);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getActive();

    /**
     * @api
     * @param int $active
     * @return AccountInterface
     */
    public function setActive($active);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getPaymentGateway();

    /**
     * @api
     * @param string $paymentGateway
     * @return AccountInterface
     */
    public function setPaymentGateway($paymentGateway);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getPaymentEmail();

    /**
     * @api
     * @param string $paymentEmail
     * @return AccountInterface
     */
    public function setPaymentEmail($paymentEmail);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getAutoWithdrawn();

    /**
     * @api
     * @param int $autoWithdrawn
     * @return AccountInterface
     */
    public function setAutoWithdrawn($autoWithdrawn);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float|null
     */
    public function getWithdrawnLevel();

    /**
     * @api
     * @param float $withdrawnLevel
     * @return AccountInterface
     */
    public function setWithdrawnLevel($withdrawnLevel);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float|null
     */
    public function getReserveLevel();

    /**
     * @api
     * @param float $reserveLevel
     * @return AccountInterface
     */
    public function setReserveLevel($reserveLevel);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float|null
     */
    public function getTotalCommission();

    /**
     * @api
     * @param float $totalCommission
     * @return AccountInterface
     */
    public function setTotalCommission($totalCommission);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float|null
     */
    public function getBaseTotalCommission();

    /**
     * @api
     * @param float $baseTotalCommission
     * @return AccountInterface
     */
    public function setBaseTotalCommission($baseTotalCommission);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float|null
     */
    public function getTotalPaid();

    /**
     * @api
     * @param float $totalPaid
     * @return AccountInterface
     */
    public function setTotalPaid($totalPaid);
    /*----------------------------------------------------------------*/


    /**
     * @api
     * @return float|null
     */
    public function getBaseTotalPaid();

    /**
     * @api
     * @param float $baseTotalPaid
     * @return AccountInterface
     */
    public function setBaseTotalPaid($baseTotalPaid);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getReferralCode();

    /**
     * @api
     * @param string $referralCode
     * @return AccountInterface
     */
    public function setReferralCode($referralCode);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getBankInformation();

    /**
     * @api
     * @param string $bankInformation
     * @return AccountInterface
     */
    public function setBankInformation($bankInformation);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getReferralSite();

    /**
     * @api
     * @param string $referralSite
     * @return AccountInterface
     */
    public function setReferralSite($referralSite);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getCustomerInvited();

    /**
     * @api
     * @param int $customerInvited
     * @return AccountInterface
     */
    public function setCustomerInvited($customerInvited);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getInvitationType();

    /**
     * @api
     * @param int $invitationType
     * @return AccountInterface
     */
    public function setInvitationType($invitationType);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getLinkClickIdPivot();

    /**
     * @api
     * @param int $link_click_id_pivot
     * @return AccountInterface
     */
    public function setLinkClickIdPivot($link_click_id_pivot);
    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @api
     * @param string $createdAt
     * @return AccountInterface
     */
    public function setCreatedAt($createdAt);
    /*----------------------------------------------------------------*/


    /**
     * @api
     * @return int|null
     */
    public function getStatus();

    /**
     * @api
     * @param int $status
     * @return AccountInterface
     */
    public function setStatus($status);
    /*----------------------------------------------------------------*/
}
