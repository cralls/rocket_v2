<?php

namespace MW\Affiliate\Model\Api;

/**
 * Class TransactionRepository
 * @package MW\Affiliate\Model\Api
 */
class TransactionRepository implements \MW\Affiliate\Api\Transaction\TransactionRepositoryInterface
{
    /**
     * @var  \MW\Affiliate\Model\ResourceModel\Credithistory
     */
    protected $transactionResourceModel;
    /**
     * @var \MW\Affiliate\Model\ResourceModel\Credithistory\CollectionFactory
     */
    protected $transactionResourceCollection;
    /**
     * @var \MW\Affiliate\Api\Data\Transaction\TransactionInterfaceFactory
     */
    protected $transactionFactory;

    public function __construct(
        \MW\Affiliate\Model\ResourceModel\Credithistory $transactionResourceModel,
        \MW\Affiliate\Model\ResourceModel\Credithistory\CollectionFactory $transactionResourceCollection,
        \MW\Affiliate\Api\Data\Transaction\TransactionInterfaceFactory $transactionFactory
    ) {
        $this->transactionResourceModel = $transactionResourceModel;
        $this->transactionResourceCollection = $transactionResourceCollection;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MW\Affiliate\Api\Data\Transaction\TransactionInterface $transaction)
    {
        try {
            $this->transactionResourceModel->save($transaction);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save Transaction'));
        }
        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($transactionId)
    {
        $transaction = $this->transactionFactory->create();
        $this->transactionResourceModel->load($transaction, $transactionId);
        if (!$transaction->getCreditHistoryId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Transaction with id "%1" does not exist.', $transactionId));
        } else {
            return $transaction;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MW\Affiliate\Api\Data\Transaction\TransactionInterface $transaction)
    {
        return $this->deleteById($transaction->getCreditHistoryId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($transactionId)
    {
        $transaction = $this->getById($transactionId);
        $this->transactionResourceModel->delete($transaction);
    }
}
