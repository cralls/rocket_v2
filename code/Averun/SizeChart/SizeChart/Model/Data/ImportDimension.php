<?php

namespace Averun\SizeChart\Model\Data;

use Averun\SizeChart\Model\Dimension;
use Averun\SizeChart\Model\DimensionFactory;
use Averun\SizeChart\Model\Entity\DimensionTypeInterface;

class ImportDimension extends CsvImportHandler
{

    protected $requiredFields = [
        'identifier',
        'name',
        'description',
        'type',
        'main',
        'position',
        'is_active',
        'length_type'
    ];

    /** @var DimensionFactory $dimensionFactory */
    protected $dimensionFactory;


    public function __construct(DimensionFactory $dimensionFactory)
    {
        $this->dimensionFactory = $dimensionFactory;
    }

    protected function importRow(array $dataRow)
    {
        /** @var Dimension $dimensionModel */
        $dimensionModel = $this->dimensionFactory->create();
        $type = $dataRow[3] == 'region' ? DimensionTypeInterface::TYPE_REGION : DimensionTypeInterface::TYPE_DIMENSION;
        $modelData = [
            'identifier'  => $dataRow[0],
            'name'        => $dataRow[1],
            'description' => $dataRow[2],
            'type'        => $type,
            'main'        => $dataRow[4],
            'position'    => $dataRow[5],
            'is_active'   => $dataRow[6],
            'length_type' => $dataRow[7]
        ];
        $dimensionModel->addData($modelData);
        $dimensionModel->save();
    }
}
