<?php

namespace Averun\SizeChart\Model\Data;

use Averun\SizeChart\Model\Category;
use Averun\SizeChart\Model\CategoryFactory;

class ImportCategory extends CsvImportHandler
{

    protected $requiredFields = [
        'identifier',
        'name',
        'position',
        'is_active'
    ];

    /** @var CategoryFactory $categoryFactory */
    protected $categoryFactory;


    public function __construct(CategoryFactory $categoryFactory)
    {
        $this->categoryFactory = $categoryFactory;
    }

    protected function importRow(array $dataRow)
    {
        /** @var Category $categoryModel */
        $categoryModel = $this->categoryFactory->create();
        $modelData = [
            'identifier' => $dataRow[0],
            'name'       => $dataRow[1],
            'position'   => $dataRow[2],
            'is_active'  => $dataRow[3]
        ];
        $categoryModel->addData($modelData);
        $categoryModel->save();
    }
}
