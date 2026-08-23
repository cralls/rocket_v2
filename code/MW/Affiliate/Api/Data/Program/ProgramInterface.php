<?php

namespace MW\Affiliate\Api\Data\Program;

/**
 * Interface ProgramInterface
 * @package MW\Affiliate\Api\Data\Program
 */
interface ProgramInterface
{

    const PROGRAM_ID = "program_id";
    const PROGRAM_NAME = 'program_name';
    const PROGRAM_TYPE = 'program_type';
    const DESCRIPTION = 'description';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED = 'actions_serialized';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const COMMISSION = 'commission';
    const DISCOUNT = 'discount';
    const TOTAL_MEMBERS = 'total_members';
    const TOTAL_COMMISSION = 'total_commission';
    const BASE_TOTAL_COMMISSION = 'base_total_commission';
    const PROGRAM_POSITION = 'program_position';
    const STORE_VIEW = 'store_view';
    const STATUS = 'status';

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int|null
     */
    public function getProgramId();

    /**
     * @api
     * @param int $id
     * @return ProgramInterface
     */
    public function setProgramId($id);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string|null
     */
    public function getProgramName();

    /**
     * @api
     * @param string $programName
     * @return ProgramInterface
     */
    public function setProgramName($programName);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getProgramType();

    /**
     * @api
     * @param int $programType
     * @return ProgramInterface
     */
    public function setProgramType($programType);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getDescription();

    /**
     * @api
     * @param string $description
     * @return ProgramInterface
     */
    public function setDescription($description);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @api
     * @param string $condition
     * @return ProgramInterface
     */
    public function setConditionsSerialized($condition);


    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getActionsSerialized();

    /**
     * @api
     * @param string $action
     * @return ProgramInterface
     */
    public function setActionsSerialized($action);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getStartDate();

    /**
     * @api
     * @param string $startDate
     * @return ProgramInterface
     */
    public function setStartDate($startDate);

    /*----------------------------------------------------------------*/
    /**
     * @api
     * @return string
     */
    public function getEndDate();

    /**
     * @api
     * @param string $endDate
     * @return ProgramInterface
     */
    public function setEndDate($endDate);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getCommission();

    /**
     * @api
     * @param string $commission
     * @return ProgramInterface
     */
    public function setCommission($commission);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getDiscount();

    /**
     * @api
     * @param string $discount
     * @return ProgramInterface
     */
    public function setDiscount($discount);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getTotalMembers();

    /**
     * @api
     * @param int $totalMembers
     * @return ProgramInterface
     */
    public function setTotalMembers($totalMembers);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getTotalCommission();

    /**
     * @api
     * @param float $totalCommission
     * @return ProgramInterface
     */
    public function setTotalCommission($totalCommission);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return float
     */
    public function getBaseTotalCommission();

    /**
     * @api
     * @param float $baseTotalCommission
     * @return ProgramInterface
     */
    public function setBaseTotalCommission($baseTotalCommission);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getProgramPosition();

    /**
     * @api
     * @param int $programPosition
     * @return ProgramInterface
     */
    public function setProgramPosition($programPosition);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return string
     */
    public function getStoreView();

    /**
     * @api
     * @param string $storeView
     * @return ProgramInterface
     */
    public function setStoreView($storeView);

    /*----------------------------------------------------------------*/

    /**
     * @api
     * @return int
     */
    public function getStatus();

    /**
     * @api
     * @param int $status
     * @return ProgramInterface
     */
    public function setStatus($status);
}
