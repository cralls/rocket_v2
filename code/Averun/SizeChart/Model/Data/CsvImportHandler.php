<?php
namespace Averun\SizeChart\Model\Data;

use Magento\Framework\Exception\LocalizedException;

class CsvImportHandler
{

    protected $requiredFields = [
        'Code',
        'Country',
        'State',
        'Zip/Post Code',
        'Rate',
        'Zip/Post is Range',
        'Range From',
        'Range To'
    ];

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields()
    {
        return $this->requiredFields;
    }

    /**
     * Import Data from CSV file
     *
     * @param array $rawData file info retrieved from $_FILES array
     * @return void
     * @throws LocalizedException
     */
    public function import($rawData)
    {
        $data = $this->filterData($rawData);
        foreach ($data as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            $this->importRow($dataRow);
        }
    }

    /**
     * Filter data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rawData
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function filterData(array $rawData)
    {
        $fileFields = $rawData[0];
        $validFields = $this->getRequiredCsvFields();
        $invalidFields = array_diff_key($fileFields, $validFields);

        $validFieldsNum = count($validFields);
        foreach ($rawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($rawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($rawData[$rowIndex]) != $validFieldsNum) {
                throw new LocalizedException(__('Invalid file format.'));
            }
        }
        return $rawData;
    }

    /**
     * Import single row
     *
     * @param array $dataRow
     * @return array regions cache populated with regions related to country of imported tax rate
     * @throws LocalizedException
     */
    protected function importRow(array $dataRow)
    {
        throw new \Exception('Must be implemented in the child class');
    }

    /**
     * Add regions of the given country to regions cache
     *
     * @param string $countryCode
     * @param array $regionsCache
     * @return array
     */
    protected function _addCountryRegionsToCache($countryCode, array $regionsCache)
    {
        if (!isset($regionsCache[$countryCode])) {
            $regionsCache[$countryCode] = [];
            // add 'All Regions' to the list
            $regionsCache[$countryCode]['*'] = '*';
//            $regionCollection = clone $this->_regionCollection;
//            $regionCollection->addCountryFilter($countryCode);
//            if ($regionCollection->getSize()) {
//                foreach ($regionCollection as $region) {
//                    $regionsCache[$countryCode][$region->getCode()] = $region->getRegionId();
//                }
//            }
        }
        return $regionsCache;
    }
}
