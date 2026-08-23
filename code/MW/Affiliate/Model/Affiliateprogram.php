<?php

namespace MW\Affiliate\Model;

class Affiliateprogram extends \Magento\Rule\Model\AbstractModel implements \MW\Affiliate\Api\Data\Program\ProgramInterface
{
    protected $_conditions;

    protected $_actions;

    protected $_form;

    /**
     * Is model deleteable
     *
     * @var boolean
     */
    protected $_isDeleteable = true;

    /**
     * Is model readonly
     *
     * @var boolean
     */
    protected $_isReadonly = false;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Combine
     */
    protected $_salesRuleCombine;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine
     */
    protected $_salesRuleProductCombine;

    protected $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\SalesRule\Model\Rule\Condition\Combine $salesRuleCombine
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\Combine $salesRuleProductCombine
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $salesRuleCombine,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $salesRuleProductCombine,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        $this->_salesRuleCombine = $salesRuleCombine;
        $this->_salesRuleProductCombine = $salesRuleProductCombine;
        $this->serializer = $serializer;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MW\Affiliate\Model\ResourceModel\Affiliateprogram');
        $this->setIdFieldName('program_id');
    }

    /**-+
     * Get conditions
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    public function getConditionsInstance()
    {
        return $this->_salesRuleCombine->create();
    }

    /**
     * Get actions
     *
     * @return \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    public function getActionsInstance()
    {
        return $this->_salesRuleProductCombine->create();
    }

    /**
     * Returns rule as an array for admin interface
     *
     * Output example:
     * array(
     *   'name'=>'Example rule',
     *   'conditions'=>{condition_combine::asArray}
     *   'actions'=>{action_collection::asArray}
     * )
     *
     * @return array
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'name'        => $this->getName(),
            'start_at'    => $this->getStartAt(),
            'expire_at'   => $this->getExpireAt(),
            'description' => $this->getDescription(),
            'conditions'  => $this->getConditions()->asArray(),
            'actions'     => $this->getActions()->asArray(),
        ];

        return $out;
    }

    public function afterLoad()
    {
        $this->_afterLoad();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        /*
        $conditionsArr = unserialize($this->getConditionsSerialized());
        if (!empty($conditionsArr) && is_array($conditionsArr)) {
            $this->getConditions()->loadArray($conditionsArr);
        }

        $actionsArr = unserialize($this->getActionsSerialized());
        if (!empty($actionsArr) && is_array($actionsArr)) {
            $this->getActions()->loadArray($actionsArr);
        }*/
    }

    /**
     * Prepare data before saving
     */
    public function beforeSave()
    {
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized($this->serializer->serialize($this->getConditions()->asArray()));
            $this->unsConditions();
            $this->_conditions = null;
        }

        // Serialize actions
        if ($this->getActions()) {
            $this->setActionsSerialized($this->serializer->serialize($this->getActions()->asArray()));
            $this->unsActions();
            $this->_actions = null;
        }

        if (!$this->getId()) {
            $this->isObjectNew(true);
        }
        $this->_eventManager->dispatch('model_save_before', ['object' => $this]);
        $this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());
        return $this;
    }

    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }
        //return $this->_conditions;
        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }
    public function getActions()
    {
        if (!$this->_actions) {
            $this->_resetActions();
        }
        //return $this->_actions;
        // Load rule actions if it is applicable
        if ($this->hasActionsSerialized()) {
            $actions = $this->getActionsSerialized();
            if (!empty($actions)) {
                $actions = $this->serializer->unserialize($actions);
                if (is_array($actions) && !empty($actions)) {
                    $this->_actions->loadArray($actions);
                }
            }
            $this->unsActionsSerialized();
        }

        return $this->_actions;
    }


    public function getProgramId()
    {
        return $this->getData(self::PROGRAM_ID);
    }
    public function setProgramId($id)
    {
        return $this->setData(self::PROGRAM_ID, $id);
    }

    public function getProgramName()
    {
        return $this->getData(self::PROGRAM_NAME);
    }
    public function setProgramName($programName)
    {
        return $this->setData(self::PROGRAM_NAME, $programName);
    }

    public function getProgramType()
    {
        return $this->getData(self::PROGRAM_TYPE);
    }
    public function setProgramType($programType)
    {
        return $this->setData(self::PROGRAM_TYPE, $programType);
    }

    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }
    public function setConditionsSerialized($condition)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $condition);
    }

    public function getActionsSerialized()
    {
        return $this->getData(self::ACTIONS_SERIALIZED);
    }
    public function setActionsSerialized($action)
    {
        return $this->setData(self::ACTIONS_SERIALIZED, $action);
    }

    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    public function getCommission()
    {
        return $this->getData(self::COMMISSION);
    }
    public function setCommission($commission)
    {
        return $this->setData(self::COMMISSION, $commission);
    }

    public function getDiscount()
    {
        return $this->getData(self::DISCOUNT);
    }
    public function setDiscount($discount)
    {
        return $this->setData(self::DISCOUNT, $discount);
    }

    public function getTotalMembers()
    {
        return $this->getData(self::TOTAL_MEMBERS);
    }
    public function setTotalMembers($totalMembers)
    {
        return $this->setData(self::TOTAL_MEMBERS, $totalMembers);
    }

    public function getTotalCommission()
    {
        return $this->getData(self::TOTAL_COMMISSION);
    }
    public function setTotalCommission($totalCommission)
    {
        return $this->setData(self::TOTAL_COMMISSION, $totalCommission);
    }

    public function getBaseTotalCommission()
    {
        return $this->getData(self::BASE_TOTAL_COMMISSION);
    }
    public function setBaseTotalCommission($baseTotalCommission)
    {
        return $this->setData(self::BASE_TOTAL_COMMISSION, $baseTotalCommission);
    }

    public function getProgramPosition()
    {
        return $this->getData(self::PROGRAM_POSITION);
    }
    public function setProgramPosition($programPosition)
    {
        return $this->setData(self::PROGRAM_POSITION, $programPosition);
    }

    public function getStoreView()
    {
        return $this->getData(self::STORE_VIEW);
    }
    public function setStoreView($storeView)
    {
        return $this->setData(self::STORE_VIEW, $storeView);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
