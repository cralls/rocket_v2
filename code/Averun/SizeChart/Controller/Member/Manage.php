<?php
namespace Averun\SizeChart\Controller\Member;

use Averun\SizeChart\Model\MemberFactory;
use Averun\SizeChart\Model\MemberMeasureFactory;
use Averun\SizeChart\Model\ResourceModel\Member\CollectionFactory;
use Averun\SizeChart\Model\ResourceModel\MemberMeasure\CollectionFactory as MemberMeasureCollectionFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Result\PageFactory;

abstract class Manage extends AbstractAccount
{
    const PARAM_CRUD_ID = 'id';

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var MemberMeasureFactory
     */
    protected $memberMeasureFactory;
    /**
     * @var MemberMeasureCollectionFactory
     */
    protected $memberMeasureCollectionFactory;
    /**
     * @var MemberFactory
     */
    protected $memberFactory;
    /**
     * @var CollectionFactory
     */
    protected $memberCollectionFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Validator $formKeyValidator,
        MemberMeasureFactory $memberMeasureFactory,
        MemberMeasureCollectionFactory $memberMeasureCollectionFactory,
        MemberFactory $memberFactory,
        CollectionFactory $memberCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->memberMeasureFactory = $memberMeasureFactory;
        $this->memberMeasureCollectionFactory = $memberMeasureCollectionFactory;
        $this->memberFactory = $memberFactory;
        $this->memberCollectionFactory = $memberCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function _buildUrl($route = '', $params = [])
    {
        /** @var UrlInterface $urlBuilder */
        $urlBuilder = $this->_objectManager->create('Magento\Framework\UrlInterface');
        return $urlBuilder->getUrl($route, $params);
    }

    /**
     * Retrieve customer session object
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->customerSession;
    }
}
