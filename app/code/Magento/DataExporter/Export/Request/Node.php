<?php
declare(strict_types=1);

namespace Magento\DataExporter\Export\Request;

/**
 * Class Node
 */
class Node
{
    /**
     * @var array
     */
    private $field;

    /**
     * @var array
     */
    private $children;

    /**
     * Node constructor.
     *
     * @param array $field
     * @param array $children
     */
    public function __construct(
        array $field,
        array $children
    ) {
        $this->field = $field;
        $this->children = $children;
    }

    /**
     * Get child nodes
     *
     * @return Node[]
     */
    public function getChildren() : array
    {
        return $this->children;
    }

    /**
     * Get field
     *
     * @return array
     */
    public function getField() : array
    {
        return $this->field;
    }
}
