<?php
namespace MageArray\OrderAttachments\Helper;

use MageArray\OrderAttachments\Model\Attachment\FileUploaderFactory;
use MageArray\OrderAttachments\Model\Attachments;
use MageArray\OrderAttachments\Model\Attachments as AttachmentList;
use MageArray\OrderAttachments\Model\FileAttachmentFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ATTACHMENT_DIR = 'attachment_dir';
    const XML_PATH_SEND_EMAIL_ATTACHMENT_CUSTOMER = 'send_email_attachment_customer';
    const XML_PATH_SEND_EMAIL_ATTACHMENT_ADMIN = 'send_email_attachment_admin';
    const XML_PATH_SEND_EMAIL_ATTACHMENT_TYPE = 'send_email_attach_type';
    const XML_PATH_SEND_EMAIL_ATTACHMENT_ORDER_STATUSES = 'order_statuses';
    const XML_PATH_ENABLE_CUSTOM_FILENAME = 'enable_custom_filename';
    const XML_PATH_ENABLE_CUSTOM_FILE_PATH = 'custom_file_path';
    const XML_PATH_ADMIN_EMAIL = 'admin_email';
    const XML_PATH_ADMIN_NAME = 'admin_name';
    const XML_PATH_ALLOWED_EXTENSIONS = 'allowed_extensions';
    const XML_PATH_MAX_FILE_SIZE = 'max_file_size_attachment';
    const XML_PATH_CAN_DELETE_ATTACHMENTS = 'can_delete_attachments';
    const XML_PATH_SEND_EMAIL_SEPARATELY = 'send_email_separately';
    const XML_PATH_ENABLED_EMAIL = 'enabled_email';
    const XML_ADD_ATTACHMENT = 'add_attachment';
    const XML_DISPLAY_ALLOWED_FILE_TYPE = 'display_allowed_file_type';
    const XML_DISPLAY_MAX_FILE_SIZE = 'display_max_file_size';
    const MAX_FILE_SIZE = 50;
    const CUSTOM_FILE_PATH_LEVEL = 2;
    const SEND_MAIL = 1;
    const SEND_ATTACHMENT_TYPE = 2;
    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */

    protected $attachmentArea = '';
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Cart $cart,
        FileAttachmentFactory $fileAttachmentFactory,
        Filesystem $filesystem,
        FileUploaderFactory $fileUploaderFactory,
        DateTime $dateTime,
        Email $emailHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \MageArray\OrderAttachments\Model\AttachmentsFactory $attachmentFactory,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        Registry $registry
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
        $this->fileAttachmentFactory = $fileAttachmentFactory;
        $this->filesystem = $filesystem;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->dateTime = $dateTime;
        $this->_emailHelper = $emailHelper;
        $this->orderRepository = $orderRepository;
        $this->_customerFactory = $customerFactory;
        $this->attachmentFactory = $attachmentFactory;
        $this->reader = $reader;
        $this->messageManager = $messageManager;
        $this->_coreRegistry = $registry;
        $this->orderFactory = $orderFactory;
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    /**
     * @param $storePath
     * @return mixed
     */
    public function getStoreConfig($storePath, $store = null)
    {
        return $this->_scopeConfig
            ->getValue('orderattachments/general/' . $storePath, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return string
     */
    public function allowedFiles()
    {
        $allowedExtensions = $this->getStoreConfig(self::XML_PATH_ALLOWED_EXTENSIONS);
        if (!empty($allowedExtensions)) {
            return $allowedExtensions;
        } else {
            return 'jpg,jpeg,gif,bmp,png';
        }
    }

    /**
     * @return string
     */
    public function displayFileTypes()
    {
        return $this->getStoreConfig(self::XML_DISPLAY_ALLOWED_FILE_TYPE);
    }

    /**
     * @return int
     */
    public function maxFileSize()
    {
        $maxFileSize = $this->getStoreConfig(self::XML_PATH_MAX_FILE_SIZE);
        if (!empty($maxFileSize)) {
            return $maxFileSize;
        } else {
            return self::MAX_FILE_SIZE;
        }
    }

    /**
     * @return int
     */
    public function displayMaxFileSize()
    {
        return $this->getStoreConfig(self::XML_DISPLAY_MAX_FILE_SIZE);
    }

    /**
     * @return mixed
     */
    public function canDelete()
    {
        return $this->getStoreConfig(self::XML_PATH_CAN_DELETE_ATTACHMENTS);
    }

    /**
     * @return mixed
     */
    public function getMediaPath()
    {
        $path = $this->getAttachmentFilePath();
        $storeManager = $this->_storeManager;
        $currentStore = $storeManager->getStore();
        if ($path) {
            $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
            return $mediaUrl;
        }
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'magearray/attachments';
        return $mediaUrl;
    }

    public function fileData($quoteId = null)
    {
        if ($quoteId == null) {
            $cartQuote = $this->cart->getQuote();
            $quoteId = $cartQuote->getEntityId();
        }

        $collection = $this->fileAttachmentFactory
            ->create()->getCollection();
        $collection->addFieldToFilter('quote_id', ['eq' => $quoteId]);
        return $collection->getData();
    }

    /**
     * @return mixed
     */
    public function getAttachmentFilePath($store = null)
    {
        $path = $this->getStoreConfig(self::XML_PATH_ATTACHMENT_DIR, $store);
        if ($path) {
            return $path;
        }

        $path = 'magearray/attachments';
        return $path;
    }

    /**
     * @return mixed
     */
    public function getTempMediaPath()
    {
        $storeManager = $this->_storeManager;
        $currentStore = $storeManager->getStore();
        $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . AttachmentList::TMP_PATH;
        return $mediaUrl;
    }

    /**
     * @return mixed
     */
    public function isMailToCustomerEnabled($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_SEND_EMAIL_ATTACHMENT_CUSTOMER, $store);
    }

    /**
     * @return mixed
     */
    public function isMailToAdminEnabled($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_SEND_EMAIL_ATTACHMENT_ADMIN, $store);
    }
    /**
     * @return mixed
     */
    public function getAttachmentType($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_SEND_EMAIL_ATTACHMENT_TYPE, $store);
    }

    /**
     * @return mixed
     */
    public function getAdminEmail($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_ADMIN_EMAIL, $store);
    }

    /**
     * @return mixed
     */
    public function getAdminName($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_ADMIN_NAME, $store);
    }

    /**
     * @return mixed
     */
    public function getOrderStatuses($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_SEND_EMAIL_ATTACHMENT_ORDER_STATUSES, $store);
    }

    /**
     * @return mixed
     */
    public function getEnableCustomFile($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_ENABLE_CUSTOM_FILENAME, $store);
    }

    /**
     * @return mixed
     */
    public function isEmailEnabled($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_ENABLED_EMAIL, $store);
    }

    /**
     * @return mixed
     */
    public function getAddAttachments($store = null)
    {
        return $this->getStoreConfig(self::XML_ADD_ATTACHMENT, $store);
    }

    /**
     * @return mixed
     */
    public function getEmailSaperately($store = null)
    {
        return $this->getStoreConfig(self::XML_PATH_SEND_EMAIL_SEPARATELY, $store);
    }

    /**
     * @return mixed
     */
    public function getCustomFilePath($orderId, $store = null)
    {
        $customFilePath = '';
        if ($this->getEnableCustomFile()) {
            $customFilePath = $this->getStoreConfig(self::XML_PATH_ENABLE_CUSTOM_FILE_PATH, $store);
            $customFilePath = str_replace("{{", "", $customFilePath);
            $customFilePath = strtolower($customFilePath);

            $filePathArray = [];
            $desinationPath = '';
            if (strpos($customFilePath, '/') !== false) {
                $filePathArray = explode('/', $customFilePath);
                foreach ($filePathArray as $key => $value) {
                    if (substr_count($value, '}}') >= self::CUSTOM_FILE_PATH_LEVEL) {
                        $filePathArray[$key]  = array_filter(explode("}}", $value));
                    } else {
                        $filePathArray[$key]  = str_replace("}}", "", $value);
                    }
                }
            } else {
                $filePathArray = explode('}}', $customFilePath);
            }
            $desinationPath = $this->getCustomPath($orderId, $filePathArray);
            if (!empty($desinationPath)) {
                $desinationPath = '/' . $desinationPath;
                $desinationPath = preg_replace('/[\s+:"?*|<>]/', '_', $desinationPath);
            }
        }

        return $desinationPath;
    }

    public function getCustomPath($orderId, $filePathArray)
    {
        $folderPath = '';
        $order = $this->orderFactory->create()->load($orderId);
        // $order = array $this->orderRepository->get($orderId);

        $currenttime = $this->dateTime->gmtTimestamp();
        foreach (array_filter($filePathArray) as $key => $val) {
            if (!is_array($val) && !empty($val)) {
                if (!empty($order->getData($val))) {
                    if ($val == 'updated_at' || $val == 'created_at') {
                        $folderPath .= $currenttime . '/';
                    } else {
                        $folderPath .= strtolower($order->getData($val)) . '/';
                    }
                } elseif ($val == 'customer_name') {
                        $customerName = $order->getCustomerFirstname().'_'.$order->getCustomerLastname();
                        $folderPath .= strtolower($customerName). '/';
                } elseif ($val == 'order_id') {
                        $folderPath .= $order->getId(). '/';
                } else {
                    $nameVariable = strtolower($val);
                    if ($nameVariable  == 'billing_firstname' || $nameVariable  == 'billing_lastname' ||
                        $nameVariable  == 'customer_firstname' || $nameVariable  == 'customer_lastname') {
                        $name = substr($nameVariable, strpos($nameVariable, "_") + 1);
                        $folderPath .= strtolower($order->getBillingAddress()->getData($name)) . '/';
                    } else {
                        if (!empty($order->getData($val))) {
                            $folderPath .= $order->getData($val). '/';
                        }

                    }
                }
            } else {
                $combinePath = '';
                foreach ($val as $k => $v) {
                    if (!empty($order->getData($v))) {
                        if ($v == 'updated_at' || $v == 'created_at') {
                            $combinePath .= $currenttime. '/';
                        } else {
                            $combinePath .= strtolower($order->getData($v)). '/';
                        }
                    } else {
                        $nameVariable = strtolower($v);
                        if ($nameVariable  == 'billing_firstname' || $nameVariable  == 'billing_lastname' ||
                            $nameVariable  == 'customer_firstname' || $nameVariable  == 'customer_lastname') {
                            $name = substr($nameVariable, strpos($nameVariable, "_") + 1);
                            $combinePath .= strtolower($order->getBillingAddress()->getData($name)). '/';
                        }
                    }
                }
                $folderPath .= $combinePath;
            }
        }
        return $folderPath;
    }

    public function uploadFile()
    {
        try {
            $fileUploader = $this->fileUploaderFactory
                ->create(['fileId' => 'file[0]'])->setAllowRenameFiles(true);
            $mediaDirectory = $this->filesystem
                ->getDirectoryRead(DirectoryList::MEDIA);
            $result = $fileUploader->save($mediaDirectory->getAbsolutePath(Attachments::TMP_PATH));
            $currentDate = $this->_timezoneInterface->date()->format('Y-m-d H:i:s');
            $result['currentDate'] = $currentDate;
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $result;
    }

    public function getMediaAbsolutePath()
    {
        $path = $this->getAttachmentFilePath();
        $absolutePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        if ($path) {
            $mediaUrl = $absolutePath . $path;
            return $mediaUrl;
        }
        $mediaUrl = $absolutePath . 'magearray/attachments';
        return $mediaUrl;
    }

    public function saveAttachment($attachmentArea, $post)
    {
        $sendMail = 0;
        $sendFile = [];
        $removeAttachmentId = [];
        $postData = $post['attachment'];
        $orderId = $post['order_id'];
        $customerId = $post['customer_id'];
        $this->attachmentArea = $attachmentArea;
        if ($this->attachmentArea == 'admin') {
            $sendMail =  $this->isMailToCustomerEnabled();
        } else {
            $sendMail = $this->isMailToAdminEnabled();
        }
        foreach ($postData as $values) {
            if (isset($values['remove'])) {
                if (isset($values['file_exist']) &&
                    !empty($values['file_exist'])
                ) {
                    $removeAttachmentId[] = $values['file_exist'];
                }
            } else {
                $sendFile[] = $this->saveAndGetFileData($values, $orderId, $customerId);
            }
        }

        $sendFile = array_map('array_filter', $sendFile);
        $sendFile = array_filter($sendFile);

        if (isset($removeAttachmentId) &&
        !empty($removeAttachmentId)
        ) {
            $this->removeAttachments($removeAttachmentId);
        }
        $emailEnabled = $this->isEmailEnabled();
        if ($emailEnabled == 1 && $sendMail == self::SEND_MAIL && !empty($sendFile)) {
            $this->sendEmail($orderId, $customerId, $sendFile, 0);
        }
        $this->messageManager
            ->addSuccess(__('Attachments details have been saved successfully.'));
        return true;
    }
    public function saveAndGetFileData($values, $orderId, $customerId)
    {
        $sendFile = [];
        $currenttime = $this->dateTime->gmtDate('Y-m-d h:i:s');
        $mediaUrl =  $this->getMediaPath();
        $model = $this->attachmentFactory->create();

        if (isset($values['new_file']) &&
            !empty($values['new_file'])) {
            $model->setData('created_at', $currenttime);
            $model->setData('updated_at', $currenttime);
        }
        if (isset($values['file_exist']) && !empty($values['file_exist'])) {
            $fileData = $model->load($values['file_exist']);
            $oldComment = $fileData->getComment();
            if (trim($oldComment) != trim($values['comment'])) {
                $model->setData('updated_at', $currenttime);
            }
        }
        $model->setData('comment', $values['comment']);
        if ($this->attachmentArea == 'customer') {
            $model->setData('visible_customer_account', 1);
        } elseif (isset($values['visible_customer_account'])) {
            $model->setData('visible_customer_account', 1);
        } else {
            $model->setData('visible_customer_account', 0);
        }
        $model->setData('order_id', $orderId);
        $model->setData('customer_id', $customerId);

        $model->setData('file_name', $values['file_name']);
        $model->setData('file_path', $values['file_path']);
        $model->save();
        if ($this->getAttachmentType() == self::SEND_ATTACHMENT_TYPE) {
            if (isset($values['new_file']) && !empty($values['new_file'])) {
                $attachmentData = $model->load($model->getId());
                $file = $this->reader->fileGetContents($mediaUrl . $attachmentData->getFilePath());
                $sendFile = ['name'=>$attachmentData->getFileName(), 'contents'=> $file];
            }
        } else {
            $attachmentData = $model->load($model->getId());
            $file = $this->reader->fileGetContents($mediaUrl . $attachmentData->getFilePath());
            $sendFile = ['name'=>$attachmentData->getFileName(), 'contents'=> $file];
        }
        return $sendFile;
    }

    public function removeAttachments($removeAttachmentId)
    {
        $model = $this->attachmentFactory->create()->getCollection()
                      ->addFieldToFilter('id', ['in' => $removeAttachmentId]);
        $filePth = $this->getMediaAbsolutePath() . $model->getFirstItem()->getFilePath();
        if ($this->reader->isExists($filePth)) {
            $this->reader->deleteFile($filePth);
        }
        $model->walk('delete');
    }

    public function sendEmail($orderId, $customerId, $sendFile, $fromCheckout)
    {
        $order = $this->orderRepository->get($orderId);
        if ($customerId) {
            $customer = $this->_customerFactory->create()->load($customerId);
            $custName = $customer->getName();
            $custEmail = $customer->getEmail();
        } else {
            $custName = 'Guest User';
            $custEmail = $order->getCustomerEmail();
        }
        if ($fromCheckout) {
            $firstName = $order->getBillingAddress()->getFirstName();
            $lastName = $order->getBillingAddress()->getLastName();
            $this->attachmentArea = 'customer';
            $custName = $firstName . ' ' . $lastName;
            $custEmail = $order->getCustomerEmail();
        }

        $adminEmail = $this->getAdminEmail();
        $adminName = $this->getAdminName();

        if ($this->attachmentArea == 'admin') {
            $receiverInfo = [
                'name' =>  $custName,
                'email' => $custEmail,
            ];

            $senderInfo = [
                'name' => $adminName,
                'email' => $adminEmail,
            ];
        } else {
            $senderInfo = [
                'name' => $custName,
                'email' => $custEmail,
            ];

            $receiverInfo = [
                'name' => $adminName,
                'email' => $adminEmail,
            ];
        }

        $emailTempVariables = [ ];
        $emailTempVariables['order_id'] = $order->getIncrementId();
        $emailTempVariables['update'] = $this->attachmentArea;
        $emailTempVariables['name'] = $senderInfo['name'];
        $emailTempVariables['email'] = $senderInfo['email'];
        $this->_emailHelper->notifyByEmailAttachment(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo,
            $sendFile
        );
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getDateFormat($date)
    {
        return $this->_timezoneInterface->date(new \DateTime($date))->format('Y-m-d H:i:s');
    }

    public function getAttachedFiles($orderId = null)
    {
        if ($orderId == null) {
            $orderId = $this->getOrder()->getId();
        }
        $collection = $this->attachmentFactory->create()->getCollection();
        $collection->addFieldToFilter('order_id', $orderId);
        return $collection->getData();
    }
}
