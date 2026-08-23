<?php

namespace Averun\SizeChart\Model\Data;

use Averun\SizeChart\Model\Type;
use Averun\SizeChart\Model\TypeFactory;

class ImportType extends CsvImportHandler
{

    protected $requiredFields = [
        'identifier',
        'name',
        'position',
        'is_active'
    ];

    /** @var TypeFactory $typeFactory */
    protected $typeFactory;


    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    protected function importRow(array $dataRow)
    {
        /** @var Type $typeModel */
        $typeModel = $this->typeFactory->create();
        $modelData = [
            'identifier' => $dataRow[0],
            'name'       => $dataRow[1],
            'position'   => $dataRow[2],
            'is_active'  => $dataRow[3]
        ];
        $typeModel->addData($modelData);
        $typeModel->save();
    }
}
