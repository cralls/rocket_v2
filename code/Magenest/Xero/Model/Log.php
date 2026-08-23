<?php
namespace Magenest\Xero\Model;

class Log extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Log constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Helper $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        Helper $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\Magenest\Xero\Model\ResourceModel\Log::class);
    }
    protected function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 ||
            (substr($haystack, -$length) === $needle);
    }

    /**
     * @param $type
     * @param $response
     */
    // @codingStandardsIgnoreStart
    public function logResponse($syncType, $type, $response)
    {
        $entityIdKey = $syncType == 'Item' ? 'Code' : $syncType.'Number';
        $xeroIdKey = $syncType.'ID';
        $records = [];
        if (is_array($response)) {
            foreach ($response as $res_level_1) {
                if (is_array($res_level_1)) {
                    foreach ($res_level_1 as $res_level_2) {
                        if (is_array($res_level_2)) {
                            if (isset($res_level_2[0])) {
                                foreach ($res_level_2 as $res_level_3) {
                                    //if (isset($res_level_3[$entityIdKey])) {
                                    if ($res_level_3['_attribute']['status'] == 'OK') {
                                        $records[] = [
                                            'type' => $type,
                                            'entity_id' => $res_level_3['_value'][$entityIdKey] ?? substr($res_level_3['_value'][$xeroIdKey], 0, 6),
                                            'dequeue_time' => new \Zend_Db_Expr('CURRENT_TIMESTAMP'),
                                            'status' => Log\Status::SUCCESS_STATUS,
                                            'xero_id' => $res_level_3['_value'][$xeroIdKey],
                                            'msg' => 'OK',
                                            'xml_log_id' => isset($res_level_3['_value'][$entityIdKey]) ? $this->getXmlLogId($res_level_3['_value'][$entityIdKey], $syncType) :
                                                $this->getXmlLogId($this->getKey($res_level_3['_value'], $entityIdKey, $xeroIdKey, $syncType), $syncType)
                                        ];
                                    }
                                    //if (isset($res_level_3['_value'])) {
                                    if ($res_level_3['_attribute']['status'] == 'ERROR') {
                                        $error = $res_level_3['_value']['ValidationErrors']['ValidationError'];
                                        //$msg = 'ERROR MESSAGE: '. $response['Message'];
                                        $msg = '';
                                        $entityId = '';
                                        if (isset($error[0])) {
                                            $msg .= 'ERROR DETAIL: ';
                                            foreach ($error as $e) {
                                                $msg .= $e['Message'].', ';
                                            }
                                        } else {
                                            $msg .= 'ERROR DETAIL: ';
                                            $msg .= $error['Message'];
                                        }
                                        if (isset($res_level_3[$entityIdKey])) {
                                            $entityId = $res_level_3[$entityIdKey];
                                        }
                                        if (isset($res_level_3['_value'][$entityIdKey])) {
                                            $entityId = $res_level_3['_value'][$entityIdKey];
                                        }
                                        $entityId = $entityId ? : $this->getKey($res_level_3['_value'], $entityIdKey, $xeroIdKey, $syncType);
                                        $records[] = [
                                            'type' => $type,
                                            'dequeue_time' => new \Zend_Db_Expr('CURRENT_TIMESTAMP'),
                                            'entity_id' => $entityId,
                                            'status' => Log\Status::ERROR_STATUS,
                                            'xero_id' => null,
                                            'msg' => $msg,
                                            'xml_log_id' => $this->getXmlLogId($entityId, $syncType)
                                        ];
                                    }
                                }
                                $this->saveRecords($records);
                                return;
                            } else {
                                if ($res_level_2['_attribute']['status'] == 'ERROR') {
                                    $error = $res_level_2['_value']['ValidationErrors']['ValidationError'];
                                    //$msg = 'ERROR MESSAGE: '. $response['Message'];
                                    $msg = '';
                                    $entityId = '';
                                    if (isset($error[0])) {
                                        $msg .= 'ERROR DETAIL: ';
                                        foreach ($error as $e) {
                                            $msg .= $e['Message'].', ';
                                        }
                                    } else {
                                        $msg .= 'ERROR DETAIL: ';
                                        $msg .= $error['Message'];
                                    }
                                    if (isset($res_level_2[$entityIdKey])) {
                                        $entityId = $res_level_2[$entityIdKey];
                                    }
                                    if (isset($res_level_2['_value'][$entityIdKey])) {
                                        $entityId = $res_level_2['_value'][$entityIdKey];
                                    }
                                    $entityId = $entityId ? : $this->getKey($res_level_2['_value'], $entityIdKey, $xeroIdKey, $syncType);
                                    $records[] = [
                                        'type' => $type,
                                        'dequeue_time' => new \Zend_Db_Expr('CURRENT_TIMESTAMP'),
                                        'entity_id' => $entityId,
                                        'status' => Log\Status::ERROR_STATUS,
                                        'xero_id' => null,
                                        'msg' => $msg,
                                        'xml_log_id' => $this->getXmlLogId($entityId, $syncType)
                                    ];
                                }

                                if ($res_level_2['_attribute']['status'] == 'OK') {
                                    $records[] = [
                                        'type' => $type,
                                        'entity_id' => isset($res_level_2['_value'][$entityIdKey]) ? $res_level_2['_value'][$entityIdKey] : substr($res_level_2['_value'][$xeroIdKey], 0, 6),
                                        'dequeue_time' => new \Zend_Db_Expr('CURRENT_TIMESTAMP'),
                                        'status' => 1,
                                        'xero_id' => $res_level_2['_value'][$xeroIdKey],
                                        'msg' => 'OK',
                                        'xml_log_id' => $this->getXmlLogId($this->getKey($res_level_2['_value'], $entityIdKey, $xeroIdKey, $syncType), $syncType)
                                    ];
                                }
                            }
                            $this->saveRecords($records);
                            return;
                        }
                    }
                }
            }
            if (isset($response['ErrorNumber'])) {
                $records[] = [
                    'type' => $type,
                    'dequeue_time' => new \Zend_Db_Expr('CURRENT_TIMESTAMP'),
                    'status' => Log\Status::ERROR_STATUS,
                    'msg' => $response['Message']
                ];
                $this->saveRecords($records);
                return;
            }
        }
    }
    // @codingStandardsIgnoreEnd
    protected function getKey($res_level_2, $entityIdKey, $xeroIdKey, $syncType)
    {
        if ($syncType == "Payment" && isset($res_level_2['Invoice'])) {
            return $res_level_2['Invoice']['InvoiceNumber'];
        } elseif ($syncType == "Payment" && isset($res_level_2['CreditNote'])) {
            return $res_level_2['CreditNote']['CreditNoteNumber'];
        }
        return isset($res_level_2[$entityIdKey]) ? $res_level_2[$entityIdKey] : substr($res_level_2[$xeroIdKey], 0, 6);
    }

    protected function saveRecords($records)
    {
        if (count($records)) {
            $this->getResource()->getConnection()->insertMultiple($this->getResource()->getMainTable(), $records);
        }
    }

    protected function getIdKeyByType($type)
    {
        if ($type == "CreditNote") {
            return 'CreditNoteNumber';
        }
        return $type;
    }

    protected function getXmlLogId($id, $type)
    {
        return $this->_helper->getSavedId($id, $type);
    }
}
