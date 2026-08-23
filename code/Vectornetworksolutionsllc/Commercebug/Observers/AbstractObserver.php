<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Vectornetworksolutionsllc\Commercebug\Observers;
abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    static protected $_calling=false;
    static protected $_isDev='unset';
    public function __construct(
        \Magento\Developer\Helper\Data $developerHelper
    )
    {
        $this->developerHelper = $developerHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(self::$_calling)
        {
            return;
        }
        self::$_calling = true;
        if(!$this->isDev($observer))
        {
            self::$_calling = false;
            return;
        }
        self::$_calling = false;
        return $this->_execute($observer);
    }

    /**
     * Wrapper for isDev
     *
     * Helps centralize logic to avoid the
     * >    More than one default website is defined
     * exception from early collection events
     */
    protected function isDev($observer)
    {
        //check cached value
        if(self::$_isDev !== 'unset')
        {
            return self::$_isDev;
        }

        //early website collection loading can trigger a nested call to
        //Magento\Store\Model\WebsiteRepository::initDefaultWebsite
        //which in turn will trigger the
        //>    More than one default website is defined
        //exception
        $collection = $observer->getCollection();
        if (is_object($collection) && @get_class($collection) === 'Magento\Store\Model\ResourceModel\Website\Collection' &&
            $observer->getEvent()->getName() === 'core_collection_abstract_load_before')
        {
            return true;
        }

        //cache value
        self::$_isDev = $this->developerHelper->isDevAllowed();

        //return
        return self::$_isDev;
    }

    abstract protected function _execute(\Magento\Framework\Event\Observer $observer);
}
