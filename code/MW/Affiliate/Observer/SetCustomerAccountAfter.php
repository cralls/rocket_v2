<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

class SetCustomerAccountAfter implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_messageManager = $messageManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_urlManager = $urlManager;
        $this->_redirect = $redirect;
        $this->_dataHelper = $dataHelper;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
    }

    /**
     * TODO: Re-check referral code
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            // Check customer before save
            $storeCode = $this->_storeManager->getStore()->getCode();

            if ($this->_dataHelper->moduleEnabled($storeCode)) {

                $controller = $observer->getAccountController();
                $data = $controller->getRequest()->getPost();
                /* check allow register affiliate account */
                $check = $this->checkAllowAffiliateRegister($data['check_affiliate']);
                if (!$check) {
                    return $this;
                }

                $session = $this->_customerSession;
                $session->unsetData('check_affiliate');
                $session->unsetData('payment_gateway');
                $session->unsetData('payment_email');
                $session->unsetData('auto_withdrawn');
                $session->unsetData('withdrawn_level');
                $session->unsetData('reserve_level');
                $session->unsetData('bank_name');
                $session->unsetData('name_account');
                $session->unsetData('bank_country');
                $session->unsetData('swift_bic');
                $session->unsetData('referral_site');


                $referralCode = '';
                if (isset($data['referral_code'])) {
                    $referralCode = $data['referral_code'];
                }

                // Save customer
                $customers = $this->_customerFactory->create()->setWebsiteId(
                    $this->_storeManager->getStore()->getWebsiteId()
                )->getCollection();
                $customers->getSelect()->where("email='" . $data['email'] . "'");
                $customerId = 0;

                if ($customers->getSize() > 0) {
                    foreach ($customers as $customer) {
                        $customerId = $customer->getId();
                        break;
                    }
                }

                $collectionFilter = $this->_affiliatecustomersFactory->create()->getCollection()
                    ->addFieldToFilter('customer_id', $customerId);

                if ($customerId && $collectionFilter->getSize() == 0) {

                    $session = $this->_customerSession;
                    $session->unsetData('check_affiliate');
                    $session->unsetData('payment_gateway');
                    $session->unsetData('payment_email');
                    $session->unsetData('auto_withdrawn');
                    $session->unsetData('withdrawn_level');
                    $session->unsetData('reserve_level');
                    $session->unsetData('bank_name');
                    $session->unsetData('name_account');
                    $session->unsetData('bank_country');
                    $session->unsetData('swift_bic');
                    $session->unsetData('referral_site');

                    $clientIP = $controller->getRequest()->getServer('REMOTE_ADDR');

                    // If the cookie of customer who invited, set cookie = 0
                    $cookie = (int)$this->_dataHelper->getCookie('customer');
                    if ($cookie) {
                        if ($this->_dataHelper->getLockAffiliate($cookie) > 0) {
                            $cookie = 0;
                        }
                    } else {
                        $cookie = 0;
                    };

                    $cookie_old = $cookie;
                    // Re-set value for customer who invited
                    if ($referralCode != '') {
                        $cookie = $this->_dataHelper->getCustomerIdByReferralCode($referralCode, $cookie);
                    }
                    $invitationType = Typeinvitation::NON_REFERRAL;
                    if ($cookie != 0) {
                        $invitationType = Typeinvitation::REFERRAL_LINK;
                    }
                    if ($cookie_old != $cookie && $cookie != 0) {
                        $invitationType = Typeinvitation::REFERRAL_CODE;
                    }

                    $paymentGateway = '';
                    $paypalEmail = '';
                    $autoWithdrawn = 1;
                    $paymentReleaseLevel = 0;
                    $reserveLevel = 0;
                    $bankName = '';
                    $nameAccount = '';
                    $bankCountry = '';
                    $swiftBic = '';
                    $accountNumber = '';
                    $reAccountNumber = '';
                    $referralSite = '';

                    // If customer registers affiliate
                    $active = Statusactive::PENDING;
                    if ($this->_dataHelper->getAutoApproveRegister($storeCode)) {
                        $active = Statusactive::ACTIVE;
                    }

                    $showSignupAffiliate = (int)$this->_dataHelper->getShowSignUpFormAffiliateRegister($storeCode);
                    if (isset($data['check_affiliate']) && $showSignupAffiliate == 2) {
                        $paymentGateway = $data['getway_withdrawn'];
                        $paypalEmail = $data['paypal_email'];
                        $autoWithdrawn = $data['auto_withdrawn'];
                        $paymentReleaseLevel = $data['payment_release_level'];
                        $reserveLevel = $data['reserve_level'];
                        $referralSite = $data['referral_site'];

                        if ($paymentGateway == 'check') {
                            $paypalEmail = '';
                        }

                        if ($paymentGateway == 'banktransfer') {
                            $paypalEmail = '';
                            $bankName = $data['bank_name'];
                            $nameAccount = $data['name_account'];
                            $bankCountry = $data['bank_country'];
                            $swiftBic = $data['swift_bic'];
                            $accountNumber = $data['account_number'];
                            $reAccountNumber = $data['re_account_number'];
                        }
                    }

                    // In case withdrawal by manually
                    if ($autoWithdrawn == Autowithdrawn::MANUAL) {
                        $paymentReleaseLevel = 0;
                    }
                    if (!$reserveLevel) {
                        $reserveLevel = 0;
                    }
                    if (!$referralSite) {
                        $referralSite = '';
                    }

                    // Save customer to database
                    $customerData = [
                        'customer_id' => $customerId,
                        'active' => $active,
                        'payment_gateway' => $paymentGateway,
                        'payment_email' => $paypalEmail,
                        'auto_withdrawn' => $autoWithdrawn,
                        'withdrawn_level' => $paymentReleaseLevel,
                        'reserve_level' => $reserveLevel,
                        'bank_name' => $bankName,
                        'name_account' => $nameAccount,
                        'bank_country' => $bankCountry,
                        'swift_bic' => $swiftBic,
                        'account_number' => $accountNumber,
                        're_account_number' => $reAccountNumber,
                        'referral_site' => $referralSite,
                        'total_commission' => 0,
                        'total_paid' => 0,
                        'referral_code' => '',
                        'status' => 1,
                        'invitation_type' => $invitationType,
                        'customer_time' => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                        'customer_invited' => $cookie
                    ];

                    if (!($active == Statusactive::INACTIVE && $cookie == 0)) {
                        $this->_affiliatecustomersFactory->create()
                            ->setData($customerData)
                            ->save();
                    }

                    // In case have customer who invited
                    $referralFrom = $this->_dataHelper->getCookie('mw_referral_from');
                    $referralTo = $this->_dataHelper->getCookie('mw_referral_to');
                    $referralFromDomain = $this->_dataHelper->getCookie('mw_referral_from_domain');
                    if (!$referralFrom) {
                        $referralFrom = '';
                    }
                    if (!$referralTo) {
                        $referralTo = '';
                    }
                    if (!$referralFromDomain) {
                        $referralFromDomain = '';
                    }

                    if ($cookie != 0) {
                        // Check have subscriber
                        $isSubscribed = $data['is_subscribed'];
                        $this->_dataHelper->updateAffiliateInvitionNew(
                            $customerId,
                            $cookie,
                            $clientIP,
                            $referralFrom,
                            $referralFromDomain,
                            $referralTo,
                            $invitationType,
                            $isSubscribed
                        );
                    }

                    // Send notification email to customer when register affiliate
                    if ($active == Statusactive::PENDING) {
                        $this->_dataHelper->sendEmailCustomerPending($customerId);
                        // Send notification email to administrator who can active affiliate customer
                        $this->_dataHelper->sendEmailAdminActiveAffiliate($customerId);
                    } elseif ($active == Statusactive::ACTIVE) {
                        // Re-set referral code for affiliate customer
                        $this->_dataHelper->setReferralCode($customerId);

                        $storeId = $this->_customerFactory->create()->load($customerId)->getStoreId();
                        $store = $this->_storeFactory->create()->load($storeId);
                        $this->_dataHelper->setMemberDefaultGroupAffiliate($customerId, $store->getCode());

                        // Set total member customer program
                        $this->_dataHelper->setTotalMemberProgram();
                        // Send notification email when administrator approve affiliate
                        $this->_dataHelper->sendMailCustomerActiveAffiliate($customerId);
                    };

                }

            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die("SetCustomerAccountAfter");
        }
    }
    public function checkAllowAffiliateRegister($checkAffiliate)
    {
        $storeCode = $this->_storeManager->getStore()->getCode();
        $autoSignupAffiliate = (int)$this->_dataHelper->getAutoSignUpAffiliate($storeCode);
        $showSignupAffiliate = (int)$this->_dataHelper->getShowSignUpFormAffiliateRegister($storeCode);
        $overwriteRegister = (int)$this->_dataHelper->getOverWriteRegister($storeCode);

        if ($showSignupAffiliate == 3) {
            $overwriteRegister = 0;
        }
        if ($autoSignupAffiliate) {
            return true;
        }
        if (!$autoSignupAffiliate && $overwriteRegister && $checkAffiliate!='') {
            return true;
        }
        return false;
    }
}
