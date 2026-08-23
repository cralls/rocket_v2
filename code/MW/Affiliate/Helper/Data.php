<?php

namespace MW\Affiliate\Helper;

use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use MW\Affiliate\Model\Days;
use MW\Affiliate\Model\Status;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Statusreferral;
use MW\Affiliate\Model\Statusprogram;
use MW\Affiliate\Model\Statusinvitation;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Transactiontype;
use MW\Affiliate\Model\Orderstatus;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MYCONFIG = 'affiliate/general_settings/enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * Cookie metadata factory
     *
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;


    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @var \MW\Affiliate\Model\CredithistoryFactory
     */
    protected $_credithistoryFactory;

    /**
     * @var \MW\Affiliate\Model\CreditcustomerFactory
     */
    protected $_creditcustomerFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliateprogramFactory
     */
    protected $_programFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupprogramFactory
     */
    protected $_groupprogramFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliategroupmemberFactory
     */
    protected $_groupmemberFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliateinvitationFactory
     */
    protected $_invitationFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatetransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $modelManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    protected $serializer;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory
     * @param \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory
     * @param \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory
     * @param \MW\Affiliate\Model\AffiliateprogramFactory $programFactory
     * @param \MW\Affiliate\Model\AffiliategroupFactory $groupFactory
     * @param \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory
     * @param \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \MW\Affiliate\Model\AffiliatetransactionFactory $affiliatetransactionFactory
     * @param \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        \MW\Affiliate\Model\CredithistoryFactory $credithistoryFactory,
        \MW\Affiliate\Model\CreditcustomerFactory $creditcustomerFactory,
        \MW\Affiliate\Model\AffiliateprogramFactory $programFactory,
        \MW\Affiliate\Model\AffiliategroupFactory $groupFactory,
        \MW\Affiliate\Model\AffiliategroupprogramFactory $groupprogramFactory,
        \MW\Affiliate\Model\AffiliategroupmemberFactory $groupmemberFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\AffiliatetransactionFactory $affiliatetransactionFactory,
        \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_messageManager = $messageManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_storeFactory = $storeFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_backendUrl = $backendUrl;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_withdrawnFactory = $withdrawnFactory;
        $this->_credithistoryFactory = $credithistoryFactory;
        $this->_creditcustomerFactory = $creditcustomerFactory;
        $this->_transactionFactory = $affiliatetransactionFactory;
        $this->_programFactory = $programFactory;
        $this->_groupFactory = $groupFactory;
        $this->_groupprogramFactory = $groupprogramFactory;
        $this->_groupmemberFactory = $groupmemberFactory;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_invitationFactory = $invitationFactory;
        $this->objectManager = $objectManager;
        $this->modelManager = $context->getModuleManager();
        $this->checkoutSession = $checkoutSession;
        $this->_localeDate = $localeDate;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * Retrieve store config value
     *
     * @param $xmlPath
     * @param null $storeCode
     * @return mixed
     */
    public function getStoreConfig($xmlPath, $storeCode = null)
    {
        if ($storeCode != null) {
            return $this->_scopeConfig->getValue(
                $xmlPath,
                ScopeInterface::SCOPE_STORE,
                $storeCode
            );
        } else {
            return $this->_scopeConfig->getValue(
                $xmlPath,
                ScopeInterface::SCOPE_STORE
            );
        }
    }

    /**
     * Check module is enabled or not
     *
     * @param null $storeCode
     * @return int
     */
    public function moduleEnabled($storeCode = null)
    {
        return (int) $this->getStoreConfig('affiliate/general_settings/enabled', $storeCode);
    }

    public function getGatewayStore($storeCode = null)
    {
        try {
            $gateways = $this->serializer->unserialize($this->getStoreConfig('affiliate/money/gateways', $storeCode));
        } catch (\Exception $e) {
            $gateways = json_decode($this->getStoreConfig('affiliate/money/gateways', $storeCode), true);
        }
        return $gateways;
    }

    /**
     * @param $paymentGateway
     * @param null $storeCode
     * @return int
     */
    public function getFeePaymentGateway($paymentGateway, $storeCode = null)
    {
        $gateways = $this->getGatewayStore($storeCode);
        if ($gateways) {
            foreach ($gateways as $gateway) {
                if ($paymentGateway == $gateway['gateway_value']) {
                    return $gateway['gateway_fee'];
                }
            }
        }
        return 0;
    }

    /**
     * @param $paymentGateway
     * @return string
     */
    public function getLabelPaymentGateway($paymentGateway)
    {
        $label = '';
        $gateways = $this->getGatewayStore();
        if ($gateways) {
            foreach ($gateways as $gateway) {
                if ($paymentGateway == $gateway['gateway_value']) {
                    $label = $gateway['gateway_title'];
                }
            }
        }
        return $label;
    }


    /**
     * @return array
     */
    public function _getPaymentGatewayArray()
    {
        $result = [];
        $gateways = $this->getGatewayStore();
        if ($gateways) {
            foreach ($gateways as $gateway) {
                $result[$gateway['gateway_value']] = $gateway['gateway_title'];
            }
        }
        return $result;
    }


    public function getDefaultGroupAffiliateStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/default_group', $storeCode);
    }

    public function getLengthReferralCodeStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/referral_code', $storeCode);
    }

    public function getWithdrawMinStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/money/affiliate_withdraw_min', $storeCode);
    }

    public function getWithdrawMaxStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/money/affiliate_withdraw_max', $storeCode);
    }

    public function getAutoSignUpAffiliate($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/auto_signup_affiliate', $storeCode);
    }

    public function getOverWriteRegister($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/overwrite_register', $storeCode);
    }

    public function getShowSignUpFormAffiliateRegister($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/signup_affiliate', $storeCode);
    }

    public function getShowReferralCodeRegister($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/show_referral_code_register', $storeCode);
    }

    public function getShowReferralCodeCartStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/show_referral_code_cart', $storeCode);
    }

    public function getAutoApproveRegister($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/auto_approve', $storeCode);
    }

    public function getReferralSignupCommission($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general/referral_signup_commission', $storeCode);
    }

    public function getReferralSubscribeCommission($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general/referral_subscribe_commission', $storeCode);
    }

    public function getWithdrawnPeriodStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/money/affiliate_withdrawn_period', $storeCode);
    }

    public function getWithdrawnDayStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/money/affiliate_withdrawn_day', $storeCode);
    }

    public function getWithdrawnMonthStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/money/affiliate_withdrawn_month', $storeCode);
    }
    public function getFeeStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/money/affiliate_fee_taken', $storeCode);
    }

    /**
     * @return mixed
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
    }
    public function getLocale()
    {
        return $this->_localeDate;
    }
    public function customerFactory()
    {
        return $this->_customerFactory->create();
    }
    public function getReferralCodeByCheckout()
    {
        return $this->checkoutSession->getReferralCode();
    }
    public function getAffiliatePositionStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/affiliate_position', $storeCode);
    }

    public function getAffiliateDiscountStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/affiliate_discount', $storeCode);
    }

    public function getAffiliateDiscountWithTax($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/affiliate_discount_with_tax', $storeCode);
    }

    public function getAffiliateTaxtStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/affiliate_tax', $storeCode);
    }
    public function getAutoSignUpAffiliateStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general_settings/auto_signup_affiliate', $storeCode);
    }
    public function getAutoApproveRegisterStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general_settings/auto_approve', $storeCode);
    }
    public function getAffiliateCommissionbyThemselves($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/affiliate_commission', $storeCode);
    }
    public function setNewCustomerInvitedStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general_settings/set_customerinvited', $storeCode);
    }
    public function getStatusAddCommissionStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/status_add_commission', $storeCode);
    }
    public function getStatusSubtractCommissionStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/status_subtract_commission', $storeCode);
    }
    public function holdingTimeConfig($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general/commission_holding_period', $storeCode);
    }
    public function getDiscountWhenRefundProductStore($storeCode)
    {
        return $this->getStoreConfig('affiliate/general/enabled_reward', $storeCode);
    }

    /**
     * @param $money
     * @return float|string
     */
    public function formatMoney($money)
    {
        return $this->_pricingHelper->currency($money);
    }

    /**
     * @param null $storeCode
     * @return int
     */
    public function getReferralVisitorNumber($storeCode = null)
    {
        $configValue = $this->getStoreConfig('affiliate/general/referral_visitor_commission', $storeCode);
        $configComponents = explode('/', $configValue);

        return intval($configComponents[1]);
    }

    /**
     * Process and change status of withdrawns
     *
     * @param $status
     * @param $withdrawnIds
     */
    public function processWithdrawn($status, $withdrawnIds)
    {
        $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
        $store = $this->_storeManager->getStore();

        $withdrawnCollection = $this->_withdrawnFactory->create()->getCollection()
            ->addFieldToFilter('withdrawn_id', ['in' => $withdrawnIds])
            ->addFieldToFilter('status', ['neq' => Status::COMPLETE])
            ->addFieldToFilter('status', ['neq' => Status::CANCELED]);

        if ($status == Status::COMPLETE) {
            foreach ($withdrawnCollection as $withdrawn) {
                $customerId = $withdrawn->getCustomerId();

                /* Handle Paypal Withdrawn by Paypal Masspay */
                if ($withdrawn->getPaymentGateway() == 'paypal') {
                    if ($this->getStoreConfig('affiliate/paypal_credential/paypal_status') > 0) {
                        $customer = $this->_customerFactory->create()->load($customerId);
                        $paypalParams = [
                            'amount' => $withdrawn->getAmountReceive(),
                            'currency' => $store->getCurrentCurrencyCode(),
                            'customer_email' => $withdrawn->getPaymentEmail(),
                            'customer_name' => $customer->getName(),
                        ];

                        $paypalResponse = $this->withdrawnPaypal($paypalParams);
                        if ($paypalResponse['status'] !== 'success') {
                            $this->_messageManager->addError(__($paypalResponse['error']));
                            continue;
                        } else {
                            $withdrawn->setStatus(Status::COMPLETE)
                                ->setWithdrawnTime($now)->save();
                        }
                    } else {
                        $withdrawn->setStatus(Status::COMPLETE)
                            ->setWithdrawnTime($now)->save();
                    }
                } else {
                    $withdrawn->setStatus(Status::COMPLETE)
                        ->setWithdrawnTime($now)->save();
                }

                // Send email to customer when withdrawn successfully
                $this->sendMailCustomerWithdrawnComplete(
                    $customerId,
                    $withdrawn->getWithdrawnAmount(),
                    $store->getCode()
                );

                // Update status in mw_credit_history table
                $collection = $this->_credithistoryFactory->create()->getCollection()
                    ->addFieldToFilter('type_transaction', Transactiontype::WITHDRAWN)
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('transaction_detail', $withdrawn->getId())
                    ->addFieldToFilter('status', Orderstatus::PENDING);

                foreach ($collection as $credithistory) {
                    $credithistory->setStatus(Orderstatus::COMPLETE)->save();
                }
            }
        } elseif ($status == Status::CANCELED) {
            foreach ($withdrawnCollection as $withdrawn) {
                $customerId = $withdrawn->getCustomerId();
                $withdrawn->setStatus(Status::CANCELED)
                    ->setWithdrawnTime($now)->save();

                // Send email to customer when withdrawn failed
                $this->sendMailCustomerWithdrawnCancel(
                    $customerId,
                    $withdrawn->getWithdrawnAmount(),
                    $store->getCode()
                );

                // Update status in mw_credit_history table
                $collection = $this->_credithistoryFactory->create()->getCollection()
                    ->addFieldToFilter('type_transaction', Transactiontype::WITHDRAWN)
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addFieldToFilter('transaction_detail', $withdrawn->getId())
                    ->addFieldToFilter('status', Orderstatus::PENDING);

                $creditcustomer = $this->_creditcustomerFactory->create()->load($customerId);
                $oldcredit = $creditcustomer->getCredit();

                foreach ($collection as $credithistory) {
                    $amount = $credithistory->getAmount();
                    $newcredit = $oldcredit - $amount;
                    $credithistory->setStatus(Orderstatus::CANCELED)->save();
                    $creditcustomer->setCredit($newcredit)->save();

                    // Save record to mw_credit_history table (Cancel status)
                    $historyData = [
                        'customer_id'            => $customerId,
                        'type_transaction'        => Transactiontype::CANCEL_WITHDRAWN,
                        'status'                => Orderstatus::COMPLETE,
                        'transaction_detail'    => $withdrawn->getId(),
                        'amount'                => -$amount,
                        'beginning_transaction' => $oldcredit,
                        'end_transaction'        => $newcredit,
                        'created_time'            => $now
                    ];
                    $this->_credithistoryFactory->create()->setData($historyData)->save();
                }
            }
        }

        $this->_messageManager->addSuccess("You have successfully updated the withdrawn(s) status");
    }

    public function withdrawnPaypal($params)
    {
        include_once 'app/code/MW/Affiliate/lib/internal/api/paypal/PaypalCallerService.php';
        $credentials['API_USERNAME']  = $this->getStoreConfig('affiliate/paypal_credential/api_username');
        $credentials['API_PASSWORD']  = $this->getStoreConfig('affiliate/paypal_credential/api_password');
        $credentials['API_SIGNATURE'] = $this->getStoreConfig('affiliate/paypal_credential/api_signature');
        $credentials['API_ENDPOINT']  = ($this->getStoreConfig('affiliate/paypal_credential/api_endpoint') > 0)
            ? 'https://api-3t.paypal.com/nvp'
            : 'https://api-3t.sandbox.paypal.com/nvp';

        $id             = urlencode((new \DateTime())->getTimestamp());
        $note              = urlencode($this->getStoreConfig('affiliate/paypal_credential/paypal_notification_note'));
        $subject          = urlencode($this->getStoreConfig('affiliate/paypal_credential/paypal_notification_subject'));
        $type              = urlencode('EmailAddress');

        $amount          = urlencode($params['amount']);
        $currency         = urlencode($params['currency']);
        $customer_email = urlencode($params['customer_email']);

        $base_call  = "&L_EMAIL0=".$customer_email.
            "&L_AMT0=".$amount.
            "&L_UNIQUEID0=".$id.
            "&L_NOTE0=".$note.
            "&EMAILSUBJECT=".$subject.
            "&RECEIVERTYPE=".$type.
            "&CURRENCYCODE=".$currency;

        $PayPal = new \MW\Affiliate\lib\internal\api\paypal\PayPal_CallerService($credentials);

        $status = $PayPal->callPayPal("MassPay", $base_call);
        if ($status) {
            if ($status['ACK'] == "Success") {
                $minimumBalance = doubleval($this->getStoreConfig('affiliate/paypal_credential/paypal_min_balance'));
                $notificationEmail = $this->getStoreConfig('affiliate/paypal_credential/paypal_email_notification');

                /* If the balance (after debited) is lower than minimum balance (defined by admin) then sending email to notify */
                $balance = $PayPal->callPayPal("GetBalance", '');
                if ($balance['L_AMT0'] < $minimumBalance) {
                    $message = "The balance is low... Please replenish the funds... \n\n".$balance['L_AMT0']." ".$balance['L_CURRENCYCODE0'];
                    mail($notificationEmail, 'PayPal Refund Balance Low', $message);
                }
                return ['status' => 'success'];

            } elseif ($status['ACK'] == "Failure") {

                if ($status['L_ERRORCODE0'] == '10321') {
                    $error = "Insufficient Funds to Send Refund to: " . $params['customer_name'] . '('.$params['customer_email'].') via PayPal in the amount of: '.$params['amount'].' '.$params['currency'].'. ';

                } elseif ($status['L_ERRORCODE0'] == '10004') {
                    $error = "Invalid Amount of Refund to: ". $params['customer_name'] .'('.$params['customer_email'].') via PayPal in the amount of: '.$params['amount'].' '.$params['currency'].'. Must be more than 0 '.$params['currency'].'.';

                } else {
                    $error = "There was an unknown error when attempting to submit the payment.";
                }
                return [
                    'status' => 'failure',
                    'error'     => $error
                ];
            }
        }
    }

    /**
     * Set total member in program
     */
    public function setTotalMemberProgram()
    {
        $programCollection = $this->_programFactory->create()->getCollection();
        if (sizeof($programCollection) > 0) {
            foreach ($programCollection as $program) {
                $totalMember = 0;
                $groupPrograms = $this->_groupprogramFactory->create()->getCollection()
                    ->addFieldToFilter('program_id', $program->getProgramId());

                if (sizeof($groupPrograms) > 0) {
                    foreach ($groupPrograms as $groupProgram) {
                        $groupId = $groupProgram ->getGroupId();
                        $customerPrograms = $this->_groupmemberFactory->create()->getCollection()
                            ->addFieldToFilter('group_id', $groupId);
                        $totalMember += sizeof($customerPrograms);
                    }
                }

                $program->setTotalMembers($totalMember)->save();
            }
        }
    }

    /**
     * @param $customerId
     * @return int
     */
    public function getActiveAndEnableAffiliate($customerId)
    {
        $collectionFilter = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', (int)$customerId)
            ->addFieldToFilter('active', Statusactive::ACTIVE)
            ->addFieldToFilter('status', Statusreferral::ENABLED);

        return sizeof($collectionFilter);
    }

    /**
     * @param $customerId
     * @param $detail
     * @return \Magento\Framework\Phrase
     */
    public function getLinkCustomer($customerId, $detail)
    {
        $result = __(
            '<b><a href="%1">%2</a></b>',
            $this->_backendUrl->getUrl('customer/index/edit', ['id' => $customerId]),
            $detail
        );

        return $result;
    }


    /**
     * @param $referralCode
     * @return int
     */
    public function checkReferralCodeCart($referralCode)
    {
        $result = 0;
        $customer_id = (int)$this->getCustomerSession()->getCustomer()->getId();
        $collectionCustomers = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('referral_code', $referralCode);
        if ($collectionCustomers->getSize() >0) {
            foreach ($collectionCustomers as $collectionCustomer) {
                $customer_id_referral_code = $collectionCustomer ->getCustomerId();
                $active = $collectionCustomer ->getActive();
                $status = $collectionCustomer ->getStatus();
                if ($active == Statusactive::ACTIVE && $status == Statusreferral::ENABLED) {
                    $result = 1;
                    if (isset($customer_id) && $customer_id == $customer_id_referral_code) {
                        $result = 0;
                    }
                }
                break;
            }
        }
        return $result;
    }

    /**
     * @param $referralCode
     * @return int
     */
    public function checkReferralCode($referralCode)
    {
        $result = 0;
        $collectionCustomers = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('referral_code', $referralCode);

        if ($collectionCustomers->getSize() > 0) {
            foreach ($collectionCustomers as $customer) {
                if ($customer->getActive() == Statusactive::ACTIVE
                    && $customer->getStatus() == Statusreferral::ENABLED
                ) {
                    $result = 1;
                }
                break;
            }
        }

        return $result;
    }

    /**
     * Set Referral code for affiliate
     *
     * @param $customerId
     */
    public function setReferralCode($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $length = (int) $this->getLengthReferralCodeStore($store->getCode());
        $i = 0;
        $referralCode = $this->rand_str($length);

        // Get all affiliates
        $allAffiliate = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToSelect('referral_code');
        $availabledReferralCode = [];
        foreach ($allAffiliate as $affiliate) {
            $availabledReferralCode[] = $affiliate->getReferralCode();
        }

        // Check the new referral code is exist or not
        while ($i == 0) {
            if (in_array($referralCode, $availabledReferralCode)) {
                $i = 0;
                $referralCode = $this->rand_str($length);
            } else {
                $i = 1;
            }
        }

        $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($customerId);
        // Save new referral code
        $affiliateCustomer->setReferralCode($referralCode)->save();
    }

    /**
     * @param $customerId
     * @param null $storeCode
     */
    public function setMemberDefaultGroupAffiliate($customerId, $storeCode = null)
    {
        if ($storeCode == null) {
            $storeCode = $this->_storeManager->getStore()->getCode();
        }

        $groupId = $this->getDefaultGroupAffiliateStore($storeCode);
        $checkGroup = $this->checkGroupExits($groupId);
        if ($checkGroup == 0) {
            $groupId = 1;
        }
        $data = [
            'customer_id' => $customerId,
            'group_id' => $groupId
        ];

        $this->_groupmemberFactory->create()->setData($data)->save();
    }

    /**
     * @param $groupId
     * @return int
     */
    public function checkGroupExits($groupId)
    {
        $collection = $this->_groupFactory->create()->getCollection()
            ->addFieldToFilter('group_id', $groupId);

        return sizeof($collection);
    }

    /**
     * @param $url
     * @return string
     */
    public function getWebsiteVerificationKey($url)
    {
        $urlParsed = parse_url($this->_urlBuilder->getCurrentUrl());
        $hostName = '';
        foreach ($urlParsed as $key => $value) {
            if ($key == 'host') {
                $hostName = $value;
            }
        }

        return '<meta name="' . $hostName . '-site-verification" content="' . md5($url) . '" />';
    }

    /**
     * Get all programs which customer is joining
     *
     * @param $customerId
     * @return mixed
     */
    public function getMemberProgram($customerId)
    {
        $programIds = [];
        $customerGroup = $this->_groupmemberFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();

        if ($customerGroup) {
            $customerPrograms = $this->_groupprogramFactory->create()->getCollection()
                ->addFieldToFilter('group_id', $customerGroup->getGroupId());
            foreach ($customerPrograms as $program) {
                $programIds[] = $program->getProgramId();
            }
        }

        $programs = $this->_programFactory->create()->getCollection()
            ->addFieldtoFilter('program_id', ['in' => $programIds])
            ->addFieldtoFilter('status', Statusprogram::ENABLED);

        return $programs;
    }


    /**
     * @param $referralCode
     * @param $cookie
     * @return int
     */
    public function getCustomerIdByReferralCode($referralCode, $cookie)
    {
        $result = $cookie;
        if (isset($referralCode) && $referralCode != '') {
            $collectionCustomers = $this->_affiliatecustomersFactory->create()->getCollection()
                ->addFieldToFilter('referral_code', $referralCode);

            if (sizeof($collectionCustomers) >0) {
                foreach ($collectionCustomers as $customer) {
                    if ($customer->getActive() == Statusactive::ACTIVE
                        && $customer->getStatus() == Statusreferral::ENABLED
                    ) {
                        $result = $customer->getCustomerId();
                    }
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * @param $customerId
     * @param $cookie
     * @param $clientIP
     * @param $referralFrom
     * @param $referralFromDomain
     * @param $referralTo
     * @param $invitationType
     * @param null $isSubscribed
     */
    public function updateAffiliateInvitionNew(
        $customerId,
        $cookie,
        $clientIP,
        $referralFrom,
        $referralFromDomain,
        $referralTo,
        $invitationType,
        $isSubscribed = null
    ) {
        if ($cookie != 0) {
            if ($invitationType == Typeinvitation::REFERRAL_CODE) {
                $referralFrom = '';
                $referralTo = '';
                $referralFromDomain = '';
            }
            $email = $this->_customerFactory->create()->load($customerId)->getEmail();
            $collection = $this->_invitationFactory->create()->getCollection()
                ->addFieldToFilter('customer_id', $cookie)
                ->addFieldToFilter('email', $email);

            // If friend who is invited to register affiliate member of website
            // If email same new email, it will update status
            $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
            if (sizeof($collection) > 0) {
                foreach ($collection as $obj) {
                    $obj->setStatus(Statusinvitation::REGISTER);
                    $obj->setIp($clientIP);
                    $obj->setInvitationTime($now);
                    $obj->setCountClickLink(0);
                    $obj->setCountRegister(1);
                    $obj->setCountPurChase(0);
                    $obj->setCountSubscribe(0);
                    $obj->setReferralFrom($referralFrom);
                    $obj->setReferralFromDomain($referralFromDomain);
                    $obj->setReferralTo($referralTo);
                    $obj->setOrderId('');
                    $obj->setInvitationType($invitationType);
                    $obj->save();
                }
            } else {
                // Add commission in case of visitor sign-up
                $store = $this->_storeManager->getStore();
                $referralSignupCommission = $this->getReferralSignupCommission($store->getCode());
                if ($referralSignupCommission > 0) {
                    $this->saveAffiliateTransactionReferral(
                        $customerId,
                        $referralSignupCommission,
                        $cookie,
                        $invitationType,
                        Transactiontype::REFERRAL_SIGNUP
                    );

                    $historyData = [
                        'customer_id'            => $cookie,
                        'email'                    => $email,
                        'status'                => Statusinvitation::REGISTER,
                        'ip'                    => $clientIP,
                        'count_click_link'        => 0,
                        'count_register'        => 1,
                        'count_purchase'        => 0,
                        'count_subscribe'        => 0,
                        'referral_from'            => $referralFrom,
                        'referral_from_domain'    => $referralFromDomain,
                        'referral_to'            => $referralTo,
                        'order_id'                => '',
                        'invitation_type'        => $invitationType,
                        'invitation_time'        => $now,
                        'commission'            => $referralSignupCommission
                    ];
                    $this->_invitationFactory->create()->setData($historyData)->save();

                    // Update total_commission in affiliate_customers
                    $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($cookie);
                    $currentCommission = $affiliateCustomer->getTotalCommission();
                    $affiliateCustomer->setTotalCommission($currentCommission + $referralSignupCommission);
                    $affiliateCustomer->save();

                    // Update customer credit
                    $customerCredit = $this->_creditcustomerFactory->create()->load($cookie);
                    $currentCredit = $customerCredit->getCredit();
                    $newCredit = $currentCredit + $referralSignupCommission;
                    $customerCredit->setCredit($newCredit)->save();

                    // Update credit history table
                    $creditHistoryData = [
                        'customer_id'            => $cookie,
                        'type_transaction'        => Transactiontype::REFERRAL_SIGNUP,
                        'status'                => Orderstatus::COMPLETE,
                        'transaction_detail'    => $customerId,
                        'amount'                => $referralSignupCommission,
                        'beginning_transaction'    => $currentCredit,
                        'end_transaction'        => $newCredit,
                        'created_time'            => $now
                    ];
                    $this->_credithistoryFactory->create()->setData($creditHistoryData)->save();
                }

                // If visitor subscribe then add commission in case of subscription
                $referralSubscribeCommission = $this->getReferralSubscribeCommission($store->getCode());
                if ($isSubscribed && $referralSubscribeCommission > 0) {
                    if ($referralFromDomain == '') {
                        $referralFromDomain = '';
                    } else {
                        $referralFromDomain = $referralFromDomain[0];
                    }

                    $subscribeHistoryData = [
                        'customer_id'            => $cookie,
                        'email'                    => $email,
                        'status'                => Statusinvitation::SUBSCRIBE,
                        'ip'                    => $clientIP,
                        'count_click_link'        => 0,
                        'count_register'        => 0,
                        'count_purchase'        => 0,
                        'count_subscribe'        => 1,
                        'referral_from'            => $referralFrom,
                        'referral_from_domain'    => $referralFromDomain,
                        'referral_to'            => $referralTo,
                        'order_id'                => '',
                        'invitation_type'        => $invitationType,
                        'invitation_time'        => $now,
                        'commission'            => $referralSubscribeCommission
                    ];
                    $this->_invitationFactory->create()->setData($subscribeHistoryData)->save();

                    $this->saveAffiliateTransactionReferral(
                        $customerId,
                        $referralSubscribeCommission,
                        $cookie,
                        $invitationType,
                        Transactiontype::REFERRAL_SUBSCRIBE
                    );

                    // Update total_commission in affiliate_customers
                    $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($cookie);
                    $currentCommission = $affiliateCustomer->getTotalCommission();
                    $affiliateCustomer->setTotalCommission($currentCommission + $referralSubscribeCommission);
                    $affiliateCustomer->save();

                    // Update customer credit
                    $customerCredit = $this->_creditcustomerFactory->create()->load($cookie);
                    $currentCredit = $customerCredit->getCredit();
                    $newCredit = $currentCredit + $referralSubscribeCommission;
                    $customerCredit->setCredit($newCredit)->save();

                    // Update credit history table
                    $creditHistoryData = [
                        'customer_id'            => $cookie,
                        'type_transaction'        => Transactiontype::REFERRAL_SUBSCRIBE,
                        'status'                => Orderstatus::COMPLETE,
                        'transaction_detail'    => $customerId,
                        'amount'                => $referralSubscribeCommission,
                        'beginning_transaction'    => $currentCredit,
                        'end_transaction'        => $newCredit,
                        'created_time'            => $now
                    ];
                    $this->_credithistoryFactory->create()->setData($creditHistoryData)->save();
                }
            }
        }
    }

    /**
     * @param $customerId
     * @param $commission
     * @param $cookie
     * @param $invitationType
     * @param $commissionType
     */
    public function saveAffiliateTransactionReferral($customerId, $commission, $cookie, $invitationType, $commissionType)
    {
        if ($commission > 0) {
            $now = date("Y-m-d H:i:s", (new \DateTime())->getTimestamp());
            $transactionData = [
                'order_id'                => '',
                'customer_id'           => $customerId,
                'total_commission'        => $commission,
                'total_discount'        => 0,
                'show_customer_invited' => $cookie,
                'customer_invited'        => 0,
                'invitation_type'        => $invitationType,
                'commission_type'        => $commissionType,
                'status'                => Status::COMPLETE,
                'transaction_time'        => $now
            ];
            $this->_transactionFactory->create()->setData($transactionData)->save();

            $transactionDataNew = [
                'order_id'                => '',
                'customer_id'           => $customerId,
                'total_commission'        => $commission,
                'total_discount'        => 0,
                'show_customer_invited' => 0,
                'customer_invited'        => $cookie,
                'invitation_type'        => $invitationType,
                'commission_type'        => $commissionType,
                'status'                => Status::COMPLETE,
                'transaction_time'        => $now
            ];
            $this->_transactionFactory->create()->setData($transactionDataNew)->save();
        }
    }

    /**
     * Check and set template for customer register form
     *
     * @return string
     */
    public function switchTemplate()
    {
        $storeCode = $this->_storeManager->getStore()->getCode();

        if ($this->moduleEnabled($storeCode)) {
            $overwriteForm = (int) $this->getOverWriteRegister($storeCode);
            $autoSignupAffiliate = (int) $this->getAutoSignUpAffiliate($storeCode);

            if (!$autoSignupAffiliate && $overwriteForm) {
                return 'MW_Affiliate::customer/form/register.phtml';
            }
        }

        return 'Magento_Customer::form/register.phtml';
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * @param $customerId
     * @return \MW\Affiliate\Model\Affiliatecustomers
     */
    public function getAffiliateMemberById($customerId)
    {
        return $this->_affiliatecustomersFactory->create()->load($customerId);
    }

    /**
     * @return \Magento\Framework\Pricing\Helper\Data
     */
    public function getPricingHelper()
    {
        return $this->_pricingHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * @param $cookieName
     * @return null|string
     */
    public function getCookie($cookieName)
    {
        return $this->_cookieManager->getCookie($cookieName);
    }

    /**
     * @param string $value
     * @param int $duration
     * @param string $path
     * @return void
     */
    public function setCookie($name, $value, $duration)
    {
        $publicCookieMetadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($duration);
        //->setPath($path)
        //->setSecure(true)
        //->setHttpOnly(true)
        //->setPath($this->sessionManager->getCookiePath())
        //>setDomain($this->sessionManager->getCookieDomain());
        $this->_cookieManager->setPublicCookie(
            $name,
            $value,
            $publicCookieMetadata
        );
    }


    // customer khong la thanh vien affiliate va khong co customer invited tra ve 0
    // hoac khong ton tai customer id tra ve 0
    // nguoc lai tra ve 1
    public function checkCustomer($customer_id)
    {
        $result = 0;
        if ($customer_id) {

            $result = 1;
            $active = $this ->getActiveAffiliate($customer_id);
            $customer_invited = $this->_affiliatecustomersFactory->create()->load($customer_id)->getCustomerInvited();
            if (!$customer_invited) {
                $customer_invited = 0;
            }
            if ($active == 0 && $customer_invited == 0 && $customer_invited == '') {
                $result = 0;
            }
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getAffiliateActive()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $collection = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('active', Statusactive::ACTIVE);

        return $collection->getSize();
    }

    /**
     * @return int
     */
    public function getActiveAffiliate($customerId)
    {
        $customerId = (int)$customerId;
        $collection = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('active', Statusactive::ACTIVE);

        return $collection->getSize();
    }


    /**
     * @return int
     */
    public function getAffiliateLock()
    {
        $customerId = (int) $this->_customerSession->getCustomer()->getId();
        $collection = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', Statusreferral::LOCKED);

        return $collection->getSize();
    }


    /**
     * @param $customerId
     * @return int
     */
    public function getLockAffiliate($customerId)
    {
        $collection = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', Statusreferral::LOCKED);

        return $collection->getSize();
    }


    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getWithdrawnPeriod()
    {
        $period = '';
        $storeCode = $this->_storeManager->getStore()->getCode();
        $withdrawnPeriod = (int) $this->getWithdrawnPeriodStore($storeCode);

        if ($withdrawnPeriod == 1) {
            $withdrawnDays = (int) $this->getWithdrawnDayStore($storeCode);
            $days = Days::getLabel($withdrawnDays);
            $period = __('Weekly, on %1', $days);
        } elseif ($withdrawnPeriod == 2) {
            $withdrawnMonth = (int) $this->getWithdrawnMonthStore($storeCode);
            $period = __('Monthly, Date %1', $withdrawnMonth);
        }

        return $period;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param $bannerLink
     * @return string
     */
    public function getLinkBanner(\Magento\Customer\Model\Customer $customer, $bannerLink)
    {
        return trim($bannerLink)."?mw_aref=".md5($customer->getEmail());
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return string
     */
    public function getLink(\Magento\Customer\Model\Customer $customer)
    {
        $url = $this->_urlBuilder->getBaseUrl();

        return trim($url) . "?mw_aref=" . md5($customer->getEmail());
    }

    /**
     * @param $url
     * @return int
     */
    public function validateDomainUrl($url)
    {
        $pattern = '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i';

        return preg_match($pattern, $url);
    }

    /**
     * Send email to customer when withdrawn cancel
     *
     * @param $customerId
     * @param $withdrawnAmount
     * @param $storeCode
     */
    public function sendMailCustomerWithdrawnCancel($customerId, $withdrawnAmount, $storeCode)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_withdrawn_cancel';
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $link = $this->_storeFactory->create()->load($storeCode, 'code')->getUrl('affiliate');

        $emailData = [
            'customer_name' => $customer->getName(),
            'amount' => $this->_pricingHelper->currency($withdrawnAmount, true, false),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'link' => $link
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send email to customer when withdrawn complete
     *
     * @param $customerId
     * @param $withdrawnAmount
     * @param $storeCode
     */
    public function sendMailCustomerWithdrawnComplete($customerId, $withdrawnAmount, $storeCode)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_withdrawn_complete';
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $link = $this->_storeFactory->create()->load($storeCode, 'code')->getUrl('affiliate/index/withdrawn');

        $emailData = [
            'customer_name' => $customer->getName(),
            'amount' => $this->_pricingHelper->currency($withdrawnAmount, true, false),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_withdrawal_link' => $link
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email to customer when request withdrawal manually
     *
     * @param $customerId
     * @param $withdrawnAmount
     * @param $storeCode
     */
    public function sendMailCustomerRequestWithdrawn($customerId, $withdrawnAmount, $storeCode)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_withdrawn';
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $link = $this->_storeFactory->create()->load($storeCode, 'code')->getUrl('affiliate/index/withdrawn');

        $emailData = [
            'customer_name' => $customer->getName(),
            'amount' => $this->_pricingHelper->currency($withdrawnAmount, true, false),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_withdrawal_link' => $link
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email to customers of the new program
     *
     * @param $data
     * @param $customerId
     * @param $storeCode
     */
    public function sendEmailNewProgram($data, $customerId, $storeCode)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_add_program';
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $link = $this->_storeFactory->create()->load($storeCode, 'code')->getUrl('affiliate/index/listprogram');

        $emailData = [
            'customer_name' => $customer->getName(),
            'program_name' => $data['program_name'],
            'program_commission' => $data['commission'],
            'program_discount' => $data['discount'],
            'program_description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_program_link' => $link
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email when admin approves affiliate
     *
     * @param $customerId
     */
    public function sendMailCustomerActiveAffiliate($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_successful';
        $url = $store->getBaseUrl(). 'affiliate/index/index';
        $emailData = [
            'customer_name' => $customer->getName(),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_affiliate_link' => $url
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email when admin does not approve affiliate
     *
     * @param $customerId
     */
    public function sendMailCustomerNotActiveAffiliate($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_unsuccessful';
        $url = $store->getBaseUrl(). 'affiliate/index/index';
        $emailData = [
            'customer_name' => $customer->getName(),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_affiliate_link' => $url
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email when the affiliate is locked
     *
     * @param $customerId
     */
    public function sendMailAffiliateIsLocked($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_lock';
        $url = $store->getBaseUrl(). 'affiliate/index/index';
        $emailData = [
            'customer_name' => $customer->getName(),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'link' => $url
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email when the affiliate is unlocked
     *
     * @param $customerId
     */
    public function sendMailAffiliateIsUnLocked($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_unlock';
        $url = $store->getBaseUrl(). 'affiliate/index/index';
        $emailData = [
            'customer_name' => $customer->getName(),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_affiliate_link' => $url
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email to customer when register affiliate
     *
     * @param $customerId
     */
    public function sendEmailCustomerPending($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template';
        $url = $store->getBaseUrl(). 'affiliate/index/index';
        $emailData = [
            'customer_name' => $customer->getName(),
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_affiliate_link' => $url
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email to customer for invite
     * @param $emailTo
     * @param $name
     * @param $data
     */
    public function sendEmailToInvite($emailTo, $name, $data)
    {

        $store = $this->_storeFactory->create()->load($data->getSender()->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/invitation/email_sender', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/invitation/email_template';
        if ($this->getStoreConfig('affiliate/invitation/using_customer_email')) {
            $senderEmail = $data->getSender()->getEmail();
        }
        $emailData = [
            'customer_name' => $name,
            'sender_email' => $senderEmail,
            'sender_name' => $data->getSender()->getName(),
            'store_name' => $storeName,
            'invitation_link' => $data->getInvitationLink(),
            'referral_code' => $data->getReferralCode(),
            'message' => $data->getMessage()
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $emailTo,
            $name,
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification email to administrator who can active affiliate customer
     *
     * @param int $customerId
     * @throws \Exception
     * @throws \Zend_Validate_Exception
     */
    public function sendEmailAdminActiveAffiliate($customerId)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/admin_customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/admin_customer/email_template';

        $emailData = [
            'customer_name' => $customer->getName(),
            'link_admin' => $this->_backendUrl->getUrl('admin'),
            'sender_name_admin' => $senderName,
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_email' => $customer->getEmail()
        ];

        $adminEmails = $this->getStoreConfig('affiliate/admin_customer/email_to');
        if (substr_count($adminEmails, ',') == 0) {
            if (\Zend_Validate::is($adminEmails, 'EmailAddress')) {
                $this->_sendEmailTransactionNew(
                    $senderEmail,
                    $adminEmails,
                    null,
                    $emailTemplate,
                    $emailData,
                    $storeCode
                );
            }
        } elseif (substr_count($adminEmails, ',') > 0) {
            $adminEmailsArray = explode(",", $adminEmails);
            foreach ($adminEmailsArray as $adminEmail) {
                if (\Zend_Validate::is($adminEmail, 'EmailAddress')) {
                    $this->_sendEmailTransactionNew(
                        $senderEmail,
                        $adminEmail,
                        null,
                        $emailTemplate,
                        $emailData,
                        $storeCode
                    );
                }
            }
        }
    }

    /**
     * Send notification email to customer when admin add or substract credit with comment
     *
     * @param $customerId
     * @param $data
     */
    public function sendMailWhenCreditBalanceChanged($customerId, $data)
    {
        $customer = $this->_customerFactory->create()->load($customerId);
        $store = $this->_storeFactory->create()->load($customer->getStoreId());
        $storeCode = $store->getCode();
        $storeName = $this->getStoreConfig('general/store_information/name', $storeCode);
        $sender = $this->getStoreConfig('affiliate/customer/email_sender', $storeCode);
        $senderName = $this->getStoreConfig('trans_email/ident_'.$sender.'/name', $storeCode);
        $senderEmail = $this->getStoreConfig('trans_email/ident_'.$sender.'/email', $storeCode);
        $emailTemplate = 'affiliate/customer/email_template_credit_balance_changed';

        $emailData = [
            'customer_name' => $customer->getName(),
            'transaction_amount' => $this->_pricingHelper->currency($data['transaction_amount'], true, false),
            'customer_balance' => $this->_pricingHelper->currency($data['customer_balance'], true, false),
            'transaction_detail' => $data['transaction_detail'],
            'transaction_time' => $data['transaction_time'],
            'sender_name' => $senderName,
            'store_name' => $storeName,
            'customer_credit_link' => $store->getUrl('credit')
        ];

        $this->_sendEmailTransactionNew(
            $senderEmail,
            $customer->getEmail(),
            $customer->getName(),
            $emailTemplate,
            $emailData,
            $storeCode
        );
    }

    /**
     * Send notification emails
     *
     * @param $sender
     * @param $emailTo
     * @param $name
     * @param $template
     * @param $data
     * @param $storeCode
     */
    public function _sendEmailTransactionNew($sender, $emailTo, $name, $template, $data, $storeCode)
    {
        $data['subject'] = 'Affiliate system !';
        $templateId = $this->getStoreConfig($template, $storeCode);

        $storeId = $this->_storeFactory->create()->load($storeCode, 'code')->getId();

        try {
            $this->_inlineTranslation->suspend();
            $this->_transportBuilder->setTemplateIdentifier(
                $templateId
            )->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )->setTemplateVars(
                $data
            )->setFrom(
                [
                    'email' => $sender,
                    'name' => $data['sender_name']
                ]
            )->addTo(
                $emailTo,
                $name
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_messageManager->addError(__("Email can not send !"));
        }
    }

    /**
     * Generate a random string
     *
     * @param int $length
     * @param string $chars
     * @return string
     */
    public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
    {
        // Length of character list
        $charsLength = (strlen($chars) - 1);

        // Start our string
        $string = $chars[rand(0, $charsLength)];

        // Generate random string
        for ($i = 1; $i < $length; $i = strlen($string)) {
            // Grab a random character from our list
            $r = $chars[rand(0, $charsLength)];

            // Make sure the same two characters don't appear next to each other
            if ($r != $string[$i - 1]) {
                $string .=  $r;
            }
        }

        // Return the random string
        return $string;
    }


    /**
     * @param $modelName
     * @param array $arguments
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getModel($modelName, array $arguments = [])
    {
        return $this->getModelObject($modelName, $arguments);
    }

    /**
     * @param $modelName
     * @param array $arguments
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getModelObject($modelName, array $arguments = [])
    {
        $model = $this->objectManager->create('\MW\Affiliate\Model\\'.$modelName);
        if (!$model) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('%1 doesn\'t extends \Magento\Framework\Model\AbstractModel', $modelName)
            );
        }
        return $model;
    }

    /**
     * @param $modelName
     * @param array $arguments
     * @return mixed
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function getModelExtensions($modelName, array $arguments = [])
    {
        $model = $this->objectManager->create($modelName);
        if (!$model) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('%1 doesn\'t extends \Magento\Framework\Model\AbstractModel', $modelName)
            );
        }
        return $model;
    }


    /**
     * @param $event
     * @param $params
     * @return mixed
     */
    public function dispatchEvent($event, $params)
    {
        return $this->_eventManager->dispatch($event, $params);
    }

    /**
     * @param $url
     * @return bool|string
     */
    public function isDirectReferral($url)
    {
        $urlComponents = parse_url($url);
        if (isset($urlComponents['scheme']) && $urlComponents['host']) {
            $domain = $urlComponents['scheme'] . '://' . $urlComponents['host'];
            $collection = $this->getModel('Affiliatewebsitemember')->getCollection()
                ->addFieldToFilter('status', ['eq' => 1])
                ->addFieldToFilter('domain_name', ['like' => '%'.$domain.'%']);
            if (sizeof($collection) > 0) {
                foreach ($collection as $item) {
                    $customerId = $item->getCustomerId();
                }
                $customer = $this->_customerFactory->create()->load($customerId);
                // Need return encrypted email => To use clickReferralLink() event
                return md5($customer->getEmail());
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function myConfig()
    {
        return self::MYCONFIG;
    }

    /**
     *
     */
    public function disableConfig()
    {
        /* $this->_scopeConfig->saveConfig(
            $this->myConfig(),
            0
        ); */
        $configModel = $this->objectManager->create(
            'Magento\Config\Model\ResourceModel\Config'
        );
        $configModel->saveConfig($this->myConfig(), 0, 'default', 0);

        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($website->getCode() != "admin") {
                $configModel->saveConfig($this->myConfig(), 0, 'websites', $website->getWebsiteId());
            }
        }


        $stores  = $this->_storeManager->getStores();
        foreach ($stores as $store) {
            if ($store->getCode() != "admin") {
                $configModel->saveConfig($this->myConfig(), 0, 'stores', $store->getStoreId());
            }
        }
        $configModel->saveConfig($this->myConfig(), 0, 'default', 0);
    }

    /**
     *
     */
    public function enableConfig()
    {
        $configModel = $this->objectManager->create(
            'Magento\Config\Model\ResourceModel\Config'
        );
        $configModel->saveConfig($this->myConfig(), 1, 'default', 0);

        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($website->getCode() != "admin") {
                $configModel->saveConfig($this->myConfig(), 1, 'websites', $website->getWebsiteId());
            }
        }


        $stores  = $this->_storeManager->getStores();
        foreach ($stores as $store) {
            if ($store->getCode() != "admin") {
                $configModel->saveConfig($this->myConfig(), 1, 'stores', $store->getStoreId());
            }
        }
        $configModel->saveConfig($this->myConfig(), 1);
    }

    /**
     * @param $moduleName
     * @return mixed
     */
    public function ModuleIsEnable($moduleName)
    {
        return $this->modelManager->isEnabled($moduleName);
    }

    /**
     * @param $store_id
     * @return mixed
     */
    public function getTimeCookieStore($store_id)
    {
        return $this->getStoreConfig('affiliate/invitation/affiliate_cookie', $store_id);
    }


    /* Return commission if there is enough visitors (to get commission), otherwise return 0 */
    public function calculateReferralVisitorCommission($customerId, $pivot)
    {
        /* Get the set of referral link click which started from pivot */
        $collection = $this->getModel('Affiliateinvitation')->getCollection();
        $collection->addFieldToFilter('customer_id', ['eq' => $customerId]);
        $collection->addFieldToFilter('count_click_link', ['eq' => '1']);
        $collection->addFieldToFilter('invitation_id', ['gt' => $pivot]);

        /* Get current referral visitor number (to get commission) from config */
        $configValue =  $this->getStoreConfig('affiliate/general/referral_visitor_commission');
        $configComponents = explode('/', $configValue);
        $commission = doubleval($configComponents[0]);
        $visitorNo  = isset($configComponents[1]) ? intval($configComponents[1]): 0 ;

        /* Plus 1 because we must include the new visit */
        if (sizeof($collection)+1 == $visitorNo) {
            return $commission;
        } else {
            return 0;
        }
    }

    /**
     * @param $customer_id
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function insertCustomerCredit($customer_id)
    {
        $customerCredit = $this->getModelExtensions('\MW\Affiliate\Model\Creditcustomer')->load($customer_id);
        if (!($customerCredit->getId())) {
            //Add credit to new customer
            $customerData = [
                'customer_id'=>$customer_id,
                'credit'=>0
            ];
            $this->getModelExtensions('\MW\Affiliate\Model\Creditcustomer')->saveCreditCustomer($customerData);
        }
    }


    /**
     * @param $customerId
     * @return float
     */
    public function getCreditByCustomer($customerId)
    {
        return $this->_creditcustomerFactory->create()->load($customerId)->getCredit();
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function _Session()
    {
        return $this->checkoutSession;
    }

    /**
     *
     */
    public function getCreditByCheckout()
    {
        return $this->_Session()->getCredit();
    }

    /**
     *
     */
    public function getShowCreditBlockCartStore($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/general_settings/show_credit_cart', $storeCode);
    }

    /**
     *
     */
    public function allowUsingCreditToCheckout($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/options/allow_using_credit_to_checkout', $storeCode);
    }

    /**
     *
     */
    public function getMaxCreditToCheckOut($storeCode = null)
    {
        return $this->getStoreConfig('affiliate/options/max_credit_to_checkout', $storeCode);
    }
}
