<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

class SetCustomerAccountBefore implements ObserverInterface
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
        // Check customer before save
        $storeCode = $this->_storeManager->getStore()->getCode();

        if ($this->_dataHelper->moduleEnabled($storeCode)) {
            $showSignupAffiliate = (int)$this->_dataHelper->getShowSignUpFormAffiliateRegister($storeCode);
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

            $controller = $observer->getControllerAction();
            $data = $controller->getRequest()->getPost();
            $referralCode = '';
            if (isset($data['referral_code'])) {
                $referralCode = $data['referral_code'];
            }

            $max = (double)$this->_dataHelper->getWithdrawMaxStore($storeCode);
            $min = (double)$this->_dataHelper->getWithdrawMinStore($storeCode);
            if (isset($data['check_affiliate']) && $showSignupAffiliate == 1) {
                $session->setCheckAffiliate($data['check_affiliate']);
            }

            if (isset($data['check_affiliate']) && $showSignupAffiliate == 2) {
                // Set session
                $getwayWithdrawn = $data['getway_withdrawn'];
                $paypalEmail = $data['paypal_email'];
                $autoWithdrawn = (int)$data['auto_withdrawn'];
                $paymentReleaseLevel = (double)$data['payment_release_level'];
                $reserveLevel = $data['reserve_level'];
                $bankName = $data['bank_name'];
                $nameAccount = $data['name_account'];
                $bankCountry = $data['bank_country'];
                $swiftBic = $data['swift_bic'];
                $referralSite = $data['referral_site'];
                $session->setCheckAffiliate($data['check_affiliate']);
                $session->setPaymentGateway($getwayWithdrawn);
                $session->setPaymentEmail($paypalEmail);
                $session->setAutoWithdrawn($autoWithdrawn);
                $session->setBankName($bankName);
                $session->setNameAccount($nameAccount);
                $session->setBankCountry($bankCountry);
                $session->setSwiftBic($swiftBic);

                if ($referralSite) {
                    $session->setReferralSite($referralSite);
                }
                if ($paymentReleaseLevel) {
                    $session->setWithdrawnLevel($paymentReleaseLevel);
                }
                if ($reserveLevel) {
                    $session->setReserveLevel($reserveLevel);
                }
                if ($getwayWithdrawn != 'banktransfer' && $getwayWithdrawn != 'check') {
                    $collectionFilter = $this->_affiliatecustomersFactory->create()->getCollection()
                        ->addFieldToFilter('payment_email', $paypalEmail);

                    if ($collectionFilter->getSize() > 0) {
                        $this->_messageManager->addError(__('There is already an account with this emails paypal'));
                        $url = $this->_urlManager->getUrl('customer/account/create', ['_nosecret' => true]);
                        $controller->getResponse()
                            ->setRedirect($this->_redirect->error($url))
                            ->sendResponse();
                        return;
                    }
                }

                if ($autoWithdrawn == Autowithdrawn::AUTO) {
                    if ($paymentReleaseLevel < $min || $paymentReleaseLevel > $max) {
                        $this->_messageManager->addError(__('Please insert a value of Auto payment when account balance reaches that is in range of [%1, %2]', $min, $max));
                        $url = $this->_urlManager->getUrl('customer/account/create', ['_nosecret' => true]);
                        $controller->getResponse()
                            ->setRedirect($this->_redirect->error($url))
                            ->sendResponse();
                        return;
                    }
                }
            }

            // Check referral code
            if ($referralCode != '') {
                $check = $this->_dataHelper->checkReferralCode($referralCode);
                if ($check == 0) {
                    $this->_messageManager->addError(__('The referral code is invalid.'));
                    $url = $this->_urlManager->getUrl('customer/account/create', ['_nosecret' => true]);
                    $controller->getResponse()
                        ->setRedirect($this->_redirect->error($url))
                        ->sendResponse();
                    return;
                }
            }

        }
    }
}
