<?php
namespace Averun\SizeChart\Model\Attribute\Source;

class Category extends AbstractModel
{
    protected $tableComment = ' Category of sizes column';

    protected function initDefaultModelCollection()
    {
        $this->defaultModelCollection = $this->resourceCategoryCollection;
    }
}
