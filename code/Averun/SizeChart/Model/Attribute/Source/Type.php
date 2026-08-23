<?php
namespace Averun\SizeChart\Model\Attribute\Source;

class Type extends AbstractModel
{
    protected $tableComment = ' Type column';

    protected function initDefaultModelCollection()
    {
        $this->defaultModelCollection = $this->resourceTypeCollection;
    }
}
