<?php

namespace Averun\SizeChart\Controller\Adminhtml\Data;

use Averun\SizeChart\Controller\Adminhtml\Data;
use Averun\SizeChart\Model\Data\ImportCategory;
use Averun\SizeChart\Model\Data\ImportChart;
use Averun\SizeChart\Model\Data\ImportDimension;
use Averun\SizeChart\Model\Data\ImportSize;
use Averun\SizeChart\Model\Data\ImportType;
use Averun\SizeChart\Setup\TruncateData;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir\Reader;

class ImportPost extends Data
{
    const TYPE_FILE_NAME        = 'type.csv';
    const CATEGORY_FILE_NAME    = 'category.csv';
    const DIMENSION_FILE_NAME   = 'dimension.csv';
    const CHART_FILE_NAME       = 'chart.csv';
    const SIZE_FILE_NAME        = 'size.csv';

    protected $files = [
        self::TYPE_FILE_NAME,
        self::CATEGORY_FILE_NAME,
        self::DIMENSION_FILE_NAME,
        self::CHART_FILE_NAME,
        self::SIZE_FILE_NAME
    ];
    protected $aliasToDataFiles = 'data/import';

    /** @var ImportType */
    protected $importType;
    /** @var ImportCategory */
    protected $importCategory;
    /** @var ImportDimension */
    protected $importDimension;
    /** @var ImportChart */
    protected $importChart;
    /** @var ImportSize */
    protected $importSize;

    /**
     * CSV Processor
     *
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var TruncateData
     */
    protected $truncateData;

    /** @var Reader */
    protected $dirReader;

    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Reader $dirReader,
        ImportType $importType,
        ImportSize $importSize,
        ImportChart $importChart,
        ImportCategory $importCategory,
        ImportDimension $importDimension,
        Csv $csvProcessor,
        TruncateData $uninstall
    ) {
        $this->truncateData = $uninstall;
        $this->importType = $importType;
        $this->importSize = $importSize;
        $this->importChart = $importChart;
        $this->importCategory = $importCategory;
        $this->importDimension = $importDimension;
        $this->csvProcessor = $csvProcessor;
        $this->dirReader = $dirReader;
        parent::__construct($context, $fileFactory);
    }

    /**
     * import action from import/export charts
     *
     * @return Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $timeStart = microtime(true);
                $this->checkFilesAvailable();
                $this->removeAllData();

                $rawData = $this->getRawDataFromFile(self::TYPE_FILE_NAME);
                $this->importType->import($rawData);
                $this->messageManager->addSuccessMessage(__('The Types has been imported.'));

                $rawData = $this->getRawDataFromFile(self::DIMENSION_FILE_NAME);
                $this->importDimension->import($rawData);
                $this->messageManager->addSuccessMessage(__('The Dimensions has been imported.'));

                $rawData = $this->getRawDataFromFile(self::CATEGORY_FILE_NAME);
                $this->importCategory->import($rawData);
                $this->messageManager->addSuccessMessage(__('The Categories has been imported.'));

                $rawData = $this->getRawDataFromFile(self::CHART_FILE_NAME);
                $this->importChart->import($rawData);
                $this->messageManager->addSuccessMessage(__('The Charts has been imported.'));

                $rawData = $this->getRawDataFromFile(self::SIZE_FILE_NAME);
                $this->importSize->import($rawData);
                $this->messageManager->addSuccessMessage(__('The Sizes has been imported.'));
                $endTime = number_format(microtime(true) - $timeStart, 3);
                $this->messageManager->addSuccessMessage(__('Import completed successfully.') . ' (total time: ' . $endTime . 's)');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Wrong request. Please try again.'));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }

    protected function getRawDataFromFile($fileName)
    {
        $rawData = $this->csvProcessor->getData($this->getFullFileName($fileName));
        return $rawData;
    }

    protected function getFullFileName($fileName)
    {
        $extensionPath = $this->dirReader->getModuleDir('', 'Averun_SizeChart');
        return $extensionPath . '/' . $this->aliasToDataFiles . '/' . $fileName;
    }

    protected function checkFilesAvailable()
    {
        foreach ($this->files as $fileName) {
            if (!file_exists($this->getFullFileName($fileName))) {
                throw new \Exception('File "' . $fileName . '" does not exist. Please check dir ' . $this->aliasToDataFiles
                                . ' in your extension folder. Needed list of files: ' . implode(',', $this->files));
            }
        }
    }

    protected function removeAllData()
    {
        $this->truncateData->truncate();
    }
}
