<?php

namespace Averun\SizeChart\Model\Data;

use Averun\SizeChart\Model\Chart;
use Averun\SizeChart\Model\ChartFactory;

class ImportChart extends CsvImportHandler
{

    protected $requiredFields = [
        'identifier',
        'name',
        'category',
        'type',
        'dimension',
        'description',
        'note',
        'image',
        'is_active'
    ];

    /** @var ChartFactory $chartFactory */
    protected $chartFactory;


    public function __construct(ChartFactory $chartFactory)
    {
        $this->chartFactory = $chartFactory;
    }

    protected function importRow(array $dataRow)
    {
        /** @var Chart $chartModel */
        $chartModel = $this->chartFactory->create();
        $dimensions = explode(',', $dataRow[4]);
        $dimensions = array_map('trim', $dimensions);
        $dimensions = implode(',', $dimensions);
        $modelData = [
            'identifier'  => trim($dataRow[0]),
            'name'        => trim($dataRow[1]),
            'category'    => trim($dataRow[2]), //todo: перевірка категорій
            'type'        => trim($dataRow[3]), //todo: перевірка типів,
            'dimension'   => $dimensions,       //todo: перевірка вимірів.
            'description' => trim($dataRow[5]),
            'note'        => trim($dataRow[6]),
            'image'       => trim($dataRow[7]),
            'is_active'   => (int)$dataRow[8]
        ];
        $chartModel->addData($modelData);
        $chartModel->save();
    }
}
