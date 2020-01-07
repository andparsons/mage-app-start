<?php

namespace Magento\Catalog\Model\ResourceModel\Product;

class LinkedProductSelectBuilderComposite implements LinkedProductSelectBuilderInterface
{
    /**
     * @var LinkedProductSelectBuilderInterface[]
     */
    private $linkedProductSelectBuilder;

    /**
     * @param LinkedProductSelectBuilderInterface[] $linkedProductSelectBuilder
     */
    public function __construct($linkedProductSelectBuilder)
    {
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function build($productId)
    {
        $selects = [];
        foreach ($this->linkedProductSelectBuilder as $productSelectBuilder) {
            $selects = array_merge($selects, $productSelectBuilder->build($productId));
        }

        return $selects;
    }
}
