<?php

namespace MW\Affiliate\Model;

class Affiliatecustomers extends \Magento\Framework\Model\AbstractModel implements \MW\Affiliate\Api\Data\Account\AccountInterface
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\Affiliate\Model\ResourceModel\Affiliatecustomers');
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }
    public function setCustomerId($id)
    {
        return $this->setData(self::CUSTOMER_ID, $id);
    }

    public function getActive()
    {
        return $this->getData(self::ACTIVE);
    }
    public function setActive($active)
    {
        return $this->setData(self::ACTIVE, $active);
    }

    public function getPaymentGateway()
    {
        return $this->getData(self::PAYMENT_GATEWAY);
    }
    public function setPaymentGateway($paymentGateway)
    {
        return $this->setData(self::PAYMENT_GATEWAY, $paymentGateway);
    }

    public function getPaymentEmail()
    {
        return $this->getData(self::PAYMENT_EMAIL);
    }
    public function setPaymentEmail($paymentEmail)
    {
        return $this->setData(self::PAYMENT_EMAIL, $paymentEmail);
    }

    public function getAutoWithdrawn()
    {
        return $this->getData(self::AUTO_WITHDRAWN);
    }
    public function setAutoWithdrawn($autoWithdrawn)
    {
        return $this->setData(self::AUTO_WITHDRAWN, $autoWithdrawn);
    }

    public function getWithdrawnLevel()
    {
        return $this->getData(self::WITHDRAWN_LEVEL);
    }
    public function setWithdrawnLevel($withdrawnLevel)
    {
        return $this->setData(self::WITHDRAWN_LEVEL, $withdrawnLevel);
    }

    public function getReserveLevel()
    {
        return $this->getData(self::RESERVE_LEVEL);
    }
    public function setReserveLevel($reserveLevel)
    {
        return $this->setData(self::RESERVE_LEVEL, $reserveLevel);
    }

    public function getTotalCommission()
    {
        return $this->getData(self::TOTAL_COMMISSION);
    }
    public function setTotalCommission($totalCommission)
    {
        return $this->setData(self::TOTAL_COMMISSION, $totalCommission);
    }

    public function getBaseTotalCommission()
    {
        return $this->getData(self::BASE_TOTAL_COMMISSION);
    }
    public function setBaseTotalCommission($baseTotalCommission)
    {
        return $this->setData(self::BASE_TOTAL_COMMISSION, $baseTotalCommission);
    }

    public function getTotalPaid()
    {
        return $this->getData(self::TOTAL_PAID);
    }
    public function setTotalPaid($totalPaid)
    {
        return $this->setData(self::TOTAL_PAID, $totalPaid);
    }

    public function getBaseTotalPaid()
    {
        return $this->getData(self::BASE_TOTAL_PAID);
    }
    public function setBaseTotalPaid($baseTotalPaid)
    {
        return $this->setData(self::BASE_TOTAL_PAID, $baseTotalPaid);
    }

    public function getReferralCode()
    {
        return $this->getData(self::REFERRAL_CODE);
    }
    public function setReferralCode($referralCode)
    {
        return $this->setData(self::REFERRAL_CODE, $referralCode);
    }

    public function getBankInformation()
    {
        return $this->getData(self::BANK_INFORMATION);
    }
    public function setBankInformation($bankInformation)
    {
        return $this->setData(self::BANK_INFORMATION, $bankInformation);
    }

    public function getReferralSite()
    {
        return $this->getData(self::REFERRAL_SITE);
    }
    public function setReferralSite($referralSite)
    {
        return $this->setData(self::REFERRAL_SITE, $referralSite);
    }

    public function getCustomerInvited()
    {
        return $this->getData(self::CUSTOMER_INVITED);
    }
    public function setCustomerInvited($customerInvited)
    {
        return $this->setData(self::CUSTOMER_INVITED, $customerInvited);
    }

    public function getInvitationType()
    {
        return $this->getData(self::INVITATION_TYPE);
    }
    public function setInvitationType($invitationType)
    {
        return $this->setData(self::INVITATION_TYPE, $invitationType);
    }

    public function getLinkClickIdPivot()
    {
        return $this->getData(self::LINK_CLICK_ID_PIVOT);
    }
    public function setLinkClickIdPivot($link_click_id_pivot)
    {
        return $this->setData(self::LINK_CLICK_ID_PIVOT, $link_click_id_pivot);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
