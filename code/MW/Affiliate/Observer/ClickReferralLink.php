<?php

namespace MW\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use MW\Affiliate\Model\Typeinvitation;
use MW\Affiliate\Model\Statusactive;
use MW\Affiliate\Model\Autowithdrawn;

class ClickReferralLink implements ObserverInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceAdapter;


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
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resourceAdapter
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
        $this->request = $request;
        $this->resourceAdapter = $resourceAdapter;
    }

    /**
     * TODO: Re-check referral code
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $argv)
    {

        $store_id  = $this->_storeManager->getStore()->getId();
        $storeCode = $this->_storeManager->getStore()->getCode();
        if ($this->_dataHelper->moduleEnabled($storeCode)) {
            $invite = $argv->getInvite();
            $request = $argv->getRequest();
            $referral_to = $argv->getReferralTo();
            $customers = $this->_customerFactory->create()->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())->getCollection();
            $connection = $this->resourceAdapter->getConnection();
            $where = $connection->quoteInto("md5(email)=?", $invite);
            $customers->getSelect()->where($where);
            $timeCokie = $this->_dataHelper->getTimeCookieStore($store_id);

            if (sizeof($customers)>0) {
                foreach ($customers as $customer) {
                    $customer_id = (int)$customer->getId();
                    if ($this->_dataHelper->getActiveAffiliate($customer_id) == 1 && $this->_dataHelper->getLockAffiliate($customer_id) == 0) {
                        $clientIP = $this->request->getServer('REMOTE_ADDR');
                        $cookie = $this->_dataHelper->getCookie('customer');
                        if (!$cookie) {
                            $referral_from = '';
                            if (isset($_SERVER['HTTP_REFERER'])) {
                                $referral_from = trim($_SERVER['HTTP_REFERER']);
                            }

                            if ($referral_from !='') {
                                $referral_from_domains = explode("://", $referral_from);
                                $referral_from_domain = explode("/", $referral_from_domains[1]);
                            } else {
                                $referral_from_domain[0] = '';
                            }

                            $this->_dataHelper->setCookie('customer', $customer_id, 60*60*24*$timeCokie);
                            $this->_dataHelper->setCookie('mw_referral_from', $referral_from, 60*60*24*$timeCokie);
                            $this->_dataHelper->setCookie('mw_referral_from_domain', $referral_from_domain[0], 60*60*24*$timeCokie);
                            $this->_dataHelper->setCookie('mw_referral_to', $referral_to, 60*60*24*$timeCokie);
                            $invitation_type = \MW\Affiliate\Model\Typeinvitation::REFERRAL_LINK;

                            // Calculate the commission from referral visitor
                            $affiliateCustomer = $this->_affiliatecustomersFactory->create()->load($customer_id);
                            $referralVisitorCommission = $this->_dataHelper->calculateReferralVisitorCommission($customer_id, $affiliateCustomer->getLinkClickIdPivot());

                            // Save new invitation to db
                            $invitationModel = $this->_dataHelper->getModel('Affiliateinvitation');
                            $historyData = [
                                'customer_id'            => $customer_id,
                                'email'                    => '',
                                'status'                => \MW\Affiliate\Model\Statusinvitation::CLICKLINK,
                                'ip'                    => $clientIP,
                                'count_click_link'        => 1,
                                'count_register'        => 0,
                                'count_purchase'        => 0,
                                'count_subscribe'        => 0,
                                'referral_from'            => $referral_from,
                                'referral_from_domain'    => $referral_from_domain[0],
                                'referral_to'            => $referral_to,
                                'order_id'                => '',
                                'invitation_type'        => $invitation_type,
                                'invitation_time'        => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()), // now()
                                'commission'            => $referralVisitorCommission
                            ];
                            $invitationModel->setData($historyData)->save();

                            $this->_dataHelper->saveAffiliateTransactionReferral($customer_id, $referralVisitorCommission, $customer_id, $invitation_type, \MW\Affiliate\Model\Transactiontype::REFERRAL_VISITOR);

                            // If there is enough visitors to get commission then update customer overall commission
                            if ($referralVisitorCommission > 0) {
                                // Update link_click_id_pivot
                                $affiliateCustomer->setLinkClickIdPivot($invitationModel->getId());

                                // Update total commission in affiliate_customers
                                $currentCommission = $affiliateCustomer->getTotalCommission();
                                $affiliateCustomer->setTotalCommission($currentCommission + $referralVisitorCommission);

                                // Save affiliate_customers
                                $affiliateCustomer->save();

                                // Update customer credit
                                $this->_dataHelper->insertCustomerCredit($customer_id);
                                $customerCredit = $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Creditcustomer')->load($customer_id);
                                $currentCredit = $customerCredit->getCredit();
                                $newCredit = $currentCredit + $referralVisitorCommission;
                                $customerCredit->setCredit($newCredit)->save();

                                // Update credit history table
                                $creditHistoryData = [
                                    'customer_id'            => $customer_id,
                                    'type_transaction'        => \MW\Affiliate\Model\Transactiontype::REFERRAL_VISITOR,
                                    'status'                => \MW\Affiliate\Model\Orderstatus::COMPLETE,
                                    'transaction_detail'    => $invitationModel->getId(),
                                    'amount'                => $referralVisitorCommission,
                                    'beginning_transaction'    => $currentCredit,
                                    'end_transaction'        => $newCredit,
                                    'created_time'            => date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()) //now()
                                ];
                                $this->_dataHelper->getModelExtensions('\MW\Affiliate\Model\Credithistory')->setData($creditHistoryData)->save();
                            }
                        }
                    }
                }
            }
        }
    }
}
