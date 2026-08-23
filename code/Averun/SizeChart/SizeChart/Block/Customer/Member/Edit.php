<?php
namespace Averun\SizeChart\Block\Customer\Member;

use Averun\SizeChart\Api\Data\EntityTypeInterface;
use Averun\SizeChart\Model\Entity\DimensionTypeInterface;
use Averun\SizeChart\Model\Member;
use Averun\SizeChart\Model\ResourceModel\Dimension\Collection as DimensionCollection;
use Magento\Customer\Model\Session;
use Magento\Directory\Block\Data;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

class Edit extends Data
{
    protected $measurementData;

    /**
     * @var Member
     */
    private $modelMember;
    /**
     * @var DimensionCollection
     */
    private $dimensionCollection;

    /**
     * @var Session
     */
    protected $customerSession;

    public function __construct(
        Context $context,
        HelperData $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        CollectionFactory $regionCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        Member $modelMember,
        DimensionCollection $dimensionCollection,
        Session $customerSession,
        array $data = []
    ) {
        $this->dimensionCollection = $dimensionCollection;
        $this->modelMember = $modelMember;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($id = $this->getRequest()->getParam('id')) {
            $this->modelMember->load($id);
            if ($this->modelMember->getCustomerId() != $this->customerSession->getCustomerId()) {
                $this->modelMember->setData([]);
            }
        }
        if (!$this->modelMember->getId()) {
            $this->modelMember->setCustomerId($this->getCustomer()->getId());
        }
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->getTitle());
        }
    }

    /**
     * @return array
     */
    public function getMeasurementList()
    {
        if (empty($this->measurementData)) {
            /** @var $dimensionCollection DimensionCollection */
            $dimensionCollection = $this->dimensionCollection;
            $dimensionCollection->addFieldToFilter('type', DimensionTypeInterface::TYPE_DIMENSION);
            $dimensionCollection->addFieldToFilter('is_active', 1);
            $dimensionCollection->addAttributeToSelect('name', true);
            $dimensionCollection->addAttributeToSelect('id');
            $dimensionCollection->addAttributeToSelect('entity_id');
            $dimensionCollection->addAttributeToSelect('description', true);
            $memberId = (int) $this->getRequest()->getParam('id');
            if (!empty($memberId)) {
                $dimensionCollection->joinField(
                    'value',
                    $dimensionCollection->getTable(EntityTypeInterface::MEMBER_MEASURE_CODE),
                    'value',
                    'dimension_id=identifier',
                    ['member_id' => $memberId],
                    'left'
                );
            }
            $dimensionCollection->addOrder('position', DataCollection::SORT_ORDER_ASC);
            $dimensionCollection->load();
            $this->measurementData = $dimensionCollection->getData();
        }
        return $this->measurementData;
    }

    public function getTitle()
    {
        if ($title = $this->getData('title')) {
            return $title;
        }
        if ($this->getMemberModel()->getId()) {
            $title = __('Edit Member');
        } else {
            $title = __('Add New Member');
        }
        return $title;
    }

    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }
        return $this->getUrl('sizechart/member_manage');
    }

    public function getSaveUrl()
    {
        return $this->getUrl(
            'sizechart/member_manage/formPost',
            ['_secure' => true, 'id' => $this->getMemberModel()->getId()]
        );
    }

    public function getMemberModel()
    {
        return $this->modelMember;
    }

    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
