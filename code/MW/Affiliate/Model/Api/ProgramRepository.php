<?php

namespace MW\Affiliate\Model\Api;

/**
 * Class ProgramRepository
 * @package MW\Affiliate\Model\Api
 */
class ProgramRepository implements \MW\Affiliate\Api\Program\ProgramRepositoryInterface
{
    /**
     * @var  \MW\Affiliate\Model\ResourceModel\Affiliateprogram
     */
    protected $programResourceModel;
    /**
     * @var \MW\Affiliate\Model\ResourceModel\Affiliateprogram\CollectionFactory
     */
    protected $programResourceCollection;
    /**
     * @var \MW\Affiliate\Api\Data\Program\ProgramInterfaceFactory
     */
    protected $programFactory;

    public function __construct(
        \MW\Affiliate\Model\ResourceModel\Affiliateprogram $programResourceModel,
        \MW\Affiliate\Model\ResourceModel\Affiliateprogram\CollectionFactory $programResourceCollection,
        \MW\Affiliate\Api\Data\Program\ProgramInterfaceFactory $programFactory
    ) {
        $this->programResourceModel = $programResourceModel;
        $this->programResourceCollection = $programResourceCollection;
        $this->programFactory = $programFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\MW\Affiliate\Api\Data\Program\ProgramInterface $program)
    {
        try {
            $this->programResourceModel->save($program);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save Program'));
        }
        return $program;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($programId)
    {
        $program = $this->programFactory->create();
        $this->programResourceModel->load($program, $programId);
        if (!$program->getProgramId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Program with id "%1" does not exist.', $programId));
        } else {
            return $program;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\MW\Affiliate\Api\Data\Program\ProgramInterface $program)
    {
        return $this->deleteById($program->getProgramId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($programId)
    {
        $program = $this->getById($programId);
        $this->programResourceModel->delete($program);
    }
}
