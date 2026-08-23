<?php

namespace MW\Affiliate\Service;

class OrderService
{

    /**
     * @var \MW\Affiliate\Helper\Data
     */
    protected $_dataHelper;

    /**
     * OrderSevice constructor.
     * @param \MW\Affiliate\Helper\Data $dataHelper
     */
    public function __construct(
        \MW\Affiliate\Helper\Data $dataHelper
    ) {
        $this->_dataHelper = $dataHelper;
    }


    // cap nhat lai trang thai complete cua order
    // update status complete when add commission
    public function saveOrderComplete($order_id, $storeCode = null)
    {
        $order_ids = [];
        $order_ids[] = $order_id;
        $check_holding = $this->_dataHelper->holdingTimeConfig($storeCode);
        $collections = $this->_dataHelper->getModel('Affiliatehistory')
            ->getCollection()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Status::PENDING);
        foreach ($collections as $collection) {
            $program_id = $collection->getProgramId();
            $history_commission = $collection->getHistoryCommission();
            $affiliate_programs = $this->_dataHelper->getModel('Affiliateprogram')
                ->getCollection()
                ->addFieldToFilter('program_id', $program_id);
            foreach ($affiliate_programs as $affiliate_program) {
                $total_commission_old = $affiliate_program->getTotalCommission();
                $total_commission_new = $total_commission_old + $history_commission;
                $total_commission_new = round($total_commission_new, 2);
                $this->_dataHelper->getModel('Affiliateprogram')->load($program_id)->setTotalCommission($total_commission_new)->save();
            }

            /* Set status to holding when order complete => commission-holding-function */
            //$collection->setStatus(MW_Affiliate_Model_Status::COMPLETE);

            if ($check_holding > 0) {
                $collection->setStatus(\MW\Affiliate\Model\Status::HOLDING);
            } else {
                $collection->setStatus(\MW\Affiliate\Model\Status::COMPLETE);
            }

            $collection->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
            $collection->save();
        }
        $transaction_collections = $this->_dataHelper->getModel('Affiliatetransaction')->getCollection()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Status::PENDING);
        foreach ($transaction_collections as $transaction_collection) {
            if ($check_holding > 0) {
                $transaction_collection->setStatus(\MW\Affiliate\Model\Status::HOLDING);
            } else {
                $transaction_collection->setStatus(\MW\Affiliate\Model\Status::COMPLETE);
            }
            $transaction_collection->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
            $transaction_collection->save();
        }

        if (sizeof($collections) > 0) {
            $this->_dataHelper->dispatchEvent(
                'mw_affiliate_save_credit_history',
                [
                    'order_ids'=>$order_ids
                ]
            );
        }
    }

    // update status closed (refund product) to order
    public function saveOrderClosed($order_id, $storeCode = null)
    {
        $enableDiscount = $this->_dataHelper->getDiscountWhenRefundProductStore($storeCode);
        $order_ids = [];
        $order_ids[] = $order_id;
        $collections = $this->_dataHelper->getModel('Affiliatehistory')
            ->getCollection()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Status::COMPLETE);
        foreach ($collections as $collection) {
            if ($enableDiscount == 1) {
                $program_id = $collection ->getProgramId();
                $history_commission = $collection ->getHistoryCommission();
                $affiliate_programs =  $this->_dataHelper->getModel('Affiliateprogram')->getCollection()
                    ->addFieldToFilter('program_id', $program_id);
                foreach ($affiliate_programs as $affiliate_program) {
                    $total_commission_old = $affiliate_program ->getTotalCommission();
                    $total_commission_new = $total_commission_old - $history_commission;
                    $total_commission_new = round($total_commission_new, 2);
                    //$affiliate_program ->setTotalCommission($total_commission_new)->save();
                    $this->_dataHelper->getModel('Affiliateprogram')->load($program_id)->setTotalCommission($total_commission_new)->save();
                }
            }
            $collection->setStatus(\MW\Affiliate\Model\Status::CLOSED);
            $collection->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
            $collection->save();
        }
        $transaction_collections = $this->_dataHelper->getModel('Affiliatetransaction')->getCollection()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Status::COMPLETE);
        foreach ($transaction_collections as $transaction_collection) {
            $transaction_collection->setStatus(\MW\Affiliate\Model\Status::CLOSED);
            $transaction_collection->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
            $transaction_collection->save();
        }

        if ($enableDiscount == 1) {
            $this->_dataHelper->dispatchEvent(
                'mw_affiliate_refund_order',
                [
                    'order_ids'=>$order_ids
                ]
            );
        }
    }

    // update status canceled for trasaction affiliate
    public function saveOrderCanceled($order_id, $storeCode = null)
    {
        $collections = $this->_dataHelper->getModel('Affiliatehistory')->getCollection()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Status::PENDING);

        foreach ($collections as $collection) {
            $collection->setStatus(\MW\Affiliate\Model\Status::CANCELED);
            $collection->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
            $collection->save();
        }
        $transaction_collections = $this->_dataHelper->getModel('Affiliatetransaction')->getCollection()
            ->addFieldToFilter('order_id', $order_id)
            ->addFieldToFilter('status', \MW\Affiliate\Model\Status::PENDING);
        foreach ($transaction_collections as $transaction_collection) {
            $transaction_collection->setStatus(\MW\Affiliate\Model\Status::CANCELED);
            $transaction_collection->setTransactionTime(date("Y-m-d H:i:s", (new \DateTime())->getTimestamp()));
            $transaction_collection->save();
        }
    }
}
