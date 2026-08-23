<?php

namespace MW\Affiliate\Model\Quote\Address\Total;

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $_adminOrder;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Service\ProgramsService
     */
    protected $programsService;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * Discount constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Sales\Model\AdminOrder\Create $adminOrder
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param \MW\Affiliate\Service\ProgramsService $programsService
     * @param \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\AdminOrder\Create $adminOrder,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Service\ProgramsService $programsService,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_storeManager = $storeManger;
        $this->_request = $request;
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_sessionManager = $sessionManager;
        $this->_state = $state;
        $this->_customerSession = $customerSession;
        $this->_localeDate = $localeDate;
        $this->_adminOrder = $adminOrder;
        $this->_dataHelper = $dataHelper;
        $this->programsService = $programsService;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $store_id = $this->_storeManager->getStore()->getId();
        $storeCode = $this->_storeManager->getStore($store_id)->getCode();
        if ($this->_dataHelper->moduleEnabled($storeCode)) {
                $address = $shippingAssignment->getShipping()->getAddress();
                //$quote = $address->getQuote();
                $items = $address->getAllVisibleItems();
            if (!count($items)) {
                return $this;
            }
                // xu ly code
                $baseDiscountAmount = 0;
                $referral_code = $this->_dataHelper->getReferralCodeByCheckout();
                $customer_id = (int)$this->_customerSession->getCustomer()->getId();
                $program_priority = $this->_dataHelper->getAffiliatePositionStore($storeCode);
                $position_discount = $this->_dataHelper->getAffiliateDiscountStore($storeCode);
                $discountWithTax = $this->_dataHelper->getAffiliateDiscountWithTax($storeCode);

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

                if ($discountWithTax == 1) {
                        $price = $item->getBasePriceInclTax();
                } else {
                        $price = $item->getBasePrice();
                }

                //$price = $item->getPrice();
                $programs = $this->programsService->processRule($item, $_programs);
                //var_dump($programs);exit;
                // neu mang program > 0
                $programs = $this->getProgramByCustomer($programs, $referral_code, $storeCode);
                //var_dump($programs);exit;
                if (sizeof($programs) >= 2) {
                    $array_customer_inviteds = array_splice($programs, sizeof($programs) - 1, 1);
                    foreach ($array_customer_inviteds as $array_customer_invited) {
                        $customer_invited = $array_customer_invited;
                        break;
                    }
                    // lay program theo 3 tieu chi
                    if ($program_priority == 1) {
                        $program_id = $this->programsService->getProgramByCommission($programs, $qty, $price, $customer_invited);
                    } elseif ($program_priority == 2) {
                        $program_id = $this->programsService->getProgramByDiscount($programs, $qty, $price, $customer_invited);
                    } elseif ($program_priority == 3) {
                        $program_id = $this->programsService->getProgramByPosition($programs);
                    }

                    $discount = $this->programsService->getDiscountByProgram($program_id, $qty, $price, null, $customer_invited);
                } else {
                    $discount = 0;
                }

                $discount = round($discount, 2);
                $baseDiscountAmount = $baseDiscountAmount + $discount;
                $item->setDiscountAmount($item->getDiscountAmount() + $this->_priceCurrency->convert($discount));
                //$item->setDiscountAmount($item->getDiscountAmount() + $this->_dataHelper->formatMoney($discount));
                $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $discount);
                $item->setMwAffiliateDiscount($discount);
                //var_dump($program_id);die();

            }

            if ($baseDiscountAmount != 0) {
                $label = 'Affiliate';
                $discountAmount = $this->_priceCurrency->convert($baseDiscountAmount);
                $discountAmountAff = $discountAmount;
                $baseDiscountAmountAff = $baseDiscountAmount;
                if ($total->getDiscountAmount() != 0) {
                    $baseDiscountAmount = $total->getDiscountAmount() - $baseDiscountAmount;
                    $discountAmount = $this->_priceCurrency->convert($baseDiscountAmount);
                    $label = $total->getDiscountDescription() . ', ' . $label;
                }

                $total->setDiscountDescription($label);
                $total->setDiscountAmount($discountAmount);
                $total->setBaseDiscountAmount($baseDiscountAmount);
                $total->setSubtotalWithDiscount($total->getSubtotal() - $discountAmountAff);
                $total->setBaseSubtotalWithDiscount($total->getBaseSubtotal() - $baseDiscountAmountAff);

                $total->addTotalAmount($this->getCode(), -$discountAmount);
                $total->addBaseTotalAmount($this->getCode(), -$baseDiscountAmount);
                $total->setGrandTotal($total->getGrandTotal() - $discountAmountAff);
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscountAmountAff);
            }
                return $this;
        }
    }


    /**
     * return list of programs and customer_invited
     * @param $programs
     * @param $referral_code
     * @param $storeCode
     * @return array
     */
    public function getProgramByCustomer($programs, $referral_code, $storeCode)
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
                /* nếu k có refferal code -> tìm đến thằng mà đã invited cho thằng này trong database */
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
                    $result = $this ->checkThreeCondition($customer_id, $customer_id, $programs);
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
                        $result = $this ->checkThreeCondition($customer_id, $customer_id, $programs);
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
                        $result_new = $this ->checkThreeCondition($customer_id, $customer_invited, $programs);
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
                            $result = $this ->checkThreeCondition($customer_id, $customer_id, $programs);
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
                $result = $this->checkThreeCondition(0, $cookie, $programs);
                if (sizeof($result) > 0) {
                    $result[]= $cookie;
                    return $result;
                }
            }
            return $result;
        }
        return $result;
    }
    /**
     * kiem tra 3 dk config tra ve mang program......
     * @param $customer_id
     * @param $customer_invited
     * @param $programs
     * @return array
     */
    public function checkThreeCondition($customer_id, $customer_invited, $programs)
    {
        $result = [];
        $group_members = $this->_dataHelper->getModel('Affiliategroupmember')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer_invited);
        $group_id = $group_members ->getFirstItem()->getGroupId();

        $group_affiliate = $this->_dataHelper->getModel('Affiliategroup')->load($group_id);

        $time_day = $group_affiliate->getLimitDay();
        $total_order = $group_affiliate->getLimitOrder();
        $total_commission_customer = $group_affiliate->getLimitCommission();

        // ham check dieu kien config thu nhat
        $check_customer_time = $this->programsService->checkCustomerInvitedTime($customer_id, $time_day);
        // ham kiem tra dieu kien config thu 2
        $check_customer_order = $this->programsService->checkCustomerInvitedTotalOrder($customer_id, $customer_invited, null, $total_order);
        //check dieu kien thu 3
        $check_customer_commission = $this->programsService->checkCustomerByTotalCommission($customer_id, $customer_invited, $total_commission_customer);
        // neu thoa man 3 dieu kien config thi thuc hien binh thuong
        if ($check_customer_time == 1 && $check_customer_order == 1 && $check_customer_commission == 1) {
            $program_ids = $this->programsService->getProgramByCustomerId($customer_invited);
            $result = array_intersect($program_ids, $programs);
        }
        return $result;
    }
}
