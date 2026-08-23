<?php

namespace Magenest\Xero\Model;

use Magenest\Xero\Model\Parser;
use Magenest\Xero\Model\XeroClient;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class XeroContactCron
{
    /**
     * @var \Magenest\Xero\Model\XeroClient
     */
    protected $xeroClient;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * XeroContactCron constructor.
     * @param \Magenest\Xero\Model\XeroClient $xeroClient
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     */
    public function __construct(XeroClient $xeroClient, DirectoryList $directoryList, Filesystem $filesystem)
    {
        $this->xeroClient = $xeroClient;
        $this->directoryList = $directoryList;
        $this->fileSystem = $filesystem;
    }

    /**
     * @return bool
     * @throws FileSystemException
     */
    public function writeContacts()
    {
        $contacts = $this->getContactsOnXero();
        if (!empty($contacts)) {
            $this->createContactFile($contacts);
            return true;
        }
        return false;
    }

    /**
     * @return false|string
     * @throws \Exception
     */
    protected function getContactsOnXero()
    {
        $helper = $this->xeroClient->getSignature();
        $helper->setUri('Contacts');
        $url = $helper->getUri();
        $response = $this->xeroClient->sendRequest($url);
        $parsedResponse = $this->parseXML($response);
        if (isset($parsedResponse['Contacts']['Contact'])) {
            return json_encode($parsedResponse['Contacts']['Contact']);
        }

        return '';
    }

    /**
     * @param $xml
     * @return array|bool
     */
    public function parseXML($xml)
    {
        $parser = new Parser();
        return $parser->parseXML($xml);
    }

    /**
     * @param $contacts
     * @throws FileSystemException
     */
    public function createContactFile($contacts)
    {
        try {
            $varDirectory = $this->fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        } catch (FileSystemException $e) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('Xero Create Contact File Exception: '.$e->getMessage());
        }
        $varPath = $this->directoryList->getPath('var');
        $fileName = 'contact.txt';
        $path = $varPath . '/xerocontact/' . $fileName;
        $this->write($varDirectory, $path, $contacts);
    }

    /**
     * @param WriteInterface $writeDirectory
     * @param $filePath
     * @param $contacts
     * @return bool
     */
    public function write(WriteInterface $writeDirectory, $filePath, $contacts)
    {
        $stream = $writeDirectory->openFile($filePath, 'w');
        $stream->lock();
        try {
            $stream->write($contacts);
        } catch (FileSystemException $e) {
            \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)->debug('Xero Exception: '.$e->getMessage());
        }
        $stream->unlock();
        $stream->close();
        return true;
    }
}
