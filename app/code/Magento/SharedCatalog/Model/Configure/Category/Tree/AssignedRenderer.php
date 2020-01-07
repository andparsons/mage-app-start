<?php
namespace Magento\SharedCatalog\Model\Configure\Category\Tree;

use Magento\Framework\Data\Tree\Node;

/**
 * Prepare nodes data for tree of assigned categories.
 */
class AssignedRenderer extends Renderer
{
    /**
     * Get node data as array.
     *
     * @param Node $node
     * @param int $level Node level in tree sructure [optional]
     * @return array
     */
    protected function getNodeDataAsArray($node, $level = 0)
    {
        $item = [];

        $children = [];
        if ($node->hasChildren()) {
            foreach ($node->getChildren() as $child) {
                $childData = $this->getNodeDataAsArray($child, $level + 1);
                if (count($childData)) {
                    $children[] = $childData;
                }
            }
        }

        if (count($children) || $node->getIsChecked()) {
            $item = $this->prepareNodeData($node, $item);
            $item['children'] = $children;
        }

        return $item;
    }
}
