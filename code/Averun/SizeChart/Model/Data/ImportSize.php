<?php

namespace Averun\SizeChart\Model\Data;

use Averun\SizeChart\Model\Size;
use Averun\SizeChart\Model\SizeFactory;

class ImportSize extends CsvImportHandler
{

    protected $requiredFields = [
        'name',
        'chart_id',
        'dimension_id',
        'position'
    ];

    /** @var SizeFactory $sizeFactory */
    protected $sizeFactory;


    public function __construct(SizeFactory $sizeFactory)
    {
        $this->sizeFactory = $sizeFactory;
    }

    protected function importRow(array $dataRow)
    {
        /** @var Size $sizeModel */
        $sizeModel = $this->sizeFactory->create();
        $modelData = [
            'name'         => $dataRow[0],
            'chart_id'     => $dataRow[1],
            'dimension_id' => $dataRow[2],
            'position'     => $dataRow[3]
        ];
        $sizeModel->addData($modelData);
        $sizeModel->save();
    }
}
