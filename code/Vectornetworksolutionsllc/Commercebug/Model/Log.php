<?php
/**
* Copyright © Pulse Storm LLC 2016
* All rights reserved
*/
namespace Vectornetworksolutionsllc\Commercebug\Model;
use Magento\Framework\DataObject\IdentityInterface;
class Log extends \Magento\Framework\Model\AbstractModel implements LogInterface, IdentityInterface
{
    const CACHE_TAG = 'pulsestorm_commercebug_log';

    protected function _construct()
    {
        $this->_init('Vectornetworksolutionsllc\Commercebug\Model\ResourceModel\Log');
    }

    protected function purgeOldRecords()
    {
        $sql = 'DELETE FROM ' . $this->getResource()->getMainTable() . 
            ' WHERE pulsestorm_commercebug_log_id NOT IN ( 
              SELECT pulsestorm_commercebug_log_id 
              FROM ( 
                SELECT pulsestorm_commercebug_log_id 
                FROM ' . $this->getResource()->getMainTable() . ' 
                ORDER BY pulsestorm_commercebug_log_id DESC 
                LIMIT 10
              ) x 
            );';

        $query = $this->getResource()->getConnection()->query($sql);
        $query->execute();
    }
    
    protected function getMaxAllowedPacketSize()
    {
        $result = '1000000';
        $db = $this->getResource()->getConnection();
        $query = $db->query('SHOW VARIABLES LIKE "max_allowed_packet"');        
        $result = $query->fetch(); 
        if($result && is_array($result) && isset($result['Value']))
        {
            $result = $result['Value'] - 10000; //10000 is arbitrary to account
                                                //for magento's generated SQL
        }
        return $result;
    }
    
    public function logData($data)
    {
        $encoded = json_encode($data);
        if(strlen($encoded) > $this->getMaxAllowedPacketSize())
        {
            $encoded = json_encode([
                'error'=>"We're sorry, we couldn't log the data.  The short version is Magento 2 generates a lot of information on every request -- sometimes more than a full MB when represented as JSON (which is the format we log in). MySQL has a 'max_allowed_packet' variable that sets the maximum size for a query.  It looks like your max_allowed_packet size was too large for your MySQL configuration to handle, so rather than bail we've generated this error message.  The long version is in the " . __CLASS__ . " definition file. Good luck, and if you're super confused please contact support at: http://www.pulsestorm.net/contact-us/"
            ]);
        }
        $this->setData(['json_log'=>$encoded])
        ->save();                         
        $this->purgeOldRecords();       
    }
    
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
