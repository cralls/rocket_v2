<?php

namespace MW\Affiliate\Model\Api;

/**
 * Class CommissionRepository
 * @package MW\Affiliate\Model\Api
 */
class CommissionRepository implements \MW\Affiliate\Api\Commission\CommissionRepositoryInterface
{
    /**
     * @var  \MW\Affiliate\Model\ResourceModel\Affiliatetransaction
     */
    protected $commissionResourceModel;
    /**
     * @var \MW\Affiliate\Model\ResourceModel\Affiliatetransaction\CollectionFactory
     */
    protected $commissionResourceCollection;
    /**
     * @var \MW\Affiliate\Api\Data\Commission\CommissionInterfaceFactory
     */
    protected $commissionFactory;

    public function __construct(
        \MW\Affiliate\Model\ResourceModel\Affiliatetransaction $commissionResourceModel,
        \MW\Affiliate\Model\ResourceModel\Affiliatetransaction\CollectionFactory $commissionResourceCollection,
        \MW\Affiliate\Api\Data\Commission\CommissionInterfaceFactory $commissionFactory
    ) {
        $this->commissionResourceModel = $commissionResourceModel;
        $this->commissionResourceCollection = $commissionResourceCollection;
        $this->commissionFactory = $commissionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MW\Affiliate\Api\Data\Commission\CommissionInterface $commission)
    {
        try {
            $this->commissionResourceModel->save($commission);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save Commission'));
        }
        return $commission;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($commissionId)
    {
        $commission = $this->commissionFactory->create();
        $this->commissionResourceModel->load($commission, $commissionId);
        if (!$commission->getHistoryId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Commission with id "%1" does not exist.', $commissionId));
        } else {
            return $commission;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MW\Affiliate\Api\Data\Commission\CommissionInterface $commission)
    {
        return $this->deleteById($commission->getHistoryId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($commissionId)
    {
        $commission = $this->getById($commissionId);
        $this->commissionResourceModel->delete($commission);
    }
}
