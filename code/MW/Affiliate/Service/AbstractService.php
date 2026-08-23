<?php

namespace MW\Affiliate\Service;

use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

abstract class AbstractService
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
     * @var
     */
    public $getProgramByCustomer;

    /**
     * @param $object
     */
    public function setGetProgramByCustomer($object)
    {
        $this->getProgramByCustomer = $object;
    }

    /**
     * @return array
     */
    public function getAllProgram()
    {
        $programs = [];
        $program_collections = $this->_dataHelper->getModel('Affiliateprogram')->getCollection();
        foreach ($program_collections as $program_collection) {
            $programs[] = $program_collection->getProgramId();
        }
        return $programs;
    }

    /**
     * @param $programs
     * @return array
     */
    public function getProgramByStoreView($programs)
    {
        $program_ids = [];
        $store_id = $this->_storeManager->getStore()->getId();
        foreach ($programs as $program) {
            $store_view = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getStoreView();
            $store_views = explode(',', $store_view);
            if (in_array($store_id, $store_views) or $store_views[0] == '0') {
                $program_ids[] = $program;
            }
        }
        return $program_ids;
    }

    /**
     * @param $programs
     * @return array
     */
    public function getProgramByEnable($programs)
    {
        $program_ids = [];
        foreach ($programs as $program) {
            $status = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getStatus();
            if ($status == \MW\Affiliate\Model\Statusprogram::ENABLED) {
                $program_ids[] = $program;
            }
        }
        return $program_ids;
    }

    /**
     * @param $programs
     * @return array
     */
    public function getProgramByTime($programs)
    {
        $program_ids = [];
        foreach ($programs as $program) {
            $start_date = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getStartDate();
            $end_date = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getEndDate();
            if ($this->_dataHelper->getLocale()->isScopeDateInInterval(null, $start_date, $end_date)) {
                $program_ids[] = $program;
            }
        }
        return $program_ids;
    }

    /**
     * @param $item
     * @param $programs
     * @return array
     */
    public function processRule($item, $programs)
    {
        $program_ids = [];
        foreach ($programs as $program) {
            $rule = $this->_dataHelper->getModel('Affiliateprogram')->load($program);
            $rule->afterLoad();
            $address = $this->getAddress_new($item);
            if (($rule->validate($address)) && ($rule->getActions()->validate($item))) {
                $program_ids[] = $program;
            }
        }
        return $program_ids;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return \Magento\Quote\Model\Quote\Address
     */
    protected function getAddress_new(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Address\Item) {
            $address = $item->getAddress();
        } elseif ($item->getQuote()->isVirtual()) {
            $address = $item->getQuote()->getBillingAddress();
        } else {
            $address = $item->getQuote()->getShippingAddress();
        }
        return $address;
    }

    public function getProgramByCustomerId($customer_id)
    {
        $program_ids = [];
        $customer_groups =  $this->_dataHelper->getModel('Affiliategroupmember')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer_id);
        if (sizeof($customer_groups) > 0) {
            foreach ($customer_groups as $customer_group) {
                $group_id = $customer_group->getGroupId();
                break;
            }
            $customer_programs = $this->_dataHelper->getModel('Affiliategroupprogram')
                ->getCollection()
                ->addFieldToFilter('group_id', $group_id);
            foreach ($customer_programs as $customer_program) {
                $program_ids[] = $customer_program->getProgramId();
            }
        }
        return $program_ids;
    }

    public function getProgramByCommission($programs, $qty, $price, $customer_invited)
    {
        $array_commissions = [];
        $max = 0;
        $program_id = 0;
        foreach ($programs as $program) {
            $result_commission = 0 ;
            $commissions = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getCommission();
            if (substr_count($commissions, ',') == 0) {
                $result_commission = $commissions;
            } elseif (substr_count($commissions, ',') >= 1) {
                $commission = explode(",", $commissions);
                $result_commission = $commission[0];
            };
            if (substr_count($result_commission, '%')==1) {
                $text = explode("%", $result_commission);
                $percent = trim($text[0]);
                $array_commissions[$program]=($percent*$price*$qty)/100;
            } elseif (substr_count($result_commission, '%')==0) {
                $array_commissions[$program]=$result_commission*$qty;
            };
            if ($max < $array_commissions[$program]) {
                $max = $array_commissions[$program];
                $program_id = $program;
            }
        }
        if ($program_id == 0) {
            $program_id = $this->getProgramByDiscountOld($programs, $qty, $price, $customer_invited);
        }
        return $program_id;
    }

    public function getProgramByDiscount($programs, $qty, $price, $customer_invited)
    {
        $customer_id = $this->_dataHelper->getCustomerSession()->getCustomer()->getId();
        if (!$customer_id) {
            $customer_id = 0;
        }
        $array_discounts = [];
        $max = 0;
        $program_id = 0;
        foreach ($programs as $program) {
            $result_discounts = 0;
            $discounts = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getDiscount();
            if (substr_count($discounts, ',') == 0) {
                $result_discounts = $discounts;
            } elseif (substr_count($discounts, ',') >= 1) {
                $discount = explode(",", $discounts);
                if ($customer_id == 0) {
                    $result_discounts = $discount[0];
                } else {
                    $collection = $this->_dataHelper->getModel('Affiliatehistory')->getCollection()
                        ->addFieldToFilter('customer_invited', $customer_invited)
                        ->addFieldToFilter('customer_id', $customer_id)
                        ->addFieldToFilter('status', ['nin' =>[\MW\Affiliate\Model\Status::CANCELED]]);
                    $collection->getSelect()->group('order_id');
                    $sizeof_discount = sizeof($discount);
                    $sizeo_order = sizeof($collection);
                    if ($sizeo_order < $sizeof_discount) {
                        $result_discounts = $discount[$sizeo_order];
                    } elseif ($sizeo_order >= $sizeof_discount) {
                        $result_discounts = $discount[$sizeof_discount - 1];
                    };
                };
            };
            if (substr_count($result_discounts, '%') == 1) {
                $text = explode("%", $result_discounts);
                $percent = trim($text[0]);
                $array_discounts[$program]=($percent*$price*$qty)/100;
            } elseif (substr_count($result_discounts, '%')==0) {
                $array_discounts[$program]= $result_discounts*$qty;
            }
            if ($max < $array_discounts[$program]) {
                $max = $array_discounts[$program];
                $program_id = $program;
            }
        }
        if ($program_id == 0) {
            $program_id = $this->getProgramByCommissionOld($programs, $qty, $price);
        }

        return $program_id;
    }

    public function getProgramByPosition($programs)
    {
        $program_id=0;
        $positions = [];
        $min_position=0;
        foreach ($programs as $program) {
            $min_position = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getProgramPosition();
            break;
        }
        foreach ($programs as $program) {
            $positions[$program] = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getProgramPosition();
            if ($positions[$program]<= $min_position) {
                $min_position = $positions[$program];
                $program_id=$program;
            }
        }
        return $program_id;
    }

    public function getDiscountByProgram($program_id, $qty, $price, $orderid = null, $customer_invited)
    {
        $customer_id = (int)$this->_dataHelper->getCustomerSession()->getCustomer()->getId();
        if (!$customer_id) {
            $customer_id = 0;
        }
        $mw_discounts = 0;
        $discounts = $this->_dataHelper->getModel('Affiliateprogram')->load($program_id)->getDiscount();
        if (substr_count($discounts, ',') == 0) {
            $result_discounts = $discounts;
        } elseif (substr_count($discounts, ',') >= 1) {
            $discount = explode(",", $discounts);
            if ($customer_id == 0) {
                $result_discounts = $discount[0];
            } else {
                $collection = $this->_dataHelper->getModel('Affiliatehistory')->getCollection()
                    ->addFieldToFilter('customer_invited', $customer_invited)
                    ->addFieldToFilter('customer_id', $customer_id)
                    ->addFieldToFilter('status', ['nin' => [\MW\Affiliate\Model\Status::CANCELED]]);

                if ($orderid) {
                    $collection->addFieldToFilter('order_id', ['nin' => [$orderid]]);
                } else {
                    /** not thing  */
                }
                $collection ->getSelect()->group('order_id');
                $sizeof_discount = sizeof($discount);
                $sizeo_order = sizeof($collection);
                if ($sizeo_order < $sizeof_discount) {
                    $result_discounts = $discount[$sizeo_order];
                } elseif ($sizeo_order >= $sizeof_discount) {
                    $result_discounts = $discount[$sizeof_discount - 1];
                };
            };
        }
        if (substr_count($result_discounts, '%') == 1) {
            $text=explode("%", $result_discounts);
            $percent=trim($text[0]);
            $mw_discounts = ($percent*$price*$qty)/100;
        } elseif (substr_count($result_discounts, '%') == 0) {
            $mw_discounts = $result_discounts * $qty ;
        }
        return $mw_discounts;
    }

    public function getMultiLevel($program_id)
    {
        $commissions = $this->_dataHelper->getModel('Affiliateprogram')->load($program_id)->getCommission();
        if (substr_count($commissions, ',') == 0) {
            return 1;
        } elseif (substr_count($commissions, ',') >= 1) {
            $commission = explode(",", $commissions);
            return sizeof($commission);
        };
        return 1;
    }


    public function getArrayCustomerInvited($customer_invited, $multi_level_commission)
    {
        $array_invited = [];
        $array_invited[1] = $customer_invited;
        if ($multi_level_commission == 1) {
            return $array_invited;
        } else {
            $i = 1;
            while ($i < $multi_level_commission) {
                $customer_invited_i = $this->_dataHelper->getModel('Affiliatecustomers')->load($array_invited[$i])->getCustomerInvited();
                if (!$customer_invited_i) {
                    $customer_invited_i = 0;
                }
                if ($customer_invited_i == 0) {
                    break;
                } elseif ($customer_invited_i != 0) {
                    $array_invited[$i +1] = $customer_invited_i;
                    $i = $i +1;
                }
            }
            return $array_invited;
        }
    }

    // kiem tra 3 dk config tra ve 0 or 1
    public function checkThreeConditionCustomerInvited($customer_id, $customer_invited, $orderid)
    {
        $group_members = $this->_dataHelper->getModel('Affiliategroupmember')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customer_invited);
        $group_id = $group_members ->getFirstItem()->getGroupId();

        $group_affiliate = $this->_dataHelper->getModel('Affiliategroup')->load($group_id);

        $time_day = $group_affiliate->getLimitDay();
        $total_order = $group_affiliate->getLimitOrder();
        $total_commission_customer = $group_affiliate->getLimitCommission();

        // ham check dieu kien config thu nhat
        $check_customer_time = $this->checkCustomerInvitedTime($customer_id, $time_day);
        // ham kiem tra dieu kien config thu 2
        $check_customer_order = $this->checkCustomerInvitedTotalOrder($customer_id, $customer_invited, $orderid, $total_order);
        //check dieu kien thu 3
        $check_customer_commission = $this->checkCustomerByTotalCommission($customer_id, $customer_invited, $total_commission_customer);
        // neu thoa man 3 dieu kien config thi thuc hien binh thuong
        if ($check_customer_time == 1 && $check_customer_order == 1 && $check_customer_commission == 1) {
            return 1;
        }
        return 0;
    }


    // kiem tra thoi gian customer invited con hieu luc khong?
    public function checkCustomerInvitedTime($customer_id, $time_day)
    {
        if ($time_day == '') {
            return 0;
        }
        $time_day = intval($time_day);
        if ($customer_id == 0) {
            return 1;
        }
        if ($time_day == 0) {
            return 1;
        }
        if ($time_day >0) {
            $time_day_second = $time_day * 24 * 60 * 60;
            $date_now = (new \DateTime())->getTimestamp();
            $time_register = $this->_dataHelper->getModel('Affiliatecustomers')->load($customer_id)->getCustomerTime();
            if (!$time_register) {
                return 1;
            }
            $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magento\Framework\Stdlib\DateTime\DateTime'
            );
            $date_register = $dateTime->timestamp($time_register);

            if (($date_register + $time_day_second) > $date_now) {
                return 1;
            }
            if (($date_register + $time_day_second) <= $date_now) {
                return 0;
            }
        }
    }


    // kiem tra so order invoice ma customer invoice moi dc
    public function checkCustomerInvitedTotalOrder($customer_id, $customer_invited, $orderid = null, $total_order)
    {
        if ($total_order == '') {
            return 0;
        }
        $total_order = intval($total_order);
        if ($customer_id == 0) {
            return 1;
        }

        if ($total_order == 0) {
            return 1;
        }
        $collection = $this->_dataHelper->getModel('Affiliatehistory')
            ->getCollection()
            ->addFieldToFilter('customer_invited', $customer_invited)
            ->addFieldToFilter('customer_id', $customer_id)
            ->addFieldToFilter('status', ['nin' => [\MW\Affiliate\Model\Status::CANCELED]]);
        if ($orderid) {
            $collection->addFieldToFilter('order_id', ['nin' => [$orderid]]);
        } else {
            /** nothing */
        }
        $collection->getSelect()->group('order_id');
        if (sizeof($collection) < $total_order) {
            return 1;
        }
        if (sizeof($collection) >= $total_order) {
            return 0;
        }
    }

    // kiem tra theo total commission neu co cau hinh trong config?
    public function checkCustomerByTotalCommission($customer_id, $customer_invited, $total_commission_customer)
    {
        if ($total_commission_customer == '') {
            return 0;
        }
        $total_commission_customer = (double)$total_commission_customer;
        if ($customer_id == 0) {
            return 1;
        }
        if ($total_commission_customer == 0) {
            return 1;
        }
        if ($total_commission_customer > 0) {
            $collection = $this->_dataHelper->getModel('Affiliatehistory')
                ->getCollection()
                ->addFieldToFilter('customer_invited', $customer_invited)
                ->addFieldToFilter('customer_id', $customer_id)
                ->addFieldToFilter('status', \MW\Affiliate\Model\Status::COMPLETE);

            $collection->addExpressionFieldToSelect('history_commission_sum', 'sum(history_commission)', 'history_commission_sum');
            $total_commission = (double)$collection->getFirstItem()->getHistoryCommissionSum();

            if ($total_commission >= $total_commission_customer) {
                return 0;
            }
        }
        return 1;
    }

    public function getProgramByDiscountOld($programs, $qty, $price, $customer_invited)
    {
        $customer_id = $this->_dataHelper->getCustomerSession()->getCustomer()->getId();
        if (!$customer_id) {
            $customer_id = 0;
        }
        $array_discounts = [];
        $max = 0;
        $program_id = 0;
        foreach ($programs as $program) {
            $result_discounts = 0;
            $discounts = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getDiscount();
            if (substr_count($discounts, ',') == 0) {
                $result_discounts = $discounts;
            } elseif (substr_count($discounts, ',') >= 1) {
                $discount = explode(",", $discounts);
                if ($customer_id == 0) {
                    $result_discounts = $discount[0];
                } else {
                    $collection = $this->_dataHelper->getModel('Affiliatehistory')->getCollection()
                        ->addFieldToFilter('customer_invited', $customer_invited)
                        ->addFieldToFilter('customer_id', $customer_id)
                        ->addFieldToFilter('status', ['nin' =>[\MW\Affiliate\Model\Status::CANCELED]]);
                    $collection ->getSelect()->group('order_id');
                    $sizeof_discount = sizeof($discount);
                    $sizeo_order = sizeof($collection);
                    if ($sizeo_order < $sizeof_discount) {
                        $result_discounts = $discount[$sizeo_order];
                    } elseif ($sizeo_order >= $sizeof_discount) {
                        $result_discounts = $discount[$sizeof_discount - 1];
                    };
                };
            };
            if (substr_count($result_discounts, '%')==1) {
                $text = explode("%", $result_discounts);
                $percent = trim($text[0]);
                $array_discounts[$program]=($percent*$price*$qty)/100;
            } elseif (substr_count($result_discounts, '%')==0) {
                $array_discounts[$program]= $result_discounts*$qty;
            };
            if ($max < $array_discounts[$program]) {
                $max = $array_discounts[$program];
                $program_id = $program;
            }
        }
        return $program_id;
    }


    public function getProgramByCommissionOld($programs, $qty, $price)
    {
        $array_commissions = [];
        $max = 0;
        $program_id = 0;
        foreach ($programs as $program) {
            $result_commission = 0 ;
            $commissions = $this->_dataHelper->getModel('Affiliateprogram')->load($program)->getCommission();
            if (substr_count($commissions, ',') == 0) {
                $result_commission = $commissions;
            } elseif (substr_count($commissions, ',') >= 1) {
                $commission = explode(",", $commissions);
                $result_commission = $commission[0];
            };
            if (substr_count($result_commission, '%') == 1) {
                $text = explode("%", $result_commission);
                $percent = trim($text[0]);
                $array_commissions[$program]=($percent*$price*$qty)/100;
            } elseif (substr_count($result_commission, '%')==0) {
                $array_commissions[$program]=$result_commission*$qty;
            };
            if ($max < $array_commissions[$program]) {
                $max = $array_commissions[$program];
                $program_id = $program;
            }
        }
        return $program_id;
    }



    public function getCommissionByProgram($program_id, $qty, $price, $level)
    {
        $mw_commissions = 0;
        $commissions = $this->_dataHelper->getModel('Affiliateprogram')->load($program_id)->getCommission();
        $commission = explode(",", $commissions);
        $result_commission = isset($commission[$level-1]) ? $commission[$level-1] : 0;
        if (substr_count($result_commission, '%') == 1) {
            $text=explode("%", $result_commission);
            $percent=trim($text[0]);
            $mw_commissions = ($percent*$price*$qty)/100;
        } elseif (substr_count($result_commission, '%') == 0) {
            $mw_commissions = $result_commission * $qty ;
        }
        return $mw_commissions;
    }


    public function timedata()
    {
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Stdlib\DateTime\DateTime'
        );
        //$now = $dateTime->timestamp();

        /* add by kai - fix timeZone */
        $now = $dateTime->gmtDate();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $timezone =$objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $current =  $timezone->formatDate($now, \IntlDateFormatter::SHORT, true);
        //$now =  $dateTime->date(null,$current);
        /* end by kai - fix timeZone */
        $now = $dateTime->timestamp($current);
    }
}
