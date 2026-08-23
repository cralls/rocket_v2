<?php

namespace MW\Affiliate\Api\Program;

interface ProgramRepositoryInterface
{
    /**
     * @param \MW\Affiliate\Api\Data\Program\ProgramInterface $program
     * @return \MW\Affiliate\Api\Data\Program\ProgramInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\MW\Affiliate\Api\Data\Program\ProgramInterface $program);

    /**
     * @param int $programId
     * @return \MW\Affiliate\Api\Data\Program\ProgramInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($programId);

    /**
     * @param \MW\Affiliate\Api\Data\Program\ProgramInterface $program
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\MW\Affiliate\Api\Data\Program\ProgramInterface $program);

    /**
     * @param int $program
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($program);
}
