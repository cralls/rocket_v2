<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

class SalesOrderAfter implements ObserverInterface
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
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \MW\Affiliate\Service\ProgramsService
     */
    protected $programsService;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * SalesOrderAfter constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $urlManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \MW\Affiliate\Service\ProgramsService $programsService
     * @param \Magento\Framework\App\RequestInterface $request
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
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \MW\Affiliate\Service\ProgramsService $programsService,
        \Magento\Framework\App\RequestInterface $request
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
        $this->orderFactory = $orderFactory;
        $this->programsService = $programsService;
        $this->request = $request;
    }


    /**
     * @param \Magento\Framework\Event\Observer $argv
     */
    public function execute(\Magento\Framework\Event\Observer $argv)
    {
        $order = $argv->getEvent()->getOrder();
        $store_id = $order->getStoreId();
        $storeCode = $this->_storeManager->getStore($store_id)->getCode();
        if ($this->_dataHelper->moduleEnabled($storeCode)) {
            $discountAmount = 0;
            $referral_code = $this->_dataHelper->getReferralCodeByCheckout();
            $program_priority = $this->_dataHelper->getAffiliatePositionStore($storeCode);
            $position_discount = $this->_dataHelper->getAffiliateDiscountStore($storeCode);
            $affiliate_tax = $this->_dataHelper->getAffiliateTaxtStore($storeCode);
            $discountWithTax = $this->_dataHelper->getAffiliateDiscountWithTax($storeCode);

            $order_id = $order->getId();
            $orderid = $order->getIncrementId();
            $customer_id = 0;

            if ($order_id) {
                try {
                    $customer_id = $this->orderFactory->create()->load($order_id)->getCustomerId();
                    if ($customer_id) {
                        $this->saveCustomerRegister($referral_code, $customer_id, $argv);
                    }
                } catch (\Exception $e) {
                }
            }

            // Set of customer_invited (direct-invited)
            $invited_customers = [];

            // Set of customer_invited (customer_id)
            $invited_customers_array = [];

            // Set of all customer_invited (union of 2 above sets)
            $total_invited_customers = [];
            $items = $this->_dataHelper->getCheckoutSession()->getQuote()->getAllVisibleItems();

            $programs = [];
            $programs = $this->programsService->getAllProgram();
            if (!$this->_storeManager->isSingleStoreMode()) {
                $programs = $this->programsService->getProgramByStoreView($programs);
            }
            $programs = $this->programsService->getProgramByEnable($programs);
            $_programs = $this->programsService->getProgramByTime($programs);


            foreach ($items as $item) {
                $product_id = $item->getProductId();
                $qty = $item->getQty();
                if ($position_discount == 1) {
                    if ($discountWithTax == 1) {
                        $price = $item->getBasePriceInclTax();
                    } else {
                        $price = $item->getBasePrice();
                    }
                    $price_tax = $item->getBasePriceInclTax();
                    $price_no_tax = $item->getBasePrice();
                } else {
                    if ($discountWithTax == 1) {
                        $price = $item->getBasePriceInclTax() - ($item->getBaseDiscountAmount()- $item->getMwAffiliateDiscount()- $item->getMwCreditDiscount())/$qty;
                    } else {
                        $price = $item->getBasePrice() - ($item->getBaseDiscountAmount()- $item->getMwAffiliateDiscount()- $item->getMwCreditDiscount())/$qty;
                    }
                    $price_tax = $item->getBasePriceInclTax() - $item->getBaseDiscountAmount()/$qty;
                    $price_no_tax = $item->getBasePrice() - $item->getBaseDiscountAmount()/$qty;
                }
                if ($affiliate_tax == 0) {
                    $price_tax = $price_no_tax;
                }
                $programs = $this->programsService->processRule($item, $_programs);
                $programs = $this->getProgramByCustomer($programs, $referral_code, $orderid, $storeCode);

                if (sizeof($programs) >= 2) {
                    $array_customer_inviteds = array_splice($programs, sizeof($programs)-1, 1);

                    foreach ($array_customer_inviteds as $array_customer_invited) {
                        $customer_invited = $array_customer_invited;
                        break;
                    }
                    if (!in_array($customer_invited, $invited_customers)) {
                        $invited_customers[] = $customer_invited;
                    }

                    // get program by 3 criterion
                    if ($program_priority == 1) {
                        $program_id=$this->programsService->getProgramByCommission($programs, $qty, $price, $customer_invited);
                    } elseif ($program_priority == 2) {
                        $program_id=$this->programsService->getProgramByDiscount($programs, $qty, $price, $customer_invited);
                    } elseif ($program_priority == 3) {
                        $program_id=$this->programsService->getProgramByPosition($programs);
                    };

                    $discount = $this->programsService->getDiscountByProgram($program_id, $qty, $price, $orderid, $customer_invited);
                    $discount = round($discount, 2);
                    if (!$customer_id) {
                        $customer_id = 0;
                    }

                    $multi_level_commission = $this->programsService->getMultiLevel($program_id);
                    $invited_customers_array = $this->programsService->getArrayCustomerInvited($customer_invited, $multi_level_commission);

                    $level = 1;
                    foreach ($invited_customers_array as $invited_customers_level) {
                        // Check if member is locked or restricted in 3 limits
                        $mw_check_limit = $this->programsService->checkThreeConditionCustomerInvited($customer_id, $invited_customers_level, $orderid);
                        if ($this->_dataHelper->getLockAffiliate($invited_customers_level) == 1 || $mw_check_limit == 0) {
                            $level = $level + 1;
                            continue;
                        }

                        if (!in_array($invited_customers_level, $total_invited_customers)) {
                            $total_invited_customers[] = $invited_customers_level;
                        }
                        $commission = $this->programsService->getCommissionByProgram($program_id, $qty, $price_tax, $level);
                        $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_LINK;
                        $historyData = [
                            'customer_id'        => $customer_id,
                            'product_id'        => $product_id,
                            'program_id'        => $program_id,
                            'order_id'            => $orderid,
                            'total_amount'        => $qty*$price,
                            'history_commission'=> $commission,
                            'history_discount'    => $discount,
                            'customer_invited'    => $invited_customers_level,
                            'invitation_type'    => $invitation_type,
                            'status'            => \MW\Affiliate\Model\Status::PENDING,
                            'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                        ];

                        if ($program_id !=0) {
                            if ($commission > 0 || $discount > 0) {
                                $this->_dataHelper->getModel('Affiliatehistory')->setData($historyData)->save();
                            }
                        }
                        $level = $level + 1;
                    }
                } else {
                    $discount = 0;
                    $commission = 0;
                };
                $discountAmount = $discountAmount + $discount;
            }

            $invitation_type = $this->getInvitationType($referral_code, $order_id, $invited_customers);
            $this->setAffiliateTransaction($orderid, $total_invited_customers, $discountAmount, $invitation_type, $argv);

            // update customer_invited in case of referral-code-checkout (if config is enable)
            $set_customer_invited = $this->_dataHelper->setNewCustomerInvitedStore($storeCode);
            if ($set_customer_invited) {
                $this->setCustomerInvited($referral_code, $order_id, $invited_customers);
            }
            // destroy session of referral code
            $this->_dataHelper->getCheckoutSession()->unsetData('referral_code');
        }
    }


    public function getInvitationType($referral_code, $order_id, $invited_customers)
    {
        $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_LINK;
        try {
            $customer_id = $this->orderFactory->create()->load($order_id)->getCustomerId();
        } catch (\Exception $e) {
            $customer_id = '';
        }
        if ($customer_id) {
            $customers = $this->_dataHelper->getModel('Affiliatecustomers')->load($customer_id);
            $customer_invited = $customers->getCustomerInvited();
            if (!$customer_invited) {
                $customer_invited = 0;
            }
            $invitation_type = $customers->getInvitationType();
            if (isset($referral_code) && $referral_code !='' && sizeof($invited_customers) > 0) {
                foreach ($invited_customers as $invited_customer) {
                    if ($invited_customer != 0 && $invited_customer != $customer_id && $invited_customer != $customer_invited) {
                        $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_CODE;

                    }
                }
            }
        }
        return $invitation_type;
    }

    public function setAffiliateTransaction($order_id, $invited_customers, $discountAmount, $invitation_type, $observer)
    {
        $total_commission = 0;
        try {
            $customer_id = $this->orderFactory->create()->loadByIncrementId($order_id)->getCustomerId();
        } catch (\Exception $e) {
            $customer_id = '';
        }
        if (sizeof($invited_customers) > 0) {
            foreach ($invited_customers as $invited_customer) {
                $affiliateHistorys = $this->_dataHelper->getModel('Affiliatehistory')->getCollection()
                    ->addFieldtoFilter('order_id', $order_id)
                    ->addFieldtoFilter('customer_invited', $invited_customer);

                if (sizeof($affiliateHistorys) > 0) {
                    $customer_commission = 0;
                    $customer_discount = 0;
                    foreach ($affiliateHistorys as $affiliateHistory) {
                        $affiliateHistory->setInvitationType($invitation_type)->save();
                        $commission = $affiliateHistory->getHistoryCommission();
                        $discount = $affiliateHistory->getHistoryDiscount();
                        $customer_commission = $customer_commission + $commission;
                        $customer_discount = $customer_discount + $discount;
                    }
                    $historyData = [
                        'order_id'            => $order_id,
                        'customer_id'      => $customer_id,
                        'total_commission'    => $customer_commission,
                        'total_discount'    => $customer_discount,
                        'customer_invited'    => $invited_customer,
                        'invitation_type'    => $invitation_type,
                        'commission_type'    => \MW\Affiliate\Model\Transactiontype::BUY_PRODUCT,
                        'status'            => \MW\Affiliate\Model\Status::PENDING,
                        'transaction_time'    => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
                    ];
                    $this->_dataHelper->getModel('Affiliatetransaction')->setData($historyData)->save();
                    $total_commission = $total_commission + $customer_commission;
                }
            }
            $transactionData = [
                'order_id'                => $order_id,
                'customer_id'          =>$customer_id,
                'total_commission'        => $total_commission,
                'total_discount'        => $discountAmount,
                'show_customer_invited'=> $invited_customers[0],
                'customer_invited'        => 0,
                'invitation_type'        => $invitation_type,
                'commission_type'        => \MW\Affiliate\Model\Transactiontype::BUY_PRODUCT,
                'status'                => \MW\Affiliate\Model\Status::PENDING,
                'transaction_time'        => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()),
            ];
            if ($total_commission > 0 || $discountAmount > 0) {
                $this->_dataHelper->getModel('Affiliatetransaction')->setData($transactionData)->save();
            }

            // save invitation when customer purchase product
            $clientIP = $this->request->getServer('REMOTE_ADDR');

            $email = $this->_dataHelper->customerFactory()->load($customer_id)->getEmail();
            $click_lick = \MW\Affiliate\Model\Statusinvitation::CLICKLINK;
            $register = \MW\Affiliate\Model\Statusinvitation::REGISTER;
            $purchase = \MW\Affiliate\Model\Statusinvitation::PURCHASE;
            $referral_from = '';
            $referral_to = '';
            $referral_from_domain = '';
            if ($invitation_type == \MW\Affiliate\Model\Typeinvitation::REFERRAL_LINK) {
                $collection_invitations = $this->_dataHelper->getModel('Affiliateinvitation')
                    ->getCollection()
                    ->addFieldToFilter('email', $email)
                    ->addFieldToFilter('status', ['in' => [$click_lick,$register,$purchase]]);

                foreach ($collection_invitations as $collection_invitation) {
                    $referral_from = $collection_invitation->getReferralFrom();
                    $referral_from_domain = $collection_invitation->getReferralFromDomain();
                    $referral_to = $collection_invitation->getReferralTo();
                    if ($referral_from != '') {
                        break;
                    }
                }
            }

            /* Update Invitation Table */
            $history = $this->_dataHelper->getModel('Affiliatehistory')
                ->getCollection()
                ->addFieldtoFilter('order_id', $order_id)
                ->addFieldtoFilter('customer_invited', $invited_customers[0]);

            if (sizeof($history)) {
                foreach ($history as $item) {
                    $invitationData = [
                        'customer_id'            => $invited_customers[0],
                        'email'                => $email,
                        'status'                => \MW\Affiliate\Model\Statusinvitation::PURCHASE,
                        'ip'                    => $clientIP,
                        'count_click_link'        => 0,
                        'count_register'        => 0,
                        'count_subscribe'        => 0,
                        'count_purchase'        => 1,
                        'referral_from'        => $referral_from,
                        'referral_from_domain'    => $referral_from_domain,
                        'referral_to'            => $referral_to,
                        'order_id'                => $order_id,
                        'commission'            => $item->getHistoryCommission(),
                        'invitation_type'        => $invitation_type,
                        'invitation_time'        => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp())
                    ];
                    $this->_dataHelper->getModel('Affiliateinvitation')->setData($invitationData)->save();
                }
            }
        }

        $amountCredit = $this->_dataHelper->getCheckoutSession()->getCredit();

        // save credit va affiliate dicount cho moi order
        if (!$amountCredit) {
            $amountCredit = 0;
        }
        if ($amountCredit > 0 || $discountAmount > 0) {
            $orderData = [
                'order_id'    => $order_id,
                'affiliate'    => round($discountAmount, 2),
                'credit'    => round($amountCredit, 2)
            ];
            $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Creditorder')->saveCreditOrder($orderData);
        }
    }

    public function setCustomerInvited($referral_code, $order_id, $invited_customers)
    {
        if (isset($referral_code) && $referral_code != '' && sizeof($invited_customers) > 0) {
            try {
                $customer_id = $this->orderFactory->create()->load($order_id)->getCustomerId();
            } catch (\Exception $e) {
                $customer_id = '';
            }
            if ($customer_id) {
                $customers = $this->_dataHelper->getModel('Affiliatecustomers')->load($customer_id);
                $customer_invited = $customers->getCustomerInvited();
                if (!$customer_invited) {
                    $customer_invited = 0;
                }
                foreach ($invited_customers as $invited_customer) {
                    if ($invited_customer != 0 && $invited_customer != $customer_id && $invited_customer != $customer_invited) {
                        $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_CODE;
                        $customers->setCustomerInvited($invited_customer)->save();
                        $customers->setCustomerTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()))->save();
                        $customers->setInvitationType($invitation_type)->save();
                    }
                }
            }
        }
    }


    /**
     * @param $referral_code
     * @param $customer_id
     * @param $observer
     */
    public function saveCustomerRegister($referral_code, $customer_id, $observer)
    {
        $cookie = (int)$this->_dataHelper->getCookie('customer');
        // if the cookie of inviter doesn't exist then assign it to zero
        if ($cookie) {
            if ($this->_dataHelper->getLockAffiliate($cookie)== 1) {
                $cookie = 0;
            }
        } else {
            $cookie = 0;
        }
        $cookie_old = $cookie;

        if ($referral_code != '') {
            $cookie = $this->_dataHelper->getCustomerIdByReferralCode($referral_code, $cookie);
        }
        $invitation_type = \MW\Affiliate\Model\Typeinvitation::NON_REFERRAL;
        if ($cookie != 0) {
            $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_LINK;
        }
        if ($cookie_old != $cookie && $cookie != 0) {
            $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_CODE;
        }

        $customers = $this->_affiliatecustomersFactory->create()->getCollection()
            ->addFieldToFilter('customer_id', $customer_id);

        $store_id  = $this->_storeManager->getStore()->getId();
        $storeCode = $this->_storeManager->getStore()->getCode();

        $active = \MW\Affiliate\Model\Statusactive::INACTIVE;
        $auto_signup_affiliate = (int)$this->_dataHelper->getAutoSignUpAffiliateStore($storeCode);

        if ($auto_signup_affiliate) {
            $active = \MW\Affiliate\Model\Statusactive::PENDING;
            $auto_approved = $this->_dataHelper->getAutoApproveRegisterStore($store_id);
            if ($auto_approved) {
                $active = \MW\Affiliate\Model\Statusactive::ACTIVE;
            }
        }
        if (sizeof($customers)== 0 && ($cookie != 0 || $active != \MW\Affiliate\Model\Statusactive::INACTIVE)) {
            $customerData = [
                'customer_id'        => $customer_id,
                'active'            => $active,
                'payment_gateway'    => '',
                'payment_email'        => '',
                'auto_withdrawn'    => 0,
                'withdrawn_level'    => 0,
                'reserve_level'        => 0,
                'bank_name'            => '',
                'name_account'        => '',
                'bank_country'        => '',
                'swift_bic'            => '',
                'account_number'    => '',
                're_account_number'    => '',
                'referral_site'        => '',
                'total_commission'    => 0,
                'total_paid'        => 0,
                'referral_code'     => '',
                'status'            => 1,
                'invitation_type'    => $invitation_type,
                'customer_time'     => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()), // now()
                'customer_invited'    => $cookie
            ];
            //Mage::getModel('affiliate/affiliatecustomers')->saveCustomerAccount($customerData);
            $this->_affiliatecustomersFactory->create()
                ->setData($customerData)
                ->save();

            $clientIP = $this->request->getServer('REMOTE_ADDR');
            $referral_from = $this->_dataHelper->getCookie('mw_referral_from');
            $referral_to = $this->_dataHelper->getCookie('mw_referral_to');
            $referral_from_domain = $this->_dataHelper->getCookie('mw_referral_from_domain');

            if (!$referral_from) {
                $referral_from = '';
            }
            if (!$referral_to) {
                $referral_to = '';
            }
            if (!$referral_from_domain) {
                $referral_from_domain = '';
            }
            if ($invitation_type == \MW\Affiliate\Model\Typeinvitation::REFERRAL_CODE) {
                $referral_from = '';
                $referral_to = '';
                $referral_from_domain = '';

            }
            $email = $this->_customerFactory->create()->load($customer_id)->getEmail();
            $collection = $this->_dataHelper->getModel('Affiliateinvitation')
                ->getCollection()
                ->addFieldToFilter('customer_id', $cookie)
                ->addFieldToFilter('email', $email);

            // neu ban be dc moi dang ky lam thanh vien cua website ?
            // voi email trung voi email moi se update lai trang thai
            if (sizeof($collection) > 0) {
                foreach ($collection as $obj) {
                    $obj->setStatus(\MW\Affiliate\Model\Statusinvitation::REGISTER);
                    $obj->setIp($clientIP);
                    $obj->setInvitationTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
                    $obj->setCountClickLink(0);
                    $obj->setCountRegister(1);
                    $obj->setCountPurChase(0);
                    $obj->setCountSubscribe(0);
                    $obj->setReferralFrom($referral_from);
                    $obj->setReferralFromDomain($referral_from_domain);
                    $obj->setReferralTo($referral_to);
                    $obj->setOrderId('');
                    $obj->setInvitationType($invitation_type);
                    $obj->save();
                }
            }
            // nguoc lai luu moi vao csdl
            else {
                $historyData = [
                    'customer_id'            => $cookie,
                    'email'                => $email,
                    'status'                => \MW\Affiliate\Model\Statusinvitation::REGISTER,
                    'ip'                    => $clientIP,
                    'count_click_link'        => 0,
                    'count_register'        => 1,
                    'count_purchase'        => 0,
                    'count_subscribe'        => 0,
                    'referral_from'        => $referral_from,
                    'referral_from_domain' => $referral_from_domain,
                    'referral_to'            => $referral_to,
                    'order_id'                => '',
                    'invitation_type'        => $invitation_type,
                    'invitation_time'        => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()), // now()
                ];
                $this->_dataHelper->getModel('Affiliateinvitation')->setData($historyData)->save();
            }
        }
    }


    // return list of programs and customer_invited
    public function getProgramByCustomer($programs, $referral_code, $orderid, $storeCode)
    {
        $program_ids = [];
        $program_id_news = [];
        $result_new = [];
        $result = [];
        $cookie = (int)$this->_dataHelper->getCookie('customer');
        if (!$cookie) {
            $cookie = 0;
        }
        $customer_id = (int)$this->_dataHelper->getCustomerSession()->getCustomer()->getId();

        // check customer_id khong la thanh vien affiliate va khong co customer invited
        $check = $this->_dataHelper->checkCustomer($customer_id);

        $affiliate_commission = (int)$this->_dataHelper->getAffiliateCommissionbyThemselves($storeCode);

        if ($customer_id) {
            $is_rererral_code = 0;
            $customer_invited = 0;
            if ($referral_code) {
                $customer_id_new = $this->_dataHelper->getCustomerIdByReferralCode($referral_code, $customer_id);
            } else {
                $customer_id_new = $cookie;
            }
            // truong hop khong co referral code va co referral code
            if ($customer_id == $customer_id_new) {
                $customer_invited = $this->_affiliatecustomersFactory->create()->load($customer_id)->getCustomerInvited();
                if (!$customer_invited) {
                    $customer_invited = 0;
                }
            } else {
                $customer_invited = $customer_id_new;
                $is_rererral_code = 1;
            }


            // kiem tra xem thanh vien do co customer_invited khong?
            if ($customer_invited == 0) {
                // kiem tra xem khach mua hang do co phai la affilite va ko bi khoa
                // tra ve mang chuong trinh va customer_invited
                if ($affiliate_commission == 0) {
                    return $result;
                }

                if ($this->_dataHelper->getActiveAndEnableAffiliate($customer_id) == 1) {
                    $result = $this ->checkThreeCondition($customer_id, $customer_id, $programs, $orderid);
                    if (sizeof($result) > 0) {
                        $result[]= $customer_id;
                        return $result;
                    } elseif (sizeof($result) == 0) {
                        return $result;
                    }
                }
            } elseif ($customer_invited != 0) {
                // customer invited bi khoa
                if ($this->_dataHelper->getLockAffiliate($customer_invited) == 1) {
                    // neu khach hang la thanh vien cua affiliate va ko bi khoa
                    // load chuong trinh ra
                    if ($affiliate_commission == 0) {
                        return $result;
                    }

                    if ($this->_dataHelper->getActiveAndEnableAffiliate($customer_id) == 1) {
                        $result = $this ->checkThreeCondition($customer_id, $customer_id, $programs, $orderid);
                        if (sizeof($result) > 0) {
                            $result[]= $customer_id;
                            return $result;
                        } elseif (sizeof($result) == 0) {
                            return $result;
                        }
                    }
                }
                // customer invited khong bi khoa
                elseif ($this->_dataHelper->getLockAffiliate($customer_invited) == 0) {
                    if ($is_rererral_code) {
                        $program_id_news = $this->programsService->getProgramByCustomerId($customer_invited);
                        $result_new = array_intersect($program_id_news, $programs);
                    } else {
                        $result_new = $this ->checkThreeCondition($customer_id, $customer_invited, $programs, $orderid);
                    }
                    // customer invited tham gia vao chuong trinh co san pham
                    if (sizeof($result_new) > 0) {
                        $result_new[] = $customer_invited;
                        return $result_new;
                    }
                    // customer invited khong tham gia vao chuong trinh co san pham
                    elseif (sizeof($result_new) == 0) {
                        if ($affiliate_commission == 0) {
                            return $result;
                        }

                        if ($this->_dataHelper->getActiveAndEnableAffiliate($customer_id) == 1) {
                            $result = $this ->checkThreeCondition($customer_id, $customer_id, $programs, $orderid);
                            if (sizeof($result) > 0) {
                                $result[]= $customer_id;
                                return $result;
                            } elseif (sizeof($result) == 0) {
                                return $result;
                            }
                        }
                    }
                }
            }
            return $result;
        }
        // neu khach hang mua hang khong dang ky la thanh vien cua website
        // chi xet truong hop tim chuong trinh theo customer invited luu o cookie
        $cookie = $this->_dataHelper->getCustomerIdByReferralCode($referral_code, $cookie);
        if ($cookie) {
            if ($this->_dataHelper->getLockAffiliate($cookie)== 0) {
                $result = $this->checkThreeCondition(0, $cookie, $programs, $orderid);
                if (sizeof($result) > 0) {
                    $result[]= $cookie;
                    return $result;
                }
            }
            return $result;
        }
        return $result;
    }


    // kiem tra 3 dk config tra ve mang program......
    public function checkThreeCondition($customer_id, $customer_invited, $programs, $orderid)
    {
        $result = [];
        $program_ids = [];
        $mw_check_limit = $this->programsService->checkThreeConditionCustomerInvited($customer_id, $customer_invited, $orderid);

        if ($mw_check_limit == 1) {
            $program_ids = $this->programsService->getProgramByCustomerId($customer_invited);
            $result = array_intersect($program_ids, $programs);
        }
        return $result;
    }
}
