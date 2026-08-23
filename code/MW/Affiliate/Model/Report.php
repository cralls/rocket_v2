<?php

namespace MW\Affiliate\Model;

use MW\Affiliate\Model\Admin\ReportRange;
use MW\Affiliate\Model\Status;

class Report extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_pricingHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_appResource;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \MW\Affiliate\Model\AffiliatetransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatecustomersFactory
     */
    protected $_affiliatecustomersFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatehistoryFactory
     */
    protected $_historyFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliatewithdrawnFactory
     */
    protected $_withdrawnFactory;

    /**
     * @var \MW\Affiliate\Model\AffiliateinvitationFactory
     */
    protected $_invitationFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\App\ResourceConnection $appResource
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \MW\Affiliate\Helper\Data $dataHelper
     * @param AffiliatetransactionFactory $transactionFactory
     * @param AffiliatecustomersFactory $affiliatecustomersFactory
     * @param AffiliatehistoryFactory $historyFactory
     * @param AffiliatewithdrawnFactory $withdrawnFactory
     * @param \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\App\ResourceConnection $appResource,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MW\Affiliate\Helper\Data $dataHelper,
        \MW\Affiliate\Model\AffiliatetransactionFactory $transactionFactory,
        \MW\Affiliate\Model\AffiliatecustomersFactory $affiliatecustomersFactory,
        \MW\Affiliate\Model\AffiliatehistoryFactory $historyFactory,
        \MW\Affiliate\Model\AffiliatewithdrawnFactory $withdrawnFactory,
        \MW\Affiliate\Model\AffiliateinvitationFactory $invitationFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_storeManager = $storeManager;
        $this->_localeDate = $localeDate;
        $this->_pricingHelper = $pricingHelper;
        $this->_appResource = $appResource;
        $this->_localeCurrency = $localeCurrency;
        $this->_dateTime = $dateTime;
        $this->_orderFactory = $orderFactory;
        $this->_customerFactory = $customerFactory;
        $this->_dataHelper = $dataHelper;
        $this->_transactionFactory = $transactionFactory;
        $this->_affiliatecustomersFactory = $affiliatecustomersFactory;
        $this->_historyFactory = $historyFactory;
        $this->_withdrawnFactory = $withdrawnFactory;
        $this->_invitationFactory = $invitationFactory;
    }

    public function prepareCollectionFrontend($data)
    {
        // Query to get my group customer_id
        switch ($data['report_range']) {
            case ReportRange::REPORT_RAGE_LAST_24H:
            case ReportRange::REPORT_RAGE_LAST_7DAYS:
            case ReportRange::REPORT_RAGE_LAST_30DAYS:
                $date = date('Y-m-d H:i:s');
                break;
            case ReportRange::REPORT_RAGE_LAST_WEEK:
                $date = date('Y-m-d H:i:s', strtotime("Sunday Last Week"));
                break;
            case ReportRange::REPORT_RAGE_LAST_MONTH:
                $date = date('Y-m-d H:i:s', strtotime("last day of last month"));
                break;
            case ReportRange::REPORT_RAGE_CUSTOM:
                $date = date('Y-m-d H:i:s', strtotime($data['to']));
                break;
            default:
                $date = date('Y-m-d H:i:s');
        }

        $arrayMembers = [];
        array_push($arrayMembers, $data['customer_id']);
        $this->_getArrayCustomers($data['customer_id'], $date, $arrayMembers);

        $storeCode = $this->_storeManager->getStore()->getCode();
        $orderStatusAddCommission = $this->_dataHelper->getStoreConfig('affiliate/general/status_add_commission', $storeCode);

        switch ($orderStatusAddCommission) {
            case 'pending':
                $orderStatusAdd = ['pending', 'processing', 'complete'];
                break;
            case 'processing':
                $orderStatusAdd = ['processing', 'complete'];
                break;
            case 'complete':
                $orderStatusAdd = ['complete'];
                break;
            default:
                $orderStatusAdd = [$orderStatusAddCommission, 'complete'];
                break;
        }

        // Query to get My Total Affiliate Child
        $countChilds = 0;
        $this->_countAffiliateChilds($data['customer_id'], $date, $countChilds);

        // Query to get my Sales
        $collection = $this->_orderFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.customer_id', ['in' => $arrayMembers]);
        $collection->addFieldToFilter('main_table.status', ['in' => $orderStatusAdd]);
        $collection->addExpressionFieldToSelect('total_sales_sum', 'sum(main_table.subtotal)', 'total_sales_sum');
        $this->_buildCollection($collection, $data, true, 'updated_at');
        $collectionSalesSum = $collection;

        // Query to get my Commission
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.customer_invited', $data['customer_id']);
        $collection->addExpressionFieldToSelect('total_commission_sum', 'SUM(total_commission)', 'total_commission_sum');
        $this->_buildCollection($collection, $data, true, 'transaction_time');
        $collectionCommissionSum = $collection;

        // Query to get My Total Sales
        $collection = $this->_orderFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', ['in' => $orderStatusAdd]);
        $collection->addFieldToFilter('main_table.customer_id', $data['customer_id']);
        $collection->addExpressionFieldToSelect('total_affiliate_sales', 'main_table.subtotal', 'total_affiliate_sales');
        $this->_buildCollection($collection, $data, false, 'updated_at');
        $collectionMySales = $collection;

        // Query to get My Total Commission
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.customer_invited', $data['customer_id']);
        $collection->addExpressionFieldToSelect('total_affiliate_commission', 'if(sum(total_commission),sum(total_commission),0)', 'total_affiliate_commission');
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionMyCommission = $collection->getFirstItem();


        // Query to get Invitation Report
        $collection = $this->_invitationFactory->create()->getCollection()
            ->setReportInvitation($data['customer_id']);
        $this->_buildCollection($collection, $data, false, 'invitation_time');
        $invitationReport = [
            'click' => 0,
            'register' => 0,
            'purchase' => 0,
            'subscribe' => 0
        ];
        foreach ($collection as $invitation) {
            $invitationReport['click']      = $invitation->getCountClickLinkSum();
            $invitationReport['register']  = $invitation->getCountRegisterSum();
            $invitationReport['purchase']  = $invitation->getCountPurchaseSum();
            $invitationReport['subscribe'] = $invitation->getCountSubscribeSum();
            break;
        }

        $collectionDiscountSum = null;

        switch ($data['report_range']) {
            case ReportRange::REPORT_RAGE_LAST_24H:
                $_time = $this->_getPreviousDateTime(24);
                $start24hTime = $this->_localeDate->formatDate(
                    date('Y-m-d h:i:s', $_time),
                    \IntlDateFormatter::MEDIUM,
                    true
                );
                $start24hTime = strtotime($start24hTime);
                $startTime = [
                    'h' => (int)date('H', $start24hTime),
                    'd' => (int)date('d', $start24hTime),
                    'm' => (int)date('m', $start24hTime),
                    'y' => (int)date('Y', $start24hTime)
                ];
                $rangeDate = $this->_buildArrayDate(
                    ReportRange::REPORT_RAGE_LAST_24H,
                    $startTime['h'],
                    $startTime['h'] + 24,
                    $startTime
                );

                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionCommissionSum,
                    'hour',
                    $rangeDate
                );

                $_data['report']['date_start'] = $startTime;
                break;
            case ReportRange::REPORT_RAGE_LAST_WEEK:
                $startTime = strtotime("-6 day", strtotime("Sunday Last Week"));
                $startDay = date('d', $startTime);
                $endDay = date('d', strtotime("Sunday Last Week"));
                $rangeDate = $this->_buildArrayDate(
                    ReportRange::REPORT_RAGE_LAST_WEEK,
                    $startDay,
                    $endDay
                );

                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionCommissionSum,
                    'day',
                    $rangeDate
                );

                $_data['report']['date_start'] = [
                    'd'   => (int)date('d', $startTime),
                    'm'   => (int)date('m', $startTime),
                    'y'   => (int)date('Y', $startTime),
                ];
                break;
            case ReportRange::REPORT_RAGE_LAST_MONTH:
                $lastMonthTime = strtotime($this->_getLastMonthTime());
                $lastMonth = date('m', $lastMonthTime);
                $startDay = 1;
                $endDay = $this->_daysInMonth($lastMonth);
                $rangeDate = $this->_buildArrayDate(
                    ReportRange::REPORT_RAGE_LAST_MONTH,
                    $startDay,
                    $endDay
                );

                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionCommissionSum,
                    'day',
                    $rangeDate
                );

                $_data['report']['date_start'] = [
                    'd'   => $startDay,
                    'm'   => (int)$lastMonth,
                    'y'   => (int)date('Y', $lastMonthTime),
                    'total_day' => $endDay
                ];
                break;
            case ReportRange::REPORT_RAGE_LAST_7DAYS:
            case ReportRange::REPORT_RAGE_LAST_30DAYS:
                $lastXDay = 0;
                if ($data['report_range'] == ReportRange::REPORT_RAGE_LAST_7DAYS) {
                    $lastXDay = 7;
                } elseif ($data['report_range'] == ReportRange::REPORT_RAGE_LAST_30DAYS) {
                    $lastXDay = 30;
                }

                $startDay = date('Y-m-d h:i:s', strtotime('-'.$lastXDay.' day', (new \DateTime())->getTimestamp()));
                $endDay = date('Y-m-d h:i:s', strtotime("-1 day"));

                $originalTime = [
                    'from'  => $startDay,
                    'to'    => $endDay
                ];
                $rangeDate = $this->_buildArrayDate(
                    ReportRange::REPORT_RAGE_CUSTOM,
                    0,
                    0,
                    $originalTime
                );

                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionCommissionSum,
                    'multiday',
                    $rangeDate
                );
                break;
            case ReportRange::REPORT_RAGE_CUSTOM:
                $originalTime = [
                    'from'  => $data['from'],
                    'to'    => $data['to']
                ];
                $rangeDate = $this->_buildArrayDate(
                    ReportRange::REPORT_RAGE_CUSTOM,
                    0,
                    0,
                    $originalTime
                );

                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionCommissionSum,
                    'multiday',
                    $rangeDate
                );
                break;
        }

        $totalAffiliateSales = null;
        $totalAffiliateOrder = 0;
        foreach ($collectionMySales as $order) {
            $totalAffiliateSales += $order->getData('total_affiliate_sales');
            $totalAffiliateOrder += 1;
        }


        $_data['statistics']['total_affiliate_sales'] = ($totalAffiliateSales == null) ? $this->_pricingHelper->currency(0) :  $this->_pricingHelper->currency($totalAffiliateSales);
        $_data['statistics']['total_affiliate_commission'] = ($collectionMyCommission->getTotalAffiliateCommission() == null) ? $this->_pricingHelper->currency(0) : $this->_pricingHelper->currency($collectionMyCommission->getTotalAffiliateCommission());
        $_data['statistics']['total_affiliate_order'] = ($totalAffiliateOrder == 0) ? 0 :  number_format($totalAffiliateOrder, 0, '.', ',');
        $_data['statistics']['total_affiliate_child'] = number_format($countChilds, 0, '.', ',');
        $piechart = $this->preapareCollectionPieChartFrontend($data, $collectionMyCommission->getData('total_affiliate_commission'));
        $_data['report_commission_by_members'] = json_encode($piechart['commission_by_members']);
        $_data['report_commission_by_programs'] = json_encode($piechart['commission_by_programs']);
        $_data['report']['title'] = __('My Total Sales / My Commission ');
        $_data['report']['curency'] = $this->_localeCurrency->getCurrency(
            $this->_storeManager->getStore()->getCurrentCurrencyCode()
        )->getSymbol();
        $_data['invitation_report'] = $invitationReport;

        return json_encode($_data);
    }

    public function prepareCollection($data)
    {
        $customerTable = $this->_appResource->getTableName('customer_entity');

        // Query to get Affiliate
        switch ($data['report_range']) {
            case ReportRange::REPORT_RAGE_LAST_24H:
            case ReportRange::REPORT_RAGE_LAST_7DAYS:
            case ReportRange::REPORT_RAGE_LAST_30DAYS:
                $date = date('Y-m-d H:i:s');
                break;
            case ReportRange::REPORT_RAGE_LAST_WEEK:
                $date = date('Y-m-d H:i:s', strtotime("Sunday Last Week"));
                break;
            case ReportRange::REPORT_RAGE_LAST_MONTH:
                $date = date('Y-m-d H:i:s', strtotime("last day of last month"));
                break;
            case ReportRange::REPORT_RAGE_CUSTOM:
                $date = date('Y-m-d H:i:s', strtotime($data['to']));
                break;
            default:
                $date = date('Y-m-d H:i:s');
        }

        // Count affiliate
        $collection = $this->_affiliatecustomersFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.status', 1);
        $collection->addFieldToFilter('customer_time', ['from' => null, 'to' => $date]);
        $collection->addFieldToFilter('referral_code', ['neq' => '']);
        $countAffiliate = $collection->getSize();

        // Get array affiliate
        $affIds = [];
        foreach ($collection as $aff) {
            array_push($affIds, $aff->getCustomerId());
        }

        // Get array customer
        $collection = $this->_affiliatecustomersFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.status', 1);
        $collection->addFieldToFilter('customer_time', ['from' => null, 'to' => $date]);
        $affiliateIds = [];
        foreach ($collection->getData() as $aff) {
            array_push($affiliateIds, $aff['customer_id']);
        }

        $storeCode = $this->_storeManager->getStore()->getCode();
        $orderStatusAddCommission = $this->_dataHelper->getStoreConfig('affiliate/general/status_add_commission', $storeCode);

        switch ($orderStatusAddCommission) {
            case 'pending':
                $orderStatusAdd = ['pending', 'processing', 'complete'];
                break;
            case 'processing':
                $orderStatusAdd = ['processing', 'complete'];
                break;
            case 'complete':
                $orderStatusAdd = ['complete'];
                break;
            default:
                $orderStatusAdd = [$orderStatusAddCommission];
                break;
        }
        // Query to get order
        $collection = $this->_orderFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', ['in' => $orderStatusAdd]);
        $collection->addFieldToFilter('main_table.customer_id', ['in' => $affiliateIds]);
        $this->_buildCollection($collection, $data, false, 'updated_at');
        $numberOrder = $collection->getSize();

        // Query to get Sales
        $collection = $this->_orderFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', ['in' => $orderStatusAdd]);
        $collection->addFieldToFilter('main_table.customer_id', ['in' => $affiliateIds]);
        $collection->addExpressionFieldToSelect('total_sales_sum', 'sum(main_table.subtotal)', 'total_sales_sum');
        $this->_buildCollection($collection, $data, true, 'updated_at');
        $collectionSalesSum = $collection;

        // Query to get Discount
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldtoFilter('main_table.customer_invited', 0);
        $collection->addExpressionFieldToSelect('total_discount_sum', 'SUM(total_discount)', 'total_discount_sum');
        $this->_buildCollection($collection, $data, true, 'transaction_time');
        $collectionDiscountSum = $collection;

        // Query to get Commission
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldtoFilter('main_table.customer_invited', 0);
        $collection->addExpressionFieldToSelect('total_commission_sum', 'SUM(total_commission)', 'total_commission_sum');
        $this->_buildCollection($collection, $data, true, 'transaction_time');
        $collectionAffiliate = $collection;

        // Query to statistic withdrawal
        $collection = $this->_withdrawnFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addExpressionFieldToSelect('total_withdrawn', 'if(sum(withdrawn_amount),sum(withdrawn_amount),0)', 'total_withdrawn');
        $collection->addExpressionFieldToSelect('total_fee', 'if(sum(fee),sum(fee),0)', 'total_fee');
        $collection->addExpressionFieldToSelect('total_count', 'sum(1)', 'total_count');
        $this->_buildCollection($collection, $data, false, 'withdrawn_time');
        $collectionStatsWithdrawn = $collection->getFirstItem();

        // Query to statistic total transactions
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('customer_invited', ['neq' => 0]);
        $collection->addExpressionFieldToSelect('total_transaction', 'sum(if(main_table.status = 4,2,1))', 'total_transaction');
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionStatsTransaction = $collection->getFirstItem();

        $collection = $this->_withdrawnFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addExpressionFieldToSelect('total_count', 'sum(if(main_table.status = 3,2,1))', 'total_count');
        $this->_buildCollection($collection, $data, false, 'withdrawn_time');
        $collectionTransactionWithdrawn = $collection->getFirstItem();

        $totalTransaction = $collectionStatsTransaction->getTotalTransaction() + $collectionTransactionWithdrawn->getTotalCount();

        // Query to get Orders
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->getSelect()->group(['order_id']);
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $arrayOrders = [];
        foreach ($collection as $order) {
            array_push($arrayOrders, $order->getOrderId());
        }

        // Statistic top afiliate sales
        // Calculate Sales in one Level for each Affiliate
        $arrayAffiliatesSales = [];
        foreach ($affIds as $affId) {
            $arrayAffiliatesSales[$affId] = $this->getTotalAffiliateSales($affId, $data, $orderStatusAdd, $date);
        }
        arsort($arrayAffiliatesSales);
        $collectionsAff = $this->_customerFactory->create()->getCollection();
        $collectionsAff->addFieldToFilter('entity_id', ['in' => $affIds]);
        $collectionsAff = $collectionsAff->getData();

        $affSales = [];
        $count = 0;
        foreach ($arrayAffiliatesSales as $key => $value) {
            if ($count == 15) {
                break;
            }

            $buff = [];
            foreach ($collectionsAff as $aff) {
                if ($aff['entity_id'] == $key) {
                    $buff['affiliate'] = $aff['email'];
                }
            }

            $buff['affiliate_id'] = $key;
            $buff['total_affiliate_sales'] = $value;
            array_push($affSales, $buff);
            $count++;
        }

        // Query to statistic top afiliate commission
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->getSelect()->join(
            ['customer_entity' => $customerTable],
            'main_table.customer_invited = customer_entity.entity_id',
            []
        );
        $collection->addExpressionFieldToSelect('affiliate', 'customer_entity.email', 'affiliate');
        $collection->addExpressionFieldToSelect('total_affiliate_sales', 'sum(0)', 'total_affiliate_sales');
        $collection->addExpressionFieldToSelect('affiliate_id', 'main_table.customer_invited', 'affiliate_id');
        $collection->addExpressionFieldToSelect('total_affiliate_commission', 'if(sum(total_commission),sum(total_commission),0)', 'total_affiliate_commission');
        $collection->getSelect()->group(['main_table.customer_invited']);
        $collection->getSelect()->order('total_affiliate_commission DESC');
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionTopAffiliateCommission = $collection->getData();

        // Processing Top Affiliate
        $arrayTopSales = [];
        $arrayTopCommission = [];
        $collectionTopAffiliate = $affSales;

        foreach ($collectionTopAffiliate as $affiliate) {
            array_push($arrayTopSales, $affiliate['affiliate_id']);
        }
        foreach ($collectionTopAffiliateCommission as $affiliate) {
            array_push($arrayTopCommission, $affiliate['affiliate_id']);
        }

        if (sizeof($collectionTopAffiliate) == 0) {
            $collectionTopAffiliate = $collectionTopAffiliateCommission;
        } else {
            $topCommissionAddMore = array_diff($arrayTopCommission, $arrayTopSales);
            foreach ($collectionTopAffiliate as &$affiliate) {
                $totalCommission = 0;
                foreach ($collectionTopAffiliateCommission as $aff) {
                    if ($affiliate['affiliate_id'] == $aff['affiliate_id']) {
                        $totalCommission = $aff['total_affiliate_commission'];
                        break;
                    }
                }
                $affiliate['total_affiliate_commission'] = $totalCommission;
            }

            foreach ($topCommissionAddMore as $affiliateId) {
                $affAdd = [];
                $affAdd['entity_id'] = 0;
                $affAdd['total_affiliate_sales'] = 0;
                $affAdd['affiliate_id'] = $affiliateId;

                foreach ($collectionTopAffiliateCommission as $aff) {
                    if ($affiliateId == $aff['affiliate_id']) {
                        $affAdd['affiliate'] = $aff['affiliate'];
                        $affAdd['total_affiliate_commission'] = $aff['total_affiliate_commission'];
                        break;
                    }
                }
                array_push($collectionTopAffiliate, $affAdd);
            }
        }

        switch ($data['report_range']) {
            case ReportRange::REPORT_RAGE_LAST_24H:
                $_time = $this->_getPreviousDateTime(24);
                $start24hTime = $this->_localeDate->formatDate(
                    date('Y-m-d h:i:s', $_time),
                    \IntlDateFormatter::MEDIUM,
                    true
                );
                $start24hTime = strtotime($start24hTime);
                $startTime = [
                    'h' => (int)date('H', $start24hTime),
                    'd' => (int)date('d', $start24hTime),
                    'm' => (int)date('m', $start24hTime),
                    'y' => (int)date('Y', $start24hTime)
                ];

                $rangeDate = $this->_buildArrayDate(ReportRange::REPORT_RAGE_LAST_24H, $startTime['h'], $startTime['h'] + 24, $startTime);
                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionAffiliate,
                    'hour',
                    $rangeDate
                );
                $_data['report']['date_start'] = $startTime;
                break;
            case ReportRange::REPORT_RAGE_LAST_WEEK:
                $startTime = strtotime("-6 day", strtotime("Sunday Last Week"));
                $startDay = date('d', $startTime);
                $endDay = date('d', strtotime("Sunday Last Week"));
                $rangeDate = $this->_buildArrayDate(ReportRange::REPORT_RAGE_LAST_WEEK, $startDay, $endDay);
                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionAffiliate,
                    'day',
                    $rangeDate
                );
                $_data['report']['date_start'] = [
                    'd' => (int)date('d', $startTime),
                    'm' => (int)date('m', $startTime),
                    'y' => (int)date('Y', $startTime)
                ];
                break;
            case ReportRange::REPORT_RAGE_LAST_MONTH:
                $lastMonthTime = strtotime($this->_getLastMonthTime());
                $lastMonth = date('m', $lastMonthTime);
                $startDay = 1;
                $endDay = $this->_daysInMonth($lastMonth);
                $rangeDate = $this->_buildArrayDate(ReportRange::REPORT_RAGE_LAST_MONTH, $startDay, $endDay);
                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionAffiliate,
                    'day',
                    $rangeDate
                );
                $_data['report']['date_start'] = [
                    'd' => $startDay,
                    'm' => (int)$lastMonth,
                    'y' => (int)date('Y', $lastMonthTime),
                    'total_day' => $endDay
                ];
                break;
            case ReportRange::REPORT_RAGE_LAST_7DAYS:
            case ReportRange::REPORT_RAGE_LAST_30DAYS:
                $lastXDay = 0;
                if ($data['report_range'] == ReportRange::REPORT_RAGE_LAST_7DAYS) {
                    $lastXDay = 7;
                } elseif ($data['report_range'] == ReportRange::REPORT_RAGE_LAST_30DAYS) {
                    $lastXDay = 30;
                }

                $startDay = date('Y-m-d h:i:s', strtotime('-'.$lastXDay.' day', (new \DateTime())->getTimestamp()));
                $endDay = date('Y-m-d h:i:s', strtotime("-1 day"));

                $originalTime = [
                    'from'  => $startDay,
                    'to'    => $endDay
                ];
                $rangeDate = $this->_buildArrayDate(ReportRange::REPORT_RAGE_CUSTOM, 0, 0, $originalTime);

                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionAffiliate,
                    'multiday',
                    $rangeDate
                );
                break;
            case ReportRange::REPORT_RAGE_CUSTOM:
                $originalTime = [
                    'from'  => $data['from'],
                    'to'    => $data['to']
                ];
                $rangeDate = $this->_buildArrayDate(ReportRange::REPORT_RAGE_CUSTOM, 0, 0, $originalTime);
                $_data = $this->_buildResult(
                    $collectionSalesSum,
                    $collectionDiscountSum,
                    $collectionAffiliate,
                    'multiday',
                    $rangeDate
                );
                break;
        }

        $_data['statistics']['total_affiliate_sales'] = $this->_pricingHelper->currency(0);
        $_data['statistics']['total_affiliate_commission'] = $this->_pricingHelper->currency(0);
        $_data['statistics']['avg_commission_per_order'] =  $this->_pricingHelper->currency(0);
        $_data['statistics']['avg_commission_per_affiliate'] = $this->_pricingHelper->currency(0);
        $_data['statistics']['avg_sales_per_order'] = $this->_pricingHelper->currency(0);
        $_data['statistics']['avg_sales_per_affiliate'] = $this->_pricingHelper->currency(0);
        $_data['statistics']['total_affiliate_order'] = 0;
        $_data['statistics']['total_affiliate']['all_actived'] = $countAffiliate;

        $collectionStatsSales = [];
        $collectionStatsSales['total_affiliate_order'] = ($numberOrder == null) ? 0 : $numberOrder;
        $collectionStatsSales['total_affiliate_sales'] = 0;
        $collectionStatsSales['total_affiliate_commission'] = 0;
        $collectionStatsSales['avg_commission_per_order'] = 0;
        $collectionStatsSales['avg_commission_per_affiliate'] = 0;
        $collectionStatsSales['avg_sales_per_order'] = 0;
        $collectionStatsSales['avg_sales_per_affiliate'] = 0;

        foreach ($collectionSalesSum as $order) {
            $collectionStatsSales['total_affiliate_sales'] += floatval($order->getTotalSalesSum());
        }

        foreach ($collectionAffiliate as $commission) {
            $collectionStatsSales['total_affiliate_commission'] += floatval($commission->getTotalCommissionSum());
        }

        if ($collectionStatsSales['total_affiliate_order'] == 0) {
            $collectionStatsSales['avg_commission_per_order'] = 0;
        } else {
            $collectionStatsSales['avg_commission_per_order'] = $collectionStatsSales['total_affiliate_commission'] / $collectionStatsSales['total_affiliate_order'];
        }

        if ($countAffiliate == 0) {
            $collectionStatsSales['avg_commission_per_affiliate'] = 0;
        } else {
            $collectionStatsSales['avg_commission_per_affiliate'] = $collectionStatsSales['total_affiliate_commission'] / $countAffiliate;
        }

        if ($collectionStatsSales['total_affiliate_order'] == 0) {
            $collectionStatsSales['avg_sales_per_order'] = 0;
        } else {
            $collectionStatsSales['avg_sales_per_order'] = $collectionStatsSales['total_affiliate_sales'] / $collectionStatsSales['total_affiliate_order'];
        }

        if ($countAffiliate == 0) {
            $collectionStatsSales['avg_sales_per_affiliate'] = 0;
        } else {
            $collectionStatsSales['avg_sales_per_affiliate'] = $collectionStatsSales['total_affiliate_sales'] / $countAffiliate;
        }

        foreach ($collectionStatsSales as $key => $stat) {
            switch ($key) {
                case "total_affiliate_order":
                    $_data['statistics'][$key] = ($stat == null) ? 0 : number_format($stat, 0, '.', ',');
                    break;
                case "total_affiliate_sales":
                case "total_affiliate_commission":
                case "avg_commission_per_order":
                case "avg_commission_per_affiliate":
                case "avg_sales_per_order":
                case "avg_sales_per_affiliate":
                    $_data['statistics'][$key] = ($stat == null) ? $this->_pricingHelper->currency(0) : $this->_pricingHelper->currency($stat);
                    break;
            }
        }
        $_data['statistics']['total_withdrawn'] = $this->_pricingHelper->currency(0);
        $_data['statistics']['total_fee'] = $this->_pricingHelper->currency(0);

        foreach ($collectionStatsWithdrawn->getData() as $key => $stat) {
            switch ($key) {
                case "total_withdrawn":
                case "total_fee":
                    $_data['statistics'][$key] = ($stat == null) ? $this->_pricingHelper->currency(0) : $this->_pricingHelper->currency($stat);
                    break;
            }
        }

        $_data['topaffiliate'] = null;
        $count = 0;

        foreach ($collectionTopAffiliate as &$affiliate) {
            foreach ($affiliate as $key => $value) {
                switch ($key) {
                    case "affiliate":
                        $_data['topaffiliate'][$count][$key] = ($value == null) ? '' : $value;
                        break;
                    case "total_affiliate_sales":
                        $_data['topaffiliate'][$count][$key] = ($value == null) ? $this->_pricingHelper->currency(0) : $this->_pricingHelper->currency($value);
                        break;
                    case "total_affiliate_commission":
                        $_data['topaffiliate'][$count][$key] = ($value == null) ? $this->_pricingHelper->currency(0) : $this->_pricingHelper->currency($value);
                        break;
                }
            }

            $count++;
            if ($count == 10) {
                break;
            }
        }

        $piechart = $this->preapareCollectionPieChart($data, $collectionStatsSales['total_affiliate_sales'], $collectionStatsSales['total_affiliate_commission']);
        $_data['statistics']['total_transaction'] = number_format($totalTransaction, 0, '.', ',');
        $_data['report_sales_by_programs'] = json_encode($piechart['sales']);
        $_data['report_commission_by_programs'] = json_encode($piechart['commission']);
        $_data['report']['title'] = __('Sales Generated By Affiliates / Affiliate Commission / Referred Customer Discount');
        $_data['report']['curency'] = $this->_localeCurrency->getCurrency(
            $this->_storeManager->getStore()->getCurrentCurrencyCode()
        )->getSymbol();

        return json_encode($_data);
    }

    /**
     * @param $collectionSalesSum
     * @param $collectionDiscountSum
     * @param $collectionAffiliate
     * @param $type
     * @param $rangeDate
     * @return array
     */
    protected function _buildResult($collectionSalesSum, $collectionDiscountSum, $collectionAffiliate, $type, $rangeDate)
    {
        $_data = [];

        if ($type == 'multiday') {
            foreach ($rangeDate as $year => $months) {
                foreach ($months as $month => $days) {
                    foreach ($days as $day) {
                        $_data['report']['commission'][$year."-".$month."-".$day] = [$year, $month, $day, 0];
                        $_data['report']['sales'][$year."-".$month."-".$day] = [$year, $month, $day, 0];
                        $_data['report']['discount'][$year."-".$month."-".$day] = [$year, $month, $day, 0];
                    }

                    foreach ($collectionAffiliate as $commission) {
                        if ($commission->getMonth() == $month) {
                            foreach ($days as $day) {
                                if ($commission->getDay() == $day) {
                                    $_data['report']['commission'][$year."-".$month."-".$day] = [$year, $month, $day, floatval($commission->getTotalCommissionSum())];
                                }
                            }
                        }
                    }

                    foreach ($collectionSalesSum as $sale) {
                        if ($sale->getMonth() == $month) {
                            foreach ($days as $day) {
                                if ($sale->getDay() == $day) {
                                    $_data['report']['sales'][$year."-".$month."-".$day][3] += floatval($sale->getTotalSalesSum());
                                }
                            }
                        }
                    }

                    if ($collectionDiscountSum != null) {
                        foreach ($collectionDiscountSum as $discount) {
                            if ($discount->getMonth() == $month) {
                                foreach ($days as $day) {
                                    if ($discount->getDay() == $day) {
                                        $_data['report']['discount'][$year."-".$month."-".$day][3] += floatval($discount->getTotalDiscountSum());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } else {
            switch ($type) {
                case 'hour':
                    $rangeTempDate = reset($rangeDate);
                    $i = $rangeTempDate['incr_hour'];
                    break;
                case 'day':
                    $rangeTempDate = reset($rangeDate);
                    $i = 0; //$rangeTempDate['count_day'];
                    break;
                default:
                    $i = 0;
                    break;
            }

            foreach ($rangeDate as $date) {
                switch ($type) {
                    case 'hour':
                        $count = $date['native_hour'];
                        break;
                    case 'day':
                        $count = $date['native_day'];
                        break;
                    default:
                        $count = 0;
                        break;
                }

                $_data['report']['commission'][$i] = 0;
                $_data['report']['sales'][$i] = 0;
                $_data['report']['discount'][$i] = 0;

                foreach ($collectionAffiliate as $commission) {
                    if ((int)$commission->{"get$type"}() == $count) {
                        if (isset($date['day']) && $date['day'] == (int)$commission->getDay()) {
                            $_data['report']['commission'][$i] = floatval($commission->getTotalCommissionSum());
                        } elseif (!isset($date['day'])) {
                            $_data['report']['commission'][$i] = floatval($commission->getTotalCommissionSum());
                        }
                    }
                }

                foreach ($collectionSalesSum as $sale) {
                    if ((int)$sale->{"get$type"}() == $count) {
                        if (isset($date['day']) && $date['day'] == (int)$sale->getDay()) {
                            $_data['report']['sales'][$i] += floatval($sale->getTotalSalesSum());
                        } elseif (!isset($date['day'])) {
                            $_data['report']['sales'][$i] += floatval($sale->getTotalSalesSum());
                        }
                    }
                }

                if ($collectionDiscountSum != null) {
                    foreach ($collectionDiscountSum as $discount) {
                        if ((int)$discount->{"get$type"}() == $count) {
                            if (isset($date['day']) && $date['day'] == (int)$discount->getDay()) {
                                $_data['report']['discount'][$i] += floatval($discount->getTotalDiscountSum());
                            } elseif (!isset($date['day'])) {
                                $_data['report']['discount'][$i] += floatval($discount->getTotalDiscountSum());
                            }
                        }
                    }
                }

                $i++;
            }
        }

        $_data['report']['commission'] = array_values($_data['report']['commission']);
        $_data['report']['sales'] = array_values($_data['report']['sales']);
        $_data['report']['discount'] = array_values($_data['report']['discount']);

        return $_data;
    }

    /**
     * @param $collection
     * @param $data
     * @param bool|true $group
     * @param $timeString
     */
    protected function _buildCollection(&$collection, $data, $group = true, $timeString)
    {
        switch ($data['report_range']) {
            case ReportRange::REPORT_RAGE_LAST_24H:
                // Last 24h
                $_hour = date('Y-m-d h:i:s', strtotime('-1 day', (new \DateTime())->getTimestamp()));
                $startHour = $this->_localeDate->formatDate(
                    $_hour,
                    \IntlDateFormatter::MEDIUM,
                    true
                );
                $_hour = date('Y-m-d h:i:s', strtotime("now"));
                $endHour = $this->_localeDate->formatDate(
                    $_hour,
                    \IntlDateFormatter::MEDIUM,
                    true
                );

                if ($group == true) {
                    $collection->addExpressionFieldToSelect('hour', 'HOUR(CONVERT_TZ('.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\'))', 'hour');
                    $collection->addExpressionFieldToSelect('day', 'DAY(CONVERT_TZ('.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\'))', 'day');
                    $collection->getSelect()->group(['hour']);
                }

                $where = 'CONVERT_TZ(main_table.'.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where.' >= "'.$startHour.'" AND '.$where.' <= "'.$endHour.'"');
                break;
            case ReportRange::REPORT_RAGE_LAST_WEEK:
                // Last week
                $startDay = date('Y-m-d', strtotime("-7 day", strtotime("Sunday Last Week")));
                $endDay = date('Y-m-d', strtotime("Sunday Last Week"));
                if ($group == true) {
                    $collection->addExpressionFieldToSelect('day', 'DAY('.$timeString.')', 'day');
                    $collection->getSelect()->group(['day']);
                }

                $where = 'CONVERT_TZ(main_table.'.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where.' >= "'.$startDay.'" AND '.$where.' <= "'.$endDay.'"');
                break;
            case ReportRange::REPORT_RAGE_LAST_MONTH:
                // Last month
                $lastMonthTime = $this->_getLastMonthTime();
                $lastMonth = date('m', strtotime($lastMonthTime));
                $startDay = date('Y', strtotime($lastMonthTime))."-".$lastMonth."-1";
                $endDay = date('Y', strtotime($lastMonthTime))."-".$lastMonth."-".$this->_daysInMonth($lastMonth);

                // Next one day
                $endDay = strtotime($endDay.' +1 day');
                $endDay = date('Y', $endDay)."-".date('m', $endDay)."-".date('d', $endDay);

                if ($group == true) {
                    $collection->addExpressionFieldToSelect('day', 'DAY('.$timeString.')', 'day');
                    $collection->getSelect()->group(['day']);
                }

                $where = 'CONVERT_TZ(main_table.'.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where.' >= "'.$startDay.'" AND '.$where.' <= "'.$endDay.'"');
                break;
            case ReportRange::REPORT_RAGE_LAST_7DAYS:
            case ReportRange::REPORT_RAGE_LAST_30DAYS:
                // Last X days
                if ($data['report_range'] == ReportRange::REPORT_RAGE_LAST_7DAYS) {
                    $lastXDay = 7;
                } elseif ($data['report_range'] == ReportRange::REPORT_RAGE_LAST_30DAYS) {
                    $lastXDay = 30;
                } else {
                    $lastXDay = 0;
                }

                $startDay = date('Y-m-d h:i:s', strtotime('-'.$lastXDay.' day', (new \DateTime())->getTimestamp()));
                $endDay = date('Y-m-d h:i:s', strtotime("-1 day"));

                if ($group == true) {
                    $collection->getSelect()->group(['day']);
                }

                $collection->addExpressionFieldToSelect('month', 'MONTH('.$timeString.')', 'month');
                $collection->addExpressionFieldToSelect('day', 'DAY('.$timeString.')', 'day');
                $collection->addExpressionFieldToSelect('year', 'YEAR('.$timeString.')', 'year');

                $where = 'CONVERT_TZ(main_table.'.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where.' >= "'.$startDay.'" AND '.$where.' <= "'.$endDay.'"');
                break;
            case ReportRange::REPORT_RAGE_CUSTOM:
                // Custom range
                if ($group == true) {
                    $collection->addExpressionFieldToSelect('month', 'MONTH('.$timeString.')', 'month');
                    $collection->addExpressionFieldToSelect('day', 'DAY('.$timeString.')', 'day');
                    $collection->addExpressionFieldToSelect('year', 'YEAR('.$timeString.')', 'year');
                    $collection->getSelect()->group(['day']);
                }

                $startDay = date('Y-m-d h:i:s', strtotime($data['from']));
                $endDay = date('Y-m-d h:i:s', strtotime($data['to']));

                $where = 'CONVERT_TZ(main_table.'.$timeString.', \'+00:00\', \'+'.$this->_calOffsetHourGMT().':00\')';
                $collection->getSelect()->where($where.' >= "'.$startDay.'" AND '.$where.' <= "'.$endDay.'"');
                //\Zend_Debug::dump($collection->getSelect()->__toString());
                break;
        }
    }

    /**
     * @param $type
     * @param int $from
     * @param int $to
     * @param null $originalTime
     * @return array
     */
    protected function _buildArrayDate($type, $from = 0, $to = 23, $originalTime = null)
    {
        $data = [];

        switch ($type) {
            case ReportRange::REPORT_RAGE_LAST_24H:
                $startDay = $originalTime['d'];

                for ($i = $from; $i <= $to; $i++) {
                    $data[$i]['incr_hour'] = $i;
                    $data[$i]['native_hour'] = ($i > 24) ? $i - 24 : $i;
                    $data[$i]['day'] = $startDay;

                    if ($i == 23) {
                        $startDay++;
                    }
                }
                break;
            case ReportRange::REPORT_RAGE_LAST_WEEK:
                $data = [];
                $dayInMonth = $this->_daysInMonth(date('m'), date('Y'));
                $cloneFrom = $from;
                $reset = false;

                for ($i = 1; $i <=7; $i++) {
                    if ($from > $dayInMonth && !$reset) {
                        $cloneFrom = 1;
                        $reset = true;
                    }
                    $data[$i]['count_day'] = $from;
                    $data[$i]['native_day'] = $cloneFrom;
                    $from++;
                    $cloneFrom++;
                }
                break;
            case ReportRange::REPORT_RAGE_LAST_MONTH:
                for ($i = (int)$from; $i <= $to; $i++) {
                    $data[$i]['native_day'] = (int)$i;
                }
                break;
            case ReportRange::REPORT_RAGE_CUSTOM:
                $total_days = $this->_dateDiff($originalTime['from'], $originalTime['to']);
                if ($total_days <= 365) {
                    $allMonths = $this->_getMonths($originalTime['from'], $originalTime['to']);
                    $startTime = strtotime($originalTime['from']);
                    $startDay  = (int)date('d', $startTime);
                    $count      = 0;
                    $data       = [];
                    $endDayTime = strtotime($originalTime['to']);

                    $endDay = [
                        'm' => (int)date('m', $endDayTime),
                        'd' => (int)date('d', $endDayTime),
                        'y' => (int)date('Y', $endDayTime)
                    ];

                    foreach ($allMonths as $month) {
                        $dayInMonth = $this->_daysInMonth($month['m'], $month['y']);
                        for ($day = ($count == 0 ? $startDay : 1); $day <= $dayInMonth; $day++) {
                            if ($day > $endDay['d'] && $month['m'] == $endDay['m'] && $month['y'] == $endDay['y']) {
                                continue;
                            }
                            $data[$month['y']][$month['m']][$day] = $day;
                        }
                        $count++;
                    }
                }
                break;
        }

        return $data;
    }

    /**
     * @param $start
     * @param $end
     * @return array
     */
    protected function _getMonths($start, $end)
    {
        $start = ($start == '') ? time() : strtotime($start);
        $end = ($end == '') ? time() : strtotime($end);
        $months = [];
        $data = [];

        for ($i = $start; $i <= $end; $i = $this->_getNextMonth($i)) {
            $data['m'] = (int)date('m', $i);
            $data['y'] = (int)date('Y', $i);
            array_push($months, $data);
        }

        return $months;
    }

    /**
     * @param $timestamp
     * @return int
     */
    protected function _getNextMonth($timestamp)
    {
        return (strtotime('+1 months', strtotime(date('Y-m-01', $timestamp))));
    }

    /**
     * @return float
     */
    protected function _calOffsetHourGMT()
    {
        $timezone =  round($this->_dateTime->calculateOffset(
            $this->_storeManager->getStore()->getConfig('general/locale/timezone')
        )/60/60);

        $str = str_pad(ceil($timezone), 2, "0", STR_PAD_LEFT);
        if ($str > 0) {
            return '+'.$str;
        } else {
            return $str;
        }
    }

    /**
     * @return bool|string
     */
    protected function _getLastMonthTime()
    {
        return date('Y-m-d', strtotime("-1 month"));
    }

    /**
     * @param $month
     * @param $year
     * @return int
     */
    protected function _daysInMonth($month, $year = null)
    {
        $year = (!$year) ? date('Y', $this->_dateTime->gmtTimestamp()) : $year;

        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    /**
     * @param $data
     * @return bool
     */
    protected function _validationDate($data)
    {
        if (strtotime($data['from']) > strtotime($data['to'])) {
            return false;
        }

        return true;
    }

    /**
     * @param $hour
     * @return mixed
     */
    protected function _getPreviousDateTime($hour)
    {
        return (new \DateTime())->getTimestamp() - (3600 * $hour);
    }

    /**
     * Return the number of days between the two dates
     *
     * @param $firstDate
     * @param $secondDate
     * @return float
     */
    protected function _dateDiff($firstDate, $secondDate)
    {
        return round(abs(strtotime($firstDate) - strtotime($secondDate)) / 86400);
    }

    /**
     * @param $data
     * @param $totalsales
     * @param $totalcommission
     * @return array
     */
    protected function preapareCollectionPieChart($data, $totalsales, $totalcommission)
    {
        $programTable = $this->_appResource->getTableName('mw_affiliate_program');
        $orderTable = $this->_appResource->getTableName('sales_order');

        // Query to piechart
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->getSelect()->join(
            ['programs_entity' => $programTable],
            'main_table.program_id = programs_entity.program_id',
            []
        );
        $collection->addExpressionFieldToSelect('program', 'programs_entity.program_name', 'program');
        $collection->addExpressionFieldToSelect('total_affiliate_commission', 'if(sum(main_table.history_commission),sum(main_table.history_commission),0)', 'total_affiliate_commission');
        $collection->getSelect()->group(['main_table.program_id']);
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionPiechart = $collection;

        // Query to piechart sales by Programs
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->getSelect()->group(['main_table.program_id']);
        $collection->getSelect()->group(['main_table.order_id']);
        $collection->getSelect()->join(
            ['programs_entity' => $programTable],
            'main_table.program_id = programs_entity.program_id',
            []
        );
        $collection->getSelect()->join(
            ['sales_order' => $orderTable],
            'main_table.order_id = sales_order.increment_id',
            []
        );
        $collection->addExpressionFieldToSelect('program', 'programs_entity.program_name', 'program');
        $collection->addExpressionFieldToSelect('sales_order_total', 'sales_order.subtotal', 'sales_order_total');
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionPiechartSales = $collection;

        $dataSalesBuf = [];
        $dataSales = [];
        $dataCommission = [];

        foreach ($collectionPiechartSales as $key => $order) {
            if (isset($dataSalesBuf[$order->getData('program')])) {
                $dataSalesBuf[$order->getData('program')] += $order->getData('sales_order_total');
            } else {
                $dataSalesBuf[$order->getData('program')] = $order->getData('sales_order_total');
            }
        }

        if ($totalsales > 0) {
            $dataSales['Non Programs'] = 100;
            foreach ($dataSalesBuf as $key => $value) {
                $dataSales[$key] = $value / $totalsales * 100;
                $dataSales['Non Programs'] = $dataSales['Non Programs'] - $dataSales[$key];
            }
        }

        if ($totalcommission > 0) {
            $dataCommission['Non Programs'] = 100;
            foreach ($collectionPiechart as $key => $program) {
                $dataCommission[$program->getData('program')] = $program->getData('total_affiliate_commission') / $totalcommission * 100;
                $dataCommission['Non Programs'] = $dataCommission['Non Programs'] - $dataCommission[$program->getData('program')];
            }
        }

        $sales = [];
        $commission = [];

        foreach ($dataSales as $key => $percent) {
            if ($percent > 0.1) {
                $sales[]= [__(ucfirst($key)), $percent];
            }
        }

        foreach ($dataCommission as $key => $percent) {
            if ($percent > 0.1) {
                $commission[]= [__(ucfirst($key)), $percent];
            }
        }

        $_data['sales'] = $sales;
        $_data['commission'] = $commission;

        return $_data;
    }

    /**
     * @param $data
     * @param $totalcommission
     * @return array
     */
    protected function preapareCollectionPieChartFrontend($data, $totalcommission)
    {
        $programTable = $this->_appResource->getTableName('mw_affiliate_program');

        // Query to piechart
        $collection = $this->_historyFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.customer_invited', $data['customer_id']);
        $collection->getSelect()->join(
            ['programs_entity' => $programTable],
            'main_table.program_id = programs_entity.program_id',
            []
        );
        $collection->addExpressionFieldToSelect('program', 'programs_entity.program_name', 'program');
        $collection->addExpressionFieldToSelect('total_affiliate_commission', 'if(sum(history_commission),sum(history_commission),0)', 'total_affiliate_commission');
        $collection->getSelect()->group(['main_table.program_id']);
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionPiechartByPrograms = $collection;

        // Query Commission by my sales
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.customer_invited', $data['customer_id']);
        $collection->addFieldToFilter('main_table.customer_id', $data['customer_id']);
        $collection->addFieldToFilter('main_table.order_id', ['neq' => '']);
        $collection->addExpressionFieldToSelect('self_commission', 'if(sum(total_commission),sum(total_commission),0)', 'self_commission');
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionSelfCommission = $collection->getFirstItem();

        // Query Commission by other Actions
        $collection = $this->_transactionFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', Status::COMPLETE);
        $collection->addFieldToFilter('main_table.customer_invited', $data['customer_id']);
        $collection->addFieldToFilter('main_table.order_id', '');
        $collection->addExpressionFieldToSelect('refferrallink_commission', 'if(sum(total_commission),sum(total_commission),0)', 'refferrallink_commission');
        $this->_buildCollection($collection, $data, false, 'transaction_time');
        $collectionOtherCommission = $collection->getFirstItem();

        $dataCommissionByPrograms = [];
        $dataCommissionByMembers = [];

        if ($totalcommission > 0) {
            $dataCommissionByPrograms['Non Programs'] = 100;
            foreach ($collectionPiechartByPrograms as $key => $program) {
                $dataCommissionByPrograms[$program->getData('program')] = $program->getData('total_affiliate_commission') / $totalcommission * 100;
                $dataCommissionByPrograms['Non Programs'] = $dataCommissionByPrograms['Non Programs'] - $dataCommissionByPrograms[$program->getData('program')];
            }
        }

        if ($totalcommission > 0) {
            $dataCommissionByMembers['From My Purchases'] = $collectionSelfCommission->getData('self_commission') / $totalcommission * 100;
            $dataCommissionByMembers['Other Sources'] = $collectionOtherCommission->getData('refferrallink_commission') / $totalcommission * 100;
            $dataCommissionByMembers['From My Group Sale'] = 100 - $dataCommissionByMembers['From My Purchases'] - $dataCommissionByMembers['Other Sources'];
        }

        $commissionByPrograms = [];
        $commissionByMembers = [];

        foreach ($dataCommissionByMembers as $key => $percent) {
            if ($percent > 0.1) {
                $commissionByMembers[]= [__(ucfirst($key)), $percent];
            }
        }

        foreach ($dataCommissionByPrograms as $key => $percent) {
            if ($percent > 0.1) {
                $commissionByPrograms[]= [__(ucfirst($key)), $percent];
            }
        }

        $_data['commission_by_members'] = $commissionByMembers;
        $_data['commission_by_programs'] = $commissionByPrograms;

        return $_data;
    }

    /**
     * @param $affiliateId
     * @param $date
     * @param $count
     */
    public function _countAffiliateChilds($affiliateId, $date, &$count)
    {
        $collection = $this->_affiliatecustomersFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.status', 1);
        $collection->addFieldToFilter('main_table.customer_invited', $affiliateId);
        $collection->addFieldToFilter('main_table.referral_code', ['neq' => '']);
        $collection->addFieldToFilter('customer_time', ['from' => null, 'to' => $date]);

        if ($collection->getSize() > 0) {
            $count += $collection->getSize();
            foreach ($collection as $affiliate) {
                $this->_countAffiliateChilds($affiliate->getCustomerId(), $date, $count);
            }
        }
    }

    /**
     * @param $affiliateId
     * @param $date
     * @param $arrayMembers
     */
    public function _getArrayCustomers($affiliateId, $date, &$arrayMembers)
    {
        $collection = $this->_affiliatecustomersFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', 1);
        $collection->addFieldToFilter('main_table.customer_invited', $affiliateId);
        $collection->addFieldToFilter('customer_time', ['from' => null, 'to' => $date]);
        $collection->addExpressionFieldToSelect('customer_id', 'main_table.customer_id', 'customer_id');

        if ($collection->getSize() > 0) {
            foreach ($collection as $affiliate) {
                array_push($arrayMembers, $affiliate->getCustomerId());
            }

            foreach ($collection as $affiliate) {
                $this->_getArrayCustomers($affiliate->getCustomerId(), $date, $arrayMembers);
            }
        }
    }

    /**
     * @param $affiliateId
     * @param $data
     * @param $orderStatusAdd
     * @param $date
     * @return int
     */
    public function getTotalAffiliateSales($affiliateId, $data, $orderStatusAdd, $date)
    {
        // Get Sales by this affiliate
        $collection = $this->_orderFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.status', ['in' => $orderStatusAdd]);
        $collection->addFieldToFilter('main_table.customer_id', $affiliateId);
        $this->_buildCollection($collection, $data, false, 'updated_at');

        $collection->removeAllFieldsFromSelect();
        $collection->addExpressionFieldToSelect('total_sales', 'sum(main_table.subtotal)', 'total_sales');
        $myselfSale = $collection->getFirstItem()->getData('total_sales');

        $totalSales = 0;
        $totalSales = $totalSales + $myselfSale;

        // Get Array Customer of this affiliate
        $collection = $this->_affiliatecustomersFactory->create()->getCollection();
        $collection->removeAllFieldsFromSelect();
        $collection->addFieldToFilter('main_table.status', 1);
        $collection->addFieldToFilter('main_table.customer_invited', $affiliateId);
        $collection->addFieldToFilter('main_table.referral_code', '');
        $collection->addFieldToFilter('customer_time', ['from' => null, 'to' => $date]);
        $collection->addExpressionFieldToSelect('customer_id', 'main_table.customer_id', 'customer_id');

        $totalSaleCustomer = 0;
        foreach ($collection as $affiliate) {
            $totalSaleCustomer += $this->getSalesByCustomer($affiliate->getCustomerId(), $orderStatusAdd, $data);
        }

        $totalSales = $totalSales + $totalSaleCustomer;

        return $totalSales;
    }

    /**
     * @param $customId
     * @param $orderStatusAdd
     * @param $data
     * @return mixed
     */
    public function getSalesByCustomer($customId, $orderStatusAdd, $data)
    {
        $collection = $this->_orderFactory->create()->getCollection();
        $collection->addFieldToFilter('main_table.status', ['in' => $orderStatusAdd]);
        $collection->addFieldToFilter('main_table.customer_id', $customId);
        $this->_buildCollection($collection, $data, false, 'updated_at');

        $collection->removeAllFieldsFromSelect();
        $collection->addExpressionFieldToSelect('total_sales', 'sum(main_table.subtotal)', 'total_sales');

        return $collection->getFirstItem()->getTotalSales();
    }
}
