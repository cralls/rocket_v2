<?php

namespace MW\Affiliate\Model\Api;

/**
 * Class AccountRepository
 * @package MW\Affiliate\Model\Api
 */
class AccountRepository implements \MW\Affiliate\Api\Account\AccountRepositoryInterface
{

    /**
     * @var  \MW\Affiliate\Model\ResourceModel\Affiliatecustomers
     */
    protected $userResourceModel;
    /**
     * @var \MW\Affiliate\Model\ResourceModel\Affiliatecustomers\CollectionFactory
     */
    protected $userResourceCollection;
    /**
     * @var \MW\Affiliate\Api\Data\Account\AccountInterfaceFactory
     */
    protected $userFactory;


    public function __construct(
        \MW\Affiliate\Model\ResourceModel\Affiliatecustomers $userResourceModel,
        \MW\Affiliate\Model\ResourceModel\Affiliatecustomers\CollectionFactory $userResourceCollection,
        \MW\Affiliate\Api\Data\Account\AccountInterfaceFactory $userFactory
    ) {
        $this->userResourceModel = $userResourceModel;
        $this->userResourceCollection = $userResourceCollection;
        $this->userFactory = $userFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MW\Affiliate\Api\Data\Account\AccountInterface $account)
    {
        try {
            $this->userResourceModel->save($account);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save User'));
        }
        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($accountId)
    {
        $user = $this->userFactory->create();
        $this->userResourceModel->load($user, $accountId);
        if (!$user->getCustomerId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('User with id "%1" does not exist.', $accountId));
        } else {
            return $user;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MW\Affiliate\Api\Data\Account\AccountInterface $account)
    {
        return $this->deleteById($account->getCustomerId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($accountId)
    {
        $user = $this->getById($accountId);
        $this->userResourceModel->delete($user);
    }
}
